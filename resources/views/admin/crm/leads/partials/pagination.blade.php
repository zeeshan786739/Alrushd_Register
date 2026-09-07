@if($paginator->total() > 0)
    <div class="crm-leads-pagination">
        <div class="crm-leads-pagination__summary">
            <strong>Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong>
            <span>of {{ number_format($paginator->total()) }} leads</span>
        </div>

        @if(($viewMode ?? 'board') === 'list')
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
                <label for="crm-leads-per-page">Rows</label>
                <select id="crm-leads-per-page" name="per_page" class="form-select form-select-sm radius-8" onchange="this.form.submit()">
                    @foreach([15, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }} / page</option>
                    @endforeach
                </select>
            </form>
        @endif

        @if($paginator->hasPages())
            <nav class="crm-leads-pagination__nav" aria-label="Leads pagination">
                @if($paginator->onFirstPage())
                    <span class="crm-page-btn is-disabled" aria-disabled="true">
                        <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                        Previous
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="crm-page-btn" rel="prev">
                        <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                        Previous
                    </a>
                @endif

                <span class="crm-page-indicator">
                    Page <strong>{{ $paginator->currentPage() }}</strong> of <strong>{{ $paginator->lastPage() }}</strong>
                </span>

                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="crm-page-btn" rel="next">
                        Next
                        <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                    </a>
                @else
                    <span class="crm-page-btn is-disabled" aria-disabled="true">
                        Next
                        <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                    </span>
                @endif
            </nav>
        @endif
    </div>
@endif
