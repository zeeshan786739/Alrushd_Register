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

<div class="em-panel">
    <div class="em-panel__head">
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
    <div class="p-16">
        <form method="GET" class="mb-16">
            <div class="input-group">
                <input type="search" name="search" value="{{ request('search') }}" class="form-control radius-8" placeholder="Search subject, sender, or recipient" aria-label="Search emails">
                <button class="btn btn-outline-primary-600 radius-8">Search</button>
            </div>
        </form>

        @forelse($messages as $msg)
            <a href="{{ route('admin.email.show', $msg) }}" class="em-list-item {{ ! $msg->is_read && $msg->folder === 'inbox' ? 'unread' : '' }}">
                <div class="d-flex justify-content-between gap-12">
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center gap-8 flex-wrap">
                            <span>{{ $msg->folder === 'sent' || $msg->folder === 'draft' ? ($msg->to ?: 'No recipients') : ($msg->from_name ?: $msg->from_email ?: 'Unknown') }}</span>
                            @if(($msg->attachments_count ?? 0) > 0)
                                <iconify-icon icon="solar:paperclip-linear" class="em-meta" title="Has attachments"></iconify-icon>
                            @endif
                            @if($msg->is_starred)
                                <iconify-icon icon="solar:star-bold" class="text-warning"></iconify-icon>
                            @endif
                        </div>
                        <div>{{ $msg->subject ?: '(no subject)' }}</div>
                        <div class="em-meta text-truncate" style="max-width:520px">{{ \Illuminate\Support\Str::limit($msg->body_text ?: strip_tags((string) $msg->body_html), 100) }}</div>
                        @if($msg->folder === 'sent')
                            <div class="mt-4">
                                @if($msg->lead_id)<span class="em-crm-chip">Lead</span>@endif
                                @if($msg->customer_id)<span class="em-crm-chip">Customer</span>@endif
                                @if($msg->quotation_id)<span class="em-crm-chip">Quotation</span>@endif
                                @if($msg->invoice_id)<span class="em-crm-chip">Invoice</span>@endif
                            </div>
                        @endif
                    </div>
                    <div class="em-meta text-nowrap text-end">
                        {{ optional($msg->sent_at ?: $msg->received_at ?: $msg->created_at)->diffForHumans() }}
                        @if($msg->folder === 'sent')
                            <div class="mt-4">
                                @include('admin.email-marketing.partials.provider-status-pill', [
                                    'status' => $msg->provider_status ?: $msg->delivery_status ?: 'pending',
                                ])
                            </div>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="em-empty-state py-40">
                <iconify-icon icon="solar:inbox-linear"></iconify-icon>
                <h3>No messages here</h3>
                <p>Your {{ strtolower($title) }} is empty.</p>
            </div>
        @endforelse

        <div class="mt-16">{{ $messages->links() }}</div>
    </div>
</div>

@include('admin.email-marketing.partials.nav-close', ['showInboxFolders' => true])
@endsection
