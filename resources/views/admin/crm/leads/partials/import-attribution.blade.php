@if($lead->isFromImport() || $lead->advertising_platform || $lead->campaign_name || $lead->adset_name || $lead->ad_name || $lead->form_name || $lead->custom_data || $lead->category)
<div class="card radius-12 shadow-2 border-0 mb-24 crm-detail-secondary-card">
    <div class="card-body p-24">
        <section class="crm-detail-section">
        <h6 class="crm-detail-section__title">Ingestion &amp; Classification</h6>
        <div class="row g-3 text-sm crm-detail-facts">
            <div class="col-md-6"><strong>Ingestion source</strong><br>{{ \App\Support\LeadSourceOptions::label($lead->source) }}</div>
            <div class="col-md-6"><strong>Category</strong><br>
                @if($lead->category)
                    <span class="crm-category-badge crm-category-badge--{{ $lead->category->displayTone() }}">
                        <iconify-icon icon="{{ $lead->category->displayIcon() }}"></iconify-icon>
                        {{ $lead->category->name }}
                    </span>
                @else
                    —
                @endif
            </div>
            @if($lead->leadImport)
                <div class="col-md-6"><strong>Imported from</strong><br>{{ $lead->leadImport->original_filename }}</div>
                <div class="col-md-6"><strong>Import date</strong><br>{{ optional($lead->leadImport->completed_at ?? $lead->created_at)->format('M j, Y H:i') }}</div>
            @endif
            <div class="col-md-6"><strong>Original source timestamp</strong><br>{{ optional($lead->source_submitted_at)->format('M j, Y H:i') ?? '—' }}</div>
        </div>
        </section>

@if($lead->advertising_platform || $lead->campaign_name || $lead->adset_name || $lead->ad_name || $lead->form_name)
        <section class="crm-detail-section">
        <h6 class="crm-detail-section__title">Platform / Campaign Attribution</h6>
        <div class="row g-3 text-sm crm-detail-facts">
            <div class="col-md-6"><strong>Platform</strong><br>{{ $lead->advertising_platform ? ucfirst($lead->advertising_platform) : '—' }}</div>
            <div class="col-md-6"><strong>Campaign</strong><br>{{ $lead->campaign_name ?? '—' }}</div>
            <div class="col-md-6"><strong>Ad set</strong><br>{{ $lead->adset_name ?? '—' }}</div>
            <div class="col-md-6"><strong>Ad / creative</strong><br>{{ $lead->ad_name ?? '—' }}</div>
            <div class="col-md-6"><strong>Form</strong><br>{{ $lead->form_name ?? '—' }}</div>
        </div>
        </section>
@endif

@if(!empty($lead->custom_data) && is_array($lead->custom_data))
        <section class="crm-detail-section">
        <h6 class="crm-detail-section__title">Custom Lead Information</h6>
        <div class="row g-3 text-sm crm-detail-facts">
            @foreach($lead->custom_data as $label => $value)
                <div class="col-md-6">
                    <strong>{{ $label }}</strong><br>{{ is_scalar($value) ? $value : json_encode($value) }}
                </div>
            @endforeach
        </div>
        @can('import leads')
            @if($lead->leadImport)
                <details class="mt-16">
                    <summary class="text-sm text-secondary-light" style="cursor:pointer">View raw import data</summary>
                    <div class="table-responsive mt-12">
                        <table class="table table-sm mb-0">
                            <tbody>
                            @foreach(($lead->leadImport->rows()->where('lead_id', $lead->id)->first()?->raw_data ?? []) as $key => $raw)
                                <tr>
                                    <th class="text-sm">{{ $key }}</th>
                                    <td class="text-sm">{{ is_scalar($raw) ? $raw : json_encode($raw) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        @endcan
        </section>
@endif
    </div>
</div>
@endif
