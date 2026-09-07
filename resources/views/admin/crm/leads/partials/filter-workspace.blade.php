@php
    use App\Enums\LeadPriority;
    use App\Enums\LeadStatus;
    use App\Support\LeadSourceOptions;
    use App\Support\LeadSmartSearch;

    $viewMode = $viewMode ?? 'board';
    $forms = $forms ?? collect();
    $categories = $categories ?? collect();
    $currentAdminId = auth('admin')->id();
    $smartSuggestions = $smartSearchSuggestions ?? [];

    $filterUrl = fn (array $merge = [], array $except = []) => route('admin.crm.leads.index', array_merge(
        request()->except(array_merge(['page'], $except)),
        $merge,
        ['view' => $viewMode]
    ));

    $smartViews = [
        [
            'key' => 'my_queue',
            'label' => 'My queue',
            'icon' => 'solar:user-check-linear',
            'description' => 'Assigned to you',
            'url' => $filterUrl(['assigned_to' => 'me'], ['assigned_to', 'follow_up', 'lead_status', 'priority', 'source', 'form_id']),
            'active' => request('assigned_to') === 'me' || (string) request('assigned_to') === (string) $currentAdminId,
        ],
        [
            'key' => 'needs_action',
            'label' => 'Needs action',
            'icon' => 'solar:alarm-linear',
            'description' => 'Overdue follow-ups',
            'url' => $filterUrl(['follow_up' => 'overdue'], ['follow_up', 'assigned_to', 'lead_status', 'priority', 'source', 'form_id']),
            'active' => request('follow_up') === 'overdue',
        ],
        [
            'key' => 'form_intake',
            'label' => 'Form intake',
            'icon' => 'solar:inbox-in-linear',
            'description' => 'Forms & submissions',
            'url' => $filterUrl(['source' => 'form_submission'], ['source', 'form_id', 'lead_status', 'priority', 'assigned_to', 'follow_up']),
            'active' => request('source') === 'form_submission' && ! request()->filled('form_id'),
        ],
        [
            'key' => 'new_leads',
            'label' => 'All new',
            'icon' => 'solar:star-linear',
            'description' => 'Fresh pipeline',
            'url' => $filterUrl(['lead_status' => 'new'], ['lead_status', 'assigned_to', 'follow_up', 'priority', 'source', 'form_id']),
            'active' => request('lead_status') === 'new' && ! request()->filled('assigned_to') && ! request()->filled('follow_up'),
        ],
    ];

    $advancedFilterKeys = ['source', 'form_id', 'advertising_platform', 'campaign_name', 'lead_status', 'priority', 'assigned_to', 'lead_category_id', 'follow_up', 'search'];
    $activeFilterCount = collect(request()->only($advancedFilterKeys))
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->count();

    $activeFilters = collect();
    $parsedContext = LeadSmartSearch::describe(
        request()->only(['assigned_to', 'follow_up', 'lead_status', 'priority', 'source', 'form_id', 'lead_category_id']),
        request('search'),
        $forms,
        $categories
    );

    if (request()->filled('search')) {
        $activeFilters->push(['label' => 'Search', 'value' => request('search'), 'url' => $filterUrl([], ['search'])]);
    }
    if (request()->filled('assigned_to')) {
        $label = match (request('assigned_to')) {
            'me', (string) $currentAdminId => 'Mine',
            'unassigned' => 'Unassigned',
            default => $admins->firstWhere('id', (int) request('assigned_to'))?->name ?? 'Assignee',
        };
        $activeFilters->push(['label' => 'Assignee', 'value' => $label, 'url' => $filterUrl([], ['assigned_to'])]);
    }
    if (request()->filled('follow_up')) {
        $activeFilters->push([
            'label' => 'Follow-up',
            'value' => request('follow_up') === 'today' ? 'Due today' : 'Overdue',
            'url' => $filterUrl([], ['follow_up']),
        ]);
    }
    if (request()->filled('lead_status')) {
        $activeFilters->push([
            'label' => 'Status',
            'value' => LeadStatus::tryFrom(request('lead_status'))?->label() ?? request('lead_status'),
            'url' => $filterUrl([], ['lead_status']),
        ]);
    }
    if (request()->filled('priority')) {
        $activeFilters->push([
            'label' => 'Priority',
            'value' => request('priority') === 'high_urgent' ? 'High & urgent' : (LeadPriority::tryFrom(request('priority'))?->label() ?? request('priority')),
            'url' => $filterUrl([], ['priority']),
        ]);
    }
    if (request()->filled('source')) {
        $activeFilters->push(['label' => 'Source', 'value' => LeadSourceOptions::label(request('source')), 'url' => $filterUrl([], ['source'])]);
    }
    if (request()->filled('form_id')) {
        $activeFilters->push(['label' => 'Form', 'value' => $forms->firstWhere('id', (int) request('form_id'))?->name ?? 'Form', 'url' => $filterUrl([], ['form_id', 'source'])]);
    }
    if (request()->filled('lead_category_id')) {
        $activeFilters->push(['label' => 'Category', 'value' => ($categoryFilterOptions ?? [])[request('lead_category_id')] ?? 'Category', 'url' => $filterUrl([], ['lead_category_id'])]);
    }
    if (request()->filled('advertising_platform')) {
        $activeFilters->push(['label' => 'Platform', 'value' => ucwords(str_replace('_', ' ', request('advertising_platform'))), 'url' => $filterUrl([], ['advertising_platform'])]);
    }
    if (request()->filled('campaign_name')) {
        $activeFilters->push(['label' => 'Campaign', 'value' => request('campaign_name'), 'url' => $filterUrl([], ['campaign_name'])]);
    }

    $expandAdvanced = $activeFilterCount > 0 && request()->boolean('filters_open');
