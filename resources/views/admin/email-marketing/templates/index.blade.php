@extends('admin.layouts.app')
@section('title', 'Templates')
@section('content')
@php
    $activeFilters = request()->filled('search') ? 1 : 0;
@endphp
<div class="dashboard-main-body" id="em-workspace-page">
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'templates',
    'shellTitle' => 'Email templates',
    'shellSubtitle' => 'Reusable designs for campaigns — save time and keep your brand consistent.',
    'shellActions' => array_values(array_filter([
        auth('admin')->user()?->can('create templates') ? [
            'label' => 'New template',
            'url' => route('admin.email.templates.create'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:add-circle-linear',
        ] : null,
    ])),
])

@include('admin.email-marketing.partials.templates.filter-workspace')

<div class="crm-leads-toolbar">
    <div class="crm-leads-toolbar__left">
        <div class="crm-leads-toolbar__meta">
            <strong>{{ number_format($templates->total()) }} template{{ $templates->total() === 1 ? '' : 's' }}</strong>
            @if($activeFilters > 0)
                <span class="crm-leads-toolbar__filters">{{ $activeFilters }} filter</span>
            @endif
            @if($templates->total() > 0)
                <span>{{ $templates->firstItem() }}–{{ $templates->lastItem() }} shown</span>
            @endif
        </div>
    </div>
</div>

<div class="em-list-shell">
    <div class="crm-leads-table">
        <div class="crm-leads-table__head crm-leads-table__head--templates" aria-hidden="true">
            <span>Template</span><span>Subject</span><span>Category</span><span>Status</span><span></span>
        </div>
        <div class="crm-leads-list">
            @forelse($templates as $template)
                @include('admin.email-marketing.partials.template-row', ['template' => $template])
            @empty
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon>
                    <strong>No templates yet</strong>
                    <span>Create a reusable email design for open days, reminders, and newsletters.</span>
                    @can('create templates')
                    <a href="{{ route('admin.email.templates.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-8">
                        <iconify-icon icon="solar:add-circle-linear"></iconify-icon> Create template
                    </a>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>
</div>

@include('admin.crm.partials.list-workspace-pagination', [
    'paginator' => $templates,
    'routeName' => 'admin.email.templates.index',
    'entityLabel' => 'templates',
    'paginationId' => 'em-templates-per-page',
])

@include('admin.email-marketing.partials.shell-close')
</div>
@endsection
