@if($paginator->total() > 0)
    @php
        $viewMode = $viewMode ?? 'board';
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $perPageOptions = $viewMode === 'list' ? [15, 25, 50] : [20, 50, 100];
        $defaultPerPage = $viewMode === 'list' ? 15 : 50;
        $selectedPerPage = (int) request('per_page', $defaultPerPage);

        $pageLinks = [];
        if ($lastPage <= 7) {
            $pageLinks = range(1, $lastPage);
        } else {
            $pageLinks[] = 1;
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);

            if ($rangeStart > 2) {
                $pageLinks[] = 'gap-left';
            }

            for ($page = $rangeStart; $page <= $rangeEnd; $page++) {
                $pageLinks[] = $page;
            }

            if ($rangeEnd < $lastPage - 1) {
                $pageLinks[] = 'gap-right';
            }

            $pageLinks[] = $lastPage;
        }
    @endphp

    <div class="crm-leads-pagination">
        <div class="crm-leads-pagination__meta">
            <div class="crm-leads-pagination__summary">
                @if($viewMode === 'board')
                    <span class="crm-leads-pagination__summary-label">Pipeline</span>
                    <strong>{{ number_format($boardLoadedCount ?? 0) }}</strong>
                    <span class="crm-leads-pagination__summary-total">of {{ number_format($paginator->total()) }} leads loaded</span>
                    @if(($boardLoadedCount ?? 0) < $paginator->total())
                        <span class="crm-leads-pagination__summary-note">— scroll a column to load more</span>
                    @endif
                @else
                    <span class="crm-leads-pagination__summary-label">Showing</span>
                    <strong>{{ number_format($paginator->firstItem()) }}–{{ number_format($paginator->lastItem()) }}</strong>
                    <span class="crm-leads-pagination__summary-total">of {{ number_format($paginator->total()) }} leads</span>
                @endif
            </div>

            @if($viewMode !== 'board')
            <form method="GET" action="{{ route('admin.crm.leads.index') }}" class="crm-leads-pagination__per-page">
                @foreach(request()->except(['page', 'per_page']) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $nestedKey => $nestedValue)
                            <input type="hidden" name="{{ $key }}[{{ $nestedKey }}]" value="{{ $nestedValue }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="crm-leads-per-page" class="crm-leads-pagination__per-page-label">
                    <iconify-icon icon="solar:layers-minimalistic-linear" aria-hidden="true"></iconify-icon>
                    <span>Per page</span>
                </label>
                <select id="crm-leads-per-page" name="per_page" class="crm-leads-pagination__per-page-select" onchange="this.form.submit()">
                    @foreach($perPageOptions as $size)
                        <option value="{{ $size }}" @selected($selectedPerPage === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>

        @if($viewMode !== 'board' && $paginator->hasPages())
            <nav class="crm-leads-pagination__nav" aria-label="Leads pagination">
                @if($paginator->onFirstPage())
                    <span class="crm-page-btn crm-page-btn--icon is-disabled" aria-disabled="true" title="First page">
                        <iconify-icon icon="solar:double-alt-arrow-left-linear"></iconify-icon>
                    </span>
                    <span class="crm-page-btn is-disabled" aria-disabled="true">
                        <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                        <span>Previous</span>
                    </span>
                @else
                    <a href="{{ $paginator->url(1) }}" class="crm-page-btn crm-page-btn--icon" rel="first" title="First page">
                        <iconify-icon icon="solar:double-alt-arrow-left-linear"></iconify-icon>
                    </a>
                    <a href="{{ $paginator->previousPageUrl() }}" class="crm-page-btn" rel="prev">
                        <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                        <span>Previous</span>
                    </a>
                @endif

                <div class="crm-leads-pagination__pages" role="list">
                    @foreach($pageLinks as $page)
                        @if(is_string($page))
                            <span class="crm-page-gap" aria-hidden="true">…</span>
                        @elseif($page === $currentPage)
                            <span class="crm-page-num is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $paginator->url($page) }}" class="crm-page-num">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>

                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="crm-page-btn" rel="next">
                        <span>Next</span>
                        <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                    </a>
                    <a href="{{ $paginator->url($lastPage) }}" class="crm-page-btn crm-page-btn--icon" rel="last" title="Last page">
                        <iconify-icon icon="solar:double-alt-arrow-right-linear"></iconify-icon>
                    </a>
                @else
                    <span class="crm-page-btn is-disabled" aria-disabled="true">
                        <span>Next</span>
                        <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                    </span>
                    <span class="crm-page-btn crm-page-btn--icon is-disabled" aria-disabled="true" title="Last page">
                        <iconify-icon icon="solar:double-alt-arrow-right-linear"></iconify-icon>
                    </span>
                @endif
            </nav>
        @endif
    </div>
@endif