@endphp
<div class="crm-filter-workspace crm-filter-workspace--smart" data-crm-filter-workspace>
    <form method="GET" action="{{ route('admin.crm.leads.index') }}" id="crm-leads-filter-form" class="crm-filter-workspace__form">
        <input type="hidden" name="view" value="{{ $viewMode }}" data-crm-view-input>
        @if(($viewMode ?? 'board') === 'list' && request('per_page'))
            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
        @endif

        <div class="crm-smart-search" data-crm-smart-search>
            <div class="crm-smart-search__shell">
                <span class="crm-smart-search__badge" aria-hidden="true">
                    <iconify-icon icon="solar:magic-stick-3-linear"></iconify-icon>
                    Smart search
                </span>
                <input type="search"
                       id="crm-smart-search-input"
                       class="crm-smart-search__input"
                       placeholder="Try: my urgent form leads, unassigned new, overdue follow ups…"
                       value="{{ request('search') && $activeFilterCount <= 1 ? request('search') : '' }}"
                       autocomplete="off"
                       aria-label="Smart search"
                       aria-expanded="false"
                       aria-controls="crm-smart-search-panel"
                       data-crm-smart-search-input>
                <button type="button" class="crm-smart-search__go" data-crm-smart-search-go aria-label="Apply smart search">
                    <iconify-icon icon="solar:arrow-right-linear"></iconify-icon>
                </button>
            </div>

            <div id="crm-smart-search-panel" class="crm-smart-search__panel" data-crm-smart-search-panel hidden>
                <div class="crm-smart-search__interpretation" data-crm-smart-search-label hidden>
                    <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                    <span data-crm-smart-search-label-text></span>
                </div>
                <div class="crm-smart-search__section">
                    <span class="crm-smart-search__section-label">Try asking</span>
                    <div class="crm-smart-search__suggestions">
                        @foreach($smartSuggestions as $suggestion)
                            <button type="button"
                                    class="crm-smart-search__suggestion"
                                    data-crm-smart-suggestion
                                    data-query="{{ $suggestion['query'] }}">
                                <strong>{{ $suggestion['label'] }}</strong>
                                <span>{{ $suggestion['description'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="crm-smart-views" aria-label="Smart views">
            @foreach($smartViews as $view)
                <a href="{{ $view['url'] }}"
                   @class(['crm-smart-view', 'is-active' => $view['active']])
                   title="{{ $view['description'] }}">
                    <iconify-icon icon="{{ $view['icon'] }}" aria-hidden="true"></iconify-icon>
                    <span class="crm-smart-view__label">{{ $view['label'] }}</span>
                </a>
            @endforeach

            <button type="button"
                    class="crm-smart-view crm-smart-view--more"
                    data-crm-toggle-advanced-filters
                    aria-expanded="{{ $expandAdvanced ? 'true' : 'false' }}"
                    aria-controls="crm-leads-advanced-filters">
                <iconify-icon icon="solar:tuning-2-linear" aria-hidden="true"></iconify-icon>
                <span class="crm-smart-view__label">More filters</span>
                @if($activeFilterCount > 0)
                    <span class="crm-smart-view__count">{{ $activeFilterCount }}</span>
                @endif
            </button>

            @if($activeFilterCount > 0)
                <a href="{{ route('admin.crm.leads.index', ['view' => $viewMode]) }}" class="crm-smart-view crm-smart-view--clear">
                    Clear all
                </a>
            @endif
        </div>

        @if($activeFilters->isNotEmpty())
            <div class="crm-filter-workspace__active" aria-label="Active filters">
                <span class="crm-filter-workspace__active-label">Showing</span>
                <div class="crm-filter-workspace__active-list">
                    @foreach($activeFilters as $filter)
                        <a href="{{ $filter['url'] }}" class="crm-active-filter" title="Remove {{ $filter['label'] }}">
                            <span class="crm-active-filter__label">{{ $filter['label'] }}</span>
                            <span class="crm-active-filter__value">{{ Str::limit($filter['value'], 24) }}</span>
                            <iconify-icon icon="solar:close-circle-linear" aria-hidden="true"></iconify-icon>
                        </a>
                    @endforeach
                </div>
            </div>
        @elseif($parsedContext !== 'All leads')
            <div class="crm-filter-workspace__context">{{ $parsedContext }}</div>
        @endif

        <div id="crm-leads-advanced-filters"
             class="crm-filter-workspace__advanced crm-filter-workspace__advanced--compact"
             data-crm-advanced-filters
             @if(! $expandAdvanced) hidden @endif>
            <input type="hidden" name="filters_open" value="1">
            <div class="crm-filter-workspace__advanced-grid">
                <div class="crm-filter-workspace__field">
                    <label for="search">Keyword</label>
                    <input type="search" name="search" id="search" class="form-control" placeholder="Name, email, phone" value="{{ request('search') }}">
                </div>
                <div class="crm-filter-workspace__field">
                    <label for="lead_status">Status</label>
                    <select name="lead_status" id="lead_status" class="form-select">
                        <option value="">Any status</option>
                        @foreach(LeadStatus::options() as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request('lead_status') == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-filter-workspace__field">
                    <label for="priority">Priority</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="">Any priority</option>
                        @foreach(LeadPriority::options() as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request('priority') == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                        <option value="high_urgent" @selected(request('priority') === 'high_urgent')>High &amp; urgent</option>
                    </select>
                </div>
                <div class="crm-filter-workspace__field">
                    <label for="assigned_to">Assignee</label>
                    <select name="assigned_to" id="assigned_to" class="form-select">
                        <option value="">Anyone</option>
                        <option value="me" @selected(request('assigned_to') === 'me' || (string) request('assigned_to') === (string) $currentAdminId)>Me</option>
                        <option value="unassigned" @selected(request('assigned_to') === 'unassigned')>Unassigned</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" @selected((string) request('assigned_to') === (string) $admin->id && request('assigned_to') !== 'me')>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-filter-workspace__field">
                    <label for="follow_up">Follow-up</label>
                    <select name="follow_up" id="follow_up" class="form-select">
                        <option value="">Any time</option>
                        <option value="today" @selected(request('follow_up') === 'today')>Due today</option>
                        <option value="overdue" @selected(request('follow_up') === 'overdue')>Overdue</option>
                    </select>
                </div>
                <div class="crm-filter-workspace__field">
                    <label for="source">Source</label>
                    <select name="source" id="source" class="form-select">
                        <option value="">Any source</option>
                        @foreach($sourceOptions ?? [] as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request('source') == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                </div>
                @if($forms->isNotEmpty())
                    <div class="crm-filter-workspace__field">
                        <label for="form_id">Form</label>
                        <select name="form_id" id="form_id" class="form-select">
                            <option value="">Any form</option>
                            @foreach($forms as $form)
                                <option value="{{ $form->id }}" @selected((string) request('form_id') === (string) $form->id)>{{ $form->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if(\App\Support\LeadCategorySchema::ready())
                    <div class="crm-filter-workspace__field">
                        <label for="lead_category_id">Category</label>
                        <select name="lead_category_id" id="lead_category_id" class="form-select">
                            <option value="">Any category</option>
                            @foreach($categoryFilterOptions ?? [] as $optValue => $optLabel)
                                <option value="{{ $optValue }}" @selected(request('lead_category_id') == $optValue)>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="crm-filter-workspace__field">
                    <label for="advertising_platform">Platform</label>
                    <select name="advertising_platform" id="advertising_platform" class="form-select">
                        <option value="">Any platform</option>
                        @foreach($platformOptions ?? [] as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request('advertising_platform') == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-filter-workspace__field">
                    <label for="campaign_name">Campaign</label>
                    <input type="text" name="campaign_name" id="campaign_name" class="form-control" placeholder="Campaign name" value="{{ request('campaign_name') }}">
                </div>
            </div>
            <div class="crm-filter-workspace__advanced-actions">
                <button type="submit" class="crm-filter-workspace__btn crm-filter-workspace__btn--primary">Apply</button>
                <a href="{{ route('admin.crm.leads.index', ['view' => $viewMode]) }}" class="crm-filter-workspace__btn crm-filter-workspace__btn--ghost">Reset</a>
            </div>
        </div>
    </form>
</div>
