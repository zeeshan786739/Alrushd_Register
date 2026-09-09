@php
    $items = $items ?? collect();
    $activeCount = $items->where('status', 1)->count();
    $inactiveCount = $items->where('status', '!=', 1)->count();
    $totalCount = $items->count();
@endphp
<div class="oe-stat-grid" id="oeStatusFilters">
    <button type="button" class="oe-stat-card oe-stat-card--all is-active" data-oe-stat-filter="all" aria-pressed="true">
        <span class="oe-stat-card__icon"><iconify-icon icon="solar:documents-linear"></iconify-icon></span>
        <span>
            <span class="oe-stat-card__label">All records</span>
            <strong>{{ number_format($totalCount) }}</strong>
        </span>
    </button>
    <button type="button" class="oe-stat-card oe-stat-card--active" data-oe-stat-filter="active" aria-pressed="false">
        <span class="oe-stat-card__icon"><iconify-icon icon="solar:check-circle-linear"></iconify-icon></span>
        <span>
            <span class="oe-stat-card__label">Active</span>
            <strong>{{ number_format($activeCount) }}</strong>
        </span>
    </button>
    <button type="button" class="oe-stat-card oe-stat-card--inactive" data-oe-stat-filter="inactive" aria-pressed="false">
        <span class="oe-stat-card__icon"><iconify-icon icon="solar:close-circle-linear"></iconify-icon></span>
        <span>
            <span class="oe-stat-card__label">Inactive</span>
            <strong>{{ number_format($inactiveCount) }}</strong>
        </span>
    </button>
</div>
