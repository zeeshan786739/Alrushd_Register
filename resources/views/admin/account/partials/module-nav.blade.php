@php $activeTab = $activeTab ?? 'overview'; @endphp
<nav class="acct-module-nav" aria-label="Account sections">
    <a href="{{ route('admin.account.index') }}" class="acct-module-nav__link {{ $activeTab === 'overview' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:widget-2-linear"></iconify-icon> Overview
    </a>
    <a href="{{ route('admin.account.profile') }}" class="acct-module-nav__link {{ $activeTab === 'profile' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:user-linear"></iconify-icon> Profile
    </a>
    <a href="{{ route('admin.account.security') }}" class="acct-module-nav__link {{ $activeTab === 'security' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:shield-keyhole-linear"></iconify-icon> Security
    </a>
    <a href="{{ route('admin.account.payments.edit') }}" class="acct-module-nav__link {{ $activeTab === 'payments' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:wallet-money-linear"></iconify-icon> Payments
    </a>
    <a href="{{ route('admin.account.website.edit') }}" class="acct-module-nav__link {{ $activeTab === 'website' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:globus-linear"></iconify-icon> Website
    </a>
    <a href="{{ route('admin.billing.index') }}" class="acct-module-nav__link {{ $activeTab === 'billing' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:card-linear"></iconify-icon> Billing
    </a>
</nav>
