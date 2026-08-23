@extends('admin.layouts.app')
@section('title', 'Customers')
@section('content')
@include('admin.crm.partials.styles')
@php
    $canUpdateCustomers = auth('admin')->user()?->can('update customers');
    $customerStatuses = ['active' => 'Active', 'inactive' => 'Inactive', 'prospect' => 'Prospect'];
@endphp
<div class="dashboard-main-body" id="crm-customers-page"
     data-inline-url-template="{{ url('admin/crm/customers') }}/__ID__/inline">
    @include('admin.partials.page-header', [
        'title' => 'Customers',
        'subtitle' => 'Manage customer relationships',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'CRM'],['label'=>'Customers']],
        'actions' => auth('admin')->user()?->can('create customers') ? [['label'=>'Add Customer','url'=>route('admin.crm.customers.create'),'icon'=>'solar:add-circle-linear','class'=>'btn-primary-600 radius-8 px-20 py-11']] : [],
    ])

    <div class="row g-3 mb-24">
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Total','value'=>$stats['total'],'icon'=>'solar:users-group-rounded-linear','tone'=>'navy'])</div>
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Active','value'=>$stats['active'],'icon'=>'solar:user-check-linear','tone'=>'green'])</div>
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Prospects','value'=>$stats['prospect'],'icon'=>'solar:user-id-linear','tone'=>'amber'])</div>
    </div>

    @include('admin.partials.filter-bar', [
        'action' => route('admin.crm.customers.index'),
        'resetUrl' => route('admin.crm.customers.index'),
        'fields' => [
            ['name'=>'search','label'=>'Search','placeholder'=>'Name, email, company'],
            ['name'=>'status','label'=>'Status','type'=>'select','options'=>$customerStatuses],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Owner</th>
                            <th>Lifetime Value</th>
                            <th class="text-end pe-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($customers as $customer)
                        <tr class="crm-clickable-row"
                            tabindex="0"
                            data-href="{{ route('admin.crm.customers.show', $customer) }}"
                            aria-label="Open customer {{ $customer->name }}">
                            <td>
                                <div class="crm-lead-name text-truncate" style="max-width:240px" title="{{ $customer->name }}">{{ $customer->name }}</div>
                                <div class="crm-lead-meta text-truncate" style="max-width:240px" title="{{ $customer->company }}">{{ $customer->company ?: '—' }}</div>
                            </td>
                            <td>
                                <div class="crm-lead-meta text-truncate" style="max-width:220px" title="{{ $customer->email }}">{{ $customer->email }}</div>
                                <div class="crm-lead-meta text-truncate" style="max-width:220px">{{ $customer->phone ?: '—' }}</div>
                            </td>
                            <td>
                                @if($canUpdateCustomers)
                                    <select class="form-select form-select-sm crm-inline-select"
                                            data-crm-inline
                                            data-field="status"
                                            data-record-id="{{ $customer->id }}"
                                            data-previous="{{ $customer->status }}"
                                            data-tone="{{ \App\Support\CrmStatusTone::for($customer->status) }}"
                                            aria-label="Status for {{ $customer->name }}">
                                        @foreach($customerStatuses as $value => $label)
                                            <option value="{{ $value }}" data-tone="{{ \App\Support\CrmStatusTone::for($value) }}" @selected($customer->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    @include('admin.crm.partials.status-pill', ['status'=>$customer->status])
                                @endif
                            </td>
                            <td>
                                @if($canUpdateCustomers)
                                    <select class="form-select form-select-sm crm-inline-select crm-inline-select--owner"
                                            data-crm-inline
                                            data-field="assigned_to"
                                            data-record-id="{{ $customer->id }}"
                                            data-previous="{{ $customer->assigned_to }}"
                                            data-tone="neutral"
                                            aria-label="Owner for {{ $customer->name }}">
                                        <option value="">Unassigned</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}" @selected((int) $customer->assigned_to === (int) $admin->id)>{{ $admin->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    {{ $customer->assignedAdmin?->name ?? '—' }}
                                @endif
                            </td>
                            <td class="fw-semibold">{{ number_format((float) $customer->lifetime_value, 2) }}</td>
                            <td class="text-end pe-20" onclick="event.stopPropagation()">
                                @include('admin.partials.table-actions', [
                                    'viewUrl' => route('admin.crm.customers.show', $customer),
                                    'editUrl' => $canUpdateCustomers ? route('admin.crm.customers.edit', $customer) : null,
                                    'deleteId' => $customer->id,
                                    'deleteRoute' => route('admin.crm.customers.destroy', $customer),
                                    'canDelete' => auth('admin')->user()?->can('delete customers'),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-40 text-secondary-light">No customers found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-24">{{ $customers->links() }}</div>
    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>
</div>
@endsection

@section('script')
<script src="{{ asset('admin/assets/js/crm-inline.js') }}"></script>
@endsection
