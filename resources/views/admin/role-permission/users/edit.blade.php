@extends('admin.layouts.app')

@section('title') Edit User @endsection

@section('content')
    @include('admin.partials.page-header', [
        'title' => 'Edit User',
        'subtitle' => 'Update account details and role assignments.',
        'breadcrumbs' => [
            ['label' => 'Users', 'url' => route('admin.users.index')],
            ['label' => $user->name],
        ],
        'actions' => [['label' => 'Back', 'url' => route('admin.users.index'), 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11', 'icon' => 'solar:alt-arrow-left-linear']],
    ])

    @include('admin.role-permission.partials.module-nav', ['activeTab' => 'users'])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-24">
            <form class="needs-validation" novalidate action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="um-form-section">
                    <div class="um-form-section-title">
                        <iconify-icon icon="solar:user-linear"></iconify-icon>
                        Account Details
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control radius-8 @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control radius-8 @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">New Password</label>
                            <input type="password" name="password" class="form-control radius-8 @error('password') is-invalid @enderror"
                                   placeholder="Leave blank to keep current">
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control radius-8" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                </div>

                <div class="um-form-section">
                    <div class="um-form-section-title">
                        <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
                        Assign Roles <span class="text-danger">*</span>
                    </div>
                    <div class="row g-2">
                        @foreach($roles as $role)
                        <div class="col-md-4 col-sm-6">
                            <label class="um-perm-item w-100" for="role_{{ $role->id }}">
                                <input type="checkbox" class="form-check-input flex-shrink-0 mt-1"
                                       name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}"
                                       {{ in_array($role->name, old('roles', $userRoles)) ? 'checked' : '' }}>
                                <span class="text-sm">{{ ucwords(str_replace(['-','_'], ' ', $role->name)) }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('roles')<div class="text-danger text-sm mt-8">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex justify-content-end gap-12">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
                        <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                        <span>Update User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
