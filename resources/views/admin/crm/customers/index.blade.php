@extends('admin.layouts.app')
@section('title', 'Customers')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
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
    @include('admin.partials.filter-bar', ['action'=>route('admin.crm.customers.index'),'resetUrl'=>route('admin.crm.customers.index'),'fields'=>[
        ['name'=>'search','label'=>'Search','placeholder'=>'Name, email, company'],
        ['name'=>'status','label'=>'Status','type'=>'select','options'=>['active'=>'Active','inactive'=>'Inactive','prospect'=>'Prospect']],
    ]])
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Status</th><th>Lifetime Value</th><th></th></tr></thead>
            <tbody>@forelse($customers as $customer)
                <tr><td><a href="{{ route('admin.crm.customers.show',$customer) }}" class="fw-semibold">{{ $customer->name }}</a></td><td>{{ $customer->email }}</td><td>{{ $customer->company ?? '—' }}</td><td>@include('admin.crm.partials.status-pill',['status'=>$customer->status])</td><td>{{ number_format($customer->lifetime_value,2) }}</td>
                <td>@include('admin.partials.table-actions',['viewUrl'=>route('admin.crm.customers.show',$customer),'editUrl'=>auth('admin')->user()?->can('update customers')?route('admin.crm.customers.edit',$customer):null,'deleteId'=>$customer->id,'deleteRoute'=>route('admin.crm.customers.destroy',$customer),'canDelete'=>auth('admin')->user()?->can('delete customers')])</td></tr>
            @empty<tr><td colspan="6" class="text-center py-40 text-secondary-light">No customers found.</td></tr>@endforelse</tbody>
        </table></div></div>
    </div>
    <div class="mt-24">{{ $customers->links() }}</div>
</div>
@endsection
