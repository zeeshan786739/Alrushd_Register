<?php

namespace App\Services\EmailMarketing\Delivery;

interface MailProviderInterface
{
    public function name(): string;

    public function isConfigured(): bool;

    public function send(OutboundEmail $email): DeliveryResult;
}
