@extends('admin.layouts.app')

@section('title') Create Role @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
@endphp

<div class="dashboard-main-body" id="um-workspace-page">
@include('admin.role-permission.partials.shell', [
    'activeTab' => 'roles',
    'stats' => $stats,
    'shellTitle' => 'Create role',
    'shellSubtitle' => 'Name the role, then choose what this job title can access.',
    'shellActions' => [[
        'label' => 'Back to roles',
        'url' => route('admin.roles.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation um-form-page" novalidate action="{{ route('admin.roles.store') }}" method="POST">
    @csrf

    <div class="um-form-grid um-form-grid--role">
        <section class="um-form-card um-form-card--sticky">
            <div class="um-form-card__head">
                <span class="um-form-card__icon"><iconify-icon icon="solar:shield-user-linear"></iconify-icon></span>
                <div>
                    <h2 class="um-form-card__title">Role details</h2>
                    <p class="um-form-card__sub">Use a job title your team will recognize</p>
                </div>
            </div>
            <div class="um-form-card__body">
                <div class="um-form-field">
                    <label class="um-form-field__label">Role name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control radius-8 @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           required
                           placeholder="e.g. Admissions Officer">
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>

        <section class="um-form-card um-form-card--wide">
            <div class="um-form-card__head">
                <span class="um-form-card__icon"><iconify-icon icon="solar:key-linear"></iconify-icon></span>
                <div>
                    <h2 class="um-form-card__title">Permissions <span class="text-danger">*</span></h2>
                    <p class="um-form-card__sub">Select capabilities for this role — grouped by module</p>
                </div>
            </div>
            <div class="um-form-card__body um-form-card__body--flush">
                @include('admin.role-permission.partials.permission-picker', [
                    'permissions' => $permissions,
                    'groupedPermissions' => $groupedPermissions,
                    'selected' => old('permissions', []),
                ])
            </div>
        </section>
    </div>

    <div class="um-form-save-bar">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
        <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
            <span>Save role</span>
        </button>
    </div>
</form>
</div>
@endsection

@section('script')
@include('admin.role-permission.partials.permission-picker-script')
@endsection
