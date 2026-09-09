@php
    $filterUrl = fn (array $merge = [], array $except = []) => route('admin.email.campaigns.index', array_merge(
        request()->except(array_merge(['page'], $except)),
        $merge
    ));

    $statuses = ['draft', 'scheduled', 'sending', 'sent', 'failed', 'cancelled'];
    $activeStatus = request('status');
    $activeFilterChips = [];
    if (request()->filled('search')) {
        $activeFilterChips[] = ['label' => 'Search', 'value' => request('search'), 'url' => $filterUrl([], ['search'])];
    }
    if (request()->filled('status')) {
        $activeFilterChips[] = ['label' => 'Status', 'value' => ucfirst(request('status')), 'url' => $filterUrl([], ['status'])];
    }
@endphp
<div class="crm-filter-workspace em-campaign-finder">
    <div class="em-campaign-finder__lookup">
        <form method="GET" action="{{ route('admin.email.campaigns.index') }}" class="em-campaign-search">
            @foreach(request()->except(['page', 'search']) as $key => $value)
                @if(is_array($value))
                    @foreach($value as $nestedKey => $nestedValue)
                        <input type="hidden" name="{{ $key }}[{{ $nestedKey }}]" value="{{ $nestedValue }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <div class="crm-ai-search">
                <div class="crm-ai-search__head">
                    <span class="crm-ai-search__badge">
                        <iconify-icon icon="solar:magnifer-linear" aria-hidden="true"></iconify-icon>
                        Find
                    </span>
                    <span class="crm-ai-search__hint">Search campaigns by name</span>
                </div>
                <div class="crm-smart-search__shell crm-ai-search__shell">
                    <span class="crm-ai-search__icon" aria-hidden="true">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                    </span>
                    <label class="visually-hidden" for="em-campaigns-search">Search campaigns</label>
                    <input type="search"
                           id="em-campaigns-search"
                           name="search"
                           class="crm-smart-search__input crm-ai-search__input"
                           value="{{ request('search') }}"
                           placeholder="Try: open day · welcome · reminder"
                           autocomplete="off"
                           aria-label="Search campaigns">
                    <button type="submit" class="crm-smart-search__go crm-ai-search__go">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        <span>Search</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="em-campaign-finder__statuses">
        <div class="crm-lead-finder__sources-head">
            <h3 class="crm-lead-finder__sources-title">Campaign status</h3>
            <p class="crm-lead-finder__sources-sub">Filter by broadcast lifecycle</p>
        </div>
        <div class="em-campaign-finder__status-grid">
            <a href="{{ $activeStatus ? $filterUrl([], ['status']) : $filterUrl() }}"
               @class(['crm-source-card', 'crm-source-card--all', 'is-active' => ! $activeStatus])>
                <span class="crm-source-card__icon" aria-hidden="true"><iconify-icon icon="solar:layers-minimalistic-linear"></iconify-icon></span>
                <span class="crm-source-card__label">All</span>
            </a>
            @foreach($statuses as $status)
                @php $isActive = $activeStatus === $status; @endphp
                <a href="{{ $isActive ? $filterUrl([], ['status']) : $filterUrl(['status' => $status], ['status']) }}"
                   @class(['crm-source-card', 'crm-source-card--'.$status, 'is-active' => $isActive])>
                    <span class="crm-source-card__icon" aria-hidden="true"><iconify-icon icon="solar:letter-linear"></iconify-icon></span>
                    <span class="crm-source-card__label">{{ ucfirst($status) }}</span>
                </a>
            @endforeach
        </div>
    </div>

    @if(count($activeFilterChips) > 0)
        <div class="crm-lead-finder__active">
            <span class="crm-lead-finder__active-label">Active filters</span>
            <div class="crm-lead-finder__active-list">
                @foreach($activeFilterChips as $chip)
                    <a href="{{ $chip['url'] }}" class="crm-active-filter">
                        <span>{{ $chip['label'] }}:</span>
                        <strong>{{ $chip['value'] }}</strong>
                        <iconify-icon icon="solar:close-circle-linear" aria-hidden="true"></iconify-icon>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('admin.email.campaigns.index') }}" class="crm-lead-finder__clear">Reset all</a>
        </div>
    @endif
</div>
