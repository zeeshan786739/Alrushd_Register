@extends('admin.layouts.app')
@section('title', 'Email Marketing')
@section('content')
@php
    $setupComplete = $stats['mailbox_connected'] && $stats['sendgrid_ready'];
    $reachableTotal = $stats['audience_leads'] + $stats['audience_customers'];
    $integrationTotal = $stats['audience_facebook'] + $stats['audience_tiktok'] + $stats['audience_imports'];
@endphp

<div class="dashboard-main-body" id="em-workspace-page">
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'overview',
    'stats' => $stats,
    'shellTitle' => 'Email Marketing',
    'shellSubtitle' => 'Send campaigns, manage replies, and reach leads from CRM, forms, and integrations.',
    'shellActions' => array_values(array_filter([
        auth('admin')->user()?->can('create campaigns') ? [
            'label' => 'New campaign',
            'url' => route('admin.email.campaigns.create'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:add-circle-linear',
        ] : null,
        auth('admin')->user()?->can('compose emails') ? [
            'label' => 'Compose email',
            'url' => route('admin.email.compose'),
            'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
            'icon' => 'solar:pen-new-square-linear',
        ] : null,
    ])),
])

@if(! $setupComplete)
<div class="em-setup-banner">
    <div class="em-setup-banner__icon">
        <iconify-icon icon="solar:settings-linear"></iconify-icon>
    </div>
    <div class="em-setup-banner__body">
        <strong>Complete your delivery setup</strong>
        <p>
            @if(! $stats['mailbox_connected'])
                Connect your mailbox so you can send campaigns and receive replies.
            @else
                Finish your email settings to unlock delivery tracking and campaign analytics.
            @endif
        </p>
    </div>
    @can('manage mailbox settings')
    <a href="{{ route('admin.email.mailbox.settings') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn flex-shrink-0">
        <iconify-icon icon="solar:settings-linear"></iconify-icon> Configure now
    </a>
    @endcan
</div>
@endif

<div class="em-quick-grid">
    @can('create campaigns')
    <a href="{{ route('admin.email.campaigns.create') }}" class="em-quick-card">
        <span class="em-quick-card__icon"><iconify-icon icon="solar:add-circle-linear"></iconify-icon></span>
        <span class="em-quick-card__text"><strong>New campaign</strong><small>Broadcast to your audience</small></span>
    </a>
    @endcan
    @can('compose emails')
    <a href="{{ route('admin.email.compose') }}" class="em-quick-card">
        <span class="em-quick-card__icon"><iconify-icon icon="solar:pen-new-square-linear"></iconify-icon></span>
        <span class="em-quick-card__text"><strong>Compose</strong><small>One-to-one email</small></span>
    </a>
    @endcan
    @can('view templates')
    <a href="{{ route('admin.email.templates.index') }}" class="em-quick-card">
        <span class="em-quick-card__icon"><iconify-icon icon="solar:clipboard-list-linear"></iconify-icon></span>
        <span class="em-quick-card__text"><strong>Templates</strong><small>{{ $stats['templates_total'] }} saved</small></span>
    </a>
    @endcan
    @can('view inbox')
    <a href="{{ route('admin.email.inbox') }}" class="em-quick-card">
        <span class="em-quick-card__icon"><iconify-icon icon="solar:inbox-linear"></iconify-icon></span>
        <span class="em-quick-card__text"><strong>Inbox</strong><small>{{ $stats['inbox_unread'] }} unread</small></span>
    </a>
    @endcan
</div>

