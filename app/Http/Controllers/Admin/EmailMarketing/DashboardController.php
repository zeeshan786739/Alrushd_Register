<?php

namespace App\Http\Controllers\Admin\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Models\EmailMarketing\Campaign;
use App\Support\EmailMarketingDashboard;
use App\Support\OrganizationContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth('admin')->user();
            if (! $user || ! $user->canany(['view inbox', 'view campaigns', 'view templates', 'manage mailbox settings'])) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        $organizationId = OrganizationContext::idOrFail();
        $stats = EmailMarketingDashboard::stats($organizationId);
        $attention = EmailMarketingDashboard::attention($organizationId);

        $recentCampaigns = Campaign::forCurrentOrganization()
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('admin.email-marketing.dashboard', compact('stats', 'attention', 'recentCampaigns'));
    }
}
