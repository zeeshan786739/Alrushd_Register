@extends('admin.layouts.app')

@section('title') Roles @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
    $shellActions = auth()->user()->can('create role') ? [[
        'label' => 'Create role',
        'url' => route('admin.roles.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:shield-plus-linear',
    ]] : [];
@endphp

@include('admin.role-permission.partials.shell', [
    'activeTab' => 'roles',
    'stats' => $stats,
    'shellTitle' => 'Roles',
    'shellSubtitle' => 'Group permissions into roles like Admin, Admissions Officer, or Finance.',
    'shellActions' => $shellActions,
])

@if($roles->isEmpty())
    <div class="um-panel">
        <div class="um-empty-state um-empty-state--panel">
            <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
            <h6>No roles yet</h6>
            <p>Create your first role to define what each job title can access.</p>
            @can('create role')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-12">
                <iconify-icon icon="solar:shield-plus-linear"></iconify-icon>
                Create role
            </a>
            @endcan
        </div>
    </div>
@else
    <div class="um-search-scope card shadow-2 radius-12 border-0 p-24">
        <div class="um-search-toolbar mb-20">
            <div class="um-search-bar um-search-bar--wide">
                <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                <input type="search"
                       class="form-control radius-8 um-table-search"
                       placeholder="Search roles by name…"
                       aria-label="Search roles">
            </div>
        </div>

        <div class="um-role-grid">
        @foreach($roles as $role)
        @php
            $isProtected = UserManagementHelper::isProtectedRole($role);
            $permTotal = $stats['permissions'] ?: 1;
            $coverage = min(100, round(($role->permissions->count() / $permTotal) * 100));
        @endphp
        <article class="um-role-card {{ $isProtected ? 'is-protected' : '' }}">
            <div class="um-role-card__top">
                <span class="um-user-avatar" style="background: {{ UserManagementHelper::avatarGradient($role->name) }};">
                    {{ UserManagementHelper::initials($role->name) }}
                </span>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-8 flex-wrap">
                        <h3 class="um-role-card__name">{{ UserManagementHelper::formatRoleName($role->name) }}</h3>
                        @if($isProtected)
                            <span class="um-system-badge">System role</span>
                        @endif
                    </div>
                    <p class="um-role-card__slug">{{ $role->name }}</p>
                </div>
            </div>

            <div class="um-role-card__metrics">
                <div>
                    <strong>{{ $role->permissions->count() }}</strong>
                    <span>Permissions</span>
                </div>
                <div>
                    <strong>{{ $role->users_count }}</strong>
                    <span>Members</span>
                </div>
            </div>

            <div class="um-role-card__progress" aria-hidden="true">
                <span style="width: {{ $coverage }}%"></span>
            </div>
            <p class="um-role-card__coverage">{{ $coverage }}% of all permissions</p>

            <div class="um-role-card__actions">
                @can('edit role')
                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary-600 radius-8 px-16 py-10 fc-btn flex-grow-1">
                    <iconify-icon icon="solar:pen-linear"></iconify-icon>
                    Edit role
                </a>
                @endcan
                @if(auth()->user()->can('delete role') && ! $isProtected && $role->users_count === 0)
                    @include('admin.partials.table-actions', [
                        'editUrl' => null,
                        'deleteId' => $role->id,
                        'deleteRoute' => route('admin.roles.destroy', $role->id),
                        'canView' => false,
                        'canEdit' => false,
                        'canDelete' => true,
                    ])
                @endif
            </div>
        </article>
        @endforeach
        </div>
    </div>
@endif
@endsection
