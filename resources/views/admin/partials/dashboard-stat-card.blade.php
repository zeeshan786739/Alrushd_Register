@props([
    'label',
    'value',
    'icon',
    'tone' => 'navy',
    'badge' => null,
    'badgeClass' => 'crm-stat-badge--up',
    'footer' => null,
    'chartId' => null,
    'href' => null,
    'meta' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    class="crm-stat-card crm-stat-card--{{ $tone }} {{ $href ? 'crm-stat-card--link' : '' }}"
    @if($href) aria-label="{{ $label }}" @endif
>
    <div class="crm-stat-card__glow" aria-hidden="true"></div>
    <div class="crm-stat-card__body">
        <div class="crm-stat-card__top">
            <div class="crm-stat-card__info">
                <span class="crm-stat-card__icon">
                    <iconify-icon icon="{{ $icon }}"></iconify-icon>
                </span>
                <div class="crm-stat-card__text">
                    <span class="crm-stat-card__label">{{ $label }}</span>
                    <span class="crm-stat-card__value">{{ $value }}</span>
                    @if($meta)
                        <span class="crm-stat-card__meta">{{ $meta }}</span>
                    @endif
                </div>
            </div>
            @if($chartId)
                <div id="{{ $chartId }}" class="crm-stat-card__chart remove-tooltip-title rounded-tooltip-value"></div>
            @endif
        </div>
        @if($badge || $footer)
            <div class="crm-stat-card__footer">
                @if($badge)
                    <span class="crm-stat-badge {{ $badgeClass }}">{{ $badge }}</span>
                @endif
                @if($footer)
                    <span class="crm-stat-card__footer-text">{{ $footer }}</span>
                @endif
            </div>
        @endif
    </div>
</{{ $tag }}>
