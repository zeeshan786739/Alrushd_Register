@extends('admin.layouts.app')
@section('title', 'Quotations')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.crm.partials.list-workspace-styles', [
    'pageId' => 'crm-quotations-page',
    'gridColumns' => 'minmax(150px,1fr) minmax(110px,.8fr) minmax(100px,.75fr) minmax(96px,.62fr) minmax(88px,.55fr) minmax(88px,.55fr) minmax(96px,.62fr) 76px',
    'statusGridCols' => 6,
])
@php $activeFilters = collect(request()->only(['search', 'status', 'customer_id']))->filter(fn ($v) => $v !== null && $v !== '')->count(); @endphp
<div class="dashboard-main-body" id="crm-quotations-page" data-crm-list-workspace>
    @include('admin.partials.page-header', [
        'title' => 'Quotations',
        'subtitle' => 'Prepare, send, and convert commercial proposals in one workspace',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Quotations']],
        'actions' => auth('admin')->user()?->can('create quotations') ? [['label' => 'Create Quotation', 'url' => route('admin.crm.quotations.create'), 'icon' => 'solar:add-circle-linear', 'class' => 'btn-primary-600 radius-8 px-20 py-11']] : [],
    ])
    <div class="crm-list-workspace-shell crm-workspace-shell">
        @include('admin.crm.quotations.partials.metrics-strip', ['stats' => $stats])
        @include('admin.crm.quotations.partials.filter-workspace', compact('customers', 'statusCounts'))
        <div class="crm-leads-toolbar">
            <div class="crm-leads-toolbar__meta">
                <strong>{{ number_format($filteredTotal ?? $quotations->total()) }} matching</strong>
                @if($activeFilters > 0)<span class="crm-leads-toolbar__filters">{{ $activeFilters }} filter{{ $activeFilters === 1 ? '' : 's' }}</span>@endif
                @if($quotations->total() > 0)<span>{{ $quotations->firstItem() }}–{{ $quotations->lastItem() }} of {{ number_format($quotations->total()) }}</span>@endif
            </div>
        </div>
        <div class="crm-list-shell">
            <div class="crm-leads-table">
                <div class="crm-leads-table__head" aria-hidden="true">
                    <span>Quotation</span><span>Customer</span><span>Project</span><span>Status</span><span>Total</span><span>Issued</span><span>Validity</span><span></span>
                </div>
                <div class="crm-leads-list">
                    @forelse($quotations as $quotation)
                        @include('admin.crm.quotations.partials.list-row', compact('quotation'))
                    @empty
                        <div class="crm-leads-list-empty"><iconify-icon icon="solar:document-linear"></iconify-icon><strong>No quotations found</strong><span>Adjust filters or create a quotation.</span></div>
                    @endforelse
                </div>
            </div>
        </div>
        @include('admin.crm.partials.list-workspace-pagination', ['paginator' => $quotations, 'routeName' => 'admin.crm.quotations.index', 'entityLabel' => 'quotations', 'paginationId' => 'crm-quotations-per-page'])
    </div>
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-list-workspace.js') }}"></script>
@endsection
