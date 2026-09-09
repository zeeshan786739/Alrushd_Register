<a href="{{ $item['url'] }}" class="crm-list-row dash-list-row dash-list-row--neutral" aria-label="Complete {{ $item['label'] }}">
    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list" aria-hidden="true">
            <iconify-icon icon="{{ $item['icon'] }}"></iconify-icon>
        </span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $item['label'] }}</span>
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">{{ $item['desc'] }}</span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__actions">
        <span class="crm-list-row__chevron" aria-hidden="true">
            <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
        </span>
    </div>
</a>
