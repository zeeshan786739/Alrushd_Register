<?php

namespace App\Http\Controllers\Platform;

use App\Enums\Platform\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use App\Services\Platform\PlatformActivityLogger;
use App\Services\Platform\StripeBillingService;
use App\Services\Platform\SubscriptionProvisioner;
use App\Support\PlanEntitlements;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = SaasSubscription::with(['organization', 'plan'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($planId = $request->input('plan')) {
            $query->where('saas_plan_id', $planId);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('organization', fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $currentQuery = SaasSubscription::query()->current();

        return view('platform.subscriptions.index', [
            'subscriptions' => $query->paginate(20)->withQueryString(),
            'plans' => SaasPlan::ordered()->get(),
            'stats' => [
                'current' => (clone $currentQuery)->count(),
                'complimentary' => (clone $currentQuery)->where('status', 'complimentary')->count(),
                'trialing' => (clone $currentQuery)->where('status', 'trialing')->count(),
                'paid' => (clone $currentQuery)->whereHas('plan', fn ($q) => $q->where('price', '>', 0))->count(),
            ],
            'moduleCatalog' => PlanEntitlements::moduleCatalog(),
        ]);
    }

    public function cancel(SaasSubscription $subscription, StripeBillingService $billing)
    {
        $billing->cancelSubscription($subscription);

        $organization = $subscription->organization;

        if ($organization && ! $organization->subscriptions()->current()->exists()) {
            $organization->transitionTo(OrganizationStatus::Cancelled);
        }

        PlatformActivityLogger::log(
            'subscription.canceled',
            "Subscription #{$subscription->id} canceled for \"{$organization?->name}\"",
            $organization
        );

        return back()->with('success', 'Subscription canceled.');
    }

    public function normalize(SubscriptionProvisioner $provisioner)
    {
        $updated = 0;

        SaasSubscription::query()
            ->with(['plan', 'organization'])
            ->current()
            ->chunkById(50, function ($subscriptions) use ($provisioner, &$updated) {
                foreach ($subscriptions as $subscription) {
                    if ($provisioner->normalizeSubscription($subscription)) {
                        $updated++;
                    }
                }
            });

        PlatformActivityLogger::log('subscriptions.normalized', "Normalized {$updated} free-plan subscription(s)");

        return back()->with('success', "Normalized {$updated} free-plan subscription(s) to complimentary.");
    }
}
