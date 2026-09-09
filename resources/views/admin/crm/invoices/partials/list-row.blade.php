@php
    $due = \App\Support\InvoiceDueState::forInvoice($invoice);
    $canUpdate = auth('admin')->user()?->can('update invoices');
    $canDelete = auth('admin')->user()?->can('delete invoices');
    $showUrl = route('admin.crm.invoices.show', $invoice);
@endphp
<article class="crm-list-row crm-list-row--status-{{ $invoice->status }}"
         data-crm-record-open data-href="{{ $showUrl }}" tabindex="0" role="button"
         aria-label="Open invoice {{ $invoice->invoice_number }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list">IN</span>
        <div class="crm-list-row__identity-copy">
            <div class="crm-list-row__name-row"><span class="crm-list-row__name">{{ $invoice->invoice_number }}</span></div>
            @if($invoice->customer)
                <div class="crm-list-row__contact-line"><span class="crm-list-row__contact"><iconify-icon icon="solar:user-linear"></iconify-icon>{{ Str::limit($invoice->customer->name, 28) }}</span></div>
            @endif
        </div>
    </div>
    <div class="crm-list-row__field"><span class="crm-list-row__contact">{{ Str::limit($invoice->customer?->name ?? '—', 18) }}</span></div>
    <div class="crm-list-row__field">@include('admin.crm.partials.status-pill', ['status' => $invoice->status])</div>
    <div class="crm-list-row__field"><span class="crm-list-row__money">{{ number_format((float) $invoice->total, 2) }}</span></div>
    <div class="crm-list-row__field"><span class="crm-list-row__money crm-list-row__money--muted">{{ number_format((float) $invoice->due_amount, 2) }}</span></div>
    <div class="crm-list-row__field crm-list-row__field--date"><span class="crm-list-row__date">{{ $invoice->invoice_date?->format('M j, Y') ?? '—' }}</span></div>
    <div class="crm-list-row__field">
        @if($due->applies)
            <span class="{{ $due->badgeClass }}">{{ $due->label }}</span>
        @else
            <span class="crm-list-row__date-sub">{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</span>
        @endif
    </div>
    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canUpdate && $invoice->status !== 'paid')<a href="{{ route('admin.crm.invoices.edit', $invoice) }}" class="crm-list-action" onclick="event.stopPropagation()"><iconify-icon icon="solar:pen-linear"></iconify-icon></a>@endif
            @if($canDelete)
                <form action="{{ route('admin.crm.invoices.destroy', $invoice) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">@csrf @method('DELETE')
                    <button type="submit" class="crm-list-action is-delete"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
                </form>
            @endif
        </div>
        <button type="button" class="crm-list-row__chevron" data-crm-record-open-trigger onclick="event.stopPropagation()"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></button>
    </div>
</article>
