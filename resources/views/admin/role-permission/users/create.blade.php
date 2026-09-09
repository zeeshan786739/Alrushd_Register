@extends('admin.layouts.app')

@section('title') Invite Teammate @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
@endphp

<div class="dashboard-main-body" id="um-workspace-page">
@include('admin.role-permission.partials.shell', [
    'activeTab' => 'users',
    'stats' => $stats,
    'shellTitle' => 'Invite teammate',
    'shellSubtitle' => 'Invite a teammate by email and assign roles. They will enter their name and password when accepting.',
    'shellActions' => [[
        'label' => 'Back to team',
        'url' => route('admin.users.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation um-form-page" novalidate action="{{ route('admin.users.store') }}" method="POST">
    @csrf

    <div class="um-form-grid">
        <section class="um-form-card">
            <div class="um-form-card__head">
                <span class="um-form-card__icon"><iconify-icon icon="solar:user-linear"></iconify-icon></span>
                <div>
                    <h2 class="um-form-card__title">Account details</h2>
                    <p class="um-form-card__sub">Invite by email — they will enter their name when accepting</p>
                </div>
            </div>
            <div class="um-form-card__body">
                <div class="um-form-fields">
                    <div class="um-form-field">
                        <label class="um-form-field__label">Work email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control radius-8 @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required placeholder="sarah@school.com">
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="um-form-note">
                    <iconify-icon icon="solar:letter-opened-linear"></iconify-icon>
                    <span>A secure setup link will be emailed. Your teammate will enter their first name, last name, and password, then sign in automatically.</span>
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
                                       {{ in_array($role->name, old('roles', []), true) ? 'checked' : '' }}>
                                <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
                                <span class="um-role-picker-row__identity">
                                    <span class="um-role-picker-row__avatar" style="background: {{ UserManagementHelper::avatarGradient($role->name) }};">
                                        {{ UserManagementHelper::initials($role->name) }}
                                    </span>
                                    <span class="um-role-picker-row__body">
                                        <strong>{{ UserManagementHelper::formatRoleName($role->name) }}</strong>
                                        <span>Assign this role to the invite</span>
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
            <iconify-icon icon="solar:user-plus-linear"></iconify-icon>
            <span>Send invite</span>
        </button>
    </div>
</form>
</div>
@endsection
