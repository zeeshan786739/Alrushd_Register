@extends('admin.layouts.app')
@section('title', 'CRM Overview')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body" id="crm-overview-page">
    @include('admin.partials.page-header', [
        'title' => 'CRM Overview',
        'subtitle' => 'Current-organization pipeline, collections, and items needing attention',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'CRM'],['label'=>'Overview']],
    ])

    <div class="d-flex flex-wrap gap-8 mb-20">
        @foreach($quickLinks as $link)
            <a href="{{ $link['url'] }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10">
                <iconify-icon icon="{{ $link['icon'] }}"></iconify-icon> {{ $link['label'] }}
            </a>
        @endforeach
    </div>

    @can('view leads')
    <h6 class="crm-section-title mb-12">Leads</h6>
    <div class="row g-3 mb-24">
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Total Leads','value'=>$stats['leads_total'],'icon'=>'solar:user-hand-up-linear','tone'=>'navy'])</div>
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'New Leads','value'=>$stats['leads_new'],'icon'=>'solar:add-circle-linear','tone'=>'amber'])</div>
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Follow-ups Today','value'=>$stats['leads_follow_up_today'],'icon'=>'solar:calendar-linear','tone'=>'gold'])</div>
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Overdue Follow-ups','value'=>$stats['leads_follow_up_overdue'],'icon'=>'solar:danger-triangle-linear','tone'=>'amber'])</div>
    </div>
    @endcan

    @canany(['view quotations','view invoices'])
    <h6 class="crm-section-title mb-12">Sales</h6>
    <div class="row g-3 mb-24">
        @can('view quotations')
            <div class="col-6 col-md-4 col-xl-4 col-xxl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Quotes Open','value'=>$stats['quotations_open'],'icon'=>'solar:document-text-linear','tone'=>'navy'])</div>
            <div class="col-6 col-md-4 col-xl-4 col-xxl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Accepted Value','value'=>number_format($stats['quotations_accepted_value'],2),'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
        @endcan
        @can('view invoices')
            <div class="col-6 col-md-4 col-xl-4 col-xxl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Total Invoiced','value'=>number_format($stats['invoiced'],2),'icon'=>'solar:bill-list-linear','tone'=>'navy'])</div>
            <div class="col-6 col-md-4 col-xl-4 col-xxl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Paid','value'=>number_format($stats['paid'],2),'icon'=>'solar:wallet-money-linear','tone'=>'green'])</div>
            <div class="col-6 col-md-4 col-xl-4 col-xxl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Outstanding','value'=>number_format($stats['outstanding'],2),'icon'=>'solar:wallet-linear','tone'=>'amber'])</div>
            <div class="col-6 col-md-4 col-xl-4 col-xxl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Overdue','value'=>number_format($stats['overdue_invoices'],2),'icon'=>'solar:danger-triangle-linear','tone'=>'amber'])</div>
        @endcan
    </div>
    @endcanany

    @can('view projects')
    <h6 class="crm-section-title mb-12">Projects</h6>
    <div class="row g-3 mb-24">
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Active','value'=>$stats['projects_active'],'icon'=>'solar:play-linear','tone'=>'navy'])</div>
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Due Soon','value'=>$stats['projects_due_soon'],'icon'=>'solar:clock-circle-linear','tone'=>'amber'])</div>
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Overdue','value'=>$stats['projects_overdue'],'icon'=>'solar:danger-triangle-linear','tone'=>'amber'])</div>
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Completed','value'=>$stats['projects_completed'],'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
    </div>
    @endcan

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <h6 class="crm-section-title"><iconify-icon icon="solar:bell-bing-linear"></iconify-icon> Needs Attention</h6>
            @forelse($attention as $item)
                <a href="{{ $item['url'] }}" class="crm-attention-row crm-attention-row--{{ $item['severity'] }}">
                    <div>
                        <div class="fw-semibold">{{ $item['label'] }}</div>
                        <div class="crm-lead-meta mt-4">{{ $item['type'] }} · {{ $item['meta'] }}</div>
                    </div>
                    <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                </a>
            @empty
                <div class="crm-empty-state mb-0 py-24">
                    <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                    Nothing needs attention right now.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
