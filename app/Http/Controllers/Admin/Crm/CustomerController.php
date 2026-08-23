<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\InlineUpdateCustomerRequest;
use App\Http\Requests\Crm\StoreCustomerRequest;
use App\Http\Requests\Crm\UpdateCustomerRequest;
use App\Models\Admin;
use App\Models\Crm\Customer;
use App\Support\CrmEmailDeliverySummary;
use App\Support\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view customers')->only(['index', 'show']);
        $this->middleware('permission:create customers')->only(['create', 'store']);
        $this->middleware('permission:update customers')->only(['edit', 'update', 'inlineUpdate', 'storeContact', 'storeActivity']);
        $this->middleware('permission:delete customers')->only(['destroy', 'destroyContact']);
    }

    public function index(Request $request): View
    {
        $customers = Customer::forCurrentOrganization()
            ->with(['assignedAdmin'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%");
                });
            })
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Customer::forCurrentOrganization()->count(),
            'active' => Customer::forCurrentOrganization()->where('status', 'active')->count(),
            'prospect' => Customer::forCurrentOrganization()->where('status', 'prospect')->count(),
        ];

        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.customers.index', compact('customers', 'stats', 'admins'));
    }

    public function create(): View
    {
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.customers.create', compact('admins'));
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create(array_merge($request->validated(), [
            'organization_id' => OrganizationContext::idOrFail(),
            'created_by' => auth('admin')->id(),
        ]));

        return redirect()->route('admin.crm.customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);
        $customer->load([
            'assignedAdmin',
            'contacts',
            'activities.admin',
            'projects',
            'quotations.project',
            'invoices',
            'lead',
        ]);

        $projects = $customer->projects;
        $quotations = $customer->quotations;
        $invoices = $customer->invoices;

        $commercial = [
            'projects_total' => $projects->count(),
            'projects_active' => $projects->whereIn('status', ['pending', 'in_progress', 'on_hold'])->count(),
            'quotations_total' => $quotations->count(),
            'quotations_accepted_value' => (float) $quotations->where('status', 'accepted')->sum('total'),
            'invoices_total' => $invoices->count(),
            'invoiced_amount' => (float) $invoices->sum('total'),
            'paid_amount' => (float) $invoices->sum('paid_amount'),
            'outstanding_amount' => (float) $invoices->sum('due_amount'),
            'lifetime_value' => (float) $customer->lifetime_value,
        ];

        $emailHistory = CrmEmailDeliverySummary::latestForCustomer(
            (int) $customer->organization_id,
            (int) $customer->id
        );

        return view('admin.crm.customers.show', compact('customer', 'commercial', 'emailHistory'));
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.customers.edit', compact('customer', 'admins'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);
        $this->assertAssigneeBelongsToOrganization($request->validated('assigned_to') ?? null);
        $customer->update($request->validated());

        return redirect()->route('admin.crm.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function inlineUpdate(InlineUpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        $field = $request->validated('field');
        $value = $request->input('value');

        if ($field === 'status') {
            $customer->update(['status' => $value]);

            return response()->json([
                'ok' => true,
                'field' => $field,
                'value' => $value,
                'tone' => \App\Support\CrmStatusTone::for((string) $value),
                'message' => 'Status updated.',
            ]);
        }

        if ($value === null || $value === '') {
            $customer->update(['assigned_to' => null]);

            return response()->json([
                'ok' => true,
                'field' => $field,
                'value' => null,
                'tone' => 'neutral',
                'message' => 'Owner cleared.',
            ]);
        }

        $assignee = Admin::forCurrentOrganization()->findOrFail((int) $value);
        $customer->update(['assigned_to' => $assignee->id]);

        return response()->json([
            'ok' => true,
            'field' => $field,
            'value' => $assignee->id,
            'tone' => 'neutral',
            'label' => $assignee->name,
            'message' => 'Owner updated.',
        ]);
    }

    private function assertAssigneeBelongsToOrganization(mixed $assignedTo): void
    {
        if ($assignedTo === null || $assignedTo === '') {
            return;
        }

        Admin::forCurrentOrganization()->findOrFail((int) $assignedTo);
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        if ($customer->projects()->exists()
            || $customer->quotations()->exists()
            || $customer->invoices()->exists()) {
            return back()->with('error', 'Cannot delete this customer while projects, quotations, or invoices are linked. Remove or reassign those records first.');
        }

        $customer->contacts()->delete();
        $customer->activities()->delete();
        $customer->delete();

        return redirect()->route('admin.crm.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function storeContact(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'position' => 'nullable|string|max:100',
            'is_primary' => 'nullable|boolean',
        ]);

        if (! empty($validated['is_primary'])) {
            $customer->contacts()->update(['is_primary' => false]);
        }

        $customer->contacts()->create(array_merge($validated, [
            'organization_id' => $customer->organization_id,
        ]));

        return back()->with('success', 'Contact added successfully.');
    }

    public function destroyContact(Customer $customer, int $contact): RedirectResponse
    {
        $this->authorize('update', $customer);
        $customer->contacts()->where('id', $contact)->delete();

        return back()->with('success', 'Contact removed.');
    }

    public function storeActivity(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);
        $validated = $request->validate([
            'type' => 'required|in:call,email,meeting,note,task',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'nullable|date',
            'status' => 'nullable|in:pending,completed,cancelled',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        $customer->activities()->create(array_merge($validated, [
            'organization_id' => $customer->organization_id,
            'admin_id' => auth('admin')->id(),
            'activity_date' => $validated['activity_date'] ?? now(),
            'status' => $validated['status'] ?? 'completed',
        ]));

        $customer->update(['last_contacted_at' => now()]);

        return back()->with('success', 'Activity logged successfully.');
    }
}
