@extends('admin.layouts.app')

@section('title', $form->name.' — Submissions')

@section('content')
@once
    @include('admin.crm.partials.styles')
    @include('admin.crm.partials.workspace-shell')
    @include('admin.form-manager.partials.premium-styles')
@endonce

@php
    use App\Support\FormEntryBrowse;

    $currentStatus = (string) request('status', '');
    $baseUrl = route('admin.form-manager.entries', $form);
    $urlWith = fn (array $query) => $baseUrl.(($clean = array_filter($query, fn ($value) => filled($value))) ? '?'.http_build_query($clean) : '');

    // The results column is named after what this form actually collects.
    $detailsFacet = collect(['role', 'subject', 'year_group'])->first(fn ($name) => isset($facetOptions[$name]));
    $detailsHeading = $detailsFacet ? $facets[$detailsFacet]['label'] : 'Details';

    // Column heading link: first click sorts ascending, the next click reverses it.
    $sortHeading = function (string $label, string $asc, string $desc) use ($sort, $urlWith) {
        $active = in_array($sort, [$asc, $desc], true);
        $next = $sort === $asc ? $desc : $asc;

        return compact('label', 'active') + [
            'url' => $urlWith(array_merge(request()->except(['page', 'sort']), ['sort' => $next])),
            'icon' => ! $active ? 'solar:sort-vertical-linear' : ($sort === $asc && $asc !== $desc ? 'solar:alt-arrow-up-linear' : 'solar:alt-arrow-down-linear'),
        ];
    };
    $headings = [
        $sortHeading('Applicant', 'name_asc', 'name_desc'),
        $detailsFacet ? $sortHeading($detailsHeading, $detailsFacet.'_asc', $detailsFacet.'_desc') : null,
        $sortHeading('Submitted', 'oldest', 'newest'),
        $sortHeading('Status', 'status', 'status'),
    ];

    $tabs = [
        ['key' => '', 'label' => 'All', 'count' => $entryStats['total']],
        ['key' => 'pending', 'label' => 'Pending', 'count' => $entryStats['pending']],
        ['key' => 'approved', 'label' => 'Approved', 'count' => $entryStats['approved']],
        ['key' => 'rejected', 'label' => 'Rejected', 'count' => $entryStats['rejected']],
    ];

    // Each active refinement becomes a chip whose link removes only that refinement.
    $chipFor = fn (string $key, string $label, string $value) => [
        'label' => $label,
        'value' => $value,
        'url' => $urlWith(request()->except(['page', $key])),
    ];
    $panelChips = [];
    foreach (array_keys($facetOptions) as $name) {
        if (request()->filled($name)) {
            $panelChips[] = $chipFor($name, $facets[$name]['label'], (string) request($name));
        }
    }
    if (request()->filled('date_from')) {
        $panelChips[] = $chipFor('date_from', 'From', \Illuminate\Support\Carbon::hasFormat((string) request('date_from'), 'Y-m-d') ? \Illuminate\Support\Carbon::parse(request('date_from'))->format('d M Y') : (string) request('date_from'));
    }
    if (request()->filled('date_to')) {
        $panelChips[] = $chipFor('date_to', 'To', \Illuminate\Support\Carbon::hasFormat((string) request('date_to'), 'Y-m-d') ? \Illuminate\Support\Carbon::parse(request('date_to'))->format('d M Y') : (string) request('date_to'));
    }
    $chips = $panelChips;
    if (request()->filled('search')) {
        array_unshift($chips, $chipFor('search', 'Search', (string) request('search')));
    }
    $keepSortOnly = $urlWith($sort !== FormEntryBrowse::DEFAULT_SORT ? ['sort' => $sort] : []);
    $isRefined = $chips !== [] || $currentStatus !== '';
@endphp

