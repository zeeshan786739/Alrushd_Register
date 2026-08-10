<?php

namespace App\Services\Platform;

use App\Enums\Platform\OrganizationStatus;
use App\Enums\Platform\SubscriptionStatus;
use App\Models\Organization;
use App\Models\PlatformSetting;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use Illuminate\Support\Carbon;
use Stripe\StripeClient;

/**
 * Billing for the SaaS platform itself (charging schools). Uses the platform
 * Stripe account — completely separate from per-tenant Stripe keys that
 * schools use to collect fees from parents.
 */
class StripeBillingService
{
    public function publishableKey(): ?string
    {
        return PlatformSetting::get('stripe_key', config('saas.stripe.key'));
    }

    public function secret(): ?string
    {
        return PlatformSetting::get('stripe_secret', config('saas.stripe.secret'));
    }

    public function webhookSecret(): ?string
    {
        return PlatformSetting::get('stripe_webhook_secret', config('saas.stripe.webhook_secret'));
    }

    public function isConfigured(): bool
    {
        return filled($this->secret());
    }

    public function client(): StripeClient
    {
        return new StripeClient($this->secret());
    }

    /**
     * Push a plan to Stripe: create the product once, and (re)create the price
     * whenever amount/currency/interval changed (Stripe prices are immutable).
     */
    public function syncPlan(SaasPlan $plan): SaasPlan
    {
        $stripe = $this->client();

        if (! $plan->stripe_product_id) {
            $product = $stripe->products->create([
                'name' => config('saas.name') . ' — ' . $plan->name,
                'description' => $plan->tagline ?: null,
                'metadata' => ['saas_plan_id' => $plan->id],
            ]);
            $plan->stripe_product_id = $product->id;
        } else {
            $stripe->products->update($plan->stripe_product_id, [
                'name' => config('saas.name') . ' — ' . $plan->name,
                'description' => $plan->tagline ?: null,
            ]);
        }

        $needsNewPrice = true;

        if ($plan->stripe_price_id) {
            try {
                $existing = $stripe->prices->retrieve($plan->stripe_price_id);
                $needsNewPrice = $existing->unit_amount !== (int) round($plan->price * 100)
                    || strtolower($existing->currency) !== strtolower($plan->currency)
                    || ($existing->recurring->interval ?? null) !== $plan->billing_interval;
            } catch (\Throwable) {
                $needsNewPrice = true;
            }
        }

        if ($needsNewPrice) {
            $price = $stripe->prices->create([
                'product' => $plan->stripe_product_id,
                'unit_amount' => (int) round($plan->price * 100),
                'currency' => strtolower($plan->currency),
                'recurring' => ['interval' => $plan->billing_interval],
                'metadata' => ['saas_plan_id' => $plan->id],
            ]);
            $plan->stripe_price_id = $price->id;
        }

        $plan->save();

        return $plan;
    }

