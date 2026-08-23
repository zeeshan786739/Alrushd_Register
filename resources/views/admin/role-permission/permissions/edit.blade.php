@extends('admin.layouts.app')

@section('title') Edit Permission @endsection

@section('content')
@include('admin.role-permission.partials.shell', [
    'activeTab' => 'permissions',
    'stats' => $stats,
    'shellTitle' => 'Edit permission',
    'shellSubtitle' => 'Update the permission identifier used in role assignments.',
    'shellActions' => [[
        'label' => 'Back to permissions',
        'url' => route('admin.permissions.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="um-panel p-24">
            <form class="needs-validation" novalidate action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                @csrf @method('PUT')
                <label class="form-label fw-semibold text-sm">Permission name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control radius-8 @error('name') is-invalid @enderror"
                       value="{{ old('name', $permission->name) }}" required>
                @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                <label class="form-label fw-semibold text-sm mt-16">Guard</label>
                <input type="text" class="form-control radius-8" value="{{ $permission->guard_name }}" disabled>

                <div class="d-flex justify-content-end gap-12 mt-24">
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
                        <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                        <span>Update permission</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
