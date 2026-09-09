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

<div class="dashboard-main-body" id="um-workspace-page">
@include('admin.role-permission.partials.shell', [
    'activeTab' => 'overview',
    'stats' => $stats,
    'shellTitle' => 'Team & Access',
    'shellSubtitle' => 'Your control center for people, roles, and permissions.',
    'shellActions' => $shellActions,
])

<div class="um-layout">
    <div class="um-layout__main">
        <div class="crm-leads-toolbar">
            <div class="crm-leads-toolbar__meta">
                <strong><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon> Team members</strong>
                <span>People who can sign in to your admin panel</span>
            </div>
            @can('view user')
                <a href="{{ route('admin.users.index') }}" class="crm-leads-toolbar__link">View all</a>
            @endcan
        </div>

        <div class="crm-list-shell um-search-scope">
            <div class="crm-leads-table">
                <div class="crm-leads-table__head crm-leads-table__head--overview" aria-hidden="true">
                    <span>Member</span><span>Role</span><span></span>
                </div>
                <div class="crm-leads-list">
                    @forelse($users as $user)
                        @include('admin.role-permission.partials.team-row', ['user' => $user, 'compact' => true, 'showActions' => false])
                    @empty
                        <div class="crm-leads-list-empty">
                            <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                            <strong>No teammates yet</strong>
                            <span>Invite your admissions staff, marketers, and admins to collaborate.</span>
                            @can('create user')
                                <a href="{{ route('admin.users.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-12">
                                    <iconify-icon icon="solar:user-plus-linear"></iconify-icon>
                                    Invite first teammate
                                </a>
                            @endcan
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <aside class="um-layout__aside">
        <section class="um-side-panel">
            <div class="um-side-panel__head">
                <h2 class="um-side-panel__title">Roles at a glance</h2>
                <p class="um-side-panel__sub">Bundle permissions into job titles your team understands.</p>
                @can('view role')
                    <a href="{{ route('admin.roles.index') }}" class="um-side-panel__link">Manage roles</a>
                @endcan
            </div>
            @foreach($roles as $role)
                <div class="um-role-mini {{ UserManagementHelper::isProtectedRole($role) ? 'is-protected' : '' }}">
                    <div class="um-role-mini__head">
                        <span class="um-user-avatar" style="background: {{ UserManagementHelper::avatarGradient($role->name) }};">
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
                </div>
            @endforeach
        </section>

        <div class="um-tip-card">
            <iconify-icon icon="solar:lightbulb-linear"></iconify-icon>
            <div>
                <strong>Tip for school admins</strong>
                <p>Most schools only need 2–3 roles: a full admin, an admissions officer, and a read-only viewer. Start simple — you can always add more later.</p>
            </div>
        </div>
    </aside>
</div>
</div>
@endsection
