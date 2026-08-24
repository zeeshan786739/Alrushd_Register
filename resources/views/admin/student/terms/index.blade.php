@extends('admin.layouts.app')

@section('title') Terms & Conditions @endsection

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Terms & Conditions',
    'subtitle' => 'Manage legal copy shown during student and staff registration.',
    'breadcrumbs' => [
        ['label' => 'Admissions Setup'],
        ['label' => 'Terms & Conditions'],
    ],
])

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card radius-12 border-0 shadow-sm h-100">
            <div class="card-body p-24">
                <h6 class="fw-semibold mb-8">Student admission terms</h6>
                <p class="text-secondary-light text-sm mb-16">Shown during multi-step student registration (step 6).</p>
                <p class="text-sm mb-0">
                    @if(filled($record->terms_description))
                        <span class="badge bg-success-focus text-success-main radius-8 px-12 py-6">Configured</span>
                    @else
                        <span class="badge bg-warning-focus text-warning-main radius-8 px-12 py-6">Not set</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card radius-12 border-0 shadow-sm h-100">
            <div class="card-body p-24 d-flex flex-column">
                <h6 class="fw-semibold mb-8">Staff application terms</h6>
                <p class="text-secondary-light text-sm mb-16">Shown on the staff admissions form before submit.</p>
                <p class="text-sm mb-16">
                    @if(filled($record->staff_terms_condition))
                        <span class="badge bg-success-focus text-success-main radius-8 px-12 py-6">Configured</span>
                    @else
                        <span class="badge bg-warning-focus text-warning-main radius-8 px-12 py-6">Not set</span>
                    @endif
                </p>
                @can('edit terms_condition')
                <a href="{{ route('admin.staff-terms-condition') }}" class="btn btn-primary-600 radius-8 fc-btn mt-auto align-self-start">
                    <iconify-icon icon="solar:pen-linear"></iconify-icon> Edit staff terms
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
