@php
    $statusCounts = $statusCounts ?? ['all' => 0, 'active' => 0, 'prospect' => 0, 'inactive' => 0];
    $currentAdminId = auth('admin')->id();

    $filterUrl = fn (array $merge = [], array $except = []) => route('admin.crm.customers.index', array_merge(
        request()->except(array_merge(['page'], $except)),
        $merge
    ));

    $isAssignedToMe = request('assigned_to') === 'me' || (string) request('assigned_to') === (string) $currentAdminId;
    $isUnassigned = request('assigned_to') === 'unassigned';
    $isActiveStatus = request('status') === 'active';
    $isProspectStatus = request('status') === 'prospect';
    $isInactiveStatus = request('status') === 'inactive';

    $shortcuts = [
        ['key' => 'assigned_me', 'label' => 'My customers', 'icon' => 'solar:user-check-linear', 'active' => $isAssignedToMe, 'url' => $isAssignedToMe ? $filterUrl([], ['assigned_to']) : $filterUrl(['assigned_to' => 'me'], ['assigned_to'])],
        ['key' => 'unassigned', 'label' => 'Unassigned', 'icon' => 'solar:user-cross-linear', 'active' => $isUnassigned, 'url' => $isUnassigned ? $filterUrl([], ['assigned_to']) : $filterUrl(['assigned_to' => 'unassigned'], ['assigned_to'])],
        ['key' => 'active', 'label' => 'Active', 'icon' => 'solar:check-circle-linear', 'active' => $isActiveStatus, 'url' => $isActiveStatus ? $filterUrl([], ['status']) : $filterUrl(['status' => 'active'], ['status'])],
        ['key' => 'prospect', 'label' => 'Prospects', 'icon' => 'solar:user-id-linear', 'active' => $isProspectStatus, 'url' => $isProspectStatus ? $filterUrl([], ['status']) : $filterUrl(['status' => 'prospect'], ['status'])],
    ];

    $statusCards = [
        ['value' => '', 'label' => 'All customers', 'hint' => 'Every customer in your workspace', 'icon' => 'solar:users-group-rounded-linear', 'tone' => 'all', 'count' => (int) ($statusCounts['all'] ?? 0)],
        ['value' => 'active', 'label' => 'Active', 'hint' => 'Paying or engaged customers', 'icon' => 'solar:user-check-linear', 'tone' => 'active', 'count' => (int) ($statusCounts['active'] ?? 0)],
        ['value' => 'prospect', 'label' => 'Prospects', 'hint' => 'Potential customers', 'icon' => 'solar:user-id-linear', 'tone' => 'prospect', 'count' => (int) ($statusCounts['prospect'] ?? 0)],
        ['value' => 'inactive', 'label' => 'Inactive', 'hint' => 'Archived or dormant accounts', 'icon' => 'solar:user-block-linear', 'tone' => 'inactive', 'count' => (int) ($statusCounts['inactive'] ?? 0)],
    ];

    $activeFilterChips = [];
    if (request()->filled('search')) {
        $activeFilterChips[] = ['label' => 'Search', 'value' => request('search'), 'url' => $filterUrl([], ['search'])];
    }
    if (request()->filled('status') && ! $isActiveStatus && ! $isProspectStatus && ! $isInactiveStatus) {
        $activeFilterChips[] = ['label' => 'Status', 'value' => ucfirst(request('status')), 'url' => $filterUrl([], ['status'])];
    }
    if (request()->filled('assigned_to') && ! $isAssignedToMe && ! $isUnassigned) {
        $assigneeLabel = $admins->firstWhere('id', (int) request('assigned_to'))?->name ?? 'Owner';
        $activeFilterChips[] = ['label' => 'Owner', 'value' => $assigneeLabel, 'url' => $filterUrl([], ['assigned_to'])];
    }
    if (request()->filled('source')) {
        $activeFilterChips[] = ['label' => 'Source', 'value' => request('source'), 'url' => $filterUrl([], ['source'])];
    }

    $expandMore = request()->filled('assigned_to') || request()->filled('source')
        || request()->filled('sort_by') || request()->filled('sort_order');
