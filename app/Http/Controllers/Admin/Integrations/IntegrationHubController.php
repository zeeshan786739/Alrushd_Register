<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Enums\IntegrationPlatform;
use App\Http\Controllers\Controller;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\MetaLeadSubmission;
use App\Support\OrganizationContext;
use Illuminate\View\View;

class IntegrationHubController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view integrations');
    }

    public function index(): View
    {
        $organizationId = OrganizationContext::idOrFail();

        $connections = IntegrationConnection::query()
            ->where('organization_id', $organizationId)
            ->get()
            ->keyBy(fn (IntegrationConnection $connection) => $connection->platform->value);

        $facebookConnection = $connections->get(IntegrationPlatform::Facebook->value);
        $tiktokConnection = $connections->get(IntegrationPlatform::TikTok->value);
        $recentFacebookLeads = MetaLeadSubmission::query()
            ->where('organization_id', $organizationId)
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'facebook_connected' => $facebookConnection?->isConnected() ?? false,
            'facebook_leads_total' => MetaLeadSubmission::query()
                ->where('organization_id', $organizationId)
                ->count(),
            'facebook_leads_unmapped' => MetaLeadSubmission::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'unmapped')
                ->count(),
        ];

        return view('admin.integrations.hub', compact(
            'facebookConnection',
            'tiktokConnection',
            'recentFacebookLeads',
            'stats'
        ));
    }
}
