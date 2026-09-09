@php
    use App\Support\LeadSourceOptions;
    $source = $source ?? null;
    $label = ($label ?? null) ?: LeadSourceOptions::label($source);
    $icon = LeadSourceOptions::icon($source);
    $compact = $compact ?? false;
@endphp
@php
    $href = $href ?? null;
    $tagClass = ['crm-source-badge', 'crm-source-badge--'.str_replace('_', '-', (string) $source), 'crm-source-badge--compact' => $compact];
    if ($href) {
        $tagClass[] = 'crm-filter-tag';
    }
@endphp
@if($href)
    <a href="{{ $href }}" @class($tagClass) data-crm-filter-tag title="Filter by {{ $label }}" onclick="event.stopPropagation()">
        @if($icon)
            <iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon>
        @endif
        <span>{{ $label }}</span>
    </a>
@else
    <span @class($tagClass)>
        @if($icon)
            <iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon>
        @endif
        <span>{{ $label }}</span>
    </span>
@endif
