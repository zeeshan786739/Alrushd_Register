@extends('admin.layouts.app')

@section('title') Create Role @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
@endphp

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

<form class="needs-validation" novalidate action="{{ route('admin.roles.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="um-panel um-panel--sticky p-24">
                <div class="um-form-section mb-0 border-0 bg-transparent p-0">
                    <div class="um-form-section-title">
                        <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
                        Role details
                    </div>
                    <label class="form-label fw-semibold text-sm">Role name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control radius-8 @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           required
                           placeholder="e.g. Admissions Officer">
                    <small class="text-secondary-light d-block mt-8">Use a job title your team will recognize.</small>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                    'selected' => old('permissions', []),
                ])
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-12 mt-20">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
        <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
            <span>Save role</span>
        </button>
    </div>
</form>
@endsection

@section('script')
@include('admin.role-permission.partials.permission-picker-script')
@endsection
