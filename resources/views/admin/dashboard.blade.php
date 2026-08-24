@extends('admin.layouts.app')
@section('title') Dashboard @endsection

@section('content')
@php
    $pendingSetup = collect($setupChecklist)->where('done', false);
    $setupProgress = count($setupChecklist) > 0
        ? (int) round((collect($setupChecklist)->where('done', true)->count() / count($setupChecklist)) * 100)
        : 100;
@endphp

<div class="dashboard-main-body dash-home">

    {{-- Hero --}}
    <section class="dash-shell mb-24">
        <div class="dash-shell__hero">
            <div class="dash-shell__intro">
                <span class="dash-shell__eyebrow">{{ $organization['name'] }}</span>
                <h1 class="dash-shell__title">{{ $greeting }}</h1>
                <p class="dash-shell__subtitle">
                    Your school command center — admissions, CRM, marketing, forms, and team in one place.
                </p>
                <div class="dash-shell__status">
                    <span class="dash-status-pill {{ $organization['status_class'] }}">
                        {{ $organization['status_label'] }}
                    </span>
                    @if($organization['plan_name'])
                        <span class="dash-status-pill dash-status-pill--muted">
                            {{ $organization['plan_name'] }} plan
                        </span>
                    @endif
                    @if($organization['trial_days_left'] !== null)
                        <span class="dash-status-pill dash-status-pill--warn">
                            <iconify-icon icon="solar:clock-circle-linear"></iconify-icon>
                            {{ $organization['trial_days_left'] }} trial day{{ $organization['trial_days_left'] === 1 ? '' : 's' }} left
                        </span>
                    @endif
                </div>
            </div>
            @if(! empty($quickActions))
            <div class="dash-shell__actions">
                @foreach(array_slice($quickActions, 0, 3) as $action)
                    <a href="{{ $action['url'] }}" class="btn {{ $loop->first ? 'btn-primary-600' : 'btn-outline-neutral-500' }} radius-8 px-20 py-11 fc-btn">
                        <iconify-icon icon="{{ $action['icon'] }}"></iconify-icon>
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
            @endif
        </div>

        @if($kpis)
        <div class="dash-kpi-row">
            @foreach($kpis as $kpi)
            <a href="{{ $kpi['href'] ?? '#' }}" class="dash-kpi-card dash-kpi-card--{{ $kpi['tone'] ?? 'navy' }}">
                <span class="dash-kpi-card__icon">
                    <iconify-icon icon="{{ $kpi['icon'] }}"></iconify-icon>
                </span>
                <span class="dash-kpi-card__body">
                    <span class="dash-kpi-card__label">{{ $kpi['label'] }}</span>
                    <strong class="dash-kpi-card__value">
                        @if(! empty($kpi['prefix'])){{ $kpi['prefix'] }}@endif{{ $kpi['value'] }}
                    </strong>
                    <span class="dash-kpi-card__meta">{{ $kpi['meta'] }}</span>
                </span>
                <iconify-icon icon="solar:alt-arrow-right-linear" class="dash-kpi-card__chevron"></iconify-icon>
            </a>
            @endforeach
        </div>
        @endif
    </section>

    @if(session('success'))
    <div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mb-24 d-flex align-items-center gap-8">
        <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="row g-4">
        {{-- Main column --}}
        <div class="col-xxl-8">
            @if($attention->isNotEmpty())
            <div class="dash-panel mb-24">
                <div class="dash-panel__head">
                    <div>
                        <h2 class="dash-panel__title">Needs your attention</h2>
                        <p class="dash-panel__desc">Prioritized items across CRM, email, and integrations.</p>
                    </div>
                    <span class="dash-panel__badge">{{ $attention->count() }}</span>
                </div>
                <div class="dash-attention-list">
                    @foreach($attention as $item)
                    <a href="{{ $item['url'] }}" class="dash-attention-row dash-attention-row--{{ $item['severity'] }}">
                        <span class="dash-attention-row__icon">
                            <iconify-icon icon="{{ $item['severity'] === 'danger' ? 'solar:danger-circle-linear' : ($item['severity'] === 'warning' ? 'solar:info-circle-linear' : 'solar:bell-linear') }}"></iconify-icon>
                        </span>
                        <span class="dash-attention-row__body">
                            <span class="dash-attention-row__type">{{ $item['type'] }}</span>
                            <strong>{{ $item['label'] }}</strong>
                            <small>{{ $item['meta'] }}</small>
                        </span>
                        <iconify-icon icon="solar:alt-arrow-right-linear" class="dash-attention-row__chevron"></iconify-icon>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if(! empty($quickActions))
            <div class="dash-panel mb-24">
                <div class="dash-panel__head">
                    <div>
                        <h2 class="dash-panel__title">Quick actions</h2>
                        <p class="dash-panel__desc">Jump straight into the work you do most.</p>
                    </div>
                </div>
                <div class="dash-quick-grid">
                    @foreach($quickActions as $action)
                    <a href="{{ $action['url'] }}" class="dash-quick-card">
                        <span class="dash-quick-card__icon">
                            <iconify-icon icon="{{ $action['icon'] }}"></iconify-icon>
                        </span>
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
            <div class="dash-panel mb-24">
                <div class="dash-panel__head">
                    <div>
                        <h2 class="dash-panel__title">Recent activity</h2>
                        <p class="dash-panel__desc">Latest submissions and leads across your school.</p>
                    </div>
                </div>
                <div class="dash-activity-grid">
                    @if($recentSubmissions->isNotEmpty())
                    <div class="dash-activity-col">
                        <div class="dash-activity-col__head">
                            <h3>Form submissions</h3>
                            <a href="{{ route('admin.form-manager.index') }}">View all</a>
                        </div>
                        <div class="dash-feed">
                            @foreach($recentSubmissions as $entry)
                            <a href="{{ $entry->form ? route('admin.form-manager.entries.show', [$entry->form, $entry]) : route('admin.form-manager.index') }}" class="dash-feed-row">
                                <span class="dash-feed-row__icon"><iconify-icon icon="solar:document-text-linear"></iconify-icon></span>
                                <span class="dash-feed-row__body">
                                    <strong>{{ $entry->form?->displayLabel() ?? 'Form submission' }}</strong>
                                    <small>{{ $entry->submitted_at?->diffForHumans() ?? 'Recently' }}</small>
                                </span>
                                @if($entry->status)
                                <span class="dash-feed-row__pill">{{ ucfirst($entry->status) }}</span>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($recentLeads->isNotEmpty())
                    <div class="dash-activity-col">
                        <div class="dash-activity-col__head">
                            <h3>New leads</h3>
                            <a href="{{ route('admin.crm.leads.index') }}">View all</a>
                        </div>
                        <div class="dash-feed">
                            @foreach($recentLeads as $lead)
                            <a href="{{ route('admin.crm.leads.show', $lead) }}" class="dash-feed-row">
                                <span class="dash-feed-row__icon"><iconify-icon icon="solar:user-hand-up-linear"></iconify-icon></span>
                                <span class="dash-feed-row__body">
                                    <strong>{{ trim($lead->first_name.' '.$lead->last_name) ?: ($lead->email ?: 'Lead #'.$lead->id) }}</strong>
                                    <small>{{ $lead->created_at?->diffForHumans() }} · {{ ucfirst(str_replace('_', ' ', (string) $lead->lead_status)) }}</small>
                                </span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($topForms->isNotEmpty())
            <div class="dash-panel">
                <div class="dash-panel__head">
                    <div>
                        <h2 class="dash-panel__title">Form Center</h2>
                        <p class="dash-panel__desc">Your most active forms and landing-page placements.</p>
                    </div>
                    <a href="{{ route('admin.form-manager.create') }}" class="dash-panel__link">
                        <iconify-icon icon="solar:add-circle-linear"></iconify-icon> Create form
                    </a>
                </div>
                <div class="dash-form-grid">
                    @foreach($topForms as $form)
                    <a href="{{ route('admin.form-manager.entries', $form) }}" class="dash-form-card {{ $form->is_active ? '' : 'is-inactive' }}">
                        <span class="dash-form-card__top">
                            <span class="dash-form-card__icon">
                                <iconify-icon icon="solar:document-text-linear"></iconify-icon>
                            </span>
                            <span class="dash-form-card__badge {{ $form->is_active ? 'is-live' : 'is-draft' }}">
                                {{ $form->is_active ? 'Live' : 'Inactive' }}
                            </span>
                        </span>
                        <strong class="dash-form-card__title">{{ $form->displayLabel() }}</strong>
                        <span class="dash-form-card__stat">{{ number_format($form->entries_count) }} submissions</span>
                        <span class="dash-form-card__meta">
                            {{ $form->hasPlacement('landing') ? 'On landing page' : 'Not on landing' }}
                            · {{ $form->slug }}
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-xxl-4">
            @if($setupChecklist)
            <div class="dash-panel dash-panel--sidebar mb-24">
                <div class="dash-panel__head">
                    <div>
                        <h2 class="dash-panel__title">Getting started</h2>
                        <p class="dash-panel__desc">{{ $setupProgress }}% complete</p>
                    </div>
                </div>
                <div class="dash-setup-progress" aria-hidden="true">
                    <span style="width: {{ $setupProgress }}%"></span>
                </div>
                @if($pendingSetup->isEmpty())
                <div class="dash-setup-done">
                    <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                    <strong>You're all set</strong>
                    <p>Core setup is complete. Explore modules below or check attention items.</p>
                </div>
                @else
                <div class="dash-setup-list">
                    @foreach($pendingSetup as $item)
                    <a href="{{ $item['url'] }}" class="dash-setup-row">
                        <span class="dash-setup-row__icon">
                            <iconify-icon icon="{{ $item['icon'] }}"></iconify-icon>
                        </span>
                        <span class="dash-setup-row__body">
                            <strong>{{ $item['label'] }}</strong>
                            <small>{{ $item['desc'] }}</small>
                        </span>
                        <iconify-icon icon="solar:alt-arrow-right-linear" class="dash-setup-row__chevron"></iconify-icon>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            @if(! empty($modules))
            <div class="dash-panel dash-panel--sidebar mb-24">
                <div class="dash-panel__head">
                    <div>
                        <h2 class="dash-panel__title">Modules</h2>
                        <p class="dash-panel__desc">Snapshot across your platform.</p>
                    </div>
                </div>
                <div class="dash-module-list">
                    @foreach($modules as $module)
                    <a href="{{ $module['url'] }}" class="dash-module-card">
                        <span class="dash-module-card__head">
                            <span class="dash-module-card__icon">
                                <iconify-icon icon="{{ $module['icon'] }}"></iconify-icon>
                            </span>
                            <span>
                                <strong>{{ $module['title'] }}</strong>
                                <small>{{ $module['desc'] }}</small>
                            </span>
                        </span>
                        @if(! empty($module['stats']))
                        <span class="dash-module-card__stats">
                            @foreach($module['stats'] as $stat)
                            <span>
                                <em>{{ $stat['value'] }}</em>
                                {{ $stat['label'] }}
                            </span>
                            @endforeach
                        </span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="dash-panel dash-panel--sidebar dash-account-card">
                <div class="dash-account-card__icon">
                    <iconify-icon icon="solar:buildings-2-linear"></iconify-icon>
                </div>
                <h3>{{ $organization['name'] }}</h3>
                <p>School workspace on Enrolliq</p>
                <div class="dash-account-card__meta">
                    <span class="{{ $organization['status_class'] }} radius-8 px-12 py-6 text-sm fw-semibold d-inline-block">
                        {{ $organization['status_label'] }}
                    </span>
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
            </div>
        </div>
    </div>
</div>
@endsection
