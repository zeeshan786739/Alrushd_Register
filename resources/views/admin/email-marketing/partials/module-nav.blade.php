@php
    $emStats = $stats ?? [];
    $activeTab = $activeTab ?? 'overview';
@endphp
<nav class="em-module-nav" aria-label="Email marketing sections">
    <a href="{{ route('admin.email.dashboard') }}" class="em-module-nav__link {{ $activeTab === 'overview' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:widget-2-linear"></iconify-icon> Overview
    </a>
    @can('view inbox')
    <a href="{{ route('admin.email.inbox') }}" class="em-module-nav__link {{ $activeTab === 'inbox' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:inbox-linear"></iconify-icon> Inbox
        @if(($emStats['inbox_unread'] ?? 0) > 0)
            <span class="em-module-nav__count">{{ $emStats['inbox_unread'] }}</span>
        @endif
    </a>
    @endcan
    @can('view campaigns')
    <a href="{{ route('admin.email.campaigns.index') }}" class="em-module-nav__link {{ $activeTab === 'campaigns' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:letter-linear"></iconify-icon> Campaigns
    </a>
    @endcan
    @can('view templates')
    <a href="{{ route('admin.email.templates.index') }}" class="em-module-nav__link {{ $activeTab === 'templates' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon> Templates
    </a>
    @endcan
    @can('manage mailbox settings')
    <a href="{{ route('admin.email.mailbox.settings') }}" class="em-module-nav__link {{ $activeTab === 'settings' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:settings-linear"></iconify-icon> Settings
    </a>
    @endcan
</nav>
