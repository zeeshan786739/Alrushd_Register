@props([
    'label',
    'value',
    'href' => null,
    'accent' => 'neutral',
    'hint' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @class(['crm-overview-metric', 'crm-overview-metric--accent-'.$accent])
    @if($href) aria-label="{{ $label }}" @endif
>
    <span class="crm-overview-metric__label">{{ $label }}</span>
    <strong class="crm-overview-metric__value">{{ $value }}</strong>
    @if($hint)
        <span class="crm-overview-metric__hint">{{ $hint }}</span>
    @endif
</{{ $tag }}>
