<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreInvoiceRequest;
use App\Http\Requests\Crm\UpdateInvoiceRequest;
use App\Mail\Crm\InvoiceMail;
use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\InvoicePayment;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Services\Crm\FinancialCalculator;
use App\Support\OrganizationContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(private FinancialCalculator $calculator)
    {
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

        $stats = [
            'total' => Invoice::forCurrentOrganization()->count(),
            'outstanding' => Invoice::forCurrentOrganization()->whereIn('status', ['sent', 'partially_paid', 'overdue'])->sum('due_amount'),
            'paid' => Invoice::forCurrentOrganization()->where('status', 'paid')->count(),
        ];

        $customers = Customer::forCurrentOrganization()->orderBy('name')->get(['id', 'name']);

        return view('admin.crm.invoices.index', compact('invoices', 'stats', 'customers'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::forCurrentOrganization()->orderBy('name')->get();
        $projects = Project::forCurrentOrganization()->orderBy('name')->get();
        $quotations = Quotation::forCurrentOrganization()->where('status', 'accepted')->orderByDesc('created_at')->get();
        $selectedCustomer = $request->customer_id;

        return view('admin.crm.invoices.create', compact('customers', 'projects', 'quotations', 'selectedCustomer'));
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        Customer::forCurrentOrganization()->findOrFail($request->validated('customer_id'));

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
            'quotation_id' => $request->validated('quotation_id'),
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

        return redirect()->route('admin.crm.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);
        $invoice->load(['customer', 'project', 'quotation', 'items', 'payments.receivedByAdmin']);

        return view('admin.crm.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $this->authorize('update', $invoice);
        $customers = Customer::forCurrentOrganization()->orderBy('name')->get();
        $projects = Project::forCurrentOrganization()->orderBy('name')->get();
        $invoice->load('items');

        return view('admin.crm.invoices.edit', compact('invoice', 'customers', 'projects'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);
        Customer::forCurrentOrganization()->findOrFail($request->validated('customer_id'));

        $normalizedItems = $this->calculator->normalizeLineItems($request->validated('items'));
        $financials = $this->calculator->calculate(
            $normalizedItems,
            (float) ($request->validated('tax_percentage') ?? 0),
            (float) ($request->validated('discount_percentage') ?? 0),
        );

        $paidAmount = (float) $invoice->paid_amount;
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
        $invoice->items()->delete();
        $invoice->payments()->delete();
        $invoice->delete();

        return redirect()->route('admin.crm.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        $this->authorize('send', $invoice);
        $invoice->update(['status' => 'sent', 'sent_at' => now()]);
        $invoice->refreshStatus();

        $customerEmail = $invoice->customer?->email;
        $message = 'Invoice marked as sent.';

        if ($customerEmail && config('mail.default') && config('mail.from.address')) {
            try {
                Mail::to($customerEmail)->send(new InvoiceMail($invoice));
                $message = 'Invoice sent and email delivered to '.$customerEmail.'.';
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

        $dueAmount = (float) $invoice->due_amount;
        if ((float) $validated['amount'] > $dueAmount + 0.001) {
            return back()->withErrors([
                'amount' => 'Payment amount cannot exceed the outstanding balance of '.number_format($dueAmount, 2).'.',
            ])->withInput();
        }

        InvoicePayment::create([
            'organization_id' => $invoice->organization_id,
            'invoice_id' => $invoice->id,
            'payment_date' => $validated['payment_date'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'received_by' => auth('admin')->id(),
        ]);

        $invoice->update([
            'paid_amount' => (float) $invoice->paid_amount + (float) $validated['amount'],
            'due_amount' => max(0, (float) $invoice->total - ((float) $invoice->paid_amount + (float) $validated['amount'])),
        ]);

        $invoice->refreshStatus();
        $invoice->customer?->updateLifetimeValue();

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function downloadPdf(Invoice $invoice): Response
    {
        $this->authorize('download', $invoice);
        $invoice->load(['customer', 'items', 'project', 'payments']);

        $pdf = Pdf::loadView('admin.crm.pdf.invoice', compact('invoice'))
            ->setOptions(['isRemoteEnabled' => true]);

        return $pdf->download($invoice->invoice_number.'.pdf');
    }
}
