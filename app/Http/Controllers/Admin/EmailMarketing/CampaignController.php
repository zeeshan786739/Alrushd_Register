<?php

namespace App\Http\Controllers\Admin\EmailMarketing;

use App\Enums\EmailMarketing\CampaignStatus;
use App\Http\Controllers\Controller;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\Template;
use App\Services\EmailMarketing\CampaignDispatchService;
use App\Services\EmailMarketing\CampaignRecipientResolver;
use App\Services\EmailMarketing\HtmlSanitizer;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignDispatchService $dispatcher,
        private CampaignRecipientResolver $resolver,
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

        return view('admin.email-marketing.campaigns.create', compact('templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $campaign = Campaign::create([
            ...$validated,
            'organization_id' => OrganizationContext::idOrFail(),
            'body_html' => $this->sanitizer->sanitize($validated['body_html'] ?? ''),
            'status' => CampaignStatus::Draft->value,
            'created_by' => auth('admin')->id(),
            'recipient_filters' => [
                'lead_ids' => $request->input('lead_ids', []),
                'customer_ids' => $request->input('customer_ids', []),
                'manual_emails' => $request->input('manual_emails'),
                'lead_status' => $request->input('lead_status'),
            ],
        ]);

        $this->dispatcher->snapshotRecipients($campaign);

        if ($request->boolean('send_now')) {
            $this->dispatcher->dispatch($campaign->fresh());

            return redirect()->route('admin.email.campaigns.show', $campaign)
                ->with('success', 'Campaign queued for delivery.');
        }

        return redirect()->route('admin.email.campaigns.show', $campaign)
            ->with('success', 'Campaign created.');
    }

    public function show(Campaign $emCampaign): View
    {
        $this->authorize('view', $emCampaign);
        $emCampaign->load(['recipients' => fn ($q) => $q->latest()->limit(100)]);

        return view('admin.email-marketing.campaigns.show', ['campaign' => $emCampaign]);
    }

    public function edit(Campaign $emCampaign): View
    {
        $this->authorize('update', $emCampaign);
        abort_unless($emCampaign->status === CampaignStatus::Draft->value, 403, 'Only drafts can be edited.');
        $templates = Template::forCurrentOrganization()->where('is_active', true)->orderBy('name')->get();

        return view('admin.email-marketing.campaigns.edit', [
            'campaign' => $emCampaign,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, Campaign $emCampaign): RedirectResponse
    {
        $this->authorize('update', $emCampaign);
        abort_unless($emCampaign->status === CampaignStatus::Draft->value, 403);

        $validated = $this->validated($request);
        $emCampaign->update([
            ...$validated,
            'body_html' => $this->sanitizer->sanitize($validated['body_html'] ?? ''),
            'recipient_filters' => [
                'lead_ids' => $request->input('lead_ids', []),
                'customer_ids' => $request->input('customer_ids', []),
                'manual_emails' => $request->input('manual_emails'),
                'lead_status' => $request->input('lead_status'),
            ],
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
        $this->authorize('send', $emCampaign);
        $validated = $request->validate(['scheduled_at' => 'required|date|after:now']);

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
            'lead_ids' => $request->input('lead_ids', []),
            'customer_ids' => $request->input('customer_ids', []),
            'lead_status' => $request->get('lead_status'),
        ];

        $recipients = $this->resolver->resolve(OrganizationContext::idOrFail(), $options);

        return view('admin.email-marketing.campaigns.preview-recipients', [
            'recipients' => $recipients->take(100),
            'total' => $recipients->count(),
        ]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'from_name' => 'nullable|string|max:150',
            'from_email' => 'nullable|email',
            'body_html' => 'required|string',
            'template_id' => 'nullable|integer',
            'recipient_source' => 'required|in:leads,customers,form_entries,manual,selected_leads,selected_customers',
            'tracking_enabled' => 'nullable|boolean',
        ]);
    }
}
