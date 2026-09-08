@php
    use App\Support\LeadSourceOptions;

    $viewMode = $viewMode ?? 'board';
    $categories = $categories ?? collect();
    $segments = $segments ?? ['all' => ['total' => 0], 'uncategorized' => ['total' => 0], 'by_id' => []];
    $sourceCounts = $sourceCounts ?? [];
    $forms = $forms ?? collect();
    $formLeadCounts = $formLeadCounts ?? [];
    $currentAdminId = auth('admin')->id();
    $activeSource = request('source');
    $activeFormId = request('form_id');
    $activePlatform = request('advertising_platform');
    $activeCategory = request('lead_category_id');

    $filterUrl = fn (array $merge = [], array $except = []) => route('admin.crm.leads.index', array_merge(
        request()->except(array_merge(['page'], $except)),
        $merge,
        ['view' => $viewMode]
    ));

    $totalLeads = (int) ($segments['all']['total'] ?? array_sum($sourceCounts));

    $sourceChannels = [
        ['value' => '', 'label' => 'All leads', 'short' => 'All', 'icon' => 'solar:layers-minimalistic-linear'],
        ['value' => 'form_submission', 'label' => 'Form Center', 'short' => 'Forms', 'icon' => LeadSourceOptions::icon('form_submission')],
        ['value' => 'facebook_lead_ads', 'label' => 'Facebook', 'short' => 'Facebook', 'icon' => LeadSourceOptions::icon('facebook_lead_ads')],
        ['value' => 'tiktok_lead_ads', 'label' => 'TikTok', 'short' => 'TikTok', 'icon' => LeadSourceOptions::icon('tiktok_lead_ads')],
        ['value' => 'file_import', 'label' => 'Import', 'short' => 'Import', 'icon' => LeadSourceOptions::icon('file_import')],
        ['value' => 'manual', 'label' => 'Manual', 'short' => 'Manual', 'icon' => LeadSourceOptions::icon('manual')],
        ['value' => 'student_admission', 'label' => 'Admission', 'short' => 'Admission', 'icon' => LeadSourceOptions::icon('student_admission')],
    ];

    $visibleChannels = collect($sourceChannels)->filter(function ($channel) use ($sourceCounts, $totalLeads, $activeSource) {
        if ($channel['value'] === '') {
            return true;
        }

        $count = (int) ($sourceCounts[$channel['value']] ?? 0);

        return $count > 0 || $activeSource === $channel['value'];
    });

    $isAssignedToMe = request('assigned_to') === 'me' || (string) request('assigned_to') === (string) $currentAdminId;
    $isUnassigned = request('assigned_to') === 'unassigned';
    $isNewStatus = request('lead_status') === 'new';
    $isFollowUpToday = request('follow_up') === 'today';
    $isHighPriority = request('priority') === 'high_urgent';

    $shortcuts = [
        ['key' => 'assigned_me', 'label' => 'My leads', 'icon' => 'solar:user-check-linear', 'active' => $isAssignedToMe, 'url' => $isAssignedToMe ? $filterUrl([], ['assigned_to']) : $filterUrl(['assigned_to' => 'me'], ['assigned_to'])],
        ['key' => 'unassigned', 'label' => 'Unassigned', 'icon' => 'solar:user-cross-linear', 'active' => $isUnassigned, 'url' => $isUnassigned ? $filterUrl([], ['assigned_to']) : $filterUrl(['assigned_to' => 'unassigned'], ['assigned_to'])],
        ['key' => 'new', 'label' => 'New', 'icon' => 'solar:star-linear', 'active' => $isNewStatus, 'url' => $isNewStatus ? $filterUrl([], ['lead_status']) : $filterUrl(['lead_status' => 'new'], ['lead_status'])],
        ['key' => 'follow_up', 'label' => 'Due today', 'icon' => 'solar:calendar-linear', 'active' => $isFollowUpToday, 'url' => $isFollowUpToday ? $filterUrl([], ['follow_up']) : $filterUrl(['follow_up' => 'today'], ['follow_up'])],
        ['key' => 'high_priority', 'label' => 'High priority', 'icon' => 'solar:flag-linear', 'active' => $isHighPriority, 'url' => $isHighPriority ? $filterUrl([], ['priority']) : $filterUrl(['priority' => 'high_urgent'], ['priority'])],
    ];

    $showFormSubfilters = $activeSource === 'form_submission' || request()->filled('form_id');
    $showImportSubfilters = $activeSource === 'file_import' || request()->filled('advertising_platform');

    $expandMore = request()->filled('campaign_name');

    $activeFilterKeys = ['search', 'source', 'form_id', 'advertising_platform', 'campaign_name', 'lead_status', 'priority', 'assigned_to', 'follow_up', 'lead_category_id'];
    $activeFilterCount = collect(request()->only($activeFilterKeys))
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->count();

    $activeFilterChips = [];
    if (request()->filled('search')) {
        $activeFilterChips[] = ['label' => 'Search', 'value' => request('search'), 'url' => $filterUrl([], ['search'])];
    }
    if (request()->filled('source')) {
        $activeFilterChips[] = ['label' => 'Source', 'value' => LeadSourceOptions::label(request('source')), 'url' => $filterUrl([], ['source'])];
    }
    if (request()->filled('form_id')) {
        $formName = $forms->firstWhere('id', (int) request('form_id'))?->name ?? 'Form';
        $activeFilterChips[] = ['label' => 'Form', 'value' => $formName, 'url' => $filterUrl([], ['form_id'])];
    }
    if (request()->filled('advertising_platform')) {
        $activeFilterChips[] = ['label' => 'Platform', 'value' => ($platformOptions ?? [])[request('advertising_platform')] ?? request('advertising_platform'), 'url' => $filterUrl([], ['advertising_platform'])];
    }
    if (request()->filled('campaign_name')) {
        $activeFilterChips[] = ['label' => 'Campaign', 'value' => request('campaign_name'), 'url' => $filterUrl([], ['campaign_name'])];
    }
    if (request()->filled('lead_status')) {
        $activeFilterChips[] = ['label' => 'Status', 'value' => \App\Enums\LeadStatus::tryFrom(request('lead_status'))?->label() ?? request('lead_status'), 'url' => $filterUrl([], ['lead_status'])];
    }
    if (request()->filled('priority')) {
        $priorityLabel = request('priority') === 'high_urgent'
            ? 'High & urgent'
            : (\App\Enums\LeadPriority::tryFrom(request('priority'))?->label() ?? request('priority'));
        $activeFilterChips[] = ['label' => 'Priority', 'value' => $priorityLabel, 'url' => $filterUrl([], ['priority'])];
    }
    if (request()->filled('assigned_to')) {
        $assigneeLabel = match (true) {
            request('assigned_to') === 'me' => 'Me',
            request('assigned_to') === 'unassigned' => 'Unassigned',
            default => $admins->firstWhere('id', (int) request('assigned_to'))?->name ?? 'Assignee',
        };
        $activeFilterChips[] = ['label' => 'Assignee', 'value' => $assigneeLabel, 'url' => $filterUrl([], ['assigned_to'])];
    }
    if (request()->filled('follow_up')) {
        $followLabel = request('follow_up') === 'today' ? 'Due today' : (request('follow_up') === 'overdue' ? 'Overdue' : request('follow_up'));
        $activeFilterChips[] = ['label' => 'Follow-up', 'value' => $followLabel, 'url' => $filterUrl([], ['follow_up'])];
    }
    if (request()->filled('lead_category_id')) {
        $categoryLabel = request('lead_category_id') === 'uncategorized'
            ? 'Uncategorized'
            : ($categories->firstWhere('id', (int) request('lead_category_id'))?->name ?? 'Category');
        $activeFilterChips[] = ['label' => 'Category', 'value' => $categoryLabel, 'url' => $filterUrl([], ['lead_category_id'])];
    }

    $smartSuggestions = $smartSearchSuggestions ?? [];
