@extends('admin.layouts.app')
@section('title', $quotation->quotation_number)
@section('content')
@include('admin.crm.partials.styles')
@php
    $locked = $quotation->status === 'accepted' || (bool) $quotation->converted_invoice_id;
    $expiry = \App\Support\QuotationExpiryState::forQuotation($quotation);
    $canEdit = auth('admin')->user()?->can('update quotations') && ! $locked;
@endphp
<div class="dashboard-main-body" id="crm-quotation-show">
    <div class="crm-workspace-header mb-20">
        <div class="crm-workspace-header__top">
            <div class="min-w-0">
                <div class="text-sm text-secondary-light mb-4">
                    <a href="{{ route('admin.crm.quotations.index') }}" class="text-secondary-light text-decoration-none">Quotations</a>
                    <span class="mx-4">/</span>
                    <span>{{ $quotation->quotation_number }}</span>
                </div>
                <h1 class="crm-workspace-header__title">{{ $quotation->quotation_number }}</h1>
                <div class="crm-workspace-header__contact">
                    @if($quotation->customer)
                        <span><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                            <a href="{{ route('admin.crm.customers.show', $quotation->customer) }}">{{ $quotation->customer->name }}</a>
                        </span>
                        @if($quotation->customer->email)
                            <span><iconify-icon icon="solar:letter-linear"></iconify-icon> {{ $quotation->customer->email }}</span>
                        @endif
                    @endif
                    @if($quotation->project)
                        <span><iconify-icon icon="solar:folder-with-files-linear"></iconify-icon>
                            <a href="{{ route('admin.crm.projects.show', $quotation->project) }}">{{ $quotation->project->name }}</a>
                        </span>
                    @endif
                    <span><iconify-icon icon="solar:calendar-linear"></iconify-icon> Issued {{ $quotation->quotation_date?->format('M j, Y') ?? '—' }}</span>
                    @if($quotation->valid_until)
                        <span><iconify-icon icon="solar:clock-circle-linear"></iconify-icon> Until {{ $quotation->valid_until->format('M j, Y') }}</span>
                    @endif
                    @if($quotation->accepted_at)
                        <span><iconify-icon icon="solar:check-circle-linear"></iconify-icon> Accepted {{ $quotation->accepted_at->format('M j, Y') }}</span>
                    @endif
                </div>
                <div class="crm-workspace-header__badges">
                    @include('admin.crm.partials.status-pill', ['status'=>$quotation->status])
                    <span class="crm-status-pill crm-status-pill--accepted">{{ number_format((float) $quotation->total, 2) }}</span>
                    @if($expiry->applies)
                        <span class="{{ $expiry->badgeClass }}">{{ $expiry->label }}</span>
                    @endif
                    @if($quotation->converted_invoice_id)
                        <span class="crm-status-pill crm-status-pill--paid">Invoice linked</span>
                    @endif
                </div>
            </div>
            <div class="crm-workspace-header__actions">
                @if($canEdit)
                    <a href="{{ route('admin.crm.quotations.edit', $quotation) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:pen-linear"></iconify-icon> Edit</a>
                @endif
                <a href="{{ route('admin.crm.quotations.pdf', $quotation) }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10"><iconify-icon icon="solar:download-linear"></iconify-icon> PDF</a>
                @if($quotation->customer)
                    <a href="{{ route('admin.crm.customers.show', $quotation->customer) }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10"><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon> Customer</a>
                @endif
                @if($quotation->project)
                    <a href="{{ route('admin.crm.projects.show', $quotation->project) }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10"><iconify-icon icon="solar:folder-with-files-linear"></iconify-icon> Project</a>
                @endif
            </div>
        </div>
    </div>

    @if($quotation->convertedInvoice)
        <div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mb-20 d-flex flex-wrap justify-content-between align-items-center gap-12">
            <div>
                <strong>Converted to invoice</strong>
                <span class="ms-8">{{ $quotation->convertedInvoice->invoice_number }}</span>
            </div>
            <a href="{{ route('admin.crm.invoices.show', $quotation->convertedInvoice) }}" class="btn btn-sm btn-outline-success radius-8">View Invoice</a>
        </div>
    @elseif($quotation->status === 'accepted')
        <div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mb-20">
            Quotation accepted{{ $quotation->accepted_at ? ' on '.$quotation->accepted_at->format('M j, Y') : '' }}. Ready to convert to an invoice.
        </div>
    @elseif($quotation->status === 'rejected')
        <div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 mb-20">
            Quotation rejected. Conversion is not available.
        </div>
    @endif

    <div class="row g-3 mb-20">
        <div class="col-lg-4">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon> Customer</h6>
                    @if($quotation->customer)
                        <a href="{{ route('admin.crm.customers.show', $quotation->customer) }}" class="fw-semibold d-inline-block mb-8">{{ $quotation->customer->name }}</a>
                        <div class="crm-lead-meta">{{ $quotation->customer->company ?: '—' }}</div>
                        <div class="crm-lead-meta mt-8">{{ $quotation->customer->email ?: 'No email' }}</div>
                        <div class="crm-lead-meta">{{ $quotation->customer->phone ?: 'No phone' }}</div>
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
                    @if($quotation->project)
                        <a href="{{ route('admin.crm.projects.show', $quotation->project) }}" class="fw-semibold d-inline-block mb-8">{{ $quotation->project->name }}</a>
                        <div class="crm-lead-meta">{{ $quotation->project->project_code }}</div>
                        <div class="mt-12">@include('admin.crm.partials.status-pill', ['status'=>$quotation->project->status])</div>
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
                        <div class="col-6"><div class="crm-lead-meta">Issued</div><div>{{ $quotation->quotation_date?->format('M j, Y') ?? '—' }}</div></div>
                        <div class="col-6"><div class="crm-lead-meta">Valid until</div><div>{{ $quotation->valid_until?->format('M j, Y') ?? '—' }}</div></div>
                        <div class="col-6"><div class="crm-lead-meta">Sent</div><div>{{ $quotation->sent_at?->format('M j, Y g:i A') ?? '—' }}</div></div>
                        <div class="col-6"><div class="crm-lead-meta">Accepted</div><div>{{ $quotation->accepted_at?->format('M j, Y g:i A') ?? '—' }}</div></div>
                    </div>
                    @if($quotation->terms)
                        <div class="mt-12 pt-12 border-top"><div class="crm-lead-meta mb-4">Terms</div><div class="text-sm">{{ $quotation->terms }}</div></div>
                    @endif
                    @if($quotation->notes)
                        <div class="mt-12 pt-12 border-top"><div class="crm-lead-meta mb-4">Notes</div><div class="text-sm">{{ $quotation->notes }}</div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            @include('admin.crm.documents.preview-quotation')
        </div>
    </div>

    @include('admin.crm.partials.email-delivery-card')

    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <h6 class="crm-section-title"><iconify-icon icon="solar:bill-list-linear"></iconify-icon> Line items</h6>
            <div class="table-responsive">
                <table class="table align-middle mb-0 crm-relation-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit price</th>
                            <th class="text-end">Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($quotation->items as $item)
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
                        <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end fw-semibold">{{ number_format((float) $quotation->subtotal, 2) }}</td></tr>
                        <tr><td colspan="3" class="text-end">Discount ({{ number_format((float) $quotation->discount_percentage, 2) }}%)</td><td class="text-end">-{{ number_format((float) $quotation->discount_amount, 2) }}</td></tr>
                        <tr><td colspan="3" class="text-end">Tax ({{ number_format((float) $quotation->tax_percentage, 2) }}%)</td><td class="text-end">{{ number_format((float) $quotation->tax_amount, 2) }}</td></tr>
                        <tr><td colspan="3" class="text-end fw-bold">Grand total</td><td class="text-end fw-bold">{{ number_format((float) $quotation->total, 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <h6 class="crm-section-title"><iconify-icon icon="solar:tuning-2-linear"></iconify-icon> Lifecycle actions</h6>
            <div class="d-flex flex-wrap gap-10">
                @can('send quotations')
                    @if($quotation->status === 'draft' && ! $locked)
                        <form method="POST" action="{{ route('admin.crm.quotations.send', $quotation) }}" data-crm-once>
                            @csrf
                            <button class="btn btn-primary-600 radius-8 px-16 py-10" type="submit">
                                <iconify-icon icon="solar:letter-linear"></iconify-icon> Send by Email
                            </button>
                        </form>
                        <form method="POST"
                              action="{{ route('admin.crm.quotations.mark-sent', $quotation) }}"
                              data-crm-once
                              data-crm-confirm
                              data-confirm-title="Mark quotation as sent?"
                              data-confirm-message="Confirm that this quotation was sent outside the CRM."
                              data-confirm-note="No email will be sent from the system."
                              data-confirm-label="Mark as Sent"
                              data-confirm-tone="info"
                              data-confirm-icon="solar:check-read-linear">
                            @csrf
                            <button class="btn btn-outline-primary-600 radius-8 px-16 py-10" type="submit">
                                <iconify-icon icon="solar:check-read-linear"></iconify-icon> Mark as Sent
                            </button>
                        </form>
                    @elseif($quotation->status === 'sent' && ! $locked)
                        <form method="POST" action="{{ route('admin.crm.quotations.send', $quotation) }}" data-crm-once>
                            @csrf
                            <button class="btn btn-outline-primary-600 radius-8 px-16 py-10" type="submit">
                                <iconify-icon icon="solar:letter-linear"></iconify-icon> Send by Email
                            </button>
                        </form>
                    @endif
                @endcan

                @can('convert quotations')
                    @if($quotation->status === 'sent')
                        <form method="POST" action="{{ route('admin.crm.quotations.accept', $quotation) }}" data-crm-once onsubmit="return confirm('Accept this quotation? This finalizes commercial values.');">
                            @csrf
                            <button class="btn btn-success radius-8 px-16 py-10" type="submit">
                                <iconify-icon icon="solar:check-circle-linear"></iconify-icon> Accept
                            </button>
                        </form>
                    @endif
                @endcan

                @can('update quotations')
                    @if($quotation->status === 'sent')
                        <form method="POST" action="{{ route('admin.crm.quotations.reject', $quotation) }}" data-crm-once onsubmit="return confirm('Reject this quotation?');">
                            @csrf
                            <button class="btn btn-outline-danger radius-8 px-16 py-10" type="submit">
                                <iconify-icon icon="solar:close-circle-linear"></iconify-icon> Reject
                            </button>
                        </form>
                    @endif
                @endcan

                @can('convert quotations')
                    @if($quotation->status === 'accepted' && ! $quotation->converted_invoice_id)
                        <form method="POST" action="{{ route('admin.crm.quotations.convert', $quotation) }}" data-crm-once onsubmit="return confirm('Convert this quotation to an invoice?');">
                            @csrf
                            <button class="btn btn-outline-primary-600 radius-8 px-16 py-10" type="submit">
                                <iconify-icon icon="solar:bill-list-linear"></iconify-icon> Convert to Invoice
                            </button>
                        </form>
                    @elseif($quotation->convertedInvoice)
                        <a href="{{ route('admin.crm.invoices.show', $quotation->convertedInvoice) }}" class="btn btn-outline-success radius-8 px-16 py-10">
                            <iconify-icon icon="solar:eye-linear"></iconify-icon> View Invoice
                        </a>
                    @endif
                @endcan
            </div>
            @if($locked && ! $canEdit)
                <p class="crm-lead-meta mt-12 mb-0">Editing is locked because this quotation is accepted or already converted.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function () {
    document.querySelectorAll('#crm-quotation-show form[data-crm-once]').forEach(function (form) {
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
