@extends('admin.layouts.app')

@section('title') Edit Teammate @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
    $isSelf = (int) $user->id === (int) auth('admin')->id();
@endphp

<div class="dashboard-main-body" id="um-workspace-page">
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

<form class="needs-validation um-form-page" novalidate action="{{ route('admin.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="um-form-grid">
        <section class="um-form-card">
            <div class="um-form-card__head">
                <span class="um-form-card__icon"><iconify-icon icon="solar:user-linear"></iconify-icon></span>
                <div>
                    <h2 class="um-form-card__title">Account details</h2>
                    <p class="um-form-card__sub">Profile and sign-in credentials</p>
                </div>
            </div>
            <div class="um-form-card__body">
                <div class="d-flex align-items-center gap-12 mb-16 pb-16 border-bottom">
                    <span class="um-user-avatar um-user-avatar--lg" style="background: {{ UserManagementHelper::avatarGradient($user->email) }};">
                        {{ UserManagementHelper::initials($user->name) }}
                    </span>
                    <div class="min-w-0">
                        <strong class="d-block text-truncate">{{ $user->email }}</strong>
                        @if($user->last_login_at)
                            <span class="text-secondary-light text-sm">Last login {{ $user->last_login_at->diffForHumans() }}</span>
                        @else
                            <span class="text-secondary-light text-sm">Never signed in</span>
                        @endif
                    </div>
                </div>

                <div class="um-form-fields">
                    <div class="um-form-field">
                        <label class="um-form-field__label">Full name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control radius-8 @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="um-form-field">
                        <label class="um-form-field__label">Work email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control radius-8 @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="um-form-field">
                        <label class="um-form-field__label">New password</label>
                        <input type="password" name="password" class="form-control radius-8 @error('password') is-invalid @enderror"
                               placeholder="Leave blank to keep">
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="um-form-field">
                        <label class="um-form-field__label">Confirm password</label>
                        <input type="password" name="password_confirmation" class="form-control radius-8" placeholder="Leave blank to keep">
                    </div>
                </div>
            </div>
        </section>

        <section class="um-form-card um-form-card--wide">
            <div class="um-form-card__head">
                <span class="um-form-card__icon"><iconify-icon icon="solar:shield-user-linear"></iconify-icon></span>
                <div>
                    <h2 class="um-form-card__title">Assign roles <span class="text-danger">*</span></h2>
                    <p class="um-form-card__sub">Choose what this person can do. Most teammates only need one role.</p>
                </div>
            </div>
            <div class="um-form-card__body um-form-card__body--flush">
                <div class="crm-list-shell um-form-list-shell">
                    <div class="crm-leads-table">
                        <div class="crm-leads-table__head crm-leads-table__head--picker" aria-hidden="true">
                            <span>Role</span><span>Access</span><span></span>
                        </div>
                        <div class="crm-leads-list">
                            @foreach($roles as $role)
                            <label class="um-role-picker-row" for="role_{{ $role->id }}">
                                <input type="checkbox"
                                       class="um-role-picker-row__check"
                                       name="roles[]"
                                       value="{{ $role->name }}"
                                       id="role_{{ $role->id }}"
                                       {{ in_array($role->name, old('roles', $userRoles), true) ? 'checked' : '' }}>
                                <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
                                <span class="um-role-picker-row__identity">
                                    <span class="um-role-picker-row__avatar" style="background: {{ UserManagementHelper::avatarGradient($role->name) }};">
                                        {{ UserManagementHelper::initials($role->name) }}
                                    </span>
                                    <span class="um-role-picker-row__body">
                                        <strong>{{ UserManagementHelper::formatRoleName($role->name) }}</strong>
                                        <span>{{ UserManagementHelper::isProtectedRole($role) ? 'Full system access' : 'Custom role' }}</span>
                                    </span>
                                </span>
                                <span class="um-role-picker-row__meta">{{ $role->permissions_count ?? $role->permissions?->count() ?? '—' }} permissions</span>
                                <span class="um-role-picker-row__mark" aria-hidden="true">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @error('roles')<div class="text-danger text-sm um-form-card__error">{{ $message }}</div>@enderror
            </div>
        </section>
    </div>

    <div class="um-form-save-bar">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
        <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
            <span>Save changes</span>
        </button>
    </div>
</form>
</div>
@endsection
