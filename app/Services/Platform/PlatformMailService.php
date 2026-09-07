<?php

namespace App\Services\Platform;

use App\Models\PlatformSetting;
use App\Services\EmailMarketing\Delivery\DeliveryResult;
use App\Services\EmailMarketing\Delivery\LaravelMailProvider;
use App\Services\EmailMarketing\Delivery\OutboundEmail;
use App\Services\EmailMarketing\Delivery\SendGridMailProvider;
use Illuminate\Support\Facades\Log;

/**
 * Platform-level transactional mail (demo / trial onboarding).
 * Prefers SendGrid when configured (platform settings → env), else Laravel mail.
 * Falls back to Laravel SMTP if SendGrid rejects the message.
 */
class PlatformMailService
{
    public function __construct(
        private SendGridMailProvider $sendGrid,
        private LaravelMailProvider $laravel,
    ) {
    }

    public function apiKey(): ?string
    {
        return PlatformSetting::get('sendgrid_api_key', config('sendgrid.api_key'));
    }

    public function fromEmail(): string
    {
        return PlatformSetting::get(
            'mail_from_email',
            config('mail.from.address') ?: PlatformSetting::get('support_email', config('saas.support_email'))
        ) ?: 'noreply@enrolliq.com';
    }

    public function fromName(): string
    {
        return PlatformSetting::get(
            'mail_from_name',
            PlatformSetting::get('platform_name', config('saas.name'))
        ) ?: 'Enrolliq';
    }

    public function notifyEmail(): string
    {
        return PlatformSetting::get(
            'notify_email',
            PlatformSetting::get('support_email', config('saas.support_email'))
        ) ?: 'hello@enrolliq.com';
    }

    public function platformName(): string
    {
        return PlatformSetting::get('platform_name', config('saas.name')) ?: 'Enrolliq';
    }

    public function isConfigured(): bool
    {
        return $this->sendGrid->isConfigured($this->apiKey())
            || filled(config('mail.mailers.smtp.host'))
            || in_array(config('mail.default'), ['smtp', 'sendmail', 'mailgun', 'ses', 'postmark', 'resend', 'log', 'array'], true);
    }

    public function sendGridReady(): bool
    {
        return $this->sendGrid->isConfigured($this->apiKey());
    }

    public function providerLabel(): string
    {
        if ($this->sendGridReady()) {
            return 'sendgrid (+ smtp fallback)';
        }

        return 'laravel ('.config('mail.default').')';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendView(string $to, string $subject, string $view, array $data = []): DeliveryResult
    {
        $html = view($view, array_merge([
            'platformName' => $this->platformName(),
            'supportEmail' => PlatformSetting::get('support_email', config('saas.support_email')),
            'loginUrl' => route('admin.login'),
        ], $data))->render();

        return $this->send($to, $subject, $html);
    }

    public function send(string $to, string $subject, string $html, ?string $text = null): DeliveryResult
    {
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return DeliveryResult::failed('none', 'Invalid recipient email.');
        }

        $email = new OutboundEmail(
            fromEmail: $this->fromEmail(),
            fromName: $this->fromName(),
            to: [$to],
            subject: $subject,
            html: $html,
            text: $text,
            replyTo: PlatformSetting::get('support_email', config('saas.support_email')),
            category: 'platform-transactional',
            trackOpens: false,
            trackClicks: false,
        );

        try {
            if (! app()->environment('testing') && $this->sendGridReady()) {
                $result = $this->sendGrid->send($email, $this->apiKey());

                if ($result->accepted) {
                    return $result;
                }

                Log::warning('Platform SendGrid delivery failed; falling back to Laravel mail', [
                    'to' => $to,
                    'subject' => $subject,
                    'error' => $result->error,
                ]);

                $fallback = $this->laravel->send($email);
                if ($fallback->accepted) {
                    return DeliveryResult::accepted(
                        'laravel-fallback',
                        $fallback->providerMessageId,
                        'sent-via-smtp-fallback'
                    );
                }

                return DeliveryResult::failed(
                    'sendgrid+laravel',
                    ($result->error ?: 'SendGrid failed').' | Fallback: '.($fallback->error ?: 'Laravel mail failed')
                );
            }

            return $this->laravel->send($email);
        } catch (\Throwable $e) {
            Log::warning('Platform mail failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return DeliveryResult::failed('none', $e->getMessage());
        }
    }
}
