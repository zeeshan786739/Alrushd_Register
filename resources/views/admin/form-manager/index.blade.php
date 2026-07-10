@extends('admin.layouts.app')

@section('title') Form Center @endsection

@section('content')
@include('admin.form-manager.partials.styles')

<div class="dashboard-main-body">
    @include('admin.form-manager.partials.header', [
        'title' => 'Form Center',
        'subtitle' => 'Create, customize, and manage every form and submission from one place.',
        'actions' => [
            ['label' => 'Create New Form', 'url' => route('admin.form-manager.create'), 'class' => 'btn-primary-600 radius-8 px-20 py-11', 'icon' => 'solar:add-circle-linear'],
        ],
    ])

    <div class="row gy-4 mb-24">
        <div class="col-xxl-3 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 h-100 bg-gradient-end-1 fc-stat-card">
                <div class="card-body p-0 d-flex align-items-center gap-3">
                    <span class="w-48-px h-48-px bg-primary-600 text-white d-flex justify-content-center align-items-center rounded-circle">
                        <iconify-icon icon="solar:document-text-linear" class="text-xl"></iconify-icon>
                    </span>
                    <div>
                        <span class="fw-medium text-secondary-light text-sm d-block mb-4">Total Forms</span>
                        <h6 class="fw-bold mb-0 text-lg">{{ $stats['total_forms'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 h-100 bg-gradient-end-2 fc-stat-card">
                <div class="card-body p-0 d-flex align-items-center gap-3">
                    <span class="w-48-px h-48-px bg-success-main text-white d-flex justify-content-center align-items-center rounded-circle">
                        <iconify-icon icon="solar:check-circle-linear" class="text-xl"></iconify-icon>
                    </span>
                    <div>
                        <span class="fw-medium text-secondary-light text-sm d-block mb-4">Active Forms</span>
                        <h6 class="fw-bold mb-0 text-lg">{{ $stats['active_forms'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 h-100 bg-gradient-end-3 fc-stat-card">
                <div class="card-body p-0 d-flex align-items-center gap-3">
                    <span class="w-48-px h-48-px bg-yellow text-white d-flex justify-content-center align-items-center rounded-circle">
                        <iconify-icon icon="solar:global-linear" class="text-xl"></iconify-icon>
                    </span>
                    <div>
                        <span class="fw-medium text-secondary-light text-sm d-block mb-4">On Landing Page</span>
                        <h6 class="fw-bold mb-0 text-lg">{{ $stats['landing_forms'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 h-100 bg-gradient-end-4 fc-stat-card">
                <div class="card-body p-0 d-flex align-items-center gap-3">
                    <span class="w-48-px h-48-px bg-purple text-white d-flex justify-content-center align-items-center rounded-circle">
                        <iconify-icon icon="solar:inbox-linear" class="text-xl"></iconify-icon>
                    </span>
                    <div>
                        <span class="fw-medium text-secondary-light text-sm d-block mb-4">Total Submissions</span>
                        <h6 class="fw-bold mb-0 text-lg">{{ number_format($stats['total_submissions']) }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($forms->isEmpty())
    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body text-center py-60 px-24">
            <div class="w-72-px h-72-px bg-primary-50 text-primary-600 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-16">
                <iconify-icon icon="solar:document-add-linear" class="text-3xl"></iconify-icon>
            </div>
            <h6 class="fw-semibold mb-8">Form Center is not set up yet</h6>
            <p class="text-secondary-light text-sm mb-20 max-w-500-px mx-auto">Run the setup command to import all existing forms and submission data into the unified system.</p>
            <code class="d-inline-block bg-neutral-100 px-16 py-10 radius-8 text-sm mb-24">php artisan forms:setup</code>
            <div>
                <a href="{{ route('admin.form-manager.create') }}" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
                    <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                    <span>Create your first form</span>
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 px-24 py-16 border-bottom">
                <div>
                    <h6 class="mb-4 fw-semibold fc-panel-title">
                        <iconify-icon icon="solar:documents-linear"></iconify-icon>
                        All Forms
                    </h6>
                    <p class="text-secondary-light text-sm mb-0">Unified storage in <span class="text-primary-600 fw-medium">form_entries</span></p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table bordered-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-24">Form</th>
                            <th>Steps</th>
                            <th>Fields</th>
                            <th>Submissions</th>
                            <th>Landing</th>
                            <th>Status</th>
                            <th class="text-end pe-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($forms as $form)
                        @php
                            $icons = [
                                'job-applications' => ['solar:case-round-linear', '#487FFF'],
                                'staff-application' => ['solar:user-id-linear', '#16a34a'],
                                'student-admission' => ['solar:square-academic-cap-linear', '#7c3aed'],
                                'debit-form' => ['solar:wallet-linear', '#ca8a04'],
                                'enquire-now' => ['solar:letter-linear', '#0891b2'],
                                'referral' => ['solar:hand-shake-linear', '#db2777'],
                                'meeting-form' => ['solar:calendar-linear', '#64748b'],
                            ];
                            [$icon, $color] = $icons[$form->slug] ?? ['solar:document-text-linear', '#487FFF'];
                        @endphp
                        <tr class="fc-form-row">
                            <td class="ps-24">
                                <div class="d-flex align-items-center gap-12">
                                    <span class="fc-form-icon" style="background: {{ $color }}18; color: {{ $color }};">
                                        <iconify-icon icon="{{ $icon }}"></iconify-icon>
                                    </span>
                                    <div>
                                        <h6 class="text-md fw-semibold mb-4">{{ $form->name }}</h6>
                                        <span class="text-secondary-light text-sm">/{{ $form->slug }}</span>
                                        @if($form->legacy_route)
                                        <span class="text-secondary-light text-xs d-block mt-2">{{ $form->legacy_route }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="fw-semibold">{{ $form->steps_count }}</span></td>
                            <td><span class="fw-semibold">{{ $form->fields_count }}</span></td>
                            <td>
                                <a href="{{ route('admin.form-manager.entries', $form) }}" class="fw-bold text-primary-600 hover-text-primary">
                                    {{ number_format($form->entries_count) }}
                                </a>
                            </td>
                            <td>
                                <form action="{{ route('admin.form-manager.toggle-landing', $form) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="fc-badge border-0 {{ $form->show_on_landing ? 'fc-badge-success' : 'fc-badge-neutral' }}">
                                        <iconify-icon icon="{{ $form->show_on_landing ? 'solar:eye-linear' : 'solar:eye-closed-linear' }}"></iconify-icon>
                                        {{ $form->show_on_landing ? 'Visible' : 'Hidden' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.form-manager.toggle', $form) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="fc-badge border-0 {{ $form->is_active ? 'fc-badge-primary' : 'fc-badge-neutral' }}">
                                        {{ $form->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-24">
                                <div class="fc-table-actions">
                                    <a href="{{ route('admin.form-manager.edit', $form) }}"
                                       class="fc-action-icon edit"
                                       title="Customize form"
                                       aria-label="Customize form">
                                        <iconify-icon icon="solar:pen-linear"></iconify-icon>
                                    </a>
                                    <a href="{{ route('admin.form-manager.entries', $form) }}"
                                       class="fc-action-icon view"
                                       title="View submissions"
                                       aria-label="View submissions">
                                        <iconify-icon icon="solar:inbox-linear"></iconify-icon>
                                    </a>
                                    <form action="{{ route('admin.form-manager.duplicate', $form) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="fc-action-icon copy"
                                                title="Duplicate form"
                                                aria-label="Duplicate form">
                                            <iconify-icon icon="solar:copy-linear"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endsection
