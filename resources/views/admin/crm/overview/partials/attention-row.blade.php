@php
    $typeIcons = [
        'Lead follow-up overdue' => 'solar:alarm-linear',
        'Lead follow-up today' => 'solar:calendar-linear',
        'Project overdue' => 'solar:folder-linear',
        'Quotation expired' => 'solar:document-linear',
        'Quotation expiring soon' => 'solar:clock-circle-linear',
        'Invoice overdue' => 'solar:bill-list-linear',
    ];
    $icon = $typeIcons[$item['type']] ?? 'solar:bell-bing-linear';
    $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $item['label']) ?: 'NA', 0, 2));
@endphp
<a href="{{ $item['url'] }}"
   class="crm-list-row crm-overview-attention-row crm-overview-attention-row--{{ $item['severity'] }}"
   aria-label="Open {{ $item['label'] }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list crm-overview-attention-row__avatar" aria-hidden="true">{{ $initials }}</span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $item['label'] }}</span>
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">
                    <iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon>
                    {{ $item['type'] }}
                </span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__field">
        <span @class(['crm-overview-attention-row__badge', 'is-danger' => $item['severity'] === 'danger', 'is-warning' => $item['severity'] === 'warning'])>
            {{ $item['severity'] === 'danger' ? 'Urgent' : 'Due soon' }}
        </span>
    </div>
    <div class="crm-list-row__field crm-list-row__field--date">
        <span class="crm-list-row__date">{{ $item['meta'] }}</span>
    </div>
    <div class="crm-list-row__actions">
        <span class="crm-list-row__chevron" aria-hidden="true">
            <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
        </span>
    </div>
</a>
