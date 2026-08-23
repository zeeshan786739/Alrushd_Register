@extends('admin.layouts.app')

@section('title') Edit Teammate @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
    $isSelf = (int) $user->id === (int) auth('admin')->id();
@endphp

@include('admin.role-permission.partials.shell', [
    'activeTab' => 'users',
    'stats' => $stats,
    'shellTitle' => $user->name,
    'shellSubtitle' => $isSelf ? 'You are editing your own account.' : 'Update account details and role assignments.',
    'shellActions' => [[
        'label' => 'Back to team',
        'url' => route('admin.users.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation" novalidate action="{{ route('admin.users.update', $user->id) }}" method="POST">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="um-panel p-24">
                <div class="d-flex align-items-center gap-12 mb-20">
                    <span class="um-user-avatar um-user-avatar--lg" style="background: {{ UserManagementHelper::avatarGradient($user->email) }};">
                        {{ UserManagementHelper::initials($user->name) }}
                    </span>
                    <div>
                        <strong class="d-block">{{ $user->email }}</strong>
                        @if($user->last_login_at)
                            <span class="text-secondary-light text-sm">Last login {{ $user->last_login_at->diffForHumans() }}</span>
                        @else
                            <span class="text-secondary-light text-sm">Never signed in</span>
                        @endif
                    </div>
                </div>

                <div class="um-form-section-title">
                    <iconify-icon icon="solar:user-linear"></iconify-icon>
                    Account details
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-sm">Full name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control radius-8 @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-sm">Work email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control radius-8 @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sm">New password</label>
                        <input type="password" name="password" class="form-control radius-8 @error('password') is-invalid @enderror"
                               placeholder="Leave blank to keep">
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sm">Confirm password</label>
                        <input type="password" name="password_confirmation" class="form-control radius-8" placeholder="Leave blank to keep">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="um-panel p-24">
                <div class="um-form-section-title">
                    <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
                    Assign roles <span class="text-danger">*</span>
                </div>

                <div class="um-role-picker">
                    @foreach($roles as $role)
                    <label class="um-role-picker__item" for="role_{{ $role->id }}">
                        <input type="checkbox"
                               class="form-check-input"
                               name="roles[]"
                               value="{{ $role->name }}"
                               id="role_{{ $role->id }}"
                               {{ in_array($role->name, old('roles', $userRoles), true) ? 'checked' : '' }}>
                        <span class="um-role-picker__avatar" style="background: {{ UserManagementHelper::avatarGradient($role->name) }};">
                            {{ UserManagementHelper::initials($role->name) }}
                        </span>
                        <span class="um-role-picker__body">
                            <strong>{{ UserManagementHelper::formatRoleName($role->name) }}</strong>
                            <span>{{ UserManagementHelper::isProtectedRole($role) ? 'Full system access' : 'Custom role' }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>
                @error('roles')<div class="text-danger text-sm mt-8">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-12 mt-20">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
        <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
            <span>Save changes</span>
        </button>
    </div>
</form>
@endsection
