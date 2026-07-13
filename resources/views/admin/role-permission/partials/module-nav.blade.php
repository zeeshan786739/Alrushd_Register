@php
    $umStats = $umStats ?? [
        'roles' => \Spatie\Permission\Models\Role::where('guard_name', 'admin')->count(),
        'permissions' => \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->count(),
        'users' => \App\Models\Admin::count(),
    ];
    $activeTab = $activeTab ?? '';
@endphp
<nav class="um-module-nav" aria-label="User management sections">
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
    @canany(['view user','create user','edit user'])
    <a href="{{ route('admin.users.index') }}"
       class="um-module-nav__link {{ $activeTab === 'users' ? 'is-active' : '' }}">
        <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
        Users
        <span class="um-module-nav__count">{{ $umStats['users'] }}</span>
    </a>
    @endcanany
</nav>
