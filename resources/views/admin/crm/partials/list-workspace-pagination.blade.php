@if($paginator->total() > 0)
    @php
        $routeName = $routeName ?? 'admin.crm.customers.index';
        $entityLabel = $entityLabel ?? 'records';
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $perPageOptions = $perPageOptions ?? [15, 25, 50];
        $selectedPerPage = (int) request('per_page', $perPageOptions[0]);

        $pageLinks = $lastPage <= 7 ? range(1, $lastPage) : array_values(array_filter(array_merge(
            [1],
            ($rangeStart = max(2, $currentPage - 1)) > 2 ? ['gap-left'] : [],
            range($rangeStart, min($lastPage - 1, $currentPage + 1)),
            min($lastPage - 1, $currentPage + 1) < $lastPage - 1 ? ['gap-right'] : [],
            $lastPage > 1 ? [$lastPage] : []
        )));
    @endphp

    <div class="crm-leads-pagination">
        <div class="crm-leads-pagination__meta">
            <div class="crm-leads-pagination__summary">
                <span class="crm-leads-pagination__summary-label">Showing</span>
                <strong>{{ number_format($paginator->firstItem()) }}–{{ number_format($paginator->lastItem()) }}</strong>
                <span class="crm-leads-pagination__summary-total">of {{ number_format($paginator->total()) }} {{ $entityLabel }}</span>
            </div>
            <form method="GET" action="{{ route($routeName) }}" class="crm-leads-pagination__per-page">
                @foreach(request()->except(['page', 'per_page']) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $nestedKey => $nestedValue)
                            <input type="hidden" name="{{ $key }}[{{ $nestedKey }}]" value="{{ $nestedValue }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="{{ $paginationId ?? 'crm-per-page' }}" class="crm-leads-pagination__per-page-label">
                    <iconify-icon icon="solar:layers-minimalistic-linear" aria-hidden="true"></iconify-icon>
                    <span>Per page</span>
                </label>
                <select id="{{ $paginationId ?? 'crm-per-page' }}" name="per_page" class="crm-leads-pagination__per-page-select" onchange="this.form.submit()">
                    @foreach($perPageOptions as $size)
                        <option value="{{ $size }}" @selected($selectedPerPage === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @if($paginator->hasPages())
            <nav class="crm-leads-pagination__nav" aria-label="Pagination">
                @if($paginator->onFirstPage())
                    <span class="crm-page-btn crm-page-btn--icon is-disabled" aria-disabled="true"><iconify-icon icon="solar:double-alt-arrow-left-linear"></iconify-icon></span>
                    <span class="crm-page-btn is-disabled" aria-disabled="true"><iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon><span>Previous</span></span>
                @else
                    <a href="{{ $paginator->url(1) }}" class="crm-page-btn crm-page-btn--icon" rel="first"><iconify-icon icon="solar:double-alt-arrow-left-linear"></iconify-icon></a>
                    <a href="{{ $paginator->previousPageUrl() }}" class="crm-page-btn" rel="prev"><iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon><span>Previous</span></a>
                @endif
                <div class="crm-leads-pagination__pages">
                    @foreach($pageLinks as $page)
                        @if(is_string($page))
                            <span class="crm-page-gap">…</span>
                        @elseif($page === $currentPage)
                            <span class="crm-page-num is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $paginator->url($page) }}" class="crm-page-num">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="crm-page-btn" rel="next"><span>Next</span><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></a>
                    <a href="{{ $paginator->url($lastPage) }}" class="crm-page-btn crm-page-btn--icon" rel="last"><iconify-icon icon="solar:double-alt-arrow-right-linear"></iconify-icon></a>
                @else
                    <span class="crm-page-btn is-disabled" aria-disabled="true"><span>Next</span><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></span>
                    <span class="crm-page-btn crm-page-btn--icon is-disabled" aria-disabled="true"><iconify-icon icon="solar:double-alt-arrow-right-linear"></iconify-icon></span>
                @endif
            </nav>
        @endif
    </div>
@endif
