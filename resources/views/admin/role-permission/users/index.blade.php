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

<div class="dashboard-main-body" id="um-workspace-page">
@include('admin.role-permission.partials.shell', [
    'activeTab' => 'users',
    'stats' => $stats,
    'shellTitle' => 'Team members',
    'shellSubtitle' => 'Invite staff, assign roles, and manage who can access your admin panel.',
    'shellActions' => $shellActions,
])

<div class="um-filter-workspace um-search-scope">
    <div class="um-filter-grid">
        <div class="um-ai-search">
            <div class="um-ai-search__head">
                <span class="um-ai-search__badge"><iconify-icon icon="solar:magnifer-linear"></iconify-icon> Find</span>
            </div>
            <div class="um-ai-search__shell">
                <span class="um-ai-search__icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                <input type="search"
                       class="um-ai-search__input um-table-search"
                       placeholder="Search by name or email…"
                       aria-label="Search team members">
            </div>
        </div>
        @if($roles->isNotEmpty())
            <form method="GET" action="{{ route('admin.users.index') }}" class="um-filter-field">
                <label for="um-role-filter">Role</label>
                <select id="um-role-filter" name="role" class="form-select" onchange="this.form.submit()">
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
</div>

<div class="crm-leads-toolbar">
    <div class="crm-leads-toolbar__meta">
        <strong>{{ number_format($users->count()) }} team member{{ $users->count() === 1 ? '' : 's' }}</strong>
        @if(request('role'))
            <span>Filtered by role</span>
        @endif
    </div>
</div>

<div class="crm-list-shell um-search-scope">
    <div class="crm-leads-table">
        <div class="crm-leads-table__head crm-leads-table__head--team" aria-hidden="true">
            <span>Member</span><span>Roles</span><span>Last login</span><span></span>
        </div>
        <div class="crm-leads-list">
            @forelse($users as $user)
                @include('admin.role-permission.partials.team-row', ['user' => $user])
            @empty
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                    <strong>No team members found</strong>
                    <span>@if(request('role')) Try clearing the role filter or @endif invite someone to get started.</span>
                    @can('create user')
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-12">
                            <iconify-icon icon="solar:user-plus-linear"></iconify-icon>
                            Invite teammate
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>
</div>
</div>
@endsection
