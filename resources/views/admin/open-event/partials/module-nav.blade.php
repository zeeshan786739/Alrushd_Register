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

    $sections = [
        [
            'key' => 'events',
            'label' => 'Events',
            'icon' => 'solar:calendar-mark-linear',
            'tone' => 'events',
            'url' => route('admin.open-events.index'),
            'count' => $oeStats['events'],
            'can' => auth()->user()->canany(['view open_event', 'create open_event', 'edit open_event']),
        ],
        [
            'key' => 'items',
            'label' => 'Event Items',
            'icon' => 'solar:checklist-linear',
            'tone' => 'items',
            'url' => route('admin.open-event-items.index'),
            'count' => $oeStats['items'],
            'can' => auth()->user()->canany(['view event_item', 'create event_item', 'edit event_item']),
        ],
        [
            'key' => 'speakers',
            'label' => 'Speakers',
            'icon' => 'solar:microphone-linear',
            'tone' => 'speakers',
            'url' => route('admin.meet-speakers.index'),
            'count' => $oeStats['speakers'],
            'can' => auth()->user()->canany(['view meet_speakers', 'create meet_speakers', 'edit meet_speakers']),
        ],
        [
            'key' => 'submissions',
            'label' => 'Submissions',
            'icon' => 'solar:clipboard-list-linear',
            'tone' => 'submissions',
            'url' => route('admin.open-event-form.index'),
            'count' => $oeStats['submissions'],
            'can' => auth()->user()->canany(['view open_event_form', 'create open_event_form', 'edit open_event_form']),
        ],
    ];
@endphp
<nav class="oe-module-nav" aria-label="Open Events sections" role="tablist">
    @foreach($sections as $section)
        @if($section['can'])
            @php $isActive = $activeTab === $section['key']; @endphp
            <a href="{{ $section['url'] }}"
               @class([
                   'crm-source-card',
                   'crm-source-card--'.$section['tone'],
                   'is-active' => $isActive,
               ])
               role="tab"
               @if($isActive) aria-selected="true" @else aria-selected="false" @endif>
                <span class="crm-source-card__icon" aria-hidden="true">
                    <iconify-icon icon="{{ $section['icon'] }}"></iconify-icon>
                </span>
                <span class="crm-source-card__label">{{ $section['label'] }}</span>
                <strong class="crm-source-card__count">{{ number_format($section['count']) }}</strong>
            </a>
        @endif
    @endforeach
</nav>