    public function ensureCustomer(Organization $organization): string
    {
        if ($organization->stripe_customer_id) {
            return $organization->stripe_customer_id;
        }

        $customer = $this->client()->customers->create([
            'name' => $organization->name,
            'email' => $organization->email,
            'metadata' => ['organization_id' => $organization->id],
        ]);

        $organization->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    public function createCheckoutSession(Organization $organization, SaasPlan $plan, string $successUrl, string $cancelUrl): string
    {
        $session = $this->client()->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $this->ensureCustomer($organization),
            'line_items' => [[
                'price' => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'subscription_data' => [
                'metadata' => [
                    'organization_id' => $organization->id,
                    'saas_plan_id' => $plan->id,
                ],
            ],
            'metadata' => [
                'organization_id' => $organization->id,
                'saas_plan_id' => $plan->id,
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return $session->url;
    }

    public function cancelSubscription(SaasSubscription $subscription): void
    {
        if ($subscription->stripe_subscription_id && $this->isConfigured()) {
            try {
                $this->client()->subscriptions->cancel($subscription->stripe_subscription_id);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $subscription->update([
            'status' => SubscriptionStatus::Canceled,
            'canceled_at' => now(),
            'ends_at' => $subscription->current_period_end ?? now(),
        ]);
    }

    /** Apply a Stripe webhook event to local subscription/organization state. */
    public function handleEvent(\Stripe\Event $event): void
    {
        $object = $event->data->object;

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->applyStripeSubscription(
                    (string) $object->subscription,
                    (int) ($object->metadata->organization_id ?? 0),
                    (int) ($object->metadata->saas_plan_id ?? 0)
                );
                break;

            case 'customer.subscription.updated':
            case 'customer.subscription.created':
                $this->applyStripeSubscription(
                    (string) $object->id,
                    (int) ($object->metadata->organization_id ?? 0),
                    (int) ($object->metadata->saas_plan_id ?? 0)
                );
                break;

            case 'customer.subscription.deleted':
                $local = SaasSubscription::where('stripe_subscription_id', $object->id)->first();
                if ($local) {
                    $local->update([
                        'status' => SubscriptionStatus::Canceled,
                        'canceled_at' => now(),
                        'ends_at' => now(),
                    ]);
                    $local->organization?->transitionTo(OrganizationStatus::Cancelled);
                }
                break;

            case 'invoice.payment_failed':
                $subId = $object->subscription ?? ($object->parent->subscription_details->subscription ?? null);
                $local = $subId ? SaasSubscription::where('stripe_subscription_id', $subId)->first() : null;
                if ($local) {
                    $local->update(['status' => SubscriptionStatus::PastDue]);
                    $local->organization?->transitionTo(OrganizationStatus::PastDue);
                }
                break;
        }
    }

    private function applyStripeSubscription(string $stripeSubscriptionId, int $organizationId, int $planId): void
    {
        if (! $stripeSubscriptionId) {
            return;
        }

        $stripeSub = $this->client()->subscriptions->retrieve($stripeSubscriptionId);

        $organizationId = $organizationId ?: (int) ($stripeSub->metadata->organization_id ?? 0);
        $planId = $planId ?: (int) ($stripeSub->metadata->saas_plan_id ?? 0);

        $organization = Organization::find($organizationId)
            ?? Organization::where('stripe_customer_id', (string) $stripeSub->customer)->first();

        if (! $organization) {
            return;
        }

        // Newer Stripe API versions expose period bounds on the subscription item.
        $item = $stripeSub->items->data[0] ?? null;
        $periodStart = $item->current_period_start ?? ($stripeSub->current_period_start ?? null);
        $periodEnd = $item->current_period_end ?? ($stripeSub->current_period_end ?? null);

        $status = match ($stripeSub->status) {
            'trialing' => SubscriptionStatus::Trialing,
            'active' => SubscriptionStatus::Active,
            'past_due', 'unpaid' => SubscriptionStatus::PastDue,
            'canceled' => SubscriptionStatus::Canceled,
            default => SubscriptionStatus::Incomplete,
        };

        SaasSubscription::updateOrCreate(
            ['stripe_subscription_id' => $stripeSub->id],
            [
                'organization_id' => $organization->id,
                'saas_plan_id' => $planId ?: null,
                'status' => $status,
                'stripe_customer_id' => (string) $stripeSub->customer,
                'current_period_start' => $periodStart ? Carbon::createFromTimestamp($periodStart) : null,
                'current_period_end' => $periodEnd ? Carbon::createFromTimestamp($periodEnd) : null,
                'trial_ends_at' => $stripeSub->trial_end ? Carbon::createFromTimestamp($stripeSub->trial_end) : null,
                'canceled_at' => $stripeSub->canceled_at ? Carbon::createFromTimestamp($stripeSub->canceled_at) : null,
            ]
        );

        $organization->transitionTo(match ($status) {
            SubscriptionStatus::Trialing => OrganizationStatus::Trial,
            SubscriptionStatus::Active => OrganizationStatus::Active,
            SubscriptionStatus::PastDue => OrganizationStatus::PastDue,
            SubscriptionStatus::Canceled => OrganizationStatus::Cancelled,
            default => OrganizationStatus::Inactive,
        });
    }
}
