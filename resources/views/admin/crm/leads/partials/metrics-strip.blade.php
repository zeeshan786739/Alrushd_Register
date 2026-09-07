@php
    $stats = $stats ?? [];
    $formStats = $formStats ?? [];
    $viewMode = $viewMode ?? 'board';
    $followUpUrl = route('admin.crm.leads.index', array_merge(
        request()->except(['page', 'follow_up']),
        request('follow_up') === 'today' ? [] : ['follow_up' => 'today', 'view' => $viewMode]
    ));
    $newUrl = route('admin.crm.leads.index', array_merge(
        request()->except(['page', 'lead_status']),
        request('lead_status') === 'new' ? [] : ['lead_status' => 'new', 'view' => $viewMode]
    ));
    $formLeadsUrl = route('admin.crm.leads.index', array_merge(
        request()->except(['page', 'source', 'form_id']),
        (request('source') === 'form_submission' && ! request()->filled('form_id'))
            ? []
            : ['source' => 'form_submission', 'view' => $viewMode]
    ));
    $pendingFormsUrl = route('admin.crm.form-entries.index', ['status' => 'pending']);
@endphp
<div class="crm-metrics-strip" aria-label="Pipeline metrics">
    <div class="crm-metrics-strip__items">
        <span class="crm-metrics-strip__item">
            <span class="crm-metrics-strip__label">Total</span>
            <strong>{{ number_format((int) ($stats['total'] ?? 0)) }}</strong>
        </span>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <a href="{{ $newUrl }}" @class(['crm-metrics-strip__item', 'crm-metrics-strip__item--link', 'is-active' => request('lead_status') === 'new'])>
            <span class="crm-metrics-strip__label">New</span>
            <strong>{{ number_format((int) ($stats['new'] ?? 0)) }}</strong>
        </a>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <a href="{{ $followUpUrl }}" @class(['crm-metrics-strip__item', 'crm-metrics-strip__item--link', 'is-active' => request('follow_up') === 'today'])>
            <span class="crm-metrics-strip__label">Follow-up today</span>
            <strong>{{ number_format((int) ($stats['follow_up_today'] ?? 0)) }}</strong>
        </a>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <a href="{{ $formLeadsUrl }}" @class(['crm-metrics-strip__item', 'crm-metrics-strip__item--link', 'is-active' => request('source') === 'form_submission' && ! request()->filled('form_id')])>
            <span class="crm-metrics-strip__label">Form leads</span>
            <strong>{{ number_format((int) ($stats['form_leads'] ?? 0)) }}</strong>
        </a>
        @can('view form submissions')
            <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
            <a href="{{ $pendingFormsUrl }}" class="crm-metrics-strip__item crm-metrics-strip__item--link">
                <span class="crm-metrics-strip__label">Pending forms</span>
                <strong>{{ number_format((int) ($formStats['submissions_pending'] ?? 0)) }}</strong>
            </a>
        @endcan
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <span class="crm-metrics-strip__item">
            <span class="crm-metrics-strip__label">Facebook · 7d</span>
            <strong>{{ number_format((int) ($stats['facebook_this_week'] ?? 0)) }}</strong>
        </span>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <span class="crm-metrics-strip__item">
            <span class="crm-metrics-strip__label">TikTok · 7d</span>
            <strong>{{ number_format((int) ($stats['tiktok_this_week'] ?? 0)) }}</strong>
        </span>
    </div>
</div>
