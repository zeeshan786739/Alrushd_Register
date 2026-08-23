<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Invoice;
use App\Models\Crm\Lead;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Support\InvoiceDueState;
use App\Support\LeadFollowUpState;
use App\Support\ProjectDueState;
use App\Support\QuotationExpiryState;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrmOverviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view leads|view customers|view projects|view quotations|view invoices');
    }

    public function index(Request $request): View
    {
        $today = now(config('app.timezone'))->toDateString();
        $dueSoonUntil = now(config('app.timezone'))->addDays(ProjectDueState::DUE_SOON_DAYS)->toDateString();
        $quoteSoonUntil = now(config('app.timezone'))->addHours(QuotationExpiryState::HOURS_SOON);

        $leads = Lead::forCurrentOrganization();
        $projects = Project::forCurrentOrganization();
        $quotations = Quotation::forCurrentOrganization();
        $invoices = Invoice::forCurrentOrganization()->where('status', '!=', 'cancelled');

        $stats = [
            'leads_total' => (clone $leads)->count(),
            'leads_new' => (clone $leads)->where('lead_status', 'new')->count(),
            'leads_follow_up_today' => (clone $leads)->followUpToday()->count(),
            'leads_follow_up_overdue' => (clone $leads)->overdueFollowUp()->count(),
            'quotations_open' => (clone $quotations)->whereIn('status', ['draft', 'sent'])->count(),
            'quotations_accepted_value' => (float) (clone $quotations)->where('status', 'accepted')->sum('total'),
            'invoiced' => (float) (clone $invoices)->sum('total'),
            'paid' => (float) (clone $invoices)->sum('paid_amount'),
            'outstanding' => (float) Invoice::forCurrentOrganization()
                ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
                ->sum('due_amount'),
            'overdue_invoices' => (float) Invoice::forCurrentOrganization()
                ->where('due_amount', '>', 0)
                ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
                ->whereDate('due_date', '<', $today)
                ->sum('due_amount'),
            'projects_active' => (clone $projects)->whereIn('status', ['pending', 'in_progress', 'on_hold'])->count(),
            'projects_due_soon' => (clone $projects)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->whereNotNull('end_date')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $dueSoonUntil)
                ->count(),
            'projects_overdue' => (clone $projects)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->whereNotNull('end_date')
                ->whereDate('end_date', '<', $today)
                ->count(),
            'projects_completed' => (clone $projects)->where('status', 'completed')->count(),
        ];

        $attention = collect();

        if ($request->user('admin')?->can('view leads')) {
            foreach ((clone $leads)->overdueFollowUp()->orderBy('next_follow_up_date')->limit(5)->get() as $lead) {
                $state = LeadFollowUpState::forLead($lead);
                $attention->push([
                    'severity' => 'danger',
                    'type' => 'Lead follow-up overdue',
                    'label' => trim($lead->first_name.' '.$lead->last_name) ?: ('Lead #'.$lead->id),
                    'meta' => $state->label,
                    'url' => route('admin.crm.leads.show', $lead),
                ]);
            }
            foreach ((clone $leads)->followUpToday()->orderBy('next_follow_up_date')->limit(5)->get() as $lead) {
                $state = LeadFollowUpState::forLead($lead);
                $attention->push([
                    'severity' => 'warning',
                    'type' => 'Lead follow-up today',
                    'label' => trim($lead->first_name.' '.$lead->last_name) ?: ('Lead #'.$lead->id),
                    'meta' => $state->label,
                    'url' => route('admin.crm.leads.show', $lead),
                ]);
            }
        }

        if ($request->user('admin')?->can('view projects')) {
            foreach ((clone $projects)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->whereNotNull('end_date')
                ->whereDate('end_date', '<', $today)
                ->orderBy('end_date')
                ->limit(5)
                ->get() as $project) {
                $state = ProjectDueState::forProject($project);
                $attention->push([
                    'severity' => 'danger',
                    'type' => 'Project overdue',
                    'label' => $project->name,
                    'meta' => $state->label,
                    'url' => route('admin.crm.projects.show', $project),
                ]);
            }
        }

        if ($request->user('admin')?->can('view quotations')) {
            foreach ((clone $quotations)
                ->whereNotIn('status', ['accepted', 'rejected'])
                ->whereNull('converted_invoice_id')
                ->whereNotNull('valid_until')
                ->where('valid_until', '<=', $quoteSoonUntil->toDateString())
                ->orderBy('valid_until')
                ->limit(5)
                ->get() as $quotation) {
                $state = QuotationExpiryState::forQuotation($quotation);
                if (! $state->applies) {
                    continue;
                }
                $attention->push([
                    'severity' => $state->state === QuotationExpiryState::EXPIRED ? 'danger' : 'warning',
                    'type' => $state->state === QuotationExpiryState::EXPIRED ? 'Quotation expired' : 'Quotation expiring soon',
                    'label' => $quotation->quotation_number,
                    'meta' => $state->label,
                    'url' => route('admin.crm.quotations.show', $quotation),
                ]);
            }
        }

        if ($request->user('admin')?->can('view invoices')) {
            foreach (Invoice::forCurrentOrganization()
                ->where('due_amount', '>', 0)
                ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
                ->whereDate('due_date', '<', $today)
                ->orderBy('due_date')
                ->limit(5)
                ->get() as $invoice) {
                $state = InvoiceDueState::forInvoice($invoice);
                $attention->push([
                    'severity' => 'danger',
                    'type' => 'Invoice overdue',
                    'label' => $invoice->invoice_number,
                    'meta' => $state->label.' · Outstanding '.number_format((float) $invoice->due_amount, 2),
                    'url' => route('admin.crm.invoices.show', $invoice),
                ]);
            }
        }

        $attention = $attention
            ->sortBy(fn (array $item) => $item['severity'] === 'danger' ? 0 : 1)
            ->values()
            ->take(15);

        $quickLinks = array_values(array_filter([
            $request->user('admin')?->can('view leads') ? ['label' => 'Leads', 'url' => route('admin.crm.leads.index'), 'icon' => 'solar:user-hand-up-linear'] : null,
            $request->user('admin')?->can('view customers') ? ['label' => 'Customers', 'url' => route('admin.crm.customers.index'), 'icon' => 'solar:users-group-rounded-linear'] : null,
            $request->user('admin')?->can('view projects') ? ['label' => 'Projects', 'url' => route('admin.crm.projects.index'), 'icon' => 'solar:folder-linear'] : null,
            $request->user('admin')?->can('view quotations') ? ['label' => 'Quotations', 'url' => route('admin.crm.quotations.index'), 'icon' => 'solar:document-text-linear'] : null,
            $request->user('admin')?->can('view invoices') ? ['label' => 'Invoices', 'url' => route('admin.crm.invoices.index'), 'icon' => 'solar:bill-list-linear'] : null,
        ]));

        return view('admin.crm.overview', compact('stats', 'attention', 'quickLinks'));
    }
}
