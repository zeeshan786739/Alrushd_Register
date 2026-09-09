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

    $filterUrl = fn (array $merge = [], array $except = []) => route('admin.crm.leads.index', array_merge(
        request()->except(array_merge(['page'], $except)),
        $merge,
        ['view' => $viewMode]
    ));

    $totalLeads = (int) ($segments['all']['total'] ?? array_sum($sourceCounts));

    $primaryChannels = [
        ['value' => '', 'label' => 'All leads', 'hint' => 'Every lead in your pipeline', 'icon' => 'solar:layers-minimalistic-linear', 'tone' => 'all'],
        ['value' => 'facebook_lead_ads', 'label' => 'Facebook', 'hint' => 'Meta Lead Ads', 'icon' => LeadSourceOptions::icon('facebook_lead_ads'), 'tone' => 'facebook'],
        ['value' => 'tiktok_lead_ads', 'label' => 'TikTok', 'hint' => 'TikTok Lead Ads', 'icon' => LeadSourceOptions::icon('tiktok_lead_ads'), 'tone' => 'tiktok'],
        ['value' => 'form_submission', 'label' => 'Forms', 'hint' => 'Form Center submissions', 'icon' => LeadSourceOptions::icon('form_submission'), 'tone' => 'forms'],
        ['value' => 'file_import', 'label' => 'Import', 'hint' => 'Spreadsheet imports', 'icon' => LeadSourceOptions::icon('file_import'), 'tone' => 'import'],
        ['value' => 'manual', 'label' => 'Manual', 'hint' => 'Added by your team', 'icon' => LeadSourceOptions::icon('manual'), 'tone' => 'manual'],
    ];

    $secondaryChannels = collect([
        ['value' => 'student_admission', 'label' => 'Admission', 'hint' => 'Student admission sync', 'icon' => LeadSourceOptions::icon('student_admission'), 'tone' => 'admission'],
    ])->filter(fn ($channel) => (int) ($sourceCounts[$channel['value']] ?? 0) > 0 || $activeSource === $channel['value']);

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

    $showFormPicker = $activeSource === 'form_submission' || request()->filled('form_id');
    $showImportPicker = $activeSource === 'file_import' || request()->filled('advertising_platform');
    $formLeadsTotal = (int) ($sourceCounts['form_submission'] ?? 0);
    $expandMore = request()->filled('campaign_name') || request()->filled('lead_status') || request()->filled('lead_category_id')
        || request()->filled('assigned_to') || request()->filled('follow_up') || request()->filled('priority');

    $activeFilterChips = [];
    if (request()->filled('search')) {
        $activeFilterChips[] = ['label' => 'Search', 'value' => request('search'), 'url' => $filterUrl([], ['search'])];
    }
    if (request()->filled('form_id')) {
        $formName = $forms->firstWhere('id', (int) request('form_id'))?->name ?? 'Form';
        $activeFilterChips[] = ['label' => 'Form', 'value' => $formName, 'url' => $filterUrl([], ['form_id'])];
    }
    if (request()->filled('advertising_platform')) {
        $activeFilterChips[] = ['label' => 'Channel', 'value' => ($platformOptions ?? [])[request('advertising_platform')] ?? request('advertising_platform'), 'url' => $filterUrl([], ['advertising_platform'])];
    }
    if (request()->filled('campaign_name')) {
        $activeFilterChips[] = ['label' => 'Campaign', 'value' => request('campaign_name'), 'url' => $filterUrl([], ['campaign_name'])];
    }
    if (request()->filled('lead_status') && ! $isNewStatus) {
        $activeFilterChips[] = ['label' => 'Status', 'value' => \App\Enums\LeadStatus::tryFrom(request('lead_status'))?->label() ?? request('lead_status'), 'url' => $filterUrl([], ['lead_status'])];
    }
    if (request()->filled('priority') && ! $isHighPriority) {
        $priorityLabel = request('priority') === 'high_urgent'
            ? 'High & urgent'
            : (\App\Enums\LeadPriority::tryFrom(request('priority'))?->label() ?? request('priority'));
        $activeFilterChips[] = ['label' => 'Priority', 'value' => $priorityLabel, 'url' => $filterUrl([], ['priority'])];
    }
    if (request()->filled('assigned_to') && ! $isAssignedToMe && ! $isUnassigned) {
        $assigneeLabel = $admins->firstWhere('id', (int) request('assigned_to'))?->name ?? 'Assignee';
        $activeFilterChips[] = ['label' => 'Assignee', 'value' => $assigneeLabel, 'url' => $filterUrl([], ['assigned_to'])];
    }
    if (request()->filled('follow_up') && ! $isFollowUpToday) {
        $followLabel = request('follow_up') === 'overdue' ? 'Overdue' : request('follow_up');
        $activeFilterChips[] = ['label' => 'Follow-up', 'value' => $followLabel, 'url' => $filterUrl([], ['follow_up'])];
    }
    if (request()->filled('lead_category_id')) {
        $categoryLabel = request('lead_category_id') === 'uncategorized'
            ? 'Uncategorized'
            : ($categories->firstWhere('id', (int) request('lead_category_id'))?->name ?? 'Category');
        $activeFilterChips[] = ['label' => 'Category', 'value' => $categoryLabel, 'url' => $filterUrl([], ['lead_category_id'])];
    }

    $smartSuggestions = $smartSearchSuggestions ?? [];
    $hasExtraFilters = ! empty($activeFilterChips);
