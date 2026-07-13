@extends('admin.layouts.app')

@section('title') Add Permission @endsection

@section('content')
    @include('admin.partials.page-header', [
        'title' => 'Create Permission',
        'subtitle' => 'Add a new permission to the access control system.',
        'breadcrumbs' => [
            ['label' => 'Permissions', 'url' => route('admin.permissions.index')],
            ['label' => 'Create'],
        ],
        'actions' => [['label' => 'Back', 'url' => route('admin.permissions.index'), 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11', 'icon' => 'solar:alt-arrow-left-linear']],
    ])

    @include('admin.role-permission.partials.module-nav', ['activeTab' => 'permissions'])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-24">
            <form class="needs-validation" novalidate action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <div class="um-form-section">
                    <div class="um-form-section-title">
                        <iconify-icon icon="solar:key-linear"></iconify-icon>
                        Permission Details
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control radius-8 @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="e.g. view reports">
                            <small class="text-secondary-light">Use lowercase with underscores: view_users, edit_settings</small>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-12">
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
                        <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                        <span>Save Permission</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
