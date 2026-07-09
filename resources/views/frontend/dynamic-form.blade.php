@extends('layouts.form')

@section('title', 'Al-rushd Online School - ' . $formName)

@section('content')
<div class="ar-form-page">
    <header class="ar-form-header">
        <a href="{{ url('/') }}" class="ar-form-header-logo">
            <img src="{{ asset('frontend/assets/img/logo.png') }}" alt="Al-Rushd">
        </a>
        <h1 class="ar-form-page-title" id="pageFormTitle">{{ $formName }}</h1>
    </header>

    <div class="ar-form-wrapper" id="dynamicFormApp"
         data-slug="{{ $slug }}"
         data-api-base="{{ url('/api/frontend/forms') }}">

        <div class="ar-form-loading" id="formLoading">
            <div class="spinner-border text-secondary" role="status" style="color: var(--ar-gold) !important;"></div>
            <p class="mt-3 mb-0">Loading form...</p>
        </div>

        <div class="ar-form-error d-none" id="formError">
            <i class="fa fa-exclamation-circle fa-2x mb-3" style="color: #dc2626;"></i>
            <p id="formErrorMessage">Unable to load this form.</p>
            <a href="{{ url('/') }}" class="ar-btn ar-btn--primary">Back to Home</a>
        </div>

        <div id="formContainer" class="ar-form-shell d-none">
            <aside class="ar-form-sidebar">
                <div class="ar-sidebar-title">Your progress</div>
                <ul class="ar-step-nav" id="stepSidebar" role="navigation"></ul>
            </aside>

            <main class="ar-form-main">
                <div class="ar-form-main-header">
                    <span class="ar-step-indicator" id="stepIndicator">STEP 1 OF 1</span>
                    <div class="ar-progress-ring" id="progressRing" aria-hidden="true">
                        <svg viewBox="0 0 56 56">
                            <circle class="ar-progress-ring-bg" cx="28" cy="28" r="24"></circle>
                            <circle class="ar-progress-ring-fill" id="progressRingFill" cx="28" cy="28" r="24"
                                    stroke-dasharray="150.8" stroke-dashoffset="150.8"></circle>
                        </svg>
                        <span class="ar-progress-ring-text" id="progressText">0%</span>
                    </div>
                </div>

                <h2 class="ar-section-title" id="sectionTitle">Step 1</h2>

                <form id="dynamicForm" enctype="multipart/form-data" novalidate>
                    <div id="stepsContainer"></div>

                    <div class="ar-form-actions">
                        <button type="button" class="ar-btn ar-btn--ghost d-none" id="btnBack">Back</button>
                        <button type="button" class="ar-btn ar-btn--primary" id="btnNext">Next</button>
                        <button type="submit" class="ar-btn ar-btn--primary d-none" id="btnSubmit">Submit</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('frontend/assets/js/dynamic-form.js') }}"></script>
@endsection
