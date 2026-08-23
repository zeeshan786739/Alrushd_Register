@extends('admin.layouts.app')

@section('title') Team & Access @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
    $shellActions = array_values(array_filter([
        auth()->user()->can('create user') ? [
            'label' => 'Invite teammate',
            'url' => route('admin.users.create'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:user-plus-linear',
        ] : null,
        auth()->user()->can('create role') ? [
            'label' => 'Create role',
            'url' => route('admin.roles.create'),
            'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
            'icon' => 'solar:shield-plus-linear',
        ] : null,
    ]));
@endphp

@include('admin.role-permission.partials.shell', [
    'activeTab' => 'overview',
    'stats' => $stats,
    'shellTitle' => 'Team & Access',
    'shellSubtitle' => 'Your control center for people, roles, and permissions.',
    'shellActions' => $shellActions,
])

<div class="row g-4">
    <div class="col-xxl-7">
        <div class="um-panel">
            <div class="um-panel__head">
                <div>
                    <h2 class="um-panel__title">Team members</h2>
                    <p class="um-panel__desc">People who can sign in to your admin panel.</p>
                </div>
                @can('view user')
                <a href="{{ route('admin.users.index') }}" class="um-panel__link">View all</a>
                @endcan
            </div>

            @if($users->isEmpty())
                <div class="um-empty-state um-empty-state--panel">
                    <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                    <h6>No teammates yet</h6>
                    <p>Invite your admissions staff, marketers, and admins to collaborate.</p>
                    @can('create user')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-12">
                        <iconify-icon icon="solar:user-plus-linear"></iconify-icon>
                        Invite first teammate
                    </a>
                    @endcan
                </div>
            @else
                <div class="um-team-list">
                    @foreach($users as $user)
                    @if(auth()->user()->can('edit user'))
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="um-team-row">
                    @else
                    <div class="um-team-row">
                    @endif
                        <span class="um-user-avatar" style="background: {{ UserManagementHelper::avatarGradient($user->email) }};">
                            {{ UserManagementHelper::initials($user->name) }}
                        </span>
                        <span class="um-team-row__body">
                            <strong>{{ $user->name }}</strong>
                            <span>{{ $user->email }}</span>
                        </span>
                        <span class="um-team-row__meta">
                            @forelse($user->roles->take(2) as $role)
                                <span class="um-role-badge">{{ UserManagementHelper::formatRoleName($role->name) }}</span>
                            @empty
                                <span class="text-secondary-light text-sm">No role</span>
                            @endforelse
                        </span>
                        @if(auth()->user()->can('edit user'))
                        <iconify-icon icon="solar:alt-arrow-right-linear" class="um-team-row__chevron"></iconify-icon>
                        @endif
                    @if(auth()->user()->can('edit user'))
                    </a>
                    @else
                    </div>
                    @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="col-xxl-5">
        <div class="um-panel um-panel--accent">
            <div class="um-panel__head">
                <div>
                    <h2 class="um-panel__title">Roles at a glance</h2>
                    <p class="um-panel__desc">Bundle permissions into job titles your team understands.</p>
                </div>
                @can('view role')
                <a href="{{ route('admin.roles.index') }}" class="um-panel__link">Manage roles</a>
                @endcan
            </div>

            <div class="um-role-stack">
                @foreach($roles as $role)
                <article class="um-role-mini {{ UserManagementHelper::isProtectedRole($role) ? 'is-protected' : '' }}">
                    <div class="um-role-mini__head">
                        <span class="um-user-avatar um-user-avatar--sm" style="background: {{ UserManagementHelper::avatarGradient($role->name) }};">
                            {{ UserManagementHelper::initials($role->name) }}
                        </span>
                        <div>
                            <strong>{{ UserManagementHelper::formatRoleName($role->name) }}</strong>
                            @if(UserManagementHelper::isProtectedRole($role))
                                <span class="um-system-badge">System</span>
                            @endif
                        </div>
                    </div>
                    <div class="um-role-mini__stats">
                        <span>{{ $role->permissions->count() }} permissions</span>
                        <span>{{ $role->users_count }} member{{ $role->users_count === 1 ? '' : 's' }}</span>
                    </div>
                    @can('edit role')
                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="um-role-mini__link">Edit role</a>
                    @endcan
                </article>
                @endforeach
            </div>
        </div>

        <div class="um-tip-card mt-4">
            <iconify-icon icon="solar:lightbulb-linear"></iconify-icon>
            <div>
                <strong>Tip for school admins</strong>
                <p>Most schools only need 2–3 roles: a full admin, an admissions officer, and a read-only viewer. Start simple — you can always add more later.</p>
            </div>
        </div>
    </div>
</div>
@endsection
