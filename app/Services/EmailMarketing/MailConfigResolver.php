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

        // Never override the testing/array/log mailers during automated tests.
        if (app()->environment('testing') || in_array(config('mail.default'), ['array', 'log', 'failover'], true)) {
            Config::set('mail.from', [
                'address' => $settings->from_email,
                'name' => $settings->from_name ?: $settings->from_email,
            ]);

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
        Config::set('mail.from', [
            'address' => $settings->from_email,
            'name' => $settings->from_name ?: $settings->from_email,
        ]);
    }

    public function resolveOrFail(int $organizationId): MailboxSetting
    {
        $settings = $this->forOrganization($organizationId);

        if (! $this->canSend($settings)) {
            throw new \RuntimeException(
                $this->sendGridConfigured($settings)
                    ? 'Mailbox is not enabled or From email is missing for this organization.'
                    : 'Mailbox SMTP is not configured for this organization.'
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
