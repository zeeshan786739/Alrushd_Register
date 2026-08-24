<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Organization;
use App\Services\Tenant\TenantStripeService;

class AccountHubHelper
{
    /** @return array<string, mixed> */
    public static function stats(?Organization $organization = null): array
    {
        $organization ??= auth('admin')->user()?->organization;
        $stripe = TenantStripeService::forOrganization($organization?->id);
        $subscription = $organization?->currentSubscription()->with('plan')->first();

        return [
            'payments_configured' => $stripe->isConfigured(),
            'payments_enabled' => $stripe->onlinePaymentsEnabled(),
            'payments_test_mode' => $stripe->testMode(),
            'plan_name' => $subscription?->plan?->name,
            'subscription_status' => $subscription?->status?->label(),
            'organization_status' => $organization?->status?->label(),
        ];
    }

    public static function initials(Admin $admin): string
    {
        $parts = preg_split('/\s+/', trim((string) $admin->name)) ?: [];
        $initials = collect($parts)->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

        return $initials !== '' ? $initials : '?';
    }
}
