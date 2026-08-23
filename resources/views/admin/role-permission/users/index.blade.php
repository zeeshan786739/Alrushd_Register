@extends('admin.layouts.app')

@section('title') Team @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
    $shellActions = auth()->user()->can('create user') ? [[
        'label' => 'Invite teammate',
        'url' => route('admin.users.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:user-plus-linear',
    ]] : [];
@endphp

@include('admin.role-permission.partials.shell', [
    'activeTab' => 'users',
    'stats' => $stats,
    'shellTitle' => 'Team members',
    'shellSubtitle' => 'Invite staff, assign roles, and manage who can access your admin panel.',
    'shellActions' => $shellActions,
])

<div class="um-panel">
    <div class="um-panel__toolbar">
        <div class="um-search-bar um-search-bar--wide">
            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
            <input type="search"
                   class="form-control radius-8 um-table-search"
                   placeholder="Search by name or email…"
                   aria-label="Search team members">
        </div>

        @if($roles->isNotEmpty())
        <form method="GET" action="{{ route('admin.users.index') }}" class="um-filter-form">
            <select name="role" class="form-select radius-8" onchange="this.form.submit()" aria-label="Filter by role">
                <option value="">All roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                        {{ UserManagementHelper::formatRoleName($role->name) }}
                    </option>
                @endforeach
            </select>
        </form>
        @endif
    </div>

    @if($users->isEmpty())
        <div class="um-empty-state um-empty-state--panel">
            <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
            <h6>No team members found</h6>
            <p>@if(request('role')) Try clearing the role filter or @endif invite someone to get started.</p>
            @can('create user')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-12">
                <iconify-icon icon="solar:user-plus-linear"></iconify-icon>
                Invite teammate
            </a>
            @endcan
        </div>
    @else
        <div class="table-responsive">
            <table class="table um-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-24">Member</th>
                        <th>Roles</th>
                        <th>Last login</th>
                        <th class="text-end pe-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="fc-form-row">
                        <td class="ps-24">
                            <div class="um-user-cell">
                                <span class="um-user-avatar" style="background: {{ UserManagementHelper::avatarGradient($user->email) }};">
                                    {{ UserManagementHelper::initials($user->name) }}
                                </span>
                                <div>
                                    <span class="um-user-name d-block">
                                        {{ $user->name }}
                                        @if((int) $user->id === (int) auth('admin')->id())
                                            <span class="um-you-badge">You</span>
                                        @endif
                                    </span>
                                    <span class="um-user-email">{{ $user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @forelse($user->roles as $role)
                                <span class="um-role-badge">{{ UserManagementHelper::formatRoleName($role->name) }}</span>
                            @empty
                                <span class="text-secondary-light text-sm">No role assigned</span>
                            @endforelse
                        </td>
                        <td class="text-secondary-light text-sm">
                            @if($user->last_login_at)
                                <span title="{{ $user->last_login_at->format('d M Y, H:i') }}">
                                    {{ $user->last_login_at->diffForHumans() }}
                                </span>
                            @else
                                <span class="um-muted-pill">Never</span>
                            @endif
                        </td>
                        <td class="text-end pe-24">
                            @include('admin.partials.table-actions', [
                                'editUrl' => route('admin.users.edit', $user->id),
                                'deleteId' => $user->id,
                                'deleteRoute' => route('admin.users.destroy', $user->id),
                                'canView' => false,
                                'canEdit' => auth()->user()->can('edit user'),
                                'canDelete' => auth()->user()->can('delete user') && (int) $user->id !== (int) auth('admin')->id(),
                            ])
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
