@extends('admin.layouts.app')
@section('title', 'Customers')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.crm.customers.partials.premium-styles')
@php
    $customerStatuses = ['active' => 'Active', 'inactive' => 'Inactive', 'prospect' => 'Prospect'];
    $statusInlineOptions = [];
    foreach ($customerStatuses as $value => $label) {
        $statusInlineOptions[$value] = [
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
    $activeFilters = collect(request()->only(['search', 'status', 'assigned_to', 'source']))
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->count();
@endphp
<div class="dashboard-main-body" id="crm-customers-page"
     data-inline-url-template="{{ url('admin/crm/customers') }}/__ID__/inline">
    @include('admin.partials.page-header', [
        'title' => 'Customers',
        'subtitle' => 'Relationships, lifetime value, and account health in one workspace',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Customers']],
        'actions' => auth('admin')->user()?->can('create customers') ? [[
            'label' => 'Add Customer',
            'url' => route('admin.crm.customers.create'),
            'icon' => 'solar:add-circle-linear',
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
        ]] : [],
    ])

    <div class="crm-customers-workspace crm-workspace-shell">
        @include('admin.crm.customers.partials.metrics-strip', ['stats' => $stats])

        @include('admin.crm.customers.partials.filter-workspace', [
            'statusCounts' => $statusCounts ?? [],
            'admins' => $admins,
        ])

        <div class="crm-leads-toolbar">
            <div class="crm-leads-toolbar__left">
                <div class="crm-leads-toolbar__meta">
                    <strong>{{ number_format($filteredTotal ?? $customers->total()) }} matching</strong>
                    @if($activeFilters > 0)
                        <span class="crm-leads-toolbar__filters">{{ $activeFilters }} filter{{ $activeFilters === 1 ? '' : 's' }}</span>
                    @endif
                    @if($customers->total() > 0)
                        <span>{{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ number_format($customers->total()) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="crm-customers-list-shell">
            <div class="crm-leads-table">
                <div class="crm-leads-table__head" aria-hidden="true">
                    <span>Customer</span>
                    <span>Status</span>
                    <span>Owner</span>
                    <span>Lifetime value</span>
                    <span>Added</span>
                    <span></span>
                </div>

                <div class="crm-leads-list" data-crm-customers-list>
                    @forelse($customers as $customer)
                        @include('admin.crm.customers.partials.list-row', [
                            'customer' => $customer,
                            'statusInlineOptions' => $statusInlineOptions,
                            'ownerInlineOptions' => $ownerInlineOptions,
                        ])
                    @empty
                        <div class="crm-leads-list-empty">
                            <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                            <strong>No customers found</strong>
                            <span>Adjust your filters or add a new customer to populate this list.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @include('admin.crm.customers.partials.pagination', ['paginator' => $customers])
    </div>

    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>
</div>
@endsection

@section('script')
<script src="{{ asset('admin/assets/js/crm-inline.js') }}"></script>
<script src="{{ asset('admin/assets/js/crm-customers.js') }}?v={{ filemtime(public_path('admin/assets/js/crm-customers.js')) }}"></script>
@endsection
