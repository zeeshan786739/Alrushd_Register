<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use App\Services\Platform\StripeBillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(StripeBillingService $billing)
    {
        $admin = auth('admin')->user();
        $organization = $admin->organization;

        abort_unless($organization, 404);

        return view('admin.account.billing.index', [
            'organization' => $organization,
            'subscription' => $organization->currentSubscription()->with('plan')->first(),
            'history' => $organization->subscriptions()->with('plan')->take(10)->get(),
            'plans' => SaasPlan::active()->ordered()->get(),
            'stripeReady' => $billing->isConfigured(),
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

        if (! $plan->isSyncedToStripe() || ! $billing->isConfigured()) {
            return back()->with('error', 'Online payment is not available for this plan yet. Please contact support.');
        }

        try {
            $url = $billing->createCheckoutSession(
                $organization,
                $plan,
                route('saas.billing.success') . '?session_id={CHECKOUT_SESSION_ID}&org=' . $organization->id,
                route('admin.billing.index')
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Could not start checkout: ' . $e->getMessage());
        }

        return redirect()->away($url);
    }
}
