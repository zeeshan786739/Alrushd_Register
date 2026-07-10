@extends('admin.layouts.app')

@section('title', $form->exists ? 'Customize Form' : 'Create Form')

@section('content')
@include('admin.form-manager.partials.styles')

<div class="dashboard-main-body">
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

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            {{-- Progress bar --}}
            <div class="fc-wizard-progress" aria-hidden="true">
                <div class="fc-wizard-progress-bar" id="wizardProgressBar" style="width:25%"></div>
            </div>

            {{-- Clickable wizard step cards --}}
            <div class="fc-wizard" role="tablist" aria-label="Form builder steps">
                <button type="button" class="fc-wizard-step active" data-step="0" role="tab" aria-selected="true" aria-controls="wizardPanel0">
                    <span class="fc-step-num">1</span>
                    <span class="fc-step-body">
                        <span class="fc-step-title">Basic Info</span>
                        <span class="fc-step-desc">Name, slug & routes</span>
                    </span>
                </button>
                <button type="button" class="fc-wizard-step" data-step="1" role="tab" aria-selected="false" aria-controls="wizardPanel1">
                    <span class="fc-step-num">2</span>
                    <span class="fc-step-body">
                        <span class="fc-step-title">Steps</span>
                        <span class="fc-step-desc">Wizard sections</span>
                    </span>
                </button>
                <button type="button" class="fc-wizard-step" data-step="2" role="tab" aria-selected="false" aria-controls="wizardPanel2">
                    <span class="fc-step-num">3</span>
                    <span class="fc-step-body">
                        <span class="fc-step-title">Fields</span>
                        <span class="fc-step-desc">Inputs & validation</span>
                    </span>
                </button>
                <button type="button" class="fc-wizard-step" data-step="3" role="tab" aria-selected="false" aria-controls="wizardPanel3">
                    <span class="fc-step-num">4</span>
                    <span class="fc-step-body">
                        <span class="fc-step-title">Review</span>
                        <span class="fc-step-desc">Preview & save</span>
                    </span>
                </button>
            </div>

            <form method="POST" action="{{ $form->exists ? route('admin.form-manager.update', $form) : route('admin.form-manager.store') }}" id="formBuilderForm">
                @csrf
                @if($form->exists) @method('PUT') @endif

                {{-- Panel 0: Basic Info --}}
                <div class="fc-panel active" id="wizardPanel0" role="tabpanel">
                    <div class="fc-panel-header">
                        <h6 class="fc-panel-title"><iconify-icon icon="solar:info-circle-linear"></iconify-icon> Form Details</h6>
                    </div>

                    <div class="fc-form-section">
                        <div class="fc-form-section-title">Identity</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-sm">Form Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control radius-8" value="{{ old('name', $form->name) }}" required placeholder="e.g. Job Application">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-sm">Slug <span class="text-danger">*</span></label>
                                <input type="text" name="slug" id="slug" class="form-control radius-8" value="{{ old('slug', $form->slug) }}" required placeholder="job-applications">
                                <small class="text-secondary-light">Used in API and database</small>
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
                                    <input type="text" name="success_route" class="form-control radius-end-8" value="{{ old('success_route', $form->success_route) }}" placeholder="job-applications-success">
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
                                    @foreach(['dynamic' => 'Default (dynamic)', 'custom' => 'Custom (React page)'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('handler', $form->handler ?? 'dynamic') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="fc-form-section">
                        <div class="fc-form-section-title">Landing Page & Status</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-sm">Hero Button Label</label>
                                <input type="text" name="hero_label" class="form-control radius-8" value="{{ old('hero_label', $form->hero_label) }}" placeholder="Apply Now">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-sm">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control radius-8" value="{{ old('sort_order', $form->sort_order ?? 0) }}" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-sm d-block mb-8">Visibility</label>
                                <div class="fc-switch-group">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $form->is_active ?? true))>
                                        <label class="form-check-label fw-medium" for="is_active">Active</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="show_on_landing" value="1" id="show_on_landing" @checked(old('show_on_landing', $form->show_on_landing))>
                                        <label class="form-check-label fw-medium" for="show_on_landing">Show on landing</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel 1: Steps --}}
                <div class="fc-panel" id="wizardPanel1" role="tabpanel">
                    <div class="fc-panel-header">
                        <h6 class="fc-panel-title"><iconify-icon icon="solar:layers-linear"></iconify-icon> Form Steps</h6>
                        <button type="button" class="btn btn-primary-600 radius-8 px-16 py-10 text-sm fc-btn" id="addStepBtn">
                            <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                            <span>Add Step</span>
                        </button>
                    </div>
                    <p class="text-secondary-light text-sm mb-20">Define wizard sections shown to users. Each step can contain multiple fields.</p>
                    <div id="stepsContainer"></div>
                </div>

                {{-- Panel 2: Fields --}}
                <div class="fc-panel" id="wizardPanel2" role="tabpanel">
                    <div class="fc-panel-header">
                        <h6 class="fc-panel-title"><iconify-icon icon="solar:text-field-linear"></iconify-icon> Form Fields</h6>
                        <button type="button" class="btn btn-primary-600 radius-8 px-16 py-10 text-sm fc-btn" id="addFieldBtn">
                            <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                            <span>Add Field</span>
                        </button>
                    </div>
                    <div id="fieldStepTabs" class="fc-field-tabs"></div>
                    <div id="fieldsContainer"></div>
                </div>

                {{-- Panel 3: Review --}}
                <div class="fc-panel" id="wizardPanel3" role="tabpanel">
                    <div class="fc-panel-header">
                        <h6 class="fc-panel-title"><iconify-icon icon="solar:eye-linear"></iconify-icon> Review & Save</h6>
                    </div>
                    <div class="fc-review-hero">
                        <div class="d-flex align-items-center gap-16 flex-wrap">
                            <div class="w-48-px h-48-px bg-primary-600 rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:document-text-bold" class="text-white text-2xl"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="mb-4 fw-bold" id="reviewName">—</h6>
                                <p class="text-secondary-light text-sm mb-0" id="reviewMeta">—</p>
                            </div>
                        </div>
                    </div>
                    <div class="fc-review-list mb-20" id="reviewSteps"></div>
                    <div class="alert alert-info radius-8 d-flex align-items-start gap-12 mb-0">
                        <iconify-icon icon="solar:info-circle-linear" class="text-xl mt-1 flex-shrink-0"></iconify-icon>
                        <div class="text-sm">Click <strong>Save Form</strong> to persist all changes. Submissions are stored in the unified <code>form_entries</code> table.</div>
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="fc-wizard-nav">
                    <button type="button" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn" id="prevBtn" disabled>
                        <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                        <span>Previous</span>
                    </button>
                    <div class="fc-wizard-nav-right">
                        <button type="button" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn" id="nextBtn">
                            <span>Next</span>
                            <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                        </button>
                        <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn" id="saveBtn" style="display:none">
                            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                            <span>Save Form</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $schemaJson = json_encode($schema ?? ['steps' => []], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

<script>
(function () {
    const FIELD_TYPES = ['text','email','tel','number','date','textarea','select','radio','checkbox','file','hidden','section','repeatable'];
    const OPTIONS_SOURCES = ['','countries','nationalities','genders','education_levels','employment_statuses','hear_about_us','job_titles','staff_roles','meeting_types','meeting_durations','meeting_times','referral_sources','custom'];
    const initialSchema = {!! $schemaJson !!};
    let state = normalizeSchema(initialSchema);
    let currentWizard = 0;
    let activeFieldStep = 0;
    const TOTAL_STEPS = 4;

    function normalizeSchema(s) {
        s = s || { steps: [] };
        if (!Array.isArray(s.steps)) s.steps = [];
        s.steps.forEach((step, si) => {
            step.key = step.key || 'step_' + (si + 1);
            step.title = step.title || 'Step ' + (si + 1);
            step.fields = step.fields || [];
            step.fields.forEach((f, fi) => {
                f.key = f.key || 'field_' + si + '_' + fi;
                f.label = f.label || 'Field';
                f.type = f.type || 'text';
                f.required = !!f.required;
                f.options = f.options || [];
                f.options_source = f.options_source || '';
            });
        });
        return s;
    }

    function collectStateFromDom() {
        state.name = document.getElementById('name')?.value || '';
        state.slug = document.getElementById('slug')?.value || '';
        state.description = document.querySelector('[name="description"]')?.value || '';
        state.legacy_route = document.getElementById('legacy_route')?.value || '';
        state.success_route = document.querySelector('[name="success_route"]')?.value || '';
        state.submit_method = document.querySelector('[name="submit_method"]')?.value || 'urlencoded';
        state.handler = document.querySelector('[name="handler"]')?.value || 'dynamic';
        state.hero_label = document.querySelector('[name="hero_label"]')?.value || '';
        state.sort_order = parseInt(document.querySelector('[name="sort_order"]')?.value || '0', 10);
        state.is_active = document.getElementById('is_active')?.checked ?? true;
        state.show_on_landing = document.getElementById('show_on_landing')?.checked ?? false;

        document.querySelectorAll('.fc-step-card').forEach((el, i) => {
            if (!state.steps[i]) return;
            state.steps[i].key = el.querySelector('[data-f="key"]')?.value || state.steps[i].key;
            state.steps[i].title = el.querySelector('[data-f="title"]')?.value || state.steps[i].title;
            state.steps[i].description = el.querySelector('[data-f="description"]')?.value || '';
        });

        document.querySelectorAll('.fc-field-row').forEach(el => {
            const si = parseInt(el.dataset.stepIndex, 10);
            const fi = parseInt(el.dataset.fieldIndex, 10);
            if (!state.steps[si]?.fields[fi]) return;
            const f = state.steps[si].fields[fi];
            f.key = el.querySelector('[data-f="key"]')?.value || f.key;
            f.label = el.querySelector('[data-f="label"]')?.value || f.label;
            f.type = el.querySelector('[data-f="type"]')?.value || f.type;
            f.placeholder = el.querySelector('[data-f="placeholder"]')?.value || '';
            f.help_text = el.querySelector('[data-f="help_text"]')?.value || '';
            f.required = el.querySelector('[data-f="required"]')?.checked ?? false;
            f.width = el.querySelector('[data-f="width"]')?.value || 'full';
            f.options_source = el.querySelector('[data-f="options_source"]')?.value || '';
            const optRaw = el.querySelector('[data-f="options"]')?.value || '';
            f.options = optRaw.split('\n').map(l => l.trim()).filter(Boolean);
            f.validation = el.querySelector('[data-f="validation"]')?.value || '';
        });
    }

    function setWizard(step) {
        step = Math.max(0, Math.min(TOTAL_STEPS - 1, step));
        collectStateFromDom();
        currentWizard = step;

        document.querySelectorAll('.fc-wizard-step').forEach(el => {
            const s = parseInt(el.dataset.step, 10);
            const isActive = s === step;
            const isDone = s < step;
            el.classList.toggle('active', isActive);
            el.classList.toggle('done', isDone);
            el.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        document.querySelectorAll('.fc-panel').forEach((el, i) => {
            el.classList.toggle('active', i === step);
        });

        const progress = ((step + 1) / TOTAL_STEPS) * 100;
        document.getElementById('wizardProgressBar').style.width = progress + '%';

        document.getElementById('prevBtn').disabled = step === 0;
        document.getElementById('nextBtn').style.display = step === TOTAL_STEPS - 1 ? 'none' : '';
        document.getElementById('saveBtn').style.display = step === TOTAL_STEPS - 1 ? '' : 'none';

        if (step === 2) renderFields();
        if (step === 3) renderReview();
    }

    function renderSteps() {
        const c = document.getElementById('stepsContainer');
        c.innerHTML = state.steps.map((step, i) => `
            <div class="fc-step-card">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-sm fw-medium">Key</label>
                        <input type="text" class="form-control radius-8 form-control-sm" data-f="key" value="${esc(step.key)}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-sm fw-medium">Title</label>
                        <input type="text" class="form-control radius-8 form-control-sm" data-f="title" value="${esc(step.title)}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-sm fw-medium">Description</label>
                        <input type="text" class="form-control radius-8 form-control-sm" data-f="description" value="${esc(step.description || '')}">
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-outline-danger-600 btn-sm radius-8" data-remove-step="${i}" title="Remove step">
                            <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        c.querySelectorAll('[data-remove-step]').forEach(btn => {
            btn.addEventListener('click', () => {
                collectStateFromDom();
                state.steps.splice(parseInt(btn.dataset.removeStep, 10), 1);
                if (activeFieldStep >= state.steps.length) activeFieldStep = Math.max(0, state.steps.length - 1);
                renderSteps();
                renderFieldTabs();
                renderFields();
            });
        });
    }

    function renderFieldTabs() {
        const tabs = document.getElementById('fieldStepTabs');
        if (!state.steps.length) {
            tabs.innerHTML = '<span class="text-secondary-light text-sm">Add a step first to configure fields.</span>';
            return;
        }
        tabs.innerHTML = state.steps.map((step, i) => `
            <button type="button" class="fc-field-tab ${i === activeFieldStep ? 'active' : ''}" data-step-tab="${i}">
                ${esc(step.title)} <span class="opacity-75">(${(step.fields || []).length})</span>
            </button>
        `).join('');

        tabs.querySelectorAll('[data-step-tab]').forEach(btn => {
            btn.addEventListener('click', () => {
                collectStateFromDom();
                activeFieldStep = parseInt(btn.dataset.stepTab, 10);
                renderFieldTabs();
                renderFields();
            });
        });
    }

    function renderFields() {
        renderFieldTabs();
        const c = document.getElementById('fieldsContainer');
        const step = state.steps[activeFieldStep];
        if (!step) {
            c.innerHTML = '<p class="text-secondary-light">No steps defined yet.</p>';
            return;
        }

        c.innerHTML = (step.fields || []).map((field, fi) => fieldRowHtml(activeFieldStep, fi, field)).join('') ||
            '<div class="text-center py-40 text-secondary-light"><iconify-icon icon="solar:text-field-linear" class="text-4xl mb-8 d-block opacity-50"></iconify-icon>No fields in this step. Click "Add Field" to create one.</div>';

        c.querySelectorAll('[data-remove-field]').forEach(btn => {
            btn.addEventListener('click', () => {
                collectStateFromDom();
                const si = parseInt(btn.dataset.stepIndex, 10);
                state.steps[si].fields.splice(parseInt(btn.dataset.fieldIndex, 10), 1);
                renderFields();
            });
        });
    }

    function fieldRowHtml(si, fi, field) {
        const optLines = (field.options || []).join('\n');
        const typeOpts = FIELD_TYPES.map(t => `<option value="${t}" ${field.type === t ? 'selected' : ''}>${t}</option>`).join('');
        const srcOpts = OPTIONS_SOURCES.map(s => `<option value="${s}" ${field.options_source === s ? 'selected' : ''}>${s || '— none —'}</option>`).join('');
        return `
        <div class="fc-field-row" data-step-index="${si}" data-field-index="${fi}">
            <div class="d-flex justify-content-between align-items-center mb-12">
                <span class="fc-badge fc-badge-neutral">${esc(field.type)}</span>
                <button type="button" class="btn btn-outline-danger-600 btn-sm radius-8" data-remove-field data-step-index="${si}" data-field-index="${fi}">
                    <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label text-sm">Key</label><input class="form-control form-control-sm radius-8" data-f="key" value="${esc(field.key)}"></div>
                <div class="col-md-3"><label class="form-label text-sm">Label</label><input class="form-control form-control-sm radius-8" data-f="label" value="${esc(field.label)}"></div>
                <div class="col-md-2"><label class="form-label text-sm">Type</label><select class="form-select form-select-sm radius-8" data-f="type">${typeOpts}</select></div>
                <div class="col-md-2"><label class="form-label text-sm">Width</label><select class="form-select form-select-sm radius-8" data-f="width"><option value="full" ${field.width === 'full' ? 'selected' : ''}>Full</option><option value="half" ${field.width === 'half' ? 'selected' : ''}>Half</option></select></div>
                <div class="col-md-2 d-flex align-items-end pb-1"><div class="form-check"><input type="checkbox" class="form-check-input" data-f="required" ${field.required ? 'checked' : ''}><label class="form-check-label text-sm">Required</label></div></div>
                <div class="col-md-4"><label class="form-label text-sm">Placeholder</label><input class="form-control form-control-sm radius-8" data-f="placeholder" value="${esc(field.placeholder || '')}"></div>
                <div class="col-md-4"><label class="form-label text-sm">Help text</label><input class="form-control form-control-sm radius-8" data-f="help_text" value="${esc(field.help_text || '')}"></div>
                <div class="col-md-4"><label class="form-label text-sm">Validation</label><input class="form-control form-control-sm radius-8" data-f="validation" value="${esc(field.validation || '')}" placeholder="max:255"></div>
                <div class="col-md-4"><label class="form-label text-sm">Options source</label><select class="form-select form-select-sm radius-8" data-f="options_source">${srcOpts}</select></div>
                <div class="col-md-8"><label class="form-label text-sm">Custom options (one per line)</label><textarea class="form-control form-control-sm radius-8" rows="2" data-f="options">${esc(optLines)}</textarea></div>
            </div>
        </div>`;
    }

    function renderReview() {
        collectStateFromDom();
        document.getElementById('reviewName').textContent = state.name || 'Untitled Form';
        const route = state.legacy_route ? '/' + state.legacy_route : '—';
        document.getElementById('reviewMeta').textContent =
            `${state.steps.length} step(s) · ${state.steps.reduce((n, s) => n + (s.fields?.length || 0), 0)} field(s) · Route: ${route}`;

        document.getElementById('reviewSteps').innerHTML = state.steps.map((step, i) => `
            <div class="fc-review-step">
                <div>
                    <span class="fw-semibold">${i + 1}. ${esc(step.title)}</span>
                    <span class="text-secondary-light text-sm ms-8">${esc(step.key)}</span>
                </div>
                <span class="fc-badge fc-badge-primary">${(step.fields || []).length} fields</span>
            </div>
        `).join('') || '<div class="p-20 text-secondary-light text-sm">No steps configured.</div>';
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
    }

    function injectPayload() {
        collectStateFromDom();
        let input = document.getElementById('schemaPayload');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'schema';
            input.id = 'schemaPayload';
            document.getElementById('formBuilderForm').appendChild(input);
        }
        input.value = JSON.stringify({ steps: state.steps });
    }

    // Clickable wizard cards
    document.querySelectorAll('.fc-wizard-step').forEach(el => {
        el.addEventListener('click', () => setWizard(parseInt(el.dataset.step, 10)));
    });

    document.getElementById('prevBtn').addEventListener('click', () => setWizard(currentWizard - 1));
    document.getElementById('nextBtn').addEventListener('click', () => setWizard(currentWizard + 1));

    document.getElementById('addStepBtn').addEventListener('click', () => {
        collectStateFromDom();
        const n = state.steps.length + 1;
        state.steps.push({ key: 'step_' + n, title: 'Step ' + n, description: '', fields: [] });
        renderSteps();
        renderFieldTabs();
    });

    document.getElementById('addFieldBtn').addEventListener('click', () => {
        collectStateFromDom();
        if (!state.steps.length) {
            state.steps.push({ key: 'step_1', title: 'Step 1', description: '', fields: [] });
            activeFieldStep = 0;
            renderSteps();
        }
        const step = state.steps[activeFieldStep];
        step.fields.push({ key: 'field_' + step.fields.length, label: 'New Field', type: 'text', required: false, options: [], options_source: '', width: 'full' });
        renderFields();
    });

    document.getElementById('formBuilderForm').addEventListener('submit', injectPayload);

    document.getElementById('name')?.addEventListener('blur', function () {
        const slug = document.getElementById('slug');
        if (slug && !slug.value) slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    });

    renderSteps();
    renderFieldTabs();
    setWizard(0);
})();
</script>
@endsection
