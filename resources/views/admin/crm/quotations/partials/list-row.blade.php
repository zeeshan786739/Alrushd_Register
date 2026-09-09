@php
    $expiry = \App\Support\QuotationExpiryState::forQuotation($quotation);
    $locked = $quotation->status === 'accepted' || (bool) $quotation->converted_invoice_id;
    $canUpdate = auth('admin')->user()?->can('update quotations');
    $canDelete = auth('admin')->user()?->can('delete quotations');
    $showUrl = route('admin.crm.quotations.show', $quotation);
    $displayStatus = $quotation->converted_invoice_id ? 'converted' : $quotation->status;
@endphp
<article class="crm-list-row crm-list-row--status-{{ $displayStatus }}"
         data-crm-record-open data-href="{{ $showUrl }}" tabindex="0" role="button"
         aria-label="Open quotation {{ $quotation->quotation_number }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list">QT</span>
        <div class="crm-list-row__identity-copy">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $quotation->quotation_number }}</span>
            </div>
            @if($quotation->customer)
                <div class="crm-list-row__contact-line"><span class="crm-list-row__contact"><iconify-icon icon="solar:user-linear"></iconify-icon>{{ Str::limit($quotation->customer->name, 28) }}</span></div>
            @endif
        </div>
    </div>
    <div class="crm-list-row__field"><span class="crm-list-row__contact">{{ Str::limit($quotation->customer?->name ?? '—', 18) }}</span></div>
    <div class="crm-list-row__field"><span class="crm-list-row__contact">{{ Str::limit($quotation->project?->name ?? '—', 16) }}</span></div>
    <div class="crm-list-row__field">@include('admin.crm.partials.status-pill', ['status' => $displayStatus])</div>
    <div class="crm-list-row__field"><span class="crm-list-row__money">{{ number_format((float) $quotation->total, 2) }}</span></div>
    <div class="crm-list-row__field crm-list-row__field--date">
        <span class="crm-list-row__date">{{ $quotation->quotation_date?->format('M j, Y') ?? '—' }}</span>
    </div>
    <div class="crm-list-row__field">
        @if($expiry->applies)
            <span class="{{ $expiry->badgeClass }}">{{ $expiry->label }}</span>
        @else
            <span class="crm-list-row__date-sub">{{ $quotation->valid_until?->format('M j, Y') ?? '—' }}</span>
        @endif
    </div>
    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canUpdate && ! $locked)<a href="{{ route('admin.crm.quotations.edit', $quotation) }}" class="crm-list-action" onclick="event.stopPropagation()"><iconify-icon icon="solar:pen-linear"></iconify-icon></a>@endif
            @if($canDelete)
                <form action="{{ route('admin.crm.quotations.destroy', $quotation) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">@csrf @method('DELETE')
                    <button type="submit" class="crm-list-action is-delete"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
                </form>
            @endif
        </div>
        <button type="button" class="crm-list-row__chevron" data-crm-record-open-trigger onclick="event.stopPropagation()"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></button>
    </div>
</article>
