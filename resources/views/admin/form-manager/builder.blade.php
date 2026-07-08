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
                <input type="hidden" name="wizard_step" id="wizard_step" value="{{ old('wizard_step', $wizardStep ?? 0) }}">

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
                        <div class="fc-form-section-title">Website display</div>
                        <p class="text-secondary-light text-sm mb-20">Control whether this form is live and where visitors can find it on the public site.</p>

                        @php
                            $currentPlacements = old('placements', $form->exists ? $form->placements() : []);
                            if (! is_array($currentPlacements)) {
                                $currentPlacements = [];
                            }
                        @endphp

                        <label class="fc-visibility-status {{ old('is_active', $form->is_active ?? true) ? 'is-on' : '' }}" for="is_active" data-visibility-status>
                            <span class="fc-visibility-status-icon">
                                <iconify-icon icon="solar:power-linear"></iconify-icon>
                            </span>
                            <span class="fc-visibility-status-body">
                                <span class="fw-semibold d-block">Form status</span>
                                <span class="text-secondary-light text-sm">When active, the form accepts submissions and can appear on the website.</span>
                            </span>
                            <span class="fc-visibility-switch">
                                <input type="checkbox"
                                       name="is_active"
                                       value="1"
                                       id="is_active"
                                       class="fc-visibility-switch-input"
                                       @checked(old('is_active', $form->is_active ?? true))>
                                <span class="fc-visibility-switch-track" aria-hidden="true"></span>
                            </span>
                        </label>

                        <div class="fc-visibility-placements mt-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-10 mb-12">
                                <span class="fw-semibold text-sm">Show this form on</span>
                                <span class="text-secondary-light text-xs">Select one or more locations</span>
                            </div>
                            <div class="fc-settings-options fc-settings-options--builder d-flex flex-column gap-12">
                                @foreach($placementOptions as $key => $option)
                                <label class="fc-settings-option {{ in_array($key, $currentPlacements, true) ? 'is-selected' : '' }}"
                                       for="builder_placement_{{ $key }}"
                                       data-settings-option>
                                    <input type="checkbox"
                                           class="fc-settings-checkbox"
                                           name="placements[]"
                                           value="{{ $key }}"
                                           id="builder_placement_{{ $key }}"
                                           data-placement="{{ $key }}"
                                           @checked(in_array($key, $currentPlacements, true))>
                                    <span class="fc-settings-option-icon">
                                        <iconify-icon icon="{{ $option['icon'] ?? 'solar:widget-linear' }}"></iconify-icon>
                                    </span>
                                    <span class="fc-settings-option-body">
                                        <span class="fw-semibold d-block">{{ $option['label'] }}</span>
                                        <span class="text-secondary-light text-sm">{{ $option['description'] }}</span>
                                    </span>
                                    <span class="fc-settings-option-check" aria-hidden="true">
                                        <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="row g-3 mt-20 pt-20 border-top border-neutral-200">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-sm">Hero Button Label</label>
                                <input type="text" name="hero_label" class="form-control radius-8" value="{{ old('hero_label', $form->hero_label) }}" placeholder="Apply Now">
                                <small class="text-secondary-light">Text shown on the landing page button</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-sm">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control radius-8" value="{{ old('sort_order', $form->sort_order ?? 0) }}" min="0">
                                <small class="text-secondary-light">Lower numbers appear first in menus</small>
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
                    <p class="text-secondary-light text-sm mb-12">Define wizard sections shown to users. Each step can contain multiple fields.</p>
                    <div class="fc-builder-hint">
                        <iconify-icon icon="solar:hand-stars-linear"></iconify-icon>
                        <span>Drag the <strong>⋮⋮</strong> handle to reorder. Step name and description sit on one row.</span>
                    </div>
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
                    <div class="fc-builder-hint">
                        <iconify-icon icon="solar:hand-stars-linear"></iconify-icon>
                        <span>Pick a step tab, add fields, then drag <strong>⋮⋮</strong> to reorder. Label + type is enough for most fields.</span>
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
                            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                            <span id="nextBtnLabel">Save &amp; Continue</span>
                            <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                        </button>
                        <button type="button" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn" id="saveBtn" style="display:none">
                            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                            <span>Save Form</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@php
    $initialSchema = ['steps' => []];

    if (old('schema')) {
        $decoded = json_decode(old('schema'), true);
        if (is_array($decoded)) {
            $initialSchema = $decoded;
        }
    } elseif (! empty($schema)) {
        $initialSchema = $schema;
    }

    if (empty($initialSchema['steps'])) {
        $initialSchema['steps'] = [
            [
                'key' => 'step_1',
                'title' => 'Step 1',
                'description' => '',
                'fields' => [],
            ],
        ];
    }

    $schemaJson = json_encode($initialSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $initialWizardStep = (int) old('wizard_step', $wizardStep ?? 0);
@endphp

@section('script')
<script>
(function () {
    const FIELD_TYPES = ['text','email','tel','number','date','textarea','select','radio','checkbox','file','hidden','section','repeatable'];
    const FIELD_TYPE_LABELS = {
        text: 'Short text', email: 'Email', tel: 'Phone', number: 'Number', date: 'Date',
        textarea: 'Long text', select: 'Dropdown', radio: 'Radio buttons', checkbox: 'Checkbox',
        file: 'File upload', hidden: 'Hidden', section: 'Section title', repeatable: 'Repeatable group',
    };
    const OPTIONS_SOURCES = ['','countries','nationalities','genders','education_levels','employment_statuses','hear_about_us','job_titles','staff_roles','meeting_types','meeting_durations','meeting_times','referral_sources','custom'];
    const initialSchema = {!! $schemaJson !!};
    let state = normalizeSchema(initialSchema);
    let currentWizard = {{ $initialWizardStep }};
    let activeFieldStep = 0;
    const TOTAL_STEPS = 4;
    const expandedFields = new Set();

    function slugifyKey(value, fallback) {
        const key = String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
        return key || fallback;
    }

    function fieldExpandedKey(si, fi) {
        return si + '-' + fi;
    }

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
        state.placements = Array.from(document.querySelectorAll('[data-placement]:checked')).map(function (input) {
            return input.value;
        });
        state.show_on_landing = state.placements.includes('landing');

        document.querySelectorAll('#stepsContainer .fc-step-card').forEach((el, i) => {
            if (!state.steps[i]) return;
            state.steps[i].key = el.querySelector('[data-f="key"]')?.value || state.steps[i].key;
            state.steps[i].title = el.querySelector('[data-f="title"]')?.value || state.steps[i].title;
            state.steps[i].description = el.querySelector('[data-f="description"]')?.value || '';
        });

        document.querySelectorAll('#fieldsContainer .fc-field-card').forEach(el => {
            const si = parseInt(el.dataset.stepIndex, 10);
            const fi = parseInt(el.dataset.fieldIndex, 10);
            if (!state.steps[si]?.fields[fi]) return;
            collectFieldFromEl(state.steps[si].fields[fi], el);
        });
    }

    function collectFieldFromEl(f, el) {
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
    }

    function reorderStepsFromDom() {
        const newSteps = [];
        document.querySelectorAll('#stepsContainer .fc-step-card').forEach((el) => {
            const idx = parseInt(el.dataset.stepIndex, 10);
            if (!state.steps[idx]) return;
            const step = { ...state.steps[idx], fields: [...(state.steps[idx].fields || [])] };
            step.key = el.querySelector('[data-f="key"]')?.value || step.key;
            step.title = el.querySelector('[data-f="title"]')?.value || step.title;
            step.description = el.querySelector('[data-f="description"]')?.value || '';
            newSteps.push(step);
        });
        state.steps = newSteps;
        expandedFields.clear();
        renderSteps();
        renderFieldTabs();
    }

    function reorderFieldsFromDom(stepIndex) {
        const step = state.steps[stepIndex];
        if (!step) return;
        const newFields = [];
        document.querySelectorAll('#fieldsContainer .fc-field-card').forEach((el) => {
            const fi = parseInt(el.dataset.fieldIndex, 10);
            if (!step.fields[fi]) return;
            const field = { ...step.fields[fi] };
            collectFieldFromEl(field, el);
            newFields.push(field);
        });
        step.fields = newFields;
        expandedFields.clear();
        renderFields();
    }

    function initDragSort(containerEl, itemSelector, onUpdate) {
        if (!containerEl) return;

        let dragged = null;

        containerEl.querySelectorAll(itemSelector).forEach((item) => {
            const handle = item.querySelector('.fc-drag-handle');
            if (!handle) return;

            handle.setAttribute('draggable', 'true');

            handle.addEventListener('dragstart', (e) => {
                dragged = item;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.stepIndex ?? item.dataset.fieldIndex ?? '');
                requestAnimationFrame(() => item.classList.add('fc-is-dragging'));
            });

            handle.addEventListener('dragend', () => {
                item.classList.remove('fc-is-dragging');
                containerEl.querySelectorAll(itemSelector).forEach(el => el.classList.remove('fc-drag-over'));
                dragged = null;
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                if (!dragged || dragged === item) return;

                const rect = item.getBoundingClientRect();
                const offset = e.clientY - rect.top;

                if (offset > rect.height / 2) {
                    item.after(dragged);
                } else {
                    item.before(dragged);
                }
            });

            item.addEventListener('dragenter', (e) => {
                e.preventDefault();
                if (dragged && dragged !== item) item.classList.add('fc-drag-over');
            });

            item.addEventListener('dragleave', () => item.classList.remove('fc-drag-over'));

            item.addEventListener('drop', (e) => {
                e.preventDefault();
                item.classList.remove('fc-drag-over');
                if (onUpdate) onUpdate();
            });
        });
    }

    function initStepsSortable() {
        initDragSort(document.getElementById('stepsContainer'), '.fc-step-card', reorderStepsFromDom);
    }

    function initFieldsSortable() {
        initDragSort(document.getElementById('fieldsContainer'), '.fc-field-card', () => reorderFieldsFromDom(activeFieldStep));
    }

    function showToast(message, icon) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon || 'success',
            title: message,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });
    }

    function ensurePutMethod() {
        const formEl = document.getElementById('formBuilderForm');
        if (!formEl || formEl.querySelector('[name="_method"]')) return;
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        formEl.appendChild(methodInput);
    }

    async function saveFormAjax(options) {
        options = options || {};
        const advance = options.advance === true;
        const finish = options.finish === true;

        collectStateFromDom();
        ensureDefaultStep();
        injectPayload();

        const name = document.getElementById('name')?.value.trim();
        const slug = document.getElementById('slug')?.value.trim();
        if (!name || !slug) {
            setWizard(0);
            showToast('Please enter a form name and slug before saving.', 'error');
            return false;
        }

        const formEl = document.getElementById('formBuilderForm');
        document.getElementById('wizard_step').value = currentWizard;

        const formData = new FormData(formEl);
        const btn = finish ? document.getElementById('saveBtn') : document.getElementById('nextBtn');

        btn?.classList.add('is-loading');
        btn?.setAttribute('disabled', 'disabled');

        try {
            const response = await fetch(formEl.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json().catch(function () { return {}; });
            if (!response.ok) {
                let errorMessage = data.message || 'Could not save form.';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join(' ');
                }
                throw new Error(errorMessage);
            }

            if (data.edit_url && formEl.action !== data.edit_url) {
                formEl.action = data.edit_url;
                ensurePutMethod();
            }

            showToast(data.message || 'Form saved.');

            if (advance && currentWizard < TOTAL_STEPS - 1) {
                setWizard(currentWizard + 1);
            }

            return true;
        } catch (error) {
            showToast(error.message || 'Could not save form.', 'error');
            return false;
        } finally {
            btn?.classList.remove('is-loading');
            btn?.removeAttribute('disabled');
        }
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

        document.querySelectorAll('.fc-panel').forEach(function (el, i) {
            const isActive = i === step;
            el.classList.toggle('active', isActive);
            if (isActive) {
                el.classList.add('is-entering');
                setTimeout(function () { el.classList.remove('is-entering'); }, 280);
            }
        });

        const progress = ((step + 1) / TOTAL_STEPS) * 100;
        document.getElementById('wizardProgressBar').style.width = progress + '%';

        document.getElementById('prevBtn').disabled = step === 0;

        const isLast = step === TOTAL_STEPS - 1;
        document.getElementById('nextBtn').style.display = isLast ? 'none' : '';
        document.getElementById('saveBtn').style.display = isLast ? '' : 'none';

        const stepLabels = ['Steps', 'Fields', 'Review'];
        const nextLabel = document.getElementById('nextBtnLabel');
        if (nextLabel && !isLast) {
            nextLabel.textContent = 'Save & Continue to ' + (stepLabels[step] || 'Next');
        }

        document.getElementById('wizard_step').value = step;

        if (step === 2) renderFields();
        if (step === 3) renderReview();

        document.querySelector('.fc-wizard')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function renderSteps() {
        const c = document.getElementById('stepsContainer');
        if (!state.steps.length) {
            c.innerHTML = '<div class="fc-empty-state"><iconify-icon icon="solar:layers-linear"></iconify-icon><p>No steps yet. Click <strong>Add Step</strong> to begin.</p></div>';
            return;
        }

        c.innerHTML = state.steps.map((step, i) => `
            <div class="fc-step-card" data-step-index="${i}">
                <span class="fc-drag-handle" draggable="true" title="Drag to reorder"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>
                <span class="fc-step-badge">${i + 1}</span>
                <div class="fc-step-inputs">
                    <input type="text" class="form-control radius-8 fc-step-title-input" data-f="title" value="${esc(step.title)}" placeholder="Step name (e.g. Personal Info)">
                    <input type="text" class="form-control radius-8 fc-step-desc-input" data-f="description" value="${esc(step.description || '')}" placeholder="Optional short description">
                    <input type="hidden" data-f="key" value="${esc(step.key)}">
                </div>
                <button type="button" class="btn btn-outline-danger-600 btn-sm radius-8 fc-btn-icon" data-remove-step="${i}" title="Remove step">
                    <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                </button>
            </div>
        `).join('');

        c.querySelectorAll('[data-f="title"]').forEach((input, i) => {
            input.addEventListener('blur', function () {
                const keyInput = c.querySelectorAll('[data-f="key"]')[i];
                if (keyInput && (!keyInput.dataset.manual || keyInput.dataset.manual !== '1')) {
                    keyInput.value = slugifyKey(this.value, 'step_' + (i + 1));
                }
            });
        });

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

        initStepsSortable();
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
            '<div class="fc-empty-state"><iconify-icon icon="solar:text-field-linear"></iconify-icon><p>No fields in this step.<br>Click <strong>Add Field</strong> to create one.</p></div>';

        c.querySelectorAll('[data-remove-field]').forEach(btn => {
            btn.addEventListener('click', () => {
                collectStateFromDom();
                const si = parseInt(btn.dataset.stepIndex, 10);
                state.steps[si].fields.splice(parseInt(btn.dataset.fieldIndex, 10), 1);
                renderFields();
            });
        });

        c.querySelectorAll('[data-toggle-advanced]').forEach(btn => {
            btn.addEventListener('click', () => {
                const card = btn.closest('.fc-field-card');
                if (!card) return;
                card.classList.toggle('is-open');
                const key = fieldExpandedKey(
                    parseInt(card.dataset.stepIndex, 10),
                    parseInt(card.dataset.fieldIndex, 10),
                );
                if (card.classList.contains('is-open')) expandedFields.add(key);
                else expandedFields.delete(key);
                btn.textContent = card.classList.contains('is-open') ? 'Less ▴' : 'More ▾';
            });
        });

        c.querySelectorAll('.fc-required-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                const checkbox = pill.querySelector('[data-f="required"]');
                if (!checkbox) return;
                checkbox.checked = !checkbox.checked;
                pill.classList.toggle('is-on', checkbox.checked);
            });
        });

        c.querySelectorAll('[data-f="label"]').forEach((input) => {
            input.addEventListener('blur', function () {
                const card = this.closest('.fc-field-card');
                const keyInput = card?.querySelector('[data-f="key"]');
                if (keyInput && keyInput.dataset.manual !== '1') {
                    const si = parseInt(card.dataset.stepIndex, 10);
                    const fi = parseInt(card.dataset.fieldIndex, 10);
                    keyInput.value = slugifyKey(this.value, 'field_' + si + '_' + fi);
                }
            });
        });

        c.querySelectorAll('[data-f="key"]').forEach(input => {
            input.addEventListener('input', () => { input.dataset.manual = '1'; });
        });

        if ((step.fields || []).length) initFieldsSortable();
    }

    function fieldRowHtml(si, fi, field) {
        const optLines = (field.options || []).join('\n');
        const isOpen = expandedFields.has(fieldExpandedKey(si, fi));
        const typeOpts = FIELD_TYPES.map(t =>
            `<option value="${t}" ${field.type === t ? 'selected' : ''}>${FIELD_TYPE_LABELS[t] || t}</option>`
        ).join('');
        const srcOpts = OPTIONS_SOURCES.map(s =>
            `<option value="${s}" ${field.options_source === s ? 'selected' : ''}>${s || '— Static options —'}</option>`
        ).join('');
        const needsOptions = ['select', 'radio'].includes(field.type);

        return `
        <div class="fc-field-card ${isOpen ? 'is-open' : ''}" data-step-index="${si}" data-field-index="${fi}">
            <div class="fc-field-card-header">
                <span class="fc-drag-handle" draggable="true" title="Drag to reorder"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>
                <div class="fc-field-main">
                    <input class="form-control radius-8" data-f="label" value="${esc(field.label)}" placeholder="Field label (e.g. Full Name)">
                    <select class="form-select radius-8 fc-field-type-select" data-f="type">${typeOpts}</select>
                    <label class="fc-required-pill ${field.required ? 'is-on' : ''}">
                        <input type="checkbox" class="d-none" data-f="required" ${field.required ? 'checked' : ''}>
                        Required
                    </label>
                </div>
                <div class="fc-field-actions">
                    <button type="button" class="fc-toggle-advanced" data-toggle-advanced>${isOpen ? 'Less ▴' : 'More ▾'}</button>
                    <button type="button" class="btn btn-outline-danger-600 btn-sm fc-btn-icon" data-remove-field data-step-index="${si}" data-field-index="${fi}" title="Remove">
                        <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                    </button>
                </div>
            </div>
            <div class="fc-field-advanced">
                <div class="fc-field-advanced-grid">
                    <div>
                        <label class="form-label text-sm text-secondary-light">Field key <small>(auto-generated)</small></label>
                        <input class="form-control form-control-sm radius-8" data-f="key" value="${esc(field.key)}">
                    </div>
                    <div>
                        <label class="form-label text-sm text-secondary-light">Width</label>
                        <select class="form-select form-select-sm radius-8" data-f="width">
                            <option value="full" ${field.width === 'full' || !field.width ? 'selected' : ''}>Full width</option>
                            <option value="half" ${field.width === 'half' ? 'selected' : ''}>Half width</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-sm text-secondary-light">Placeholder</label>
                        <input class="form-control form-control-sm radius-8" data-f="placeholder" value="${esc(field.placeholder || '')}" placeholder="Hint text inside the field">
                    </div>
                    <div>
                        <label class="form-label text-sm text-secondary-light">Help text</label>
                        <input class="form-control form-control-sm radius-8" data-f="help_text" value="${esc(field.help_text || '')}" placeholder="Shown below the field">
                    </div>
                    <div class="${needsOptions ? '' : 'd-none'}">
                        <label class="form-label text-sm text-secondary-light">Options source</label>
                        <select class="form-select form-select-sm radius-8" data-f="options_source">${srcOpts}</select>
                    </div>
                    <div class="${needsOptions ? 'full-width' : 'd-none'}">
                        <label class="form-label text-sm text-secondary-light">Dropdown / radio options <small>(one per line)</small></label>
                        <textarea class="form-control form-control-sm radius-8" rows="2" data-f="options" placeholder="Option 1&#10;Option 2">${esc(optLines)}</textarea>
                    </div>
                    <div class="full-width">
                        <label class="form-label text-sm text-secondary-light">Validation rule</label>
                        <input class="form-control form-control-sm radius-8" data-f="validation" value="${esc(field.validation || '')}" placeholder="e.g. max:255">
                    </div>
                </div>
            </div>
        </div>`;
    }

    function renderReview() {
        collectStateFromDom();
        document.getElementById('reviewName').textContent = state.name || 'Untitled Form';
        const route = state.legacy_route ? '/' + state.legacy_route : '—';
        document.getElementById('reviewMeta').textContent =
            `${state.steps.length} step(s) · ${state.steps.reduce((n, s) => n + (s.fields?.length || 0), 0)} field(s) · Route: ${route} · ${state.is_active ? 'Active' : 'Inactive'} · ${state.placements.length ? state.placements.join(', ') : 'Not shown on site'}`;

        document.getElementById('reviewSteps').innerHTML = state.steps.map((step, i) => `
            <div class="fc-review-step">
                <div>
                    <span class="fw-semibold">${i + 1}. ${esc(step.title)}</span>
                    ${(step.fields || []).length ? `<div class="text-secondary-light text-sm mt-4">${(step.fields || []).map(f => esc(f.label)).join(' · ')}</div>` : '<div class="text-secondary-light text-sm mt-4">No fields</div>'}
                </div>
                <span class="fc-badge fc-badge-primary">${(step.fields || []).length} fields</span>
            </div>
        `).join('') || '<div class="fc-empty-state text-sm">No steps configured.</div>';
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
    }

    function injectPayload() {
        collectStateFromDom();
        ensureUniqueFieldKeys();

        if (!state.steps.length) {
            state.steps.push({
                key: 'step_1',
                title: 'Step 1',
                description: '',
                fields: [],
            });
        }

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

    function ensureUniqueFieldKeys() {
        const used = new Set();

        state.steps.forEach((step, stepIndex) => {
            (step.fields || []).forEach((field, fieldIndex) => {
                let key = (field.key || '').trim() || `field_${stepIndex}_${fieldIndex}`;

                while (used.has(key)) {
                    key = `${key}_${fieldIndex}`;
                }

                field.key = key;
                used.add(key);
            });
        });
    }

    function ensureDefaultStep() {
        if (!state.steps.length) {
            state.steps.push({
                key: 'step_1',
                title: 'Step 1',
                description: '',
                fields: [],
            });
        }
    }

    // Clickable wizard cards — instant step switch
    document.querySelectorAll('.fc-wizard-step').forEach(function (el) {
        el.addEventListener('click', function () {
            el.classList.add('is-pressed');
            setTimeout(function () { el.classList.remove('is-pressed'); }, 160);
            setWizard(parseInt(el.dataset.step, 10));
        });
    });

    document.getElementById('prevBtn')?.addEventListener('click', function () {
        setWizard(currentWizard - 1);
    });

    document.getElementById('nextBtn')?.addEventListener('click', function () {
        saveFormAjax({ advance: true });
    });

    document.getElementById('saveBtn')?.addEventListener('click', function () {
        saveFormAjax({ finish: true });
    });

    document.getElementById('formBuilderForm')?.addEventListener('submit', function (event) {
        event.preventDefault();
    });

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
        const fieldIndex = step.fields.length;
        step.fields.push({
            key: 'field_' + activeFieldStep + '_' + fieldIndex,
            label: '',
            type: 'text',
            required: false,
            options: [],
            options_source: '',
            width: 'full',
        });
        renderFields();
        setTimeout(() => {
            const cards = document.querySelectorAll('#fieldsContainer .fc-field-card');
            const last = cards[cards.length - 1];
            last?.querySelector('[data-f="label"]')?.focus();
        }, 50);
    });

    function syncVisibilityUi() {
        document.querySelectorAll('[data-settings-option]').forEach(function (option) {
            const input = option.querySelector('[data-placement]');
            option.classList.toggle('is-selected', Boolean(input && input.checked));
        });

        const statusCard = document.querySelector('[data-visibility-status]');
        const activeInput = document.getElementById('is_active');
        if (statusCard && activeInput) {
            statusCard.classList.toggle('is-on', activeInput.checked);
        }
    }

    function initVisibilityControls() {
        document.querySelectorAll('[data-placement]').forEach(function (input) {
            input.addEventListener('change', syncVisibilityUi);
        });

        document.getElementById('is_active')?.addEventListener('change', syncVisibilityUi);
        syncVisibilityUi();
    }

    document.getElementById('name')?.addEventListener('blur', function () {
        const slug = document.getElementById('slug');
        if (slug && !slug.value) slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    });

    ensureDefaultStep();
    renderSteps();
    renderFieldTabs();
    initVisibilityControls();
    setWizard({{ $initialWizardStep }});
})();
</script>
@endsection
