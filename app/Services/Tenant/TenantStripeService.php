<?php

namespace App\Services\Tenant;

use App\Models\OrganizationPaymentSetting;
use App\Models\Setting;
use App\Support\OrganizationContext;

final class TenantStripeService
{
    public function __construct(
        private ?OrganizationPaymentSetting $paymentSettings,
        private ?Setting $legacySettings,
    ) {}

    public static function forOrganization(?int $organizationId): self
    {
        $paymentSettings = $organizationId
            ? OrganizationPaymentSetting::query()->where('organization_id', $organizationId)->first()
            : null;

        return new self($paymentSettings, Setting::first());
    }

    public static function forCurrentOrganization(): self
    {
        return self::forOrganization(OrganizationContext::id());
    }

    public function settings(): ?OrganizationPaymentSetting
    {
        return $this->paymentSettings;
    }

    public function publishableKey(): ?string
    {
        if ($this->paymentSettings?->is_enabled && filled($this->paymentSettings->stripe_publishable_key)) {
            return $this->paymentSettings->stripe_publishable_key;
        }

        return $this->legacySettings?->stripe_key ?: config('services.stripe.key');
    }

    public function secret(): ?string
    {
        if ($this->paymentSettings?->is_enabled && filled($this->paymentSettings->stripe_secret)) {
            return $this->paymentSettings->stripe_secret;
        }

        return $this->legacySettings?->stripe_secret ?: config('services.stripe.secret');
    }

    public function onlinePaymentsEnabled(): bool
    {
        if ($this->paymentSettings?->is_enabled) {
            return $this->isConfigured();
        }

        return (bool) ($this->legacySettings?->payment_method_status ?? true) && $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        return filled($this->publishableKey()) && filled($this->secret());
    }

    public function usesOrganizationSettings(): bool
    {
        return (bool) ($this->paymentSettings?->is_enabled && filled($this->paymentSettings->stripe_secret));
    }

    public function testMode(): bool
    {
        if ($this->paymentSettings?->is_enabled) {
            return (bool) $this->paymentSettings->test_mode;
        }

        $key = (string) $this->publishableKey();

        return str_starts_with($key, 'pk_test_');
    }
}
