@extends('admin.layouts.app')
@section('title', 'Projects')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.crm.partials.list-workspace-styles', [
    'pageId' => 'crm-projects-page',
    'gridColumns' => 'minmax(220px,1.8fr) minmax(110px,.75fr) minmax(100px,.65fr) minmax(90px,.58fr) minmax(88px,.52fr) minmax(96px,.62fr) minmax(110px,.72fr) 76px',
    'statusGridCols' => 6,
])
@php
    $projectStatuses = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    $projectPriorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];
    $statusInlineOptions = []; foreach ($projectStatuses as $value => $label) { $statusInlineOptions[$value] = ['label' => $label, 'tone' => \App\Support\CrmStatusTone::for($value), 'icon' => \App\Support\CrmStatusTone::icon($value)]; }
    $priorityInlineOptions = []; foreach ($projectPriorities as $value => $label) { $priorityInlineOptions[$value] = ['label' => $label, 'tone' => \App\Support\CrmStatusTone::for($value), 'icon' => \App\Support\CrmStatusTone::icon($value)]; }
    $ownerInlineOptions = ['' => ['label' => 'Unassigned', 'tone' => 'neutral', 'icon' => 'solar:user-linear']];
    foreach ($admins as $admin) { $ownerInlineOptions[(string) $admin->id] = ['label' => $admin->name, 'tone' => 'neutral', 'icon' => 'solar:user-linear']; }
    $activeFilters = collect(request()->only(['search', 'status', 'customer_id', 'priority', 'assigned_to']))->filter(fn ($v) => $v !== null && $v !== '')->count();
@endphp
<div class="dashboard-main-body" id="crm-projects-page" data-crm-list-workspace data-inline-url-template="{{ url('admin/crm/projects') }}/__ID__/inline">
    @include('admin.partials.page-header', [
        'title' => 'Projects',
        'subtitle' => 'Delivery pipeline, progress, and ownership in one workspace',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Projects']],
        'actions' => auth('admin')->user()?->can('create projects') ? [['label' => 'Add Project', 'url' => route('admin.crm.projects.create'), 'icon' => 'solar:add-circle-linear', 'class' => 'btn-primary-600 radius-8 px-20 py-11']] : [],
    ])
    <div class="crm-list-workspace-shell crm-workspace-shell">
        @include('admin.crm.projects.partials.metrics-strip', ['stats' => $stats])
        @include('admin.crm.projects.partials.filter-workspace', compact('customers', 'admins', 'statusCounts'))
        <div class="crm-leads-toolbar">
            <div class="crm-leads-toolbar__meta">
                <strong>{{ number_format($filteredTotal ?? $projects->total()) }} matching</strong>
                @if($activeFilters > 0)<span class="crm-leads-toolbar__filters">{{ $activeFilters }} filter{{ $activeFilters === 1 ? '' : 's' }}</span>@endif
                @if($projects->total() > 0)<span>{{ $projects->firstItem() }}–{{ $projects->lastItem() }} of {{ number_format($projects->total()) }}</span>@endif
            </div>
        </div>
        <div class="crm-list-shell">
            <div class="crm-leads-table">
                <div class="crm-leads-table__head" aria-hidden="true">
                    <span>Project</span><span>Customer</span><span>Status</span><span>Priority</span><span>Progress</span><span>Due</span><span>Owner</span><span></span>
                </div>
                <div class="crm-leads-list">
                    @forelse($projects as $project)
                        @include('admin.crm.projects.partials.list-row', compact('project', 'statusInlineOptions', 'priorityInlineOptions', 'ownerInlineOptions'))
                    @empty
                        <div class="crm-leads-list-empty"><iconify-icon icon="solar:folder-linear"></iconify-icon><strong>No projects found</strong><span>Adjust filters or add a new project.</span></div>
                    @endforelse
                </div>
            </div>
        </div>
        @include('admin.crm.partials.list-workspace-pagination', ['paginator' => $projects, 'routeName' => 'admin.crm.projects.index', 'entityLabel' => 'projects', 'paginationId' => 'crm-projects-per-page'])
    </div>
    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-inline.js') }}"></script>
<script src="{{ asset('admin/assets/js/crm-list-workspace.js') }}"></script>
@endsection
