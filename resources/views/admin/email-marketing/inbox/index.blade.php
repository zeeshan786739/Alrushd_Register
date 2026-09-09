@extends('admin.layouts.app')
@section('title', $title)
@section('content')
<div class="dashboard-main-body" id="em-workspace-page">
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
<form id="em-sync-form" method="POST" action="{{ route('admin.email.inbox.sync') }}" class="d-none">
    @csrf
    @if($selectedSenderMailbox ?? null)<input type="hidden" name="sender_mailbox_id" value="{{ $selectedSenderMailbox->id }}">@endif
</form>
@endcan

@php $folder = $folder ?? 'inbox'; @endphp
@include('admin.email-marketing.partials.nav', ['folder' => $folder, 'showInboxFolders' => true])

@php
    $inboxConnections = ($senderMailboxes ?? collect())->filter->isImapConfigured();
    $activeInboxConnection = ($selectedSenderMailbox ?? null)
        ? $selectedSenderMailbox->isImapConfigured()
        : $inboxConnections->isNotEmpty();
    $failedInbox = ($selectedSenderMailbox ?? null)
        ?: $inboxConnections->first(fn ($item) => $item->last_sync_status === 'failed');
@endphp

@if($folder === 'inbox' && (! ($imapClientAvailable ?? false) || ! $activeInboxConnection))
    <div class="em-inline-alert em-inline-alert--warning">
        <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
        <div>
            <strong>Inbox connection required.</strong>
            @if(! ($imapClientAvailable ?? false))
                Install the project IMAP dependency with <code>composer require webklex/php-imap:^6.2</code>.
            @else
                Add the receiving mailbox's IMAP host, username, and app password in Mailbox Settings.
            @endif
            @can('manage mailbox settings')
                <a href="{{ route('admin.email.mailbox.settings') }}" class="em-inline-alert__link">Open Mailbox Settings</a>
            @endcan
        </div>
    </div>
@elseif($folder === 'inbox' && ($failedInbox?->last_sync_status === 'failed'))
    <div class="em-inline-alert em-inline-alert--danger">
        <iconify-icon icon="solar:danger-triangle-linear"></iconify-icon>
        <div>
            <strong>Last inbox sync failed.</strong>
            {{ $failedInbox->last_sync_error ?: 'Verify the IMAP server and credentials.' }}
            @can('manage mailbox settings')
                <a href="{{ route('admin.email.mailbox.settings') }}" class="em-inline-alert__link">Check Mailbox Settings</a>
            @endcan
        </div>
    </div>
@endif

@include('admin.email-marketing.partials.inbox.filter-workspace', [
    'senderMailboxes' => $senderMailboxes ?? collect(),
    'selectedSenderMailbox' => $selectedSenderMailbox ?? null,
])

<div class="crm-leads-toolbar">
    <div class="crm-leads-toolbar__left">
        <div class="crm-leads-toolbar__meta">
            <strong>{{ number_format($messages->total()) }} message{{ $messages->total() === 1 ? '' : 's' }}</strong>
            @if($messages->total() > 0)
                <span>{{ $messages->firstItem() }}–{{ $messages->lastItem() }} shown</span>
            @endif
        </div>
    </div>
    @if($folder === 'inbox' && auth('admin')->user()?->can('sync inbox'))
    <div class="crm-leads-toolbar__right">
        <button type="button" class="btn btn-outline-neutral-500 radius-8 px-16 py-10 fc-btn" onclick="document.getElementById('em-sync-form').submit();">
            <iconify-icon icon="solar:refresh-linear"></iconify-icon> Refresh
        </button>
    </div>
    @endif
</div>

<div class="em-list-shell">
    <div class="crm-leads-table">
        <div class="crm-leads-table__head crm-leads-table__head--inbox" aria-hidden="true">
            <span>From / To</span><span>Subject</span><span>Date</span><span>Flags</span><span></span>
        </div>
        <div class="crm-leads-list">
            @forelse($messages as $msg)
                @include('admin.email-marketing.partials.inbox-row', [
                    'msg' => $msg,
                    'senderMailboxes' => $senderMailboxes ?? collect(),
                ])
            @empty
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:inbox-linear"></iconify-icon>
                    <strong>No messages here</strong>
                    <span>Your {{ strtolower($title) }} is empty.</span>
                    @can('compose emails')
                    <a href="{{ route('admin.email.compose') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-8">
                        <iconify-icon icon="solar:pen-new-square-linear"></iconify-icon> Write a message
                    </a>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>
</div>

@include('admin.crm.partials.list-workspace-pagination', [
    'paginator' => $messages,
    'routeName' => match ($folder) {
        'sent' => 'admin.email.sent',
        'draft' => 'admin.email.drafts',
        'starred' => 'admin.email.starred',
        default => 'admin.email.inbox',
    },
    'entityLabel' => 'messages',
    'paginationId' => 'em-inbox-per-page',
])

@include('admin.email-marketing.partials.nav-close', ['showInboxFolders' => true])
@include('admin.email-marketing.partials.shell-close')
</div>
@endsection
