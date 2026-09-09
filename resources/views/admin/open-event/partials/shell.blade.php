@php
    use App\Models\MeetSpeaker;
    use App\Models\OpenEvent;
    use App\Models\OpenEventForm;
    use App\Models\OpenEventItem;

    $oeStats = $oeStats ?? [
        'events' => OpenEvent::count(),
        'items' => OpenEventItem::count(),
        'speakers' => MeetSpeaker::count(),
        'submissions' => OpenEventForm::count(),
    ];
    $activeTab = $activeTab ?? '';
@endphp

@once
    @include('admin.crm.partials.styles')
    @include('admin.crm.partials.workspace-shell')
    @include('admin.open-event.partials.premium-styles')
@endonce

@include('admin.partials.page-header', [
    'title' => $shellTitle ?? 'Open Events',
    'subtitle' => $shellSubtitle ?? 'Manage events, sessions, speakers, and registration submissions.',
    'showBreadcrumb' => true,
    'breadcrumbs' => [['label' => 'Events'], ['label' => $shellTitle ?? 'Open Events']],
    'actions' => $shellActions ?? [],
    'hideFlash' => true,
])

<div class="oe-workspace crm-workspace-shell">
    @if(empty($compact))
        <div class="crm-metrics-strip" aria-label="Open Events metrics">
            <div class="crm-metrics-strip__items">
                <span class="crm-metrics-strip__hint">Open Events workspace</span>
                @canany(['view open_event','create open_event','edit open_event'])
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">Events</span>
                        <strong>{{ number_format($oeStats['events']) }}</strong>
                    </span>
                @endcanany
                @canany(['view event_item','create event_item','edit event_item'])
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">Items</span>
                        <strong>{{ number_format($oeStats['items']) }}</strong>
                    </span>
                @endcanany
                @canany(['view meet_speakers','create meet_speakers','edit meet_speakers'])
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">Speakers</span>
                        <strong>{{ number_format($oeStats['speakers']) }}</strong>
                    </span>
                @endcanany
                @canany(['view open_event_form','create open_event_form','edit open_event_form'])
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">Submissions</span>
                        <strong>{{ number_format($oeStats['submissions']) }}</strong>
                    </span>
                @endcanany
            </div>
        </div>

        <div class="oe-tabs-workspace">
            <div class="oe-tabs-workspace__head">
                <h2 class="oe-tabs-workspace__title">Workspace sections</h2>
                <p class="oe-tabs-workspace__sub">Events, sessions, speakers, and registration submissions</p>
            </div>
            @include('admin.open-event.partials.module-nav', ['activeTab' => $activeTab, 'oeStats' => $oeStats])
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 m-3 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 m-3 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:close-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>
