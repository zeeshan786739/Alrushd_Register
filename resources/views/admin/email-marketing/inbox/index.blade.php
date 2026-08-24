@extends('admin.layouts.app')
@section('title', $title)
@section('content')
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'inbox',
    'shellTitle' => $title,
    'shellSubtitle' => 'Reply to parents and leads from your connected mailbox.',
    'shellActions' => array_values(array_filter([
        auth('admin')->user()?->can('compose emails') ? [
            'label' => 'Compose',
            'url' => route('admin.email.compose'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:pen-new-square-linear',
        ] : null,
    ])),
])

@can('sync inbox')
<form id="em-sync-form" method="POST" action="{{ route('admin.email.inbox.sync') }}" class="d-none">@csrf</form>
@endcan

@php $folder = $folder ?? 'inbox'; @endphp
@include('admin.email-marketing.partials.nav', ['folder' => $folder, 'skipModuleNav' => true, 'showInboxFolders' => true])

<div class="em-inbox-panel em-panel">
    <div class="em-inbox-panel__head em-panel__head">
        <div>
            <h2 class="em-panel__title">{{ $title }}</h2>
            <p class="em-panel__desc">{{ $messages->total() }} message{{ $messages->total() === 1 ? '' : 's' }}</p>
        </div>
        @if($folder === 'inbox' && auth('admin')->user()?->can('sync inbox'))
        <button type="button" class="btn btn-outline-neutral-500 radius-8 px-16 py-10 fc-btn" onclick="document.getElementById('em-sync-form').submit();">
            <iconify-icon icon="solar:refresh-linear"></iconify-icon> Refresh
        </button>
        @endif
    </div>

    <div class="em-inbox-toolbar">
        <form method="GET" class="em-inbox-search">
            <div class="em-inbox-search__field">
                <iconify-icon icon="solar:magnifer-linear" class="em-inbox-search__icon"></iconify-icon>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control radius-8" placeholder="Search subject, sender, or recipient" aria-label="Search emails">
            </div>
            <button type="submit" class="btn btn-outline-primary-600 radius-8 fc-btn">Search</button>
        </form>
    </div>

    <div class="em-inbox-panel__body">
        @forelse($messages as $msg)
            @php
                $isUnread = ! $msg->is_read && $msg->folder === 'inbox';
                $senderLabel = $msg->folder === 'sent' || $msg->folder === 'draft'
                    ? ($msg->to ?: 'No recipients')
                    : ($msg->from_name ?: $msg->from_email ?: 'Unknown');
                $initial = strtoupper(mb_substr(trim($senderLabel), 0, 1, 'UTF-8') ?: '?');
            @endphp
            <a href="{{ route('admin.email.show', $msg) }}" class="em-mail-row {{ $isUnread ? 'is-unread' : '' }}">
                <span class="em-mail-row__avatar" aria-hidden="true">{{ $initial }}</span>
                <span class="em-mail-row__main">
                    <span class="em-mail-row__top">
                        <strong class="em-mail-row__sender">{{ $senderLabel }}</strong>
                        <span class="em-mail-row__meta">
                            {{ optional($msg->sent_at ?: $msg->received_at ?: $msg->created_at)->diffForHumans() }}
                        </span>
                    </span>
                    <span class="em-mail-row__subject">{{ $msg->subject ?: '(no subject)' }}</span>
                    <span class="em-mail-row__preview">{{ \Illuminate\Support\Str::limit($msg->body_text ?: strip_tags((string) $msg->body_html), 120) }}</span>
                    @if($msg->folder === 'sent' && ($msg->lead_id || $msg->customer_id || $msg->quotation_id || $msg->invoice_id))
                    <span class="em-mail-row__tags">
                        @if($msg->lead_id)<span class="em-crm-chip">Lead</span>@endif
                        @if($msg->customer_id)<span class="em-crm-chip">Customer</span>@endif
                        @if($msg->quotation_id)<span class="em-crm-chip">Quotation</span>@endif
                        @if($msg->invoice_id)<span class="em-crm-chip">Invoice</span>@endif
                    </span>
                    @endif
                </span>
                <span class="em-mail-row__aside">
                    @if(($msg->attachments_count ?? 0) > 0)
                        <iconify-icon icon="solar:paperclip-linear" class="em-mail-row__icon" title="Has attachments"></iconify-icon>
                    @endif
                    @if($msg->is_starred)
                        <iconify-icon icon="solar:star-bold" class="em-mail-row__icon em-mail-row__icon--star"></iconify-icon>
                    @endif
                    @if($isUnread)
                        <span class="em-mail-row__dot" title="Unread"></span>
                    @endif
                    @if($msg->folder === 'sent')
                        @include('admin.email-marketing.partials.provider-status-pill', [
                            'status' => $msg->provider_status ?: $msg->delivery_status ?: 'pending',
                        ])
                    @endif
                </span>
            </a>
        @empty
            <div class="em-inbox-empty em-empty-state">
                <span class="em-inbox-empty__icon">
                    <iconify-icon icon="solar:inbox-linear"></iconify-icon>
                </span>
                <h3>No messages here</h3>
                <p>Your {{ strtolower($title) }} is empty.</p>
                @can('compose emails')
                <a href="{{ route('admin.email.compose') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-8">
                    <iconify-icon icon="solar:pen-new-square-linear"></iconify-icon> Write a message
                </a>
                @endcan
            </div>
        @endforelse
    </div>

    @if($messages->hasPages())
    <div class="em-inbox-panel__footer">
        {{ $messages->links() }}
    </div>
    @endif
</div>

@include('admin.email-marketing.partials.nav-close', ['showInboxFolders' => true])
@endsection
