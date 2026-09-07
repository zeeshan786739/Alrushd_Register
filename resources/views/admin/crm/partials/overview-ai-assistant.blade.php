@php
    $insights = $insights ?? ['brief' => '', 'tone' => 'calm', 'actions' => [], 'signals' => []];
    $smartSuggestions = $smartSuggestions ?? [];
@endphp
<section class="crm-overview-ai" aria-label="AI assistant" data-crm-overview-ai>
    <div class="crm-overview-ai__head">
        <div class="crm-overview-ai__badge" aria-hidden="true">
            <iconify-icon icon="solar:magic-stick-3-linear"></iconify-icon>
        </div>
        <div class="crm-overview-ai__copy">
            <h2 class="crm-overview-ai__title">AI brief</h2>
            <p class="crm-overview-ai__brief crm-overview-ai__brief--{{ $insights['tone'] }}">{{ $insights['brief'] }}</p>
        </div>
    </div>

    @can('view leads')
        <div class="crm-overview-ai__search" data-crm-overview-smart-search>
            <label class="visually-hidden" for="crm-overview-smart-input">Ask about your pipeline</label>
            <div class="crm-overview-ai__search-shell">
                <iconify-icon icon="solar:chat-round-dots-linear" aria-hidden="true"></iconify-icon>
                <input type="search"
                       id="crm-overview-smart-input"
                       class="crm-overview-ai__search-input"
                       placeholder="Ask: my overdue leads, unassigned new, form submissions…"
                       autocomplete="off"
                       aria-label="Smart pipeline search"
                       aria-controls="crm-overview-smart-panel"
                       data-crm-overview-smart-input>
                <button type="button" class="crm-overview-ai__search-go" data-crm-overview-smart-go aria-label="Go to results">
                    <iconify-icon icon="solar:arrow-right-linear"></iconify-icon>
                </button>
            </div>
            <div id="crm-overview-smart-panel" class="crm-overview-ai__search-panel" data-crm-overview-smart-panel hidden>
                <div class="crm-overview-ai__interpretation" data-crm-overview-smart-label hidden>
                    <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                    <span data-crm-overview-smart-label-text></span>
                </div>
                @if(!empty($smartSuggestions))
                    <div class="crm-overview-ai__suggestions">
                        @foreach(array_slice($smartSuggestions, 0, 4) as $suggestion)
                            <button type="button"
                                    class="crm-overview-ai__suggestion"
                                    data-crm-overview-suggestion
                                    data-query="{{ $suggestion['query'] }}">
                                {{ $suggestion['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endcan

    @if(!empty($insights['actions']))
        <div class="crm-overview-ai__actions" aria-label="Suggested actions">
            @foreach($insights['actions'] as $action)
                <a href="{{ $action['url'] }}" class="crm-overview-ai__action crm-overview-ai__action--{{ $action['tone'] }}">
                    <iconify-icon icon="{{ $action['icon'] }}" aria-hidden="true"></iconify-icon>
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    @endif

    @if(!empty($insights['signals']))
        <div class="crm-overview-ai__signals" aria-label="Key numbers">
            @foreach($insights['signals'] as $signal)
                @if($signal['href'])
                    <a href="{{ $signal['href'] }}" class="crm-overview-signal crm-overview-signal--{{ $signal['accent'] }}">
                        <span class="crm-overview-signal__label">{{ $signal['label'] }}</span>
                        <strong class="crm-overview-signal__value">{{ $signal['value'] }}</strong>
                    </a>
                @else
                    <div class="crm-overview-signal crm-overview-signal--{{ $signal['accent'] }}">
                        <span class="crm-overview-signal__label">{{ $signal['label'] }}</span>
                        <strong class="crm-overview-signal__value">{{ $signal['value'] }}</strong>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</section>
