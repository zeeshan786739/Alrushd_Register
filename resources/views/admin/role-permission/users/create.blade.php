@extends('admin.layouts.app')

@section('title') Invite Teammate @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
@endphp

@include('admin.role-permission.partials.shell', [
    'activeTab' => 'users',
    'stats' => $stats,
    'shellTitle' => 'Invite teammate',
    'shellSubtitle' => 'Create an admin account and assign one or more roles.',
    'shellActions' => [[
        'label' => 'Back to team',
        'url' => route('admin.users.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation" novalidate action="{{ route('admin.users.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="um-panel p-24">
                <div class="um-form-section-title">
                    <iconify-icon icon="solar:user-linear"></iconify-icon>
                    Account details
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-sm">Full name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control radius-8 @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required placeholder="Sarah Ahmed">
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-sm">Work email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control radius-8 @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required placeholder="sarah@school.com">
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sm">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control radius-8 @error('password') is-invalid @enderror" required minlength="8">
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sm">Confirm password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control radius-8" required minlength="8">
                    </div>
                </div>
                <p class="text-secondary-light text-sm mt-12 mb-0">Minimum 8 characters. Share credentials securely with your teammate.</p>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="um-panel p-24">
                <div class="um-form-section-title">
                    <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
                    Assign roles <span class="text-danger">*</span>
                </div>
                <p class="text-secondary-light text-sm mb-16">Choose what this person can do. Most teammates only need one role.</p>

                <div class="um-role-picker">
                    @foreach($roles as $role)
                    <label class="um-role-picker__item" for="role_{{ $role->id }}">
                        <input type="checkbox"
                               class="form-check-input"
                               name="roles[]"
                               value="{{ $role->name }}"
                               id="role_{{ $role->id }}"
                               {{ in_array($role->name, old('roles', []), true) ? 'checked' : '' }}>
                        <span class="um-role-picker__avatar" style="background: {{ UserManagementHelper::avatarGradient($role->name) }};">
                            {{ UserManagementHelper::initials($role->name) }}
                        </span>
                        <span class="um-role-picker__body">
                            <strong>{{ UserManagementHelper::formatRoleName($role->name) }}</strong>
                            <span>{{ $role->permissions_count ?? $role->permissions?->count() ?? '—' }} permissions</span>
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
            <iconify-icon icon="solar:user-plus-linear"></iconify-icon>
            <span>Send invite</span>
        </button>
    </div>
</form>
@endsection
