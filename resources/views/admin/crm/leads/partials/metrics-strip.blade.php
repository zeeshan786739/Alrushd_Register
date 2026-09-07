@php
    $stats = $stats ?? [];
    $viewMode = $viewMode ?? 'board';
    $followUpUrl = route('admin.crm.leads.index', array_merge(
        request()->except(['page', 'follow_up']),
        request('follow_up') === 'today' ? [] : ['follow_up' => 'today', 'view' => $viewMode]
    ));
    $newUrl = route('admin.crm.leads.index', array_merge(
        request()->except(['page', 'lead_status']),
        request('lead_status') === 'new' ? [] : ['lead_status' => 'new', 'view' => $viewMode]
    ));
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