@endphp
<div class="crm-filter-workspace crm-lead-finder" data-crm-filter-workspace>
    {{-- Smart search: natural language + keyword lookup --}}
    <div class="crm-smart-search crm-lead-finder__search" data-crm-smart-search>
        <label class="visually-hidden" for="crm-leads-smart-search">Find leads</label>
        <div class="crm-smart-search__shell">
            <span class="crm-smart-search__badge">
                <iconify-icon icon="solar:magic-stick-3-linear" aria-hidden="true"></iconify-icon>
                Find
            </span>
            <input type="search"
                   id="crm-leads-smart-search"
                   class="crm-smart-search__input"
                   value="{{ request('search') }}"
                   placeholder="Name, email, phone — or try &quot;facebook unassigned&quot;, &quot;form submissions&quot;, &quot;teachers due today&quot;"
                   autocomplete="off"
                   aria-label="Find leads"
                   aria-controls="crm-leads-smart-panel"
                   aria-expanded="false"
                   data-crm-smart-search-input>
            <button type="button" class="crm-smart-search__go" data-crm-smart-search-go aria-label="Search leads">
                <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
            </button>
        </div>
        <div id="crm-leads-smart-panel" class="crm-smart-search__panel" data-crm-smart-search-panel hidden>
            <div class="crm-smart-search__interpretation" data-crm-smart-search-label hidden>
                <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                <span data-crm-smart-search-label-text></span>
            </div>
            @if(!empty($smartSuggestions))
                <span class="crm-smart-search__section-label">Try these</span>
                <div class="crm-smart-search__suggestions">
                    @foreach(array_slice($smartSuggestions, 0, 6) as $suggestion)
                        <button type="button"
                                class="crm-smart-search__suggestion"
                                data-crm-smart-suggestion
                                data-query="{{ $suggestion['query'] }}">
                            <strong>{{ $suggestion['label'] }}</strong>
                            <span>{{ $suggestion['description'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Source channels: where did this lead come from? --}}
    <div class="crm-lead-finder__section">
        <div class="crm-lead-finder__section-head">
            <span class="crm-lead-finder__section-label">Where leads come from</span>
            <span class="crm-lead-finder__section-hint">Pick a channel first — then narrow down</span>
        </div>
        <div class="crm-lead-finder__channels" role="tablist" aria-label="Lead source channels">
            @foreach($visibleChannels as $channel)
                @php
                    $isActive = ($channel['value'] === '' && ! request()->filled('source'))
                        || ($channel['value'] !== '' && $activeSource === $channel['value']);
                    $count = $channel['value'] === ''
                        ? $totalLeads
                        : (int) ($sourceCounts[$channel['value']] ?? 0);
                    $channelUrl = $channel['value'] === ''
                        ? $filterUrl([], ['source', 'form_id', 'advertising_platform'])
                        : $filterUrl(['source' => $channel['value']], ['source', 'form_id', 'advertising_platform']);
                @endphp
                <a href="{{ $channelUrl }}"
                   @class(['crm-channel-tab', 'crm-channel-tab--'.$channel['value'], 'is-active' => $isActive])
                   role="tab"
                   @if($isActive) aria-selected="true" @else aria-selected="false" @endif
                   title="{{ $channel['label'] }}">
                    <span class="crm-channel-tab__icon" aria-hidden="true">
                        <iconify-icon icon="{{ $channel['icon'] }}"></iconify-icon>
                    </span>
                    <span class="crm-channel-tab__copy">
                        <span class="crm-channel-tab__label">{{ $channel['short'] }}</span>
                        <strong class="crm-channel-tab__count">{{ number_format($count) }}</strong>
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Sub-filters: which form / import platform --}}
    @if($showFormSubfilters && $forms->isNotEmpty())
        <div class="crm-lead-finder__subfilters">
            <span class="crm-lead-finder__subfilters-label">Which form</span>
            <div class="crm-lead-finder__subfilters-chips">
                <a href="{{ $filterUrl(['source' => 'form_submission'], ['form_id']) }}"
                   @class(['crm-filter-chip', 'crm-filter-chip--form', 'is-active' => ! request()->filled('form_id')])>
                    All forms
                </a>
                @foreach($forms as $form)
                    @php
                        $formCount = (int) ($formLeadCounts[$form->id] ?? 0);
                        $isFormActive = (string) $activeFormId === (string) $form->id;
                    @endphp
                    @if($formCount > 0 || $isFormActive)
                        <a href="{{ $filterUrl(['source' => 'form_submission', 'form_id' => $form->id], ['form_id']) }}"
                           @class(['crm-filter-chip', 'crm-filter-chip--form', 'is-active' => $isFormActive])>
                            {{ $form->name }}
                            <span class="crm-filter-chip__count">{{ $formCount }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if($showImportSubfilters)
        <div class="crm-lead-finder__subfilters">
            <span class="crm-lead-finder__subfilters-label">Import channel</span>
            <div class="crm-lead-finder__subfilters-chips">
                <a href="{{ $filterUrl(['source' => 'file_import'], ['advertising_platform']) }}"
                   @class(['crm-filter-chip', 'is-active' => ! request()->filled('advertising_platform')])>
                    All imports
                </a>
                @foreach($platformOptions ?? [] as $platformValue => $platformLabel)
                    @php $isPlatformActive = $activePlatform === $platformValue; @endphp
                    <a href="{{ $filterUrl(['source' => 'file_import', 'advertising_platform' => $platformValue], ['advertising_platform']) }}"
                       @class(['crm-filter-chip', 'is-active' => $isPlatformActive])>
                        {{ $platformLabel }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Refine: always-visible dropdowns --}}
    <form method="GET" action="{{ route('admin.crm.leads.index') }}" id="crm-leads-filter-form" class="crm-lead-finder__refine-form">
        <input type="hidden" name="view" value="{{ $viewMode }}" data-crm-view-input>
        @if(($viewMode ?? 'board') === 'list' && request('per_page'))
            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
        @endif
        @foreach(['search', 'source', 'form_id', 'advertising_platform'] as $preserveKey)
            @if(request()->filled($preserveKey))
                <input type="hidden" name="{{ $preserveKey }}" value="{{ request($preserveKey) }}">
            @endif
        @endforeach

        <div class="crm-lead-finder__section">
            <span class="crm-lead-finder__section-label">Narrow results</span>
            <div class="crm-lead-finder__refine">
                <div class="crm-lead-finder__refine-field">
                    <label for="lead_status">Status</label>
                    <select name="lead_status" id="lead_status" class="form-select" data-crm-filter-auto-submit>
                        <option value="">Any status</option>
                        @foreach(\App\Enums\LeadStatus::options() as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request('lead_status') == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                </div>
                @if(\App\Support\LeadCategorySchema::ready())
                    <div class="crm-lead-finder__refine-field">
                        <label for="lead_category_id">Category</label>
                        <select name="lead_category_id" id="lead_category_id" class="form-select" data-crm-filter-auto-submit>
                            <option value="">Any category</option>
                            @foreach($categoryFilterOptions ?? [] as $optValue => $optLabel)
                                <option value="{{ $optValue }}" @selected(request('lead_category_id') == $optValue)>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="crm-lead-finder__refine-field">
                    <label for="assigned_to">Assignee</label>
                    <select name="assigned_to" id="assigned_to" class="form-select" data-crm-filter-auto-submit>
                        <option value="">Anyone</option>
                        <option value="me" @selected(request('assigned_to') === 'me' || (string) request('assigned_to') === (string) $currentAdminId)>Me</option>
                        <option value="unassigned" @selected(request('assigned_to') === 'unassigned')>Unassigned</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" @selected((string) request('assigned_to') === (string) $admin->id && request('assigned_to') !== 'me')>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-lead-finder__refine-field">
                    <label for="follow_up">Follow-up</label>
                    <select name="follow_up" id="follow_up" class="form-select" data-crm-filter-auto-submit>
                        <option value="">Any time</option>
                        <option value="today" @selected(request('follow_up') === 'today')>Due today</option>
                        <option value="overdue" @selected(request('follow_up') === 'overdue')>Overdue</option>
                    </select>
                </div>
                <div class="crm-lead-finder__refine-field">
                    <label for="priority">Priority</label>
                    <select name="priority" id="priority" class="form-select" data-crm-filter-auto-submit>
                        <option value="">Any priority</option>
                        @foreach(\App\Enums\LeadPriority::options() as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request('priority') == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                        <option value="high_urgent" @selected(request('priority') === 'high_urgent')>High &amp; urgent</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="crm-lead-finder__more-row">
            <button type="button"
                    class="crm-lead-finder__more-toggle"
                    data-crm-toggle-advanced-filters
                    aria-expanded="{{ $expandMore ? 'true' : 'false' }}"
                    aria-controls="crm-leads-more-filters">
                <iconify-icon icon="solar:settings-linear" aria-hidden="true"></iconify-icon>
                Campaign filter
                @if(request()->filled('campaign_name'))
                    <span class="crm-filter-workspace__filter-count">1</span>
                @endif
                <iconify-icon icon="solar:alt-arrow-down-linear" class="crm-filter-workspace__chevron" aria-hidden="true"></iconify-icon>
            </button>
        </div>

        <div id="crm-leads-more-filters"
             class="crm-lead-finder__more-panel"
             data-crm-advanced-filters
             @if(! $expandMore) hidden @endif>
            <div class="crm-lead-finder__refine-field crm-lead-finder__refine-field--wide">
                <label for="campaign_name">Campaign name contains</label>
                <div class="crm-lead-finder__campaign-row">
                    <input type="text"
                           name="campaign_name"
                           id="campaign_name"
                           class="form-control"
                           placeholder="e.g. Year 7 Open Day"
                           value="{{ request('campaign_name') }}">
                    <button type="submit" class="crm-filter-workspace__btn crm-filter-workspace__btn--primary">Apply</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Shortcuts: one-click common workflows --}}
    <div class="crm-lead-finder__shortcuts">
        <span class="crm-lead-finder__section-label">Shortcuts</span>
        <div class="crm-lead-finder__shortcuts-chips">
            @foreach($shortcuts as $chip)
                <a href="{{ $chip['url'] }}"
                   @class(['crm-filter-chip', 'crm-filter-chip--'.$chip['key'], 'is-active' => $chip['active']])
                   @if($chip['active']) aria-current="true" @endif>
                    <iconify-icon icon="{{ $chip['icon'] }}" aria-hidden="true"></iconify-icon>
                    {{ $chip['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Active filters: what's applied right now --}}
    @if(!empty($activeFilterChips))
        <div class="crm-lead-finder__active" aria-label="Active filters">
            <span class="crm-lead-finder__active-label">Showing</span>
            <div class="crm-lead-finder__active-list">
                @foreach($activeFilterChips as $chip)
                    <a href="{{ $chip['url'] }}" class="crm-active-filter" title="Remove {{ $chip['label'] }} filter">
                        <span class="crm-active-filter__label">{{ $chip['label'] }}</span>
                        <span class="crm-active-filter__value">{{ Str::limit($chip['value'], 28) }}</span>
                        <iconify-icon icon="solar:close-circle-linear" aria-hidden="true"></iconify-icon>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('admin.crm.leads.index', ['view' => $viewMode]) }}" class="crm-lead-finder__clear">Clear all</a>
        </div>
    @endif
</div>
