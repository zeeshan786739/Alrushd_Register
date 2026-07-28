@php
    use App\Support\LeadSourceOptions;
    $source = $source ?? null;
    $label = ($label ?? null) ?: LeadSourceOptions::label($source);
    $icon = LeadSourceOptions::icon($source);
    $badgeClass = LeadSourceOptions::badgeClass($source);
@endphp
<span class="badge {{ $badgeClass }} radius-8 d-inline-flex align-items-center gap-4">
    @if($icon)
        <iconify-icon icon="{{ $icon }}" width="14"></iconify-icon>
    @endif
    {{ $label }}
</span>
