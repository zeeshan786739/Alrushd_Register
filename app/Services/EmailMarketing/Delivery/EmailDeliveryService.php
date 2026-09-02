<?php

namespace App\Services\EmailMarketing\Delivery;

use App\Models\EmailMarketing\MailboxSetting;
use App\Services\EmailMarketing\MailConfigResolver;

class EmailDeliveryService
{
    public function __construct(
        private MailConfigResolver $mailConfig,
        private SendGridMailProvider $sendGrid,
        private LaravelMailProvider $laravel,
    ) {
    }

    public function sendGridConfigured(?MailboxSetting $settings = null): bool
    {
        return $this->sendGrid->isConfigured($settings?->sendgrid_api_key);
    }

    public function activeProviderName(MailboxSetting $settings): string
    {
        if ($this->shouldUseLaravelBridge()) {
            return $this->laravel->name();
        }

        if ($this->preferSendGrid($settings)) {
            return $this->sendGrid->name();
        }

        return $this->laravel->name();
    }

    public function send(OutboundEmail $email, MailboxSetting $settings): DeliveryResult
    {
        if ($this->shouldUseLaravelBridge()) {
            $this->mailConfig->applyRuntimeConfig($settings);

            return $this->laravel->send($email);
        }

        if ($this->preferSendGrid($settings)) {
            return $this->sendGrid->send($email, $settings->sendgrid_api_key);
        }

        $this->mailConfig->applyRuntimeConfig($settings);

        return $this->laravel->send($email);
    }

    private function preferSendGrid(MailboxSetting $settings): bool
    {
        if (! config('sendgrid.prefer_sendgrid', true)) {
            return false;
        }

        if (! $this->sendGrid->isConfigured($settings->sendgrid_api_key)) {
            return false;
        }

        // Org must still declare a verified-looking from identity.
        return filled($settings->from_email);
    }

    private function shouldUseLaravelBridge(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        return in_array(config('mail.default'), ['array', 'log', 'failover'], true);
    }
}
