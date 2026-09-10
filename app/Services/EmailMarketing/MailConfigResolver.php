<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use App\Services\EmailMarketing\Delivery\EmailDeliveryService;
use Illuminate\Support\Facades\Config;

class MailConfigResolver
{
    public function forOrganization(int $organizationId): ?MailboxSetting
    {
        return MailboxSetting::query()->where('organization_id', $organizationId)->first();
    }

    public function sendGridConfigured(?MailboxSetting $settings = null): bool
    {
        return filled($settings?->sendgrid_api_key) || filled(config('sendgrid.api_key'));
    }

    public function canSend(?MailboxSetting $settings): bool
    {
        if (! $settings || ! $settings->is_enabled || ! filled($settings->from_email)) {
            return false;
        }

        // SendGrid (global key) or classic per-org SMTP.
        return $this->sendGridConfigured($settings) || $settings->isSmtpConfigured();
    }

    public function applyRuntimeConfig(MailboxSetting $settings): void
    {
        if (! $settings->isSmtpConfigured()) {
            Config::set('mail.from', [
                'address' => $settings->from_email,
                'name' => $settings->from_name ?: $settings->from_email,
            ]);

            return;
        }

        $this->applyRuntimeConfigForDelivery($settings);
    }

    /** Apply org SMTP for a real outbound send (ignores MAIL_MAILER=log). */
    public function applyRuntimeConfigForDelivery(MailboxSetting $settings): void
    {
        Config::set('mail.from', [
            'address' => $settings->from_email,
            'name' => $settings->from_name ?: $settings->from_email,
        ]);

        if (app()->environment('testing') || ! $settings->isSmtpConfigured()) {
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $settings->smtp_host,
            'port' => $settings->smtp_port ?: 587,
            'encryption' => $settings->smtp_encryption ?: 'tls',
            'username' => $settings->smtp_username,
            'password' => $settings->smtp_password,
            'timeout' => 30,
        ]);
    }

    public function resolveOrFail(int $organizationId): MailboxSetting
    {
        $settings = $this->forOrganization($organizationId);

        if (! $this->canSend($settings)) {
            if (! $settings) {
                throw new \RuntimeException('Mailbox settings are missing for this organization. Open Email Marketing → Mailbox settings and save your sender.');
            }

            if (! $settings->is_enabled) {
                throw new \RuntimeException('Outbound mail is disabled for this organization. Enable the mailbox in Email Marketing → Mailbox settings.');
            }

            if (! filled($settings->from_email)) {
                throw new \RuntimeException('From email is missing. Set a verified sender address in Email Marketing → Mailbox settings.');
            }

            throw new \RuntimeException(
                'No delivery provider is configured. Add a SendGrid API key (organization or platform) or complete SMTP settings in Email Marketing → Mailbox settings.'
            );
        }

        return $settings;
    }

    public function providerStatusLabel(MailboxSetting $settings): string
    {
        if ($this->sendGridConfigured($settings)) {
            return filled($settings->from_email) && $settings->is_enabled
                ? 'SendGrid configured (org sender ready)'
                : 'SendGrid API configured — enable mailbox and set From email';
        }

        if ($settings->isSmtpConfigured()) {
            return 'Using organization SMTP';
        }

        return 'No delivery provider configured';
    }
}
