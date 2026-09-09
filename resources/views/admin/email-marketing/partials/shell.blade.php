@php
    $emStats = $stats ?? \App\Support\EmailMarketingDashboard::stats(\App\Support\OrganizationContext::idOrFail());
    $activeTab = $activeTab ?? 'overview';
    $setupComplete = ($emStats['mailbox_connected'] ?? false) && ($emStats['sendgrid_ready'] ?? false);
@endphp

@once
    @include('admin.crm.partials.styles')
    @include('admin.crm.partials.workspace-shell')
    @include('admin.email-marketing.partials.premium-styles')
@endonce

@include('admin.partials.page-header', [
    'title' => $shellTitle ?? 'Email Marketing',
    'subtitle' => $shellSubtitle ?? 'Send campaigns, manage replies, and reach leads from CRM, forms, and integrations.',
    'showBreadcrumb' => true,
    'breadcrumbs' => [['label' => 'Marketing'], ['label' => $shellTitle ?? 'Email Marketing']],
    'actions' => $shellActions ?? [],
    'hideFlash' => true,
])

<div class="em-workspace em-workspace-page crm-workspace-shell">
    @if(($activeTab ?? '') !== '')
        <div class="crm-metrics-strip {{ ($activeTab ?? '') === 'overview' ? '' : 'crm-metrics-strip--compact' }}" aria-label="Email marketing metrics">
            <div class="crm-metrics-strip__items">
                <span class="crm-metrics-strip__hint">Email marketing workspace</span>
                @if($setupComplete)
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="em-hero-status__pill em-hero-status__pill--ok">
                        <iconify-icon icon="solar:check-circle-linear"></iconify-icon> Ready to send
                    </span>
                @else
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="em-hero-status__pill em-hero-status__pill--warn">
                        <iconify-icon icon="solar:info-circle-linear"></iconify-icon> Setup required
                    </span>
                @endif
                @can('view inbox')
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">Unread</span>
                        <strong>{{ number_format($emStats['inbox_unread'] ?? 0) }}</strong>
                    </span>
                @endcan
                @can('view campaigns')
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">Campaigns</span>
                        <strong>{{ number_format($emStats['campaigns_total'] ?? 0) }}</strong>
                    </span>
                @endcan
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item">
                    <span class="crm-metrics-strip__label">Reachable</span>
                    <strong>{{ number_format(($emStats['audience_leads'] ?? 0) + ($emStats['audience_customers'] ?? 0)) }}</strong>
                </span>
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item">
                    <span class="crm-metrics-strip__label">Open rate</span>
                    <strong>{{ $emStats['last_open_rate'] ?? 0 }}%</strong>
                </span>
            </div>
        </div>
    @endif

    <div class="em-tabs-workspace">
        @if(($activeTab ?? '') === 'overview')
            <div class="em-tabs-workspace__head">
                <h2 class="em-tabs-workspace__title">Workspace sections</h2>
                <p class="em-tabs-workspace__sub">Overview, inbox, campaigns, templates, and delivery settings</p>
            </div>
        @endif
        @include('admin.email-marketing.partials.module-nav', ['activeTab' => $activeTab, 'stats' => $emStats])
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mx-3 mt-3 mb-0 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
            <span>{{ session('success') }}</span>
        </div>
    @endif