@endphp
<div class="crm-filter-workspace crm-customer-finder" data-crm-filter-workspace>
    <div class="crm-customer-finder__lookup">
        <form method="GET" action="{{ route('admin.crm.customers.index') }}" class="crm-customer-search" data-crm-customer-search-form>
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
                    <span class="crm-ai-search__hint">Search by name, email, phone, or company</span>
                </div>
                <div class="crm-smart-search__shell crm-ai-search__shell">
                    <span class="crm-ai-search__icon" aria-hidden="true">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                    </span>
                    <label class="visually-hidden" for="crm-customers-search">Search customers</label>
                    <input type="search"
                           id="crm-customers-search"
                           name="search"
                           class="crm-smart-search__input crm-ai-search__input"
                           value="{{ request('search') }}"
                           placeholder="Try: acme · john@ · unassigned active"
                           autocomplete="off"
                           aria-label="Search customers">
                    <button type="submit" class="crm-smart-search__go crm-ai-search__go" aria-label="Search customers">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        <span>Search</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="crm-customer-finder__quick">
            <div class="crm-lead-finder__quick-pills" role="toolbar" aria-label="Quick filters">
                @foreach($shortcuts as $chip)
                    <a href="{{ $chip['url'] }}"
                       @class(['crm-quick-pill', 'is-active' => $chip['active']])
                       @if($chip['active']) aria-current="true" @endif>
                        <iconify-icon icon="{{ $chip['icon'] }}" aria-hidden="true"></iconify-icon>
                        {{ $chip['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="crm-customer-finder__statuses">
        <div class="crm-lead-finder__sources-head">
            <h3 class="crm-lead-finder__sources-title">Customer status</h3>
            <p class="crm-lead-finder__sources-sub">Filter by lifecycle stage — active, prospect, or inactive</p>
        </div>
        <div class="crm-customer-finder__status-grid" role="tablist" aria-label="Customer status">
            @foreach($statusCards as $card)
                @php
                    $isActive = ($card['value'] === '' && ! request()->filled('status'))
                        || ($card['value'] !== '' && request('status') === $card['value']);
                    $cardUrl = $card['value'] === ''
                        ? $filterUrl([], ['status'])
                        : $filterUrl(['status' => $card['value']], ['status']);
                @endphp
                <a href="{{ $cardUrl }}"
                   @class([
                       'crm-source-card',
                       'crm-source-card--'.$card['tone'],
                       'is-active' => $isActive,
                       'is-empty' => $card['value'] !== '' && $card['count'] === 0,
                   ])
                   role="tab"
                   @if($isActive) aria-selected="true" @else aria-selected="false" @endif
                   title="{{ $card['hint'] }}">
                    <span class="crm-source-card__icon" aria-hidden="true">
                        <iconify-icon icon="{{ $card['icon'] }}"></iconify-icon>
                    </span>
                    <span class="crm-source-card__label">{{ $card['label'] }}</span>
                    <strong class="crm-source-card__count">{{ number_format($card['count']) }}</strong>
                </a>
            @endforeach
        </div>
    </div>

    <form method="GET" action="{{ route('admin.crm.customers.index') }}" id="crm-customers-filter-form" class="crm-customer-finder__refine-form">
        @foreach(request()->only(['search', 'status']) as $key => $value)
            @if($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <details class="crm-lead-finder__more" @if($expandMore) open @endif>
            <summary class="crm-lead-finder__more-summary">
                <iconify-icon icon="solar:filter-linear" aria-hidden="true"></iconify-icon>
                More filters
            </summary>
            <div class="crm-lead-finder__refine">
                <div class="crm-lead-finder__refine-field">
                    <label for="crm-customers-filter-owner">Owner</label>
                    <select id="crm-customers-filter-owner" name="assigned_to" class="form-select" data-crm-filter-auto-submit>
                        <option value="">Any owner</option>
                        <option value="me" @selected(request('assigned_to') === 'me')>Assigned to me</option>
                        <option value="unassigned" @selected(request('assigned_to') === 'unassigned')>Unassigned</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" @selected((string) request('assigned_to') === (string) $admin->id)>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-lead-finder__refine-field">
                    <label for="crm-customers-filter-source">Source</label>
                    <input type="text"
                           id="crm-customers-filter-source"
                           name="source"
                           class="form-control"
                           value="{{ request('source') }}"
                           placeholder="e.g. referral, website">
                </div>
                <div class="crm-lead-finder__refine-field">
                    <label for="crm-customers-filter-sort">Sort by</label>
                    <select id="crm-customers-filter-sort" name="sort_by" class="form-select" data-crm-filter-auto-submit>
                        <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>Date added</option>
                        <option value="name" @selected(request('sort_by') === 'name')>Name</option>
                        <option value="company" @selected(request('sort_by') === 'company')>Company</option>
                        <option value="lifetime_value" @selected(request('sort_by') === 'lifetime_value')>Lifetime value</option>
                        <option value="status" @selected(request('sort_by') === 'status')>Status</option>
                    </select>
                </div>
                <div class="crm-lead-finder__refine-field">
                    <label for="crm-customers-filter-order">Order</label>
                    <select id="crm-customers-filter-order" name="sort_order" class="form-select" data-crm-filter-auto-submit>
                        <option value="desc" @selected(request('sort_order', 'desc') === 'desc')>Newest first</option>
                        <option value="asc" @selected(request('sort_order') === 'asc')>Oldest first</option>
                    </select>
                </div>
                <div class="crm-lead-finder__refine-field crm-lead-finder__refine-field--wide">
                    <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8">Apply filters</button>
                    <a href="{{ route('admin.crm.customers.index') }}" class="btn btn-sm btn-outline-neutral-500 radius-8">Reset all</a>
                </div>
            </div>
        </details>
    </form>

    @if(! empty($activeFilterChips))
        <div class="crm-lead-finder__active">
            <span class="crm-lead-finder__active-label">Active filters</span>
            <div class="crm-lead-finder__active-list">
                @foreach($activeFilterChips as $chip)
                    <a href="{{ $chip['url'] }}" class="crm-active-filter" title="Remove {{ $chip['label'] }} filter">
                        <span>{{ $chip['label'] }}:</span>
                        <strong>{{ Str::limit($chip['value'], 28) }}</strong>
                        <iconify-icon icon="solar:close-circle-linear" aria-hidden="true"></iconify-icon>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('admin.crm.customers.index') }}" class="crm-lead-finder__clear">Reset all</a>
        </div>
    @endif
</div>
