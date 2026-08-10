<?php

namespace App\Http\Controllers\Platform;

use App\Enums\Platform\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\SaasSubscription;
use App\Services\Platform\PlatformActivityLogger;
use App\Services\Platform\StripeBillingService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = SaasSubscription::with(['organization', 'plan'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return view('platform.subscriptions.index', [
            'subscriptions' => $query->paginate(20)->withQueryString(),
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
}
