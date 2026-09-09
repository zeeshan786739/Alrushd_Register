@php
    $showUrl = route('admin.email.campaigns.show', $campaign);
    $status = $campaign->status;
@endphp
<article class="crm-list-row em-list-row em-list-row--{{ $status }}"
         data-em-campaign-open
         data-href="{{ $showUrl }}"
         tabindex="0"
         role="link"
         aria-label="Open campaign {{ $campaign->name }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="em-row-icon" aria-hidden="true"><iconify-icon icon="solar:letter-linear"></iconify-icon></span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $campaign->name }}</span>
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">{{ Str::limit($campaign->subject, 48) }}</span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__field">
        <span class="em-status-pill em-status-pill--{{ $status }}">{{ ucfirst($status) }}</span>
    </div>
    <div class="crm-list-row__field crm-list-row__field--metric">
        <span class="crm-list-row__date">{{ number_format($campaign->recipient_count) }}</span>
        <span class="crm-list-row__date-sub">Recipients</span>
    </div>
    <div class="crm-list-row__field crm-list-row__field--metric">
        <span class="crm-list-row__date">{{ number_format($campaign->sent_count) }}</span>
        <span class="crm-list-row__date-sub">Sent</span>
    </div>
    <div class="crm-list-row__field crm-list-row__field--metric">
        <span class="crm-list-row__date">{{ number_format($campaign->opened_count) }}</span>
        <span class="crm-list-row__date-sub">Opens</span>
    </div>
    <div class="crm-list-row__field crm-list-row__field--metric">
        <span class="crm-list-row__date">{{ number_format($campaign->clicked_count) }}</span>
        <span class="crm-list-row__date-sub">Clicks</span>
    </div>
    <div class="crm-list-row__actions">
        <span class="crm-list-row__chevron" aria-hidden="true"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></span>
    </div>
</article>
