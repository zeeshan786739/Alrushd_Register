@php
    $umStats = $umStats ?? ($stats ?? [
        'roles' => 0,
        'permissions' => 0,
        'users' => 0,
    ]);
    $activeTab = $activeTab ?? '';

    $sections = [
        [
            'key' => 'overview',
            'label' => 'Overview',
            'icon' => 'solar:widget-2-linear',
            'tone' => 'all',
            'url' => route('admin.user-management.index'),
            'count' => null,
            'can' => true,
        ],
        [
            'key' => 'users',
            'label' => 'Team',
            'icon' => 'solar:users-group-rounded-linear',
            'tone' => 'team',
            'url' => route('admin.users.index'),
            'count' => $umStats['users'],
            'can' => auth()->user()->canany(['view user', 'create user', 'edit user']),
        ],
        [
            'key' => 'roles',
            'label' => 'Roles',
            'icon' => 'solar:shield-user-linear',
            'tone' => 'roles',
            'url' => route('admin.roles.index'),
            'count' => $umStats['roles'],
            'can' => auth()->user()->canany(['view role', 'create role', 'edit role']),
        ],
        [
            'key' => 'permissions',
            'label' => 'Permissions',
            'icon' => 'solar:key-linear',
            'tone' => 'permissions',
            'url' => route('admin.permissions.index'),
            'count' => $umStats['permissions'],
            'can' => auth()->user()->canany(['view permission', 'create permission', 'edit permission']),
        ],
    ];
@endphp
<nav class="um-module-nav" aria-label="Team and access sections" role="tablist">
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