<div class="em-filter-workspace">
    <div class="em-filter-workspace__head">
        <h2 class="em-filter-workspace__title">Your audience</h2>
        <p class="em-filter-workspace__sub">Contacts with email addresses you can reach today · {{ number_format($reachableTotal + $stats['audience_forms'] + $integrationTotal) }} total sources</p>
    </div>
    <div class="em-audience-grid">
        <div class="em-audience-card">
            <span class="em-audience-card__icon em-audience-card__icon--leads"><iconify-icon icon="solar:user-hand-up-linear"></iconify-icon></span>
            <span class="em-audience-card__count">{{ number_format($stats['audience_leads']) }}</span>
            <span class="em-audience-card__label">CRM leads</span>
        </div>
        <div class="em-audience-card">
            <span class="em-audience-card__icon em-audience-card__icon--customers"><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon></span>
            <span class="em-audience-card__count">{{ number_format($stats['audience_customers']) }}</span>
            <span class="em-audience-card__label">Customers</span>
        </div>
        <div class="em-audience-card">
            <span class="em-audience-card__icon em-audience-card__icon--forms"><iconify-icon icon="solar:inbox-in-linear"></iconify-icon></span>
            <span class="em-audience-card__count">{{ number_format($stats['audience_forms']) }}</span>
            <span class="em-audience-card__label">Form submissions</span>
        </div>
        <div class="em-audience-card">
            <span class="em-audience-card__icon em-audience-card__icon--integrations"><iconify-icon icon="solar:plug-circle-linear"></iconify-icon></span>
            <span class="em-audience-card__count">{{ number_format($integrationTotal) }}</span>
            <span class="em-audience-card__label">Integrations & imports</span>
            @if($integrationTotal > 0)
                <span class="em-audience-card__sub">Facebook · TikTok · CSV</span>
            @endif
        </div>
    </div>
</div>

<div class="em-layout">
    <div class="em-layout__main">
        <div class="em-panel">
            <div class="em-panel__head">
                <div>
                    <h2 class="em-panel__title">Recent campaigns</h2>
                    <p class="em-panel__desc">Track performance and continue drafts.</p>
                </div>
                @can('view campaigns')
                    <a href="{{ route('admin.email.campaigns.index') }}" class="em-panel__link">View all</a>
                @endcan
            </div>
            @if($recentCampaigns->isEmpty())
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:letter-linear"></iconify-icon>
                    <strong>No campaigns yet</strong>
                    <span>Your first broadcast can target CRM leads, customers, form submissions, or a custom list.</span>
                </div>
            @else
                <div class="crm-leads-table">
                    <div class="crm-leads-table__head" aria-hidden="true">
                        <span>Campaign</span><span>Status</span><span>Sent</span><span>Open rate</span><span></span>
                    </div>
                    <div class="crm-leads-list">
                        @foreach($recentCampaigns as $campaign)
                            @include('admin.email-marketing.partials.campaign-row', ['campaign' => $campaign])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="em-layout__side">
        <div class="em-panel">
            <div class="em-panel__head">
                <h2 class="em-panel__title">Delivery status</h2>
            </div>
            <div class="em-checklist">
                <div class="em-checklist__item {{ $stats['mailbox_connected'] ? 'is-done' : 'is-pending' }}">
                    <span class="em-checklist__icon">
                        <iconify-icon icon="{{ $stats['mailbox_connected'] ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}"></iconify-icon>
                    </span>
                    <span class="em-checklist__body">
                        <strong>Mailbox</strong>
                        <span>{{ $stats['mailbox_connected'] ? 'Connected' : 'Not configured' }}</span>
                    </span>
                </div>
                <div class="em-checklist__item {{ $stats['sendgrid_ready'] ? 'is-done' : 'is-pending' }}">
                    <span class="em-checklist__icon">
                        <iconify-icon icon="{{ $stats['sendgrid_ready'] ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}"></iconify-icon>
                    </span>
                    <span class="em-checklist__body">
                        <strong>SendGrid</strong>
                        <span>{{ $stats['sendgrid_ready'] ? 'Ready for campaigns' : 'Not configured' }}</span>
                    </span>
                </div>
                @can('manage mailbox settings')
                <a href="{{ route('admin.email.mailbox.settings') }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10 fc-btn w-100">
                    <iconify-icon icon="solar:settings-linear"></iconify-icon> Mailbox settings
                </a>
                @endcan
            </div>
        </div>

        @if($attention)
        <div class="em-panel">
            <div class="em-panel__head"><h2 class="em-panel__title">Needs attention</h2></div>
            <div>
                @foreach($attention as $item)
                <a href="{{ $item['url'] }}" class="em-attention-row em-attention-row--{{ $item['severity'] }}">
                    <iconify-icon icon="{{ $item['severity'] === 'warning' ? 'solar:danger-triangle-linear' : 'solar:info-circle-linear' }}"></iconify-icon>
                    <span>
                        <strong>{{ $item['label'] }}</strong>
                        <small>{{ $item['meta'] }}</small>
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@include('admin.email-marketing.partials.shell-close')
</div>
@endsection
