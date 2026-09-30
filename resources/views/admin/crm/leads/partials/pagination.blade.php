@if($paginator->total() > 0)
    @php
        $viewMode = $viewMode ?? 'board';
    @endphp

    <div class="crm-leads-pagination">
        <div class="crm-leads-pagination__meta">
            <div class="crm-leads-pagination__summary">
                @if($viewMode === 'board')
                    <span class="crm-leads-pagination__summary-label">Pipeline</span>
                    <strong data-crm-board-loaded-count>{{ number_format($boardLoadedCount ?? 0) }}</strong>
                    <span class="crm-leads-pagination__summary-total">of <span data-crm-pagination-total>{{ number_format($paginator->total()) }}</span> leads loaded</span>
                    @if(($boardLoadedCount ?? 0) < $paginator->total())
                        <span class="crm-leads-pagination__summary-note">— scroll a column to load more</span>
                    @endif
                @else
                    <span class="crm-leads-pagination__summary-label">Loaded</span>
                    <strong data-crm-list-loaded-count>{{ number_format($listLoadedCount ?? 0) }}</strong>
                    <span class="crm-leads-pagination__summary-total">of <span data-crm-pagination-total>{{ number_format($paginator->total()) }}</span> leads</span>
                    <span class="crm-leads-pagination__summary-note">— 4 per category · See more below each group</span>
                @endif
            </div>
        </div>
    </div>
@endif
