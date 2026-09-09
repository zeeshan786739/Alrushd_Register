<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Exports\Crm\FormEntriesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\UpdateFormEntryRequest;
use App\Models\Crm\Customer;
use App\Models\Form;
use App\Models\FormEntry;
use App\Services\Crm\FormEntryLeadConverter;
use App\Support\FormEntryContact;
use App\Support\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FormEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view form submissions')->only(['index', 'forForm', 'show', 'panel']);
        $this->middleware('permission:update form submissions')->only(['edit', 'update']);
        $this->middleware('permission:delete form submissions')->only(['destroy']);
        $this->middleware('permission:export form submissions')->only(['export']);
        $this->middleware('permission:convert form submissions')->only(['convertToLead', 'convertToCustomer']);
    }

    public function index(Request $request): View
    {
        $entries = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $forms = Form::query()->orderBy('name')->get(['id', 'name']);
        $stats = $this->entryStats();

        return view('admin.crm.form-entries.index', compact('entries', 'forms', 'stats'));
    }

    public function forForm(Request $request, Form $form): View
    {
        $entries = $this->filteredQuery($request, $form->id)->paginate(15)->withQueryString();
        $stats = $this->entryStats($form->id);

        return view('admin.crm.form-entries.for-form', compact('entries', 'form', 'stats'));
    }

    public function show(FormEntry $formEntry): View
    {
        $this->authorize('view', $formEntry);
        $formEntry->load('form');

        return view('admin.crm.form-entries.show', compact('formEntry'));
    }

    public function panel(FormEntry $formEntry): View
    {
        $this->authorize('view', $formEntry);
        $formEntry->load('form');
        $contact = FormEntryContact::fromEntry($formEntry);

        return view('admin.crm.form-entries.partials.submission-panel', compact('formEntry', 'contact'));
    }

    public function edit(FormEntry $formEntry): View
    {
        $this->authorize('update', $formEntry);
        $formEntry->load('form');

        return view('admin.crm.form-entries.edit', compact('formEntry'));
    }

    public function update(UpdateFormEntryRequest $request, FormEntry $formEntry): RedirectResponse
    {
        $this->authorize('update', $formEntry);
        $formEntry->update($request->validated());

        return redirect()->route('admin.crm.form-entries.show', $formEntry)
            ->with('success', 'Submission updated successfully.');
    }

    public function destroy(FormEntry $formEntry): RedirectResponse
    {
        $this->authorize('delete', $formEntry);
        $formEntry->delete();

        return redirect()->route('admin.crm.form-entries.index')
            ->with('success', 'Submission deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('export', FormEntry::class);

        return Excel::download(
            new FormEntriesExport($this->filteredQuery($request)),
            'form-submissions-'.date('Y-m-d').'.xlsx'
        );
    }

    public function convertToLead(FormEntry $formEntry, FormEntryLeadConverter $converter): RedirectResponse|JsonResponse
    {
        $this->authorize('convert', $formEntry);

        $lead = $converter->convertIfMissing($formEntry, auth('admin')->id());

        if (! $lead) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['message' => 'Could not add submission to pipeline.'], 422);
            }

            return back()->with('error', 'Could not add submission to pipeline.');
        }

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Form submission converted to lead.',
                'redirect' => route('admin.crm.leads.index', ['view' => 'board']),
                'lead_id' => $lead->id,
            ]);
        }

        return redirect()->route('admin.crm.leads.show', $lead)
            ->with('success', 'Form submission converted to lead.');
    }

    public function convertToCustomer(FormEntry $formEntry): RedirectResponse
    {
        $this->authorize('convert', $formEntry);
        $data = $this->extractEntryData($formEntry);

        $customer = Customer::create([
            'organization_id' => OrganizationContext::idOrFail(),
            'form_entry_id' => $formEntry->id,
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'] ?? 'entry-'.$formEntry->id.'@placeholder.local',
            'phone' => $data['phone'],
            'company' => $data['company'],
            'status' => 'prospect',
            'source' => $formEntry->form?->name ?? 'Form Submission',
            'notes' => $data['description'],
            'created_by' => auth('admin')->id(),
        ]);

        return redirect()->route('admin.crm.customers.show', $customer)
            ->with('success', 'Form submission converted to customer.');
    }

    private function filteredQuery(Request $request, ?int $formId = null)
    {
        return FormEntry::forCurrentOrganization()
            ->with('form')
            ->when($formId, fn ($q) => $q->where('form_id', $formId))
            ->when($request->form_id, fn ($q, $id) => $q->where('form_id', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->legacy_source, fn ($q, $source) => $q->where('legacy_source', $source))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('entry_id', 'like', "%{$search}%")
                        ->orWhere('data', 'like', "%{$search}%");
                });
            })
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('submitted_at', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('submitted_at', '<=', $date))
            ->orderByDesc('submitted_at');
    }

    /** @return array<string, mixed> */
    private function entryStats(?int $formId = null): array
    {
        $base = FormEntry::forCurrentOrganization()->when($formId, fn ($q) => $q->where('form_id', $formId));

        return [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'facebook' => (clone $base)->where('legacy_source', 'facebook_lead_ads')->count(),
        ];
    }

    /** @return array{first_name: string, last_name: ?string, email: ?string, phone: ?string, company: ?string, description: ?string} */
    private function extractEntryData(FormEntry $formEntry): array
    {
        $data = is_array($formEntry->data) ? $formEntry->data : [];
        $firstName = $data['first_name'] ?? $data['fname'] ?? $data['name'] ?? $data['full_name'] ?? 'Unknown';
        $lastName = $data['last_name'] ?? $data['lname'] ?? null;

        if ($firstName === 'Unknown' && ! empty($data['full_name'])) {
            $parts = explode(' ', (string) $data['full_name'], 2);
            $firstName = $parts[0];
            $lastName = $lastName ?? ($parts[1] ?? null);
        }

        return [
            'first_name' => (string) $firstName,
            'last_name' => $lastName ? (string) $lastName : null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? $data['mobile'] ?? $data['mobile_number'] ?? null,
            'company' => $data['company'] ?? null,
            'description' => collect($data)->except(['first_name', 'last_name', 'fname', 'lname', 'name', 'full_name', 'email', 'phone', 'mobile', 'mobile_number', 'company'])
                ->map(fn ($v, $k) => $k.': '.$v)->implode("\n") ?: null,
        ];
    }
}
