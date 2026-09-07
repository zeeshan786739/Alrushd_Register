@php
    $viewMode = $viewMode ?? 'board';
    $categories = $categories ?? collect();
    $forms = $forms ?? collect();
    $formLeadCounts = $formLeadCounts ?? [];
    $segments = $segments ?? ['all' => ['total' => 0], 'uncategorized' => ['total' => 0], 'by_id' => []];
    $currentAdminId = auth('admin')->id();
    $activeCategory = request('lead_category_id');
    $activeFormId = request('form_id');

    $isAssignedToMe = request('assigned_to') === 'me' || (string) request('assigned_to') === (string) $currentAdminId;
    $isUnassigned = request('assigned_to') === 'unassigned';
    $isNewStatus = request('lead_status') === 'new';
    $isFollowUpToday = request('follow_up') === 'today';
    $isFollowUpOverdue = request('follow_up') === 'overdue';
    $isHighPriority = request('priority') === 'high_urgent';
    $isUrgentPriority = request('priority') === 'urgent';
    $isMediumPriority = request('priority') === 'medium';
    $isFormSource = request('source') === 'form_submission';

    $baseQuery = request()->except('page');
    $filterUrl = fn (array $merge = [], array $except = []) => route('admin.crm.leads.index', array_merge(
        request()->except(array_merge(['page'], $except)),
        $merge,
        ['view' => $viewMode]
    ));

    $quickFilters = [
        [
            'key' => 'assigned_me',
            'label' => 'Assigned to me',
            'icon' => 'solar:user-check-linear',
            'active' => $isAssignedToMe,
            'url' => $isAssignedToMe
                ? $filterUrl([], ['assigned_to'])
                : $filterUrl(['assigned_to' => 'me'], ['assigned_to']),
        ],
        [
            'key' => 'unassigned',
            'label' => 'Unassigned',
            'icon' => 'solar:user-cross-linear',
            'active' => $isUnassigned,
            'url' => $isUnassigned
                ? $filterUrl([], ['assigned_to'])
                : $filterUrl(['assigned_to' => 'unassigned'], ['assigned_to']),
        ],
        [
            'key' => 'new',
            'label' => 'New',
            'icon' => 'solar:star-linear',
            'active' => $isNewStatus,
            'url' => $isNewStatus
                ? $filterUrl([], ['lead_status'])
                : $filterUrl(['lead_status' => 'new'], ['lead_status']),
        ],
        [
            'key' => 'follow_up',
            'label' => 'Follow-up today',
            'icon' => 'solar:calendar-linear',
            'active' => $isFollowUpToday,
            'url' => $isFollowUpToday
                ? $filterUrl([], ['follow_up'])
                : $filterUrl(['follow_up' => 'today'], ['follow_up']),
        ],
        [
            'key' => 'follow_up_overdue',
            'label' => 'Overdue',
            'icon' => 'solar:alarm-linear',
            'active' => $isFollowUpOverdue,
            'url' => $isFollowUpOverdue
                ? $filterUrl([], ['follow_up'])
                : $filterUrl(['follow_up' => 'overdue'], ['follow_up']),
        ],
        [
            'key' => 'high_priority',
            'label' => 'High priority',
            'icon' => 'solar:flag-linear',
            'active' => $isHighPriority,
            'url' => $isHighPriority
                ? $filterUrl([], ['priority'])
                : $filterUrl(['priority' => 'high_urgent'], ['priority']),
        ],
        [
            'key' => 'urgent',
            'label' => 'Urgent',
            'icon' => 'solar:danger-linear',
            'active' => $isUrgentPriority,
            'url' => $isUrgentPriority
                ? $filterUrl([], ['priority'])
                : $filterUrl(['priority' => 'urgent'], ['priority']),
        ],
        [
            'key' => 'medium',
            'label' => 'Medium',
            'icon' => 'solar:flag-2-linear',
            'active' => $isMediumPriority,
            'url' => $isMediumPriority
                ? $filterUrl([], ['priority'])
                : $filterUrl(['priority' => 'medium'], ['priority']),
        ],
        [
            'key' => 'form_source',
            'label' => 'From forms',
            'icon' => 'solar:inbox-in-linear',
            'active' => $isFormSource && ! request()->filled('form_id'),
            'url' => ($isFormSource && ! request()->filled('form_id'))
                ? $filterUrl([], ['source', 'form_id'])
                : $filterUrl(['source' => 'form_submission'], ['source', 'form_id']),
        ],
    ];

    $advancedFilterKeys = ['source', 'form_id', 'advertising_platform', 'campaign_name', 'lead_status', 'priority', 'assigned_to', 'lead_category_id'];
    $hasAdvancedValues = collect(request()->only($advancedFilterKeys))
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->isNotEmpty();
    $onlyQuickFilters = ! $hasAdvancedValues || (
        (! request()->filled('source') || request('source') === 'form_submission')
        && ! request()->filled('advertising_platform')
        && ! request()->filled('campaign_name')
        && ! request()->filled('lead_category_id')
        && ! request()->filled('form_id')
        && (
            ! request()->filled('lead_status') || request('lead_status') === 'new'
        )
        && (
            ! request()->filled('priority')
            || in_array(request('priority'), ['high_urgent', 'urgent', 'medium'], true)
        )
        && (
            ! request()->filled('assigned_to')
            || in_array(request('assigned_to'), ['me', 'unassigned'], true)
            || (string) request('assigned_to') === (string) $currentAdminId
        )
        && (
            ! request()->filled('follow_up')
            || in_array(request('follow_up'), ['today', 'overdue'], true)
        )
    );
    $expandAdvanced = $hasAdvancedValues && ! $onlyQuickFilters;

    $activeFilterCount = collect(request()->only(array_merge(['search', 'follow_up'], $advancedFilterKeys)))
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->count();
@endphp
<div class="crm-filter-workspace" data-crm-filter-workspace>
    <form method="GET" action="{{ route('admin.crm.leads.index') }}" id="crm-leads-filter-form" class="crm-filter-workspace__form">
        <input type="hidden" name="view" value="{{ $viewMode }}" data-crm-view-input>
        @if(($viewMode ?? 'board') === 'list' && request('per_page'))
            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
        @endif

        <div class="crm-filter-workspace__search-row">
            <div class="crm-filter-workspace__search">
                <iconify-icon icon="solar:magnifer-linear" aria-hidden="true"></iconify-icon>
                <input type="search"
                       name="search"
                       id="crm-leads-search"
                       class="crm-filter-workspace__search-input"
                       placeholder="Search name, email, or phone"
                       value="{{ request('search') }}"
                       autocomplete="off">
            </div>
            <div class="crm-filter-workspace__search-actions">
                <button type="submit" class="crm-filter-workspace__btn crm-filter-workspace__btn--primary">Search</button>
                @if($activeFilterCount > 0 || request()->filled('search'))
                    <a href="{{ route('admin.crm.leads.index', ['view' => $viewMode]) }}" class="crm-filter-workspace__btn crm-filter-workspace__btn--ghost">Clear all</a>
                @endif
            </div>
        </div>

        <div class="crm-filter-workspace__quick-row">
            <span class="crm-filter-workspace__quick-label">Quick filters</span>
            <div class="crm-filter-workspace__chips" role="list">
                @foreach($quickFilters as $chip)
                    <a href="{{ $chip['url'] }}"
                       @class(['crm-filter-chip', 'crm-filter-chip--'.$chip['key'], 'is-active' => $chip['active']])
                       role="listitem"
                       @if($chip['active']) aria-current="true" @endif>
                        <iconify-icon icon="{{ $chip['icon'] }}" aria-hidden="true"></iconify-icon>
                        {{ $chip['label'] }}
                    </a>
                @endforeach

                @if(\App\Support\LeadCategorySchema::ready())
                    <span class="crm-filter-workspace__chip-divider" aria-hidden="true"></span>
                    <a href="{{ $filterUrl([], ['lead_category_id']) }}"
                       @class(['crm-filter-chip', 'crm-filter-chip--segment', 'is-active' => $activeCategory === null || $activeCategory === ''])
                       aria-current="{{ ($activeCategory === null || $activeCategory === '') ? 'true' : 'false' }}">
                        All
                        <span class="crm-filter-chip__count">{{ (int) ($segments['all']['total'] ?? 0) }}</span>
                    </a>
                    @foreach($categories as $category)
                        @php
                            $counts = $segments['by_id'][$category->id] ?? ['total' => 0];
                            $isActive = (string) $activeCategory === (string) $category->id;
                        @endphp
                        <a href="{{ $filterUrl(['lead_category_id' => $category->id], ['lead_category_id']) }}"
                           @class(['crm-filter-chip', 'crm-filter-chip--segment', 'is-active' => $isActive])
                           @if($isActive) aria-current="true" @endif>
                            {{ $category->name }}
                            <span class="crm-filter-chip__count">{{ (int) $counts['total'] }}</span>
                        </a>
                    @endforeach
                    @php $uncategorizedTotal = (int) ($segments['uncategorized']['total'] ?? 0); @endphp
                    @if($uncategorizedTotal > 0 || $activeCategory === 'uncategorized')
                        <a href="{{ $filterUrl(['lead_category_id' => 'uncategorized'], ['lead_category_id']) }}"
                           @class(['crm-filter-chip', 'crm-filter-chip--segment', 'is-active' => $activeCategory === 'uncategorized'])
                           @if($activeCategory === 'uncategorized') aria-current="true" @endif>
                            Uncategorized
                            <span class="crm-filter-chip__count">{{ $uncategorizedTotal }}</span>
                        </a>
                    @endif
                @endif

                @if($forms->isNotEmpty())
                    <span class="crm-filter-workspace__chip-divider" aria-hidden="true"></span>
                    @foreach($forms as $form)
                        @php
                            $counts = $formLeadCounts[$form->id] ?? ['total' => 0, 'new' => 0];
                            $isActive = (string) $activeFormId === (string) $form->id;
                        @endphp
                        <a href="{{ $filterUrl(['form_id' => $form->id, 'source' => 'form_submission'], ['form_id', 'source']) }}"
                           @class(['crm-filter-chip', 'crm-filter-chip--form', 'is-active' => $isActive])
                           title="{{ $form->name }} leads"
                           @if($isActive) aria-current="true" @endif>
                            <iconify-icon icon="solar:document-text-linear" aria-hidden="true"></iconify-icon>
                            {{ Str::limit($form->name, 22) }}
                            <span class="crm-filter-chip__count">{{ (int) $counts['total'] }}</span>
                        </a>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="crm-filter-workspace__advanced-toggle-row">
            <button type="button"
                    class="crm-filter-workspace__advanced-toggle"
                    data-crm-toggle-advanced-filters
                    aria-expanded="{{ $expandAdvanced ? 'true' : 'false' }}"
                    aria-controls="crm-leads-advanced-filters">
                <iconify-icon icon="solar:filter-linear" aria-hidden="true"></iconify-icon>
                More filters
                @if($activeFilterCount > 0)
                    <span class="crm-filter-workspace__filter-count">{{ $activeFilterCount }}</span>
                @endif
                <iconify-icon icon="solar:alt-arrow-down-linear" class="crm-filter-workspace__chevron" aria-hidden="true"></iconify-icon>
            </button>
        </div>

        <div id="crm-leads-advanced-filters"
             class="crm-filter-workspace__advanced"
             data-crm-advanced-filters
             @if(! $expandAdvanced) hidden @endif>
            <div class="crm-filter-workspace__advanced-grid">
                @if(\App\Support\LeadCategorySchema::ready())
                    <div class="crm-filter-workspace__field">
                        <label for="lead_category_id">Category</label>
                        <select name="lead_category_id" id="lead_category_id" class="form-select">
                            <option value="">All categories</option>
                            @foreach($categoryFilterOptions ?? [] as $optValue => $optLabel)
                                <option value="{{ $optValue }}" @selected(request('lead_category_id') == $optValue)>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="crm-filter-workspace__field">
                    <label for="source">Source</label>
                    <select name="source" id="source" class="form-select">
                        <option value="">All sources</option>
                        @foreach($sourceOptions ?? [] as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request('source') == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                </div>
                @if($forms->isNotEmpty())
                    <div class="crm-filter-workspace__field">
                        <label for="form_id">Form</label>
                        <select name="form_id" id="form_id" class="form-select">
                            <option value="">All forms</option>
                            @foreach($forms as $form)
                                <option value="{{ $form->id }}" @selected((string) request('form_id') === (string) $form->id)>{{ $form->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="crm-filter-workspace__field">
                    <label for="advertising_platform">Platform</label>
                    <select name="advertising_platform" id="advertising_platform" class="form-select">
                        <option value="">All platforms</option>
                        @foreach($platformOptions ?? [] as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request('advertising_platform') == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-filter-workspace__field">
                    <label for="campaign_name">Campaign</label>
                    <input type="text" name="campaign_name" id="campaign_name" class="form-control" placeholder="Campaign name" value="{{ request('campaign_name') }}">
                </div>
                <div class="crm-filter-workspace__field">
                    <label for="lead_status">Status</label>
                    <select name="lead_status" id="lead_status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach(\App\Enums\LeadStatus::options() as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request('lead_status') == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-filter-workspace__field">
                    <label for="priority">Priority</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="">All priorities</option>
                        @foreach(\App\Enums\LeadPriority::options() as $optValue => $optLabel)
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
            </div>
            <div class="crm-filter-workspace__advanced-actions">
                <button type="submit" class="crm-filter-workspace__btn crm-filter-workspace__btn--primary">Apply filters</button>
                <a href="{{ route('admin.crm.leads.index', ['view' => $viewMode]) }}" class="crm-filter-workspace__btn crm-filter-workspace__btn--ghost">Reset</a>
            </div>
        </div>
    </form>
</div>
