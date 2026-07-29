<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\UpdateFacebookFormMappingRequest;
use App\Models\Admin;
use App\Models\Integrations\IntegrationFormMapping;
use App\Models\Integrations\MetaLeadSubmission;
use App\Services\Integrations\Meta\FacebookIntegrationService;
use App\Services\Integrations\Meta\MetaLeadReprocessService;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacebookIntegrationController extends Controller
{
    public function __construct(
        private FacebookIntegrationService $facebookService,
        private MetaLeadReprocessService $reprocessService,
    ) {
        $this->middleware('permission:view integrations')->only(['show', 'testConnection']);
        $this->middleware('permission:manage integrations')->except(['show', 'testConnection']);
    }

    public function show(): View
    {
        $connection = $this->facebookService->connectionForCurrentOrganization()
            ->load(['connectedByAdmin', 'formMappings.assignedAdmin']);

        $pages = $this->facebookService->availablePagesFromSession();
        $pendingPageSelection = $this->facebookService->hasPendingPageSelection();

        $recentSubmissions = MetaLeadSubmission::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->latest()
            ->limit(10)
            ->with(['lead', 'formMapping'])
            ->get();

        $admins = Admin::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->orderBy('name')
            ->get(['id', 'name']);

        $webhookUrl = url('/webhooks/meta/leads');

        $unmappedCount = MetaLeadSubmission::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->where('status', 'unmapped')
            ->count();

        $failedCount = MetaLeadSubmission::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->where('status', 'failed')
            ->count();

        $configOk = filled(config('integrations.meta.app_id'))
            && filled(config('integrations.meta.app_secret'))
            && filled(config('integrations.meta.webhook_verify_token'));

        return view('admin.integrations.facebook.show', compact(
            'connection',
            'pages',
            'pendingPageSelection',
            'recentSubmissions',
            'admins',
            'webhookUrl',
            'unmappedCount',
            'failedCount',
            'configOk'
        ));
    }

    public function connect(): RedirectResponse
    {
        if (! config('integrations.meta.app_id') || ! config('integrations.meta.app_secret')) {
            return redirect()
                ->route('admin.integrations.facebook.show')
                ->with('error', 'Meta App ID and App Secret must be configured in the environment before connecting.');
        }

        return redirect()->away($this->facebookService->beginOAuth());
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('admin.integrations.facebook.show')
                ->with('error', 'Facebook authorization was cancelled or denied.');
        }

        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $this->facebookService->handleOAuthCallback(
            $request->string('code')->toString(),
            $request->string('state')->toString()
        );

        return redirect()
            ->route('admin.integrations.facebook.show')
            ->with('success', 'Facebook authorized. Select the Page that runs your Lead Ads.');
    }

    public function selectPage(Request $request): RedirectResponse
    {
        $request->validate([
            'page_id' => ['required', 'string'],
        ]);

        $this->facebookService->connectPage(
            $request->string('page_id')->toString(),
            $request->user('admin')
        );

        return redirect()
            ->route('admin.integrations.facebook.show')
            ->with('success', 'Facebook Page connected and webhook subscription requested.');
    }

    public function disconnect(): RedirectResponse
    {
        $connection = $this->facebookService->connectionForCurrentOrganization();
        $this->facebookService->disconnect($connection);

        return redirect()
            ->route('admin.integrations.facebook.show')
            ->with('success', 'Facebook disconnected for this organization.');
    }

    public function syncForms(): RedirectResponse
    {
        $connection = $this->facebookService->connectionForCurrentOrganization();
        $forms = $this->facebookService->syncLeadForms($connection);

        return redirect()
            ->route('admin.integrations.facebook.show')
            ->with('success', count($forms).' Facebook Lead Form(s) synced. Configure mappings below.');
    }

    public function updateMapping(UpdateFacebookFormMappingRequest $request, IntegrationFormMapping $mapping): RedirectResponse
    {
        abort_unless($mapping->organization_id === OrganizationContext::idOrFail(), 404);

        $mapping->update($request->validated());

        return redirect()
            ->route('admin.integrations.facebook.show')
            ->with('success', 'Form mapping updated.');
    }

    public function reprocessPending(): RedirectResponse
    {
        $count = $this->reprocessService->reprocessPendingForOrganization(
            OrganizationContext::idOrFail()
        );

        return redirect()
            ->route('admin.integrations.facebook.show')
            ->with('success', $count.' lead submission(s) queued for reprocessing.');
    }

    public function reprocessSubmission(MetaLeadSubmission $submission): RedirectResponse
    {
        abort_unless($submission->organization_id === OrganizationContext::idOrFail(), 404);

        $this->reprocessService->reprocess($submission);

        return redirect()
            ->route('admin.integrations.facebook.show')
            ->with('success', 'Lead submission queued for reprocessing.');
    }

    public function testConnection(): RedirectResponse
    {
        $issues = [];

        if (! config('integrations.meta.app_id')) {
            $issues[] = 'META_APP_ID is missing';
        }
        if (! config('integrations.meta.app_secret')) {
            $issues[] = 'META_APP_SECRET is missing';
        }
        if (! config('integrations.meta.webhook_verify_token')) {
            $issues[] = 'META_WEBHOOK_VERIFY_TOKEN is missing';
        }

        $connection = $this->facebookService->connectionForCurrentOrganization();

        if (! $connection->isConnected()) {
            $issues[] = 'Facebook Page is not connected for this school';
        } elseif (! $connection->webhook_subscribed_at) {
            $issues[] = 'Webhook subscription is not confirmed';
        }

        if ($issues !== []) {
            return redirect()
                ->route('admin.integrations.facebook.show')
                ->with('error', 'Connection check failed: '.implode('; ', $issues));
        }

        return redirect()
            ->route('admin.integrations.facebook.show')
            ->with('success', 'Connection check passed. Send a test lead from Meta Lead Ads Testing Tool to confirm end-to-end delivery.');
    }
}
