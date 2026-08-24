@extends('admin.layouts.app')
@section('title', 'Leads')
@section('content')
@include('admin.crm.partials.styles')
@php
    $categoryFilterOptions = collect($categories ?? [])->pluck('name', 'id')->all();
    if ((($segments['uncategorized']['total'] ?? 0) > 0) || request('lead_category_id') === 'uncategorized') {
        $categoryFilterOptions = ['uncategorized' => 'Uncategorized'] + $categoryFilterOptions;
    }
    $statusInlineOptions = [];
    foreach (\App\Enums\LeadStatus::cases() as $status) {
        $statusInlineOptions[$status->value] = [
            'label' => $status->label(),
            'tone' => \App\Support\CrmStatusTone::for($status->value),
            'icon' => \App\Support\CrmStatusTone::icon($status->value),
        ];
    }
    $priorityInlineOptions = [];
    foreach (\App\Enums\LeadPriority::cases() as $priority) {
        $priorityInlineOptions[$priority->value] = [
            'label' => $priority->label(),
            'tone' => \App\Support\CrmStatusTone::for($priority->value),
            'icon' => \App\Support\CrmStatusTone::icon($priority->value),
        ];
    }
    $assigneeInlineOptions = ['' => ['label' => 'Unassigned', 'tone' => 'neutral', 'icon' => 'solar:user-linear']];
    foreach ($admins as $admin) {
        $assigneeInlineOptions[(string) $admin->id] = [
            'label' => $admin->name,
            'tone' => 'neutral',
            'icon' => 'solar:user-linear',
        ];
    }
