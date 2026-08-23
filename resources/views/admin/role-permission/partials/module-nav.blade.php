@php
    $umStats = $umStats ?? ($stats ?? [
        'roles' => 0,
        'permissions' => 0,
        'users' => 0,
    ]);
    $activeTab = $activeTab ?? '';
@endphp
<nav class="um-module-nav" aria-label="Team and access sections">
    <a href="{{ route('admin.user-management.index') }}"
       class="um-module-nav__link {{ $activeTab === 'overview' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:widget-2-linear"></iconify-icon>
        Overview
    </a>
    @canany(['view user','create user','edit user'])
    <a href="{{ route('admin.users.index') }}"
       class="um-module-nav__link {{ $activeTab === 'users' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
        Team
        <span class="um-module-nav__count">{{ $umStats['users'] }}</span>
    </a>
    @endcanany
    @canany(['view role','create role','edit role'])
    <a href="{{ route('admin.roles.index') }}"
       class="um-module-nav__link {{ $activeTab === 'roles' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
        Roles
        <span class="um-module-nav__count">{{ $umStats['roles'] }}</span>
    </a>
    @endcanany
    @canany(['view permission','create permission','edit permission'])
    <a href="{{ route('admin.permissions.index') }}"
       class="um-module-nav__link {{ $activeTab === 'permissions' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:key-linear"></iconify-icon>
        Permissions
        <span class="um-module-nav__count">{{ $umStats['permissions'] }}</span>
    </a>
    @endcanany
</nav>
