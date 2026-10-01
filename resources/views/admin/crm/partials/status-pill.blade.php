@php
    $normalized = str_replace('-', '_', $status ?? 'draft');
    $tone = $tone ?? \App\Support\LeadStatusCatalog::tone($normalized);
    $label = $label ?? \App\Support\LeadStatusCatalog::label($normalized);
    $class = 'crm-status-pill crm-status-pill--tone-'.$tone.' crm-status-pill--'.$normalized;
@endphp
<span class="{{ $class }}">{{ $label }}</span>
