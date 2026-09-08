@php $spreadsheetGroups = \App\Support\ImportedLeadDetails::forLead($lead); @endphp
@if($spreadsheetGroups)
<section class="crm-detail-section crm-spreadsheet-details" aria-label="Complete spreadsheet record">
    <h3 class="crm-detail-section__title">Complete spreadsheet record</h3>
    <p class="text-sm text-secondary-light">Original imported values. These remain separate from the current CRM status and assignment.</p>
    @if($lead->leadImport)
        <p class="text-sm text-secondary-light" style="overflow-wrap:anywhere">{{ $lead->leadImport->original_filename }} · {{ $lead->leadImport->selected_sheet }}</p>
    @endif
    @foreach($spreadsheetGroups as $group => $items)
        <div style="margin-top:20px">
            <h4 style="font-size:13px;font-weight:600;color:var(--crm-text);margin-bottom:10px">{{ $group }}</h4>
            <dl style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(220px,100%),1fr));gap:0 20px;margin:0">
                @foreach($items as $item)
                    <div style="min-width:0;padding:10px 0;border-top:1px solid var(--crm-border)">
                        <dt style="font-size:12px;color:var(--crm-text-muted);font-weight:500;margin-bottom:5px">{{ $item['label'] }}</dt>
                        <dd style="font-size:13px;color:var(--crm-text);line-height:1.6;white-space:pre-wrap;overflow-wrap:anywhere;margin:0">{{ $item['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endforeach
</section>
@endif
