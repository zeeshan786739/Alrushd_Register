@extends('admin.layouts.app')
@section('title', $project->name)
@section('content')
@include('admin.crm.partials.styles')
@php
    $due = \App\Support\ProjectDueState::forProject($project);
    $quotations = $project->quotations->sortByDesc('created_at');
    $invoices = $project->invoices->sortByDesc('created_at');
    $tasks = $project->tasks->sortBy(fn ($t) => $t->status === 'completed' ? 1 : 0)->values();
@endphp
<div class="dashboard-main-body">
    <div class="crm-workspace-header mb-20">
        <div class="crm-workspace-header__top">
            <div class="min-w-0">
                <div class="text-sm text-secondary-light mb-4">
                    <a href="{{ route('admin.crm.projects.index') }}" class="text-secondary-light text-decoration-none">Projects</a>
                    <span class="mx-4">/</span>
                    <span>{{ $project->name }}</span>
                </div>
                <h1 class="crm-workspace-header__title">{{ $project->name }}</h1>
                <div class="crm-lead-meta mb-8">{{ $project->project_code }}</div>
                <div class="crm-workspace-header__contact">
                    @if($project->customer)
                        <span><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                            <a href="{{ route('admin.crm.customers.show', $project->customer) }}">{{ $project->customer->name }}</a>
                        </span>
                        @if($project->customer->email)
                            <span><iconify-icon icon="solar:letter-linear"></iconify-icon> {{ $project->customer->email }}</span>
                        @endif
                        @if($project->customer->phone)
                            <span><iconify-icon icon="solar:phone-linear"></iconify-icon> {{ $project->customer->phone }}</span>
                        @endif
                    @endif
                    <span><iconify-icon icon="solar:user-linear"></iconify-icon> {{ $project->assignedAdmin?->name ?? 'Unassigned' }}</span>
                    @if($project->start_date)
                        <span><iconify-icon icon="solar:calendar-linear"></iconify-icon> Start {{ $project->start_date->format('M j, Y') }}</span>
                    @endif
                    @if($project->budget)
                        <span><iconify-icon icon="solar:wallet-money-linear"></iconify-icon> Budget {{ number_format((float) $project->budget, 2) }}</span>
                    @endif
                </div>
                <div class="crm-workspace-header__badges">
                    @include('admin.crm.partials.status-pill', ['status'=>$project->status])
                    @include('admin.crm.partials.status-pill', ['status'=>$project->priority])
                    <span class="{{ $due->badgeClass }}">{{ $due->label }}</span>
                </div>
            </div>
            <div class="crm-workspace-header__actions">
                @can('update projects')
                    <a href="{{ route('admin.crm.projects.edit', $project) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:pen-linear"></iconify-icon> Edit</a>
                    <a href="#project-tasks" class="btn btn-outline-neutral-500 radius-8 px-16 py-10"><iconify-icon icon="solar:checklist-linear"></iconify-icon> Add Task</a>
                @endcan
                @if($project->customer)
                    <a href="{{ route('admin.crm.customers.show', $project->customer) }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10"><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon> Customer</a>
                @endif
                @can('create quotations')
                    <a href="{{ route('admin.crm.quotations.create', ['customer_id'=>$project->customer_id,'project_id'=>$project->id]) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:document-text-linear"></iconify-icon> Quotation</a>
                @endcan
                @can('create invoices')
                    <a href="{{ route('admin.crm.invoices.create', ['customer_id'=>$project->customer_id,'project_id'=>$project->id]) }}" class="btn btn-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:bill-list-linear"></iconify-icon> Invoice</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row g-3 mb-20">
        <div class="col-lg-8">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:chart-linear"></iconify-icon> Progress</h6>
                    <div class="d-flex justify-content-between align-items-center mb-8">
                        <span class="fw-semibold">{{ (int) $project->progress }}% complete</span>
                        <span class="crm-lead-meta">{{ $project->tasks->where('status','completed')->count() }} / {{ $project->tasks->count() }} tasks done</span>
                    </div>
                    <div class="progress crm-progress-bar mb-16" style="height:12px">
                        <div class="progress-bar bg-primary-600" role="progressbar" style="width:{{ (int) $project->progress }}%"></div>
                    </div>
                    <p class="mb-0 text-sm">{{ $project->description ?: 'No description provided.' }}</p>
                    @if($project->notes)
                        <div class="mt-12 pt-12 border-top">
                            <div class="crm-lead-meta mb-4">Notes</div>
                            <div class="text-sm">{{ $project->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon> Customer</h6>
                    @if($project->customer)
                        <a href="{{ route('admin.crm.customers.show', $project->customer) }}" class="fw-semibold d-inline-block mb-8">{{ $project->customer->name }}</a>
                        <div class="crm-lead-meta">{{ $project->customer->company ?: '—' }}</div>
                        <div class="crm-lead-meta mt-8">{{ $project->customer->email ?: 'No email' }}</div>
                        <div class="crm-lead-meta">{{ $project->customer->phone ?: 'No phone' }}</div>
                        <div class="mt-12">@include('admin.crm.partials.status-pill', ['status'=>$project->customer->status])</div>
                    @else
                        <div class="crm-empty-state py-20 mb-0">No customer linked.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-20" id="project-tasks">
        <div class="card-body p-24">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-8 mb-12">
                <h6 class="crm-section-title mb-0"><iconify-icon icon="solar:checklist-linear"></iconify-icon> Tasks</h6>
                <span class="crm-lead-meta">{{ $tasks->count() }} total</span>
            </div>

            <div class="crm-activity-scroll mb-16" style="max-height:560px">
                @forelse($tasks as $task)
                    <div class="crm-task-row {{ $task->status === 'completed' ? 'is-complete' : '' }}">
                        <div class="d-flex justify-content-between gap-12 align-items-start flex-wrap">
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold {{ $task->status === 'completed' ? 'text-decoration-line-through text-secondary-light' : '' }}">{{ $task->name }}</div>
                                <div class="crm-lead-meta mt-4 d-flex flex-wrap gap-8 align-items-center">
                                    @include('admin.crm.partials.status-pill', ['status'=>$task->status])
                                    @include('admin.crm.partials.status-pill', ['status'=>$task->priority])
                                    <span>{{ $task->assignedAdmin?->name ?? 'Unassigned' }}</span>
                                    <span>Due {{ $task->due_date?->format('M j, Y') ?? '—' }}</span>
                                </div>
                                @if($task->description)
                                    <div class="text-sm mt-6">{{ $task->description }}</div>
                                @endif
                            </div>
                            @can('update projects')
                            <div class="d-flex flex-wrap gap-8">
                                @if($task->status !== 'completed')
                                    <form method="POST" action="{{ route('admin.crm.projects.tasks.update', [$project, $task->id]) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="name" value="{{ $task->name }}">
                                        <input type="hidden" name="description" value="{{ $task->description }}">
                                        <input type="hidden" name="assigned_to" value="{{ $task->assigned_to }}">
                                        <input type="hidden" name="status" value="completed">
                                        <input type="hidden" name="priority" value="{{ $task->priority }}">
                                        <input type="hidden" name="due_date" value="{{ optional($task->due_date)->format('Y-m-d') }}">
                                        <input type="hidden" name="estimated_hours" value="{{ $task->estimated_hours }}">
                                        <input type="hidden" name="actual_hours" value="{{ $task->actual_hours }}">
                                        <button class="btn btn-sm btn-outline-success radius-8" title="Mark complete">Complete</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.crm.projects.tasks.update', [$project, $task->id]) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="name" value="{{ $task->name }}">
                                        <input type="hidden" name="description" value="{{ $task->description }}">
                                        <input type="hidden" name="assigned_to" value="{{ $task->assigned_to }}">
                                        <input type="hidden" name="status" value="pending">
                                        <input type="hidden" name="priority" value="{{ $task->priority }}">
                                        <input type="hidden" name="due_date" value="{{ optional($task->due_date)->format('Y-m-d') }}">
                                        <input type="hidden" name="estimated_hours" value="{{ $task->estimated_hours }}">
                                        <input type="hidden" name="actual_hours" value="{{ $task->actual_hours }}">
                                        <button class="btn btn-sm btn-outline-neutral-500 radius-8" title="Reopen task">Reopen</button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-primary-600 radius-8" data-crm-task-edit="{{ $task->id }}">Edit</button>
                                <form method="POST" action="{{ route('admin.crm.projects.tasks.destroy', [$project, $task->id]) }}" onsubmit="return confirm('Remove this task?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger radius-8">Delete</button>
                                </form>
                            </div>
                            @endcan
                        </div>

                        @can('update projects')
                        <form method="POST"
                              action="{{ route('admin.crm.projects.tasks.update', [$project, $task->id]) }}"
                              class="crm-task-edit-form mt-12 pt-12 border-top d-none"
                              data-crm-task-form="{{ $task->id }}">
                            @csrf @method('PUT')
                            <div class="row g-2">
                                <div class="col-md-4"><input name="name" class="form-control radius-8" value="{{ $task->name }}" required></div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select radius-8">
                                        @foreach(['pending','in_progress','completed','cancelled'] as $status)
                                            <option value="{{ $status }}" @selected($task->status === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="priority" class="form-select radius-8">
                                        @foreach(['low','medium','high','urgent'] as $priority)
                                            <option value="{{ $priority }}" @selected($task->priority === $priority)>{{ ucfirst($priority) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="assigned_to" class="form-select radius-8">
                                        <option value="">Unassigned</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}" @selected((int) $task->assigned_to === (int) $admin->id)>{{ $admin->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2"><input type="date" name="due_date" class="form-control radius-8" value="{{ optional($task->due_date)->format('Y-m-d') }}"></div>
                                <div class="col-12"><textarea name="description" class="form-control radius-8" rows="2" placeholder="Description">{{ $task->description }}</textarea></div>
                                <div class="col-md-3"><input type="number" step="0.01" min="0" name="estimated_hours" class="form-control radius-8" value="{{ $task->estimated_hours }}" placeholder="Est. hours"></div>
                                <div class="col-md-3"><input type="number" step="0.01" min="0" name="actual_hours" class="form-control radius-8" value="{{ $task->actual_hours }}" placeholder="Actual hours"></div>
                                <div class="col-md-6 d-flex gap-8">
                                    <button class="btn btn-primary-600 radius-8 px-16 py-8">Save Task</button>
                                    <button type="button" class="btn btn-outline-neutral-500 radius-8 px-16 py-8" data-crm-task-cancel="{{ $task->id }}">Cancel</button>
                                </div>
                            </div>
                        </form>
                        @endcan
                    </div>
                @empty
                    <div class="crm-empty-state"><iconify-icon icon="solar:checklist-linear"></iconify-icon>No tasks yet. Add the first delivery task below.</div>
                @endforelse
            </div>

            @can('update projects')
            <form method="POST" action="{{ route('admin.crm.projects.tasks.store', $project) }}" class="border-top pt-16">
                @csrf
                <div class="row g-2">
                    <div class="col-md-3"><input name="name" class="form-control radius-8" placeholder="Task name *" required></div>
                    <div class="col-md-2">
                        <select name="status" class="form-select radius-8">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="priority" class="form-select radius-8">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="assigned_to" class="form-select radius-8">
                            <option value="">Unassigned</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><input type="date" name="due_date" class="form-control radius-8"></div>
                    <div class="col-md-1"><button class="btn btn-primary-600 radius-8 w-100">Add</button></div>
                    <div class="col-12"><textarea name="description" class="form-control radius-8" rows="2" placeholder="Optional description"></textarea></div>
                </div>
            </form>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-20">
        <div class="col-lg-6">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-8 mb-12">
                        <h6 class="crm-section-title mb-0"><iconify-icon icon="solar:document-text-linear"></iconify-icon> Quotations</h6>
                        @can('create quotations')
                            <a href="{{ route('admin.crm.quotations.create', ['customer_id'=>$project->customer_id,'project_id'=>$project->id]) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Create Quotation</a>
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 crm-relation-table">
                            <thead><tr><th>Quote</th><th>Status</th><th>Total</th><th>Dates</th></tr></thead>
                            <tbody>
                            @forelse($quotations as $quotation)
                                <tr>
                                    <td><a href="{{ route('admin.crm.quotations.show', $quotation) }}">{{ $quotation->quotation_number }}</a></td>
                                    <td>@include('admin.crm.partials.status-pill', ['status'=>$quotation->status])</td>
                                    <td class="fw-semibold">{{ number_format((float) $quotation->total, 2) }}</td>
                                    <td>
                                        <div>{{ $quotation->quotation_date?->format('M j, Y') ?? '—' }}</div>
                                        <div class="crm-lead-meta">Valid {{ $quotation->valid_until?->format('M j, Y') ?? '—' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="crm-empty-state py-20 mb-0">No quotations for this project.</div></td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-8 mb-12">
                        <h6 class="crm-section-title mb-0"><iconify-icon icon="solar:bill-list-linear"></iconify-icon> Invoices</h6>
                        @can('create invoices')
                            <a href="{{ route('admin.crm.invoices.create', ['customer_id'=>$project->customer_id,'project_id'=>$project->id]) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Create Invoice</a>
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 crm-relation-table">
                            <thead><tr><th>Invoice</th><th>Status</th><th>Total</th><th>Paid / Due</th></tr></thead>
                            <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td><a href="{{ route('admin.crm.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                    <td>@include('admin.crm.partials.status-pill', ['status'=>$invoice->status])</td>
                                    <td class="fw-semibold">{{ number_format((float) $invoice->total, 2) }}</td>
                                    <td>
                                        <div>{{ number_format((float) $invoice->paid_amount, 2) }} paid</div>
                                        <div class="crm-lead-meta">{{ number_format((float) $invoice->due_amount, 2) }} due · {{ $invoice->due_date?->format('M j, Y') ?? '—' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="crm-empty-state py-20 mb-0">No invoices for this project.</div></td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function () {
    document.querySelectorAll('[data-crm-task-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-crm-task-edit');
            var form = document.querySelector('[data-crm-task-form="' + id + '"]');
            if (form) form.classList.toggle('d-none');
        });
    });
    document.querySelectorAll('[data-crm-task-cancel]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-crm-task-cancel');
            var form = document.querySelector('[data-crm-task-form="' + id + '"]');
            if (form) form.classList.add('d-none');
        });
    });
})();
</script>
@endsection
