@php
    $emStats = $stats ?? [];
    $activeTab = $activeTab ?? 'overview';

    $sections = [
        [
            'key' => 'overview',
            'label' => 'Overview',
            'icon' => 'solar:widget-2-linear',
            'tone' => 'overview',
            'url' => route('admin.email.dashboard'),
            'count' => null,
            'can' => true,
        ],
        [
            'key' => 'inbox',
            'label' => 'Inbox',
            'icon' => 'solar:inbox-linear',
            'tone' => 'inbox',
            'url' => route('admin.email.inbox'),
            'count' => ($emStats['inbox_unread'] ?? 0) > 0 ? $emStats['inbox_unread'] : null,
            'can' => auth()->user()->can('view inbox'),
        ],
        [
            'key' => 'campaigns',
            'label' => 'Campaigns',
            'icon' => 'solar:letter-linear',
            'tone' => 'campaigns',
            'url' => route('admin.email.campaigns.index'),
            'count' => $emStats['campaigns_total'] ?? null,
            'can' => auth()->user()->can('view campaigns'),
        ],
        [
            'key' => 'templates',
            'label' => 'Templates',
            'icon' => 'solar:clipboard-list-linear',
            'tone' => 'templates',
            'url' => route('admin.email.templates.index'),
            'count' => $emStats['templates_total'] ?? null,
            'can' => auth()->user()->can('view templates'),
        ],
        [
            'key' => 'settings',
            'label' => 'Settings',
            'icon' => 'solar:settings-linear',
            'tone' => 'settings',
            'url' => route('admin.email.mailbox.settings'),
            'count' => null,
            'can' => auth()->user()->can('manage mailbox settings'),
        ],
    ];
@endphp
<nav class="em-module-nav" aria-label="Email marketing sections" role="tablist">
    @foreach($sections as $section)
        @if($section['can'])
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
                @if($section['count'] !== null)
                    <strong class="crm-source-card__count">{{ number_format($section['count']) }}</strong>
                @endif
            </a>
        @endif
    @endforeach
</nav>
