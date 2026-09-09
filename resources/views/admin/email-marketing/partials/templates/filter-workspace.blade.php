@php
    $filterUrl = fn (array $except = []) => route('admin.email.templates.index', request()->except(array_merge(['page', 'search'], $except)));
    $activeFilterChips = request()->filled('search')
        ? [['label' => 'Search', 'value' => request('search'), 'url' => $filterUrl(['search'])]]
        : [];
@endphp
<div class="crm-filter-workspace em-template-finder">
    <div class="em-template-finder__lookup">
        <form method="GET" action="{{ route('admin.email.templates.index') }}" class="em-template-search">
            <div class="crm-ai-search">
                <div class="crm-ai-search__head">
                    <span class="crm-ai-search__badge">
                        <iconify-icon icon="solar:magnifer-linear" aria-hidden="true"></iconify-icon>
                        Find
                    </span>
                    <span class="crm-ai-search__hint">Search templates by name or subject</span>
                </div>
                <div class="crm-smart-search__shell crm-ai-search__shell">
                    <span class="crm-ai-search__icon" aria-hidden="true">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                    </span>
                    <label class="visually-hidden" for="em-templates-search">Search templates</label>
                    <input type="search"
                           id="em-templates-search"
                           name="search"
                           class="crm-smart-search__input crm-ai-search__input"
                           value="{{ request('search') }}"
                           placeholder="Try: welcome · newsletter · reminder"
                           autocomplete="off"
                           aria-label="Search templates">
                    <button type="submit" class="crm-smart-search__go crm-ai-search__go">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        <span>Search</span>
                    </button>
                </div>
            </div>
        </form>
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
            <a href="{{ route('admin.email.templates.index') }}" class="crm-lead-finder__clear">Reset all</a>
        </div>
    @endif
</div>
