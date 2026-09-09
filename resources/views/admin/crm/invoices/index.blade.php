@extends('admin.layouts.app')
@section('title', 'Invoices')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.crm.partials.list-workspace-styles', [
    'pageId' => 'crm-invoices-page',
    'gridColumns' => 'minmax(150px,1fr) minmax(110px,.8fr) minmax(96px,.62fr) minmax(88px,.55fr) minmax(88px,.55fr) minmax(88px,.55fr) minmax(96px,.62fr) 76px',
    'statusGridCols' => 6,
])
@php $activeFilters = collect(request()->only(['search', 'status', 'customer_id']))->filter(fn ($v) => $v !== null && $v !== '')->count(); @endphp
<div class="dashboard-main-body" id="crm-invoices-page" data-crm-list-workspace>
    @include('admin.partials.page-header', [
        'title' => 'Invoices',
        'subtitle' => 'Billing, collections, and outstanding balances in one workspace',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Invoices']],
        'actions' => auth('admin')->user()?->can('create invoices') ? [['label' => 'Create Invoice', 'url' => route('admin.crm.invoices.create'), 'icon' => 'solar:add-circle-linear', 'class' => 'btn-primary-600 radius-8 px-20 py-11']] : [],
    ])
    <div class="crm-list-workspace-shell crm-workspace-shell">
        @include('admin.crm.invoices.partials.metrics-strip', ['stats' => $stats])
        @include('admin.crm.invoices.partials.filter-workspace', compact('customers', 'statusCounts'))
        <div class="crm-leads-toolbar">
            <div class="crm-leads-toolbar__meta">
                <strong>{{ number_format($filteredTotal ?? $invoices->total()) }} matching</strong>
                @if($activeFilters > 0)<span class="crm-leads-toolbar__filters">{{ $activeFilters }} filter{{ $activeFilters === 1 ? '' : 's' }}</span>@endif
                @if($invoices->total() > 0)<span>{{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ number_format($invoices->total()) }}</span>@endif
            </div>
        </div>
        <div class="crm-list-shell">
            <div class="crm-leads-table">
                <div class="crm-leads-table__head" aria-hidden="true">
                    <span>Invoice</span><span>Customer</span><span>Status</span><span>Total</span><span>Outstanding</span><span>Issued</span><span>Due</span><span></span>
                </div>
                <div class="crm-leads-list">
                    @forelse($invoices as $invoice)
                        @include('admin.crm.invoices.partials.list-row', compact('invoice'))
                    @empty
                        <div class="crm-leads-list-empty"><iconify-icon icon="solar:bill-list-linear"></iconify-icon><strong>No invoices found</strong><span>Adjust filters or create an invoice.</span></div>
                    @endforelse
                </div>
            </div>
        </div>
        @include('admin.crm.partials.list-workspace-pagination', ['paginator' => $invoices, 'routeName' => 'admin.crm.invoices.index', 'entityLabel' => 'invoices', 'paginationId' => 'crm-invoices-per-page'])
    </div>
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-list-workspace.js') }}"></script>
@endsection
