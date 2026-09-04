<?php

namespace App\Services\EmailMarketing;

use App\Enums\EmailMarketing\DeliveryStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\SenderMailbox;
use App\Services\EmailMarketing\Delivery\EmailDeliveryService;
use App\Services\EmailMarketing\Delivery\OutboundEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComposeService
{
    public function __construct(
        private MailConfigResolver $mailConfig,
        private HtmlSanitizer $sanitizer,
        private AttachmentService $attachments,
        private EmailDeliveryService $delivery,
    ) {
    }

    /**
     * @param  array{
     *   to:string,cc?:?string,bcc?:?string,subject:string,body_html?:?string,body_text?:?string,
     *   lead_id?:?int,customer_id?:?int,quotation_id?:?int,invoice_id?:?int,
     *   parent_id?:?int,thread_id?:?string,created_by?:?int
     * }  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function send(int $organizationId, array $data, array $files = []): Message
    {
        $settings = $this->mailConfig->resolveOrFail($organizationId);
        $sender = $this->resolveSender($organizationId, $data['sender_mailbox_id'] ?? null);

        $to = $this->normalizeRecipients($data['to']);
        if ($to === []) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $html = $this->sanitizer->sanitize($data['body_html'] ?? '');
        $text = $data['body_text'] ?? $this->sanitizer->toPlainText($html);
        $correlationUuid = (string) Str::uuid();
        $threadId = $data['thread_id'] ?? $correlationUuid;

        return DB::transaction(function () use ($organizationId, $data, $files, $settings, $sender, $to, $html, $text, $correlationUuid, $threadId) {
            $message = Message::create([
                'organization_id' => $organizationId,
                'sender_mailbox_id' => $sender?->id,
                'folder' => 'sent',
                'direction' => 'outbound',
                'message_id' => 'local-'.Str::uuid(),
                'correlation_uuid' => $correlationUuid,
                'thread_id' => $threadId,
                'parent_id' => $data['parent_id'] ?? null,
                'from_email' => $sender?->email ?: $settings->from_email,
                'from_name' => $sender?->name ?: $settings->from_name,
                'to' => implode(', ', $to),
                'cc' => $data['cc'] ?? null,
                'bcc' => $data['bcc'] ?? null,
                'subject' => $data['subject'],
                'body_html' => $html,
                'body_text' => $text,
                'delivery_status' => DeliveryStatus::Sending->value,
                'provider_status' => 'pending',
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'quotation_id' => $data['quotation_id'] ?? null,
                'invoice_id' => $data['invoice_id'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);

            foreach ($files as $file) {
                $this->attachments->storeUpload($message, $file);
            }

            $message->load('attachments');

            $attachmentPayload = [];
            foreach ($message->attachments as $attachment) {
                $attachmentPayload[] = [
                    'path' => Storage::disk($attachment->disk)->path($attachment->path),
                    'name' => $attachment->original_name,
                    'mime' => $attachment->mime_type,
                ];
            }

            $replyTo = $this->resolveReplyTo($settings, $threadId, $sender);

            $result = $this->delivery->send(new OutboundEmail(
                fromEmail: (string) ($sender?->email ?: $settings->from_email),
                fromName: $sender?->name ?: $settings->from_name,
                to: $to,
                subject: (string) $message->subject,
                html: $html ?: nl2br(e($text)),
                text: $text,
                cc: $message->parseAddressList($message->cc),
                bcc: $message->parseAddressList($message->bcc),
                replyTo: $replyTo,
                attachments: $attachmentPayload,
                customArgs: [
                    'correlation_uuid' => $correlationUuid,
                ],
                category: 'transactional',
                trackOpens: (bool) ($settings->open_tracking ?? $settings->tracking_enabled),
                trackClicks: (bool) ($settings->click_tracking ?? false),
            ), $settings);

            if (! $result->accepted) {
                throw new \RuntimeException($result->error ?: 'Send failed.');
            }

            $message->update([
                'delivery_status' => DeliveryStatus::Sent->value,
                'sent_at' => now(),
                'delivery_error' => null,
                'provider' => $result->provider,
                'provider_message_id' => $result->providerMessageId,
                'provider_status' => $result->providerStatus ?: 'processed',
            ]);

            $this->logCrmActivity($message);

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
                'sender_mailbox_id' => $data['sender_mailbox_id'] ?? null,
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

    private function resolveReplyTo(object $settings, string $threadId, ?SenderMailbox $sender = null): ?string
    {
        $domain = $settings->inbound_domain ?: config('sendgrid.inbound_domain');
        if ($settings->inbound_enabled && filled($domain)) {
            return 'reply+'.$threadId.'@'.$domain;
        }

        return $sender?->reply_to ?: $settings->reply_to ?: $sender?->email ?: null;
    }

    private function resolveSender(int $organizationId, mixed $senderMailboxId): ?SenderMailbox
    {
        $query = SenderMailbox::query()
            ->where('organization_id', $organizationId)
            ->available();

        if ($senderMailboxId) {
            return $query->whereKey((int) $senderMailboxId)->firstOrFail();
        }

        return $query->orderByDesc('is_default')->orderBy('id')->first();
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
