@php
    $raw = $status ?? 'draft';
    $normalized = str_replace('-', '_', $raw);
    // Custom status slugs use hyphens, so look up the original value before the CSS-friendly one.
    $tone = $tone ?? \App\Support\LeadStatusCatalog::tone($raw);
    $label = $label ?? \App\Support\LeadStatusCatalog::label($raw);
    $class = 'crm-status-pill crm-status-pill--tone-'.$tone.' crm-status-pill--'.$normalized;
@endphp
<span class="{{ $class }}">{{ $label }}</span>
