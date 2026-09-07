@props([
    'label',
    'value',
    'href' => null,
    'accent' => 'neutral',
    'hint' => null,
    'icon' => null,
    'progress' => null,
])

@php
    $tag = $href ? 'a' : 'div';
    $defaultIcons = [
        'brand' => 'solar:chart-square-linear',
        'purple' => 'solar:star-linear',
        'blue' => 'solar:calendar-linear',
        'teal' => 'solar:inbox-in-linear',
        'green' => 'solar:check-circle-linear',
        'amber' => 'solar:wallet-money-linear',
        'red' => 'solar:danger-triangle-linear',
        'neutral' => 'solar:graph-linear',
    ];
    $metricIcon = $icon ?? ($defaultIcons[$accent] ?? $defaultIcons['neutral']);
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @class(['crm-overview-metric', 'crm-overview-metric--accent-'.$accent])
    @if($href) aria-label="{{ $label }}" @endif
>
    <div class="crm-overview-metric__top">
        <span class="crm-overview-metric__icon" aria-hidden="true">
            <iconify-icon icon="{{ $metricIcon }}"></iconify-icon>
        </span>
        <span class="crm-overview-metric__label">{{ $label }}</span>
    </div>
    <strong class="crm-overview-metric__value">{{ $value }}</strong>
    @if($hint)
        <span class="crm-overview-metric__hint">{{ $hint }}</span>
    @endif
    @if($progress !== null)
        <span class="crm-overview-metric__track" aria-hidden="true">
            <span class="crm-overview-metric__bar" style="width: {{ min(100, max(0, (float) $progress)) }}%"></span>
        </span>
    @endif
</{{ $tag }}>
