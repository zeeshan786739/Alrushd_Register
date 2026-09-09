@extends('admin.layouts.app')

@section('title') Event Items @endsection

@section('content')
@php
    $activeCount = $data->where('status', 1)->count();
    $inactiveCount = $data->where('status', '!=', 1)->count();
@endphp

<div class="dashboard-main-body" id="oe-workspace-page">
@include('admin.open-event.partials.shell', [
    'activeTab' => 'items',
    'shellTitle' => 'Event Items',
    'shellSubtitle' => 'Manage sessions, time slots, and registration options linked to each event.',
    'shellActions' => auth()->user()->can('create event_item') ? [[
        'label' => 'Add item',
        'url' => route('admin.open-event-items.create'),
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
                <input type="search" class="oe-ai-search__input um-table-search" placeholder="Search by title or event…" aria-label="Search event items">
            </div>
        </div>
    </div>
</div>

<div class="crm-leads-toolbar">
    <div class="crm-leads-toolbar__meta">
        <strong>{{ number_format($data->count()) }} item{{ $data->count() === 1 ? '' : 's' }}</strong>
        <span>{{ number_format($activeCount) }} active · {{ number_format($inactiveCount) }} inactive</span>
    </div>
</div>

<div class="crm-list-shell um-search-scope">
    <div class="crm-leads-table">
        <div class="crm-leads-table__head crm-leads-table__head--items" aria-hidden="true">
            <span>Item</span><span>Open event</span><span>Years</span><span>Minutes</span><span>Status</span><span></span>
        </div>
        <div class="crm-leads-list">
            @forelse($data as $item)
                @include('admin.open-event.partials.item-row', ['item' => $item])
            @empty
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:checklist-linear"></iconify-icon>
                    <strong>No event items yet</strong>
                    <span>Add sessions or registration slots linked to your open events.</span>
                    @can('create event_item')
                        <a href="{{ route('admin.open-event-items.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-12">
                            <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                            Add item
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
