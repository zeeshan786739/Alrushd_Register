@extends('admin.layouts.app')

@section('title') Roles @endsection

@section('content')
    @include('admin.partials.page-header', [
        'title' => 'User Management',
        'subtitle' => 'Define roles and assign permissions to control access across the CRM.',
        'breadcrumbs' => [['label' => 'Roles']],
        'actions' => auth()->user()->can('create role') ? [[
            'label' => 'Add Role',
            'url' => route('admin.roles.create'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:add-circle-linear',
        ]] : [],
    ])

    @include('admin.role-permission.partials.module-nav', ['activeTab' => 'roles'])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 px-24 py-16 border-bottom">
                <h6 class="mb-0 fw-semibold fc-panel-title">
                    <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
                    Roles
                    <span class="fc-badge fc-badge-neutral ms-8">{{ $roles->count() }}</span>
                </h6>
                @include('admin.partials.search-bar', ['placeholder' => 'Search roles…'])
            </div>

            @if($roles->isEmpty())
                <div class="um-empty-state">
                    <iconify-icon icon="solar:shield-user-linear" class="d-block mx-auto"></iconify-icon>
                    <h6 class="fw-semibold mb-8">No roles yet</h6>
                    <p class="text-secondary-light text-sm mb-0">Create a role to group permissions for your team.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table bordered-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-24" style="width:60px">#</th>
                                <th>Role</th>
                                <th style="width:140px">Permissions</th>
                                <th class="text-end pe-24" style="width:120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            @can('view role')
                            <tr class="fc-form-row">
                                <td class="ps-24 fw-semibold text-secondary-light">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-12">
                                        <span class="um-user-avatar" style="width:32px;height:32px;font-size:11px;border-radius:8px;">
                                            {{ strtoupper(substr($role->name, 0, 2)) }}
                                        </span>
                                        <div>
                                            <span class="um-user-name d-block">{{ ucwords(str_replace(['-','_'], ' ', $role->name)) }}</span>
                                            <span class="text-secondary-light text-xs">{{ $role->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fc-badge fc-badge-primary">{{ $role->permissions->count() }} assigned</span>
                                </td>
                                <td class="text-end pe-24">
                                    @include('admin.partials.table-actions', [
                                        'editUrl' => route('admin.roles.edit', $role->id),
                                        'deleteId' => $role->id,
                                        'deleteRoute' => route('admin.roles.destroy', $role->id),
                                        'canView' => false,
                                        'canEdit' => auth()->user()->can('edit role'),
                                        'canDelete' => auth()->user()->can('delete role'),
                                    ])
                                </td>
                            </tr>
                            @endcan
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
