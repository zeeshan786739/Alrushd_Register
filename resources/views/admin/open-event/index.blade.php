@extends('admin.layouts.app')

@section('title') Open Events @endsection

@section('content')
@php
    $activeCount = $data->where('status', 1)->count();
    $inactiveCount = $data->where('status', '!=', 1)->count();
@endphp

<div class="dashboard-main-body" id="oe-workspace-page">
@include('admin.open-event.partials.shell', [
    'activeTab' => 'events',
    'shellTitle' => 'Events',
    'shellSubtitle' => 'Create and manage open events shown on your public registration page.',
    'shellActions' => auth()->user()->can('create open_event') ? [[
        'label' => 'Add event',
        'url' => route('admin.open-events.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]] : [],
])

@include('admin.open-event.partials.status-filters', ['items' => $data])

<div class="oe-filter-workspace um-search-scope">
    <div class="oe-filter-grid">
        <div class="oe-ai-search">
            <div class="oe-ai-search__badge"><iconify-icon icon="solar:magnifer-linear"></iconify-icon> Find</div>
            <div class="oe-ai-search__shell">
                <span class="oe-ai-search__icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                <input type="search" class="oe-ai-search__input um-table-search" placeholder="Search by name or title…" aria-label="Search events">
            </div>
        </div>
    </div>
</div>

<div class="crm-leads-toolbar">
    <div class="crm-leads-toolbar__meta">
        <strong>{{ number_format($data->count()) }} event{{ $data->count() === 1 ? '' : 's' }}</strong>
        <span>{{ number_format($activeCount) }} active · {{ number_format($inactiveCount) }} inactive</span>
    </div>
</div>

<div class="crm-list-shell um-search-scope">
    <div class="crm-leads-table">
        <div class="crm-leads-table__head crm-leads-table__head--events" aria-hidden="true">
            <span>Event</span><span>Description</span><span>Status</span><span></span>
        </div>
        <div class="crm-leads-list" id="oeEventsList">
            @forelse($data as $item)
                @include('admin.open-event.partials.event-row', ['item' => $item])
            @empty
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:calendar-mark-linear"></iconify-icon>
                    <strong>No events yet</strong>
                    <span>Create your first open event to start collecting registrations.</span>
                    @can('create open_event')
                        <a href="{{ route('admin.open-events.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-12">
                            <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                            Add event
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>
</div>
</div>
@endsection

@section('script')
<script src="{{ asset('admin/assets/js/open-events.js') }}?v={{ @filemtime(public_path('admin/assets/js/open-events.js')) ?: time() }}"></script>
@endsection
