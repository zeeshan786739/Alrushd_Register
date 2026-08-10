<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Models\Organization;
use App\Models\PlatformActivityLog;
use App\Models\SaasSubscription;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSchools = Organization::count();
        $activeSchools = Organization::whereIn('status', ['active', 'trial'])->count();
        $trialSchools = Organization::where('status', 'trial')->count();
        $suspendedSchools = Organization::whereIn('status', ['suspended', 'past_due', 'inactive', 'cancelled'])->count();

        $currentSubscriptions = SaasSubscription::current()->with('plan')->get();

        $mrr = $currentSubscriptions
            ->filter(fn ($sub) => $sub->plan && $sub->status->value !== 'complimentary')
            ->sum(function ($sub) {
                $price = (float) $sub->plan->price;

                return $sub->plan->billing_interval === 'year' ? $price / 12 : $price;
            });

        $openDemoRequests = DemoRequest::whereIn('status', ['new', 'contacted', 'demo_scheduled'])->count();

        return view('platform.dashboard', [
            'totalSchools' => $totalSchools,
            'activeSchools' => $activeSchools,
            'trialSchools' => $trialSchools,
            'suspendedSchools' => $suspendedSchools,
            'mrr' => $mrr,
            'paidSubscriptions' => $currentSubscriptions->filter(fn ($sub) => $sub->status->value !== 'complimentary')->count(),
            'openDemoRequests' => $openDemoRequests,
            'recentSchools' => Organization::with('currentSubscription.plan')->latest()->take(6)->get(),
            'recentDemoRequests' => DemoRequest::latest()->take(6)->get(),
            'recentActivity' => PlatformActivityLog::with(['admin', 'organization'])->latest('created_at')->take(10)->get(),
        ]);
    }
}
