@php
    $showUrl = route('admin.email.show', $msg);
    $isUnread = ! $msg->is_read && $msg->folder === 'inbox';
    $senderLabel = $msg->folder === 'sent' || $msg->folder === 'draft'
        ? ($msg->to ?: 'No recipients')
        : ($msg->from_name ?: $msg->from_email ?: 'Unknown');
    $initial = strtoupper(mb_substr(trim($senderLabel), 0, 1, 'UTF-8') ?: '?');
@endphp
<article class="crm-list-row em-inbox-row {{ $isUnread ? 'is-unread' : '' }}"
         data-em-inbox-open
         data-href="{{ $showUrl }}"
         tabindex="0"
         role="link"
         aria-label="Open message {{ $msg->subject ?: 'no subject' }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list em-inbox-row__avatar" aria-hidden="true">{{ $initial }}</span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $senderLabel }}</span>
                @if($isUnread)
                    <span class="em-inbox-row__unread-badge">Unread</span>
                @endif
            </div>
            @if($msg->senderMailbox && ($senderMailboxes ?? collect())->count() > 1)
                <div class="crm-list-row__contact-line">
                    <span class="crm-list-row__contact">{{ $msg->senderMailbox->email }}</span>
                </div>
            @endif
        </div>
    </div>
    <div class="crm-list-row__field em-inbox-row__subject-col min-w-0">
        <span class="em-inbox-row__subject">{{ $msg->subject ?: '(no subject)' }}</span>
        <span class="em-inbox-row__preview">{{ \Illuminate\Support\Str::limit($msg->body_text ?: strip_tags((string) $msg->body_html), 96) }}</span>
        @if($msg->folder === 'sent' && ($msg->lead_id || $msg->customer_id || $msg->quotation_id || $msg->invoice_id))
            <span class="crm-list-row__tags">
                @if($msg->lead_id)<span class="em-crm-chip">Lead</span>@endif
                @if($msg->customer_id)<span class="em-crm-chip">Customer</span>@endif
                @if($msg->quotation_id)<span class="em-crm-chip">Quotation</span>@endif
                @if($msg->invoice_id)<span class="em-crm-chip">Invoice</span>@endif
            </span>
        @endif
    </div>
    <div class="crm-list-row__field crm-list-row__field--metric">
        <span class="crm-list-row__date">{{ optional($msg->sent_at ?: $msg->received_at ?: $msg->created_at)->diffForHumans() }}</span>
    </div>
    <div class="crm-list-row__field em-inbox-row__indicators">
        @if(($msg->attachments_count ?? 0) > 0)
            <iconify-icon icon="solar:paperclip-linear" class="em-inbox-row__icon" title="Has attachments"></iconify-icon>
        @endif
        @if($msg->is_starred)
            <iconify-icon icon="solar:star-bold" class="em-inbox-row__icon em-inbox-row__icon--star"></iconify-icon>
        @endif
        @if($msg->folder === 'sent')
            @include('admin.email-marketing.partials.provider-status-pill', [
                'status' => $msg->provider_status ?: $msg->delivery_status ?: 'pending',
            ])
        @endif
    </div>
    <div class="crm-list-row__actions">
        <span class="crm-list-row__chevron" aria-hidden="true"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></span>
    </div>
</article>
