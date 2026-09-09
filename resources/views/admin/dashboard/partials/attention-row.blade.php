@php
    $typeIcons = [
        'Email' => 'solar:letter-linear',
        'CRM' => 'solar:user-hand-up-linear',
        'Billing' => 'solar:bill-list-linear',
        'Integrations' => 'solar:plug-circle-linear',
    ];
    $icon = $typeIcons[$item['type']] ?? 'solar:bell-bing-linear';
    $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $item['type']) ?: 'NA', 0, 2));
    $badgeLabel = match ($item['severity']) {
        'danger' => 'Urgent',
        'warning' => 'Due soon',
        default => 'Action',
    };
    $badgeClass = match ($item['severity']) {
        'danger' => 'is-danger',
        'warning' => 'is-warning',
        'info' => 'is-info',
        default => 'is-neutral',
    };
@endphp
<a href="{{ $item['url'] }}" class="crm-list-row dash-list-row dash-list-row--{{ $item['severity'] }}" aria-label="Open {{ $item['label'] }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list" aria-hidden="true">{{ $initials }}</span>
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
        <span class="dash-row-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
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
