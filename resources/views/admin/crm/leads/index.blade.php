@extends('admin.layouts.app')
@section('title', 'Leads')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body crm-list-view" id="crm-leads-page">
    @include('admin.partials.page-header', [
        'title' => 'Leads',
        'subtitle' => 'Manage and track sales leads',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Leads']],
        'actions' => array_filter([
            auth('admin')->user()?->can('export leads') ? ['label' => 'Export', 'url' => route('admin.crm.leads.export', request()->query()), 'icon' => 'solar:export-linear', 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'] : null,
            auth('admin')->user()?->can('create leads') ? ['label' => 'Add Lead', 'url' => route('admin.crm.leads.create'), 'icon' => 'solar:add-circle-linear', 'class' => 'btn-primary-600 radius-8 px-20 py-11'] : null,
        ]),
    ])

    <div class="row g-3 mb-24">
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Total Leads','value'=>$stats['total'],'icon'=>'solar:users-group-rounded-linear','tone'=>'navy'])</div>
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'New','value'=>$stats['new'],'icon'=>'solar:user-plus-linear','tone'=>'green'])</div>
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Won','value'=>$stats['won'],'icon'=>'solar:cup-star-linear','tone'=>'gold'])</div>
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Follow-up Today','value'=>$stats['follow_up_today'],'icon'=>'solar:calendar-linear','tone'=>'amber','badge'=>($stats['monthly_change']>=0?'+':'').$stats['monthly_change'].'%','badgeClass'=>$stats['monthly_change']>=0?'crm-stat-badge--up':'crm-stat-badge--down','footer'=>'vs last month'])</div>
    </div>

    @include('admin.partials.filter-bar', [
        'action' => route('admin.crm.leads.index'),
        'resetUrl' => route('admin.crm.leads.index'),
        'fields' => [
            ['name'=>'search','label'=>'Search','placeholder'=>'Name, email, phone'],
            ['name'=>'lead_status','label'=>'Status','type'=>'select','options'=>collect(\App\Enums\LeadStatus::cases())->mapWithKeys(fn($s)=>[$s->value=>str_replace('_',' ',$s->value)])->all()],
            ['name'=>'priority','label'=>'Priority','type'=>'select','options'=>collect(\App\Enums\LeadPriority::cases())->mapWithKeys(fn($p)=>[$p->value=>ucfirst($p->value)])->all()],
            ['name'=>'assigned_to','label'=>'Assigned To','type'=>'select','options'=>$admins->pluck('name','id')->all()],
        ],
    ])

    <div class="d-flex justify-content-end mb-16">
        <div class="crm-view-toggle" data-crm-view-toggle>
            <button type="button" data-view="list" class="is-active" title="List view"><iconify-icon icon="solar:list-linear"></iconify-icon></button>
            <button type="button" data-view="grid" title="Grid view"><iconify-icon icon="solar:widget-4-linear"></iconify-icon></button>
        </div>
    </div>

    @can('view leads')
    @if($savedFilters->isNotEmpty())
    <div class="mb-16 d-flex flex-wrap gap-8 align-items-center">
        <span class="text-sm text-secondary-light">Saved filters:</span>
        @foreach($savedFilters as $filter)
            <a href="{{ route('admin.crm.leads.index', $filter->filters) }}" class="btn btn-sm btn-outline-primary-600 radius-8">{{ $filter->name }}</a>
        @endforeach
    </div>
    @endif
    @endcan

    <div class="card radius-12 shadow-2 border-0 crm-list-only">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Status</th><th>Priority</th><th>Assigned</th><th>Follow-up</th><th>Created</th><th></th></tr></thead>
                    <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td><a href="{{ route('admin.crm.leads.show', $lead) }}" class="fw-semibold">{{ $lead->full_name }}</a><br><span class="text-sm text-secondary-light">{{ $lead->email ?? '—' }}</span></td>
                            <td>@include('admin.crm.partials.status-pill', ['status'=>$lead->lead_status])</td>
                            <td>@include('admin.crm.partials.status-pill', ['status'=>$lead->priority])</td>
                            <td>{{ $lead->assignedAdmin?->name ?? '—' }}</td>
                            <td>{{ $lead->next_follow_up_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $lead->created_at->format('M j, Y') }}</td>
                            <td>@include('admin.partials.table-actions', ['viewUrl'=>route('admin.crm.leads.show',$lead),'editUrl'=>auth('admin')->user()?->can('update leads')?route('admin.crm.leads.edit',$lead):null,'deleteId'=>$lead->id,'deleteRoute'=>route('admin.crm.leads.destroy',$lead),'canDelete'=>auth('admin')->user()?->can('delete leads')])</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-40 text-secondary-light">No leads found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="crm-grid-only crm-card-grid mb-24">
        @foreach($leads as $lead)
            <div class="crm-record-card">
                <div class="d-flex justify-content-between align-items-start mb-8">
                    <div><a href="{{ route('admin.crm.leads.show', $lead) }}" class="fw-semibold text-lg">{{ $lead->full_name }}</a><br><span class="text-sm text-secondary-light">{{ $lead->email ?? '—' }}</span></div>
                    @include('admin.crm.partials.status-pill', ['status'=>$lead->lead_status])
                </div>
                <div class="text-sm text-secondary-light mb-12">Priority: {{ ucfirst($lead->priority) }} · {{ $lead->assignedAdmin?->name ?? 'Unassigned' }}</div>
                <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary-600 radius-8">View Details</a>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.crm.leads.filters.save') }}" class="card radius-12 border-0 shadow-2 mb-24" id="crm-save-filter-form">
        @csrf
        <div class="card-body p-20 d-flex flex-wrap gap-12 align-items-end">
            <div class="flex-grow-1"><label class="form-label text-sm">Save current filter</label><input type="text" name="name" class="form-control radius-8" placeholder="Filter name" required></div>
            @foreach(request()->only(['search','lead_status','priority','assigned_to','sort_by','sort_order']) as $key=>$value)
                @if($value)<input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">@endif
            @endforeach
            <button type="submit" class="btn btn-outline-primary-600 radius-8 px-20 py-11">Save Filter</button>
        </div>
    </form>

    {{ $leads->links() }}
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-leads.js') }}"></script>
@endsection
