<?php

namespace App\Services\EmailMarketing\Delivery;

final class DeliveryResult
{
    public function __construct(
        public readonly bool $accepted,
        public readonly string $provider,
        public readonly ?string $providerMessageId = null,
        public readonly ?string $providerStatus = null,
        public readonly ?string $error = null,
    ) {
    }

    public static function accepted(string $provider, ?string $providerMessageId = null, string $providerStatus = 'processed'): self
    {
        return new self(true, $provider, $providerMessageId, $providerStatus, null);
    }

    public static function failed(string $provider, string $error): self
    {
        return new self(false, $provider, null, 'failed', $error);
    }
}
