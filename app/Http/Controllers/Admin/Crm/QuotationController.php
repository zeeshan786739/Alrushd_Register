<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreQuotationRequest;
use App\Http\Requests\Crm\UpdateQuotationRequest;
use App\Mail\Crm\QuotationMail;
use App\Models\Crm\Customer;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Services\Crm\FinancialCalculator;
use App\Services\Crm\QuotationConversionService;
use App\Support\OrganizationContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QuotationController extends Controller
{
    public function __construct(
        private FinancialCalculator $calculator,
        private QuotationConversionService $conversionService,
    ) {
        $this->middleware('permission:view quotations')->only(['index', 'show', 'downloadPdf']);
        $this->middleware('permission:create quotations')->only(['create', 'store']);
        $this->middleware('permission:update quotations')->only(['edit', 'update']);
        $this->middleware('permission:delete quotations')->only(['destroy']);
        $this->middleware('permission:send quotations')->only(['send']);
        $this->middleware('permission:convert quotations')->only(['accept', 'convert']);
    }

    public function index(Request $request): View
    {
        $quotations = Quotation::forCurrentOrganization()
            ->with(['customer', 'project'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->search, fn ($q, $search) => $q->where('quotation_number', 'like', "%{$search}%"))
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Quotation::forCurrentOrganization()->count(),
            'draft' => Quotation::forCurrentOrganization()->where('status', 'draft')->count(),
            'sent' => Quotation::forCurrentOrganization()->where('status', 'sent')->count(),
            'accepted' => Quotation::forCurrentOrganization()->where('status', 'accepted')->count(),
        ];

        $customers = Customer::forCurrentOrganization()->orderBy('name')->get(['id', 'name']);

        return view('admin.crm.quotations.index', compact('quotations', 'stats', 'customers'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::forCurrentOrganization()->orderBy('name')->get();
        $projects = Project::forCurrentOrganization()->orderBy('name')->get();
        $selectedCustomer = $request->customer_id;
        $selectedProject = $request->project_id;

        return view('admin.crm.quotations.create', compact('customers', 'projects', 'selectedCustomer', 'selectedProject'));
    }

    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        Customer::forCurrentOrganization()->findOrFail($request->validated('customer_id'));

        $normalizedItems = $this->calculator->normalizeLineItems($request->validated('items'));
        $financials = $this->calculator->calculate(
            $normalizedItems,
            (float) ($request->validated('tax_percentage') ?? 0),
            (float) ($request->validated('discount_percentage') ?? 0),
        );

        $quotation = Quotation::create([
            'organization_id' => OrganizationContext::idOrFail(),
            'quotation_number' => Quotation::generateQuotationNumber(),
            'customer_id' => $request->validated('customer_id'),
            'project_id' => $request->validated('project_id'),
            'quotation_date' => $request->validated('quotation_date'),
            'valid_until' => $request->validated('valid_until'),
            'tax_percentage' => $request->validated('tax_percentage') ?? 0,
            'discount_percentage' => $request->validated('discount_percentage') ?? 0,
            'status' => $request->validated('status'),
            'terms' => $request->validated('terms'),
            'notes' => $request->validated('notes'),
            'created_by' => auth('admin')->id(),
            ...$financials,
        ]);

        foreach ($normalizedItems as $item) {
            QuotationItem::create(array_merge($item, ['quotation_id' => $quotation->id]));
        }

        return redirect()->route('admin.crm.quotations.show', $quotation)
            ->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation): View
    {
        $this->authorize('view', $quotation);
        $quotation->load(['customer', 'project', 'items', 'convertedInvoice']);

        return view('admin.crm.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation): View
    {
        $this->authorize('update', $quotation);
        $customers = Customer::forCurrentOrganization()->orderBy('name')->get();
        $projects = Project::forCurrentOrganization()->orderBy('name')->get();
        $quotation->load('items');

        return view('admin.crm.quotations.edit', compact('quotation', 'customers', 'projects'));
    }

    public function update(UpdateQuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);
        Customer::forCurrentOrganization()->findOrFail($request->validated('customer_id'));

        $normalizedItems = $this->calculator->normalizeLineItems($request->validated('items'));
        $financials = $this->calculator->calculate(
            $normalizedItems,
            (float) ($request->validated('tax_percentage') ?? 0),
            (float) ($request->validated('discount_percentage') ?? 0),
        );

        $quotation->update([
            'customer_id' => $request->validated('customer_id'),
            'project_id' => $request->validated('project_id'),
            'quotation_date' => $request->validated('quotation_date'),
            'valid_until' => $request->validated('valid_until'),
            'tax_percentage' => $request->validated('tax_percentage') ?? 0,
            'discount_percentage' => $request->validated('discount_percentage') ?? 0,
            'status' => $request->validated('status'),
            'terms' => $request->validated('terms'),
            'notes' => $request->validated('notes'),
            ...$financials,
        ]);

        $quotation->items()->delete();
        foreach ($normalizedItems as $item) {
            QuotationItem::create(array_merge($item, ['quotation_id' => $quotation->id]));
        }

        return redirect()->route('admin.crm.quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $this->authorize('delete', $quotation);
        $quotation->items()->delete();
        $quotation->delete();

        return redirect()->route('admin.crm.quotations.index')
            ->with('success', 'Quotation deleted successfully.');
    }

    public function send(Quotation $quotation): RedirectResponse
    {
        $this->authorize('send', $quotation);
        $quotation->update(['status' => 'sent', 'sent_at' => now()]);

        $customerEmail = $quotation->customer?->email;
        $message = 'Quotation marked as sent.';

        if ($customerEmail && config('mail.default') && config('mail.from.address')) {
            try {
                Mail::to($customerEmail)->send(new QuotationMail($quotation));
                $message = 'Quotation sent and email delivered to '.$customerEmail.'.';
            } catch (\Throwable $e) {
                $message = 'Quotation marked as sent, but email could not be delivered: '.$e->getMessage();
            }
        } else {
            $message = 'Quotation marked as sent. Email skipped (no customer email or mail not configured).';
        }

        return back()->with('success', $message);
    }

    public function accept(Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);
        $quotation->update(['status' => 'accepted', 'accepted_at' => now()]);

        return back()->with('success', 'Quotation accepted.');
    }

    public function reject(Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);
        $quotation->update(['status' => 'rejected']);

        return back()->with('success', 'Quotation rejected.');
    }

    public function convert(Quotation $quotation): RedirectResponse
    {
        $this->authorize('convert', $quotation);

        if ($quotation->status !== 'accepted') {
            return back()->with('error', 'Only accepted quotations can be converted to invoices.');
        }

        $invoice = $this->conversionService->convertToInvoice($quotation);

        return redirect()->route('admin.crm.invoices.show', $invoice)
            ->with('success', 'Quotation converted to invoice successfully.');
    }

    public function downloadPdf(Quotation $quotation): Response
    {
        $this->authorize('download', $quotation);
        $quotation->load(['customer', 'items', 'project']);

        $pdf = Pdf::loadView('admin.crm.pdf.quotation', compact('quotation'))
            ->setOptions(['isRemoteEnabled' => true]);

        return $pdf->download($quotation->quotation_number.'.pdf');
    }
}
