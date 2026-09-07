@extends('admin.layouts.app')
@section('title', 'CRM Overview')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.crm.partials.overview-styles')
<div class="dashboard-main-body" id="crm-overview-page">
    @include('admin.partials.page-header', [
        'title' => 'CRM Overview',
        'subtitle' => 'Pipeline health, collections, and items that need action today',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'CRM'],['label'=>'Overview']],
    ])

    @include('admin.crm.partials.module-nav', ['active' => 'overview'])

    <div class="crm-overview-workspace crm-workspace-shell">
        <div class="crm-overview-summary" aria-label="At a glance">
        @can('view leads')
            <span class="crm-overview-summary__item">
                <span class="crm-overview-summary__label">Leads</span>
                <strong>{{ number_format($stats['leads_total']) }}</strong>
            </span>
            <span class="crm-overview-summary__sep" aria-hidden="true"></span>
            <span class="crm-overview-summary__item">
                <span class="crm-overview-summary__label">New</span>
                <strong>{{ number_format($stats['leads_new']) }}</strong>
            </span>
            <span class="crm-overview-summary__sep" aria-hidden="true"></span>
        @endcan
        @canany(['view quotations','view invoices'])
            <span class="crm-overview-summary__item">
                <span class="crm-overview-summary__label">Outstanding</span>
                <strong>{{ number_format($stats['outstanding'], 2) }}</strong>
            </span>
            <span class="crm-overview-summary__sep" aria-hidden="true"></span>
        @endcanany
        @can('view projects')
            <span class="crm-overview-summary__item">
                <span class="crm-overview-summary__label">Active projects</span>
                <strong>{{ number_format($stats['projects_active']) }}</strong>
            </span>
            <span class="crm-overview-summary__sep" aria-hidden="true"></span>
        @endcan
        @can('view form submissions')
            @if(($stats['submissions_pending'] ?? 0) > 0)
                <span class="crm-overview-summary__item">
                    <span class="crm-overview-summary__label">Pending forms</span>
                    <strong>{{ number_format($stats['submissions_pending']) }}</strong>
                </span>
                <span class="crm-overview-summary__sep" aria-hidden="true"></span>
            @endif
            @if(($stats['leads_from_forms'] ?? 0) > 0)
                <span class="crm-overview-summary__item">
                    <span class="crm-overview-summary__label">Form leads</span>
                    <strong>{{ number_format($stats['leads_from_forms']) }}</strong>
                </span>
                <span class="crm-overview-summary__sep" aria-hidden="true"></span>
            @endif
        @endcan
        <span class="crm-overview-summary__item">
            <span class="crm-overview-summary__label">Needs attention</span>
            <strong>{{ number_format($attention->count()) }}</strong>
        </span>
        </div>

        <div class="crm-overview-layout">
        <div class="crm-overview-main">
            @can('view leads')
                <section class="crm-overview-panel" aria-labelledby="crm-overview-leads">
                    <div class="crm-overview-panel__head">
                        <h2 class="crm-overview-panel__title" id="crm-overview-leads">
                            <iconify-icon icon="solar:user-hand-up-linear" aria-hidden="true"></iconify-icon>
                            Leads
                        </h2>
                        <a href="{{ route('admin.crm.leads.index') }}" class="crm-overview-panel__link">View pipeline</a>
                    </div>
                    <div class="crm-overview-metrics">
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Total leads',
                            'value' => number_format($stats['leads_total']),
                            'href' => route('admin.crm.leads.index'),
                            'accent' => 'brand',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'New',
                            'value' => number_format($stats['leads_new']),
                            'href' => route('admin.crm.leads.index', ['lead_status' => 'new']),
                            'accent' => 'purple',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Follow-up today',
                            'value' => number_format($stats['leads_follow_up_today']),
                            'href' => route('admin.crm.leads.index', ['follow_up' => 'today']),
                            'accent' => 'blue',
                            'hint' => $stats['leads_follow_up_today'] > 0 ? 'Due today' : 'All clear',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Overdue follow-ups',
                            'value' => number_format($stats['leads_follow_up_overdue']),
                            'href' => route('admin.crm.leads.index', ['follow_up' => 'overdue']),
                            'accent' => 'red',
                            'hint' => $stats['leads_follow_up_overdue'] > 0 ? 'Needs action' : 'None overdue',
                        ])
                    </div>
                </section>
            @endcan

            @canany(['view quotations','view invoices'])
                <section class="crm-overview-panel" aria-labelledby="crm-overview-sales">
                    <div class="crm-overview-panel__head">
                        <h2 class="crm-overview-panel__title" id="crm-overview-sales">
                            <iconify-icon icon="solar:wallet-money-linear" aria-hidden="true"></iconify-icon>
                            Sales &amp; billing
                        </h2>
                        @can('view invoices')
                            <a href="{{ route('admin.crm.invoices.index') }}" class="crm-overview-panel__link">View invoices</a>
                        @endcan
                    </div>
                    <div class="crm-overview-metrics crm-overview-metrics--cols-6">
                        @can('view quotations')
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Quotes open',
                                'value' => number_format($stats['quotations_open']),
                                'href' => route('admin.crm.quotations.index'),
                                'accent' => 'blue',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Accepted value',
                                'value' => number_format($stats['quotations_accepted_value'], 2),
                                'href' => route('admin.crm.quotations.index'),
                                'accent' => 'green',
                            ])
                        @endcan
                        @can('view invoices')
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Total invoiced',
                                'value' => number_format($stats['invoiced'], 2),
                                'href' => route('admin.crm.invoices.index'),
                                'accent' => 'brand',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Paid',
                                'value' => number_format($stats['paid'], 2),
                                'href' => route('admin.crm.invoices.index'),
                                'accent' => 'green',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Outstanding',
                                'value' => number_format($stats['outstanding'], 2),
                                'href' => route('admin.crm.invoices.index'),
                                'accent' => 'amber',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Overdue',
                                'value' => number_format($stats['overdue_invoices'], 2),
                                'href' => route('admin.crm.invoices.index'),
                                'accent' => 'red',
                            ])
                        @endcan
                    </div>
                </section>
            @endcanany

            @can('view projects')
                <section class="crm-overview-panel" aria-labelledby="crm-overview-projects">
                    <div class="crm-overview-panel__head">
                        <h2 class="crm-overview-panel__title" id="crm-overview-projects">
                            <iconify-icon icon="solar:folder-linear" aria-hidden="true"></iconify-icon>
                            Projects
                        </h2>
                        <a href="{{ route('admin.crm.projects.index') }}" class="crm-overview-panel__link">View projects</a>
                    </div>
                    <div class="crm-overview-metrics">
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Active',
                            'value' => number_format($stats['projects_active']),
                            'href' => route('admin.crm.projects.index'),
                            'accent' => 'brand',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Due soon',
                            'value' => number_format($stats['projects_due_soon']),
                            'href' => route('admin.crm.projects.index'),
                            'accent' => 'amber',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Overdue',
                            'value' => number_format($stats['projects_overdue']),
                            'href' => route('admin.crm.projects.index'),
                            'accent' => 'red',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Completed',
                            'value' => number_format($stats['projects_completed']),
                            'href' => route('admin.crm.projects.index'),
                            'accent' => 'green',
                        ])
                    </div>
                </section>
            @endcan

            @can('view form submissions')
                <section class="crm-overview-panel" aria-labelledby="crm-overview-forms">
                    <div class="crm-overview-panel__head">
                        <h2 class="crm-overview-panel__title" id="crm-overview-forms">
                            <iconify-icon icon="solar:inbox-in-linear" aria-hidden="true"></iconify-icon>
                            Forms &amp; intake
                        </h2>
                        <a href="{{ route('admin.crm.form-entries.index') }}" class="crm-overview-panel__link">View submissions</a>
                    </div>
                    <div class="crm-overview-metrics crm-overview-metrics--cols-6">
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Total submissions',
                            'value' => number_format($stats['submissions_total'] ?? 0),
                            'href' => route('admin.crm.form-entries.index'),
                            'accent' => 'brand',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Pending review',
                            'value' => number_format($stats['submissions_pending'] ?? 0),
                            'href' => route('admin.crm.form-entries.index', ['status' => 'pending']),
                            'accent' => 'amber',
                            'hint' => ($stats['submissions_pending'] ?? 0) > 0 ? 'Needs triage' : 'All reviewed',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Awaiting lead',
                            'value' => number_format($stats['submissions_unconverted'] ?? 0),
                            'href' => route('admin.crm.form-entries.index', ['status' => 'pending']),
                            'accent' => 'purple',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Leads from forms',
                            'value' => number_format($stats['leads_from_forms'] ?? 0),
                            'href' => route('admin.crm.leads.index', ['source' => 'form_submission']),
                            'accent' => 'teal',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'This week',
                            'value' => number_format($stats['submissions_this_week'] ?? 0),
                            'href' => route('admin.crm.form-entries.index'),
                            'accent' => 'blue',
                        ])
                        @include('admin.crm.partials.overview-metric', [
                            'label' => 'Active forms',
                            'value' => number_format($stats['active_forms'] ?? 0),
                            'href' => route('admin.crm.form-entries.index'),
                            'accent' => 'green',
                        ])
                    </div>

                    @if(($formBreakdown ?? collect())->isNotEmpty())
                        <div class="crm-overview-form-breakdown" aria-label="Submissions by form">
                            <div class="crm-overview-form-breakdown__head">
                                <span>Form</span>
                                <span>Total</span>
                                <span>Pending</span>
                                <span>Leads</span>
                                <span>7d</span>
                            </div>
                            @foreach($formBreakdown as $row)
                                <a href="{{ route('admin.crm.leads.index', ['form_id' => $row['id']]) }}"
                                   class="crm-overview-form-breakdown__row">
                                    <span class="crm-overview-form-breakdown__name" title="{{ $row['name'] }}">
                                        <iconify-icon icon="solar:document-text-linear" aria-hidden="true"></iconify-icon>
                                        {{ $row['name'] }}
                                    </span>
                                    <span class="crm-overview-form-breakdown__stat">{{ number_format($row['total']) }}</span>
                                    <span @class(['crm-overview-form-breakdown__stat', 'is-attention' => $row['pending'] > 0])>{{ number_format($row['pending']) }}</span>
                                    <span class="crm-overview-form-breakdown__stat">{{ number_format($row['leads']) }}</span>
                                    <span class="crm-overview-form-breakdown__stat">{{ number_format($row['week']) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endcan
        </div>

        <aside class="crm-overview-aside">
            <section class="crm-overview-panel crm-overview-attention" aria-labelledby="crm-overview-attention">
                <div class="crm-overview-attention__head">
                    <h2 id="crm-overview-attention">
                        <iconify-icon icon="solar:bell-bing-linear" aria-hidden="true"></iconify-icon>
                        Needs attention
                    </h2>
                    @if($attention->isNotEmpty())
                        <span class="crm-overview-attention__count">{{ $attention->count() }}</span>
                    @endif
                </div>
                <div class="crm-overview-attention__list">
                    @forelse($attention as $item)
                        <a href="{{ $item['url'] }}"
                           class="crm-overview-attention__item crm-overview-attention__item--{{ $item['severity'] }}">
                            <span class="crm-overview-attention__icon" aria-hidden="true">
                                <iconify-icon icon="{{ $item['severity'] === 'danger' ? 'solar:danger-triangle-linear' : 'solar:clock-circle-linear' }}"></iconify-icon>
                            </span>
                            <span class="crm-overview-attention__body">
                                <span class="crm-overview-attention__type">{{ $item['type'] }}</span>
                                <span class="crm-overview-attention__label">{{ $item['label'] }}</span>
                                <span class="crm-overview-attention__meta">{{ $item['meta'] }}</span>
                            </span>
                            <iconify-icon class="crm-overview-attention__chevron" icon="solar:alt-arrow-right-linear" aria-hidden="true"></iconify-icon>
                        </a>
                    @empty
                        <div class="crm-overview-attention__empty">
                            <iconify-icon icon="solar:check-circle-linear" aria-hidden="true"></iconify-icon>
                            <strong>All caught up</strong>
                            <span>Nothing needs attention right now.</span>
                        </div>
                    @endforelse
                </div>
            </section>
        </aside>
        </div>
    </div>
</div>
@endsection
