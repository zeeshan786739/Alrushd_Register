<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Exports\Crm\LeadsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\BulkUpdateLeadsRequest;
use App\Http\Requests\Crm\InlineUpdateLeadRequest;
use App\Http\Requests\Crm\StoreLeadRequest;
use App\Http\Requests\Crm\UpdateLeadRequest;
use App\Models\Admin;
use App\Models\Crm\Lead;
use App\Models\Form;
use App\Models\Crm\LeadCategory;
use App\Models\Crm\SavedFilter;
use App\Services\Crm\CrmTransactionalMailService;
use App\Services\Crm\LeadConversionException;
use App\Services\Crm\LeadConversionService;
use App\Support\CrmFormStats;
use App\Support\CrmEmailDeliverySummary;
use App\Support\CrmStatusTone;
use App\Support\LeadCategorySchema;
use App\Support\LeadFollowUpState;
use App\Support\LeadSourceOptions;
use App\Support\OrganizationContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadController extends Controller
{
    public function __construct(
        private LeadConversionService $conversionService,
        private CrmTransactionalMailService $crmMail,
    ) {
        $this->middleware('permission:view leads')->only(['index', 'show', 'panel']);
        $this->middleware('permission:create leads')->only(['create', 'store']);
        $this->middleware('permission:update leads')->only([
            'edit', 'update', 'updateStatus', 'setFollowUp', 'completeFollowUp', 'setAppointment', 'emailForm', 'sendEmail', 'panelEdit',
        ]);
        $this->middleware('permission:update leads|assign leads')->only(['inlineUpdate', 'bulkUpdate']);
        $this->middleware('permission:delete leads')->only(['destroy']);
        $this->middleware('permission:assign leads')->only(['assign']);
        $this->middleware('permission:convert leads')->only(['convert']);
        $this->middleware('permission:export leads')->only(['export']);
    }

    public function index(Request $request): View
    {
        $viewMode = $request->query('view') === 'list' ? 'list' : 'board';
        $perPage = $viewMode === 'list'
            ? min(50, max(10, (int) $request->query('per_page', 15)))
            : min(100, max(20, (int) $request->query('per_page', 50)));

        $leads = $this->filteredQuery($request)->paginate($perPage)->withQueryString();

        $orgScope = Lead::forCurrentOrganization();
        $currentMonth = (clone $orgScope)->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count();
        $lastMonth = (clone $orgScope)->whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year)->count();
        $percentageChange = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;

        $stats = [
            'total' => (clone $orgScope)->count(),
            'new' => (clone $orgScope)->where('lead_status', 'new')->count(),
            'won' => (clone $orgScope)->where('lead_status', 'won')->count(),
            'follow_up_today' => (clone $orgScope)->followUpToday()->count(),
            'monthly_change' => round($percentageChange, 1),
            'facebook_this_week' => (clone $orgScope)
                ->where('source', 'facebook_lead_ads')
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->count(),
            'tiktok_this_week' => (clone $orgScope)
                ->where('source', 'tiktok_lead_ads')
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->count(),
            'form_leads' => (clone $orgScope)->where('source', 'form_submission')->count(),
            'form_leads_week' => (clone $orgScope)
                ->where('source', 'form_submission')
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->count(),
        ];

        $formStats = CrmFormStats::summary();
        $forms = Form::forCurrentOrganization()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $formLeadCounts = CrmFormStats::leadCountsByForm();

        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();
        $savedFilters = SavedFilter::forCurrentOrganization()
            ->where('admin_id', auth('admin')->id())
            ->where('module', 'leads')
            ->orderBy('name')
            ->get();
        $workflowCounts = $this->filteredQuery($request)
            ->reorder()
            ->selectRaw('lead_status, COUNT(*) as total')
            ->groupBy('lead_status')
            ->pluck('total', 'lead_status')
            ->map(fn ($total) => (int) $total)
            ->all();
        $filteredTotal = (int) $this->filteredQuery($request)->count();

        $categories = collect();
        $segments = ['all' => ['total' => $stats['total'], 'new' => $stats['new']], 'uncategorized' => ['total' => 0, 'new' => 0], 'by_id' => []];

        if (LeadCategorySchema::ready()) {
            $categories = LeadCategory::forCurrentOrganization()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $aggregates = Lead::forCurrentOrganization()
                ->selectRaw('lead_category_id, COUNT(*) as total, SUM(CASE WHEN lead_status = ? THEN 1 ELSE 0 END) as new_count', [LeadStatus::New->value])
                ->groupBy('lead_category_id')
                ->get();

            $byId = [];
            $uncategorized = ['total' => 0, 'new' => 0];
            foreach ($aggregates as $row) {
                $bucket = [
                    'total' => (int) $row->total,
                    'new' => (int) $row->new_count,
                ];
                if ($row->lead_category_id === null) {
                    $uncategorized = $bucket;
                } else {
                    $byId[(int) $row->lead_category_id] = $bucket;
                }
            }
            $segments = [
                'all' => ['total' => $stats['total'], 'new' => $stats['new']],
                'uncategorized' => $uncategorized,
                'by_id' => $byId,
            ];
        }

        return view('admin.crm.leads.index', compact('leads', 'stats', 'admins', 'savedFilters', 'categories', 'segments', 'workflowCounts', 'viewMode', 'filteredTotal', 'formStats', 'forms', 'formLeadCounts'))
            ->with('sourceOptions', LeadSourceOptions::filterOptions())
            ->with('platformOptions', [
                'facebook' => 'Facebook',
                'instagram' => 'Instagram',
                'tiktok' => 'TikTok',
                'web' => 'Web',
                'google' => 'Google',
            ]);
    }

    public function create(): View
    {
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();
        $categories = LeadCategorySchema::ready()
            ? LeadCategory::forCurrentOrganization()->active()->orderBy('sort_order')->orderBy('name')->get()
            : collect();

        return view('admin.crm.leads.create', compact('admins', 'categories'));
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $lead = Lead::create(array_merge($request->validated(), [
            'organization_id' => OrganizationContext::idOrFail(),
            'source' => 'manual',
            'created_by' => auth('admin')->id(),
        ]));

        $lead->logActivity('created', 'Lead created manually');

        return redirect()->route('admin.crm.leads.show', $lead)->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        return view('admin.crm.leads.show', $this->leadDetailContext($lead));
    }

    public function panel(Lead $lead): View
    {
        $this->authorize('view', $lead);

        return view('admin.crm.leads.partials.detail-panel', $this->leadDetailContext($lead));
    }

    public function panelEdit(Lead $lead): View
    {
        $this->authorize('update', $lead);

        return view('admin.crm.leads.partials.detail-panel-edit', [
            'lead' => $lead,
            'admins' => Admin::forCurrentOrganization()->orderBy('name')->get(),
            'categories' => LeadCategorySchema::ready()
                ? LeadCategory::forCurrentOrganization()->active()->orderBy('sort_order')->orderBy('name')->get()
                : collect(),
        ]);
    }

    /** @return array<string, mixed> */
    private function leadDetailContext(Lead $lead): array
    {
        $lead->load([
            'assignedAdmin',
            'notes.admin',
            'activities.admin',
            'customer',
            'formEntry.form',
            'metaLeadSubmission.formMapping',
            'leadImport.uploader',
            'category',
        ]);

        return [
            'lead' => $lead,
            'admins' => Admin::forCurrentOrganization()->orderBy('name')->get(),
            'emailHistory' => CrmEmailDeliverySummary::latestForLead(
                (int) $lead->organization_id,
                (int) $lead->id
            ),
        ];
    }

    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();
        $categories = LeadCategorySchema::ready()
            ? LeadCategory::forCurrentOrganization()->active()->orderBy('sort_order')->orderBy('name')->get()
            : collect();

        return view('admin.crm.leads.edit', compact('lead', 'admins', 'categories'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        if ($lead->lead_status !== $request->validated('lead_status')) {
            $lead->logActivity('status_changed', 'Status changed to '.$request->validated('lead_status'));
        }

        $lead->update($request->validated());
        $lead->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lead updated successfully.',
                'lead' => [
                    'id' => $lead->id,
                    'full_name' => $lead->full_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'lead_status' => $lead->lead_status,
                    'priority' => $lead->priority,
                ],
            ]);
        }

        return redirect()->route('admin.crm.leads.show', $lead)->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);
        $lead->notes()->delete();
        $lead->activities()->delete();
        $lead->delete();

        return redirect()->route('admin.crm.leads.index')->with('success', 'Lead deleted successfully.');
    }

    public function addNote(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate([
            'note' => 'required|string',
            'is_important' => 'nullable|boolean',
        ]);

        $lead->notes()->create([
            'organization_id' => $lead->organization_id,
            'admin_id' => auth('admin')->id(),
            'note' => $validated['note'],
            'is_important' => (bool) ($validated['is_important'] ?? false),
        ]);

        $lead->logActivity('note_added', 'Note added');

        return back()->with('success', 'Note added successfully.');
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate([
            'lead_status' => 'required|in:'.implode(',', array_column(LeadStatus::cases(), 'value')),
        ]);

        $lead->update(['lead_status' => $validated['lead_status']]);
        $lead->logActivity('status_changed', 'Status updated to '.$validated['lead_status']);

        return back()->with('success', 'Lead status updated.');
    }

    public function inlineUpdate(InlineUpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        $field = $request->validated('field');
        $value = $request->input('value');

        if ($field === 'lead_status') {
            $this->authorize('update', $lead);
            $lead->update(['lead_status' => $value]);
            $lead->logActivity('status_changed', 'Status updated to '.$value);

            return response()->json([
                'ok' => true,
                'field' => $field,
                'value' => $value,
                'label' => LeadStatus::tryFrom((string) $value)?->label() ?? $value,
                'tone' => CrmStatusTone::for((string) $value),
                'icon' => CrmStatusTone::icon((string) $value),
                'message' => 'Status updated.',
            ]);
        }

        if ($field === 'priority') {
            $this->authorize('update', $lead);
            $lead->update(['priority' => $value]);
            $lead->logActivity('priority_changed', 'Priority updated to '.$value);

            return response()->json([
                'ok' => true,
                'field' => $field,
                'value' => $value,
                'label' => LeadPriority::tryFrom((string) $value)?->label() ?? $value,
                'tone' => CrmStatusTone::for((string) $value),
                'icon' => CrmStatusTone::icon((string) $value),
                'message' => 'Priority updated.',
            ]);
        }

        $this->authorize('assign', $lead);
        if ($value === null || $value === '') {
            $lead->update(['assigned_to' => null]);
            $lead->logActivity('assigned', 'Lead unassigned');

            return response()->json([
                'ok' => true,
                'field' => $field,
                'value' => null,
                'label' => 'Unassigned',
                'tone' => 'neutral',
                'icon' => 'solar:user-linear',
                'message' => 'Assignee cleared.',
            ]);
        }

        $assignee = Admin::forCurrentOrganization()->findOrFail((int) $value);
        $lead->update(['assigned_to' => $assignee->id]);
        $lead->logActivity('assigned', 'Lead assigned to '.$assignee->name);

        return response()->json([
            'ok' => true,
            'field' => $field,
            'value' => $assignee->id,
            'label' => $assignee->name,
            'tone' => 'neutral',
            'icon' => 'solar:user-linear',
            'message' => 'Assignee updated.',
        ]);
    }

    public function bulkUpdate(BulkUpdateLeadsRequest $request): JsonResponse
    {
        $field = $request->validated('field');
        $value = $request->input('value');
        $ids = array_values(array_unique(array_map('intval', $request->validated('lead_ids'))));

        $leads = Lead::forCurrentOrganization()->whereIn('id', $ids)->get();

        if ($leads->count() !== count($ids)) {
            return response()->json([
                'ok' => false,
                'message' => 'One or more leads could not be found.',
            ], 422);
        }

        $updated = 0;
        $results = [];

        foreach ($leads as $lead) {
            if ($field === 'assigned_to') {
                $this->authorize('assign', $lead);

                if ($value === null || $value === '') {
                    $lead->update(['assigned_to' => null]);
                    $lead->logActivity('assigned', 'Lead unassigned (bulk update)');
                    $results[] = [
                        'id' => $lead->id,
                        'value' => null,
                        'label' => 'Unassigned',
                        'tone' => 'neutral',
                        'icon' => 'solar:user-linear',
                    ];
                } else {
                    $assignee = Admin::forCurrentOrganization()->findOrFail((int) $value);
                    $lead->update(['assigned_to' => $assignee->id]);
                    $lead->logActivity('assigned', 'Lead assigned to '.$assignee->name.' (bulk update)');
                    $results[] = [
                        'id' => $lead->id,
                        'value' => (string) $assignee->id,
                        'label' => $assignee->name,
                        'tone' => 'neutral',
                        'icon' => 'solar:user-linear',
                    ];
                }
            } elseif ($field === 'lead_status') {
                $this->authorize('update', $lead);
                $lead->update(['lead_status' => $value]);
                $lead->logActivity('status_changed', 'Status updated to '.$value.' (bulk update)');
                $results[] = [
                    'id' => $lead->id,
                    'value' => $value,
                    'label' => LeadStatus::tryFrom((string) $value)?->label() ?? $value,
                    'tone' => CrmStatusTone::for((string) $value),
                    'icon' => CrmStatusTone::icon((string) $value),
                    'current_status' => $value,
                ];
            } else {
                $this->authorize('update', $lead);
                $lead->update(['priority' => $value]);
                $lead->logActivity('priority_changed', 'Priority updated to '.$value.' (bulk update)');
                $results[] = [
                    'id' => $lead->id,
                    'value' => $value,
                    'label' => LeadPriority::tryFrom((string) $value)?->label() ?? $value,
                    'tone' => CrmStatusTone::for((string) $value),
                    'icon' => CrmStatusTone::icon((string) $value),
                ];
            }

            $updated++;
        }

        $message = match ($field) {
            'lead_status' => $updated.' lead'.($updated === 1 ? '' : 's').' moved to '.(LeadStatus::tryFrom((string) $value)?->label() ?? $value).'.',
            'priority' => $updated.' lead'.($updated === 1 ? '' : 's').' set to '.(LeadPriority::tryFrom((string) $value)?->label() ?? $value).' priority.',
            default => $updated.' lead'.($updated === 1 ? '' : 's').' reassigned.',
        };

        return response()->json([
            'ok' => true,
            'field' => $field,
            'updated' => $updated,
            'results' => $results,
            'message' => $message,
        ]);
    }

    public function setFollowUp(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate([
            'next_follow_up_date' => 'required|date',
            'next_follow_up_time' => 'nullable',
            'next_follow_up_type' => 'nullable|string|max:100',
        ]);

        $lead->update($validated);
        $lead->logActivity('follow_up_scheduled', 'Follow-up scheduled for '.$validated['next_follow_up_date']);

        return back()->with('success', 'Follow-up scheduled.');
    }

    public function completeFollowUp(Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        if (! $lead->next_follow_up_date) {
            return back()->with('success', 'No active follow-up to complete.');
        }

        $when = LeadFollowUpState::scheduledAt($lead)->format('M j, Y g:i A');
        $lead->update([
            'next_follow_up_date' => null,
            'next_follow_up_time' => null,
            'next_follow_up_type' => null,
        ]);
        $lead->logActivity('follow_up_completed', 'Follow-up marked complete (was scheduled for '.$when.')');

        return back()->with('success', 'Follow-up marked complete.');
    }

    public function setAppointment(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'appointment_type' => 'required|in:school_visit,online_meeting,phone_call',
            'appointment_notes' => 'nullable|string',
        ]);

        $lead->update($validated);
        $lead->logActivity('appointment_scheduled', 'Appointment scheduled');

        return back()->with('success', 'Appointment scheduled.');
    }

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('assign', $lead);
        $validated = $request->validate(['assigned_to' => 'required|exists:admins,id']);

        $assignee = Admin::forCurrentOrganization()->findOrFail($validated['assigned_to']);
        $lead->update(['assigned_to' => $assignee->id]);
        $lead->logActivity('assigned', 'Lead assigned to '.$assignee->name);

        return back()->with('success', 'Lead assigned successfully.');
    }

    public function convert(Lead $lead): RedirectResponse
    {
        $this->authorize('convert', $lead);

        try {
            $customer = $this->conversionService->convertToCustomer($lead);
        } catch (LeadConversionException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.crm.customers.show', $customer)
            ->with('success', 'Lead converted to customer successfully.');
    }

    public function saveFilter(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Lead::class);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'filters' => 'required|array',
        ]);

        SavedFilter::updateOrCreate(
            [
                'organization_id' => OrganizationContext::idOrFail(),
                'admin_id' => auth('admin')->id(),
                'module' => 'leads',
                'name' => $validated['name'],
            ],
            ['filters' => $validated['filters']]
        );

        return back()->with('success', 'Filter saved successfully.');
    }

    public function destroyFilter(SavedFilter $savedFilter): JsonResponse|RedirectResponse
    {
        $this->authorize('viewAny', Lead::class);
        $this->assertOwnedLeadFilter($savedFilter);

        $savedFilter->delete();

        if ($this->wantsJsonResponse()) {
            return response()->json(['ok' => true, 'message' => 'Saved filter removed.']);
        }

        return back()->with('success', 'Saved filter removed.');
    }

    public function clearFilters(): JsonResponse|RedirectResponse
    {
        $this->authorize('viewAny', Lead::class);

        SavedFilter::forCurrentOrganization()
            ->where('admin_id', auth('admin')->id())
            ->where('module', 'leads')
            ->delete();

        if ($this->wantsJsonResponse()) {
            return response()->json(['ok' => true, 'message' => 'All saved filters cleared.']);
        }

        return back()->with('success', 'All saved filters cleared.');
    }

    private function assertOwnedLeadFilter(SavedFilter $savedFilter): void
    {
        if (
            (int) $savedFilter->organization_id !== (int) OrganizationContext::idOrFail()
            || (int) $savedFilter->admin_id !== (int) auth('admin')->id()
            || $savedFilter->module !== 'leads'
        ) {
            abort(404);
        }
    }

    private function wantsJsonResponse(): bool
    {
        return request()->expectsJson() || request()->ajax();
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('export', Lead::class);

        return Excel::download(
            new LeadsExport($this->filteredQuery($request)),
            'leads-'.date('Y-m-d').'.xlsx'
        );
    }

    public function emailForm(Lead $lead): View
    {
        $this->authorize('update', $lead);

        if (! $lead->email) {
            abort(422, 'This lead does not have an email address.');
        }

        return view('admin.crm.leads.email', compact('lead'));
    }

    public function sendEmail(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        if (! $lead->email) {
            return back()->withErrors(['email' => 'This lead does not have an email address.']);
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $result = $this->crmMail->sendLeadEmail(
            $lead,
            $validated['subject'],
            $validated['message'],
            auth('admin')->id()
        );

        if (! $result->accepted) {
            return back()
                ->withInput()
                ->withErrors(['email' => $result->error ?: 'Email could not be delivered.']);
        }

        $lead->update([
            'last_contacted_at' => now(),
            'contact_count' => (int) $lead->contact_count + 1,
        ]);

        $lead->logActivity('email_sent', 'Email sent: '.$validated['subject'], [
            'subject' => $validated['subject'],
        ]);

        return redirect()->route('admin.crm.leads.show', $lead)
            ->with('success', 'Email sent successfully.');
    }

    private function filteredQuery(Request $request)
    {
        return Lead::forCurrentOrganization()
            ->with(array_filter([
                'assignedAdmin',
                'formEntry.form',
                LeadCategorySchema::ready() ? 'category' : null,
            ]))
            ->when(LeadCategorySchema::ready() && $request->filled('lead_category_id'), function ($q) use ($request) {
                if ($request->lead_category_id === 'uncategorized') {
                    $q->whereNull('lead_category_id');
                } else {
                    $q->where('lead_category_id', (int) $request->lead_category_id);
                }
            })
            ->when($request->lead_status, fn ($q, $status) => $q->where('lead_status', $status))
            ->when($request->priority === 'high_urgent', fn ($q) => $q->whereIn('priority', [
                LeadPriority::High->value,
                LeadPriority::Urgent->value,
            ]))
            ->when($request->priority && $request->priority !== 'high_urgent', fn ($q, $priority) => $q->where('priority', $priority))
            ->when($request->assigned_to === 'me', fn ($q) => $q->where('assigned_to', auth('admin')->id()))
            ->when($request->assigned_to === 'unassigned', fn ($q) => $q->whereNull('assigned_to'))
            ->when(
                $request->assigned_to && ! in_array($request->assigned_to, ['me', 'unassigned'], true),
                fn ($q, $assigned) => $q->where('assigned_to', $assigned)
            )
            ->when($request->follow_up === 'today', fn ($q) => $q->followUpToday())
            ->when($request->follow_up === 'overdue', fn ($q) => $q->overdueFollowUp())
            ->when($request->source, fn ($q, $source) => $q->where('source', $source))
            ->when($request->form_id, fn ($q, $formId) => $q->whereHas(
                'formEntry',
                fn ($entryQuery) => $entryQuery->where('form_id', (int) $formId)
            ))
            ->when($request->advertising_platform, fn ($q, $platform) => $q->where('advertising_platform', $platform))
            ->when($request->campaign_name, fn ($q, $campaign) => $q->where('campaign_name', 'like', '%'.$campaign.'%'))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'));
    }
}
