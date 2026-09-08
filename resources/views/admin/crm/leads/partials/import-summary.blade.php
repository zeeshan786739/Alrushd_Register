@php
    $importSummary = $importSummary ?? \App\Support\LeadImportSummary::forLead($lead);
@endphp
@if($importSummary)
<section class="crm-import-summary" aria-label="Import summary">
    <div class="crm-import-summary__head">
        <div class="crm-import-summary__title">
            <iconify-icon icon="solar:document-text-linear"></iconify-icon>
            <span>Import summary</span>
        </div>
        @if($importSummary['filename'])
            <span class="crm-import-summary__file">{{ Str::limit($importSummary['filename'], 42) }}</span>
        @endif
    </div>

    @if($importSummary['highlights'] !== [] || $importSummary['checklist'] !== [])
        <div class="crm-import-summary__strip">
            @foreach($importSummary['highlights'] as $item)
                <span class="crm-field-chip crm-field-chip--{{ $item['tone'] }}" title="{{ $item['label'] }}">
                    <span class="crm-field-chip__key">{{ $item['label'] }}</span>
                    <span class="crm-field-chip__val">{{ $item['value'] }}</span>
                </span>
            @endforeach

            @if($importSummary['checklist'] !== [])
                <div class="crm-import-checklist" aria-label="Admission checklist">
                    @foreach($importSummary['checklist'] as $item)
                        <span class="crm-import-check crm-import-check--{{ $item['done'] ? 'done' : 'pending' }}"
                              title="{{ $item['label'] }}: {{ $item['done'] ? 'Complete' : 'Not yet' }}">
                            <iconify-icon icon="{{ $item['icon'] }}"></iconify-icon>
                            <span>{{ $item['short'] }}</span>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if(($importSummary['extra_count'] ?? 0) > 0)
        <details class="crm-import-summary__details">
            <summary>
                <span>View {{ $importSummary['extra_count'] }} more imported field{{ $importSummary['extra_count'] === 1 ? '' : 's' }}</span>
                <iconify-icon icon="solar:alt-arrow-down-linear"></iconify-icon>
            </summary>
            <div class="crm-import-summary__details-body">
                @foreach($importSummary['extra_groups'] as $group => $items)
                    <div class="crm-import-summary__group">
                        <h4>{{ $group }}</h4>
                        <dl class="crm-import-summary__grid">
                            @foreach($items as $item)
                                <div class="crm-import-summary__field">
                                    <dt>{{ $item['label'] }}</dt>
                                    <dd>
                                        <span class="crm-field-chip crm-field-chip--{{ $item['tone'] }} crm-field-chip--value-only">
                                            {{ $item['value'] }}
                                        </span>
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endforeach
            </div>
        </details>
    @endif
</section>
@endif
