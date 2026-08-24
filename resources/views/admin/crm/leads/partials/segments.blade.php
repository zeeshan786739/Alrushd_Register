@php
    $categories = $categories ?? collect();
    $segments = $segments ?? ['all' => ['total' => 0, 'new' => 0], 'uncategorized' => ['total' => 0, 'new' => 0], 'by_id' => []];
    $activeCategory = request('lead_category_id');
    $queryBase = request()->except(['lead_category_id', 'page']);
    $uncategorizedTotal = (int) ($segments['uncategorized']['total'] ?? 0);
@endphp
@if(\App\Support\LeadCategorySchema::ready())
<section class="crm-lead-segments mb-20" aria-label="Lead segments">
    <div class="crm-lead-segments__head">
        <h2 class="crm-lead-segments__title">Lead Segments</h2>
        <span class="crm-lead-segments__hint">Filter by category</span>
    </div>
    <div class="crm-lead-segments__track" data-crm-segments>
        <a href="{{ route('admin.crm.leads.index', $queryBase) }}"
           class="crm-segment-card {{ $activeCategory === null || $activeCategory === '' ? 'is-active' : '' }}"
           data-tone="navy">
            <span class="crm-segment-card__icon"><iconify-icon icon="solar:widget-2-linear"></iconify-icon></span>
            <span class="crm-segment-card__body">
                <span class="crm-segment-card__name">All Leads</span>
                <span class="crm-segment-card__counts">
                    <strong>{{ (int) ($segments['all']['total'] ?? 0) }}</strong>
                </span>
            </span>
        </a>

        @foreach($categories as $category)
            @php
                $counts = $segments['by_id'][$category->id] ?? ['total' => 0, 'new' => 0];
                $isActive = (string) $activeCategory === (string) $category->id;
            @endphp
            <a href="{{ route('admin.crm.leads.index', array_merge($queryBase, ['lead_category_id' => $category->id])) }}"
               class="crm-segment-card {{ $isActive ? 'is-active' : '' }}"
               data-tone="{{ $category->displayTone() }}">
                <span class="crm-segment-card__icon"><iconify-icon icon="{{ $category->displayIcon() }}"></iconify-icon></span>
                <span class="crm-segment-card__body">
                    <span class="crm-segment-card__name">{{ $category->name }}</span>
                    <span class="crm-segment-card__counts">
                        <strong>{{ (int) $counts['total'] }}</strong>
                        @if((int) $counts['new'] > 0)
                            <span class="crm-segment-card__new">{{ (int) $counts['new'] }} New</span>
                        @endif
                    </span>
                </span>
            </a>
        @endforeach

        @if($uncategorizedTotal > 0 || $activeCategory === 'uncategorized')
            <a href="{{ route('admin.crm.leads.index', array_merge($queryBase, ['lead_category_id' => 'uncategorized'])) }}"
               class="crm-segment-card {{ $activeCategory === 'uncategorized' ? 'is-active' : '' }}"
               data-tone="neutral">
                <span class="crm-segment-card__icon"><iconify-icon icon="solar:folder-open-linear"></iconify-icon></span>
                <span class="crm-segment-card__body">
                    <span class="crm-segment-card__name">Uncategorized</span>
                    <span class="crm-segment-card__counts">
                        <strong>{{ $uncategorizedTotal }}</strong>
                        @if((int) ($segments['uncategorized']['new'] ?? 0) > 0)
                            <span class="crm-segment-card__new">{{ (int) $segments['uncategorized']['new'] }} New</span>
                        @endif
                    </span>
                </span>
            </a>
        @endif
    </div>
</section>
@endif
