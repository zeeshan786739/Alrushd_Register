@php
    use App\Support\UserManagementHelper;
    $umStats = $stats ?? UserManagementHelper::stats();
    $activeTab = $activeTab ?? '';
@endphp

<div class="um-shell mb-24">
    <div class="um-shell__hero">
        <div class="um-shell__intro">
            <span class="um-shell__eyebrow">People &amp; Access</span>
            <h1 class="um-shell__title">{{ $shellTitle ?? 'Team & Access' }}</h1>
            <p class="um-shell__subtitle">{{ $shellSubtitle ?? 'Invite teammates, define roles, and control who can do what in your school.' }}</p>
        </div>
        @if(!empty($shellActions))
            <div class="um-shell__actions">
                @foreach($shellActions as $action)
                    <a href="{{ $action['url'] }}"
                       class="btn {{ $action['class'] ?? 'btn-outline-neutral-500 radius-8 px-20 py-11' }} fc-btn">
                        @if(!empty($action['icon']))
                            <iconify-icon icon="{{ $action['icon'] }}"></iconify-icon>
                        @endif
                        <span>{{ $action['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if(empty($compact))
    <div class="um-stats-row">
        @canany(['view user','create user','edit user'])
        <a href="{{ route('admin.users.index') }}" class="um-stat-card {{ $activeTab === 'users' ? 'is-active' : '' }}">
            <span class="um-stat-card__icon um-stat-card__icon--users">
                <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
            </span>
            <span class="um-stat-card__body">
                <strong>{{ $umStats['users'] }}</strong>
                <span>Team members</span>
            </span>
        </a>
        @endcanany
        @canany(['view role','create role','edit role'])
        <a href="{{ route('admin.roles.index') }}" class="um-stat-card {{ $activeTab === 'roles' ? 'is-active' : '' }}">
            <span class="um-stat-card__icon um-stat-card__icon--roles">
                <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
            </span>
            <span class="um-stat-card__body">
                <strong>{{ $umStats['roles'] }}</strong>
                <span>Roles</span>
            </span>
        </a>
        @endcanany
        @canany(['view permission','create permission','edit permission'])
        <a href="{{ route('admin.permissions.index') }}" class="um-stat-card {{ $activeTab === 'permissions' ? 'is-active' : '' }}">
            <span class="um-stat-card__icon um-stat-card__icon--permissions">
                <iconify-icon icon="solar:key-linear"></iconify-icon>
            </span>
            <span class="um-stat-card__body">
                <strong>{{ $umStats['permissions'] }}</strong>
                <span>Permissions</span>
            </span>
        </a>
        @endcanany
    </div>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mb-24 d-flex align-items-center gap-8">
    <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 mb-24 d-flex align-items-center gap-8">
    <iconify-icon icon="solar:close-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
    <span>{{ session('error') }}</span>
</div>
@endif

@include('admin.role-permission.partials.module-nav', ['activeTab' => $activeTab, 'umStats' => $umStats])