<div class="dashboard-main-body" id="form-center-page">
    @include('admin.partials.page-header', [
        'title' => $form->name,
        'subtitle' => number_format($entryStats['total']).' '.\Illuminate\Support\Str::plural('submission', $entryStats['total']).($entryStats['pending'] > 0 ? ' · '.number_format($entryStats['pending']).' awaiting review' : ''),
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

    <section class="fc-subs" aria-label="{{ $form->name }} submissions">
        <nav class="fc-subs-tabs" aria-label="Filter by status">
            @foreach($tabs as $tab)
                @php $isActive = $currentStatus === $tab['key']; @endphp
                <a href="{{ $urlWith(array_merge(request()->except(['status', 'page']), ['status' => $tab['key']])) }}"
                   @class(['fc-subs-tab', 'is-active' => $isActive])
                   data-status="{{ $tab['key'] ?: 'all' }}"
                   @if($isActive) aria-current="page" @endif>
                    <span class="fc-subs-tab__dot" aria-hidden="true"></span>
                    {{ $tab['label'] }}
                    <span class="fc-subs-tab__count">{{ number_format($tab['count']) }}</span>
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ $baseUrl }}" class="fc-subs-controls" id="fc-entries-filter">
            @if($currentStatus !== '')
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif

            <div class="fc-subs-toolbar">
                <label class="fc-subs-search" for="fc-entry-search">
                    <span class="visually-hidden">Search submissions</span>
                    <iconify-icon icon="solar:magnifer-linear" aria-hidden="true"></iconify-icon>
                    <input type="search" id="fc-entry-search" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone or any answer" autocomplete="off">
                </label>

                <button type="button"
                        @class(['fc-subs-filter-toggle', 'is-active' => $panelChips !== []])
                        data-fc-filter-toggle
                        aria-controls="fc-subs-panel"
                        aria-expanded="{{ $panelChips !== [] ? 'true' : 'false' }}">
                    <iconify-icon icon="solar:filter-linear" aria-hidden="true"></iconify-icon>
                    Filters
                    @if($panelChips !== [])
                        <span class="fc-subs-filter-toggle__count">{{ count($panelChips) }}</span>
                    @endif
                </button>

                <div class="fc-subs-sort">
                    <label for="fc-entry-sort">Sort by</label>
                    <select id="fc-entry-sort" name="sort" class="form-select" data-fc-auto-submit>
                        @foreach($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="fc-subs-panel" id="fc-subs-panel" @if($panelChips === []) hidden @endif>
                <div class="fc-subs-panel__fields">
                    @foreach($facetOptions as $name => $options)
                        <div class="fc-subs-field">
                            <label for="fc-entry-{{ $name }}">{{ $facets[$name]['label'] }}</label>
                            @if($facets[$name]['match'] === 'contains')
                                <input type="search" id="fc-entry-{{ $name }}" name="{{ $name }}" class="form-control" list="fc-entry-{{ $name }}-list" value="{{ request($name) }}" placeholder="Type or choose" autocomplete="off">
                                <datalist id="fc-entry-{{ $name }}-list">
                                    @foreach($options as $option)
                                        <option value="{{ $option }}"></option>
                                    @endforeach
                                </datalist>
                            @else
                                <select id="fc-entry-{{ $name }}" name="{{ $name }}" class="form-select" data-fc-auto-submit>
                                    <option value="">All</option>
                                    @foreach($options as $option)
                                        <option value="{{ $option }}" @selected(mb_strtolower((string) request($name)) === mb_strtolower($option))>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    @endforeach
                    <div class="fc-subs-field">
                        <label for="fc-date-from">Submitted from</label>
                        <input type="date" id="fc-date-from" name="date_from" class="form-control" value="{{ request('date_from') }}" max="{{ request('date_to') }}">
                    </div>
                    <div class="fc-subs-field">
                        <label for="fc-date-to">Submitted to</label>
                        <input type="date" id="fc-date-to" name="date_to" class="form-control" value="{{ request('date_to') }}" min="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="fc-subs-panel__actions">
                    <button type="submit" class="fc-subs-btn fc-subs-btn--primary">Apply filters</button>
                </div>
            </div>
        </form>

        <div class="fc-subs-summary">
            <span class="fc-subs-summary__count" role="status">
                @if($entries->total() > 0)
                    Showing <strong>{{ number_format($entries->firstItem()) }}–{{ number_format($entries->lastItem()) }}</strong> of <strong>{{ number_format($entries->total()) }}</strong>
                @else
                    No results
                @endif
            </span>
            @foreach($chips as $chip)
                <a href="{{ $chip['url'] }}" class="fc-subs-chip" title="Remove this filter">
                    <span class="fc-subs-chip__label">{{ $chip['label'] }}:</span>
                    {{ \Illuminate\Support\Str::limit($chip['value'], 32) }}
                    <iconify-icon icon="solar:close-circle-linear" aria-hidden="true"></iconify-icon>
                    <span class="visually-hidden">Remove</span>
                </a>
            @endforeach
            @if($chips !== [])
                <a href="{{ $urlWith(['status' => $currentStatus, 'sort' => $sort !== FormEntryBrowse::DEFAULT_SORT ? $sort : null]) }}" class="fc-subs-clear">Clear all</a>
            @endif
        </div>

        @if($entries->isEmpty())
            <div class="fc-subs-empty">
                <span class="fc-subs-empty__icon" aria-hidden="true"><iconify-icon icon="solar:inbox-linear"></iconify-icon></span>
                @if($isRefined)
                    <strong>No submissions match</strong>
                    <p>Try a different search or remove a filter.</p>
                    <a href="{{ $keepSortOnly }}" class="fc-subs-btn">Show all submissions</a>
                @else
                    <strong>No submissions yet</strong>
                    <p>New submissions for this form will appear here.</p>
                @endif
            </div>
        @else
            <div class="fc-subs-table">
                <div class="fc-subs-head">
                    @foreach($headings as $heading)
                        @if($heading)
                            <a href="{{ $heading['url'] }}" @class(['fc-sort-link', 'is-active' => $heading['active']])>
                                {{ $heading['label'] }}
                                <iconify-icon icon="{{ $heading['icon'] }}" aria-hidden="true"></iconify-icon>
                            </a>
                        @else
                            <span>{{ $detailsHeading }}</span>
                        @endif
                    @endforeach
                    <span class="visually-hidden">Actions</span>
                </div>
                <div class="fc-subs-rows">
                    @foreach($entries as $entry)
                        @include('admin.form-manager.partials.entry-row', compact('entry', 'form', 'facets', 'detailsHeading'))
                    @endforeach
                </div>
            </div>
            @if($entries->hasPages())
                <div class="fc-pagination">
                    {{ $entries->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection

@section('script')
<script>
(function () {
    var page = document.getElementById('form-center-page');
    if (!page) return;

    var toggle = page.querySelector('[data-fc-filter-toggle]');
    var panel = page.querySelector('#fc-subs-panel');
    if (toggle && panel) {
        toggle.addEventListener('click', function () {
            panel.hidden = !panel.hidden;
            toggle.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
            if (!panel.hidden) {
                var first = panel.querySelector('input, select');
                if (first) first.focus();
            }
        });
    }

    page.addEventListener('change', function (event) {
        var field = event.target.closest('[data-fc-auto-submit]');
        if (field && field.form) field.form.submit();
    });

    // The whole row opens the submission; controls inside it keep their own behaviour.
    page.addEventListener('click', function (event) {
        var row = event.target.closest('[data-fc-row-href]');
        if (!row || event.target.closest('a, button, select, input, label, form')) return;
        window.location.href = row.getAttribute('data-fc-row-href');
    });
})();
</script>
@endsection
