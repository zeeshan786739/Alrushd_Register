@extends('admin.layouts.app')
@section('title', $quotation->quotation_number)
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title'=>$quotation->quotation_number,'subtitle'=>$quotation->customer?->name,'showBreadcrumb'=>true,
        'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Quotations','url'=>route('admin.crm.quotations.index')],['label'=>$quotation->quotation_number]],
        'actions'=>array_filter([
            auth('admin')->user()?->can('update quotations')?['label'=>'Edit','url'=>route('admin.crm.quotations.edit',$quotation),'icon'=>'solar:pen-linear','class'=>'btn-outline-primary-600 radius-8 px-20 py-11']:null,
            ['label'=>'Download PDF','url'=>route('admin.crm.quotations.pdf',$quotation),'icon'=>'solar:download-linear','class'=>'btn-outline-neutral-500 radius-8 px-20 py-11'],
        ]),
    ])
    <div class="row g-3 mb-24">
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-20"><div class="text-sm text-secondary-light">Status</div>@include('admin.crm.partials.status-pill',['status'=>$quotation->status])</div></div></div>
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-20"><div class="text-sm text-secondary-light">Total</div><div class="fw-bold text-lg">{{ number_format($quotation->total,2) }}</div></div></div></div>
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-20"><div class="text-sm text-secondary-light">Date</div><div class="fw-bold">{{ $quotation->quotation_date->format('M j, Y') }}</div></div></div></div>
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-20"><div class="text-sm text-secondary-light">Valid Until</div><div class="fw-bold">{{ $quotation->valid_until?->format('M j, Y') ?? '—' }}</div></div></div></div>
    </div>
    <div class="card radius-12 shadow-2 border-0 mb-24"><div class="card-body p-24">
        <table class="table"><thead><tr><th>Description</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
        <tbody>@foreach($quotation->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price,2) }}</td><td>{{ number_format($item->total,2) }}</td></tr>@endforeach</tbody>
        <tfoot><tr><td colspan="3" class="text-end fw-semibold">Subtotal</td><td>{{ number_format($quotation->subtotal,2) }}</td></tr>
        <tr><td colspan="3" class="text-end">Tax ({{ $quotation->tax_percentage }}%)</td><td>{{ number_format($quotation->tax_amount,2) }}</td></tr>
        <tr><td colspan="3" class="text-end">Discount ({{ $quotation->discount_percentage }}%)</td><td>-{{ number_format($quotation->discount_amount,2) }}</td></tr>
        <tr><td colspan="3" class="text-end fw-bold">Total</td><td class="fw-bold">{{ number_format($quotation->total,2) }}</td></tr></tfoot></table>
    </div></div>
    <div class="d-flex flex-wrap gap-12">
        @can('send quotations')@if(in_array($quotation->status,['draft','sent']))<form method="POST" action="{{ route('admin.crm.quotations.send',$quotation) }}">@csrf<button class="btn btn-primary-600 radius-8">Send Quotation</button></form>@endif @endcan
        @can('update quotations')@if($quotation->status==='sent')<form method="POST" action="{{ route('admin.crm.quotations.accept',$quotation) }}">@csrf<button class="btn btn-success radius-8">Accept</button></form>
        <form method="POST" action="{{ route('admin.crm.quotations.reject',$quotation) }}">@csrf<button class="btn btn-outline-danger radius-8">Reject</button></form>@endif @endcan
        @can('convert quotations')@if($quotation->status==='accepted' && !$quotation->converted_invoice_id)<form method="POST" action="{{ route('admin.crm.quotations.convert',$quotation) }}">@csrf<button class="btn btn-outline-primary-600 radius-8">Convert to Invoice</button></form>
        @elseif($quotation->convertedInvoice)<a href="{{ route('admin.crm.invoices.show',$quotation->convertedInvoice) }}" class="btn btn-outline-success radius-8">View Invoice</a>@endif @endcan
    </div>
</div>
@endsection
