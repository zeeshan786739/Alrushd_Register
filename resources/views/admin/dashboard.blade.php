@extends('admin.layouts.app')
@section('title') Dashboard @endsection

@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.dashboard.partials.premium-styles')
@php
    $pendingSetup = collect($setupChecklist)->where('done', false);
    $setupProgress = count($setupChecklist) > 0
        ? (int) round((collect($setupChecklist)->where('done', true)->count() / count($setupChecklist)) * 100)
        : 100;
    $headerActions = collect($quickActions ?? [])->take(3)->map(function ($action, $index) {
        return [
            'label' => $action['label'],
            'url' => $action['url'],
            'icon' => $action['icon'],
            'class' => $index === 0 ? 'btn-primary-600 radius-8 px-20 py-11' : 'btn-outline-neutral-500 radius-8 px-20 py-11',
        ];
    })->all();
@endphp

<div class="dashboard-main-body" id="admin-dashboard-page">
    @include('admin.partials.page-header', [
        'title' => $greeting,
        'subtitle' => 'Your school command center — admissions, CRM, marketing, forms, and team in one place.',
        'showBreadcrumb' => false,
        'actions' => $headerActions,
    ])

    <div class="dash-workspace crm-workspace-shell">
        <div class="crm-metrics-strip" aria-label="Organization status">
            <div class="crm-metrics-strip__items">
                <span class="crm-metrics-strip__hint">{{ $organization['name'] }}</span>
                <span class="dash-status-pill {{ $organization['status_class'] }}">{{ $organization['status_label'] }}</span>
                @if($organization['plan_name'])
                    <span class="dash-status-pill dash-status-pill--muted">{{ $organization['plan_name'] }} plan</span>
                @endif
                @if($organization['trial_days_left'] !== null)
                    <span class="dash-status-pill dash-status-pill--warn">
                        <iconify-icon icon="solar:clock-circle-linear"></iconify-icon>
                        {{ $organization['trial_days_left'] }} trial day{{ $organization['trial_days_left'] === 1 ? '' : 's' }} left
                    </span>
                @endif
            </div>
        </div>

        @if($kpis)
            <div class="dash-kpi-workspace">
                <div class="dash-kpi-workspace__head">
                    <h2 class="dash-kpi-workspace__title">Workspace snapshot</h2>
                    <p class="dash-kpi-workspace__sub">Key metrics across CRM, forms, marketing, and team</p>
                </div>
                <div class="dash-kpi-grid">
                    @foreach($kpis as $kpi)
                        <a href="{{ $kpi['href'] ?? '#' }}" class="dash-kpi-card dash-kpi-card--{{ $kpi['tone'] ?? 'navy' }}">
                            <span class="dash-kpi-card__icon"><iconify-icon icon="{{ $kpi['icon'] }}"></iconify-icon></span>
                            <span class="dash-kpi-card__label">{{ $kpi['label'] }}</span>
                            <strong class="dash-kpi-card__value">@if(! empty($kpi['prefix'])){{ $kpi['prefix'] }}@endif{{ $kpi['value'] }}</strong>
                            <span class="dash-kpi-card__meta">{{ $kpi['meta'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="dash-layout">
            <div class="dash-main">
                @if($attention->isNotEmpty())
                    <div class="crm-leads-toolbar">
                        <div class="crm-leads-toolbar__meta">
                            <strong><iconify-icon icon="solar:bell-bing-linear"></iconify-icon> Needs your attention · {{ $attention->count() }}</strong>
                            <span>Prioritized items across CRM, email, and integrations</span>
                        </div>
                    </div>
                    <div class="crm-list-shell">
                        <div class="crm-leads-table">
                            <div class="crm-leads-table__head" aria-hidden="true">
                                <span>Item</span><span>Priority</span><span>Details</span><span></span>
                            </div>
                            <div class="crm-leads-list">
                                @foreach($attention as $item)
                                    @include('admin.dashboard.partials.attention-row', compact('item'))
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if(! empty($quickActions))
                    <div class="dash-quick-workspace">
                        <div class="dash-kpi-workspace__head">
                            <h2 class="dash-kpi-workspace__title">Quick actions</h2>
                            <p class="dash-kpi-workspace__sub">Jump straight into the work you do most</p>
                        </div>
                        <div class="dash-quick-grid">
                            @foreach($quickActions as $action)
                                <a href="{{ $action['url'] }}" class="dash-quick-card">
                                    <span class="dash-quick-card__icon"><iconify-icon icon="{{ $action['icon'] }}"></iconify-icon></span>
                                    <span class="dash-quick-card__text">
                                        <strong>{{ $action['label'] }}</strong>
                                        <small>{{ $action['desc'] }}</small>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($recentSubmissions->isNotEmpty() || $recentLeads->isNotEmpty())
                    <div class="crm-leads-toolbar">
                        <div class="crm-leads-toolbar__meta">
                            <strong><iconify-icon icon="solar:history-linear"></iconify-icon> Recent activity</strong>
                            <span>Latest submissions and leads across your school</span>
                        </div>
                        <div class="crm-leads-toolbar__links">
                            @if($recentSubmissions->isNotEmpty())
                                <a href="{{ route('admin.form-manager.index') }}" class="crm-leads-toolbar__link">All submissions</a>
                            @endif
                            @if($recentLeads->isNotEmpty())
                                <a href="{{ route('admin.crm.leads.index') }}" class="crm-leads-toolbar__link">All leads</a>
                            @endif
                        </div>
                    </div>
                    <div class="crm-list-shell">
                        <div class="crm-leads-table">
                            <div class="crm-leads-table__head" aria-hidden="true">
                                <span>Record</span><span>Type</span><span>When</span><span></span>
                            </div>
                            <div class="crm-leads-list">
                                @foreach($recentSubmissions as $entry)
                                    @include('admin.dashboard.partials.activity-row', ['row' => $entry, 'rowType' => 'submission'])
                                @endforeach
                                @foreach($recentLeads as $lead)
                                    @include('admin.dashboard.partials.activity-row', ['row' => $lead, 'rowType' => 'lead'])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if($topForms->isNotEmpty())
                    <div class="crm-leads-toolbar">
                        <div class="crm-leads-toolbar__meta">
                            <strong><iconify-icon icon="solar:document-add-linear"></iconify-icon> Form Center</strong>
                            <span>Your most active forms and landing-page placements</span>
                        </div>
                        <a href="{{ route('admin.form-manager.create') }}" class="crm-leads-toolbar__link">Create form</a>
                    </div>
                    <div class="crm-list-shell">
                        <div class="crm-leads-table">
                            <div class="crm-leads-table__head" aria-hidden="true">
                                <span>Form</span><span>Status</span><span>Activity</span><span></span>
                            </div>
                            <div class="crm-leads-list">
                                @foreach($topForms as $form)
                                    @include('admin.dashboard.partials.form-row', compact('form'))
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <aside class="dash-aside">
                @if($setupChecklist)
                    <section class="dash-side-panel">
                        <div class="dash-side-panel__head">
                            <h2 class="dash-side-panel__title">Getting started</h2>
                            <p class="dash-side-panel__sub">{{ $setupProgress }}% complete</p>
                        </div>
                        <div class="dash-setup-progress" aria-hidden="true"><span style="width: {{ $setupProgress }}%"></span></div>
                        @if($pendingSetup->isEmpty())
                            <div class="dash-setup-done">
                                <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                                <strong>You're all set</strong>
                                <p>Core setup is complete. Explore modules below or check attention items.</p>
                            </div>
                        @else
                            <div class="crm-leads-list dash-side-list">
                                @foreach($pendingSetup as $item)
                                    @include('admin.dashboard.partials.setup-row', compact('item'))
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif

                @if(! empty($modules))
                    <section class="dash-side-panel">
                        <div class="dash-side-panel__head">
                            <h2 class="dash-side-panel__title">Modules</h2>
                            <p class="dash-side-panel__sub">Snapshot across your platform</p>
                        </div>
                        <div>
                            @foreach($modules as $module)
                                <a href="{{ $module['url'] }}" class="dash-module-card">
                                    <span class="dash-module-card__head">
                                        <span class="dash-module-card__icon"><iconify-icon icon="{{ $module['icon'] }}"></iconify-icon></span>
                                        <span>
                                            <strong>{{ $module['title'] }}</strong>
                                            <small>{{ $module['desc'] }}</small>
                                        </span>
                                    </span>
                                    @if(! empty($module['stats']))
                                        <span class="dash-module-card__stats">
                                            @foreach($module['stats'] as $stat)
                                                <span><em>{{ $stat['value'] }}</em>{{ $stat['label'] }}</span>
                                            @endforeach
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="dash-side-panel dash-account-card">
                    <div class="dash-account-card__icon"><iconify-icon icon="solar:buildings-2-linear"></iconify-icon></div>
                    <h3>{{ $organization['name'] }}</h3>
                    <p>School workspace on Enrolliq</p>
                    <div class="dash-account-card__meta">
                        <span class="{{ $organization['status_class'] }} radius-8 px-12 py-6 text-sm fw-semibold d-inline-block">{{ $organization['status_label'] }}</span>
                        @if($organization['plan_name'])
                            <span class="text-secondary-light text-sm">{{ $organization['plan_name'] }}</span>
                        @endif
                    </div>
                    <div class="dash-account-card__actions">
                        <a href="{{ route('admin.account.index') }}" class="btn btn-primary-600 radius-8 fc-btn w-100">
                            <iconify-icon icon="solar:user-id-linear"></iconify-icon> Account settings
                        </a>
                        <a href="{{ route('admin.account.payments.edit') }}" class="btn btn-outline-neutral-500 radius-8 fc-btn w-100">
                            <iconify-icon icon="solar:wallet-money-linear"></iconify-icon> Payment setup
                        </a>
                        <a href="{{ route('admin.billing.index') }}" class="btn btn-outline-neutral-500 radius-8 fc-btn w-100">
                            <iconify-icon icon="solar:card-linear"></iconify-icon> Billing
                        </a>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
