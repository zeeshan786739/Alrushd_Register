/* global Swal */
window.FormBuilder = (function () {
    'use strict';

    const FIELD_TYPES = ['section', 'text', 'email', 'tel', 'number', 'date', 'textarea', 'select', 'radio', 'checkbox', 'file'];
    const FIELD_TYPE_LABELS = {
        section: 'Section divider', text: 'Short text', email: 'Email', tel: 'Phone', number: 'Number', date: 'Date',
        textarea: 'Long text', select: 'Dropdown', radio: 'Radio', checkbox: 'Checkbox', file: 'File',
    };
    const FIELD_TYPE_ICONS = {
        section: 'solar:widget-5-linear',
        text: 'solar:text-field-linear', email: 'solar:letter-linear', tel: 'solar:phone-linear',
        number: 'solar:hashtag-linear', date: 'solar:calendar-linear', textarea: 'solar:align-left-linear',
        select: 'solar:alt-arrow-down-linear', radio: 'solar:record-circle-linear',
        checkbox: 'solar:check-square-linear', file: 'solar:upload-linear',
    };
    const TRANSITIONS = [
        { value: 'slide', label: 'Slide' },
        { value: 'fade', label: 'Fade' },
        { value: 'none', label: 'None' },
    ];
    let OPTIONS_SOURCES = Object.keys({
        '': 'Static',
        nationalities: 'Nationalities',
        genders: 'Genders',
        student_years: 'Year Groups',
        countries: 'Countries',
        yes_no: 'Yes / No',
    });
    let OPTION_SOURCE_LABELS = {};
    let OPTION_SOURCE_GROUPS = [];
    let OPTION_SOURCE_URL = '';
    const optionCache = {};

    let state = { steps: [] };
    let activeFieldStep = 0;
    let previewStep = 0;
    let previewTransition = 'slide';
    let expandedFields = new Set();
    let previewDebounce = null;
    let isEdit = false;
    let isDirty = false;

    function markDirty() {
        isDirty = true;
        const el = document.getElementById('saveStatus');
        if (el) {
            el.className = 'fc-pro-save-status is-dirty';
            el.innerHTML = '<iconify-icon icon="solar:pen-2-linear"></iconify-icon> Unsaved changes';
        }
    }

    function markSaved() {
        isDirty = false;
        const el = document.getElementById('saveStatus');
        if (el) {
            el.className = 'fc-pro-save-status';
            el.innerHTML = '<iconify-icon icon="solar:check-circle-linear"></iconify-icon> All changes saved';
        }
    }

    function collectActiveStepFromPanel() {
        const panel = document.getElementById('stepSettingsPanel');
        const step = state.steps[activeFieldStep];
        if (!panel || !step) return;
        step.key = panel.querySelector('[data-f="key"]')?.value || step.key;
        step.title = panel.querySelector('[data-f="title"]')?.value || step.title;
        step.description = panel.querySelector('[data-f="description"]')?.value || '';
        step.transition = panel.querySelector('[data-f="transition"]')?.value || 'slide';
    }

    function slugifyKey(value, fallback) {
        const key = String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
        return key || fallback;
    }

    function fieldExpandedKey(si, fi) {
        return si + '-' + fi;
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

    function normalizeSchema(s) {
        s = s || { steps: [] };
        if (!Array.isArray(s.steps)) s.steps = [];
        s.steps.forEach((step, si) => {
            step.key = step.key || 'step_' + (si + 1);
            step.title = step.title || 'Step ' + (si + 1);
            step.description = step.description || '';
            step.transition = step.transition || 'slide';
            step.fields = step.fields || [];
            step.fields.forEach((f, fi) => {
                f.key = f.key || 'field_' + si + '_' + fi;
                f.label = f.label || 'Field';
                f.type = f.type || 'text';
                f.required = !!f.required;
                f.options = f.options || [];
                f.options_source = f.options_source || '';
                f.width = f.col_span === 1 ? 'half' : (f.width || 'full');
            });
        });
        return s;
    }

    function collectStateFromDom() {
        state.name = document.getElementById('name')?.value || '';
        state.slug = document.getElementById('slug')?.value || document.getElementById('slugInput')?.value || '';
        state.description = document.querySelector('[name="description"]')?.value || '';
        state.legacy_route = document.getElementById('legacy_route')?.value || '';
        state.success_route = document.querySelector('[name="success_route"]')?.value || '';
        state.submit_method = document.querySelector('[name="submit_method"]')?.value || 'urlencoded';
        state.handler = document.querySelector('[name="handler"]')?.value || 'dynamic';
        state.hero_label = document.querySelector('[name="hero_label"]')?.value || '';
        state.hero_variant = document.querySelector('[name="hero_variant"]')?.value || 'outline';
        state.sort_order = parseInt(document.querySelector('[name="sort_order"]')?.value || '0', 10);
        state.is_active = document.getElementById('is_active')?.checked ?? true;
        state.placements = Array.from(document.querySelectorAll('[data-placement]:checked')).map(i => i.value);
        state.show_on_landing = state.placements.includes('landing');

        document.querySelectorAll('#stepsContainer .fc-step-card').forEach((el, i) => {
            if (i === activeFieldStep) return;
            if (!state.steps[i]) return;
        });

        collectActiveStepFromPanel();

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
        if (f.type === 'section') {
            f.required = false;
            f.options = [];
            f.options_source = '';
            f.width = 'full';
            return;
        }
        f.placeholder = el.querySelector('[data-f="placeholder"]')?.value || '';
        f.help_text = el.querySelector('[data-f="help_text"]')?.value || '';
        f.required = el.querySelector('[data-f="required"]')?.checked ?? false;
        f.width = el.querySelector('[data-f="width"]')?.value || 'full';
        f.options_source = el.querySelector('[data-f="options_source"]')?.value || '';
        const optRaw = el.querySelector('[data-f="options"]')?.value || '';
        if (['select', 'radio'].includes(f.type) && f.options_source) {
            f.options = getSelectedOptionsFromCard(el);
        } else {
            f.options = optRaw.split('\n').map(l => l.trim()).filter(Boolean);
        }
        f.validation = el.querySelector('[data-f="validation"]')?.value || '';
    }

    function getSelectedOptionsFromCard(card) {
        const selected = [];
        card.querySelectorAll('[data-option-chip].is-selected').forEach(chip => {
            if (chip.dataset.value) selected.push(chip.dataset.value);
        });
        return selected;
    }

    function buildGroupedSourceOptions(field) {
        const current = field.options_source || '';
        let html = `<option value="">— Manual list —</option>`;

        if (OPTION_SOURCE_GROUPS.length) {
            OPTION_SOURCE_GROUPS.forEach(group => {
                html += `<optgroup label="${esc(group.label)}">`;
                (group.sources || []).forEach(key => {
                    const label = OPTION_SOURCE_LABELS[key] || key;
                    html += `<option value="${esc(key)}" ${current === key ? 'selected' : ''}>${esc(label)}</option>`;
                });
                html += '</optgroup>';
            });
        } else {
            OPTIONS_SOURCES.filter(s => s).forEach(key => {
                const label = OPTION_SOURCE_LABELS[key] || key;
                html += `<option value="${esc(key)}" ${current === key ? 'selected' : ''}>${esc(label)}</option>`;
            });
        }

        return html;
    }

    async function fetchSourceOptions(source) {
        if (!source) return [];
        if (optionCache[source]) return optionCache[source];
        if (!OPTION_SOURCE_URL) return [];

        const response = await fetch(`${OPTION_SOURCE_URL}/${encodeURIComponent(source)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) throw new Error('Could not load options');
        const data = await response.json();
        optionCache[source] = data.options || [];
        return optionCache[source];
    }

    function renderOptionChips(container, options, selectedValues) {
        const search = (container.dataset.search || '').toLowerCase();
        const selected = new Set(selectedValues || []);
        const selectAllByDefault = selected.size === 0 && selectedValues !== null;

        const chips = (options || []).filter(opt => {
            const label = (opt.label || opt.value || '').toLowerCase();
            return !search || label.includes(search);
        }).map(opt => {
            const value = opt.value ?? opt.label;
            const isOn = selectAllByDefault || selected.has(value);
            return `<button type="button" class="fc-option-chip${isOn ? ' is-selected' : ''}" data-option-chip data-value="${esc(value)}" title="${esc(opt.label || value)}">
                <i class="ri-check-line" aria-hidden="true"></i>
                <span>${esc(opt.label || value)}</span>
            </button>`;
        }).join('');

        const grid = container.querySelector('[data-option-grid]');
        const empty = container.querySelector('[data-option-empty]');
        if (grid) grid.innerHTML = chips || '';
        if (empty) empty.classList.toggle('d-none', !!(options || []).length);

        updateOptionPickerCount(container);
    }

    function updateOptionPickerCount(container) {
        const countEl = container.querySelector('[data-option-count]');
        const total = container.querySelectorAll('[data-option-chip]').length;
        const selected = container.querySelectorAll('[data-option-chip].is-selected').length;
        if (countEl) {
            countEl.textContent = total ? `${selected} of ${total} selected` : 'No values found';
        }
    }

    async function mountOptionPicker(card, field) {
        const panel = card.querySelector('[data-option-picker]');
        const manual = card.querySelector('[data-option-manual]');
        const source = field.options_source || card.querySelector('[data-f="options_source"]')?.value || '';

        if (!panel) return;

        if (!source) {
            panel.classList.add('d-none');
            manual?.classList.remove('d-none');
            return;
        }

        panel.classList.remove('d-none');
        manual?.classList.add('d-none');

        const grid = panel.querySelector('[data-option-grid]');
        if (grid) grid.innerHTML = '<div class="fc-option-loading">Loading values…</div>';

        try {
            const options = await fetchSourceOptions(source);
            const selected = Array.isArray(field.options) && field.options.length ? field.options : null;
            renderOptionChips(panel, options, selected);
        } catch (err) {
            if (grid) grid.innerHTML = `<div class="fc-option-error">${esc(err.message || 'Failed to load')}</div>`;
        }
    }

    function bindOptionPickerEvents(card) {
        const picker = card.querySelector('[data-option-picker]');
        const sourceSelect = card.querySelector('[data-f="options_source"]');

        sourceSelect?.addEventListener('change', async function () {
            const si = parseInt(card.dataset.stepIndex, 10);
            const fi = parseInt(card.dataset.fieldIndex, 10);
            const field = state.steps[si]?.fields[fi];
            if (!field) return;
            field.options_source = this.value;
            field.options = [];
            await mountOptionPicker(card, field);
            schedulePreview();
        });

        if (!picker) return;

        picker.querySelector('[data-option-search]')?.addEventListener('input', function () {
            picker.dataset.search = this.value;
            const source = sourceSelect?.value;
            if (!source || !optionCache[source]) return;
            const selected = getSelectedOptionsFromCard(card);
            renderOptionChips(picker, optionCache[source], selected);
        });

        picker.querySelector('[data-select-all]')?.addEventListener('click', () => {
            picker.querySelectorAll('[data-option-chip]').forEach(chip => chip.classList.add('is-selected'));
            updateOptionPickerCount(picker);
            schedulePreview();
        });

        picker.querySelector('[data-clear-all]')?.addEventListener('click', () => {
            picker.querySelectorAll('[data-option-chip]').forEach(chip => chip.classList.remove('is-selected'));
            updateOptionPickerCount(picker);
            schedulePreview();
        });

        picker.addEventListener('click', e => {
            const chip = e.target.closest('[data-option-chip]');
            if (!chip) return;
            chip.classList.toggle('is-selected');
            updateOptionPickerCount(picker);
            schedulePreview();
        });
    }

    function getFieldOptionsForPreview(field) {
        if (field.options?.length) return field.options;
        if (field.options_source && optionCache[field.options_source]?.length) {
            return optionCache[field.options_source].map(o => o.value || o.label);
        }
        return field.options || [];
    }

    function schedulePreview() {
        clearTimeout(previewDebounce);
        previewDebounce = setTimeout(() => {
            renderPreview(false);
            updateStatsBar();
        }, 120);
        markDirty();
    }

    function setMode(mode) {
        document.querySelectorAll('.fc-pro-mode-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.mode === mode);
        });
        document.querySelectorAll('.fc-pro-panel').forEach(panel => {
            panel.classList.toggle('active', panel.dataset.panel === mode);
        });
    }

    function setActiveStep(index) {
        collectStateFromDom();
        activeFieldStep = index;
        previewStep = index;
        renderSteps();
        renderStepSettings();
        renderFields();
        renderPreview(false);
        updateStatsBar();
    }

    function updateStatsBar() {
        const totalSteps = state.steps.length;
        const totalFields = state.steps.reduce((n, s) => n + (s.fields || []).filter(f => f.type !== 'section').length, 0);
        const stepsEl = document.getElementById('statSteps');
        const fieldsEl = document.getElementById('statFields');
        const activeEl = document.getElementById('statActiveStep');
        if (stepsEl) stepsEl.textContent = totalSteps;
        if (fieldsEl) fieldsEl.textContent = totalFields;
        if (activeEl) activeEl.textContent = totalSteps ? `Step ${activeFieldStep + 1} of ${totalSteps}` : 'No steps';
    }

    function renderStepSettings() {
        const panel = document.getElementById('stepSettingsPanel');
        const step = state.steps[activeFieldStep];
        const chip = document.getElementById('activeStepChip');
        if (!panel) return;

        if (!step) {
            panel.classList.add('is-hidden');
            return;
        }
        panel.classList.remove('is-hidden');

        panel.querySelector('[data-f="title"]').value = step.title || '';
        panel.querySelector('[data-f="description"]').value = step.description || '';
        panel.querySelector('[data-f="transition"]').value = step.transition || 'slide';
        panel.querySelector('[data-f="key"]').value = step.key || '';

        if (chip) chip.textContent = 'Step ' + (activeFieldStep + 1);
    }

    function bindStepSettingsEvents() {
        const panel = document.getElementById('stepSettingsPanel');
        if (!panel || panel.dataset.bound === '1') return;
        panel.dataset.bound = '1';

        panel.querySelectorAll('[data-f="title"], [data-f="description"], [data-f="transition"]').forEach(el => {
            el.addEventListener('input', () => {
                collectActiveStepFromPanel();
                renderSteps();
                schedulePreview();
            });
        });

        panel.querySelector('[data-f="title"]')?.addEventListener('blur', function () {
            const keyInput = panel.querySelector('[data-f="key"]');
            if (keyInput && keyInput.dataset.manual !== '1') {
                keyInput.value = slugifyKey(this.value, 'step_' + (activeFieldStep + 1));
            }
        });
    }

    function reorderStepsFromDom() {
        const newSteps = [];
        document.querySelectorAll('#stepsContainer .fc-step-card').forEach(el => {
            const idx = parseInt(el.dataset.stepIndex, 10);
            if (!state.steps[idx]) return;
            newSteps.push({ ...state.steps[idx], fields: [...(state.steps[idx].fields || [])] });
        });
        state.steps = newSteps;
        if (activeFieldStep >= state.steps.length) activeFieldStep = Math.max(0, state.steps.length - 1);
        expandedFields.clear();
        renderSteps();
        renderStepSettings();
        renderFields();
        schedulePreview();
    }

    function reorderFieldsFromDom() {
        const step = state.steps[activeFieldStep];
        if (!step) return;
        const newFields = [];
        document.querySelectorAll('#fieldsContainer .fc-field-card').forEach(el => {
            const fi = parseInt(el.dataset.fieldIndex, 10);
            if (!step.fields[fi]) return;
            const field = { ...step.fields[fi] };
            collectFieldFromEl(field, el);
            newFields.push(field);
        });
        step.fields = newFields;
        expandedFields.clear();
        renderFields();
        schedulePreview();
    }

    function initDragSort(containerEl, itemSelector, onUpdate) {
        if (!containerEl) return;
        let dragged = null;

        containerEl.querySelectorAll(itemSelector).forEach(item => {
            const handle = item.querySelector('.fc-drag-handle');
            if (!handle) return;
            handle.setAttribute('draggable', 'true');

            handle.addEventListener('dragstart', e => {
                dragged = item;
                e.dataTransfer.effectAllowed = 'move';
                requestAnimationFrame(() => item.classList.add('fc-is-dragging'));
            });

            handle.addEventListener('dragend', () => {
                item.classList.remove('fc-is-dragging');
                containerEl.querySelectorAll(itemSelector).forEach(el => el.classList.remove('fc-drag-over'));
                dragged = null;
            });

            item.addEventListener('dragover', e => {
                e.preventDefault();
                if (!dragged || dragged === item) return;
                const rect = item.getBoundingClientRect();
                if (e.clientY - rect.top > rect.height / 2) item.after(dragged);
                else item.before(dragged);
            });

            item.addEventListener('dragenter', e => {
                e.preventDefault();
                if (dragged && dragged !== item) item.classList.add('fc-drag-over');
            });
            item.addEventListener('dragleave', () => item.classList.remove('fc-drag-over'));
            item.addEventListener('drop', e => {
                e.preventDefault();
                item.classList.remove('fc-drag-over');
                if (onUpdate) onUpdate();
            });
        });
    }

    function renderPalette() {
        const palette = document.getElementById('fieldPalette');
        if (!palette) return;
        palette.innerHTML = FIELD_TYPES.map(type => `
            <button type="button" class="fc-pro-palette-item" data-add-type="${type}" title="Add ${FIELD_TYPE_LABELS[type]}" draggable="true">
                <iconify-icon icon="${FIELD_TYPE_ICONS[type]}"></iconify-icon>
                <span>${FIELD_TYPE_LABELS[type]}</span>
            </button>
        `).join('');

        palette.querySelectorAll('[data-add-type]').forEach(btn => {
            btn.addEventListener('click', () => addField(btn.dataset.addType));
        });
    }

    function renderSteps() {
        const c = document.getElementById('stepsContainer');
        if (!state.steps.length) {
            c.innerHTML = '<div class="fc-empty-state fc-empty-state--compact"><p>No steps yet</p></div>';
            return;
        }

        c.innerHTML = state.steps.map((step, i) => `
            <div class="fc-step-card fc-step-pill ${i === activeFieldStep ? 'is-active' : ''}" data-step-index="${i}">
                <span class="fc-drag-handle" title="Drag to reorder"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>
                <button type="button" class="fc-pro-step-select" data-select-step="${i}">
                    <span class="fc-step-badge">${i + 1}</span>
                    <span class="fc-pro-step-info">
                        <span class="fc-pro-step-name">${esc(step.title)}</span>
                        <span class="fc-pro-step-count">${(step.fields || []).length} field${(step.fields || []).length === 1 ? '' : 's'}</span>
                    </span>
                </button>
                <button type="button" class="fc-pro-icon-btn fc-pro-icon-btn--danger fc-pro-icon-btn--sm" data-remove-step="${i}" title="Remove step">
                    <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                </button>
            </div>
        `).join('');

        c.querySelectorAll('[data-select-step]').forEach(btn => {
            btn.addEventListener('click', () => setActiveStep(parseInt(btn.dataset.selectStep, 10)));
        });

        c.querySelectorAll('[data-remove-step]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                collectStateFromDom();
                const idx = parseInt(btn.dataset.removeStep, 10);
                if (state.steps.length <= 1) {
                    showToast('A form needs at least one step.', 'warning');
                    return;
                }
                state.steps.splice(idx, 1);
                if (activeFieldStep >= state.steps.length) activeFieldStep = Math.max(0, state.steps.length - 1);
                renderSteps();
                renderStepSettings();
                renderFields();
                schedulePreview();
            });
        });

        initDragSort(c, '.fc-step-card', reorderStepsFromDom);
    }

    function fieldRowHtml(si, fi, field) {
        if (field.type === 'section') {
            return `
            <div class="fc-field-card fc-field-card--section" data-step-index="${si}" data-field-index="${fi}">
                <div class="fc-field-card-header">
                    <span class="fc-drag-handle" title="Drag to reorder"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>
                    <span class="fc-pro-field-icon"><iconify-icon icon="solar:widget-5-linear"></iconify-icon></span>
                    <div class="fc-field-body flex-grow-1">
                        <input class="fc-field-label-input form-control radius-8" data-f="label" value="${esc(field.label)}" placeholder="Section heading (e.g. Student Details)">
                        <input type="hidden" data-f="key" value="${esc(field.key)}">
                        <input type="hidden" data-f="type" value="section">
                    </div>
                    <button type="button" class="fc-meta-btn fc-meta-btn--danger" data-remove-field data-step-index="${si}" data-field-index="${fi}" title="Delete section">
                        <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                    </button>
                </div>
            </div>`;
        }

        const optLines = (field.options || []).join('\n');
        const isOpen = expandedFields.has(fieldExpandedKey(si, fi));
        const typeOpts = FIELD_TYPES.filter(t => t !== 'section').map(t => `<option value="${t}" ${field.type === t ? 'selected' : ''}>${FIELD_TYPE_LABELS[t]}</option>`).join('');
        const srcOpts = buildGroupedSourceOptions(field);
        const needsOptions = ['select', 'radio'].includes(field.type);
        const usesModule = !!(field.options_source);

        return `
        <div class="fc-field-card fc-field-card--pro ${isOpen ? 'is-open' : ''} ${field.required ? 'is-required' : ''}" data-step-index="${si}" data-field-index="${fi}">
            <div class="fc-field-card-header">
                <span class="fc-drag-handle" title="Drag to reorder"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>
                <span class="fc-field-index">${String(fi + 1).padStart(2, '0')}</span>
                <span class="fc-pro-field-icon"><iconify-icon icon="${FIELD_TYPE_ICONS[field.type] || 'solar:text-field-linear'}"></iconify-icon></span>
                <div class="fc-field-body">
                    <input class="fc-field-label-input form-control radius-8" data-f="label" value="${esc(field.label)}" placeholder="Enter field label…">
                    <div class="fc-field-meta">
                        <select class="form-select form-select-sm radius-8 fc-field-type-select" data-f="type">${typeOpts}</select>
                        <label class="fc-required-pill ${field.required ? 'is-on' : ''}" title="Required field">
                            <input type="checkbox" class="d-none" data-f="required" ${field.required ? 'checked' : ''}>
                            <iconify-icon icon="solar:star-linear"></iconify-icon>
                            Required
                        </label>
                        <button type="button" class="fc-meta-btn" data-toggle-advanced title="Advanced settings">
                            <iconify-icon icon="solar:settings-linear"></iconify-icon>
                            ${isOpen ? 'Hide' : 'Settings'}
                        </button>
                        <button type="button" class="fc-meta-btn" data-duplicate-field data-step-index="${si}" data-field-index="${fi}" title="Duplicate">
                            <iconify-icon icon="solar:copy-linear"></iconify-icon>
                        </button>
                        <button type="button" class="fc-meta-btn fc-meta-btn--danger" data-remove-field data-step-index="${si}" data-field-index="${fi}" title="Delete">
                            <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>
            <div class="fc-field-advanced">
                <div class="fc-field-advanced-grid">
                    <div><label class="form-label text-sm text-secondary-light">Field key</label>
                        <input class="form-control form-control-sm radius-8" data-f="key" value="${esc(field.key)}"></div>
                    <div><label class="form-label text-sm text-secondary-light">Width</label>
                        <select class="form-select form-select-sm radius-8" data-f="width">
                            <option value="full" ${field.width === 'full' || !field.width ? 'selected' : ''}>Full</option>
                            <option value="half" ${field.width === 'half' ? 'selected' : ''}>Half</option>
                        </select></div>
                    <div><label class="form-label text-sm text-secondary-light">Placeholder</label>
                        <input class="form-control form-control-sm radius-8" data-f="placeholder" value="${esc(field.placeholder || '')}"></div>
                    <div><label class="form-label text-sm text-secondary-light">Help text</label>
                        <input class="form-control form-control-sm radius-8" data-f="help_text" value="${esc(field.help_text || '')}"></div>
                    <div class="${needsOptions ? 'full-width' : 'd-none'}">
                        <label class="form-label text-sm text-secondary-light fw-semibold">Dropdown values</label>
                        <div class="fc-option-builder">
                            <label class="form-label text-xs text-secondary-light mb-4">Data source</label>
                            <select class="form-select form-select-sm radius-8" data-f="options_source">${srcOpts}</select>

                            <div class="fc-option-picker ${usesModule ? '' : 'd-none'}" data-option-picker>
                                <div class="fc-option-picker-toolbar">
                                    <span class="fc-option-picker-count" data-option-count>Loading…</span>
                                    <div class="fc-option-picker-actions">
                                        <button type="button" class="fc-option-toolbar-btn" data-select-all>Select all</button>
                                        <button type="button" class="fc-option-toolbar-btn" data-clear-all>Clear</button>
                                    </div>
                                </div>
                                <div class="fc-option-search-wrap">
                                    <i class="ri-search-line" aria-hidden="true"></i>
                                    <input type="search" class="form-control form-control-sm radius-8" data-option-search placeholder="Search values…">
                                </div>
                                <div class="fc-option-grid" data-option-grid></div>
                                <p class="fc-option-empty text-secondary-light text-sm mb-0 d-none" data-option-empty>No values in this module yet. Add them under Admission Form in the sidebar.</p>
                            </div>

                            <div class="fc-option-manual ${usesModule ? 'd-none' : ''}" data-option-manual>
                                <label class="form-label text-xs text-secondary-light mt-8 mb-4">Manual options (one per line)</label>
                                <textarea class="form-control form-control-sm radius-8" rows="3" data-f="options" placeholder="Option 1&#10;Option 2">${esc(optLines)}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="full-width"><label class="form-label text-sm text-secondary-light">Validation</label>
                        <input class="form-control form-control-sm radius-8" data-f="validation" value="${esc(field.validation || '')}" placeholder="e.g. max:255"></div>
                </div>
            </div>
        </div>`;
    }

    function renderFields() {
        const c = document.getElementById('fieldsContainer');
        const step = state.steps[activeFieldStep];

        if (!step) {
            c.innerHTML = '<div class="fc-empty-state fc-empty-state--pro"><div class="fc-empty-icon"><iconify-icon icon="solar:layers-linear"></iconify-icon></div><h6>No steps yet</h6><p>Add your first step from the left panel</p></div>';
            return;
        }

        c.innerHTML = (step.fields || []).map((field, fi) => fieldRowHtml(activeFieldStep, fi, field)).join('') ||
            '<div class="fc-empty-state fc-empty-state--pro"><div class="fc-empty-icon"><iconify-icon icon="solar:text-field-linear"></iconify-icon></div><h6>No fields in this step</h6><p>Click a field type above or use <strong>Add Field</strong></p></div>';

        bindFieldEvents(c);
        if ((step.fields || []).length) initDragSort(c, '.fc-field-card', reorderFieldsFromDom);
        updateStatsBar();

        c.querySelectorAll('.fc-field-card').forEach(card => {
            const si = parseInt(card.dataset.stepIndex, 10);
            const fi = parseInt(card.dataset.fieldIndex, 10);
            const field = state.steps[si]?.fields[fi];
            if (field && ['select', 'radio'].includes(field.type)) {
                bindOptionPickerEvents(card);
                if (field.options_source) mountOptionPicker(card, field);
            }
        });
    }

    function bindFieldEvents(c) {
        c.querySelectorAll('[data-remove-field]').forEach(btn => {
            btn.addEventListener('click', () => {
                collectStateFromDom();
                state.steps[parseInt(btn.dataset.stepIndex, 10)].fields.splice(parseInt(btn.dataset.fieldIndex, 10), 1);
                renderFields();
                renderSteps();
                schedulePreview();
            });
        });

        c.querySelectorAll('[data-toggle-advanced]').forEach(btn => {
            btn.addEventListener('click', () => {
                const card = btn.closest('.fc-field-card');
                card.classList.toggle('is-open');
                const key = fieldExpandedKey(parseInt(card.dataset.stepIndex, 10), parseInt(card.dataset.fieldIndex, 10));
                if (card.classList.contains('is-open')) expandedFields.add(key);
                else expandedFields.delete(key);
                btn.innerHTML = `<iconify-icon icon="solar:settings-linear"></iconify-icon> ${card.classList.contains('is-open') ? 'Hide' : 'Settings'}`;
            });
        });

        c.querySelectorAll('[data-duplicate-field]').forEach(btn => {
            btn.addEventListener('click', () => {
                collectStateFromDom();
                const si = parseInt(btn.dataset.stepIndex, 10);
                const fi = parseInt(btn.dataset.fieldIndex, 10);
                const original = state.steps[si]?.fields[fi];
                if (!original) return;
                const copy = { ...original, key: original.key + '_copy', label: original.label + ' (copy)' };
                state.steps[si].fields.splice(fi + 1, 0, copy);
                renderFields();
                renderSteps();
                schedulePreview();
            });
        });

        c.querySelectorAll('.fc-required-pill').forEach(pill => {
            const cb = pill.querySelector('[data-f="required"]');
            cb?.addEventListener('change', () => {
                pill.classList.toggle('is-on', cb.checked);
                pill.closest('.fc-field-card')?.classList.toggle('is-required', cb.checked);
                schedulePreview();
            });
        });

        c.querySelectorAll('input, select, textarea').forEach(el => {
            el.addEventListener('input', schedulePreview);
            el.addEventListener('change', function () {
                if (el.dataset.f === 'type') {
                    const card = el.closest('.fc-field-card');
                    const icon = card?.querySelector('.fc-pro-field-icon iconify-icon');
                    if (icon) icon.setAttribute('icon', FIELD_TYPE_ICONS[el.value] || 'solar:text-field-linear');
                    if (['select', 'radio'].includes(el.value)) {
                        collectStateFromDom();
                        renderFields();
                        return;
                    }
                }
                schedulePreview();
            });
        });

        c.querySelectorAll('[data-f="label"]').forEach(input => {
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
    }

    function previewFieldHtml(field) {
        const req = field.required ? ' <span class="fc-preview-req">*</span>' : '';
        const half = field.width === 'half' ? ' fc-preview-field--half' : '';
        const ph = esc(field.placeholder || '');
        const help = field.help_text ? `<small class="fc-preview-help">${esc(field.help_text)}</small>` : '';

        if (field.type === 'section') {
            return `<div class="fc-preview-section">${esc(field.label)}</div>`;
        }

        let input = '';
        switch (field.type) {
            case 'textarea':
                input = `<textarea class="fc-preview-input" rows="3" placeholder="${ph}" disabled></textarea>`;
                break;
            case 'select':
                input = `<select class="fc-preview-input" disabled><option>${ph || 'Select...'}</option>${getFieldOptionsForPreview(field).map(o => `<option>${esc(o)}</option>`).join('')}</select>`;
                break;
            case 'radio':
                input = (getFieldOptionsForPreview(field).length ? getFieldOptionsForPreview(field) : ['Option 1', 'Option 2']).map((o, i) => `
                    <label class="fc-preview-radio"><input type="radio" disabled ${i === 0 ? 'checked' : ''}> ${esc(o)}</label>`).join('');
                break;
            case 'checkbox':
                input = `<label class="fc-preview-check"><input type="checkbox" disabled> ${esc(field.label)}</label>`;
                return `<div class="fc-preview-field${half}">${input}${help}</div>`;
            case 'file':
                input = `<div class="fc-preview-file"><iconify-icon icon="solar:upload-linear"></iconify-icon> Choose file</div>`;
                break;
            default:
                input = `<input type="${field.type || 'text'}" class="fc-preview-input" placeholder="${ph}" disabled>`;
        }

        return `<div class="fc-preview-field${half}">
            <label class="fc-preview-label">${esc(field.label)}${req}</label>
            ${input}${help}
        </div>`;
    }

    function renderPreview(animate) {
        collectStateFromDom();
        const name = state.name || 'Untitled Form';
        const step = state.steps[previewStep];
        const total = state.steps.length || 1;
        const pct = Math.round(((previewStep + 1) / total) * 100);

        document.getElementById('previewFormTitle').textContent = 'STEP ' + (previewStep + 1) + ' OF ' + total;
        document.getElementById('previewProgressBar').style.width = pct + '%';
        document.getElementById('previewProgressText').textContent = pct + '%';

        const wrap = document.getElementById('previewStepWrap');
        const content = document.getElementById('previewStepContent');
        const transition = step?.transition || 'slide';

        if (animate && transition !== 'none') {
            content.classList.add('fc-preview-anim-out', 'fc-preview-anim--' + transition);
            setTimeout(() => {
                content.classList.remove('fc-preview-anim-out', 'fc-preview-anim--' + transition);
                content.innerHTML = step
                    ? `<h4 class="fc-preview-step-title">${esc(step.title)}</h4>
                       ${step.description ? `<p class="fc-preview-step-desc">${esc(step.description)}</p>` : ''}
                       <div class="fc-preview-fields">${(step.fields || []).map(previewFieldHtml).join('') || '<p class="fc-preview-empty">No fields in this step</p>'}</div>`
                    : '<p class="fc-preview-empty">Add steps to preview</p>';
                content.classList.add('fc-preview-anim-in', 'fc-preview-anim--' + transition);
                setTimeout(() => content.classList.remove('fc-preview-anim-in', 'fc-preview-anim--' + transition), 320);
            }, 180);
        } else {
            content.innerHTML = step
                ? `<h4 class="fc-preview-step-title">${esc(step.title)}</h4>
                   ${step.description ? `<p class="fc-preview-step-desc">${esc(step.description)}</p>` : ''}
                   <div class="fc-preview-fields">${(step.fields || []).map(previewFieldHtml).join('') || '<p class="fc-preview-empty">No fields in this step</p>'}</div>`
                : '<p class="fc-preview-empty">Add steps to preview</p>';
        }

        document.getElementById('previewPrevBtn').disabled = previewStep <= 0;
        const nextBtn = document.getElementById('previewNextBtn');
        nextBtn.textContent = previewStep >= total - 1 ? 'Submit' : 'Next';
        nextBtn.disabled = !step;
    }

    function addField(type) {
        collectStateFromDom();
        if (!state.steps.length) {
            state.steps.push({ key: 'step_1', title: 'Step 1', description: '', transition: 'slide', fields: [] });
            activeFieldStep = 0;
            renderSteps();
        }
        const step = state.steps[activeFieldStep];
        const fi = step.fields.length;
        if (type === 'section') {
            step.fields.push({
                key: '_section_' + Date.now(),
                label: 'New Section',
                type: 'section',
                required: false,
                options: [],
                options_source: '',
                width: 'full',
            });
        } else {
            step.fields.push({
                key: 'field_' + activeFieldStep + '_' + fi,
                label: FIELD_TYPE_LABELS[type] || 'New field',
                type: type || 'text',
                required: false,
                options: type === 'select' || type === 'radio' ? ['Option 1', 'Option 2'] : [],
                options_source: '',
                width: 'full',
            });
        }
        renderFields();
        renderSteps();
        schedulePreview();
        setTimeout(() => {
            const cards = document.querySelectorAll('#fieldsContainer .fc-field-card');
            cards[cards.length - 1]?.querySelector('[data-f="label"]')?.focus();
        }, 50);
    }

    function addSection() {
        addField('section');
    }

    function showToast(message, icon) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({ toast: true, position: 'top-end', icon: icon || 'success', title: message, showConfirmButton: false, timer: 2500, timerProgressBar: true });
    }

    function ensurePutMethod() {
        const formEl = document.getElementById('formBuilderForm');
        if (!formEl || formEl.querySelector('[name="_method"]')) return;
        const m = document.createElement('input');
        m.type = 'hidden'; m.name = '_method'; m.value = 'PUT';
        formEl.appendChild(m);
    }

    function injectPayload() {
        collectStateFromDom();
        ensureUniqueFieldKeys();
        if (!state.steps.length) {
            state.steps.push({ key: 'step_1', title: 'Step 1', description: '', transition: 'slide', fields: [] });
        }
        state.steps.forEach(step => {
            (step.fields || []).forEach(field => {
                field.col_span = field.width === 'half' ? 1 : 2;
            });
        });
        let input = document.getElementById('schemaPayload');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden'; input.name = 'schema'; input.id = 'schemaPayload';
            document.getElementById('formBuilderForm').appendChild(input);
        }
        input.value = JSON.stringify({ steps: state.steps });
    }

    function ensureUniqueFieldKeys() {
        const used = new Set();
        state.steps.forEach((step, si) => {
            (step.fields || []).forEach((field, fi) => {
                let key = (field.key || '').trim() || 'field_' + si + '_' + fi;
                while (used.has(key)) key = key + '_' + fi;
                field.key = key;
                used.add(key);
            });
        });
    }

    async function saveForm() {
        collectStateFromDom();
        injectPayload();

        const slugVal = document.getElementById('slug')?.value.trim();
        const nameVal = document.getElementById('name')?.value.trim();
        if (!nameVal || !slugVal) {
            setMode('settings');
            showToast('Please enter a form name and slug.', 'error');
            return false;
        }

        const formEl = document.getElementById('formBuilderForm');
        const btn = document.getElementById('saveBtn');
        btn?.classList.add('is-loading');
        btn?.setAttribute('disabled', 'disabled');

        try {
            const res = await fetch(formEl.action, {
                method: 'POST',
                body: new FormData(formEl),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                let msg = data.message || 'Could not save form.';
                if (data.errors) msg = Object.values(data.errors).flat().join(' ');
                throw new Error(msg);
            }
            if (data.update_url) {
                formEl.action = data.update_url;
                ensurePutMethod();
                isEdit = true;
            }
            showToast(data.message || 'Form saved.');
            markSaved();
            return true;
        } catch (e) {
            showToast(e.message || 'Could not save.', 'error');
            return false;
        } finally {
            btn?.classList.remove('is-loading');
            btn?.removeAttribute('disabled');
        }
    }

    function syncSlugDisplay() {
        const slug = document.getElementById('slugInput')?.value || document.getElementById('slug')?.value || '';
        const hidden = document.getElementById('slug');
        if (hidden && document.getElementById('slugInput')) hidden.value = slug;
        const display = document.getElementById('slugDisplay');
        if (display) display.textContent = slug || 'your-slug';
    }

    function syncVisibilityUi() {
        document.querySelectorAll('[data-settings-option]').forEach(opt => {
            const input = opt.querySelector('[data-placement]');
            opt.classList.toggle('is-selected', Boolean(input?.checked));
        });
        const status = document.querySelector('[data-visibility-status]');
        const active = document.getElementById('is_active');
        if (status && active) status.classList.toggle('is-on', active.checked);
    }

    function init(config) {
        state = normalizeSchema(config.schema || {});
        isEdit = config.isEdit || false;
        if (config.optionSources && typeof config.optionSources === 'object') {
            OPTION_SOURCE_LABELS = config.optionSources;
            OPTIONS_SOURCES = Object.keys(config.optionSources);
        }
        if (Array.isArray(config.optionSourceGroups)) {
            OPTION_SOURCE_GROUPS = config.optionSourceGroups;
        }
        if (config.optionSourceUrl) {
            OPTION_SOURCE_URL = config.optionSourceUrl;
        }

        if (config.formAction) {
            document.getElementById('formBuilderForm').action = config.formAction;
        }
        if (isEdit) ensurePutMethod();

        renderPalette();
        renderSteps();
        renderStepSettings();
        renderFields();
        renderPreview(false);
        updateStatsBar();

        document.querySelectorAll('.fc-pro-mode-tab').forEach(tab => {
            tab.addEventListener('click', () => setMode(tab.dataset.mode));
        });

        document.getElementById('addStepBtn')?.addEventListener('click', () => {
            collectStateFromDom();
            const n = state.steps.length + 1;
            state.steps.push({ key: 'step_' + n, title: 'Step ' + n, description: '', transition: 'slide', fields: [] });
            activeFieldStep = state.steps.length - 1;
            renderSteps();
            renderStepSettings();
            renderFields();
            schedulePreview();
        });

        document.getElementById('paletteToggle')?.addEventListener('click', () => {
            const wrap = document.getElementById('paletteWrap');
            const btn = document.getElementById('paletteToggle');
            wrap?.classList.toggle('is-open');
            const open = wrap?.classList.contains('is-open');
            btn?.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn?.classList.toggle('is-open', open);
        });

        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                saveForm();
            }
        });

        bindStepSettingsEvents();
        document.getElementById('paletteToggle')?.classList.add('is-open');

        document.getElementById('addFieldBtn')?.addEventListener('click', () => addField('text'));
        document.getElementById('addSectionBtn')?.addEventListener('click', addSection);

        document.getElementById('saveBtn')?.addEventListener('click', saveForm);
        document.getElementById('formBuilderForm')?.addEventListener('submit', e => e.preventDefault());

        document.getElementById('previewPrevBtn')?.addEventListener('click', () => {
            if (previewStep > 0) { previewStep--; renderPreview(true); }
        });
        document.getElementById('previewNextBtn')?.addEventListener('click', () => {
            if (previewStep < state.steps.length - 1) { previewStep++; renderPreview(true); }
        });

        document.getElementById('name')?.addEventListener('input', () => {
            schedulePreview();
            markDirty();
            const slugInput = document.getElementById('slugInput');
            const slugHidden = document.getElementById('slug');
            if (slugInput && !slugInput.value && slugHidden && !slugHidden.value) {
                const auto = document.getElementById('name').value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
                slugInput.value = auto;
                slugHidden.value = auto;
                syncSlugDisplay();
            }
        });

        document.getElementById('slugInput')?.addEventListener('input', syncSlugDisplay);

        document.querySelectorAll('[data-placement]').forEach(i => i.addEventListener('change', syncVisibilityUi));
        document.getElementById('is_active')?.addEventListener('change', syncVisibilityUi);
        syncVisibilityUi();
        syncSlugDisplay();

        document.querySelectorAll('.fc-pro-device-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.fc-pro-device-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const device = document.getElementById('previewDevice');
                device?.classList.toggle('fc-pro-preview-device--mobile', btn.dataset.device === 'mobile');
                device?.classList.toggle('fc-pro-preview-device--desktop', btn.dataset.device === 'desktop');
            });
        });

        document.getElementById('togglePreviewBtn')?.addEventListener('click', () => {
            document.getElementById('builderPreview')?.classList.toggle('is-visible');
        });
    }

    return { init };
})();
