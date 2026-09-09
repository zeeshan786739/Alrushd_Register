@extends('admin.layouts.app')
@section('title', 'Campaigns')
@section('content')
@php
    $activeFilters = collect(request()->only(['search', 'status']))->filter(fn ($v) => $v !== null && $v !== '')->count();
@endphp
<div class="dashboard-main-body" id="em-workspace-page">
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'campaigns',
    'shellTitle' => 'Campaigns',
    'shellSubtitle' => 'Create broadcasts, track opens and clicks, and reach your CRM audience.',
    'shellActions' => array_values(array_filter([
        auth('admin')->user()?->can('create campaigns') ? [
            'label' => 'New campaign',
            'url' => route('admin.email.campaigns.create'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:add-circle-linear',
        ] : null,
    ])),
])

@include('admin.email-marketing.partials.campaigns.filter-workspace')

<div class="crm-leads-toolbar">
    <div class="crm-leads-toolbar__left">
        <div class="crm-leads-toolbar__meta">
            <strong>{{ number_format($campaigns->total()) }} campaign{{ $campaigns->total() === 1 ? '' : 's' }}</strong>
            @if($activeFilters > 0)
                <span class="crm-leads-toolbar__filters">{{ $activeFilters }} filter{{ $activeFilters === 1 ? '' : 's' }}</span>
            @endif
            @if($campaigns->total() > 0)
                <span>{{ $campaigns->firstItem() }}–{{ $campaigns->lastItem() }} shown</span>
            @endif
        </div>
    </div>
</div>

<div class="em-list-shell">
    <div class="crm-leads-table">
        <div class="crm-leads-table__head crm-leads-table__head--campaigns" aria-hidden="true">
            <span>Campaign</span><span>Status</span><span>Recipients</span><span>Sent</span><span>Opens</span><span>Clicks</span><span></span>
        </div>
        <div class="crm-leads-list">
            @forelse($campaigns as $campaign)
                @include('admin.email-marketing.partials.campaign-list-row', ['campaign' => $campaign])
            @empty
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:letter-linear"></iconify-icon>
                    <strong>No campaigns yet</strong>
                    <span>Send your first email broadcast to leads, customers, or form submissions.</span>
                    @can('create campaigns')
                    <a href="{{ route('admin.email.campaigns.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-8">
                        <iconify-icon icon="solar:add-circle-linear"></iconify-icon> Create campaign
                    </a>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>
</div>

@include('admin.crm.partials.list-workspace-pagination', [
    'paginator' => $campaigns,
    'routeName' => 'admin.email.campaigns.index',
    'entityLabel' => 'campaigns',
    'paginationId' => 'em-campaigns-per-page',
])

@include('admin.email-marketing.partials.shell-close')
</div>
@endsection
