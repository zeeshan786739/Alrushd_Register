<a href="{{ $url }}" class="acct-quick-row acct-quick-row--{{ $tone }}" aria-label="{{ $title }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="acct-row-icon acct-row-icon--{{ $tone }}" aria-hidden="true">
            <iconify-icon icon="{{ $icon }}"></iconify-icon>
        </span>
        <div class="crm-list-row__identity-copy min-w-0">
            <span class="crm-list-row__name">{{ $title }}</span>
            <span class="crm-list-row__contact">{{ $description }}</span>
        </div>
    </div>
    <div class="crm-list-row__field">
        @if(! empty($status))
            <span @class(['acct-status-pill', $statusClass ?? 'acct-status-pill--muted'])>{{ $status }}</span>
        @endif
    </div>
    <div class="crm-list-row__actions">
        <span class="crm-list-row__chevron" aria-hidden="true"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></span>
    </div>
</a>
