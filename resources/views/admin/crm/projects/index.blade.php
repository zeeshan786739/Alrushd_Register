@extends('admin.layouts.app')
@section('title', 'Projects')
@section('content')
@include('admin.crm.partials.styles')
@php
    $canUpdateProjects = auth('admin')->user()?->can('update projects');
    $projectStatuses = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
    $projectPriorities = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];
    $statusInlineOptions = [];
    foreach ($projectStatuses as $value => $label) {
        $statusInlineOptions[$value] = [
            'label' => $label,
            'tone' => \App\Support\CrmStatusTone::for($value),
            'icon' => \App\Support\CrmStatusTone::icon($value),
        ];
    }
    $priorityInlineOptions = [];
    foreach ($projectPriorities as $value => $label) {
        $priorityInlineOptions[$value] = [
            'label' => $label,
            'tone' => \App\Support\CrmStatusTone::for($value),
            'icon' => \App\Support\CrmStatusTone::icon($value),
        ];
    }
    $ownerInlineOptions = ['' => ['label' => 'Unassigned', 'tone' => 'neutral', 'icon' => 'solar:user-linear']];
    foreach ($admins as $admin) {
        $ownerInlineOptions[(string) $admin->id] = [
            'label' => $admin->name,
            'tone' => 'neutral',
            'icon' => 'solar:user-linear',
        ];
    }
@endphp
<div class="dashboard-main-body" id="crm-projects-page"
     data-inline-url-template="{{ url('admin/crm/projects') }}/__ID__/inline">
    @include('admin.partials.page-header', [
        'title' => 'Projects',
        'subtitle' => 'Track delivery work and related commercial records',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'CRM'],['label'=>'Projects']],
        'actions' => auth('admin')->user()?->can('create projects')
            ? [['label'=>'Add Project','url'=>route('admin.crm.projects.create'),'icon'=>'solar:add-circle-linear','class'=>'btn-primary-600 radius-8 px-20 py-11']]
            : [],
    ])

    <div class="row g-3 mb-24">
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Total','value'=>$stats['total'],'icon'=>'solar:folder-linear','tone'=>'navy'])</div>
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'In Progress','value'=>$stats['in_progress'],'icon'=>'solar:play-linear','tone'=>'amber'])</div>
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Completed','value'=>$stats['completed'],'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
    </div>

    @include('admin.partials.filter-bar', [
        'action' => route('admin.crm.projects.index'),
        'resetUrl' => route('admin.crm.projects.index'),
        'fields' => [
            ['name'=>'search','label'=>'Search','placeholder'=>'Project name'],
            ['name'=>'status','label'=>'Status','type'=>'select','options'=>$projectStatuses],
            ['name'=>'customer_id','label'=>'Customer','type'=>'select','options'=>$customers->pluck('name','id')->all()],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Progress</th>
                            <th>Due</th>
                            <th>Owner</th>
                            <th class="text-end pe-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($projects as $project)
                        @php $due = \App\Support\ProjectDueState::forProject($project); @endphp
                        <tr class="crm-clickable-row"
                            tabindex="0"
                            data-href="{{ route('admin.crm.projects.show', $project) }}"
                            aria-label="Open project {{ $project->name }}">
                            <td>
                                <div class="crm-lead-name text-truncate" style="max-width:240px" title="{{ $project->name }}">{{ $project->name }}</div>
                                <div class="crm-lead-meta">{{ $project->project_code }}</div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width:180px">{{ $project->customer?->name ?? '—' }}</div>
                            </td>
                            <td>
                                @if($canUpdateProjects)
                                    @include('admin.crm.partials.inline-control', [
                                        'field' => 'status',
                                        'value' => $project->status,
                                        'recordId' => $project->id,
                                        'options' => $statusInlineOptions,
                                        'ariaLabel' => 'Status for '.$project->name,
                                    ])
                                @else
                                    @include('admin.crm.partials.status-pill', ['status'=>$project->status])
                                @endif
                            </td>
                            <td>
                                @if($canUpdateProjects)
                                    @include('admin.crm.partials.inline-control', [
                                        'field' => 'priority',
                                        'value' => $project->priority,
                                        'recordId' => $project->id,
                                        'options' => $priorityInlineOptions,
                                        'ariaLabel' => 'Priority for '.$project->name,
                                    ])
                                @else
                                    @include('admin.crm.partials.status-pill', ['status'=>$project->priority])
                                @endif
                            </td>
                            <td style="min-width:120px">
                                <div class="progress crm-progress-bar mb-4" style="height:8px">
                                    <div class="progress-bar" role="progressbar" style="width:{{ (int) $project->progress }}%" aria-valuenow="{{ (int) $project->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="crm-lead-meta">{{ (int) $project->progress }}%</span>
                            </td>
                            <td>
                                <span class="{{ $due->badgeClass }}">{{ $due->label }}</span>
                            </td>
                            <td>
                                @if($canUpdateProjects)
                                    @include('admin.crm.partials.inline-control', [
                                        'field' => 'assigned_to',
                                        'value' => $project->assigned_to ?? '',
                                        'recordId' => $project->id,
                                        'options' => $ownerInlineOptions,
                                        'owner' => true,
                                        'ariaLabel' => 'Owner for '.$project->name,
                                    ])
                                @else
                                    {{ $project->assignedAdmin?->name ?? '—' }}
                                @endif
                            </td>
                            <td class="text-end pe-20" onclick="event.stopPropagation()">
                                @include('admin.partials.table-actions', [
                                    'viewUrl' => route('admin.crm.projects.show', $project),
                                    'editUrl' => $canUpdateProjects ? route('admin.crm.projects.edit', $project) : null,
                                    'deleteId' => $project->id,
                                    'deleteRoute' => route('admin.crm.projects.destroy', $project),
                                    'canDelete' => auth('admin')->user()?->can('delete projects'),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-40 text-secondary-light">No projects found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-24">{{ $projects->links() }}</div>
    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>
</div>
@endsection

@section('script')
<script src="{{ asset('admin/assets/js/crm-inline.js') }}"></script>
@endsection
