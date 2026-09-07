@php
    use App\Support\LeadSourceOptions;
    $source = $source ?? null;
    $label = ($label ?? null) ?: LeadSourceOptions::label($source);
    $icon = LeadSourceOptions::icon($source);
    $compact = $compact ?? false;
@endphp
<span @class(['crm-source-badge', 'crm-source-badge--'.str_replace('_', '-', (string) $source), 'crm-source-badge--compact' => $compact])>
    @if($icon)
        <iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon>
    @endif
    <span>{{ $label }}</span>
</span>
