@extends('admin.layouts.app')
@section('title') Dashboard @endsection

@section('css')
<style>
    .crm-dash-section { margin-bottom: 28px; }
    .crm-dash-section__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }
    .crm-dash-section__title {
        font-size: 15px;
        font-weight: 700;
        color: var(--crm-brand);
        margin: 0;
        letter-spacing: -0.02em;
    }
    .crm-dash-section__link {
        font-size: 13px;
        font-weight: 600;
        color: var(--crm-accent);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .crm-dash-section__link:hover { color: var(--crm-brand); }

    .crm-stat-card {
        position: relative;
        display: block;
        height: 100%;
        border-radius: 14px;
        border: 1px solid var(--crm-border);
        background: var(--crm-surface);
        overflow: hidden;
        box-shadow: var(--crm-shadow-sm);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        text-decoration: none !important;
        color: inherit;
    }
    .crm-stat-card--link { cursor: pointer; }
    .crm-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--crm-shadow-lg);
        border-color: rgba(197, 168, 109, 0.35);
    }
    .crm-stat-card__glow {
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        height: 3px;
        opacity: 0.9;
    }
    .crm-stat-card--navy .crm-stat-card__glow { background: linear-gradient(90deg, #0F274A, #3d5a80); }
    .crm-stat-card--green .crm-stat-card__glow { background: linear-gradient(90deg, #16a34a, #4ade80); }
    .crm-stat-card--gold .crm-stat-card__glow { background: linear-gradient(90deg, #C5A86D, #e8d5b0); }
    .crm-stat-card--amber .crm-stat-card__glow { background: linear-gradient(90deg, #d97706, #fbbf24); }
    .crm-stat-card--purple .crm-stat-card__glow { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
    .crm-stat-card--rose .crm-stat-card__glow { background: linear-gradient(90deg, #db2777, #f472b6); }
    .crm-stat-card--cyan .crm-stat-card__glow { background: linear-gradient(90deg, #0891b2, #22d3ee); }
    .crm-stat-card--slate .crm-stat-card__glow { background: linear-gradient(90deg, #475569, #94a3b8); }

    .crm-stat-card__body { padding: 18px 18px 16px; }
    .crm-stat-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .crm-stat-card__info {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        min-width: 0;
    }
    .crm-stat-card__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 22px;
    }
    .crm-stat-card--navy .crm-stat-card__icon { background: rgba(15, 39, 74, 0.1); color: #0F274A; }
    .crm-stat-card--green .crm-stat-card__icon { background: rgba(22, 163, 74, 0.1); color: #16a34a; }
    .crm-stat-card--gold .crm-stat-card__icon { background: rgba(197, 168, 109, 0.16); color: #9a7b42; }
    .crm-stat-card--amber .crm-stat-card__icon { background: rgba(217, 119, 6, 0.1); color: #d97706; }
    .crm-stat-card--purple .crm-stat-card__icon { background: rgba(124, 58, 237, 0.1); color: #7c3aed; }
    .crm-stat-card--rose .crm-stat-card__icon { background: rgba(219, 39, 119, 0.1); color: #db2777; }
    .crm-stat-card--cyan .crm-stat-card__icon { background: rgba(8, 145, 178, 0.1); color: #0891b2; }
    .crm-stat-card--slate .crm-stat-card__icon { background: rgba(71, 85, 105, 0.1); color: #475569; }

    .crm-stat-card__text { min-width: 0; }
    .crm-stat-card__label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--crm-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }
    .crm-stat-card__value {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--crm-text);
        line-height: 1.2;
        letter-spacing: -0.02em;
    }
    .crm-stat-card__meta {
        display: block;
        font-size: 11px;
        color: var(--crm-text-muted);
        margin-top: 2px;
    }
    .crm-stat-card__chart { flex-shrink: 0; margin-top: 4px; }
    .crm-stat-card__footer {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .crm-stat-card__footer-text {
        font-size: 12px;
        color: var(--crm-text-muted);
    }
    .crm-stat-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
    }
    .crm-stat-badge--up { background: rgba(22, 163, 74, 0.12); color: #15803d; }
    .crm-stat-badge--gold { background: var(--crm-accent-soft); color: #9a7b42; }
    .crm-stat-badge--navy { background: var(--crm-brand-soft); color: var(--crm-brand); }
    .crm-stat-badge--muted { background: var(--crm-surface-sunken); color: var(--crm-text-muted); }
    .crm-stat-badge--live { background: rgba(22, 163, 74, 0.12); color: #15803d; }
    .crm-stat-badge--draft { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    .crm-form-card .crm-stat-card__value { font-size: 1.25rem; }
    .crm-form-card .crm-stat-card__label {
        text-transform: none;
        letter-spacing: 0;
        font-size: 13px;
        color: var(--crm-text);
    }

    .crm-dash-grid > [class*="col-"] {
        animation: crmStatIn 0.45s cubic-bezier(0.4, 0, 0.2, 1) both;
    }
    .crm-dash-grid > [class*="col-"]:nth-child(1) { animation-delay: 0.04s; }
    .crm-dash-grid > [class*="col-"]:nth-child(2) { animation-delay: 0.08s; }
    .crm-dash-grid > [class*="col-"]:nth-child(3) { animation-delay: 0.12s; }
    .crm-dash-grid > [class*="col-"]:nth-child(4) { animation-delay: 0.16s; }
    .crm-dash-grid > [class*="col-"]:nth-child(5) { animation-delay: 0.2s; }
    .crm-dash-grid > [class*="col-"]:nth-child(6) { animation-delay: 0.24s; }

    @keyframes crmStatIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: none; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">

    @include('admin.partials.page-header', [
        'title' => 'Dashboard',
        'subtitle' => 'Overview of users, students, revenue, and Form Center activity.',
        'hideFlash' => true,
    ])

    {{-- CRM Overview --}}
    <section class="crm-dash-section">
        <div class="crm-dash-section__head">
            <h2 class="crm-dash-section__title">CRM Overview</h2>
        </div>
        <div class="row gy-4 crm-dash-grid">
            <div class="col-xxl-4 col-sm-6">
                @include('admin.partials.dashboard-stat-card', [
                    'label' => 'Users',
                    'value' => number_format($users),
                    'icon' => 'solar:users-group-rounded-bold',
                    'tone' => 'navy',
                    'badge' => '+' . number_format($userIncrease),
                    'footer' => 'new this week',
                    'chartId' => 'new-user-chart',
                ])
            </div>
            <div class="col-xxl-4 col-sm-6">
                @include('admin.partials.dashboard-stat-card', [
                    'label' => 'Students',
                    'value' => number_format($students),
                    'icon' => 'solar:square-academic-cap-bold',
                    'tone' => 'green',
                    'badge' => '+' . number_format($studentIncrease),
                    'footer' => 'new this week',
                    'chartId' => 'active-user-chart',
                ])
            </div>
            <div class="col-xxl-4 col-sm-6">
                @include('admin.partials.dashboard-stat-card', [
                    'label' => 'Revenue',
                    'value' => '$' . number_format($revenue, 2),
                    'icon' => 'solar:wallet-money-bold',
                    'tone' => 'gold',
                    'badge' => '$' . number_format($revenueThisWeek, 2),
                    'badgeClass' => 'crm-stat-badge--gold',
                    'footer' => 'this week',
                    'chartId' => 'total-sales-chart',
                ])
            </div>
        </div>
    </section>

    {{-- Form Center --}}
    <section class="crm-dash-section">
        <div class="crm-dash-section__head">
            <h2 class="crm-dash-section__title">Form Center</h2>
            <a href="{{ route('admin.form-manager.index') }}" class="crm-dash-section__link">
                View all forms
                <iconify-icon icon="solar:arrow-right-linear"></iconify-icon>
            </a>
        </div>
        <div class="row gy-4 crm-dash-grid">
            <div class="col-xxl-3 col-lg-4 col-sm-6">
                @include('admin.partials.dashboard-stat-card', [
                    'label' => 'Total Forms',
                    'value' => number_format($formStats['total_forms']),
                    'icon' => 'solar:document-text-bold',
                    'tone' => 'navy',
                    'href' => route('admin.form-manager.index'),
                    'footer' => 'All forms in system',
                ])
            </div>
            <div class="col-xxl-3 col-lg-4 col-sm-6">
                @include('admin.partials.dashboard-stat-card', [
                    'label' => 'Active Forms',
                    'value' => number_format($formStats['active_forms']),
                    'icon' => 'solar:check-circle-bold',
                    'tone' => 'green',
                    'badge' => $formStats['active_forms'] . ' live',
                    'badgeClass' => 'crm-stat-badge--live',
                    'footer' => 'Accepting submissions',
                    'href' => route('admin.form-manager.index'),
                ])
            </div>
            <div class="col-xxl-3 col-lg-4 col-sm-6">
                @include('admin.partials.dashboard-stat-card', [
                    'label' => 'Total Submissions',
                    'value' => number_format($formStats['total_submissions']),
                    'icon' => 'solar:inbox-bold',
                    'tone' => 'purple',
                    'badge' => '+' . number_format($formStats['submissions_this_week']),
                    'footer' => 'this week',
                    'chartId' => 'form-submissions-chart',
                ])
            </div>
            <div class="col-xxl-3 col-lg-4 col-sm-6">
                @include('admin.partials.dashboard-stat-card', [
                    'label' => 'On Landing Page',
                    'value' => number_format($formStats['landing_forms']),
                    'icon' => 'solar:global-bold',
                    'tone' => 'amber',
                    'badge' => number_format($formStats['forms_with_entries']) . ' with data',
                    'badgeClass' => 'crm-stat-badge--navy',
                    'footer' => 'Published on homepage',
                    'href' => route('admin.form-manager.index'),
                ])
            </div>
        </div>
    </section>

    @if($topForms->isNotEmpty())
    <section class="crm-dash-section">
        <div class="crm-dash-section__head">
            <h2 class="crm-dash-section__title">Forms at a Glance</h2>
            <a href="{{ route('admin.form-manager.create') }}" class="crm-dash-section__link">
                <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                Create form
            </a>
        </div>
        <div class="row gy-4 crm-dash-grid">
            @foreach($topForms as $form)
            <div class="col-xxl-4 col-lg-6 col-sm-6">
                <div class="crm-form-card">
                    @include('admin.partials.dashboard-stat-card', [
                        'label' => $form->displayLabel(),
                        'value' => number_format($form->entries_count) . ' entries',
                        'icon' => 'solar:document-text-linear',
                        'tone' => $form->is_active ? 'cyan' : 'slate',
                        'badge' => $form->is_active ? 'Active' : 'Inactive',
                        'badgeClass' => $form->is_active ? 'crm-stat-badge--live' : 'crm-stat-badge--draft',
                        'footer' => $form->hasPlacement('landing') ? 'On landing page' : 'Not on landing',
                        'meta' => $form->slug,
                        'href' => route('admin.form-manager.entries', $form),
                    ])
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection
