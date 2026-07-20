@extends('admin.layouts.app')
@section('title', 'Invoices')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Invoices','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Invoices']],'actions'=>auth('admin')->user()?->can('create invoices')?[['label'=>'Create Invoice','url'=>route('admin.crm.invoices.create'),'icon'=>'solar:add-circle-linear','class'=>'btn-primary-600 radius-8 px-20 py-11']]:[]])
    <div class="row g-3 mb-24">
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Total Invoices','value'=>$stats['total'],'icon'=>'solar:bill-list-linear','tone'=>'navy'])</div>
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Outstanding','value'=>number_format($stats['outstanding'],2),'icon'=>'solar:wallet-linear','tone'=>'amber'])</div>
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Paid','value'=>$stats['paid'],'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
    </div>
    @include('admin.partials.filter-bar', ['action'=>route('admin.crm.invoices.index'),'resetUrl'=>route('admin.crm.invoices.index'),'fields'=>[
        ['name'=>'search','label'=>'Search','placeholder'=>'Invoice number'],
        ['name'=>'status','label'=>'Status','type'=>'select','options'=>['draft'=>'Draft','sent'=>'Sent','partially_paid'=>'Partially Paid','paid'=>'Paid','overdue'=>'Overdue','cancelled'=>'Cancelled']],
        ['name'=>'customer_id','label'=>'Customer','type'=>'select','options'=>$customers->pluck('name','id')->all()],
    ]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Number</th><th>Customer</th><th>Due Date</th><th>Total</th><th>Paid</th><th>Status</th><th></th></tr></thead>
        <tbody>@forelse($invoices as $invoice)<tr>
            <td><a href="{{ route('admin.crm.invoices.show',$invoice) }}" class="fw-semibold">{{ $invoice->invoice_number }}</a></td>
            <td>{{ $invoice->customer?->name }}</td><td>{{ $invoice->due_date->format('M j, Y') }}</td>
            <td>{{ number_format($invoice->total,2) }}</td><td>{{ number_format($invoice->paid_amount,2) }}</td>
            <td>@include('admin.crm.partials.status-pill',['status'=>$invoice->status])</td>
            <td>@include('admin.partials.table-actions',['viewUrl'=>route('admin.crm.invoices.show',$invoice),'editUrl'=>auth('admin')->user()?->can('update invoices')?route('admin.crm.invoices.edit',$invoice):null,'deleteId'=>$invoice->id,'deleteRoute'=>route('admin.crm.invoices.destroy',$invoice),'canDelete'=>auth('admin')->user()?->can('delete invoices')])</td>
        </tr>@empty<tr><td colspan="7" class="text-center py-40 text-secondary-light">No invoices found.</td></tr>@endforelse</tbody>
    </table></div></div></div>
    <div class="mt-24">{{ $invoices->links() }}</div>
</div>
@endsection
