<?php

namespace App\Services\EmailMarketing\Delivery;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendGridMailProvider implements MailProviderInterface
{
    public function name(): string
    {
        return 'sendgrid';
    }

    public function isConfigured(?string $apiKey = null): bool
    {
        return filled($apiKey ?: config('sendgrid.api_key'));
    }

    public function send(OutboundEmail $email, ?string $apiKey = null): DeliveryResult
    {
        $apiKey = $apiKey ?: (string) config('sendgrid.api_key');

        if (! $this->isConfigured($apiKey)) {
            return DeliveryResult::failed($this->name(), 'SendGrid is not configured.');
        }

        $personalization = [
            'to' => array_map(fn ($addr) => ['email' => $addr], $email->to),
        ];

        if ($email->cc !== []) {
            $personalization['cc'] = array_map(fn ($addr) => ['email' => $addr], $email->cc);
        }
        if ($email->bcc !== []) {
            $personalization['bcc'] = array_map(fn ($addr) => ['email' => $addr], $email->bcc);
        }
        if ($email->customArgs !== []) {
            $personalization['custom_args'] = $email->customArgs;
        }

        $content = [];
        if (filled($email->text)) {
            $content[] = ['type' => 'text/plain', 'value' => $email->text];
        }
        $content[] = [
            'type' => 'text/html',
            'value' => $email->html !== '' ? $email->html : nl2br(e($email->text ?: '')),
        ];

        $payload = [
            'personalizations' => [$personalization],
            'from' => array_filter([
                'email' => $email->fromEmail,
                'name' => $email->fromName,
            ]),
            'subject' => $email->subject,
            'content' => $content,
            'categories' => [Str::limit($email->category, 255, '')],
            'tracking_settings' => [
                'click_tracking' => ['enable' => $email->trackClicks, 'enable_text' => false],
                'open_tracking' => ['enable' => $email->trackOpens],
            ],
        ];

        if ($email->replyTo) {
            $payload['reply_to'] = ['email' => $email->replyTo];
        }

        if ($email->asmGroupId) {
            $payload['asm'] = [
                'group_id' => $email->asmGroupId,
            ];
        }

        $attachments = [];
        foreach ($email->attachments as $file) {
            if (! is_readable($file['path'])) {
                continue;
            }
            $binary = @file_get_contents($file['path']);
            if ($binary === false) {
                continue;
            }
            $attachments[] = [
                'content' => base64_encode($binary),
                'filename' => $file['name'],
                'type' => $file['mime'] ?: 'application/octet-stream',
                'disposition' => 'attachment',
            ];
        }
        if ($attachments !== []) {
            $payload['attachments'] = $attachments;
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->baseUrl(rtrim((string) config('sendgrid.api_base'), '/'))
                ->timeout(30)
                ->post('/v3/mail/send', $payload);

            if ($response->successful() || $response->status() === 202) {
                $providerId = $response->header('X-Message-Id') ?: $response->header('X-Message-ID');

                return DeliveryResult::accepted($this->name(), $providerId ?: null, 'accepted');
            }

            Log::warning('SendGrid mail send rejected', [
                'status' => $response->status(),
                'errors' => $response->json('errors'),
            ]);

            $providerMessage = collect((array) $response->json('errors'))
                ->pluck('message')
                ->filter(fn ($message) => is_string($message) && $message !== '')
                ->first();

            return DeliveryResult::failed(
                $this->name(),
                $providerMessage
                    ? 'SendGrid rejected the message: '.Str::limit($providerMessage, 350)
                    : 'Email provider rejected the message.'
            );
        } catch (\Throwable $e) {
            Log::warning('SendGrid mail send failed', [
                'error' => Str::limit($e->getMessage(), 200),
            ]);

            return DeliveryResult::failed($this->name(), 'Email provider is temporarily unavailable.');
        }
    }
}
