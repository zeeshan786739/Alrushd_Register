<?php

namespace App\Http\Controllers\Admin\EmailMarketing;

use App\Enums\EmailMarketing\CampaignStatus;
use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\SenderMailbox;
use App\Models\EmailMarketing\Template;
use App\Services\EmailMarketing\CampaignDispatchService;
use App\Services\EmailMarketing\CampaignPreflightService;
use App\Services\EmailMarketing\CampaignRecipientResolver;
use App\Services\EmailMarketing\HtmlSanitizer;
use App\Support\CampaignAnalyticsSummary;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignDispatchService $dispatcher,
        private CampaignRecipientResolver $resolver,
        private CampaignPreflightService $preflight,
        private HtmlSanitizer $sanitizer,
    ) {
        $this->middleware('permission:view campaigns')->only(['index', 'show']);
        $this->middleware('permission:create campaigns')->only(['create', 'store', 'duplicate']);
        $this->middleware('permission:update campaigns')->only(['edit', 'update']);
        $this->middleware('permission:delete campaigns')->only(['destroy']);
        $this->middleware('permission:send campaigns')->only(['send', 'previewRecipients']);
        $this->middleware('permission:schedule campaigns')->only(['schedule']);
    }

    public function index(Request $request): View
    {
        $campaigns = Campaign::forCurrentOrganization()
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.email-marketing.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        $templates = Template::forCurrentOrganization()->where('is_active', true)->orderBy('name')->get();
        $mailbox = MailboxSetting::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->first();
        $senderMailboxes = $this->availableSenders();

        return view('admin.email-marketing.campaigns.create', [
            'templates' => $templates,
            'mailbox' => $mailbox,
            'senderMailboxes' => $senderMailboxes,
            'leadStatuses' => LeadStatus::options(),
            'leadPriorities' => LeadPriority::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $mailbox = $this->campaignMailbox();
        $sender = $this->campaignSender((int) $validated['sender_mailbox_id']);
        $campaign = Campaign::create([
            ...$validated,
            'organization_id' => OrganizationContext::idOrFail(),
            'body_html' => $this->sanitizer->sanitize($validated['body_html'] ?? ''),
            'sender_mailbox_id' => $sender->id,
            'from_email' => $sender->email,
            'from_name' => $validated['from_name'] ?: $sender->name,
            'status' => CampaignStatus::Draft->value,
            'created_by' => auth('admin')->id(),
            'recipient_filters' => $this->recipientFilters($request),
        ]);

        $this->dispatcher->snapshotRecipients($campaign);

        if ($request->boolean('send_now')) {
            try {
                $this->dispatcher->dispatch($campaign->fresh());
            } catch (\Throwable $e) {
                report($e);

                return redirect()->route('admin.email.campaigns.show', $campaign)
                    ->withErrors(['send' => $e->getMessage()]);
            }

            return redirect()->route('admin.email.campaigns.show', $campaign)
                ->with('success', 'Campaign queued for delivery.');
        }

        return redirect()->route('admin.email.campaigns.show', $campaign)
            ->with('success', 'Campaign created.');
    }

    public function show(Campaign $emCampaign): View
    {
        $this->authorize('view', $emCampaign);

        $statusFilter = request('status');
        $search = request('search');

        $recipients = $emCampaign->recipients()
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where(function ($inner) use ($statusFilter) {
                    $inner->where('status', $statusFilter)
                        ->orWhere('provider_status', $statusFilter);
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $analytics = CampaignAnalyticsSummary::forCampaign($emCampaign);
        $mailbox = MailboxSetting::query()
            ->where('organization_id', $emCampaign->organization_id)
            ->first();

        return view('admin.email-marketing.campaigns.show', [
            'campaign' => $emCampaign,
            'recipients' => $recipients,
            'analytics' => $analytics,
            'asmConfigured' => filled($mailbox?->sendgrid_asm_group_id),
        ]);
    }

    public function edit(Campaign $emCampaign): View
    {
        $this->authorize('update', $emCampaign);
        abort_unless($emCampaign->status === CampaignStatus::Draft->value, 403, 'Only drafts can be edited.');
        $templates = Template::forCurrentOrganization()->where('is_active', true)->orderBy('name')->get();
        $mailbox = $this->campaignMailbox();
        $senderMailboxes = $this->availableSenders();

        return view('admin.email-marketing.campaigns.edit', [
            'campaign' => $emCampaign,
            'templates' => $templates,
            'mailbox' => $mailbox,
            'senderMailboxes' => $senderMailboxes,
            'leadStatuses' => LeadStatus::options(),
            'leadPriorities' => LeadPriority::options(),
        ]);
    }

    public function update(Request $request, Campaign $emCampaign): RedirectResponse
    {
        $this->authorize('update', $emCampaign);
        abort_unless($emCampaign->status === CampaignStatus::Draft->value, 403);

        $validated = $this->validated($request);
        $mailbox = $this->campaignMailbox();
        $sender = $this->campaignSender((int) $validated['sender_mailbox_id']);
        $emCampaign->update([
            ...$validated,
            'body_html' => $this->sanitizer->sanitize($validated['body_html'] ?? ''),
            'sender_mailbox_id' => $sender->id,
            'from_email' => $sender->email,
            'from_name' => $validated['from_name'] ?: $sender->name,
            'recipient_filters' => $this->recipientFilters($request),
        ]);

        $this->dispatcher->snapshotRecipients($emCampaign->fresh());

        return redirect()->route('admin.email.campaigns.show', $emCampaign)
            ->with('success', 'Campaign updated.');
    }

    public function destroy(Campaign $emCampaign): RedirectResponse
    {
        $this->authorize('delete', $emCampaign);
        $emCampaign->recipients()->delete();
        $emCampaign->delete();

        return redirect()->route('admin.email.campaigns.index')->with('success', 'Campaign deleted.');
    }

    public function send(Campaign $emCampaign): RedirectResponse
    {
        $this->authorize('send', $emCampaign);

        try {
            $this->dispatcher->dispatch($emCampaign);

            return back()->with('success', 'Campaign queued for delivery.');
        } catch (\Throwable $e) {
            return back()->withErrors(['send' => $e->getMessage()]);
        }
    }

    public function schedule(Request $request, Campaign $emCampaign): RedirectResponse
    {
        $this->authorize('schedule', $emCampaign);
        abort_unless(
            in_array($emCampaign->status, [CampaignStatus::Draft->value, CampaignStatus::Scheduled->value], true),
            403,
            'Only draft or scheduled campaigns can be scheduled.'
        );

        $validated = $request->validate(['scheduled_at' => 'required|date|after:now']);

        if ($emCampaign->recipients()->count() === 0) {
            $this->dispatcher->snapshotRecipients($emCampaign);
        }

        if ($emCampaign->recipients()->count() === 0) {
            return back()->withErrors(['schedule' => 'Campaign has no eligible recipients.']);
        }

        $emCampaign->update([
            'status' => CampaignStatus::Scheduled->value,
            'scheduled_at' => $validated['scheduled_at'],
        ]);

        return back()->with('success', 'Campaign scheduled.');
    }

    public function duplicate(Campaign $emCampaign): RedirectResponse
    {
        $this->authorize('create', Campaign::class);

        $copy = $emCampaign->replicate([
            'status', 'started_at', 'completed_at', 'sent_count', 'failed_count',
            'opened_count', 'clicked_count', 'scheduled_at',
        ]);
        $copy->name = $emCampaign->name.' (Copy)';
        $copy->status = CampaignStatus::Draft->value;
        $copy->created_by = auth('admin')->id();
        $copy->save();

        $this->dispatcher->snapshotRecipients($copy);

        return redirect()->route('admin.email.campaigns.edit', $copy)
            ->with('success', 'Campaign duplicated.');
    }

    public function previewRecipients(Request $request): View
    {
        $options = [
            'source' => $request->get('recipient_source', 'manual'),
            'manual_emails' => $request->get('manual_emails'),
            'lead_ids' => array_filter(array_map('intval', (array) $request->input('lead_ids', []))),
            'customer_ids' => array_filter(array_map('intval', (array) $request->input('customer_ids', []))),
            'form_entry_ids' => array_filter(array_map('intval', (array) $request->input('form_entry_ids', []))),
            'lead_status' => $request->get('lead_status'),
            'lead_statuses' => array_filter((array) $request->input('lead_statuses', [])),
            'lead_priority' => $request->get('lead_priority'),
            'lead_source' => $request->get('lead_source'),
            'form_id' => $request->integer('form_id') ?: null,
        ];

        $orgId = OrganizationContext::idOrFail();
        $summary = $this->preflight->summarize($orgId, $options);

        return view('admin.email-marketing.campaigns.preview-recipients', [
            'recipients' => $summary['eligible_rows']->take(100),
            'total' => $summary['eligible'],
            'preflight' => $summary,
        ]);
    }

    /** @return array<string, mixed> */
    private function recipientFilters(Request $request): array
    {
        return [
            'lead_ids' => array_filter(array_map('intval', (array) $request->input('lead_ids', []))),
            'customer_ids' => array_filter(array_map('intval', (array) $request->input('customer_ids', []))),
            'form_entry_ids' => array_filter(array_map('intval', (array) $request->input('form_entry_ids', []))),
            'manual_emails' => $request->input('manual_emails'),
            'lead_status' => $request->input('lead_status'),
            'lead_statuses' => array_values(array_filter((array) $request->input('lead_statuses', []))),
            'lead_priority' => $request->input('lead_priority'),
            'lead_source' => $request->input('lead_source'),
            'form_id' => $request->integer('form_id') ?: null,
        ];
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'from_name' => 'nullable|string|max:150',
            'from_email' => 'nullable|email',
            'sender_mailbox_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('em_sender_mailboxes', 'id')->where(
                    fn ($query) => $query
                        ->where('organization_id', OrganizationContext::idOrFail())
                        ->where('is_active', true)
                        ->where('is_verified', true)
                ),
            ],
            'body_html' => 'required|string',
            'template_id' => 'nullable|integer',
            'recipient_source' => 'required|in:leads,customers,form_entries,manual,selected_leads,selected_customers,selected_form_entries,integration_leads',
            'lead_status' => 'nullable|string|max:80',
            'lead_statuses' => 'nullable|array',
            'lead_statuses.*' => 'string|max:80',
            'lead_priority' => 'nullable|string|max:40',
            'lead_source' => 'nullable|string|max:80',
            'form_id' => 'nullable|integer',
            'lead_ids' => 'nullable|array',
            'customer_ids' => 'nullable|array',
            'form_entry_ids' => 'nullable|array',
            'tracking_enabled' => 'nullable|boolean',
        ]);
    }

    private function campaignMailbox(): MailboxSetting
    {
        $mailbox = MailboxSetting::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->first();

        abort_unless(
            $mailbox?->isSendReady(),
            422,
            'Configure and enable a verified sender in Mailbox Settings before creating a campaign.'
        );

        return $mailbox;
    }

    private function availableSenders()
    {
        return SenderMailbox::forCurrentOrganization()->available()
            ->orderByDesc('is_default')->orderBy('email')->get();
    }

    private function campaignSender(int $id): SenderMailbox
    {
        return SenderMailbox::forCurrentOrganization()->available()->findOrFail($id);
    }
}
