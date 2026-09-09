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

<div class="dashboard-main-body" id="um-workspace-page">
@include('admin.role-permission.partials.shell', [
    'activeTab' => 'roles',
    'stats' => $stats,
    'shellTitle' => 'Roles',
    'shellSubtitle' => 'Group permissions into roles like Admin, Admissions Officer, or Finance.',
    'shellActions' => $shellActions,
])

@if($roles->isEmpty())
    <div class="crm-list-shell">
        <div class="crm-leads-list-empty">
            <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
            <strong>No roles yet</strong>
            <span>Create your first role to define what each job title can access.</span>
            @can('create role')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn mt-12">
                    <iconify-icon icon="solar:shield-plus-linear"></iconify-icon>
                    Create role
                </a>
            @endcan
        </div>
    </div>
@else
    <div class="um-filter-workspace um-filter-workspace--standalone um-search-scope">
        <div class="um-ai-search">
            <div class="um-ai-search__head">
                <span class="um-ai-search__badge"><iconify-icon icon="solar:magnifer-linear"></iconify-icon> Find</span>
            </div>
            <div class="um-ai-search__shell">
                <span class="um-ai-search__icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                <input type="search"
                       class="um-ai-search__input um-table-search"
                       placeholder="Search roles by name…"
                       aria-label="Search roles">
            </div>
        </div>
    </div>

    <div class="crm-leads-toolbar">
        <div class="crm-leads-toolbar__meta">
            <strong>{{ number_format($roles->count()) }} role{{ $roles->count() === 1 ? '' : 's' }}</strong>
            <span>Permission bundles assigned to teammates</span>
        </div>
    </div>

    <div class="crm-list-shell um-search-scope">
        <div class="crm-leads-table">
            <div class="crm-leads-table__head crm-leads-table__head--roles" aria-hidden="true">
                <span>Role</span><span>Permissions</span><span>Members</span><span>Coverage</span><span></span>
            </div>
            <div class="crm-leads-list">
                @foreach($roles as $role)
                    @include('admin.role-permission.partials.role-row', ['role' => $role, 'permTotal' => $stats['permissions'] ?? 1])
                @endforeach
            </div>
        </div>
    </div>
@endif
</div>
@endsection
