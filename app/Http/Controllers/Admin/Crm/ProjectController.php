<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreProjectRequest;
use App\Http\Requests\Crm\UpdateProjectRequest;
use App\Models\Admin;
use App\Models\Crm\Customer;
use App\Models\Crm\Project;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view projects')->only(['index', 'show']);
        $this->middleware('permission:create projects')->only(['create', 'store']);
        $this->middleware('permission:update projects')->only(['edit', 'update', 'storeTask', 'updateTask', 'destroyTask']);
        $this->middleware('permission:delete projects')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $projects = Project::forCurrentOrganization()
            ->with(['customer', 'assignedAdmin'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Project::forCurrentOrganization()->count(),
            'in_progress' => Project::forCurrentOrganization()->where('status', 'in_progress')->count(),
            'completed' => Project::forCurrentOrganization()->where('status', 'completed')->count(),
        ];

        $customers = Customer::forCurrentOrganization()->orderBy('name')->get(['id', 'name']);

        return view('admin.crm.projects.index', compact('projects', 'stats', 'customers'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::forCurrentOrganization()->orderBy('name')->get();
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();
        $selectedCustomer = $request->customer_id;

        return view('admin.crm.projects.create', compact('customers', 'admins', 'selectedCustomer'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Customer::forCurrentOrganization()->findOrFail($request->validated('customer_id'));

        $project = Project::create(array_merge($request->validated(), [
            'organization_id' => OrganizationContext::idOrFail(),
            'project_code' => Project::generateProjectCode(),
            'created_by' => auth('admin')->id(),
        ]));

        return redirect()->route('admin.crm.projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);
        $project->load(['customer', 'assignedAdmin', 'tasks.assignedAdmin', 'quotations', 'invoices']);
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.projects.show', compact('project', 'admins'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);
        $customers = Customer::forCurrentOrganization()->orderBy('name')->get();
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.projects.edit', compact('project', 'customers', 'admins'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        Customer::forCurrentOrganization()->findOrFail($request->validated('customer_id'));
        $project->update($request->validated());

        return redirect()->route('admin.crm.projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);
        $project->tasks()->delete();
        $project->delete();

        return redirect()->route('admin.crm.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function storeTask(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:admins,id',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $project->tasks()->create(array_merge($validated, [
            'organization_id' => $project->organization_id,
        ]));

        $project->recalculateProgress();

        return back()->with('success', 'Task added successfully.');
    }

    public function updateTask(Request $request, Project $project, int $task): RedirectResponse
    {
        $this->authorize('update', $project);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:admins,id',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
        ]);

        $project->tasks()->where('id', $task)->update($validated);
        $project->recalculateProgress();

        return back()->with('success', 'Task updated successfully.');
    }

    public function destroyTask(Project $project, int $task): RedirectResponse
    {
        $this->authorize('update', $project);
        $project->tasks()->where('id', $task)->delete();
        $project->recalculateProgress();

        return back()->with('success', 'Task removed.');
    }
}
