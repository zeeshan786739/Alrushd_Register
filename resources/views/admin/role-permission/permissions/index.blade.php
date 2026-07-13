@extends('admin.layouts.app')

@section('title') Permissions @endsection

@section('content')
    @include('admin.partials.page-header', [
        'title' => 'User Management',
        'subtitle' => 'Manage granular permissions that control what each role can do.',
        'breadcrumbs' => [['label' => 'Permissions']],
        'actions' => auth()->user()->can('create permission') ? [[
            'label' => 'Add Permission',
            'url' => route('admin.permissions.create'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:add-circle-linear',
        ]] : [],
    ])

    @include('admin.role-permission.partials.module-nav', ['activeTab' => 'permissions'])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 px-24 py-16 border-bottom">
                <h6 class="mb-0 fw-semibold fc-panel-title">
                    <iconify-icon icon="solar:key-linear"></iconify-icon>
                    Permissions
                    <span class="fc-badge fc-badge-neutral ms-8">{{ $permissions->count() }}</span>
                </h6>
                @include('admin.partials.search-bar', ['placeholder' => 'Search permissions…'])
            </div>

            @if($permissions->isEmpty())
                <div class="um-empty-state">
                    <iconify-icon icon="solar:key-linear" class="d-block mx-auto"></iconify-icon>
                    <h6 class="fw-semibold mb-8">No permissions found</h6>
                    <p class="text-secondary-light text-sm mb-0">Add permissions to build your access control model.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table bordered-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-24" style="width:60px">#</th>
                                <th>Permission</th>
                                <th style="width:120px">Guard</th>
                                <th class="text-end pe-24" style="width:120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $permission)
                            @can('view permission')
                            <tr class="fc-form-row">
                                <td class="ps-24 fw-semibold text-secondary-light">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-semibold">{{ str_replace('_', ' ', $permission->name) }}</span>
                                    <span class="d-block text-secondary-light text-xs mt-2">{{ $permission->name }}</span>
                                </td>
                                <td><span class="um-guard-badge">{{ $permission->guard_name }}</span></td>
                                <td class="text-end pe-24">
                                    @include('admin.partials.table-actions', [
                                        'editUrl' => route('admin.permissions.edit', $permission->id),
                                        'deleteId' => $permission->id,
                                        'deleteRoute' => route('admin.permissions.destroy', $permission->id),
                                        'canView' => false,
                                        'canEdit' => auth()->user()->can('edit permission'),
                                        'canDelete' => auth()->user()->can('delete permission'),
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