@endphp
<div class="dashboard-main-body crm-list-view" id="crm-leads-page"
     data-inline-url-template="{{ url('admin/crm/leads') }}/__ID__/inline"
     data-filter-clear-url="{{ route('admin.crm.leads.filters.clear') }}"
     data-can-update="{{ auth('admin')->user()?->can('update leads') ? '1' : '0' }}"
     data-can-assign="{{ auth('admin')->user()?->can('assign leads') ? '1' : '0' }}">
    @include('admin.partials.page-header', [
        'title' => 'Leads',
        'subtitle' => 'Manage and track sales leads',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Leads']],
        'actions' => array_filter([
            auth('admin')->user()?->can('import leads') ? ['label' => 'Import History', 'url' => route('admin.crm.leads.import.index'), 'icon' => 'solar:history-linear', 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'] : null,
            auth('admin')->user()?->can('import leads') ? ['label' => 'Import Leads', 'url' => route('admin.crm.leads.import.create'), 'icon' => 'solar:import-linear', 'class' => 'btn-outline-primary-600 radius-8 px-20 py-11'] : null,
            auth('admin')->user()?->can('export leads') ? ['label' => 'Export', 'url' => route('admin.crm.leads.export', request()->query()), 'icon' => 'solar:export-linear', 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'] : null,
            auth('admin')->user()?->can('create leads') ? ['label' => 'Add Lead', 'url' => route('admin.crm.leads.create'), 'icon' => 'solar:add-circle-linear', 'class' => 'btn-primary-600 radius-8 px-20 py-11'] : null,
        ]),
    ])

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-5 g-3 mb-24">
        <div class="col">@include('admin.partials.dashboard-stat-card', ['label'=>'Total Leads','value'=>$stats['total'],'icon'=>'solar:users-group-rounded-linear','tone'=>'navy'])</div>
        <div class="col">@include('admin.partials.dashboard-stat-card', ['label'=>'New','value'=>$stats['new'],'icon'=>'solar:user-plus-linear','tone'=>'green'])</div>
        <div class="col">@include('admin.partials.dashboard-stat-card', ['label'=>'Facebook (7d)','value'=>$stats['facebook_this_week'],'icon'=>'logos:facebook','tone'=>'gold'])</div>
        <div class="col">@include('admin.partials.dashboard-stat-card', ['label'=>'TikTok (7d)','value'=>$stats['tiktok_this_week'],'icon'=>'logos:tiktok-icon','tone'=>'purple'])</div>
        <div class="col">@include('admin.partials.dashboard-stat-card', ['label'=>'Follow-up Today','value'=>$stats['follow_up_today'],'icon'=>'solar:calendar-linear','tone'=>'amber','badge'=>($stats['monthly_change']>=0?'+':'').$stats['monthly_change'].'%','badgeClass'=>$stats['monthly_change']>=0?'crm-stat-badge--up':'crm-stat-badge--down','footer'=>'vs last month'])</div>
    </div>

    @include('admin.crm.leads.partials.segments', ['categories' => $categories ?? collect(), 'segments' => $segments ?? []])

    @include('admin.partials.filter-bar', [
        'action' => route('admin.crm.leads.index'),
        'resetUrl' => route('admin.crm.leads.index'),
        'fields' => array_values(array_filter([
            ['name'=>'search','label'=>'Search','placeholder'=>'Name, email, phone'],
            \App\Support\LeadCategorySchema::ready() ? ['name'=>'lead_category_id','label'=>'Category','type'=>'select','options'=>$categoryFilterOptions] : null,
            ['name'=>'source','label'=>'Source','type'=>'select','options'=>$sourceOptions ?? []],
            ['name'=>'advertising_platform','label'=>'Platform','type'=>'select','options'=>$platformOptions ?? []],
            ['name'=>'campaign_name','label'=>'Campaign','placeholder'=>'Campaign name'],
            ['name'=>'lead_status','label'=>'Status','type'=>'select','options'=>\App\Enums\LeadStatus::options()],
            ['name'=>'priority','label'=>'Priority','type'=>'select','options'=>\App\Enums\LeadPriority::options()],
            ['name'=>'assigned_to','label'=>'Assigned To','type'=>'select','options'=>$admins->pluck('name','id')->all()],
        ])),
    ])

    <div class="d-flex justify-content-end mb-16">
        <div class="crm-view-toggle" data-crm-view-toggle>
            <button type="button" data-view="list" class="is-active" title="List view"><iconify-icon icon="solar:list-linear"></iconify-icon></button>
            <button type="button" data-view="grid" title="Grid view"><iconify-icon icon="solar:widget-4-linear"></iconify-icon></button>
        </div>
    </div>

    @can('view leads')
    @if($savedFilters->isNotEmpty())
    <div class="mb-16 d-flex flex-wrap gap-8 align-items-center" data-saved-filters>
        <span class="text-sm text-secondary-light">Saved filters:</span>
        @foreach($savedFilters as $filter)
            <span class="crm-saved-filter-chip" data-saved-filter-id="{{ $filter->id }}">
                <a href="{{ route('admin.crm.leads.index', $filter->filters) }}" class="crm-saved-filter-chip__link">{{ $filter->name }}</a>
                <button type="button"
                        class="crm-saved-filter-chip__remove"
                        data-crm-remove-filter
                        data-url="{{ route('admin.crm.leads.filters.destroy', $filter) }}"
                        title="Remove saved filter"
                        aria-label="Remove saved filter {{ $filter->name }}">
                    <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                </button>
            </span>
        @endforeach
        @if($savedFilters->count() >= 2)
            <button type="button"
                    class="btn btn-sm btn-outline-neutral-500 radius-8"
                    data-crm-clear-filters>
                Clear all
            </button>
        @endif
    </div>
    @endif
    @endcan

    <div class="card radius-12 shadow-2 border-0 crm-list-only">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assigned</th>
                            <th>Follow-up</th>
                            <th>Created</th>
                            <th class="text-end pe-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($leads as $lead)
                        @php $followUp = \App\Support\LeadFollowUpState::forLead($lead); @endphp
                        <tr class="crm-lead-row"
                            tabindex="0"
                            data-href="{{ route('admin.crm.leads.show', $lead) }}"
                            aria-label="Open lead {{ $lead->full_name }}">
                            <td>
                                <div class="crm-lead-name text-truncate" style="max-width:220px" title="{{ $lead->full_name }}">{{ $lead->full_name }}</div>
                                <div class="crm-lead-meta text-truncate" style="max-width:220px" title="{{ $lead->email ?? $lead->phone }}">{{ $lead->email ?? $lead->phone ?? '—' }}</div>
                                @if($lead->category)
                                    <span class="crm-category-badge crm-category-badge--{{ $lead->category->displayTone() }}" title="{{ $lead->category->name }}">
                                        <iconify-icon icon="{{ $lead->category->displayIcon() }}"></iconify-icon>
                                        {{ $lead->category->name }}
                                    </span>
                                @endif
                            </td>
                            <td>@include('admin.crm.partials.lead-source-badge', ['source'=>$lead->source, 'label'=>$lead->lead_source ?? null])</td>
                            <td>
                                @can('update leads')
                                    @include('admin.crm.partials.inline-control', [
                                        'field' => 'lead_status',
                                        'value' => $lead->lead_status,
                                        'recordId' => $lead->id,
                                        'idAttr' => 'data-lead-id',
                                        'options' => $statusInlineOptions,
                                        'ariaLabel' => 'Status for '.$lead->full_name,
                                    ])
                                @else
                                    @include('admin.crm.partials.status-pill', ['status'=>$lead->lead_status])
                                @endcan
                            </td>
                            <td>
                                @can('update leads')
                                    @include('admin.crm.partials.inline-control', [
                                        'field' => 'priority',
                                        'value' => $lead->priority,
                                        'recordId' => $lead->id,
                                        'idAttr' => 'data-lead-id',
                                        'options' => $priorityInlineOptions,
                                        'ariaLabel' => 'Priority for '.$lead->full_name,
                                    ])
                                @else
                                    @include('admin.crm.partials.status-pill', ['status'=>$lead->priority])
                                @endcan
                            </td>
                            <td>
                                @can('assign leads')
                                    @include('admin.crm.partials.inline-control', [
                                        'field' => 'assigned_to',
                                        'value' => $lead->assigned_to ?? '',
                                        'recordId' => $lead->id,
                                        'idAttr' => 'data-lead-id',
                                        'options' => $assigneeInlineOptions,
                                        'owner' => true,
                                        'ariaLabel' => 'Assignee for '.$lead->full_name,
                                    ])
                                @else
                                    <span class="text-sm">{{ $lead->assignedAdmin?->name ?? '—' }}</span>
                                @endcan
                            </td>
                            <td>
                                @if($followUp->hasFollowUp())
                                    <span class="{{ $followUp->badgeClass }}" title="{{ $followUp->detail }}">
                                        @if($followUp->attention)<span class="crm-followup-dot" aria-hidden="true"></span>@endif
                                        {{ $followUp->label }}
                                    </span>
                                @else
                                    <span class="text-secondary-light">—</span>
                                @endif
                            </td>
                            <td class="text-sm text-secondary-light">{{ $lead->created_at->format('M j, Y') }}</td>
                            <td class="text-end pe-16">@include('admin.partials.table-actions', ['viewUrl'=>route('admin.crm.leads.show',$lead),'editUrl'=>auth('admin')->user()?->can('update leads')?route('admin.crm.leads.edit',$lead):null,'deleteId'=>$lead->id,'deleteRoute'=>route('admin.crm.leads.destroy',$lead),'canDelete'=>auth('admin')->user()?->can('delete leads')])</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-40 text-secondary-light">No leads found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="crm-grid-only crm-card-grid mb-24">
        @foreach($leads as $lead)
            @php $followUp = \App\Support\LeadFollowUpState::forLead($lead); @endphp
            <div class="crm-record-card">
                <div class="d-flex justify-content-between align-items-start mb-8">
                    <div><a href="{{ route('admin.crm.leads.show', $lead) }}" class="fw-semibold text-lg">{{ $lead->full_name }}</a><br><span class="text-sm text-secondary-light">{{ $lead->email ?? '—' }}</span></div>
                    @include('admin.crm.partials.status-pill', ['status'=>$lead->lead_status])
                </div>
                <div class="text-sm text-secondary-light mb-12">
                    @include('admin.crm.partials.lead-source-badge', ['source'=>$lead->source, 'label'=>$lead->lead_source ?? null])
                    @if($lead->category) · {{ $lead->category->name }}@endif
                    · {{ ucfirst($lead->priority) }} · {{ $lead->assignedAdmin?->name ?? 'Unassigned' }}
                </div>
                @if($followUp->hasFollowUp())
                    <div class="mb-12"><span class="{{ $followUp->badgeClass }}">{{ $followUp->label }}</span></div>
                @endif
                <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary-600 radius-8">View Details</a>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.crm.leads.filters.save') }}" class="card radius-12 border-0 shadow-2 mb-24" id="crm-save-filter-form">
        @csrf
        <div class="card-body p-20 d-flex flex-wrap gap-12 align-items-end">
            <div class="flex-grow-1"><label class="form-label text-sm">Save current filter</label><input type="text" name="name" class="form-control radius-8" placeholder="Filter name" required></div>
            @foreach(request()->only(['search','lead_category_id','source','advertising_platform','campaign_name','lead_status','priority','assigned_to','sort_by','sort_order']) as $key=>$value)
                @if($value !== null && $value !== '')<input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">@endif
            @endforeach
            <button type="submit" class="btn btn-outline-primary-600 radius-8 px-20 py-11">Save Filter</button>
        </div>
    </form>

    {{ $leads->links() }}
    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-leads.js') }}"></script>
@endsection
