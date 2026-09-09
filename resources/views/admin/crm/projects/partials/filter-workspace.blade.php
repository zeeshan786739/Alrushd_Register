@php
    $statusCounts = $statusCounts ?? [];
    $filterUrl = fn (array $merge = [], array $except = []) => route('admin.crm.projects.index', array_merge(request()->except(array_merge(['page'], $except)), $merge));
    $statusCards = [
        ['value' => '', 'label' => 'All projects', 'icon' => 'solar:folder-linear', 'tone' => 'all', 'count' => (int) ($statusCounts['all'] ?? 0)],
        ['value' => 'pending', 'label' => 'Pending', 'icon' => 'solar:clock-circle-linear', 'tone' => 'pending', 'count' => (int) ($statusCounts['pending'] ?? 0)],
        ['value' => 'in_progress', 'label' => 'In progress', 'icon' => 'solar:play-linear', 'tone' => 'in_progress', 'count' => (int) ($statusCounts['in_progress'] ?? 0)],
        ['value' => 'on_hold', 'label' => 'On hold', 'icon' => 'solar:pause-linear', 'tone' => 'on_hold', 'count' => (int) ($statusCounts['on_hold'] ?? 0)],
        ['value' => 'completed', 'label' => 'Completed', 'icon' => 'solar:check-circle-linear', 'tone' => 'completed', 'count' => (int) ($statusCounts['completed'] ?? 0)],
        ['value' => 'cancelled', 'label' => 'Cancelled', 'icon' => 'solar:close-circle-linear', 'tone' => 'cancelled', 'count' => (int) ($statusCounts['cancelled'] ?? 0)],
    ];
    $shortcuts = [
        ['label' => 'My projects', 'icon' => 'solar:user-check-linear', 'active' => request('assigned_to') === 'me', 'url' => request('assigned_to') === 'me' ? $filterUrl([], ['assigned_to']) : $filterUrl(['assigned_to' => 'me'], ['assigned_to'])],
        ['label' => 'Unassigned', 'icon' => 'solar:user-cross-linear', 'active' => request('assigned_to') === 'unassigned', 'url' => request('assigned_to') === 'unassigned' ? $filterUrl([], ['assigned_to']) : $filterUrl(['assigned_to' => 'unassigned'], ['assigned_to'])],
    ];
@endphp
<div class="crm-filter-workspace crm-module-finder" data-crm-filter-workspace>
    <div class="crm-module-finder__lookup">
        <form method="GET" action="{{ route('admin.crm.projects.index') }}">
            @foreach(request()->except(['page', 'search']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="crm-ai-search">
                <div class="crm-ai-search__head">
                    <span class="crm-ai-search__badge"><iconify-icon icon="solar:magnifer-linear"></iconify-icon> Find</span>
                    <span class="crm-ai-search__hint">Search by project name or code</span>
                </div>
                <div class="crm-smart-search__shell crm-ai-search__shell">
                    <span class="crm-ai-search__icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                    <input type="search" name="search" class="crm-smart-search__input crm-ai-search__input" value="{{ request('search') }}" placeholder="Try: website redesign · PRJ-2024">
                    <button type="submit" class="crm-smart-search__go crm-ai-search__go"><iconify-icon icon="solar:magnifer-linear"></iconify-icon><span>Search</span></button>
                </div>
            </div>
        </form>
        <div class="crm-lead-finder__quick-pills">
            @foreach($shortcuts as $chip)
                <a href="{{ $chip['url'] }}" @class(['crm-quick-pill', 'is-active' => $chip['active']])><iconify-icon icon="{{ $chip['icon'] }}"></iconify-icon>{{ $chip['label'] }}</a>
            @endforeach
        </div>
    </div>
    <div class="crm-module-finder__statuses">
        <div class="crm-lead-finder__sources-head">
            <h3 class="crm-lead-finder__sources-title">Project status</h3>
            <p class="crm-lead-finder__sources-sub">Filter by delivery stage</p>
        </div>
        <div class="crm-module-finder__status-grid">
            @foreach($statusCards as $card)
                @php $isActive = ($card['value'] === '' && ! request()->filled('status')) || request('status') === $card['value']; @endphp
                <a href="{{ $card['value'] === '' ? $filterUrl([], ['status']) : $filterUrl(['status' => $card['value']], ['status']) }}"
                   @class(['crm-source-card', 'crm-source-card--'.$card['tone'], 'is-active' => $isActive, 'is-empty' => $card['value'] !== '' && $card['count'] === 0])>
                    <span class="crm-source-card__icon"><iconify-icon icon="{{ $card['icon'] }}"></iconify-icon></span>
                    <span class="crm-source-card__label">{{ $card['label'] }}</span>
                    <strong class="crm-source-card__count">{{ number_format($card['count']) }}</strong>
                </a>
            @endforeach
        </div>
    </div>
    <form method="GET" action="{{ route('admin.crm.projects.index') }}" id="crm-projects-filter-form">
        @foreach(request()->only(['search', 'status']) as $key => $value)
            @if($value !== null && $value !== '')<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
        @endforeach
        <details class="crm-lead-finder__more" @if(request()->filled('customer_id') || request()->filled('priority') || request()->filled('assigned_to')) open @endif>
            <summary class="crm-lead-finder__more-summary"><iconify-icon icon="solar:filter-linear"></iconify-icon> More filters</summary>
            <div class="crm-lead-finder__refine">
                <div class="crm-lead-finder__refine-field">
                    <label for="crm-projects-customer">Customer</label>
                    <select id="crm-projects-customer" name="customer_id" class="form-select" data-crm-filter-auto-submit>
                        <option value="">Any customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) request('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-lead-finder__refine-field">
                    <label for="crm-projects-priority">Priority</label>
                    <select id="crm-projects-priority" name="priority" class="form-select" data-crm-filter-auto-submit>
                        <option value="">Any priority</option>
                        @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-lead-finder__refine-field">
                    <label for="crm-projects-owner">Owner</label>
                    <select id="crm-projects-owner" name="assigned_to" class="form-select" data-crm-filter-auto-submit>
                        <option value="">Any owner</option>
                        <option value="me" @selected(request('assigned_to') === 'me')>Assigned to me</option>
                        <option value="unassigned" @selected(request('assigned_to') === 'unassigned')>Unassigned</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" @selected((string) request('assigned_to') === (string) $admin->id)>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-lead-finder__refine-field crm-lead-finder__refine-field--wide">
                    <a href="{{ route('admin.crm.projects.index') }}" class="btn btn-sm btn-outline-neutral-500 radius-8">Reset all</a>
                </div>
            </div>
        </details>
    </form>
</div>
