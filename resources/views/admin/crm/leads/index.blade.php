@extends('admin.layouts.app')
@section('title', 'Leads')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.crm.leads.partials.premium-styles')
@php
    $categoryFilterOptions = collect($categories ?? [])->pluck('name', 'id')->all();
    if ((($segments['uncategorized']['total'] ?? 0) > 0) || request('lead_category_id') === 'uncategorized') {
        $categoryFilterOptions = ['uncategorized' => 'Uncategorized'] + $categoryFilterOptions;
    }
    $statusInlineOptions = [];
    foreach (\App\Support\LeadStatusCatalog::ordered() as $status) {
        $statusInlineOptions[$status->value] = [
            'label' => $status->label(),
            'tone' => $status->tone,
            'icon' => \App\Support\CrmStatusTone::icon($status->value),
        ];
    }
    $priorityInlineOptions = [];
    foreach (\App\Enums\LeadPriority::cases() as $priority) {
        $priorityInlineOptions[$priority->value] = [
            'label' => $priority->label(),
            'tone' => \App\Support\CrmStatusTone::for($priority->value),
            'icon' => \App\Support\CrmStatusTone::icon($priority->value),
        ];
    }
    $assigneeInlineOptions = ['' => ['label' => 'Unassigned', 'tone' => 'neutral', 'icon' => 'solar:user-linear']];
    foreach ($admins as $admin) {
        $assigneeInlineOptions[(string) $admin->id] = [
            'label' => $admin->name,
            'tone' => 'neutral',
            'icon' => 'solar:user-linear',
        ];
    }
    $workflowStatuses = $workflowStatuses ?? \App\Support\LeadPipeline::orderedStatuses();
    $activeFilters = collect(request()->only(['search','follow_up','lead_category_id','source','form_id','advertising_platform','campaign_name','lead_status','priority','assigned_to']))
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->count();
@endphp
<div class="dashboard-main-body {{ ($viewMode ?? 'board') === 'list' ? 'crm-list-view' : 'crm-board-view' }}" id="crm-leads-page"
     data-inline-url-template="{{ url('admin/crm/leads') }}/__ID__/inline"
     data-panel-url-template="{{ url('admin/crm/leads') }}/__ID__/panel"
     data-panel-edit-url-template="{{ url('admin/crm/leads') }}/__ID__/panel/edit"
     data-create-panel-url="{{ route('admin.crm.leads.create.panel') }}"
     data-store-url="{{ route('admin.crm.leads.store') }}"
     data-submission-panel-url-template="{{ url('admin/crm/form-submissions') }}/__ID__/panel"
     data-convert-submission-url-template="{{ url('admin/crm/form-submissions') }}/__ID__/convert-lead"
     data-update-url-template="{{ url('admin/crm/leads') }}/__ID__"
     data-filter-clear-url="{{ route('admin.crm.leads.filters.clear') }}"
     data-can-update="{{ auth('admin')->user()?->can('update leads') ? '1' : '0' }}"
     data-can-assign="{{ auth('admin')->user()?->can('assign leads') ? '1' : '0' }}"
     data-can-bulk="{{ auth('admin')->user()?->can('update leads') || auth('admin')->user()?->can('assign leads') ? '1' : '0' }}"
     data-bulk-url="{{ route('admin.crm.leads.bulk') }}"
     data-bulk-filter-url="{{ route('admin.crm.leads.bulk-filter') }}"
     data-filtered-total="{{ $filteredTotal ?? 0 }}"
     data-reorder-url="{{ route('admin.crm.leads.reorder-list') }}"
     data-reorder-board-url="{{ route('admin.crm.leads.reorder-board') }}"
     data-reorder-pipeline-url="{{ route('admin.crm.leads.reorder-pipeline-columns') }}"
     data-board-column-url="{{ route('admin.crm.leads.board-column') }}"
     data-list-group-url="{{ route('admin.crm.leads.list-group') }}"
     data-board-batch-size="{{ $boardColumnBatch ?? 20 }}"
     data-list-group-batch="{{ $listGroupBatch ?? 4 }}"
     data-board-total="{{ $filteredTotal ?? 0 }}"
     data-list-loaded="{{ $listLoadedCount ?? 0 }}"
     data-smart-search-url="{{ route('admin.crm.leads.smart-search') }}"
     data-initial-view="{{ $viewMode ?? 'board' }}">
    <script>
        (function () {
            var page = document.currentScript && document.currentScript.parentElement;
            if (!page || page.id !== 'crm-leads-page') return;
            var key = 'crm_leads_view';
            var view = page.getAttribute('data-initial-view') === 'list' ? 'list' : 'board';
            try {
                var q = new URL(window.location.href).searchParams.get('view');
                if (q === 'list' || q === 'board') view = q;
                else {
                    var stored = localStorage.getItem(key);
                    if (stored === 'list' || stored === 'board') view = stored;
                }
            } catch (e) { /* ignore */ }
            page.classList.remove('crm-board-view', 'crm-list-view');
            page.classList.add(view === 'list' ? 'crm-list-view' : 'crm-board-view');
        })();
    </script>
    @include('admin.partials.page-header', [
        'title' => 'Leads',
        'subtitle' => 'Pipeline, follow-ups, and conversions in one workspace',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Leads']],
        'actions' => array_filter([
            auth('admin')->user()?->can('import leads') ? ['label' => 'Import History', 'url' => route('admin.crm.leads.import.index'), 'icon' => 'solar:history-linear', 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'] : null,
            auth('admin')->user()?->can('import leads') ? ['label' => 'Import Leads', 'url' => route('admin.crm.leads.import.create'), 'icon' => 'solar:import-linear', 'class' => 'btn-outline-primary-600 radius-8 px-20 py-11'] : null,
            auth('admin')->user()?->can('export leads') ? ['label' => 'Export', 'url' => route('admin.crm.leads.export', request()->query()), 'icon' => 'solar:export-linear', 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'] : null,
            auth('admin')->user()?->can('create leads') ? ['label' => 'Add Lead', 'url' => '#', 'icon' => 'solar:add-circle-linear', 'class' => 'btn-primary-600 radius-8 px-20 py-11', 'attrs' => ['data-crm-lead-create-open' => '1']] : null,
        ]),
    ])

    <div class="crm-leads-workspace crm-workspace-shell">
        @include('admin.crm.leads.partials.metrics-strip', ['stats' => $stats, 'formStats' => $formStats ?? [], 'viewMode' => $viewMode ?? 'board'])

        @include('admin.crm.leads.partials.filter-workspace', [
            'viewMode' => $viewMode ?? 'board',
            'categories' => $categories ?? collect(),
            'forms' => $forms ?? collect(),
            'formLeadCounts' => $formLeadCounts ?? [],
            'segments' => $segments ?? [],
            'sourceCounts' => $sourceCounts ?? [],
            'smartSearchSuggestions' => $smartSearchSuggestions ?? [],
            'categoryFilterOptions' => $categoryFilterOptions,
            'sourceOptions' => $sourceOptions ?? [],
            'platformOptions' => $platformOptions ?? [],
            'admins' => $admins,
        ])

        <div class="crm-leads-toolbar">
            <div class="crm-leads-toolbar__left">
                <div class="crm-view-toggle" data-crm-view-toggle>
                    <button type="button" data-view="board" @class(['is-active' => ($viewMode ?? 'board') === 'board']) title="Board view"><iconify-icon icon="solar:kanban-linear"></iconify-icon></button>
                    <button type="button" data-view="list" @class(['is-active' => ($viewMode ?? 'board') === 'list']) title="List view"><iconify-icon icon="solar:list-linear"></iconify-icon></button>
                </div>
                <div class="crm-leads-toolbar__meta">
                    <strong data-crm-toolbar-matching>{{ number_format($filteredTotal ?? $leads->total()) }}</strong> matching
                    @if($activeFilters > 0)
                        <span class="crm-leads-toolbar__filters">{{ $activeFilters }} filter{{ $activeFilters === 1 ? '' : 's' }}</span>
                    @endif
                    @if(($viewMode ?? 'board') === 'board')
                        <span data-crm-toolbar-board-loaded>{{ $boardLoadedCount ?? 0 }} loaded on board</span>
                    @else
                        <span data-crm-toolbar-list-loaded>{{ number_format($listLoadedCount ?? 0) }} loaded · {{ number_format($filteredTotal ?? 0) }} matching</span>
                    @endif
                </div>
            </div>
            <div class="crm-leads-toolbar__right">
                @can('view leads')
                @if($savedFilters->isNotEmpty())
                    <div class="crm-leads-toolbar__saved" data-saved-filters>
                        @foreach($savedFilters as $filter)
                            <span class="crm-saved-filter-chip" data-saved-filter-id="{{ $filter->id }}">
                                <a href="{{ route('admin.crm.leads.index', array_merge($filter->filters, ['view' => $viewMode ?? 'board'])) }}" class="crm-saved-filter-chip__link">{{ $filter->name }}</a>
                                <button type="button"
                                        class="crm-saved-filter-chip__remove"
                                        data-crm-remove-filter
                                        data-url="{{ route('admin.crm.leads.filters.destroy', $filter) }}"
                                        title="Remove saved filter"
                                        aria-label="Remove saved filter {{ $filter->name }}">
                                    <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                                </button>
                            </span>
                        @endforeach
                        @if($savedFilters->count() >= 2)
                            <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-crm-clear-filters>Clear all</button>
                        @endif
                    </div>
                @endif
                @endcan
                <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-crm-toggle-save-filter>
                    <iconify-icon icon="solar:bookmark-linear"></iconify-icon> Save filter
                </button>
            </div>
        </div>

        @include('admin.crm.leads.partials.filter-bulk-bar', [
            'admins' => $admins,
            'filteredTotal' => $filteredTotal ?? 0,
            'hasActiveFilters' => $activeFilters > 0,
        ])

        @can('view leads')
        <form method="POST" action="{{ route('admin.crm.leads.filters.save') }}" class="crm-save-filter-inline mb-16" id="crm-save-filter-form" hidden>
            @csrf
            <div class="crm-save-filter-inline__inner">
                <input type="text" name="name" class="form-control radius-8" placeholder="Filter name" required>
                <input type="hidden" name="filters[view]" value="{{ $viewMode ?? 'board' }}">
                @foreach(request()->only(['search','follow_up','lead_category_id','source','form_id','advertising_platform','campaign_name','lead_status','priority','assigned_to','sort_by','sort_order']) as $key=>$value)
                    @if($value !== null && $value !== '')<input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">@endif
                @endforeach
                <button type="submit" class="btn btn-outline-primary-600 radius-8">Save</button>
                <button type="button" class="btn btn-outline-neutral-500 radius-8" data-crm-toggle-save-filter>Cancel</button>
            </div>
        </form>
        @endcan

        <div class="crm-board-only crm-workflow-board" data-crm-board>
            @foreach($workflowStatuses as $status)
                @php
                    $columnLeads = ($boardLeadsByStatus[$status->value] ?? collect())
                        ->sortBy(fn ($lead) => [$lead->list_position ?? PHP_INT_MAX, $lead->created_at?->timestamp ?? 0])
                        ->values();
                    $columnLeadTotal = (int) ($workflowCounts[$status->value] ?? 0);
                    $columnSubmissions = $status->value === 'new' ? ($pendingFormEntries ?? collect()) : collect();
                    $columnTotal = $columnLeadTotal + ($status->value === 'new' ? $columnSubmissions->count() : 0);
                    $columnVisible = $columnLeads->count() + $columnSubmissions->count();
                    $columnHasMore = $columnLeads->count() < $columnLeadTotal;
                @endphp
                <section class="crm-board-column"
                         data-crm-dropzone
                         data-crm-board-column
                         data-status="{{ $status->value }}">
                    <div class="crm-board-column__head"
                         @if(auth('admin')->user()?->can('update leads'))
                         data-crm-column-handle
                         title="Drag to reorder column"
                         @endif>
                        <div class="crm-board-column__title-wrap">
                            @can('update leads')
                                <span class="crm-board-column__grip" aria-hidden="true">
                                    <iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon>
                                </span>
                            @endcan
                            <span class="crm-board-column__dot" aria-hidden="true"></span>
                            <div>
                                <span class="crm-board-column__kicker">{{ $status->label() }}</span>
                                <strong>{{ number_format($columnTotal) }}</strong>
                            </div>
                        </div>
                        <span class="crm-board-column__count" title="{{ $columnVisible < $columnTotal ? 'Showing '.$columnVisible.' of '.$columnTotal.' in this column' : $columnVisible.' visible' }}">{{ $columnVisible }}</span>
                    </div>
                    <div class="crm-board-column__body"
                         data-crm-board-scroll
                         data-status="{{ $status->value }}"
                         data-offset="{{ $columnLeads->count() }}"
                         data-total="{{ $columnLeadTotal }}"
                         data-has-more="{{ $columnHasMore ? '1' : '0' }}">
                        @if($columnSubmissions->isNotEmpty())
                            @foreach($columnSubmissions as $entry)
                                @include('admin.crm.leads.partials.board-submission-card', ['entry' => $entry])
                            @endforeach
                        @endif
                        @forelse($columnLeads as $lead)
                            @include('admin.crm.leads.partials.board-card', [
                                'lead' => $lead,
                                'priorityInlineOptions' => $priorityInlineOptions,
                                'assigneeInlineOptions' => $assigneeInlineOptions,
                            ])
                        @empty
                            @if($columnSubmissions->isEmpty())
                            <div class="crm-board-empty">
                                <iconify-icon icon="solar:inbox-line-linear"></iconify-icon>
                                <span>Drop leads here</span>
                            </div>
                            @endif
                        @endforelse
                        <div class="crm-board-column__load-more" data-crm-board-load-more @if(! $columnHasMore) hidden @endif>
                            <span class="crm-board-column__load-idle">Scroll for more</span>
                            <span class="crm-board-column__load-busy" hidden>Loading…</span>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>

        <div class="crm-list-only crm-leads-list-shell">
            @can('update leads')
                <div class="crm-list-status-rail" data-crm-list-status-rail>
                    <div class="crm-list-status-rail__hint">
                        <iconify-icon icon="solar:transfer-horizontal-linear" aria-hidden="true"></iconify-icon>
                        Click a status to filter. Drag any row to reorder, or drop on a status pill to move it. Hover any status to delete it (custom statuses only).
                    </div>
                    <div class="crm-list-status-rail__zones">
                        @foreach($workflowStatuses as $status)
                            @php
                                $isHexTone = \App\Support\CrmColorPalette::isHex($status->tone);
                                $dropClass = $isHexTone
                                    ? 'crm-list-status-drop crm-list-status-drop--custom'
                                    : 'crm-list-status-drop crm-list-status-drop--tone-'.$status->tone;
                                $statusLeadCount = (int) (($workflowCounts[$status->value] ?? 0));
                            @endphp
                            <div class="crm-list-status-chip {{ $status->isCustom ? 'is-custom' : 'is-system' }}"
                                 data-crm-status-chip
                                 data-status="{{ $status->value }}"
                                 @if($status->isCustom) data-custom-status="1" @endif>
                                <a href="{{ \App\Support\CrmLeadFilterUrl::toggle('lead_status', $status->value) }}"
                                   class="{{ $dropClass }} @if(request('lead_status') === $status->value) is-filter-active @endif"
                                   @if($isHexTone) style="{{ \App\Support\CrmColorPalette::cssVars($status->tone) }}" @endif
                                   data-crm-list-dropzone
                                   data-crm-status-filter
                                   data-status="{{ $status->value }}"
                                   data-status-label="{{ $status->label() }}"
                                   data-status-tone="{{ $status->tone }}"
                                   aria-label="Filter {{ $status->label() }} leads">
                                    {{ $status->label() }}
                                </a>
                                <button type="button"
                                        class="crm-list-status-chip__delete"
                                        data-crm-delete-status
                                        data-status="{{ $status->value }}"
                                        data-status-label="{{ $status->label() }}"
                                        data-lead-count="{{ $statusLeadCount }}"
                                        data-is-custom="{{ $status->isCustom ? '1' : '0' }}"
                                        @if($status->isCustom)
                                            data-delete-url="{{ route('admin.crm.leads.statuses.destroy', $status->value) }}"
                                        @endif
                                        aria-label="Delete {{ $status->label() }}">
                                    <iconify-icon icon="solar:close-circle-bold" aria-hidden="true"></iconify-icon>
                                </button>
                            </div>
                        @endforeach
                        @if(\App\Support\LeadStatusCatalog::ready())
                            <button type="button"
                                    class="crm-list-status-add"
                                    data-crm-add-status
                                    aria-label="Add status">
                                <iconify-icon icon="solar:add-circle-linear" aria-hidden="true"></iconify-icon>
                                <span>Add</span>
                            </button>
                        @endif
                    </div>
                </div>

                @if(\App\Support\LeadStatusCatalog::ready())
                    <div class="crm-status-create-modal" data-crm-status-modal hidden>
                        <div class="crm-status-create-modal__backdrop" data-crm-status-modal-close></div>
                        <div class="crm-status-create-modal__panel" role="dialog" aria-modal="true" aria-labelledby="crm-status-create-title">
                            <div class="crm-status-create-modal__head">
                                <div class="crm-status-create-modal__title-wrap">
                                    <span class="crm-status-create-modal__badge" aria-hidden="true">
                                        <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                                    </span>
                                    <div>
                                        <h3 id="crm-status-create-title">Add status</h3>
                                        <p class="crm-status-create-modal__sub">Create a custom pipeline stage for your team.</p>
                                    </div>
                                </div>
                                <button type="button" class="crm-status-create-modal__close" data-crm-status-modal-close aria-label="Close">
                                    <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                                </button>
                            </div>
                            <form data-crm-status-create-form action="{{ route('admin.crm.leads.statuses.store') }}" method="POST">
                                @csrf
                                <label class="crm-status-create-modal__label" for="crm-status-name">Name</label>
                                <input id="crm-status-name" name="name" type="text" class="form-control radius-8" maxlength="100" placeholder="e.g. Waiting on docs" required autocomplete="off">

                                <span class="crm-status-create-modal__label">Color</span>
                                <input type="hidden" name="color" value="#0080FF" data-crm-status-color>

                                <div class="crm-color-picker" data-crm-color-picker>
                                    <div class="crm-color-picker__wheel-row">
                                        <label class="crm-color-picker__wheel" title="Pick any color">
                                            <input type="color" value="#0080FF" data-crm-color-wheel aria-label="Pick a color">
                                            <span class="crm-color-picker__wheel-face" data-crm-color-preview style="background:#0080FF"></span>
                                        </label>
                                        <div class="crm-color-picker__meta">
                                            <strong data-crm-color-hex>#0080FF</strong>
                                            <span>Click the circle to pick any color</span>
                                        </div>
                                    </div>

                                    <div class="crm-color-picker__section">
                                        <span class="crm-color-picker__section-label">Quick colors</span>
                                        <div class="crm-color-picker__circles" role="listbox" aria-label="Quick colors">
                                            @foreach(\App\Support\CrmColorPalette::circles() as $hex)
                                                @php $hex = strtoupper($hex); @endphp
                                                <button type="button"
                                                        class="crm-color-picker__circle {{ $hex === '#FFFFFF' ? 'is-light' : '' }} {{ $hex === '#0080FF' ? 'is-selected' : '' }}"
                                                        data-crm-color-swatch
                                                        data-color="{{ $hex }}"
                                                        style="background:{{ $hex }}"
                                                        aria-label="Color {{ $hex }}"></button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <p class="crm-status-create-modal__error" data-crm-status-create-error hidden></p>
                                <div class="crm-status-create-modal__actions">
                                    <button type="button" class="btn btn-outline-secondary radius-8" data-crm-status-modal-close>Cancel</button>
                                    <button type="submit" class="btn btn-primary-600 radius-8" data-crm-status-create-submit>Create status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan

            @include('admin.crm.leads.partials.list-bulk-bar', ['admins' => $admins])

            <div class="crm-leads-table">
                <div class="crm-leads-table__head" aria-hidden="true">
                    @canany(['update leads', 'assign leads'])
                        <span class="crm-leads-table__select">
                            <input type="checkbox"
                                   class="crm-list-select-all"
                                   data-crm-select-all
                                   aria-label="Select all leads on this page">
                        </span>
                    @else
                        <span></span>
                    @endcanany
                    <span></span>
                    <span>Lead</span>
                    <span>Status</span>
                    <span>Priority</span>
                    <span>Assigned</span>
                    <span>Follow-up</span>
                    <span>Added</span>
                    <span></span>
                </div>

                <div class="crm-leads-list" data-crm-leads-list>
                @if(($pendingFormEntries ?? collect())->isNotEmpty())
                    <div class="crm-list-status-group" data-crm-list-status-group data-status="form_intake">
                        <div class="crm-list-status-group__head" data-status="form_intake">
                            <div class="crm-list-status-group__title">
                                <span class="crm-list-status-group__label">Form intake</span>
                                <span class="crm-list-status-group__count">{{ number_format(($pendingFormEntries ?? collect())->count()) }}</span>
                            </div>
                        </div>
                        <div class="crm-list-status-group__rows" data-crm-list-status-rows>
                            @foreach($pendingFormEntries as $entry)
                                @include('admin.crm.leads.partials.list-submission-row', ['entry' => $entry])
                            @endforeach
                        </div>
                    </div>
                @endif
                @foreach($workflowStatuses as $status)
                    @php
                        $group = ($listLeadsByStatus ?? [])[$status->value] ?? null;
                        $statusLeads = collect($group['leads'] ?? []);
                        $groupTotal = (int) ($group['total'] ?? ($workflowCounts[$status->value] ?? 0));
                        $groupLoaded = (int) ($group['loaded'] ?? $statusLeads->count());
                        $groupHasMore = (bool) ($group['has_more'] ?? false);
                    @endphp
                    @if($statusLeads->isNotEmpty())
                        <div class="crm-list-status-group"
                             data-crm-list-status-group
                             data-status="{{ $status->value }}"
                             data-offset="{{ $groupLoaded }}"
                             data-total="{{ $groupTotal }}"
                             data-has-more="{{ $groupHasMore ? '1' : '0' }}"
                             data-expanded="0"
                             data-preview-count="4">
                            @include('admin.crm.leads.partials.list-status-group-head', [
                                'status' => $status,
                                'count' => $groupTotal,
                            ])
                            <div class="crm-list-status-group__rows" data-crm-list-status-rows>
                                @foreach($statusLeads as $lead)
                                    @include('admin.crm.leads.partials.list-row', [
                                        'lead' => $lead,
                                        'statusInlineOptions' => $statusInlineOptions,
                                        'priorityInlineOptions' => $priorityInlineOptions,
                                        'assigneeInlineOptions' => $assigneeInlineOptions,
                                    ])
                                @endforeach
                            </div>
                            <div class="crm-list-status-group__footer" data-crm-list-group-footer>
                                <button type="button"
                                        class="crm-list-status-group__more-btn"
                                        data-crm-list-see-more
                                        data-status="{{ $status->value }}"
                                        @if(! $groupHasMore && $groupTotal <= $groupLoaded) hidden @endif>
                                    <span class="crm-list-status-group__more-idle">
                                        See more
                                        <span class="crm-list-status-group__more-remaining" data-crm-list-group-remaining>
                                            ({{ number_format(max(0, $groupTotal - 4)) }})
                                        </span>
                                    </span>
                                    <span class="crm-list-status-group__more-busy" hidden>Loading…</span>
                                </button>
                                <button type="button"
                                        class="crm-list-status-group__less-btn"
                                        data-crm-list-show-less
                                        data-status="{{ $status->value }}"
                                        hidden>
                                    Show less
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(empty($listLeadsByStatus) && ($pendingFormEntries ?? collect())->isEmpty())
                    <div class="crm-leads-list-empty">
                        <iconify-icon icon="solar:inbox-line-linear"></iconify-icon>
                        <strong>No leads found</strong>
                        <span>Adjust your filters or import a new spreadsheet to populate this list.</span>
                    </div>
                @endif
                </div>
            </div>
        </div>

        @include('admin.crm.leads.partials.pagination', [
            'paginator' => $leads,
            'viewMode' => $viewMode ?? 'board',
            'boardLoadedCount' => $boardLoadedCount ?? 0,
            'listLoadedCount' => $listLoadedCount ?? 0,
        ])
    </div>

    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>

    @include('admin.crm.leads.partials.detail-drawer')
</div>
@endsection

@section('script')
<script src="{{ asset('admin/assets/js/crm-leads.js') }}?v={{ filemtime(public_path('admin/assets/js/crm-leads.js')) }}"></script>
@endsection
