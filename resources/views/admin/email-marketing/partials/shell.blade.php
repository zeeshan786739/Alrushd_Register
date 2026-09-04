@php
    $emStats = $stats ?? \App\Support\EmailMarketingDashboard::stats(\App\Support\OrganizationContext::idOrFail());
    $activeTab = $activeTab ?? 'overview';
    $setupComplete = ($emStats['mailbox_connected'] ?? false) && ($emStats['sendgrid_ready'] ?? false);
@endphp

<div class="em-platform-shell mb-20">
    <div class="em-platform-shell__hero">
        <div class="em-platform-shell__intro">
            <span class="em-platform-shell__eyebrow">Marketing</span>
            <h1 class="em-platform-shell__title">{{ $shellTitle ?? 'Email Marketing' }}</h1>
            @if(! empty($shellSubtitle))
                <p class="em-platform-shell__subtitle">{{ $shellSubtitle }}</p>
            @endif
            @if(($activeTab ?? '') === 'overview')
            <div class="em-hero-status">
                @if($setupComplete)
                    <span class="em-hero-status__pill em-hero-status__pill--ok">
                        <iconify-icon icon="solar:check-circle-linear"></iconify-icon> Ready to send
                    </span>
                @else
                    <span class="em-hero-status__pill em-hero-status__pill--warn">
                        <iconify-icon icon="solar:info-circle-linear"></iconify-icon> Setup required before sending
                    </span>
                @endif
            </div>
            @endif
        </div>
        @if(! empty($shellActions))
            <div class="em-platform-shell__actions">
                @foreach($shellActions as $action)
                    <a href="{{ $action['url'] }}" class="btn {{ $action['class'] ?? 'btn-outline-neutral-500 radius-8 px-20 py-11' }} fc-btn">
                        @if(! empty($action['icon']))
                            <iconify-icon icon="{{ $action['icon'] }}"></iconify-icon>
                        @endif
                        <span>{{ $action['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if(($activeTab ?? '') === 'overview')
    <div class="em-kpi-row">
        @can('view inbox')
        <a href="{{ route('admin.email.inbox') }}" class="em-kpi-card">
            <span class="em-kpi-card__icon em-kpi-card__icon--inbox">
                <iconify-icon icon="solar:inbox-linear"></iconify-icon>
            </span>
            <span class="em-kpi-card__body">
                <strong>{{ $emStats['inbox_unread'] ?? 0 }}</strong>
                <span>Unread inbox</span>
            </span>
        </a>
        @endcan
        @can('view campaigns')
        <a href="{{ route('admin.email.campaigns.index') }}" class="em-kpi-card">
            <span class="em-kpi-card__icon em-kpi-card__icon--campaigns">
                <iconify-icon icon="solar:letter-linear"></iconify-icon>
            </span>
            <span class="em-kpi-card__body">
                <strong>{{ $emStats['campaigns_total'] ?? 0 }}</strong>
                <span>Campaigns</span>
            </span>
        </a>
        @endcan
        <div class="em-kpi-card em-kpi-card--static">
            <span class="em-kpi-card__icon em-kpi-card__icon--reach">
                <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
            </span>
            <span class="em-kpi-card__body">
                <strong>{{ number_format(($emStats['audience_leads'] ?? 0) + ($emStats['audience_customers'] ?? 0)) }}</strong>
                <span>Reachable contacts</span>
            </span>
        </div>
        <div class="em-kpi-card em-kpi-card--static">
            <span class="em-kpi-card__icon em-kpi-card__icon--rate">
                <iconify-icon icon="solar:chart-2-linear"></iconify-icon>
            </span>
            <span class="em-kpi-card__body">
                <strong>{{ $emStats['last_open_rate'] ?? 0 }}%</strong>
                <span>Last open rate</span>
            </span>
        </div>
    </div>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mb-20 d-flex align-items-center gap-8">
    <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
    <span>{{ session('success') }}</span>
</div>
@endif

@include('admin.email-marketing.partials.module-nav', ['activeTab' => $activeTab, 'stats' => $emStats])
