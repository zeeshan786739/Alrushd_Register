@php
    $filterUrl = fn (array $merge = [], array $except = []) => route('admin.crm.quotations.index', array_merge(request()->except(array_merge(['page'], $except)), $merge));
    $statusCards = [
        ['value' => '', 'label' => 'All', 'icon' => 'solar:document-linear', 'tone' => 'all', 'count' => (int) ($statusCounts['all'] ?? 0)],
        ['value' => 'draft', 'label' => 'Draft', 'icon' => 'solar:pen-linear', 'tone' => 'draft', 'count' => (int) ($statusCounts['draft'] ?? 0)],
        ['value' => 'sent', 'label' => 'Sent', 'icon' => 'solar:letter-linear', 'tone' => 'sent', 'count' => (int) ($statusCounts['sent'] ?? 0)],
        ['value' => 'accepted', 'label' => 'Accepted', 'icon' => 'solar:check-circle-linear', 'tone' => 'accepted', 'count' => (int) ($statusCounts['accepted'] ?? 0)],
        ['value' => 'rejected', 'label' => 'Rejected', 'icon' => 'solar:close-circle-linear', 'tone' => 'rejected', 'count' => (int) ($statusCounts['rejected'] ?? 0)],
        ['value' => 'expired', 'label' => 'Expired', 'icon' => 'solar:clock-circle-linear', 'tone' => 'expired', 'count' => (int) ($statusCounts['expired'] ?? 0)],
    ];
@endphp
<div class="crm-filter-workspace crm-module-finder">
    <div class="crm-module-finder__lookup">
        <form method="GET" action="{{ route('admin.crm.quotations.index') }}">
            @foreach(request()->except(['page', 'search']) as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
            <div class="crm-ai-search">
                <div class="crm-ai-search__head"><span class="crm-ai-search__badge"><iconify-icon icon="solar:magnifer-linear"></iconify-icon> Find</span><span class="crm-ai-search__hint">Search quotation number or customer</span></div>
                <div class="crm-smart-search__shell crm-ai-search__shell">
                    <span class="crm-ai-search__icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                    <input type="search" name="search" class="crm-smart-search__input crm-ai-search__input" value="{{ request('search') }}" placeholder="Try: QT-2024 · customer name">
                    <button type="submit" class="crm-smart-search__go crm-ai-search__go"><iconify-icon icon="solar:magnifer-linear"></iconify-icon><span>Search</span></button>
                </div>
            </div>
        </form>
    </div>
    <div class="crm-module-finder__statuses">
        <div class="crm-lead-finder__sources-head"><h3 class="crm-lead-finder__sources-title">Quotation status</h3><p class="crm-lead-finder__sources-sub">Filter by commercial stage</p></div>
        <div class="crm-module-finder__status-grid">
            @foreach($statusCards as $card)
                @php $isActive = ($card['value'] === '' && ! request()->filled('status')) || request('status') === $card['value']; @endphp
                <a href="{{ $card['value'] === '' ? $filterUrl([], ['status']) : $filterUrl(['status' => $card['value']], ['status']) }}" @class(['crm-source-card', 'crm-source-card--'.$card['tone'], 'is-active' => $isActive])>
                    <span class="crm-source-card__icon"><iconify-icon icon="{{ $card['icon'] }}"></iconify-icon></span>
                    <span class="crm-source-card__label">{{ $card['label'] }}</span>
                    <strong class="crm-source-card__count">{{ number_format($card['count']) }}</strong>
                </a>
            @endforeach
        </div>
    </div>
    <details class="crm-lead-finder__more" @if(request()->filled('customer_id')) open @endif>
        <summary class="crm-lead-finder__more-summary"><iconify-icon icon="solar:filter-linear"></iconify-icon> More filters</summary>
        <form method="GET" action="{{ route('admin.crm.quotations.index') }}" class="crm-lead-finder__refine">
            @foreach(request()->only(['search', 'status']) as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
            <div class="crm-lead-finder__refine-field">
                <label for="crm-quotations-customer">Customer</label>
                <select id="crm-quotations-customer" name="customer_id" class="form-select" data-crm-filter-auto-submit>
                    <option value="">Any customer</option>
                    @foreach($customers as $customer)<option value="{{ $customer->id }}" @selected((string) request('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>@endforeach
                </select>
            </div>
            <div class="crm-lead-finder__refine-field crm-lead-finder__refine-field--wide"><a href="{{ route('admin.crm.quotations.index') }}" class="btn btn-sm btn-outline-neutral-500 radius-8">Reset all</a></div>
        </form>
    </details>
</div>
