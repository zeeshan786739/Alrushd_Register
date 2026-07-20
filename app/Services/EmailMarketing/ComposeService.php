<?php

namespace App\Services\EmailMarketing;

use App\Enums\EmailMarketing\DeliveryStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\MessageAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComposeService
{
    public function __construct(
        private MailConfigResolver $mailConfig,
        private HtmlSanitizer $sanitizer,
        private AttachmentService $attachments,
    ) {
    }

    /**
     * @param  array{
     *   to:string,cc?:?string,bcc?:?string,subject:string,body_html?:?string,body_text?:?string,
     *   lead_id?:?int,customer_id?:?int,parent_id?:?int,created_by?:?int
     * }  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function send(int $organizationId, array $data, array $files = []): Message
    {
        $settings = $this->mailConfig->resolveOrFail($organizationId);
        $this->mailConfig->applyRuntimeConfig($settings);

        $to = $this->normalizeRecipients($data['to']);
        if ($to === []) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $html = $this->sanitizer->sanitize($data['body_html'] ?? '');
        $text = $data['body_text'] ?? $this->sanitizer->toPlainText($html);

        return DB::transaction(function () use ($organizationId, $data, $files, $settings, $to, $html, $text) {
            $message = Message::create([
                'organization_id' => $organizationId,
                'folder' => 'sent',
                'direction' => 'outbound',
                'message_id' => 'local-'.Str::uuid(),
                'parent_id' => $data['parent_id'] ?? null,
                'from_email' => $settings->from_email,
                'from_name' => $settings->from_name,
                'to' => implode(', ', $to),
                'cc' => $data['cc'] ?? null,
                'bcc' => $data['bcc'] ?? null,
                'subject' => $data['subject'],
                'body_html' => $html,
                'body_text' => $text,
                'delivery_status' => DeliveryStatus::Sending->value,
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);

            foreach ($files as $file) {
                $this->attachments->storeUpload($message, $file);
            }

            try {
                Mail::html($html ?: nl2br(e($text)), function ($mail) use ($message, $to, $settings) {
                    $mail->to($to)
                        ->subject($message->subject)
                        ->from($settings->from_email, $settings->from_name);

                    if ($message->cc) {
                        $mail->cc($message->parseAddressList($message->cc));
                    }
                    if ($message->bcc) {
                        $mail->bcc($message->parseAddressList($message->bcc));
                    }
                    if ($settings->reply_to) {
                        $mail->replyTo($settings->reply_to);
                    }

                    foreach ($message->attachments as $attachment) {
                        $mail->attach(Storage::disk($attachment->disk)->path($attachment->path), [
                            'as' => $attachment->original_name,
                            'mime' => $attachment->mime_type,
                        ]);
                    }
                });

                $message->update([
                    'delivery_status' => DeliveryStatus::Sent->value,
                    'sent_at' => now(),
                    'delivery_error' => null,
                ]);

                $this->logCrmActivity($message);
            } catch (\Throwable $e) {
                $message->update([
                    'delivery_status' => DeliveryStatus::Failed->value,
                    'delivery_error' => Str::limit($e->getMessage(), 500),
                ]);
                throw $e;
            }

            return $message->fresh(['attachments']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function saveDraft(int $organizationId, array $data, array $files = [], ?Message $existing = null): Message
    {
        return DB::transaction(function () use ($organizationId, $data, $files, $existing) {
            $payload = [
                'organization_id' => $organizationId,
                'folder' => 'draft',
                'direction' => 'outbound',
                'to' => $data['to'] ?? null,
                'cc' => $data['cc'] ?? null,
                'bcc' => $data['bcc'] ?? null,
                'subject' => $data['subject'] ?? null,
                'body_html' => $this->sanitizer->sanitize($data['body_html'] ?? null),
                'body_text' => $data['body_text'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'created_by' => $data['created_by'] ?? null,
                'delivery_status' => null,
            ];

            if ($existing) {
                $existing->update($payload);
                $message = $existing;
            } else {
                $payload['message_id'] = 'draft-'.Str::uuid();
                $message = Message::create($payload);
            }

            foreach ($files as $file) {
                $this->attachments->storeUpload($message, $file);
            }

            return $message->fresh(['attachments']);
        });
    }

    /** @return list<string> */
    public function normalizeRecipients(string $raw): array
    {
        $parts = preg_split('/[,;\n]+/', $raw) ?: [];
        $emails = [];
        foreach ($parts as $part) {
            $email = strtolower(trim($part));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[$email] = $email;
            }
        }

        return array_values($emails);
    }

    private function logCrmActivity(Message $message): void
    {
        if ($message->lead_id) {
            $lead = Lead::find($message->lead_id);
            $lead?->logActivity('email_sent', 'Email sent: '.$message->subject, [
                'em_message_id' => $message->id,
            ]);
        }

        if ($message->customer_id) {
            $customer = Customer::find($message->customer_id);
            $customer?->activities()->create([
                'organization_id' => $message->organization_id,
                'admin_id' => $message->created_by,
                'type' => 'email',
                'subject' => $message->subject,
                'description' => 'Email sent via Email Marketing (message #'.$message->id.')',
                'activity_date' => now(),
                'status' => 'completed',
            ]);
        }
    }
}
