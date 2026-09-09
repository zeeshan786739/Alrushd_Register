@php
    $activeTab = $activeTab ?? 'hub';
    $sections = [
        [
            'key' => 'hub',
            'label' => 'Overview',
            'icon' => 'solar:widget-2-linear',
            'tone' => 'all',
            'url' => route('admin.integrations.hub'),
        ],
        [
            'key' => 'facebook',
            'label' => 'Facebook',
            'icon' => 'logos:facebook',
            'tone' => 'facebook',
            'url' => route('admin.integrations.facebook.show'),
        ],
        [
            'key' => 'tiktok',
            'label' => 'TikTok',
            'icon' => 'logos:tiktok-icon',
            'tone' => 'tiktok',
            'url' => route('admin.integrations.tiktok.show'),
        ],
    ];
@endphp
<nav class="int-module-nav" aria-label="Integration sections" role="tablist">
    @foreach($sections as $section)
        @php $isActive = $activeTab === $section['key']; @endphp
        <a href="{{ $section['url'] }}"
           @class([
               'crm-source-card',
               'crm-source-card--'.$section['tone'],
               'is-active' => $isActive,
           ])
           role="tab"
           @if($isActive) aria-selected="true" @else aria-selected="false" @endif>
            <span class="crm-source-card__icon" aria-hidden="true">
                <iconify-icon icon="{{ $section['icon'] }}"></iconify-icon>
            </span>
            <span class="crm-source-card__label">{{ $section['label'] }}</span>
        </a>
    @endforeach
</nav>
