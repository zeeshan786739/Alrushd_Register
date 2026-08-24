@php
    use App\Support\AccountHubHelper;
    $acctStats = $stats ?? AccountHubHelper::stats();
    $activeTab = $activeTab ?? 'overview';
@endphp

<div class="acct-shell mb-24">
    <div class="acct-shell__hero">
        <div class="acct-shell__intro">
            <span class="acct-shell__eyebrow">Account</span>
            <h1 class="acct-shell__title">{{ $shellTitle ?? 'Your account' }}</h1>
            <p class="acct-shell__subtitle">{{ $shellSubtitle ?? 'Manage billing, payments, profile, and security for your school.' }}</p>
        </div>
        @if(! empty($shellActions))
        <div class="acct-shell__actions">
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

    @if(($activeTab ?? '') === 'overview' && ! empty($organization))
    <div class="acct-status-row">
        <span class="acct-status-pill {{ $organization->status?->badgeClass() ?? 'bg-success-focus text-success-main' }}">
            {{ $acctStats['organization_status'] ?? 'Active' }}
        </span>
        @if($acctStats['plan_name'] ?? false)
        <span class="acct-status-pill acct-status-pill--muted">{{ $acctStats['plan_name'] }} plan</span>
        @endif
        <span class="acct-status-pill {{ ($acctStats['payments_configured'] ?? false) ? 'acct-status-pill--ok' : 'acct-status-pill--warn' }}">
            <iconify-icon icon="{{ ($acctStats['payments_configured'] ?? false) ? 'solar:check-circle-linear' : 'solar:info-circle-linear' }}"></iconify-icon>
            {{ ($acctStats['payments_configured'] ?? false) ? 'Payments connected' : 'Payments not configured' }}
        </span>
    </div>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mb-20 d-flex align-items-center gap-8">
    <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 mb-20 d-flex align-items-center gap-8">
    <iconify-icon icon="solar:danger-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
    <span>{{ session('error') }}</span>
</div>
@endif

@include('admin.account.partials.module-nav', ['activeTab' => $activeTab, 'stats' => $acctStats])
