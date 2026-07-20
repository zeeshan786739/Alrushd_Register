<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreCustomerRequest;
use App\Http\Requests\Crm\UpdateCustomerRequest;
use App\Models\Admin;
use App\Models\Crm\Customer;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view customers')->only(['index', 'show']);
        $this->middleware('permission:create customers')->only(['create', 'store']);
        $this->middleware('permission:update customers')->only(['edit', 'update', 'storeContact', 'storeActivity']);
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

        return view('admin.crm.customers.index', compact('customers', 'stats'));
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
        $customer->load(['assignedAdmin', 'contacts', 'activities.admin', 'projects', 'quotations', 'invoices', 'lead']);
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.customers.show', compact('customer', 'admins'));
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
        $customer->update($request->validated());

        return redirect()->route('admin.crm.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);
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
