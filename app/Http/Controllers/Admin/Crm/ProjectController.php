<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\InlineUpdateProjectRequest;
use App\Http\Requests\Crm\StoreProjectRequest;
use App\Http\Requests\Crm\UpdateProjectRequest;
use App\Models\Admin;
use App\Models\Crm\Customer;
use App\Models\Crm\Project;
use App\Support\CrmOrgRules;
use App\Support\CrmStatusTone;
use App\Support\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view projects')->only(['index', 'show']);
        $this->middleware('permission:create projects')->only(['create', 'store']);
        $this->middleware('permission:update projects')->only(['edit', 'update', 'inlineUpdate', 'storeTask', 'updateTask', 'destroyTask']);
        $this->middleware('permission:delete projects')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $baseQuery = Project::forCurrentOrganization();
        $filteredQuery = $this->filteredProjectQuery($request);
        $perPage = min(max((int) $request->get('per_page', 15), 10), 50);
        $sortBy = in_array($request->get('sort_by'), ['name', 'status', 'priority', 'progress', 'end_date', 'created_at'], true)
            ? $request->get('sort_by')
            : 'created_at';
        $sortOrder = strtolower((string) $request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $projects = (clone $filteredQuery)
            ->with(['customer', 'assignedAdmin'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'on_hold' => (clone $baseQuery)->where('status', 'on_hold')->count(),
        ];

        $statusCounts = [
            'all' => $stats['total'],
            'pending' => $stats['pending'],
            'in_progress' => $stats['in_progress'],
            'on_hold' => $stats['on_hold'],
            'completed' => $stats['completed'],
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        $filteredTotal = (clone $filteredQuery)->count();
        $customers = Customer::forCurrentOrganization()->orderBy('name')->get(['id', 'name']);
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.projects.index', compact('projects', 'stats', 'customers', 'admins', 'statusCounts', 'filteredTotal'));
    }

    private function filteredProjectQuery(Request $request)
    {
        return Project::forCurrentOrganization()
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->priority, fn ($q, $priority) => $q->where('priority', $priority))
            ->when($request->assigned_to === 'me', fn ($q) => $q->where('assigned_to', auth('admin')->id()))
            ->when($request->assigned_to === 'unassigned', fn ($q) => $q->whereNull('assigned_to'))
            ->when(
                $request->filled('assigned_to') && ! in_array($request->assigned_to, ['me', 'unassigned'], true),
                fn ($q) => $q->where('assigned_to', (int) $request->assigned_to)
            )
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%");
                });
            });
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
        $this->assertAssigneeBelongsToOrganization($request->validated('assigned_to'));

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
        $this->assertAssigneeBelongsToOrganization($request->validated('assigned_to'));
        $project->update($request->validated());

        return redirect()->route('admin.crm.projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function inlineUpdate(InlineUpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $field = $request->validated('field');
        $value = $request->input('value');
        $progressBefore = (int) $project->progress;

        if ($field === 'status') {
            $project->update(['status' => $value]);

            return response()->json([
                'ok' => true,
                'field' => $field,
                'value' => $value,
                'tone' => CrmStatusTone::for((string) $value),
                'progress' => (int) $project->fresh()->progress,
                'message' => 'Status updated.',
            ]);
        }

        if ($field === 'priority') {
            $project->update(['priority' => $value]);

            return response()->json([
                'ok' => true,
                'field' => $field,
                'value' => $value,
                'tone' => CrmStatusTone::for((string) $value),
                'progress' => $progressBefore,
                'message' => 'Priority updated.',
            ]);
        }

        if ($value === null || $value === '') {
            $project->update(['assigned_to' => null]);

            return response()->json([
                'ok' => true,
                'field' => $field,
                'value' => null,
                'tone' => 'neutral',
                'progress' => $progressBefore,
                'message' => 'Owner cleared.',
            ]);
        }

        $assignee = Admin::forCurrentOrganization()->findOrFail((int) $value);
        $project->update(['assigned_to' => $assignee->id]);

        return response()->json([
            'ok' => true,
            'field' => $field,
            'value' => $assignee->id,
            'tone' => 'neutral',
            'label' => $assignee->name,
            'progress' => $progressBefore,
            'message' => 'Owner updated.',
        ]);
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
        if ($request->input('assigned_to') === '') {
            $request->merge(['assigned_to' => null]);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => ['nullable', 'integer', CrmOrgRules::adminId()],
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
        if ($request->input('assigned_to') === '') {
            $request->merge(['assigned_to' => null]);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => ['nullable', 'integer', CrmOrgRules::adminId()],
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

    private function assertAssigneeBelongsToOrganization(mixed $assignedTo): void
    {
        if ($assignedTo === null || $assignedTo === '') {
            return;
        }

        Admin::forCurrentOrganization()->findOrFail((int) $assignedTo);
    }

    public function destroyTask(Project $project, int $task): RedirectResponse
    {
        $this->authorize('update', $project);
        $project->tasks()->where('id', $task)->delete();
        $project->recalculateProgress();

        return back()->with('success', 'Task removed.');
    }
}
