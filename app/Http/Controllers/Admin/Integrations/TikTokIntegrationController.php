<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Enums\LeadPriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\UpdateTikTokFormMappingRequest;
use App\Models\Admin;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\TikTokFormMapping;
use App\Models\Integrations\TikTokLeadSubmission;
use App\Services\Integrations\TikTok\TikTokApiException;
use App\Services\Integrations\TikTok\TikTokCrmFields;
use App\Services\Integrations\TikTok\TikTokIntegrationService;
use App\Services\Integrations\TikTok\TikTokOAuthException;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TikTokIntegrationController extends Controller
{
    public function __construct(
        private TikTokIntegrationService $tikTokService,
    ) {
        $this->middleware('permission:view integrations')->only(['show']);
        $this->middleware('permission:manage integrations')->only([
            'connect',
            'callback',
            'selectAdvertiser',
            'syncForms',
            'configure',
            'updateMapping',
            'registerWebhook',
            'reprocessPending',
            'reprocessSubmission',
        ]);
    }

    public function show(): View
    {
        OrganizationContext::idOrFail();

        $connection = $this->tikTokService->connectionForCurrentOrganization();
        $connected = $connection?->isConnected() ?? false;
        $configOk = $this->tikTokService->credentialsConfigured();
        $pendingAdvertiserSelection = $this->tikTokService->hasPendingAdvertiserSelection();
        $pendingAdvertisers = $this->tikTokService->pendingAdvertisersFromSession();
        $connectedAt = $this->connectedAtDisplay($connection);

        $formMappings = $connected
            ? $this->tikTokService->formMappingsForCurrentOrganization()
            : collect();
        $formsSynced = $connected && $this->tikTokService->hasSyncedForms($connection);
        $hasConfiguredMapping = $connected && $this->tikTokService->hasConfiguredFormMapping();
        $formsLastSyncedAt = $this->tikTokService->formsLastSyncedAt($connection);
        $webhookSubscribed = $connected && $this->tikTokService->hasLeadWebhookSubscription($connection);
        $recentSubmissions = $connected
            ? $this->tikTokService->recentSubmissionsForCurrentOrganization()
            : collect();
        $eligibleReprocessCount = $connected ? $this->tikTokService->eligibleReprocessCount() : 0;
        $webhookUrl = $this->tikTokService->webhookCallbackUrl();
        $deliveryReady = $webhookSubscribed;

        $setupStatus = [
            'application' => $configOk ? 'Configured' : 'Not configured',
            'account' => $connected ? 'Connected' : ($pendingAdvertiserSelection ? 'Select account' : 'Not connected'),
            'advertiser' => ($connected && filled($connection?->external_account_name))
                ? $connection->external_account_name
                : ($pendingAdvertiserSelection ? 'Choose an account' : 'Waiting for connection'),
            'forms' => $formsSynced
                ? ($formMappings->isEmpty() ? 'Synced (none found)' : $formMappings->count().' form(s)')
                : ($connected ? 'Waiting for sync' : 'Waiting for connection'),
            'delivery' => $webhookSubscribed
                ? 'Subscribed'
                : ($connected ? 'Setup required' : 'Waiting for connection'),
        ];

        $setupSteps = [
            [
                'n' => '1',
                'title' => 'Connect TikTok',
                'text' => 'Authorize this school’s TikTok Ads account.',
                'state' => ($connected || $pendingAdvertiserSelection) ? 'complete' : 'current',
            ],
            [
                'n' => '2',
                'title' => 'Select Ads Account',
                'text' => 'Choose the advertiser account that runs your ads.',
                'state' => $connected ? 'complete' : ($pendingAdvertiserSelection ? 'current' : 'pending'),
            ],
            [
                'n' => '3',
                'title' => 'Sync Lead Forms',
                'text' => 'Pull Instant Forms from that advertiser.',
                'state' => $formsSynced ? 'complete' : ($connected ? 'current' : 'pending'),
            ],
            [
                'n' => '4',
                'title' => 'Map CRM Fields',
                'text' => 'Match TikTok fields to your CRM lead fields.',
                'state' => $hasConfiguredMapping ? 'complete' : ($formsSynced ? 'current' : 'pending'),
            ],
            [
                'n' => '5',
                'title' => 'Receive Leads Automatically',
                'text' => $webhookSubscribed
                    ? 'TikTok will send Instant Form leads to this school.'
                    : 'Enable lead delivery so TikTok can send Instant Form submissions here.',
                'state' => $deliveryReady ? 'complete' : ($connected ? 'current' : 'pending'),
            ],
        ];

        return view('admin.integrations.tiktok.show', compact(
            'connection',
            'connected',
            'configOk',
            'setupStatus',
            'setupSteps',
            'pendingAdvertiserSelection',
            'pendingAdvertisers',
            'connectedAt',
            'formMappings',
            'formsSynced',
            'formsLastSyncedAt',
            'webhookSubscribed',
            'recentSubmissions',
            'eligibleReprocessCount',
            'webhookUrl',
        ));
    }

    public function connect(): RedirectResponse
    {
        OrganizationContext::idOrFail();

        if (! $this->tikTokService->credentialsConfigured()) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', 'TikTok API credentials have not been configured yet.');
        }

        return redirect()->away($this->tikTokService->beginOAuth());
    }

    public function callback(Request $request): RedirectResponse
    {
        OrganizationContext::idOrFail();

        if ($request->filled('error')) {
            $this->tikTokService->forgetOAuthSession();

            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', 'TikTok authorization was cancelled or denied.');
        }

        $authCode = $request->string('auth_code')->toString();
        $state = $request->string('state')->toString();

        if ($authCode === '' || $state === '') {
            $this->tikTokService->forgetOAuthSession();

            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', 'TikTok authorization was incomplete. Please connect again.');
        }

        try {
            $this->tikTokService->handleOAuthCallback($authCode, $state);
        } catch (TikTokOAuthException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->getMessage());
        } catch (TikTokApiException $e) {
            $this->tikTokService->forgetOAuthSession();

            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->userMessage());
        }

        return redirect()
            ->route('admin.integrations.tiktok.show')
            ->with('success', 'TikTok authorized. Select the Ads Manager account to connect.');
    }

    public function selectAdvertiser(Request $request): RedirectResponse
    {
        OrganizationContext::idOrFail();

        $validated = $request->validate([
            'advertiser_id' => ['required', 'string', 'max:64'],
        ]);

        try {
            $this->tikTokService->selectAdvertiser(
                $validated['advertiser_id'],
                $request->user('admin')
            );
        } catch (TikTokOAuthException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.integrations.tiktok.show')
            ->with('success', 'TikTok Ads account connected for this organization.');
    }

    public function syncForms(): RedirectResponse
    {
        OrganizationContext::idOrFail();

        try {
            $forms = $this->tikTokService->syncInstantForms();
        } catch (TikTokOAuthException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->getMessage());
        } catch (TikTokApiException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->operationUserMessage());
        }

        $count = $forms->count();
        $message = $count === 0
            ? 'Sync complete. No TikTok Lead Forms were found for this advertiser.'
            : 'Synced '.$count.' TikTok Lead Form'.($count === 1 ? '' : 's').'. Configure each form before leads can be imported.';

        return redirect()
            ->route('admin.integrations.tiktok.show')
            ->with('success', $message);
    }

    public function configure(TikTokFormMapping $tiktokForm): View|RedirectResponse
    {
        try {
            $mapping = $this->tikTokService->mappingForCurrentOrganization($tiktokForm);
        } catch (TikTokOAuthException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->getMessage());
        }

        try {
            $fields = $this->tikTokService->fieldsForMapping($mapping);
        } catch (TikTokApiException $e) {
            $fields = $this->tikTokService->fieldsFromStoredMapping($mapping);
            session()->now('error', $e->operationUserMessage());
        }

        $admins = Admin::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->orderBy('name')
            ->get(['id', 'name']);

        $crmFields = TikTokCrmFields::options();
        $priorities = LeadPriority::options();

        return view('admin.integrations.tiktok.configure', compact(
            'mapping',
            'fields',
            'admins',
            'crmFields',
            'priorities',
        ));
    }

    public function updateMapping(UpdateTikTokFormMappingRequest $request, TikTokFormMapping $tiktokForm): RedirectResponse
    {
        try {
            $this->tikTokService->updateFormMapping(
                $tiktokForm,
                $request->mappingPayload()
            );
        } catch (TikTokOAuthException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.integrations.tiktok.show')
            ->with('success', 'TikTok form mapping saved for this organization.');
    }

    public function registerWebhook(): RedirectResponse
    {
        OrganizationContext::idOrFail();

        try {
            $connection = $this->tikTokService->subscribeCurrentOrganizationWebhooks();
        } catch (TikTokOAuthException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->getMessage());
        } catch (TikTokApiException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->operationUserMessage());
        }

        $alreadySubscribed = filled($connection->webhook_subscribed_at)
            && filled($connection->settings['subscription_id'] ?? null);

        return redirect()
            ->route('admin.integrations.tiktok.show')
            ->with('success', $alreadySubscribed
                ? 'TikTok lead delivery is enabled for this advertiser.'
                : 'TikTok lead delivery is enabled for this advertiser.');
    }

    public function reprocessPending(): RedirectResponse
    {
        OrganizationContext::idOrFail();

        try {
            $count = $this->tikTokService->reprocessPendingSubmissions();
        } catch (TikTokOAuthException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->getMessage());
        }

        $message = $count === 0
            ? 'There are no TikTok leads waiting to be reprocessed.'
            : 'Queued '.$count.' TikTok lead'.($count === 1 ? '' : 's').' for reprocessing.';

        return redirect()
            ->route('admin.integrations.tiktok.show')
            ->with('success', $message);
    }

    public function reprocessSubmission(TikTokLeadSubmission $tiktokSubmission): RedirectResponse
    {
        OrganizationContext::idOrFail();

        try {
            $this->tikTokService->reprocessSubmission($tiktokSubmission);
        } catch (TikTokOAuthException $e) {
            return redirect()
                ->route('admin.integrations.tiktok.show')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.integrations.tiktok.show')
            ->with('success', 'This TikTok lead was queued for reprocessing.');
    }

    private function connectedAtDisplay(?IntegrationConnection $connection): ?string
    {
        if (! $connection?->isConnected()) {
            return null;
        }

        $settingsTimestamp = $connection->settings['connected_at'] ?? null;
        if (is_string($settingsTimestamp) && $settingsTimestamp !== '') {
            try {
                return Carbon::parse($settingsTimestamp)->timezone(config('app.timezone'))->toDayDateTimeString();
            } catch (\Throwable) {
                // Fall through to updated_at.
            }
        }

        return $connection->updated_at?->timezone(config('app.timezone'))->toDayDateTimeString();
    }
}
