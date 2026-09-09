@extends('admin.layouts.app')

@section('title', $form->name.' — Submissions')

@section('content')
@once
    @include('admin.crm.partials.styles')
    @include('admin.crm.partials.workspace-shell')
    @include('admin.form-manager.partials.premium-styles')
@endonce

@php
    $currentStatus = request('status', '');
    $previewKeys = ['full_name', 'name', 'first_name', 'email', 'phone', 'mobile', 'job_title', 'company'];
@endphp

<div class="dashboard-main-body" id="form-center-page">
    @include('admin.partials.page-header', [
        'title' => $form->name,
        'subtitle' => ($entryStats['total'] ?? $entries->total()).' total submissions stored in form_entries',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'Forms & Intake'],
            ['label' => 'Form Center', 'url' => route('admin.form-manager.index')],
            ['label' => $form->name],
            ['label' => 'Submissions'],
        ],
        'actions' => [
            ['label' => 'Customize Form', 'url' => route('admin.form-manager.edit', $form), 'class' => 'btn-outline-primary-600 radius-8 px-20 py-11', 'icon' => 'solar:pen-linear'],
            ['label' => 'Back', 'url' => route('admin.form-manager.index'), 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11', 'icon' => 'solar:alt-arrow-left-linear'],
        ],
    ])

    <div class="fc-workspace crm-workspace-shell">
        <div class="crm-metrics-strip" aria-label="Submission metrics">
            <div class="crm-metrics-strip__items">
                <span class="crm-metrics-strip__hint">{{ $form->name }} submissions</span>
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Total</span><strong>{{ number_format($entryStats['total']) }}</strong></span>
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Pending</span><strong>{{ number_format($entryStats['pending']) }}</strong></span>
            </div>
        </div>
    </div>

    <div class="fc-page-body">
        <div class="fc-stat-pill-grid">
            @php
                $pills = [
                    ['key' => '', 'label' => 'All', 'count' => $entryStats['total'], 'icon' => 'solar:documents-linear', 'bg' => 'bg-primary-50 text-primary-600'],
                    ['key' => 'pending', 'label' => 'Pending', 'count' => $entryStats['pending'], 'icon' => 'solar:clock-circle-linear', 'bg' => 'bg-warning-50 text-warning-600'],
                    ['key' => 'approved', 'label' => 'Approved', 'count' => $entryStats['approved'], 'icon' => 'solar:check-circle-linear', 'bg' => 'bg-success-50 text-success-600'],
                    ['key' => 'rejected', 'label' => 'Rejected', 'count' => $entryStats['rejected'], 'icon' => 'solar:close-circle-linear', 'bg' => 'bg-danger-50 text-danger-600'],
                ];
            @endphp
            @foreach($pills as $pill)
                @php
                    $pillQuery = array_filter([
                        'search' => request('search'),
                        'date_from' => request('date_from'),
                        'date_to' => request('date_to'),
                        'status' => $pill['key'] ?: null,
                    ]);
                    $isActive = $currentStatus === $pill['key'];
                @endphp
                <a href="{{ route('admin.form-manager.entries', $form) }}?{{ http_build_query($pillQuery) }}"
                   class="fc-stat-pill {{ $isActive ? 'active-filter' : '' }}">
                    <span class="fc-stat-pill-icon {{ $pill['bg'] }}">
                        <iconify-icon icon="{{ $pill['icon'] }}"></iconify-icon>
                    </span>
                    <span>
                        <span class="d-block fw-bold text-lg lh-1 mb-4">{{ number_format($pill['count']) }}</span>
                        <span class="text-secondary-light text-sm">{{ $pill['label'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>

        <div class="fc-filter-workspace mb-16">
            <div class="fc-filter-workspace__head">
                <h2 class="fc-filter-workspace__title">Filter submissions</h2>
                <p class="fc-filter-workspace__sub">Search and narrow by date or status</p>
            </div>
            <form method="GET" action="{{ route('admin.form-manager.entries', $form) }}" class="fc-filter-grid">
                <div class="fc-filter-field">
                    <label for="fc-entry-search">Search</label>
                    <div class="um-ai-search__shell">
                        <span class="um-ai-search__icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                        <input type="text" id="fc-entry-search" name="search" class="um-ai-search__input" value="{{ request('search') }}" placeholder="Name, email, phone…">
                    </div>
                </div>
                <div class="fc-filter-field">
                    <label for="fc-date-from">Start date</label>
                    <input type="date" id="fc-date-from" name="date_from" class="form-control radius-8" value="{{ request('date_from') }}">
                </div>
                <div class="fc-filter-field">
                    <label for="fc-date-to">End date</label>
                    <input type="date" id="fc-date-to" name="date_to" class="form-control radius-8" value="{{ request('date_to') }}">
                </div>
                <div class="fc-filter-field">
                    <label for="fc-entry-status">Status</label>
                    <select id="fc-entry-status" name="status" class="form-select radius-8">
                        <option value="">All</option>
                        @foreach(['pending','approved','rejected'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fc-filter-field">
                    <label>&nbsp;</label>
                    <div class="d-flex gap-8">
                        <button type="submit" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn">
                            <iconify-icon icon="solar:filter-linear"></iconify-icon>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('admin.form-manager.entries', $form) }}"
                           class="btn btn-outline-neutral-500 radius-8 fc-btn fc-btn-icon"
                           title="Reset filters"
                           aria-label="Reset filters">
                            <iconify-icon icon="solar:refresh-linear"></iconify-icon>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="crm-leads-toolbar">
            <div class="crm-leads-toolbar__meta">
                <strong>Submissions</strong>
                <span>{{ $entries->total() }} result{{ $entries->total() === 1 ? '' : 's' }}</span>
            </div>
        </div>

        <div class="crm-list-shell">
            <div class="crm-leads-table">
                @if($entries->isEmpty())
                    <div class="crm-leads-list-empty">
                        <iconify-icon icon="solar:inbox-linear"></iconify-icon>
                        <strong>No submissions found</strong>
                        <span>Try adjusting your filters or check back later.</span>
                    </div>
                @else
                    <div class="crm-leads-table__head crm-leads-table__head--entries" aria-hidden="true">
                        <span>#</span><span>Submitted</span><span>Status</span><span>Preview</span><span></span>
                    </div>
                    <div class="crm-leads-list">
                        @foreach($entries as $entry)
                            @include('admin.form-manager.partials.entry-row', compact('entry', 'form', 'previewKeys'))
                        @endforeach
                    </div>
                    @if($entries->hasPages())
                        <div class="fc-pagination">
                            <span class="fc-pagination-info">
                                Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ $entries->total() }} results
                            </span>
                            {{ $entries->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @elseif($entries->total() > 0)
                        <div class="fc-pagination">
                            <span class="fc-pagination-info">{{ $entries->total() }} submission{{ $entries->total() === 1 ? '' : 's' }}</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
