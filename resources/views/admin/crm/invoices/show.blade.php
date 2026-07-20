@extends('admin.layouts.app')
@section('title', $invoice->invoice_number)
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title'=>$invoice->invoice_number,'subtitle'=>$invoice->customer?->name,'showBreadcrumb'=>true,
        'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Invoices','url'=>route('admin.crm.invoices.index')],['label'=>$invoice->invoice_number]],
        'actions'=>array_filter([
            auth('admin')->user()?->can('update invoices')?['label'=>'Edit','url'=>route('admin.crm.invoices.edit',$invoice),'icon'=>'solar:pen-linear','class'=>'btn-outline-primary-600 radius-8 px-20 py-11']:null,
            ['label'=>'Download PDF','url'=>route('admin.crm.invoices.pdf',$invoice),'icon'=>'solar:download-linear','class'=>'btn-outline-neutral-500 radius-8 px-20 py-11'],
        ]),
    ])
    <div class="row g-3 mb-24">
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-20"><div class="text-sm text-secondary-light">Status</div>@include('admin.crm.partials.status-pill',['status'=>$invoice->status])</div></div></div>
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-20"><div class="text-sm text-secondary-light">Total</div><div class="fw-bold text-lg">{{ number_format($invoice->total,2) }}</div></div></div></div>
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-20"><div class="text-sm text-secondary-light">Paid</div><div class="fw-bold text-lg">{{ number_format($invoice->paid_amount,2) }}</div></div></div></div>
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-20"><div class="text-sm text-secondary-light">Due</div><div class="fw-bold text-lg">{{ number_format($invoice->due_amount,2) }}</div></div></div></div>
    </div>
    <div class="card radius-12 shadow-2 border-0 mb-24"><div class="card-body p-24">
        <table class="table"><thead><tr><th>Description</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
        <tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price,2) }}</td><td>{{ number_format($item->total,2) }}</td></tr>@endforeach</tbody>
        <tfoot><tr><td colspan="3" class="text-end fw-bold">Total</td><td class="fw-bold">{{ number_format($invoice->total,2) }}</td></tr></tfoot></table>
    </div></div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24">
                <h6 class="fw-semibold mb-16">Payments</h6>
                @forelse($invoice->payments as $payment)
                    <div class="border-bottom pb-12 mb-12"><strong>{{ number_format($payment->amount,2) }}</strong> · {{ ucfirst(str_replace('_',' ',$payment->payment_method)) }} · {{ $payment->payment_date->format('M j, Y') }}</div>
                @empty<p class="text-secondary-light">No payments recorded.</p>@endforelse
                @can('record invoice payments')<form method="POST" action="{{ route('admin.crm.invoices.payments.store',$invoice) }}" class="row g-2 mt-16">@csrf
                    <div class="col-md-4"><input type="date" name="payment_date" class="form-control radius-8" value="{{ date('Y-m-d') }}" required></div>
                    <div class="col-md-4"><input type="number" step="0.01" name="amount" class="form-control radius-8" placeholder="Amount" required></div>
                    <div class="col-md-4"><select name="payment_method" class="form-select radius-8"><option value="bank_transfer">Bank Transfer</option><option value="cash">Cash</option><option value="card">Card</option><option value="cheque">Cheque</option><option value="other">Other</option></select></div>
                    <div class="col-12"><button class="btn btn-primary-600 radius-8 btn-sm">Record Payment</button></div>
                </form>@endcan
            </div></div>
        </div>
        <div class="col-lg-6 d-flex align-items-start">
            @can('send invoices')@if(!in_array($invoice->status,['paid','cancelled']))<form method="POST" action="{{ route('admin.crm.invoices.send',$invoice) }}">@csrf<button class="btn btn-primary-600 radius-8">Send Invoice</button></form>@endif @endcan
        </div>
    </div>
</div>
@endsection
