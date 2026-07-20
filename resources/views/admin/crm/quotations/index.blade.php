@extends('admin.layouts.app')
@section('title', 'Quotations')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Quotations','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Quotations']],'actions'=>auth('admin')->user()?->can('create quotations')?[['label'=>'Create Quotation','url'=>route('admin.crm.quotations.create'),'icon'=>'solar:add-circle-linear','class'=>'btn-primary-600 radius-8 px-20 py-11']]:[]])
    <div class="row g-3 mb-24">
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Total','value'=>$stats['total'],'icon'=>'solar:document-linear','tone'=>'navy'])</div>
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Draft','value'=>$stats['draft'],'icon'=>'solar:pen-linear','tone'=>'slate'])</div>
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Sent','value'=>$stats['sent'],'icon'=>'solar:letter-linear','tone'=>'amber'])</div>
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Accepted','value'=>$stats['accepted'],'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
    </div>
    @include('admin.partials.filter-bar', ['action'=>route('admin.crm.quotations.index'),'resetUrl'=>route('admin.crm.quotations.index'),'fields'=>[
        ['name'=>'search','label'=>'Search','placeholder'=>'Quotation number'],
        ['name'=>'status','label'=>'Status','type'=>'select','options'=>['draft'=>'Draft','sent'=>'Sent','accepted'=>'Accepted','rejected'=>'Rejected','expired'=>'Expired']],
        ['name'=>'customer_id','label'=>'Customer','type'=>'select','options'=>$customers->pluck('name','id')->all()],
    ]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Number</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>@forelse($quotations as $quotation)<tr>
            <td><a href="{{ route('admin.crm.quotations.show',$quotation) }}" class="fw-semibold">{{ $quotation->quotation_number }}</a></td>
            <td>{{ $quotation->customer?->name }}</td><td>{{ $quotation->quotation_date->format('M j, Y') }}</td>
            <td>{{ number_format($quotation->total,2) }}</td><td>@include('admin.crm.partials.status-pill',['status'=>$quotation->status])</td>
            <td>@include('admin.partials.table-actions',['viewUrl'=>route('admin.crm.quotations.show',$quotation),'editUrl'=>auth('admin')->user()?->can('update quotations')?route('admin.crm.quotations.edit',$quotation):null,'deleteId'=>$quotation->id,'deleteRoute'=>route('admin.crm.quotations.destroy',$quotation),'canDelete'=>auth('admin')->user()?->can('delete quotations')])</td>
        </tr>@empty<tr><td colspan="6" class="text-center py-40 text-secondary-light">No quotations found.</td></tr>@endforelse</tbody>
    </table></div></div></div>
    <div class="mt-24">{{ $quotations->links() }}</div>
</div>
@endsection
