@extends('admin.layouts.app')
@section('title', 'Account')
@section('content')
@include('admin.account.partials.shell', [
    'activeTab' => 'overview',
    'stats' => $stats,
    'organization' => $organization,
    'shellTitle' => 'Account',
    'shellSubtitle' => 'Billing, customer payments, profile, and security for '.$organization->name.'.',
])

<div class="row g-4">
    <div class="col-xxl-8">
        <div class="acct-panel mb-24">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Quick settings</h2>
                    <p class="acct-panel__desc">Everything about your school workspace in one place.</p>
                </div>
            </div>
            <div class="acct-quick-grid">
                <a href="{{ route('admin.account.payments.edit') }}" class="acct-quick-card">
                    <span class="acct-quick-card__icon acct-quick-card__icon--payments">
                        <iconify-icon icon="solar:wallet-money-linear"></iconify-icon>
                    </span>
                    <span class="acct-quick-card__text">
                        <strong>Customer payments</strong>
                        <small>Connect Stripe to collect fees on forms &amp; admissions</small>
                    </span>
                    <span class="acct-quick-card__status {{ $stats['payments_configured'] ? 'is-ok' : 'is-warn' }}">
                        {{ $stats['payments_configured'] ? 'Connected' : 'Setup needed' }}
                    </span>
                </a>
                <a href="{{ route('admin.billing.index') }}" class="acct-quick-card">
                    <span class="acct-quick-card__icon acct-quick-card__icon--billing">
                        <iconify-icon icon="solar:card-linear"></iconify-icon>
                    </span>
                    <span class="acct-quick-card__text">
                        <strong>Enrolliq subscription</strong>
                        <small>Your plan, renewal dates, and billing history</small>
                    </span>
                    <span class="acct-quick-card__status is-muted">{{ $stats['plan_name'] ?? 'View plan' }}</span>
                </a>
                <a href="{{ route('admin.account.profile') }}" class="acct-quick-card">
                    <span class="acct-quick-card__icon acct-quick-card__icon--profile">
                        <iconify-icon icon="solar:user-linear"></iconify-icon>
                    </span>
                    <span class="acct-quick-card__text">
                        <strong>Profile</strong>
                        <small>Name, email, and avatar for your admin login</small>
                    </span>
                </a>
                <a href="{{ route('admin.account.security') }}" class="acct-quick-card">
                    <span class="acct-quick-card__icon acct-quick-card__icon--security">
                        <iconify-icon icon="solar:shield-keyhole-linear"></iconify-icon>
                    </span>
                    <span class="acct-quick-card__text">
                        <strong>Security</strong>
                        <small>Change password and keep access secure</small>
                    </span>
                </a>
            </div>
        </div>

        <div class="acct-panel">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Where payments are used</h2>
                    <p class="acct-panel__desc">Your Stripe keys power checkout across the product.</p>
                </div>
            </div>
            <div class="acct-usage-list">
                <div class="acct-usage-row">
                    <iconify-icon icon="solar:document-add-linear"></iconify-icon>
                    <div>
                        <strong>Form Center</strong>
                        <small>Payment fields on admissions, enquiries, and custom forms</small>
                    </div>
                </div>
                <div class="acct-usage-row">
                    <iconify-icon icon="solar:square-academic-cap-linear"></iconify-icon>
                    <div>
                        <strong>Admissions checkout</strong>
                        <small>Multi-step application fees and course payments</small>
                    </div>
                </div>
                <div class="acct-usage-row">
                    <iconify-icon icon="solar:cart-large-2-linear"></iconify-icon>
                    <div>
                        <strong>Public checkout pages</strong>
                        <small>Stripe Checkout sessions on your marketing site</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-4">
        <div class="acct-panel acct-profile-card mb-24">
            <span class="acct-profile-card__avatar">{{ \App\Support\AccountHubHelper::initials($admin) }}</span>
            <h3>{{ $admin->name }}</h3>
            <p>{{ $admin->email }}</p>
            <div class="acct-profile-card__meta">
                <span class="acct-status-pill {{ $organization->status?->badgeClass() ?? '' }}">{{ $stats['organization_status'] }}</span>
            </div>
            <div class="acct-profile-card__actions">
                <a href="{{ route('admin.account.profile') }}" class="btn btn-outline-neutral-500 radius-8 fc-btn w-100">Edit profile</a>
                <a href="{{ route('admin.account.security') }}" class="btn btn-outline-neutral-500 radius-8 fc-btn w-100">Change password</a>
            </div>
        </div>

        @unless($stats['payments_configured'])
        <div class="acct-setup-banner">
            <iconify-icon icon="solar:wallet-money-linear"></iconify-icon>
            <div>
                <strong>Connect Stripe to get paid</strong>
                <p>Add your publishable and secret keys to accept card payments from parents and applicants.</p>
            </div>
            <a href="{{ route('admin.account.payments.edit') }}" class="btn btn-primary-600 radius-8 fc-btn">Set up payments</a>
        </div>
        @endunless
    </div>
</div>
@endsection
