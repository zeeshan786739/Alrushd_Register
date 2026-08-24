<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use App\Support\PlanEntitlements;
use App\Services\Platform\StripeBillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(StripeBillingService $billing)
    {
        $admin = auth('admin')->user();
        $organization = $admin->organization;

        abort_unless($organization, 404);

        $portalUrl = $billing->createBillingPortalSession(
            $organization,
            route('admin.billing.index')
        );

        return view('admin.account.billing.index', [
            'organization' => $organization,
            'subscription' => $organization->currentSubscription()->with('plan')->first(),
            'history' => $organization->subscriptions()->with('plan')->take(10)->get(),
            'plans' => SaasPlan::active()->ordered()->get(),
            'stripeReady' => $billing->isConfigured(),
            'portalUrl' => $portalUrl,
            'moduleCatalog' => PlanEntitlements::moduleCatalog(),
            'enabledModules' => PlanEntitlements::enabledModules($organization),
        ]);
    }

    public function checkout(Request $request, StripeBillingService $billing)
    {
        $organization = auth('admin')->user()->organization;
        abort_unless($organization, 404);

        $data = $request->validate([
            'plan' => ['required', 'exists:saas_plans,slug'],
        ]);

        $plan = SaasPlan::where('slug', $data['plan'])->where('is_active', true)->firstOrFail();
        $current = $organization->currentSubscription()->first();

        if ($current?->saas_plan_id === $plan->id && $current->status?->isCurrent()) {
            return back()->with('success', 'You are already on the '.$plan->name.' plan.');
        }

        // Paid plan with active Stripe subscription — swap in place.
        if ($billing->isConfigured() && $plan->isSyncedToStripe() && $current?->stripe_subscription_id) {
            try {
                $billing->switchPlan($organization, $plan);

                return redirect()->route('admin.billing.index')
                    ->with('success', 'Your plan has been switched to '.$plan->name.'.');
            } catch (\Throwable $e) {
                report($e);

                return back()->with('error', 'Could not switch plan: '.$e->getMessage());
            }
        }

        // Free / trial / complimentary — update locally without Stripe Checkout.
        if (! $plan->isSyncedToStripe() || ! $billing->isConfigured() || (float) $plan->price <= 0) {
            try {
                $billing->switchPlan($organization, $plan);

                return redirect()->route('admin.billing.index')
                    ->with('success', 'Your plan has been switched to '.$plan->name.'.');
            } catch (\Throwable $e) {
                report($e);

                return back()->with('error', 'Could not switch plan: '.$e->getMessage());
            }
        }

        // New paid subscription via Checkout (no existing Stripe sub).
        try {
            $url = $billing->createCheckoutSession(
                $organization,
                $plan,
                route('admin.billing.index').'?upgraded=1',
                route('admin.billing.index')
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Could not start checkout: '.$e->getMessage());
        }

        return redirect()->away($url);
    }
}
