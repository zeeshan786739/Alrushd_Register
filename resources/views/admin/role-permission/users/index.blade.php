@extends('admin.layouts.app')

@section('title') Users @endsection

@section('content')
    @include('admin.partials.page-header', [
        'title' => 'User Management',
        'subtitle' => 'Manage admin accounts and assign roles for secure access control.',
        'breadcrumbs' => [['label' => 'Users']],
        'actions' => auth()->user()->can('create user') ? [[
            'label' => 'Add User',
            'url' => route('admin.users.create'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:user-plus-linear',
        ]] : [],
    ])

    @include('admin.role-permission.partials.module-nav', ['activeTab' => 'users'])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 px-24 py-16 border-bottom">
                <h6 class="mb-0 fw-semibold fc-panel-title">
                    <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                    Admin Users
                    <span class="fc-badge fc-badge-neutral ms-8">{{ $users->count() }}</span>
                </h6>
                @include('admin.partials.search-bar', ['placeholder' => 'Search name or email…'])
            </div>

            @if($users->isEmpty())
                <div class="um-empty-state">
                    <iconify-icon icon="solar:users-group-rounded-linear" class="d-block mx-auto"></iconify-icon>
                    <h6 class="fw-semibold mb-8">No users yet</h6>
                    <p class="text-secondary-light text-sm mb-0">Add admin users and assign them appropriate roles.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table bordered-table mb-0 align-middle" id="dataTable">
                        <thead>
                            <tr>
                                <th class="ps-24" style="width:60px">#</th>
                                <th>User</th>
                                <th>Roles</th>
                                <th class="text-end pe-24" style="width:120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            @can('view user')
                            <tr class="fc-form-row">
                                <td class="ps-24 fw-semibold text-secondary-light">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="um-user-cell">
                                        <span class="um-user-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                        <div>
                                            <span class="um-user-name d-block">{{ $user->name }}</span>
                                            <span class="um-user-email">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="um-role-badge">{{ ucwords(str_replace(['-','_'], ' ', $role->name)) }}</span>
                                    @empty
                                        <span class="text-secondary-light text-sm">No role</span>
                                    @endforelse
                                </td>
                                <td class="text-end pe-24">
                                    @include('admin.partials.table-actions', [
                                        'editUrl' => route('admin.users.edit', $user->id),
                                        'deleteId' => $user->id,
                                        'deleteRoute' => route('admin.users.destroy', $user->id),
                                        'canView' => false,
                                        'canEdit' => auth()->user()->can('edit user'),
                                        'canDelete' => auth()->user()->can('delete user'),
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
