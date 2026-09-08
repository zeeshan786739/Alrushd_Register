@php $spreadsheetGroups = \App\Support\ImportedLeadDetails::forLead($lead); @endphp
@if($spreadsheetGroups)
<section class="crm-lead-ticket__block crm-spreadsheet-details" aria-label="Complete spreadsheet record">
    <div class="crm-lead-ticket__block-head">
        <h3 class="crm-lead-ticket__block-title"><iconify-icon icon="solar:document-text-linear"></iconify-icon> Spreadsheet record</h3>
        @if($lead->leadImport)
            <span class="crm-lead-ticket__block-meta">{{ $lead->leadImport->original_filename }}</span>
        @endif
    </div>
    <p class="crm-spreadsheet-details__intro">Original imported values — separate from current CRM status and assignment.</p>
    @foreach($spreadsheetGroups as $group => $items)
        <div class="crm-spreadsheet-details__group">
            <h4 class="crm-spreadsheet-details__group-title">{{ $group }}</h4>
            <dl class="crm-spreadsheet-details__grid">
                @foreach($items as $item)
                    <div class="crm-spreadsheet-details__item">
                        <dt>{{ $item['label'] }}</dt>
                        <dd>{{ $item['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endforeach
</section>
@endif
