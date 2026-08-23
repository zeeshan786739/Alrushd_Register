<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\MailboxSetting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SendGrid Inbound Parse → local inbox messages with safe org/thread resolution.
 */
class SendGridInboundService
{
    public function __construct(
        private HtmlSanitizer $sanitizer,
        private AttachmentService $attachments,
    ) {
    }

    public function ingest(Request $request): ?Message
    {
        $toRaw = (string) $request->input('to', '');
        $fromRaw = (string) $request->input('from', '');
        $ccRaw = $request->input('cc');
        $subject = (string) $request->input('subject', '(no subject)');
        $text = (string) $request->input('text', '');
        $html = $this->sanitizer->sanitize((string) $request->input('html', ''));
        $headers = (string) $request->input('headers', '');

        $organizationId = $this->resolveOrganizationId($toRaw, $request->input('envelope'));
        if (! $organizationId) {
            Log::info('SendGrid inbound ignored — organization could not be resolved.');

            return null;
        }

        $providerMessageId = $this->extractHeaderValue($headers, 'Message-ID')
            ?: $this->extractHeaderValue($headers, 'Message-Id');

        if ($providerMessageId) {
            $existing = Message::query()
                ->where('organization_id', $organizationId)
                ->where('message_id', $providerMessageId)
                ->first();
            if ($existing) {
                return $existing->loadMissing('attachments');
            }
        }

        $from = $this->extractEmail($fromRaw) ?? 'unknown@invalid.local';
        $fromName = $this->extractName($fromRaw);
        $threadToken = $this->resolveThreadToken($toRaw);

        $parent = null;
        if ($threadToken) {
            $parent = Message::query()
                ->where('organization_id', $organizationId)
                ->where(function ($q) use ($threadToken) {
                    $q->where('thread_id', $threadToken)
                        ->orWhere('correlation_uuid', $threadToken);
                })
                ->orderByDesc('id')
                ->first();
        }

        $threadId = $parent?->thread_id ?: $threadToken;

        $message = Message::create([
            'organization_id' => $organizationId,
            'folder' => 'inbox',
            'direction' => 'inbound',
            'message_id' => $providerMessageId ?: ('inbound-'.Str::uuid()),
            'correlation_uuid' => (string) Str::uuid(),
            'thread_id' => $threadId,
            'parent_id' => $parent?->id,
            'from_email' => $from,
            'from_name' => $fromName,
            'to' => $toRaw,
            'cc' => is_string($ccRaw) ? $ccRaw : null,
            'subject' => $subject,
            'body_html' => $html,
            'body_text' => $text !== '' ? $text : $this->sanitizer->toPlainText($html),
            'is_read' => false,
            'provider' => 'sendgrid',
            'provider_status' => 'received',
            'lead_id' => $parent?->lead_id,
            'customer_id' => $parent?->customer_id,
            'quotation_id' => $parent?->quotation_id,
            'invoice_id' => $parent?->invoice_id,
            'received_at' => now(),
        ]);

        foreach ($this->collectUploadedFiles($request) as $file) {
            $this->attachments->tryStoreUpload($message, $file);
        }

        return $message->fresh(['attachments']);
    }

    private function resolveOrganizationId(string $toRaw, mixed $envelope): ?int
    {
        $candidates = $this->extractEmails($toRaw);
        if (is_string($envelope)) {
            $decoded = json_decode($envelope, true);
            if (is_array($decoded) && isset($decoded['to']) && is_array($decoded['to'])) {
                foreach ($decoded['to'] as $addr) {
                    $email = $this->extractEmail((string) $addr);
                    if ($email) {
                        $candidates[] = $email;
                    }
                }
            }
        }

        $candidates = array_values(array_unique($candidates));

        foreach ($candidates as $email) {
            $domain = Str::after($email, '@');
            $local = Str::before($email, '@');

            $settings = MailboxSetting::query()
                ->where('inbound_enabled', true)
                ->where(function ($q) use ($domain, $email) {
                    $q->where('inbound_domain', $domain)
                        ->orWhere('from_email', $email);
                })
                ->first();

            if ($settings) {
                return (int) $settings->organization_id;
            }

            // Opaque reply+{uuid}@domain — resolve only via local message ownership.
            if (str_starts_with($local, 'reply+')) {
                $token = substr($local, strlen('reply+'));
                if (Str::isUuid($token)) {
                    $message = Message::query()
                        ->where(function ($q) use ($token) {
                            $q->where('thread_id', $token)
                                ->orWhere('correlation_uuid', $token);
                        })
                        ->first();
                    if ($message) {
                        return (int) $message->organization_id;
                    }
                }
            }
        }

        return null;
    }

    private function resolveThreadToken(string $toRaw): ?string
    {
        foreach ($this->extractEmails($toRaw) as $email) {
            $local = Str::before($email, '@');
            if (str_starts_with($local, 'reply+')) {
                $token = substr($local, strlen('reply+'));
                if (Str::isUuid($token)) {
                    return $token;
                }
            }
        }

        return null;
    }

    private function extractHeaderValue(string $headers, string $name): ?string
    {
        if ($headers === '') {
            return null;
        }

        if (preg_match('/^'.preg_quote($name, '/').':\s*(.+)$/mi', $headers, $m)) {
            return trim($m[1], " \t\"<>");
        }

        return null;
    }

    /** @return list<UploadedFile> */
    private function collectUploadedFiles(Request $request): array
    {
        $files = [];
        foreach ($request->allFiles() as $file) {
            if (is_array($file)) {
                foreach ($file as $uploaded) {
                    if ($uploaded instanceof UploadedFile) {
                        $files[] = $uploaded;
                    }
                }
            } elseif ($file instanceof UploadedFile) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /** @return list<string> */
    private function extractEmails(string $raw): array
    {
        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $raw, $matches);

        return array_values(array_unique(array_map('strtolower', $matches[0] ?? [])));
    }

    private function extractEmail(string $raw): ?string
    {
        $emails = $this->extractEmails($raw);

        return $emails[0] ?? null;
    }

    private function extractName(string $raw): ?string
    {
        if (preg_match('/^"?([^"<]+)"?\s*</', $raw, $m)) {
            return trim($m[1]);
        }

        return null;
    }
}
