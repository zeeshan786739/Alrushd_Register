<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use App\Models\Organization;
use Illuminate\Support\Facades\Config;

class MailConfigResolver
{
    public function forOrganization(int $organizationId): ?MailboxSetting
    {
        return MailboxSetting::query()->where('organization_id', $organizationId)->first();
    }

    public function applyRuntimeConfig(MailboxSetting $settings): void
    {
        if (! $settings->isSmtpConfigured()) {
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

        if (! $settings || ! $settings->is_enabled || ! $settings->isSmtpConfigured()) {
            throw new \RuntimeException('Mailbox SMTP is not configured for this organization.');
        }

        return $settings;
    }
}
