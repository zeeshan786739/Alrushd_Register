@php
    /** @var \App\Enums\LeadStatus $status */
    $groupCount = (int) ($count ?? 0);
@endphp
<div class="crm-list-status-group__head" data-status="{{ $status->value }}">
    <div class="crm-list-status-group__title">
        <span class="crm-list-status-group__label">{{ $status->label() }} leads</span>
        <span class="crm-list-status-group__count" data-crm-list-group-count>{{ number_format($groupCount) }}</span>
    </div>
</div>
