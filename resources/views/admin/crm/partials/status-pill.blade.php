@php
    $normalized = str_replace('-', '_', $status ?? 'draft');
    $tone = \App\Support\CrmStatusTone::for($normalized);
    $class = 'crm-status-pill crm-status-pill--tone-'.$tone.' crm-status-pill--'.$normalized;
@endphp
<span class="{{ $class }}">{{ str_replace('_', ' ', $normalized) }}</span>
