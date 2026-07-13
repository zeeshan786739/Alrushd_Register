@extends('admin.layouts.app')

@section('title') View Permission @endsection

@section('content')
    @include('admin.partials.page-header', [
        'title' => 'Permission Details',
        'subtitle' => 'View permission information.',
        'breadcrumbs' => [
            ['label' => 'Permissions', 'url' => route('admin.permissions.index')],
            ['label' => $permission->name],
        ],
        'actions' => [
            ['label' => 'Back', 'url' => route('admin.permissions.index'), 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11', 'icon' => 'solar:alt-arrow-left-linear'],
            ['label' => 'Edit', 'url' => route('admin.permissions.edit', $permission->id), 'class' => 'btn-primary-600 radius-8 px-20 py-11', 'icon' => 'solar:pen-linear'],
        ],
    ])

    @include('admin.role-permission.partials.module-nav', ['activeTab' => 'permissions'])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-24">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-sm fw-semibold text-secondary-light">Permission Name</label>
                    <p class="fw-semibold mb-0">{{ $permission->name }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-sm fw-semibold text-secondary-light">Guard</label>
                    <p class="mb-0"><span class="um-guard-badge">{{ $permission->guard_name }}</span></p>
                </div>
            </div>
        </div>
    </div>
@endsection
