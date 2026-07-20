@php
    $class = 'crm-status-pill crm-status-pill--' . str_replace('-', '_', $status ?? 'draft');
@endphp
<span class="{{ $class }}">{{ str_replace('_', ' ', $status ?? 'draft') }}</span>
