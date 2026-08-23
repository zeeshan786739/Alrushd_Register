@extends('admin.layouts.app')
@section('title', $invoice->invoice_number)
@section('content')
@include('admin.crm.partials.styles')
@php
    $due = \App\Support\InvoiceDueState::forInvoice($invoice);
    $canEdit = auth('admin')->user()?->can('update invoices') && $invoice->status !== 'paid';
    $canPay = auth('admin')->user()?->can('record invoice payments')
        && ! in_array($invoice->status, ['paid', 'cancelled'], true)
        && (float) $invoice->due_amount > 0.001;
@endphp
<div class="dashboard-main-body" id="crm-invoice-show">
    <div class="crm-workspace-header mb-20">
        <div class="crm-workspace-header__top">
            <div class="min-w-0">
                <div class="text-sm text-secondary-light mb-4">
                    <a href="{{ route('admin.crm.invoices.index') }}" class="text-secondary-light text-decoration-none">Invoices</a>
                    <span class="mx-4">/</span>
                    <span>{{ $invoice->invoice_number }}</span>
                </div>
                <h1 class="crm-workspace-header__title">{{ $invoice->invoice_number }}</h1>
                <div class="crm-workspace-header__contact">
                    @if($invoice->customer)
                        <span><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                            <a href="{{ route('admin.crm.customers.show', $invoice->customer) }}">{{ $invoice->customer->name }}</a>
                        </span>
                    @endif
                    @if($invoice->project)
                        <span><iconify-icon icon="solar:folder-with-files-linear"></iconify-icon>
                            <a href="{{ route('admin.crm.projects.show', $invoice->project) }}">{{ $invoice->project->name }}</a>
                        </span>
                    @endif
                    @if($invoice->quotation)
                        <span><iconify-icon icon="solar:document-text-linear"></iconify-icon>
                            <a href="{{ route('admin.crm.quotations.show', $invoice->quotation) }}">{{ $invoice->quotation->quotation_number }}</a>
                        </span>
                    @endif
                    <span><iconify-icon icon="solar:calendar-linear"></iconify-icon> Issued {{ $invoice->invoice_date?->format('M j, Y') ?? '—' }}</span>
                    <span><iconify-icon icon="solar:clock-circle-linear"></iconify-icon> Due {{ $invoice->due_date?->format('M j, Y') ?? '—' }}</span>
                </div>
                <div class="crm-workspace-header__badges">
                    @include('admin.crm.partials.status-pill', ['status'=>$invoice->status])
                    @if($due->applies)
                        <span class="{{ $due->badgeClass }}">{{ $due->label }}</span>
                    @endif
                </div>
            </div>
            <div class="crm-workspace-header__actions">
                @if($canEdit)
                    <a href="{{ route('admin.crm.invoices.edit', $invoice) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:pen-linear"></iconify-icon> Edit</a>
                @endif
                <a href="{{ route('admin.crm.invoices.pdf', $invoice) }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10"><iconify-icon icon="solar:download-linear"></iconify-icon> PDF</a>
                @if($invoice->customer)
                    <a href="{{ route('admin.crm.customers.show', $invoice->customer) }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10">Customer</a>
                @endif
                @if($invoice->project)
                    <a href="{{ route('admin.crm.projects.show', $invoice->project) }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10">Project</a>
                @endif
                @if($invoice->quotation)
                    <a href="{{ route('admin.crm.quotations.show', $invoice->quotation) }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10">Quotation</a>
                @endif
                @can('send invoices')
                    @if(! in_array($invoice->status, ['paid', 'cancelled'], true))
                        <form method="POST" action="{{ route('admin.crm.invoices.send', $invoice) }}" data-crm-once class="d-inline">
                            @csrf
                            <button class="btn btn-primary-600 radius-8 px-16 py-10" type="submit"><iconify-icon icon="solar:letter-linear"></iconify-icon> Send</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    @if($invoice->quotation)
        <div class="alert alert-primary bg-primary-50 text-primary-600 border-0 radius-8 mb-20">
            Created from Quotation
            <a href="{{ route('admin.crm.quotations.show', $invoice->quotation) }}" class="fw-semibold">{{ $invoice->quotation->quotation_number }}</a>
        </div>
    @endif

    <div class="row g-3 mb-20 crm-commercial-grid">
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Subtotal</div><p class="crm-commercial-stat__value">{{ number_format((float) $invoice->subtotal, 2) }}</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Discount</div><p class="crm-commercial-stat__value">{{ number_format((float) $invoice->discount_amount, 2) }}</p><p class="crm-commercial-stat__hint">{{ number_format((float) $invoice->discount_percentage, 2) }}%</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Tax</div><p class="crm-commercial-stat__value">{{ number_format((float) $invoice->tax_amount, 2) }}</p><p class="crm-commercial-stat__hint">{{ number_format((float) $invoice->tax_percentage, 2) }}%</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Total</div><p class="crm-commercial-stat__value">{{ number_format((float) $invoice->total, 2) }}</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Paid</div><p class="crm-commercial-stat__value">{{ number_format((float) $invoice->paid_amount, 2) }}</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Outstanding</div><p class="crm-commercial-stat__value">{{ number_format((float) $invoice->due_amount, 2) }}</p></div></div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            @include('admin.crm.documents.preview-invoice')
        </div>
    </div>

    @include('admin.crm.partials.email-delivery-card')

    <div class="row g-3 mb-20">
        <div class="col-lg-4">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon> Customer</h6>
                    @if($invoice->customer)
                        <a href="{{ route('admin.crm.customers.show', $invoice->customer) }}" class="fw-semibold d-inline-block mb-8">{{ $invoice->customer->name }}</a>
                        <div class="crm-lead-meta">{{ $invoice->customer->email ?: 'No email' }}</div>
                        <div class="crm-lead-meta">{{ $invoice->customer->phone ?: 'No phone' }}</div>
                    @else
                        <div class="crm-empty-state py-20 mb-0">No customer linked.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:folder-with-files-linear"></iconify-icon> Project</h6>
                    @if($invoice->project)
                        <a href="{{ route('admin.crm.projects.show', $invoice->project) }}" class="fw-semibold d-inline-block mb-8">{{ $invoice->project->name }}</a>
                        <div class="crm-lead-meta">{{ $invoice->project->project_code }}</div>
                    @else
                        <div class="crm-empty-state py-20 mb-0">No project linked.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:info-circle-linear"></iconify-icon> Commercial details</h6>
                    <div class="row g-3">
                        <div class="col-6"><div class="crm-lead-meta">Issued</div><div>{{ $invoice->invoice_date?->format('M j, Y') ?? '—' }}</div></div>
                        <div class="col-6"><div class="crm-lead-meta">Due</div><div>{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</div></div>
                        <div class="col-6"><div class="crm-lead-meta">Sent</div><div>{{ $invoice->sent_at?->format('M j, Y g:i A') ?? '—' }}</div></div>
                        <div class="col-6"><div class="crm-lead-meta">Paid at</div><div>{{ $invoice->paid_at?->format('M j, Y g:i A') ?? '—' }}</div></div>
                    </div>
                    @if($invoice->terms)
                        <div class="mt-12 pt-12 border-top"><div class="crm-lead-meta mb-4">Terms</div><div class="text-sm">{{ $invoice->terms }}</div></div>
                    @endif
                    @if($invoice->notes)
                        <div class="mt-12 pt-12 border-top"><div class="crm-lead-meta mb-4">Notes</div><div class="text-sm">{{ $invoice->notes }}</div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <h6 class="crm-section-title"><iconify-icon icon="solar:bill-list-linear"></iconify-icon> Line items</h6>
            <div class="table-responsive">
                <table class="table align-middle mb-0 crm-relation-table">
                    <thead><tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit price</th><th class="text-end">Line total</th></tr></thead>
                    <tbody>
                    @forelse($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="crm-empty-state py-20 mb-0">No line items.</div></td></tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end">{{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
                        <tr><td colspan="3" class="text-end">Discount</td><td class="text-end">-{{ number_format((float) $invoice->discount_amount, 2) }}</td></tr>
                        <tr><td colspan="3" class="text-end">Tax</td><td class="text-end">{{ number_format((float) $invoice->tax_amount, 2) }}</td></tr>
                        <tr><td colspan="3" class="text-end fw-bold">Grand total</td><td class="text-end fw-bold">{{ number_format((float) $invoice->total, 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-20">
        <div class="col-lg-5" id="record-payment">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:card-transfer-linear"></iconify-icon> Record payment</h6>
                    <div class="row g-2 mb-16">
                        <div class="col-4"><div class="crm-lead-meta">Total</div><div class="fw-semibold">{{ number_format((float) $invoice->total, 2) }}</div></div>
                        <div class="col-4"><div class="crm-lead-meta">Paid</div><div class="fw-semibold">{{ number_format((float) $invoice->paid_amount, 2) }}</div></div>
                        <div class="col-4"><div class="crm-lead-meta">Remaining</div><div class="fw-semibold">{{ number_format((float) $invoice->due_amount, 2) }}</div></div>
                    </div>
                    @if($canPay)
                        <form method="POST" action="{{ route('admin.crm.invoices.payments.store', $invoice) }}" data-crm-once>
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label text-sm">Payment date *</label>
                                    <input type="date" name="payment_date" class="form-control radius-8" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-sm">Amount *</label>
                                    <input type="number" step="0.01" min="0.01" max="{{ number_format((float) $invoice->due_amount, 2, '.', '') }}" name="amount" class="form-control radius-8 @error('amount') is-invalid @enderror" value="{{ old('amount', number_format((float) $invoice->due_amount, 2, '.', '')) }}" required>
                                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-sm">Method *</label>
                                    <select name="payment_method" class="form-select radius-8" required>
                                        @foreach(['bank_transfer'=>'Bank Transfer','cash'=>'Cash','card'=>'Card','cheque'=>'Cheque','other'=>'Other'] as $value=>$label)
                                            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-sm">Reference</label>
                                    <input type="text" name="transaction_id" class="form-control radius-8" value="{{ old('transaction_id') }}" maxlength="100" placeholder="Txn / cheque ref">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-sm">Notes</label>
                                    <textarea name="notes" class="form-control radius-8" rows="2" placeholder="Optional notes">{{ old('notes') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-10">Record Payment</button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="crm-empty-state py-20 mb-0">
                            @if($invoice->status === 'paid')
                                Invoice is fully paid.
                            @elseif($invoice->status === 'cancelled')
                                Payments cannot be recorded on a cancelled invoice.
                            @else
                                No outstanding balance to collect.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-8 mb-12">
                        <h6 class="crm-section-title mb-0"><iconify-icon icon="solar:history-linear"></iconify-icon> Payment history</h6>
                        <span class="crm-lead-meta">{{ $invoice->payments->count() }} payments</span>
                    </div>
                    <div class="crm-activity-scroll" style="max-height:420px">
                        @forelse($invoice->payments as $payment)
                            <div class="crm-task-row">
                                <div class="d-flex justify-content-between gap-12 flex-wrap">
                                    <div>
                                        <div class="fw-semibold">{{ number_format((float) $payment->amount, 2) }}</div>
                                        <div class="crm-lead-meta mt-4">
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                            · {{ $payment->payment_date?->format('M j, Y') ?? '—' }}
                                            @if($payment->transaction_id) · Ref {{ $payment->transaction_id }}@endif
                                        </div>
                                        @if($payment->notes)
                                            <div class="text-sm mt-4">{{ $payment->notes }}</div>
                                        @endif
                                        <div class="crm-lead-meta mt-4">
                                            Recorded by {{ $payment->receivedByAdmin?->name ?? '—' }}
                                            · {{ $payment->created_at?->format('M j, Y g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="crm-empty-state"><iconify-icon icon="solar:card-transfer-linear"></iconify-icon>No payments recorded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function () {
    document.querySelectorAll('#crm-invoice-show form[data-crm-once]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (!btn || btn.disabled) return;
            btn.disabled = true;
            btn.classList.add('is-loading');
        });
    });
    document.querySelectorAll('[data-crm-print-preview]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector(btn.getAttribute('data-crm-print-preview'));
            if (!target) return;
            var win = window.open('', '_blank');
            if (!win) return;
            win.document.write('<html><head><title>Print</title></head><body>' + target.innerHTML + '</body></html>');
            win.document.close();
            win.focus();
            win.print();
        });
    });
})();
</script>
@endsection
