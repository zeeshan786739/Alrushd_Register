@extends('admin.layouts.app')

@section('title') Add Role @endsection

@section('content')
    @include('admin.partials.page-header', [
        'title' => 'Create Role',
        'subtitle' => 'Define a new role and assign permissions.',
        'breadcrumbs' => [
            ['label' => 'Roles', 'url' => route('admin.roles.index')],
            ['label' => 'Create'],
        ],
        'actions' => [['label' => 'Back', 'url' => route('admin.roles.index'), 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11', 'icon' => 'solar:alt-arrow-left-linear']],
    ])

    @include('admin.role-permission.partials.module-nav', ['activeTab' => 'roles'])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-24">
            <form class="needs-validation" novalidate action="{{ route('admin.roles.store') }}" method="POST">
                @csrf

                <div class="um-form-section">
                    <div class="um-form-section-title">
                        <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
                        Role Details
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control radius-8 @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="e.g. Content Manager">
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="um-form-section">
                    <div class="um-form-section-title">
                        <iconify-icon icon="solar:key-linear"></iconify-icon>
                        Assign Permissions <span class="text-danger">*</span>
                    </div>
                    <div class="um-perm-grid">
                        @foreach($permissions as $permission)
                        <label class="um-perm-item" for="permission_{{ $permission->id }}">
                            <input type="checkbox" class="form-check-input flex-shrink-0 mt-1"
                                   id="permission_{{ $permission->id }}" name="permissions[]"
                                   value="{{ $permission->name }}"
                                   {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                            <span class="text-sm">{{ ucwords(str_replace('_', ' ', $permission->name)) }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('permissions')<div class="text-danger text-sm mt-8">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex justify-content-end gap-12">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
                        <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                        <span>Save Role</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
