@extends('admin.layouts.app')

@section('title', $form->exists ? 'Customize Form' : 'Create Form')

@section('content')
@include('admin.form-manager.partials.styles')

<div class="dashboard-main-body fc-pro-page">
    <div class="fc-pro-page-header-compact">
    @include('admin.form-manager.partials.header', [
        'title' => $form->exists ? 'Customize Form' : 'Create Form',
        'subtitle' => $form->exists ? $form->name : 'Define a new dynamic form',
        'breadcrumbs' => [
            ['label' => 'Form Center', 'url' => route('admin.form-manager.index')],
            ['label' => $form->exists ? $form->name : 'New Form'],
        ],
        'actions' => $form->exists ? [
            ['label' => 'Submissions', 'url' => route('admin.form-manager.entries', $form), 'class' => 'btn-outline-primary-600 radius-8 px-20 py-11', 'icon' => 'solar:document-text-linear'],
            ['label' => 'Back', 'url' => route('admin.form-manager.index'), 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'],
        ] : [
            ['label' => 'Back', 'url' => route('admin.form-manager.index'), 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'],
        ],
    ])

    </div>

    @if ($errors->any())
        <div class="alert alert-danger radius-12 mb-20" role="alert">
            <div class="d-flex align-items-start gap-12">
                <iconify-icon icon="solar:danger-circle-linear" class="text-xl mt-1 flex-shrink-0"></iconify-icon>
                <div>
                    <strong class="d-block mb-8">Could not save form</strong>
                    <ul class="mb-0 ps-20">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="fc-pro-builder" id="formBuilderApp">
        <form method="POST"
              action="{{ $form->exists ? route('admin.form-manager.update', $form) : route('admin.form-manager.store') }}"
              id="formBuilderForm">
            @csrf
            @if($form->exists) @method('PUT') @endif
            <input type="hidden" name="wizard_step" id="wizard_step" value="0">

            {{-- Sticky toolbar --}}
            <div class="fc-pro-toolbar">
                <div class="fc-pro-toolbar-left">
                    <div class="fc-pro-form-badge">
                        <iconify-icon icon="solar:document-text-bold"></iconify-icon>
                    </div>
                    <div class="fc-pro-form-meta">
                        <input type="text" name="name" id="name" class="fc-pro-form-name"
                               value="{{ old('name', $form->name) }}" required placeholder="Untitled form">
                        <span class="fc-pro-form-slug">/<span id="slugDisplay">{{ old('slug', $form->slug) ?: 'your-slug' }}</span></span>
                    </div>
                </div>
                <div class="fc-pro-toolbar-center">
                    <div class="fc-pro-mode-tabs" role="tablist">
                        <button type="button" class="fc-pro-mode-tab active" data-mode="build" role="tab">
                            <iconify-icon icon="solar:layers-minimalistic-linear"></iconify-icon>
                            Build
                        </button>
                        <button type="button" class="fc-pro-mode-tab" data-mode="settings" role="tab">
                            <iconify-icon icon="solar:settings-linear"></iconify-icon>
                            Settings
                        </button>
                    </div>
                </div>
                <div class="fc-pro-toolbar-right">
                    <span class="fc-pro-save-status" id="saveStatus" aria-live="polite">
                        <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                        All changes saved
                    </span>
                    <button type="button" class="btn btn-outline-neutral-500 radius-8 px-16 py-10 fc-btn fc-pro-preview-toggle d-xl-none" id="togglePreviewBtn">
                        <iconify-icon icon="solar:eye-linear"></iconify-icon>
                        Preview
                    </button>
                    <button type="button" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn" id="saveBtn">
                        <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                        <span>Save Form</span>
                    </button>
                </div>
            </div>

            <div class="fc-pro-workspace">
                {{-- LEFT: Editor --}}
                <div class="fc-pro-editor">
                    {{-- BUILD mode --}}
                    <div class="fc-pro-panel active" data-panel="build">
                        <div class="fc-pro-build-grid">
                            {{-- Steps rail --}}
                            <aside class="fc-pro-steps-rail">
                                <div class="fc-pro-rail-header">
                                    <span class="fw-semibold text-sm">Steps</span>
                                    <button type="button" class="fc-pro-icon-btn" id="addStepBtn" title="Add step">
                                        <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                                    </button>
                                </div>
                                <div class="fc-pro-rail-hint">
                                    <iconify-icon icon="solar:hand-stars-linear"></iconify-icon>
                                    Drag to reorder
                                </div>
                                <div id="stepsContainer" class="fc-pro-steps-list"></div>
                            </aside>

                            {{-- Fields area --}}
                            <div class="fc-pro-fields-zone">
                                <div class="fc-pro-stats-bar" id="builderStatsBar">
                                    <span class="fc-pro-stat"><iconify-icon icon="solar:layers-linear"></iconify-icon> <strong id="statSteps">0</strong> steps</span>
                                    <span class="fc-pro-stat-divider"></span>
                                    <span class="fc-pro-stat"><iconify-icon icon="solar:text-field-linear"></iconify-icon> <strong id="statFields">0</strong> fields</span>
                                    <span class="fc-pro-stat-divider"></span>
                                    <span class="fc-pro-stat fc-pro-stat--muted" id="statActiveStep">Step 1 of 1</span>
                                </div>

                                <div class="fc-pro-step-settings" id="stepSettingsPanel">
                                    <div class="fc-pro-step-settings-top">
                                        <span class="fc-pro-step-chip" id="activeStepChip">Step 1</span>
                                        <input type="text" class="fc-pro-step-title-input" data-f="title" placeholder="Step title (e.g. Personal Details)">
                                        <input type="hidden" data-f="key" value="">
                                    </div>
                                    <div class="fc-pro-step-settings-bottom">
                                        <input type="text" class="form-control form-control-sm radius-8" data-f="description" placeholder="Optional description shown under the step title">
                                        <div class="fc-pro-transition-wrap">
                                            <iconify-icon icon="solar:transfer-horizontal-linear"></iconify-icon>
                                            <select class="form-select form-select-sm radius-8" data-f="transition" title="Step transition animation">
                                                <option value="slide">Slide transition</option>
                                                <option value="fade">Fade transition</option>
                                                <option value="none">No transition</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="fc-pro-fields-toolbar">
                                    <button type="button" class="fc-pro-palette-toggle" id="paletteToggle" aria-expanded="true">
                                        <iconify-icon icon="solar:widget-add-linear"></iconify-icon>
                                        <span>Field types</span>
                                        <iconify-icon icon="solar:alt-arrow-down-linear" class="fc-pro-chevron"></iconify-icon>
                                    </button>
                                    <button type="button" class="btn btn-outline-neutral-500 radius-8 px-16 py-10 text-sm fc-btn" id="addSectionBtn">
                                        <iconify-icon icon="solar:widget-5-linear"></iconify-icon>
                                        Add Section
                                    </button>
                                    <button type="button" class="btn btn-primary-600 radius-8 px-16 py-10 text-sm fc-btn" id="addFieldBtn">
                                        <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                                        Add Field
                                    </button>
                                </div>

                                <div class="fc-pro-palette is-open" id="paletteWrap">
                                    <div class="fc-pro-palette-grid" id="fieldPalette"></div>
                                </div>

                                <div id="fieldsContainer" class="fc-pro-fields-list"></div>
                            </div>
                        </div>
                    </div>

                    {{-- SETTINGS mode --}}
                    <div class="fc-pro-panel" data-panel="settings">
                        <div class="fc-pro-settings-scroll">
                            <input type="hidden" name="slug" id="slug" value="{{ old('slug', $form->slug) }}">

                            <div class="fc-form-section">
                                <div class="fc-form-section-title">Identity</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-sm">Slug <span class="text-danger">*</span></label>
                                        <input type="text" id="slugInput" class="form-control radius-8" value="{{ old('slug', $form->slug) }}" required placeholder="job-applications">
                                        <small class="text-secondary-light">Used in API and public URL</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-sm">Description</label>
                                        <textarea name="description" class="form-control radius-8" rows="2" placeholder="Brief description for admins">{{ old('description', $form->description) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="fc-form-section">
                                <div class="fc-form-section-title">Routing & Submission</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-sm">Public Route</label>
                                        <div class="input-group">
                                            <span class="input-group-text radius-start-8 bg-neutral-100">/</span>
                                            <input type="text" name="legacy_route" id="legacy_route" class="form-control radius-end-8" value="{{ old('legacy_route', $form->legacy_route) }}" placeholder="job-applications">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-sm">Success Page</label>
                                        <div class="input-group">
                                            <span class="input-group-text radius-start-8 bg-neutral-100">/</span>
                                            <input type="text" name="success_route" class="form-control radius-end-8" value="{{ old('success_route', $form->success_route) }}" placeholder="forms/my-form/success">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-sm">Submit Method</label>
                                        <select name="submit_method" class="form-select radius-8">
                                            @foreach(['urlencoded' => 'Standard (URL encoded)', 'multipart' => 'With file uploads'] as $val => $label)
                                                <option value="{{ $val }}" @selected(old('submit_method', $form->submit_method ?? 'urlencoded') === $val)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-sm">Handler</label>
                                        <select name="handler" class="form-select radius-8">
                                            @foreach(['dynamic' => 'Default (dynamic)', 'custom' => 'Custom (legacy page)'] as $val => $label)
                                                <option value="{{ $val }}" @selected(old('handler', $form->handler ?? 'dynamic') === $val)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="fc-form-section">
                                <div class="fc-form-section-title">Website display</div>
                                @php
                                    $currentPlacements = old('placements', $form->exists ? $form->placements() : []);
                                    if (! is_array($currentPlacements)) { $currentPlacements = []; }
                                @endphp

                                <label class="fc-visibility-status {{ old('is_active', $form->is_active ?? true) ? 'is-on' : '' }}" for="is_active" data-visibility-status>
                                    <span class="fc-visibility-status-icon"><iconify-icon icon="solar:power-linear"></iconify-icon></span>
                                    <span class="fc-visibility-status-body">
                                        <span class="fw-semibold d-block">Form status</span>
                                        <span class="text-secondary-light text-sm">When active, the form accepts submissions.</span>
                                    </span>
                                    <span class="fc-visibility-switch">
                                        <input type="checkbox" name="is_active" value="1" id="is_active" class="fc-visibility-switch-input" @checked(old('is_active', $form->is_active ?? true))>
                                        <span class="fc-visibility-switch-track" aria-hidden="true"></span>
                                    </span>
                                </label>

                                <div class="fc-visibility-placements mt-20">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-10 mb-12">
                                        <span class="fw-semibold text-sm">Show this form on</span>
                                    </div>
                                    <div class="fc-settings-options fc-settings-options--builder d-flex flex-column gap-12">
                                        @foreach($placementOptions as $key => $option)
                                        <label class="fc-settings-option {{ in_array($key, $currentPlacements, true) ? 'is-selected' : '' }}" for="builder_placement_{{ $key }}" data-settings-option>
                                            <input type="checkbox" class="fc-settings-checkbox" name="placements[]" value="{{ $key }}" id="builder_placement_{{ $key }}" data-placement="{{ $key }}" @checked(in_array($key, $currentPlacements, true))>
                                            <span class="fc-settings-option-icon"><iconify-icon icon="{{ $option['icon'] ?? 'solar:widget-linear' }}"></iconify-icon></span>
                                            <span class="fc-settings-option-body">
                                                <span class="fw-semibold d-block">{{ $option['label'] }}</span>
                                                <span class="text-secondary-light text-sm">{{ $option['description'] }}</span>
                                            </span>
                                            <span class="fc-settings-option-check" aria-hidden="true"><iconify-icon icon="solar:check-circle-bold"></iconify-icon></span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="row g-3 mt-20 pt-20 border-top border-neutral-200">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-sm">Hero Button Label</label>
                                        <input type="text" name="hero_label" class="form-control radius-8" value="{{ old('hero_label', $form->hero_label) }}" placeholder="Apply Now">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-sm">Button Style</label>
                                        <select name="hero_variant" class="form-select radius-8">
                                            <option value="gold" @selected(old('hero_variant', $form->hero_variant) === 'gold')>Gold (filled)</option>
                                            <option value="outline" @selected(old('hero_variant', $form->hero_variant ?? 'outline') === 'outline')>Outline</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-sm">Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control radius-8" value="{{ old('sort_order', $form->sort_order ?? 0) }}" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Live Preview --}}
                <aside class="fc-pro-preview" id="builderPreview">
                    <div class="fc-pro-preview-chrome">
                        <div class="fc-pro-preview-header">
                            <span class="fc-pro-preview-label">
                                <span class="fc-pro-live-dot"></span>
                                Live Preview
                            </span>
                            <div class="fc-pro-preview-tools">
                                <button type="button" class="fc-pro-device-btn active" data-device="desktop" title="Desktop">
                                    <iconify-icon icon="solar:monitor-linear"></iconify-icon>
                                </button>
                                <button type="button" class="fc-pro-device-btn" data-device="mobile" title="Mobile">
                                    <iconify-icon icon="solar:smartphone-linear"></iconify-icon>
                                </button>
                            </div>
                        </div>
                        <div class="fc-pro-preview-frame-wrap" id="previewFrameWrap">
                            <div class="fc-pro-preview-device fc-pro-preview-device--desktop" id="previewDevice">
                                <div class="fc-pro-preview-mockup" id="livePreview">
                                    <div class="fc-preview-app">
                                        <div class="fc-preview-logo">
                                            <div class="fc-preview-logo-icon"></div>
                                        </div>
                                        <div class="fc-preview-progress">
                                            <h6 class="fc-preview-title" id="previewFormTitle">Form Preview</h6>
                                            <div class="fc-preview-bar-track">
                                                <div class="fc-preview-bar-fill" id="previewProgressBar"></div>
                                            </div>
                                            <small class="fc-preview-pct" id="previewProgressText">0%</small>
                                        </div>
                                        <div class="fc-preview-step-wrap" id="previewStepWrap">
                                            <div class="fc-preview-step" id="previewStepContent"></div>
                                        </div>
                                        <div class="fc-preview-nav">
                                            <button type="button" class="fc-preview-btn fc-preview-btn--ghost" id="previewPrevBtn" disabled>Back</button>
                                            <button type="button" class="fc-preview-btn fc-preview-btn--primary" id="previewNextBtn">Next</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fc-pro-preview-footer">
                            <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
                            <span>Updates instantly as you edit</span>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $initialSchema = ['steps' => []];
    if (old('schema')) {
        $decoded = json_decode(old('schema'), true);
        if (is_array($decoded)) { $initialSchema = $decoded; }
    } elseif (! empty($schema)) {
        $initialSchema = $schema;
    }
    if (empty($initialSchema['steps'])) {
        $initialSchema['steps'] = [['key' => 'step_1', 'title' => 'Step 1', 'description' => '', 'transition' => 'slide', 'fields' => []]];
    }
    $schemaJson = json_encode($initialSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

@section('script')
<script src="{{ asset('admin/assets/js/form-builder.js') }}"></script>
<script>
    FormBuilder.init({
        schema: {!! $schemaJson !!},
        formAction: @json($form->exists ? route('admin.form-manager.update', $form) : route('admin.form-manager.store')),
        isEdit: @json($form->exists),
        optionSources: @json($optionSources),
        optionSourceGroups: @json($optionSourceGroups),
        optionSourceUrl: @json(url('/admin/form-center/option-sources')),
    });
</script>
@endsection
