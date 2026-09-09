@php
    use App\Support\AccountHubHelper;
    $acctStats = $stats ?? AccountHubHelper::stats($organization ?? null);
    $activeTab = $activeTab ?? 'overview';
@endphp

@once
    @include('admin.crm.partials.styles')
    @include('admin.crm.partials.workspace-shell')
    @include('admin.account.partials.premium-styles')
@endonce

@include('admin.partials.page-header', [
    'title' => $shellTitle ?? 'Account',
    'subtitle' => $shellSubtitle ?? 'Manage billing, payments, profile, and security for your school.',
    'showBreadcrumb' => true,
    'breadcrumbs' => [['label' => 'Account'], ['label' => $shellTitle ?? 'Account']],
    'actions' => $shellActions ?? [],
    'hideFlash' => true,
])

<div class="acct-workspace acct-workspace-page crm-workspace-shell">
    <div class="crm-metrics-strip {{ ($activeTab ?? '') === 'overview' ? '' : 'crm-metrics-strip--compact' }}" aria-label="Account metrics">
        <div class="crm-metrics-strip__items">
            <span class="crm-metrics-strip__hint">Account workspace</span>
            <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
            @if($acctStats['payments_configured'] ?? false)
                <span class="acct-hero-status__pill acct-hero-status__pill--ok">
                    <iconify-icon icon="solar:check-circle-linear"></iconify-icon> Payments connected
                </span>
            @else
                <span class="acct-hero-status__pill acct-hero-status__pill--warn">
                    <iconify-icon icon="solar:info-circle-linear"></iconify-icon> Payments not configured
                </span>
            @endif
            @if($acctStats['plan_name'] ?? false)
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item">
                    <span class="crm-metrics-strip__label">Plan</span>
                    <strong>{{ $acctStats['plan_name'] }}</strong>
                </span>
            @endif
            @if($acctStats['organization_status'] ?? false)
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item">
                    <span class="crm-metrics-strip__label">School</span>
                    <strong>{{ $acctStats['organization_status'] }}</strong>
                </span>
            @endif
            @if($acctStats['payments_configured'] ?? false)
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="acct-hero-status__pill acct-hero-status__pill--muted">
                    {{ ($acctStats['payments_test_mode'] ?? false) ? 'Test mode' : 'Live mode' }}
                </span>
            @endif
        </div>
    </div>

    <div class="acct-tabs-workspace">
        @if(($activeTab ?? '') === 'overview')
            <div class="acct-tabs-workspace__head">
                <h2 class="acct-tabs-workspace__title">Workspace sections</h2>
                <p class="acct-tabs-workspace__sub">Overview, profile, security, payments, website, and billing</p>
            </div>
        @endif
        @include('admin.account.partials.module-nav', ['activeTab' => $activeTab, 'stats' => $acctStats])
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mx-3 mt-3 mb-0 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 mx-3 mt-3 mb-0 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:danger-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
            <span>{{ session('error') }}</span>
        </div>
    @endif
