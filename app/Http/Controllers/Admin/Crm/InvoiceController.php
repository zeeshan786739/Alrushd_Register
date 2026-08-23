<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreInvoiceRequest;
use App\Http\Requests\Crm\UpdateInvoiceRequest;
use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\InvoicePayment;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Services\Crm\CrmTransactionalMailService;
use App\Services\Crm\FinancialCalculator;
use App\Support\CrmDocument;
use App\Support\CrmEmailDeliverySummary;
use App\Support\OrganizationContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private FinancialCalculator $calculator,
        private CrmTransactionalMailService $crmMail,
    ) {
        $this->middleware('permission:view invoices')->only(['index', 'show', 'downloadPdf']);
        $this->middleware('permission:create invoices')->only(['create', 'store']);
        $this->middleware('permission:update invoices')->only(['edit', 'update']);
        $this->middleware('permission:delete invoices')->only(['destroy']);
        $this->middleware('permission:send invoices')->only(['send']);
        $this->middleware('permission:record invoice payments')->only(['storePayment']);
    }

    public function index(Request $request): View
    {
        $invoices = Invoice::forCurrentOrganization()
            ->with(['customer', 'project'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->search, fn ($q, $search) => $q->where('invoice_number', 'like', "%{$search}%"))
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(15)
            ->withQueryString();

        $orgInvoices = Invoice::forCurrentOrganization()->where('status', '!=', 'cancelled');

        $stats = [
            'total' => (clone $orgInvoices)->count(),
            'invoiced' => (float) (clone $orgInvoices)->sum('total'),
            'paid_amount' => (float) (clone $orgInvoices)->sum('paid_amount'),
            'outstanding' => (float) Invoice::forCurrentOrganization()
                ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
                ->sum('due_amount'),
            'overdue' => (float) Invoice::forCurrentOrganization()
                ->where('due_amount', '>', 0)
                ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
                ->whereDate('due_date', '<', now()->toDateString())
                ->sum('due_amount'),
            'paid_count' => Invoice::forCurrentOrganization()->where('status', 'paid')->count(),
        ];

        $customers = Customer::forCurrentOrganization()->orderBy('name')->get(['id', 'name']);

        return view('admin.crm.invoices.index', compact('invoices', 'stats', 'customers'));
    }

    public function create(Request $request): View
    {
        $selectedCustomer = $request->customer_id;
        $selectedProject = $request->project_id;

        if ($selectedProject && ! $selectedCustomer) {
            $selectedCustomer = Project::forCurrentOrganization()
                ->whereKey($selectedProject)
                ->value('customer_id');
        }

        $customers = Customer::forCurrentOrganization()->orderBy('name')->get();
        $projects = Project::forCurrentOrganization()
            ->when($selectedCustomer, fn ($q) => $q->where('customer_id', $selectedCustomer))
            ->orderBy('name')
            ->get();
        $quotations = Quotation::forCurrentOrganization()
            ->where('status', 'accepted')
            ->when($selectedCustomer, fn ($q) => $q->where('customer_id', $selectedCustomer))
            ->orderByDesc('created_at')
            ->get();

        if ($selectedProject && ! $projects->contains(fn ($p) => (int) $p->id === (int) $selectedProject)) {
            $selectedProject = null;
        }

        return view('admin.crm.invoices.create', compact('customers', 'projects', 'quotations', 'selectedCustomer', 'selectedProject'));
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        Customer::forCurrentOrganization()->findOrFail($request->validated('customer_id'));
        $this->assertProjectBelongsToCustomer(
            $request->validated('project_id'),
            (int) $request->validated('customer_id')
        );
        $quotation = $this->resolveQuotationForCreate(
            $request->validated('quotation_id'),
            (int) $request->validated('customer_id')
        );

        $normalizedItems = $this->calculator->normalizeLineItems($request->validated('items'));
        $financials = $this->calculator->calculate(
            $normalizedItems,
            (float) ($request->validated('tax_percentage') ?? 0),
            (float) ($request->validated('discount_percentage') ?? 0),
        );

        $invoice = Invoice::create([
            'organization_id' => OrganizationContext::idOrFail(),
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id' => $request->validated('customer_id'),
            'project_id' => $request->validated('project_id'),
            'quotation_id' => $quotation?->id,
            'invoice_date' => $request->validated('invoice_date'),
            'due_date' => $request->validated('due_date'),
            'tax_percentage' => $request->validated('tax_percentage') ?? 0,
            'discount_percentage' => $request->validated('discount_percentage') ?? 0,
            'status' => $request->validated('status'),
            'terms' => $request->validated('terms'),
            'notes' => $request->validated('notes'),
            'paid_amount' => 0,
            'due_amount' => $financials['total'],
            'created_by' => auth('admin')->id(),
            ...$financials,
        ]);

        foreach ($normalizedItems as $item) {
            InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
        }

        if ($quotation && ! $quotation->converted_invoice_id) {
            $quotation->update(['converted_invoice_id' => $invoice->id]);
        }

        $invoice->refreshStatus();

        return redirect()->route('admin.crm.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);
        $invoice->load([
            'customer',
            'project',
            'quotation',
            'items',
            'payments' => fn ($q) => $q->with('receivedByAdmin')->orderByDesc('payment_date')->orderByDesc('id'),
        ]);
        $emailDelivery = CrmEmailDeliverySummary::latestForInvoice(
            (int) $invoice->organization_id,
            (int) $invoice->id
        );

        return view('admin.crm.invoices.show', compact('invoice', 'emailDelivery'));
    }

    public function edit(Invoice $invoice): View|RedirectResponse
    {
        $this->authorize('update', $invoice);

        if ($invoice->status === 'paid') {
            return redirect()->route('admin.crm.invoices.show', $invoice)
                ->with('error', 'Paid invoices cannot be edited.');
        }

        $customers = Customer::forCurrentOrganization()->orderBy('name')->get();
        $projects = Project::forCurrentOrganization()->orderBy('name')->get();
        $invoice->load('items');

        return view('admin.crm.invoices.edit', compact('invoice', 'customers', 'projects'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if ($invoice->status === 'paid') {
            return redirect()->route('admin.crm.invoices.show', $invoice)
                ->with('error', 'Paid invoices cannot be edited.');
        }

        Customer::forCurrentOrganization()->findOrFail($request->validated('customer_id'));
        $this->assertProjectBelongsToCustomer(
            $request->validated('project_id'),
            (int) $request->validated('customer_id')
        );

        $normalizedItems = $this->calculator->normalizeLineItems($request->validated('items'));
        $financials = $this->calculator->calculate(
            $normalizedItems,
            (float) ($request->validated('tax_percentage') ?? 0),
            (float) ($request->validated('discount_percentage') ?? 0),
        );

        $paidAmount = (float) $invoice->paid_amount;
        if ($financials['total'] + 0.001 < $paidAmount) {
            return back()->withErrors([
                'items' => 'Invoice total cannot be less than the amount already paid ('.number_format($paidAmount, 2).').',
            ])->withInput();
        }

        $dueAmount = max(0, $financials['total'] - $paidAmount);

        $invoice->update([
            'customer_id' => $request->validated('customer_id'),
            'project_id' => $request->validated('project_id'),
            'invoice_date' => $request->validated('invoice_date'),
            'due_date' => $request->validated('due_date'),
            'tax_percentage' => $request->validated('tax_percentage') ?? 0,
            'discount_percentage' => $request->validated('discount_percentage') ?? 0,
            'status' => $request->validated('status'),
            'terms' => $request->validated('terms'),
            'notes' => $request->validated('notes'),
            'due_amount' => $dueAmount,
            ...$financials,
        ]);

        $invoice->items()->delete();
        foreach ($normalizedItems as $item) {
            InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
        }

        $invoice->refreshStatus();

        return redirect()->route('admin.crm.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        if ($invoice->payments()->exists()) {
            return back()->with(
                'error',
                'This invoice has recorded payments and cannot be deleted.'
            );
        }

        $customer = $invoice->customer;
        $invoice->items()->delete();
        $invoice->delete();
        $customer?->updateLifetimeValue();

        return redirect()->route('admin.crm.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        $this->authorize('send', $invoice);
        // Existing safe semantics: mark Sent first, then attempt delivery.
        $invoice->update(['status' => 'sent', 'sent_at' => now()]);
        $invoice->refreshStatus();

        $customerEmail = $invoice->customer?->email;
        $message = 'Invoice marked as sent.';

        if ($customerEmail && config('mail.from.address')) {
            try {
                $result = $this->crmMail->sendInvoice($invoice, auth('admin')->id());
                $message = $result->accepted
                    ? 'Invoice sent and email delivered to '.$customerEmail.'.'
                    : 'Invoice marked as sent, but email could not be delivered: '.($result->error ?: 'Send failed');
            } catch (\Throwable $e) {
                $message = 'Invoice marked as sent, but email could not be delivered: '.$e->getMessage();
            }
        } else {
            $message = 'Invoice marked as sent. Email skipped (no customer email or mail not configured).';
        }

        return back()->with('success', $message);
    }

    public function storePayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('recordPayment', $invoice);
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,card,cheque,other',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $invoice) {
                $locked = Invoice::forCurrentOrganization()
                    ->whereKey($invoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $dueAmount = (float) $locked->due_amount;
                if ((float) $validated['amount'] > $dueAmount + 0.001) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount cannot exceed the outstanding balance of '.number_format($dueAmount, 2).'.',
                    ]);
                }

                InvoicePayment::create([
                    'organization_id' => $locked->organization_id,
                    'invoice_id' => $locked->id,
                    'payment_date' => $validated['payment_date'],
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'received_by' => auth('admin')->id(),
                ]);

                $newPaid = (float) $locked->paid_amount + (float) $validated['amount'];
                $locked->update([
                    'paid_amount' => $newPaid,
                    'due_amount' => max(0, (float) $locked->total - $newPaid),
                ]);

                $locked->refreshStatus();
                $locked->customer?->updateLifetimeValue();
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function downloadPdf(Request $request, Invoice $invoice): Response
    {
        $this->authorize('download', $invoice);
        $invoice->load(['customer', 'items', 'project', 'quotation', 'payments']);
        $organization = CrmDocument::organizationFor($invoice->organization_id);
        $doc = CrmDocument::invoiceViewData($invoice, 'pdf');

        $html = view('admin.crm.pdf.invoice', compact('invoice', 'organization', 'doc'))->render();
        $html = CrmDocument::prepareHtmlForPdf($html);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOptions(CrmDocument::pdfOptions());

        $filename = $invoice->invoice_number.'.pdf';

        if ($request->boolean('inline')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    private function assertProjectBelongsToCustomer(mixed $projectId, int $customerId): void
    {
        if ($projectId === null || $projectId === '') {
            return;
        }

        Project::forCurrentOrganization()
            ->whereKey($projectId)
            ->where('customer_id', $customerId)
            ->firstOrFail();
    }

    private function resolveQuotationForCreate(mixed $quotationId, int $customerId): ?Quotation
    {
        if ($quotationId === null || $quotationId === '') {
            return null;
        }

        $quotation = Quotation::forCurrentOrganization()
            ->whereKey($quotationId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        if ($quotation->converted_invoice_id
            || Invoice::forCurrentOrganization()->where('quotation_id', $quotation->id)->exists()) {
            abort(422, 'This quotation already has an invoice.');
        }

        return $quotation;
    }
}
