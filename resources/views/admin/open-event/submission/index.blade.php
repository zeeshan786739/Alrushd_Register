@extends('admin.layouts.app')

@section('title') Event Submissions @endsection

@section('content')
<div class="dashboard-main-body" id="oe-workspace-page">
@include('admin.open-event.partials.shell', [
    'activeTab' => 'submissions',
    'shellTitle' => 'Event Submissions',
    'shellSubtitle' => 'Review registrations submitted through your public open event forms.',
    'shellActions' => [],
])

<div class="oe-filter-workspace um-search-scope">
    <div class="oe-filter-grid">
        <form method="GET" action="{{ route('admin.open-event-form.index') }}" class="oe-ai-search" style="display:grid;gap:8px;">
            <div class="oe-ai-search__badge"><iconify-icon icon="solar:magnifer-linear"></iconify-icon> Find</div>
            <div class="oe-ai-search__shell">
                <span class="oe-ai-search__icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                <input type="search"
                       name="search"
                       class="oe-ai-search__input"
                       value="{{ $search ?? request('search') }}"
                       placeholder="Search by entry ID, name, email, phone…"
                       aria-label="Search submissions">
            </div>
        </form>
    </div>
</div>

<div class="crm-leads-toolbar">
    <div class="crm-leads-toolbar__meta">
        <strong>{{ number_format($paginatedData->total()) }} submission{{ $paginatedData->total() === 1 ? '' : 's' }}</strong>
        @if(!empty($search ?? request('search')))
            <span>Filtered results</span>
        @endif
        @if($paginatedData->total() > 0)
            <span>{{ $paginatedData->firstItem() }}–{{ $paginatedData->lastItem() }} shown</span>
        @endif
    </div>
</div>

<div class="crm-list-shell um-search-scope">
    <div class="crm-leads-table">
        <div class="crm-leads-table__head crm-leads-table__head--submissions" aria-hidden="true">
            <span>Entry</span><span>Submitted</span><span>First name</span><span>Last name</span><span></span>
        </div>
        <div class="crm-leads-list">
            @forelse($paginatedData as $entry)
                @include('admin.open-event.partials.submission-row', ['entry' => $entry])
            @empty
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon>
                    <strong>No submissions found</strong>
                    <span>@if(!empty($search ?? request('search'))) Try clearing your search or @endif registrations will appear here once families submit the open event form.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>

@if($paginatedData->total() > 0)
    <div class="crm-list-shell">
        @include('admin.crm.partials.list-workspace-pagination', [
            'paginator' => $paginatedData,
            'routeName' => 'admin.open-event-form.index',
            'entityLabel' => 'submissions',
            'paginationId' => 'oe-submissions-per-page',
            'perPageOptions' => [20],
        ])
    </div>
@endif
</div>
@endsection

@section('script')
<script src="{{ asset('admin/assets/js/open-events.js') }}?v={{ @filemtime(public_path('admin/assets/js/open-events.js')) ?: time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('#oe-workspace-page .oe-ai-search');
    var input = form ? form.querySelector('input[name="search"]') : null;
    if (!form || !input) return;

    var timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }, 450);
    });
});
</script>
@endsection
