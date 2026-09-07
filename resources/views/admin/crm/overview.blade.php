@extends('admin.layouts.app')
@section('title', 'CRM Overview')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.crm.partials.overview-styles')
@php
    $showSales = auth('admin')->user()?->can('view quotations') || auth('admin')->user()?->can('view invoices');
    $showForms = auth('admin')->user()?->can('view form submissions')
        && (($stats['submissions_pending'] ?? 0) > 0 || ($stats['submissions_unconverted'] ?? 0) > 0 || ($formBreakdown ?? collect())->isNotEmpty());
    $showProjects = auth('admin')->user()?->can('view projects')
        && (($stats['projects_active'] ?? 0) > 0 || ($stats['projects_due_soon'] ?? 0) > 0 || ($stats['projects_overdue'] ?? 0) > 0);
@endphp
<div class="dashboard-main-body" id="crm-overview-page"
     @can('view leads') data-smart-search-url="{{ route('admin.crm.leads.smart-search') }}" @endcan>
    @include('admin.partials.page-header', [
        'title' => 'CRM Overview',
        'subtitle' => 'What matters today — pipeline, billing, and follow-ups',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'CRM'],['label'=>'Overview']],
    ])

    @include('admin.crm.partials.module-nav', ['active' => 'overview'])

    <div class="crm-overview-workspace crm-workspace-shell">
        @include('admin.crm.partials.overview-ai-assistant', [
            'insights' => $insights,
            'smartSuggestions' => $smartSuggestions ?? [],
        ])

        <div class="crm-overview-layout">
            <div class="crm-overview-main">
                @can('view leads')
                    <section class="crm-overview-panel" aria-labelledby="crm-overview-leads">
                        <div class="crm-overview-panel__head">
                            <h2 class="crm-overview-panel__title" id="crm-overview-leads">
                                <iconify-icon icon="solar:user-hand-up-linear" aria-hidden="true"></iconify-icon>
                                Leads
                            </h2>
                            <a href="{{ route('admin.crm.leads.index') }}" class="crm-overview-panel__link">Open board</a>
                        </div>
                        <div class="crm-overview-metrics crm-overview-metrics--3">
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Total',
                                'value' => number_format($stats['leads_total']),
                                'href' => route('admin.crm.leads.index'),
                                'accent' => 'brand',
                                'icon' => 'solar:users-group-rounded-linear',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'New',
                                'value' => number_format($stats['leads_new']),
                                'href' => route('admin.crm.leads.index', ['lead_status' => 'new']),
                                'accent' => 'purple',
                                'icon' => 'solar:star-linear',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Overdue',
                                'value' => number_format($stats['leads_follow_up_overdue']),
                                'href' => route('admin.crm.leads.index', ['follow_up' => 'overdue']),
                                'accent' => $stats['leads_follow_up_overdue'] > 0 ? 'red' : 'green',
                                'icon' => 'solar:alarm-linear',
                                'hint' => $stats['leads_follow_up_overdue'] > 0 ? 'Needs action' : 'On track',
                            ])
                        </div>
                    </section>
                @endcan

                @if($showSales)
                    <section class="crm-overview-panel" aria-labelledby="crm-overview-sales">
                        <div class="crm-overview-panel__head">
                            <h2 class="crm-overview-panel__title" id="crm-overview-sales">
                                <iconify-icon icon="solar:wallet-money-linear" aria-hidden="true"></iconify-icon>
                                Sales &amp; billing
                            </h2>
                            @can('view invoices')
                                <a href="{{ route('admin.crm.invoices.index') }}" class="crm-overview-panel__link">Invoices</a>
                            @endcan
                        </div>
                        <div class="crm-overview-metrics crm-overview-metrics--3">
                            @can('view invoices')
                                @include('admin.crm.partials.overview-metric', [
                                    'label' => 'Outstanding',
                                    'value' => number_format($stats['outstanding'], 2),
                                    'href' => route('admin.crm.invoices.index'),
                                    'accent' => 'amber',
                                    'icon' => 'solar:clock-circle-linear',
                                ])
                                @include('admin.crm.partials.overview-metric', [
                                    'label' => 'Overdue',
                                    'value' => number_format($stats['overdue_invoices'], 2),
                                    'href' => route('admin.crm.invoices.index'),
                                    'accent' => $stats['overdue_invoices'] > 0 ? 'red' : 'green',
                                    'icon' => 'solar:danger-triangle-linear',
                                ])
                            @endcan
                            @can('view quotations')
                                @include('admin.crm.partials.overview-metric', [
                                    'label' => 'Quotes open',
                                    'value' => number_format($stats['quotations_open']),
                                    'href' => route('admin.crm.quotations.index'),
                                    'accent' => 'blue',
                                    'icon' => 'solar:document-text-linear',
                                ])
                            @endcan
                        </div>
                    </section>
                @endif

                @if($showProjects)
                    <section class="crm-overview-panel" aria-labelledby="crm-overview-projects">
                        <div class="crm-overview-panel__head">
                            <h2 class="crm-overview-panel__title" id="crm-overview-projects">
                                <iconify-icon icon="solar:folder-linear" aria-hidden="true"></iconify-icon>
                                Projects
                            </h2>
                            <a href="{{ route('admin.crm.projects.index') }}" class="crm-overview-panel__link">View all</a>
                        </div>
                        <div class="crm-overview-metrics crm-overview-metrics--3">
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Active',
                                'value' => number_format($stats['projects_active']),
                                'href' => route('admin.crm.projects.index'),
                                'accent' => 'brand',
                                'icon' => 'solar:play-circle-linear',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Due soon',
                                'value' => number_format($stats['projects_due_soon']),
                                'href' => route('admin.crm.projects.index'),
                                'accent' => 'amber',
                                'icon' => 'solar:calendar-mark-linear',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Overdue',
                                'value' => number_format($stats['projects_overdue']),
                                'href' => route('admin.crm.projects.index'),
                                'accent' => $stats['projects_overdue'] > 0 ? 'red' : 'green',
                                'icon' => 'solar:alarm-linear',
                            ])
                        </div>
                    </section>
                @endif

                @if($showForms)
                    <section class="crm-overview-panel" aria-labelledby="crm-overview-forms">
                        <div class="crm-overview-panel__head">
                            <h2 class="crm-overview-panel__title" id="crm-overview-forms">
                                <iconify-icon icon="solar:inbox-in-linear" aria-hidden="true"></iconify-icon>
                                Form intake
                            </h2>
                            <a href="{{ route('admin.crm.form-entries.index') }}" class="crm-overview-panel__link">Submissions</a>
                        </div>
                        <div class="crm-overview-metrics crm-overview-metrics--3">
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Pending',
                                'value' => number_format($stats['submissions_pending'] ?? 0),
                                'href' => route('admin.crm.form-entries.index', ['status' => 'pending']),
                                'accent' => ($stats['submissions_pending'] ?? 0) > 0 ? 'amber' : 'green',
                                'icon' => 'solar:hourglass-linear',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'Awaiting lead',
                                'value' => number_format($stats['submissions_unconverted'] ?? 0),
                                'href' => route('admin.crm.form-entries.index', ['status' => 'pending']),
                                'accent' => 'purple',
                                'icon' => 'solar:user-plus-linear',
                            ])
                            @include('admin.crm.partials.overview-metric', [
                                'label' => 'This week',
                                'value' => number_format($stats['submissions_this_week'] ?? 0),
                                'href' => route('admin.crm.form-entries.index'),
                                'accent' => 'blue',
                                'icon' => 'solar:calendar-linear',
                            ])
                        </div>

                        @if(($formBreakdown ?? collect())->where('pending', '>', 0)->isNotEmpty())
                            <div class="crm-overview-form-breakdown" aria-label="Forms with pending submissions">
                                @foreach($formBreakdown->where('pending', '>', 0)->take(4) as $row)
                                    <a href="{{ route('admin.crm.leads.index', ['form_id' => $row['id']]) }}"
                                       class="crm-overview-form-breakdown__row crm-overview-form-breakdown__row--compact">
                                        <span class="crm-overview-form-breakdown__name" title="{{ $row['name'] }}">{{ $row['name'] }}</span>
                                        <span class="crm-overview-form-breakdown__stat is-attention">{{ number_format($row['pending']) }} pending</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif
            </div>

            <aside class="crm-overview-aside">
                <section class="crm-overview-attention" aria-labelledby="crm-overview-attention" id="crm-overview-attention">
                    <div class="crm-overview-attention__head">
                        <h2 id="crm-overview-attention-title">
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
                                    <span class="crm-overview-attention__label">{{ $item['label'] }}</span>
                                    <span class="crm-overview-attention__meta">{{ $item['type'] }} · {{ $item['meta'] }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="crm-overview-attention__empty">
                                <iconify-icon icon="solar:check-circle-linear" aria-hidden="true"></iconify-icon>
                                <strong>All clear</strong>
                                <span>No urgent items right now.</span>
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection

@section('script')
@can('view leads')
<script src="{{ asset('admin/assets/js/crm-overview.js') }}?v={{ filemtime(public_path('admin/assets/js/crm-overview.js')) }}"></script>
@endcan
@endsection
