@php
    /** @var \Illuminate\Support\Collection|iterable $events */
    $events = $events ?? collect();
@endphp
@if(count($events))
    <div class="mt-16">
        <h6 class="fw-semibold mb-8">Delivery timeline</h6>
        <ul class="list-unstyled mb-0">
            @foreach($events as $event)
                @php
                    $type = \App\Enums\EmailMarketing\ProviderStatus::tryFrom((string) $event->event_type);
                    $label = $type?->label() ?? ucfirst(str_replace('_', ' ', (string) $event->event_type));
                @endphp
                <li class="d-flex justify-content-between gap-12 py-6 border-bottom">
                    <span>{{ $label }}</span>
                    <span class="em-meta text-nowrap">{{ optional($event->occurred_at)->format('M j, Y g:i A') ?? '—' }}</span>
                </li>
            @endforeach
        </ul>
        <div class="em-meta mt-8">Provider “accepted/processed” is not the same as Delivered.</div>
    </div>
@endif
