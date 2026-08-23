@extends('admin.layouts.app')

@section('title') Edit Role @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
    $isProtected = UserManagementHelper::isProtectedRole($role);
@endphp

@include('admin.role-permission.partials.shell', [
    'activeTab' => 'roles',
    'stats' => $stats,
    'shellTitle' => UserManagementHelper::formatRoleName($role->name),
    'shellSubtitle' => $isProtected
        ? 'System role — you can adjust permissions but not rename or delete it.'
        : 'Update the role name and permission assignments.',
    'shellActions' => [[
        'label' => 'Back to roles',
        'url' => route('admin.roles.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation" novalidate action="{{ route('admin.roles.update', $role->id) }}" method="POST">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="um-panel um-panel--sticky p-24">
                <div class="um-form-section mb-0 border-0 bg-transparent p-0">
                    <div class="um-form-section-title">
                        <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
                        Role details
                    </div>
                    @if($isProtected)
                        <span class="um-system-badge mb-12 d-inline-flex">System role</span>
                    @endif
                    <label class="form-label fw-semibold text-sm">Role name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control radius-8 @error('name') is-invalid @enderror"
                           value="{{ old('name', $role->name) }}"
                           required
                           @disabled($isProtected)>
                    @if($isProtected)
                        <input type="hidden" name="name" value="{{ $role->name }}">
                    @endif
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                    <div class="um-role-mini__stats mt-20">
                        <div>
                            <strong>{{ $role->permissions->count() }}</strong>
                            <span>Current permissions</span>
                        </div>
                        <div>
                            <strong>{{ UserManagementHelper::usersCountForRole($role) }}</strong>
                            <span>Members</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="um-panel p-24">
                <div class="um-form-section-title mb-16">
                    <iconify-icon icon="solar:key-linear"></iconify-icon>
                    Permissions <span class="text-danger">*</span>
                </div>
                @include('admin.role-permission.partials.permission-picker', [
                    'permissions' => $permissions,
                    'groupedPermissions' => $groupedPermissions,
                    'selected' => old('permissions', $rolePermissions),
                ])
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-12 mt-20">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
        <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
            <span>Update role</span>
        </button>
    </div>
</form>
@endsection

@section('script')
@include('admin.role-permission.partials.permission-picker-script')
@endsection
