@extends('admin.layouts.app')
@section('title', 'Account')
@section('content')
<div class="dashboard-main-body" id="acct-workspace-page">
@include('admin.account.partials.shell', [
    'activeTab' => 'overview',
    'stats' => $stats,
    'organization' => $organization,
    'shellTitle' => 'Account',
    'shellSubtitle' => 'Billing, customer payments, profile, and security for '.$organization->name.'.',
])

<div class="acct-layout">
    <div class="acct-layout__main">
        <div class="acct-panel">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Quick settings</h2>
                    <p class="acct-panel__desc">Everything about your school workspace in one place.</p>
                </div>
            </div>
            <div class="acct-list-shell">
                <div class="crm-leads-table">
                    <div class="crm-leads-table__head crm-leads-table__head--quick" aria-hidden="true">
                        <span>Setting</span><span>Status</span><span></span>
                    </div>
                    <div class="crm-leads-list">
                        @include('admin.account.partials.quick-setting-row', [
                            'url' => route('admin.account.payments.edit'),
                            'tone' => 'payments',
                            'icon' => 'solar:wallet-money-linear',
                            'title' => 'Customer payments',
                            'description' => 'Connect Stripe to collect fees on forms & admissions',
                            'status' => $stats['payments_configured'] ? 'Connected' : 'Setup needed',
                            'statusClass' => $stats['payments_configured'] ? 'acct-status-pill--ok' : 'acct-status-pill--warn',
                        ])
                        @include('admin.account.partials.quick-setting-row', [
                            'url' => route('admin.billing.index'),
                            'tone' => 'billing',
                            'icon' => 'solar:card-linear',
                            'title' => 'Enrolliq subscription',
                            'description' => 'Your plan, renewal dates, and billing history',
                            'status' => $stats['plan_name'] ?? 'View plan',
                            'statusClass' => 'acct-status-pill--muted',
                        ])
                        @include('admin.account.partials.quick-setting-row', [
                            'url' => route('admin.account.website.edit'),
                            'tone' => 'website',
                            'icon' => 'solar:globus-linear',
                            'title' => 'Public website',
                            'description' => 'Your live site URL and custom domain',
                            'status' => 'View link',
                            'statusClass' => 'acct-status-pill--muted',
                        ])
                        @include('admin.account.partials.quick-setting-row', [
                            'url' => route('admin.account.profile'),
                            'tone' => 'profile',
                            'icon' => 'solar:user-linear',
                            'title' => 'Profile',
                            'description' => 'Name, email, and avatar for your admin login',
                            'status' => null,
                        ])
                        @include('admin.account.partials.quick-setting-row', [
                            'url' => route('admin.account.security'),
                            'tone' => 'security',
                            'icon' => 'solar:shield-keyhole-linear',
                            'title' => 'Security',
                            'description' => 'Change password and keep access secure',
                            'status' => null,
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="acct-panel">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Where payments are used</h2>
                    <p class="acct-panel__desc">Your Stripe keys power checkout across the product.</p>
                </div>
            </div>
            <div class="acct-list-shell">
                <div class="crm-leads-table">
                    <div class="crm-leads-table__head crm-leads-table__head--usage" aria-hidden="true">
                        <span>Product area</span>
                    </div>
                    <div class="crm-leads-list">
                        @include('admin.account.partials.usage-row', [
                            'icon' => 'solar:document-add-linear',
                            'title' => 'Form Center',
                            'description' => 'Payment fields on admissions, enquiries, and custom forms',
                        ])
                        @include('admin.account.partials.usage-row', [
                            'icon' => 'solar:square-academic-cap-linear',
                            'title' => 'Admissions checkout',
                            'description' => 'Multi-step application fees and course payments',
                        ])
                        @include('admin.account.partials.usage-row', [
                            'icon' => 'solar:cart-large-2-linear',
                            'title' => 'Public checkout pages',
                            'description' => 'Stripe Checkout sessions on your marketing site',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="acct-layout__side">
        <div class="acct-profile-card">
            <span class="acct-profile-card__avatar">{{ \App\Support\AccountHubHelper::initials($admin) }}</span>
            <h3>{{ $admin->name }}</h3>
            <p>{{ $admin->email }}</p>
            <div class="acct-profile-card__meta">
                <span class="acct-status-pill acct-status-pill--ok">{{ $stats['organization_status'] }}</span>
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

@include('admin.account.partials.shell-close')
</div>
@endsection
