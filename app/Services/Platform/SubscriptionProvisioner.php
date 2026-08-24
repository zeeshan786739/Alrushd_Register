<?php

namespace App\Services\Platform;

use App\Enums\Platform\OrganizationStatus;
use App\Enums\Platform\SubscriptionStatus;
use App\Models\Organization;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use Illuminate\Support\Carbon;

/**
 * Single source of truth for creating and normalizing local (non-Stripe) subscriptions.
 */
class SubscriptionProvisioner
{
    /**
     * @return array{0: SubscriptionStatus, 1: OrganizationStatus, 2: ?Carbon}
     */
    public function resolve(SaasPlan $plan, string $mode = 'auto'): array
    {
        return match ($mode) {
            'complimentary' => [
                SubscriptionStatus::Complimentary,
                OrganizationStatus::Active,
                null,
            ],
            'trial' => [
                SubscriptionStatus::Trialing,
                OrganizationStatus::Trial,
                now()->addDays($plan->trial_days ?: 14),
            ],
            'auto' => $this->resolveAuto($plan),
            default => $this->resolveAuto($plan),
        };
    }

    /**
     * @return array{0: SubscriptionStatus, 1: OrganizationStatus, 2: ?Carbon}
     */
    private function resolveAuto(SaasPlan $plan): array
    {
        if ($plan->isFree()) {
            return [
                SubscriptionStatus::Complimentary,
                OrganizationStatus::Active,
                null,
            ];
        }

        if ($plan->trial_days > 0) {
            return [
                SubscriptionStatus::Trialing,
                OrganizationStatus::Trial,
                now()->addDays($plan->trial_days),
            ];
        }

        return [
            SubscriptionStatus::Active,
            OrganizationStatus::Active,
            null,
        ];
    }

    public function createForOrganization(
        Organization $organization,
        SaasPlan $plan,
        string $mode = 'auto',
    ): SaasSubscription {
        [$subscriptionStatus, $organizationStatus, $trialEndsAt] = $this->resolve($plan, $mode);

        $organization->update([
            'status' => $organizationStatus,
            'is_active' => $organizationStatus->allowsAccess(),
            'trial_ends_at' => $trialEndsAt,
        ]);

        return SaasSubscription::create([
            'organization_id' => $organization->id,
            'saas_plan_id' => $plan->id,
            'status' => $subscriptionStatus,
            'trial_ends_at' => $subscriptionStatus === SubscriptionStatus::Trialing ? $trialEndsAt : null,
        ]);
    }

    public function replacePlan(
        Organization $organization,
        SaasPlan $plan,
        ?SaasSubscription $current,
        string $mode = 'auto',
    ): SaasSubscription {
        if ($current) {
            $current->update([
                'status' => SubscriptionStatus::Canceled,
                'canceled_at' => now(),
                'ends_at' => now(),
            ]);
        }

        return $this->createForOrganization($organization, $plan, $mode);
    }

    /**
     * Align legacy rows where free plans were stored as trialing/active.
     */
    public function normalizeSubscription(SaasSubscription $subscription): bool
    {
        $plan = $subscription->plan;
        $organization = $subscription->organization;

        if (! $plan || ! $organization || ! $subscription->status?->isCurrent()) {
            return false;
        }

        if (! $plan->isFree()) {
            return false;
        }

        if ($subscription->status === SubscriptionStatus::Complimentary && ! $subscription->trial_ends_at) {
            if ($organization->status === OrganizationStatus::Active && ! $organization->trial_ends_at) {
                return false;
            }
        }

        [$subscriptionStatus, $organizationStatus, $trialEndsAt] = $this->resolve($plan, 'auto');

        $subscription->update([
            'status' => $subscriptionStatus,
            'trial_ends_at' => $subscriptionStatus === SubscriptionStatus::Trialing ? $trialEndsAt : null,
        ]);

        $organization->update([
            'status' => $organizationStatus,
            'is_active' => $organizationStatus->allowsAccess(),
            'trial_ends_at' => $trialEndsAt,
        ]);

        return true;
    }
}