@endphp
<div class="crm-filter-workspace crm-lead-finder" data-crm-filter-workspace>
    <div class="crm-lead-finder__lookup">
        <div class="crm-ai-search crm-smart-search" data-crm-smart-search>
            <div class="crm-ai-search__head">
                <span class="crm-ai-search__badge">
                    <iconify-icon icon="solar:magic-stick-3-linear" aria-hidden="true"></iconify-icon>
                    AI Find
                </span>
                <span class="crm-ai-search__hint">Ask in plain English — or type a name, email, or phone number</span>
            </div>
            <label class="visually-hidden" for="crm-leads-smart-search">AI lead search</label>
            <div class="crm-smart-search__shell crm-ai-search__shell">
                <span class="crm-ai-search__icon" aria-hidden="true">
                    <iconify-icon icon="solar:chat-round-dots-linear"></iconify-icon>
                </span>
                <input type="search"
                       id="crm-leads-smart-search"
                       class="crm-smart-search__input crm-ai-search__input"
                       value="{{ request('search') }}"
                       placeholder="Try: facebook unassigned · import teachers · form submissions · due today"
                       autocomplete="off"
                       aria-label="AI lead search"
                       aria-controls="crm-leads-smart-panel"
                       aria-expanded="false"
                       data-crm-smart-search-input>
                <button type="button" class="crm-smart-search__go crm-ai-search__go" data-crm-smart-search-go aria-label="Run AI search">
                    <iconify-icon icon="solar:magic-stick-3-linear"></iconify-icon>
                    <span>Find</span>
                </button>
            </div>
            <div id="crm-leads-smart-panel" class="crm-smart-search__panel crm-ai-search__panel" data-crm-smart-search-panel hidden>
                <div class="crm-ai-search__panel-kicker">
                    <iconify-icon icon="solar:stars-minimalistic-linear" aria-hidden="true"></iconify-icon>
                    AI understands sources, status, assignee, category, and follow-ups
                </div>
                <div class="crm-smart-search__interpretation crm-ai-search__interpretation" data-crm-smart-search-label hidden>
                    <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                    <span data-crm-smart-search-label-text></span>
                </div>
                @if(!empty($smartSuggestions))
                    <span class="crm-smart-search__section-label">Try asking</span>
                    <div class="crm-smart-search__suggestions crm-ai-search__suggestions">
                        @foreach(array_slice($smartSuggestions, 0, 6) as $suggestion)
                            <button type="button"
                                    class="crm-smart-search__suggestion crm-ai-search__suggestion"
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

        <div class="crm-lead-finder__quick">
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

    <div class="crm-lead-finder__sources">
        <div class="crm-lead-finder__sources-head">
            <h3 class="crm-lead-finder__sources-title">Lead source</h3>
            <p class="crm-lead-finder__sources-sub">Choose where leads came from — Facebook, TikTok, forms, or imports</p>
        </div>
        <div class="crm-lead-finder__source-grid" role="tablist" aria-label="Lead sources">
            @foreach($primaryChannels as $channel)
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
                   @class([
                       'crm-source-card',
                       'crm-source-card--'.$channel['tone'],
                       'is-active' => $isActive,
                       'is-empty' => $channel['value'] !== '' && $count === 0,
                   ])
                   role="tab"
                   @if($isActive) aria-selected="true" @else aria-selected="false" @endif
                   title="{{ $channel['hint'] }}">
                    <span class="crm-source-card__icon" aria-hidden="true">
                        <iconify-icon icon="{{ $channel['icon'] }}"></iconify-icon>
                    </span>
                    <span class="crm-source-card__label">{{ $channel['label'] }}</span>
                    <strong class="crm-source-card__count">{{ number_format($count) }}</strong>
                </a>
            @endforeach
            @foreach($secondaryChannels as $channel)
                @php
                    $isActive = $activeSource === $channel['value'];
                    $count = (int) ($sourceCounts[$channel['value']] ?? 0);
                    $channelUrl = $filterUrl(['source' => $channel['value']], ['source', 'form_id', 'advertising_platform']);
                @endphp
                <a href="{{ $channelUrl }}"
                   @class(['crm-source-card', 'crm-source-card--'.$channel['tone'], 'is-active' => $isActive])
                   role="tab"
                   title="{{ $channel['hint'] }}">
                    <span class="crm-source-card__icon" aria-hidden="true">
                        <iconify-icon icon="{{ $channel['icon'] }}"></iconify-icon>
                    </span>
                    <span class="crm-source-card__label">{{ $channel['label'] }}</span>
                    <strong class="crm-source-card__count">{{ number_format($count) }}</strong>
                </a>
            @endforeach
        </div>
    </div>

    @if($showFormPicker)
        <div class="crm-lead-finder__step2 crm-lead-finder__step2--forms">
            <div class="crm-lead-finder__step2-head">
                <iconify-icon icon="solar:arrow-right-linear" aria-hidden="true"></iconify-icon>
                <div>
                    <strong>Which form?</strong>
                    <span>Pick all forms or one specific form</span>
                </div>
            </div>
            <div class="crm-lead-finder__step2-options">
                <a href="{{ $filterUrl(['source' => 'form_submission'], ['form_id']) }}"
                   @class(['crm-source-pill', 'crm-source-pill--form', 'is-active' => ! request()->filled('form_id')])>
                    <iconify-icon icon="solar:inbox-in-linear" aria-hidden="true"></iconify-icon>
                    <span>All forms</span>
                    <span class="crm-source-pill__count">{{ number_format($formLeadsTotal) }}</span>
                </a>
                @forelse($forms as $form)
                    @php
                        $formCount = (int) ($formLeadCounts[$form->id] ?? 0);
                        $isFormActive = (string) $activeFormId === (string) $form->id;
                    @endphp
                    <a href="{{ $filterUrl(['source' => 'form_submission', 'form_id' => $form->id], ['form_id']) }}"
                       @class(['crm-source-pill', 'crm-source-pill--form', 'is-active' => $isFormActive, 'is-empty' => $formCount === 0 && ! $isFormActive])>
                        <iconify-icon icon="solar:document-text-linear" aria-hidden="true"></iconify-icon>
                        <span>{{ $form->name }}</span>
                        <span class="crm-source-pill__count">{{ number_format($formCount) }}</span>
                    </a>
                @empty
                    <span class="crm-lead-finder__step2-empty">No active forms yet — create one in Form Center.</span>
                @endforelse
            </div>
        </div>
    @endif

    @if($showImportPicker)
        <div class="crm-lead-finder__step2 crm-lead-finder__step2--import">
            <div class="crm-lead-finder__step2-head">
                <iconify-icon icon="solar:arrow-right-linear" aria-hidden="true"></iconify-icon>
                <div>
                    <strong>Import channel</strong>
                    <span>Filter imported leads by where they originally came from</span>
                </div>
            </div>
            <div class="crm-lead-finder__step2-options">
                <a href="{{ $filterUrl(['source' => 'file_import'], ['advertising_platform']) }}"
                   @class(['crm-source-pill', 'is-active' => ! request()->filled('advertising_platform')])>
                    <span>All imports</span>
                    <span class="crm-source-pill__count">{{ number_format((int) ($sourceCounts['file_import'] ?? 0)) }}</span>
                </a>
                @foreach($platformOptions ?? [] as $platformValue => $platformLabel)
                    @php $isPlatformActive = $activePlatform === $platformValue; @endphp
                    <a href="{{ $filterUrl(['source' => 'file_import', 'advertising_platform' => $platformValue], ['advertising_platform']) }}"
                       @class(['crm-source-pill', 'is-active' => $isPlatformActive])>
                        <span>{{ $platformLabel }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

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

        <details class="crm-lead-finder__more" @if($expandMore) open @endif>
            <summary class="crm-lead-finder__more-summary">
                <iconify-icon icon="solar:filter-linear" aria-hidden="true"></iconify-icon>
                More filters
                @if(count($activeFilterChips) > 0)
                    <span class="crm-filter-workspace__filter-count">{{ count($activeFilterChips) }}</span>
                @endif
            </summary>
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
                <div class="crm-lead-finder__refine-field crm-lead-finder__refine-field--wide">
                    <label for="campaign_name">Campaign contains</label>
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
        </details>
    </form>

    @if($hasExtraFilters)
        <div class="crm-lead-finder__active" aria-label="Active filters">
            <span class="crm-lead-finder__active-label">Also filtered by</span>
            <div class="crm-lead-finder__active-list">
                @foreach($activeFilterChips as $chip)
                    <a href="{{ $chip['url'] }}" class="crm-active-filter" title="Remove {{ $chip['label'] }} filter">
                        <span class="crm-active-filter__label">{{ $chip['label'] }}</span>
                        <span class="crm-active-filter__value">{{ Str::limit($chip['value'], 28) }}</span>
                        <iconify-icon icon="solar:close-circle-linear" aria-hidden="true"></iconify-icon>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('admin.crm.leads.index', ['view' => $viewMode]) }}" class="crm-lead-finder__clear">Reset all</a>
        </div>
    @endif
</div>
