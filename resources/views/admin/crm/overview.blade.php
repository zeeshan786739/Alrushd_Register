@extends('admin.layouts.app')
@section('title', 'CRM Overview')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.crm.partials.overview-styles')
<div class="dashboard-main-body" id="crm-overview-page" data-smart-search-url="{{ route('admin.crm.leads.smart-search') }}">
    @include('admin.partials.page-header', [
        'title' => 'CRM Overview',
        'subtitle' => 'Pipeline, collections, and items needing attention in one workspace',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Overview']],
    ])

    <div class="crm-overview-workspace crm-workspace-shell">
        @include('admin.crm.partials.overview-ai-assistant', compact('insights', 'smartSuggestions'))

        @include('admin.crm.overview.partials.module-cards', compact('quickLinks', 'formStats'))

        <div class="crm-overview-layout crm-overview-layout--single">
            <div class="crm-overview-main">
                @can('view leads')
                    <section class="crm-overview-panel">
                        <div class="crm-overview-panel__head">
                            <h2 class="crm-overview-panel__title">
                                <iconify-icon icon="solar:user-hand-up-linear"></iconify-icon>
                                Leads
                            </h2>
                            <a href="{{ route('admin.crm.leads.index') }}" class="crm-overview-panel__link">Open workspace</a>
                        </div>
                        <div class="crm-overview-metrics crm-overview-metrics--4">
                            @include('admin.crm.partials.overview-metric', ['label' => 'Total', 'value' => number_format($stats['leads_total']), 'href' => route('admin.crm.leads.index'), 'accent' => 'brand', 'icon' => 'solar:users-group-rounded-linear'])
                            @include('admin.crm.partials.overview-metric', ['label' => 'New', 'value' => number_format($stats['leads_new']), 'href' => route('admin.crm.leads.index', ['lead_status' => 'new']), 'accent' => 'purple', 'icon' => 'solar:star-linear'])
                            @include('admin.crm.partials.overview-metric', ['label' => 'Due today', 'value' => number_format($stats['leads_follow_up_today']), 'href' => route('admin.crm.leads.index', ['follow_up' => 'today', 'view' => 'list']), 'accent' => 'blue', 'icon' => 'solar:calendar-linear'])
                            @include('admin.crm.partials.overview-metric', ['label' => 'Overdue', 'value' => number_format($stats['leads_follow_up_overdue']), 'href' => route('admin.crm.leads.index', ['follow_up' => 'overdue', 'view' => 'list']), 'accent' => 'red', 'icon' => 'solar:alarm-linear'])
                        </div>
                    </section>
                @endcan

                @canany(['view quotations', 'view invoices'])
                    <section class="crm-overview-panel">
                        <div class="crm-overview-panel__head">
                            <h2 class="crm-overview-panel__title">
                                <iconify-icon icon="solar:wallet-money-linear"></iconify-icon>
                                Sales
                            </h2>
                            @can('view invoices')
                                <a href="{{ route('admin.crm.invoices.index') }}" class="crm-overview-panel__link">Open invoices</a>
                            @endcan
                        </div>
                        <div class="crm-overview-metrics crm-overview-metrics--sales">
                            @can('view quotations')
                                @include('admin.crm.partials.overview-metric', ['label' => 'Quotes open', 'value' => number_format($stats['quotations_open']), 'href' => route('admin.crm.quotations.index'), 'accent' => 'brand', 'icon' => 'solar:document-text-linear'])
                                @include('admin.crm.partials.overview-metric', ['label' => 'Accepted', 'value' => number_format($stats['quotations_accepted_value'], 2), 'href' => route('admin.crm.quotations.index', ['status' => 'accepted']), 'accent' => 'green', 'icon' => 'solar:check-circle-linear'])
                            @endcan
                            @can('view invoices')
                                @include('admin.crm.partials.overview-metric', ['label' => 'Invoiced', 'value' => number_format($stats['invoiced'], 2), 'href' => route('admin.crm.invoices.index'), 'accent' => 'brand', 'icon' => 'solar:bill-list-linear'])
                                @include('admin.crm.partials.overview-metric', ['label' => 'Paid', 'value' => number_format($stats['paid'], 2), 'href' => route('admin.crm.invoices.index', ['status' => 'paid']), 'accent' => 'green', 'icon' => 'solar:wallet-money-linear'])
                                @include('admin.crm.partials.overview-metric', ['label' => 'Outstanding', 'value' => number_format($stats['outstanding'], 2), 'href' => route('admin.crm.invoices.index'), 'accent' => 'amber', 'icon' => 'solar:wallet-linear'])
                                @include('admin.crm.partials.overview-metric', ['label' => 'Overdue', 'value' => number_format($stats['overdue_invoices'], 2), 'href' => route('admin.crm.invoices.index', ['status' => 'overdue']), 'accent' => 'red', 'icon' => 'solar:danger-triangle-linear'])
                            @endcan
                        </div>
                    </section>
                @endcanany

                @can('view projects')
                    <section class="crm-overview-panel">
                        <div class="crm-overview-panel__head">
                            <h2 class="crm-overview-panel__title">
                                <iconify-icon icon="solar:folder-linear"></iconify-icon>
                                Projects
                            </h2>
                            <a href="{{ route('admin.crm.projects.index') }}" class="crm-overview-panel__link">Open workspace</a>
                        </div>
                        <div class="crm-overview-metrics crm-overview-metrics--4">
                            @include('admin.crm.partials.overview-metric', ['label' => 'Active', 'value' => number_format($stats['projects_active']), 'href' => route('admin.crm.projects.index'), 'accent' => 'brand', 'icon' => 'solar:play-linear'])
                            @include('admin.crm.partials.overview-metric', ['label' => 'Due soon', 'value' => number_format($stats['projects_due_soon']), 'href' => route('admin.crm.projects.index'), 'accent' => 'amber', 'icon' => 'solar:clock-circle-linear'])
                            @include('admin.crm.partials.overview-metric', ['label' => 'Overdue', 'value' => number_format($stats['projects_overdue']), 'href' => route('admin.crm.projects.index'), 'accent' => 'red', 'icon' => 'solar:danger-triangle-linear'])
                            @include('admin.crm.partials.overview-metric', ['label' => 'Completed', 'value' => number_format($stats['projects_completed']), 'href' => route('admin.crm.projects.index', ['status' => 'completed']), 'accent' => 'green', 'icon' => 'solar:check-circle-linear'])
                        </div>
                    </section>
                @endcan

                @if(($formBreakdown ?? collect())->isNotEmpty())
                    <section class="crm-overview-panel">
                        <div class="crm-overview-panel__head">
                            <h2 class="crm-overview-panel__title">
                                <iconify-icon icon="solar:inbox-in-linear"></iconify-icon>
                                Form intake
                            </h2>
                            <a href="{{ route('admin.crm.form-entries.index') }}" class="crm-overview-panel__link">All submissions</a>
                        </div>
                        <div class="crm-overview-form-breakdown">
                            @foreach($formBreakdown as $form)
                                <a href="{{ route('admin.crm.form-entries.index', ['form_id' => $form['id']]) }}" class="crm-overview-form-breakdown__row--compact">
                                    <span class="crm-overview-form-breakdown__name">{{ $form['name'] }}</span>
                                    <span @class(['crm-overview-form-breakdown__stat', 'is-attention' => $form['pending'] > 0])>
                                        {{ number_format($form['pending']) }} pending · {{ number_format($form['total']) }} total
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>

        <div class="crm-leads-toolbar" id="crm-overview-attention">
            <div class="crm-leads-toolbar__meta">
                <strong><iconify-icon icon="solar:bell-bing-linear"></iconify-icon> Needs Attention · {{ number_format($attention->count()) }}</strong>
                @if($attention->isNotEmpty())
                    <span>Priority queue for this organization</span>
                @endif
            </div>
        </div>

        <div class="crm-list-shell">
            <div class="crm-leads-table">
                <div class="crm-leads-table__head" aria-hidden="true">
                    <span>Item</span>
                    <span>Status</span>
                    <span>Details</span>
                    <span></span>
                </div>
                <div class="crm-leads-list">
                    @forelse($attention as $item)
                        @include('admin.crm.overview.partials.attention-row', compact('item'))
                    @empty
                        <div class="crm-leads-list-empty">
                            <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                            <strong>Nothing needs attention</strong>
                            <span>Your pipeline is clear for now.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-overview.js') }}"></script>
@endsection
