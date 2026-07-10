/* global Swal */
window.FormBuilder = (function () {
    'use strict';

    const FIELD_TYPES = ['section', 'text', 'email', 'tel', 'number', 'date', 'textarea', 'select', 'radio', 'checkbox', 'file'];
    const FIELD_TYPE_LABELS = {
        section: 'Section', text: 'Short text', email: 'Email', tel: 'Phone', number: 'Number', date: 'Date',
        textarea: 'Long text', select: 'Dropdown', radio: 'Radio', checkbox: 'Checkbox', file: 'File',
    };
    const FIELD_TYPE_ICONS = {
        section: 'solar:widget-5-linear',
        text: 'solar:text-field-linear', email: 'solar:letter-linear', tel: 'solar:phone-linear',
        number: 'solar:hashtag-linear', date: 'solar:calendar-linear', textarea: 'solar:align-left-linear',
        select: 'solar:alt-arrow-down-linear', radio: 'solar:record-circle-linear',
        checkbox: 'solar:check-square-linear', file: 'solar:upload-linear',
    };
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
    let selectedField = null;
    let activeSideTab = 'preview';
    let previewDebounce = null;
    let isEdit = false;

    function markDirty() {
        const el = document.getElementById('saveStatus');
        if (el) {
            el.className = 'fc-v2-save-badge is-dirty';
            el.innerHTML = '<iconify-icon icon="solar:pen-2-linear"></iconify-icon> Unsaved';
        }
    }

    function markSaved() {
        const el = document.getElementById('saveStatus');
        if (el) {
            el.className = 'fc-v2-save-badge';
            el.innerHTML = '<iconify-icon icon="solar:check-circle-linear"></iconify-icon> Saved';
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

    function fieldKey(si, fi) {
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

        collectActiveStepFromPanel();

        document.querySelectorAll('#fieldsContainer .fc-v2-field-row').forEach(el => {
            const si = parseInt(el.dataset.stepIndex, 10);
            const fi = parseInt(el.dataset.fieldIndex, 10);
            if (!state.steps[si]?.fields[fi]) return;
            collectFieldFromListRow(state.steps[si].fields[fi], el);
        });

        if (selectedField && document.getElementById('inspectorContent') && !document.getElementById('inspectorContent').classList.contains('d-none')) {
            const field = state.steps[selectedField.si]?.fields[selectedField.fi];
            if (field) collectFieldFromInspector(field);
        }
    }

    function collectFieldFromListRow(f, el) {
        f.label = el.querySelector('[data-f="label"]')?.value || f.label;
        f.type = el.querySelector('[data-f="type"]')?.value || f.type;
        if (f.type === 'section') {
            f.required = false;
            f.options = [];
            f.options_source = '';
            f.width = 'full';
            return;
        }
        f.required = el.querySelector('[data-f="required"]')?.checked ?? false;
    }

    function collectFieldFromInspector(f) {
        const root = document.getElementById('inspectorContent');
        if (!root) return;
        f.key = root.querySelector('[data-f="key"]')?.value || f.key;
        f.label = root.querySelector('[data-f="label"]')?.value || f.label;
        f.type = root.querySelector('[data-f="type"]')?.value || f.type;
        if (f.type === 'section') return;
        f.placeholder = root.querySelector('[data-f="placeholder"]')?.value || '';
        f.help_text = root.querySelector('[data-f="help_text"]')?.value || '';
        f.required = root.querySelector('[data-f="required"]')?.checked ?? false;
        f.width = root.querySelector('[data-f="width"]')?.value || 'full';
        f.options_source = root.querySelector('[data-f="options_source"]')?.value || '';
        const optRaw = root.querySelector('[data-f="options"]')?.value || '';
        if (['select', 'radio'].includes(f.type) && f.options_source) {
            f.options = getSelectedOptionsFromPicker(root);
        } else {
            f.options = optRaw.split('\n').map(l => l.trim()).filter(Boolean);
        }
        f.validation = root.querySelector('[data-f="validation"]')?.value || '';
    }

    function getSelectedOptionsFromPicker(container) {
        const selected = [];
        container.querySelectorAll('[data-option-chip].is-selected').forEach(chip => {
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
                    html += `<option value="${esc(key)}" ${current === key ? 'selected' : ''}>${esc(OPTION_SOURCE_LABELS[key] || key)}</option>`;
                });
                html += '</optgroup>';
            });
        } else {
            OPTIONS_SOURCES.filter(s => s).forEach(key => {
                html += `<option value="${esc(key)}" ${current === key ? 'selected' : ''}>${esc(OPTION_SOURCE_LABELS[key] || key)}</option>`;
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
                <i class="ri-check-line" aria-hidden="true"></i><span>${esc(opt.label || value)}</span>
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
        if (countEl) countEl.textContent = total ? `${selected} of ${total} selected` : 'No values found';
    }

    async function mountOptionPicker(container, field) {
        const panel = container.querySelector('[data-option-picker]');
        const manual = container.querySelector('[data-option-manual]');
        const source = field.options_source || container.querySelector('[data-f="options_source"]')?.value || '';
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
            renderOptionChips(panel, options, Array.isArray(field.options) && field.options.length ? field.options : null);
        } catch (err) {
            if (grid) grid.innerHTML = `<div class="fc-option-error">${esc(err.message || 'Failed to load')}</div>`;
        }
    }

    function bindOptionPickerEvents(container) {
        const picker = container.querySelector('[data-option-picker]');
        const sourceSelect = container.querySelector('[data-f="options_source"]');
        sourceSelect?.addEventListener('change', async function () {
            if (!selectedField) return;
            const field = state.steps[selectedField.si]?.fields[selectedField.fi];
            if (!field) return;
            field.options_source = this.value;
            field.options = [];
            await mountOptionPicker(container, field);
            schedulePreview();
            renderFieldList();
        });
        if (!picker) return;
        picker.querySelector('[data-option-search]')?.addEventListener('input', function () {
            picker.dataset.search = this.value;
            const source = sourceSelect?.value;
            if (!source || !optionCache[source]) return;
            renderOptionChips(picker, optionCache[source], getSelectedOptionsFromPicker(container));
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
        document.querySelectorAll('.fc-v2-mode-btn').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.mode === mode);
        });
        document.querySelectorAll('.fc-v2-panel').forEach(panel => {
            panel.classList.toggle('active', panel.dataset.panel === mode);
        });
    }

    function setSideTab(tab) {
        activeSideTab = tab;
        document.querySelectorAll('.fc-v2-side-tab').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.sideTab === tab);
        });
        document.querySelectorAll('.fc-v2-side-panel').forEach(panel => {
            panel.classList.toggle('active', panel.dataset.sidePanel === tab);
        });
    }

    function selectField(si, fi, opts) {
        opts = opts || {};
        collectStateFromDom();
        const changed = !selectedField || selectedField.si !== si || selectedField.fi !== fi;
        selectedField = { si, fi };
        if (opts.skipListRender) {
            updateFieldSelectionClasses();
        } else if (changed) {
            renderFieldList();
        } else {
            updateFieldSelectionClasses();
        }
        renderInspector();
        if (changed && !opts.skipSideTab) setSideTab('inspector');
        updateInspectorBadge();
    }

    function updateFieldSelectionClasses() {
        document.querySelectorAll('.fc-v2-field-row').forEach(row => {
            const si = parseInt(row.dataset.stepIndex, 10);
            const fi = parseInt(row.dataset.fieldIndex, 10);
            row.classList.toggle('is-selected', selectedField?.si === si && selectedField?.fi === fi);
        });
    }

    function clearFieldSelection() {
        selectedField = null;
        renderFieldList();
        renderInspector();
        updateInspectorBadge();
    }

    function updateInspectorBadge() {
        const badge = document.getElementById('inspectorBadge');
        if (!badge) return;
        if (selectedField) {
            const field = state.steps[selectedField.si]?.fields[selectedField.fi];
            badge.textContent = field?.label?.substring(0, 12) || '1';
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    function setActiveStep(index) {
        collectStateFromDom();
        activeFieldStep = index;
        previewStep = index;
        selectedField = null;
        renderSteps();
        renderStepSettings();
        renderFieldList();
        renderInspector();
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
        if (activeEl) activeEl.textContent = totalSteps ? `${activeFieldStep + 1} of ${totalSteps}` : '—';
    }

    function renderStepSettings() {
        const panel = document.getElementById('stepSettingsPanel');
        const step = state.steps[activeFieldStep];
        const chip = document.getElementById('activeStepChip');
        if (!panel || !step) return;
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
        document.querySelectorAll('#stepsContainer .fc-v2-step-item').forEach(el => {
            const idx = parseInt(el.dataset.stepIndex, 10);
            if (!state.steps[idx]) return;
            newSteps.push({ ...state.steps[idx], fields: [...(state.steps[idx].fields || [])] });
        });
        state.steps = newSteps;
        if (activeFieldStep >= state.steps.length) activeFieldStep = Math.max(0, state.steps.length - 1);
        selectedField = null;
        renderSteps();
        renderStepSettings();
        renderFieldList();
        renderInspector();
        schedulePreview();
    }

    function reorderFieldsFromDom() {
        const step = state.steps[activeFieldStep];
        if (!step) return;
        const newFields = [];
        document.querySelectorAll('#fieldsContainer .fc-v2-field-row').forEach(el => {
            const fi = parseInt(el.dataset.fieldIndex, 10);
            if (!step.fields[fi]) return;
            const field = { ...step.fields[fi] };
            collectFieldFromListRow(field, el);
            if (selectedField?.si === activeFieldStep && selectedField.fi === fi) {
                collectFieldFromInspector(field);
            }
            newFields.push(field);
        });
        step.fields = newFields;
        selectedField = null;
        renderFieldList();
        renderInspector();
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
        palette.innerHTML = FIELD_TYPES.filter(t => t !== 'section').map(type => `
            <button type="button" class="fc-v2-type-chip" data-add-type="${type}" title="Add ${FIELD_TYPE_LABELS[type]}">
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
            c.innerHTML = '<div class="fc-v2-empty-steps"><p>No steps yet</p></div>';
            return;
        }
        c.innerHTML = state.steps.map((step, i) => {
            const fieldCount = (step.fields || []).filter(f => f.type !== 'section').length;
            return `
            <div class="fc-v2-step-item ${i === activeFieldStep ? 'is-active' : ''}" data-step-index="${i}">
                <span class="fc-drag-handle" title="Drag to reorder"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>
                <button type="button" class="fc-v2-step-btn" data-select-step="${i}">
                    <span class="fc-v2-step-num">${i + 1}</span>
                    <span class="fc-v2-step-text">
                        <span class="fc-v2-step-name">${esc(step.title)}</span>
                        <span class="fc-v2-step-meta">${fieldCount} field${fieldCount === 1 ? '' : 's'}</span>
                    </span>
                </button>
                <button type="button" class="fc-v2-icon-btn fc-v2-icon-btn--ghost fc-v2-step-delete" data-remove-step="${i}" title="Remove step">
                    <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                </button>
            </div>`;
        }).join('');

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
                selectedField = null;
                renderSteps();
                renderStepSettings();
                renderFieldList();
                renderInspector();
                schedulePreview();
            });
        });
        initDragSort(c, '.fc-v2-step-item', reorderStepsFromDom);
    }

    function fieldRowHtml(si, fi, field) {
        const isSelected = selectedField?.si === si && selectedField?.fi === fi;
        const isSection = field.type === 'section';

        if (isSection) {
            return `
            <div class="fc-v2-field-row fc-v2-field-row--section ${isSelected ? 'is-selected' : ''}" data-step-index="${si}" data-field-index="${fi}" data-select-field="${si},${fi}">
                <span class="fc-drag-handle"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>
                <iconify-icon icon="solar:widget-5-linear" class="fc-v2-row-icon"></iconify-icon>
                <input class="fc-v2-row-label" data-f="label" value="${esc(field.label)}" placeholder="Section heading">
                <input type="hidden" data-f="key" value="${esc(field.key)}">
                <input type="hidden" data-f="type" value="section">
                <span class="fc-v2-row-badge">Section</span>
                <div class="fc-v2-row-actions">
                    <button type="button" class="fc-v2-row-action fc-v2-row-action--danger" data-remove-field data-step-index="${si}" data-field-index="${fi}" title="Delete">
                        <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                    </button>
                </div>
            </div>`;
        }

        const typeOpts = FIELD_TYPES.filter(t => t !== 'section').map(t =>
            `<option value="${t}" ${field.type === t ? 'selected' : ''}>${FIELD_TYPE_LABELS[t]}</option>`
        ).join('');

        return `
        <div class="fc-v2-field-row ${isSelected ? 'is-selected' : ''} ${field.required ? 'is-required' : ''}" data-step-index="${si}" data-field-index="${fi}" data-select-field="${si},${fi}">
            <span class="fc-drag-handle" title="Drag"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>
            <iconify-icon icon="${FIELD_TYPE_ICONS[field.type] || 'solar:text-field-linear'}" class="fc-v2-row-icon"></iconify-icon>
            <input class="fc-v2-row-label" data-f="label" value="${esc(field.label)}" placeholder="Field label">
            <select class="fc-v2-row-type" data-f="type">${typeOpts}</select>
            <label class="fc-v2-required-toggle ${field.required ? 'is-on' : ''}" title="Required">
                <input type="checkbox" class="d-none" data-f="required" ${field.required ? 'checked' : ''}>
                <iconify-icon icon="solar:star-bold"></iconify-icon>
            </label>
            <div class="fc-v2-row-actions">
                <button type="button" class="fc-v2-row-action" data-edit-field="${si},${fi}" title="Edit settings">
                    <iconify-icon icon="solar:settings-linear"></iconify-icon>
                </button>
                <button type="button" class="fc-v2-row-action" data-duplicate-field data-step-index="${si}" data-field-index="${fi}" title="Duplicate">
                    <iconify-icon icon="solar:copy-linear"></iconify-icon>
                </button>
                <button type="button" class="fc-v2-row-action fc-v2-row-action--danger" data-remove-field data-step-index="${si}" data-field-index="${fi}" title="Delete">
                    <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                </button>
            </div>
        </div>`;
    }

    function inspectorHtml(si, fi, field) {
        if (field.type === 'section') {
            return `
            <div class="fc-v2-inspector-head">
                <iconify-icon icon="solar:widget-5-linear"></iconify-icon>
                <div>
                    <h4>Section divider</h4>
                    <p>Groups related fields visually</p>
                </div>
            </div>
            <div class="fc-v2-inspector-section">
                <label class="fc-v2-inspector-label">Heading</label>
                <input class="form-control radius-8" data-f="label" value="${esc(field.label)}" placeholder="Section title">
                <input type="hidden" data-f="type" value="section">
                <input type="hidden" data-f="key" value="${esc(field.key)}">
            </div>`;
        }

        const optLines = (field.options || []).join('\n');
        const typeOpts = FIELD_TYPES.filter(t => t !== 'section').map(t =>
            `<option value="${t}" ${field.type === t ? 'selected' : ''}>${FIELD_TYPE_LABELS[t]}</option>`
        ).join('');
        const srcOpts = buildGroupedSourceOptions(field);
        const needsOptions = ['select', 'radio'].includes(field.type);
        const usesModule = !!(field.options_source);

        return `
        <div class="fc-v2-inspector-head">
            <iconify-icon icon="${FIELD_TYPE_ICONS[field.type] || 'solar:text-field-linear'}"></iconify-icon>
            <div>
                <h4>${esc(field.label || 'Field')}</h4>
                <p>${FIELD_TYPE_LABELS[field.type] || field.type}</p>
            </div>
            <button type="button" class="fc-v2-inspector-close" id="closeInspectorBtn" title="Close">
                <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
            </button>
        </div>

        <div class="fc-v2-inspector-section">
            <h5 class="fc-v2-inspector-section-title">Question</h5>
            <label class="fc-v2-inspector-label">Label</label>
            <input class="form-control radius-8" data-f="label" value="${esc(field.label)}" placeholder="What should users see?">
            <label class="fc-v2-inspector-label">Field type</label>
            <select class="form-select radius-8" data-f="type">${typeOpts}</select>
            <label class="fc-v2-inspector-label">Help text <span class="text-muted">(optional)</span></label>
            <input class="form-control radius-8" data-f="help_text" value="${esc(field.help_text || '')}" placeholder="Shown below the field">
            <label class="fc-v2-inspector-label">Placeholder <span class="text-muted">(optional)</span></label>
            <input class="form-control radius-8" data-f="placeholder" value="${esc(field.placeholder || '')}" placeholder="Hint inside the input">
        </div>

        <div class="fc-v2-inspector-section">
            <h5 class="fc-v2-inspector-section-title">Rules</h5>
            <label class="fc-v2-toggle-row">
                <span>
                    <strong>Required field</strong>
                    <small>Users must fill this in</small>
                </span>
                <input type="checkbox" class="fc-v2-switch" data-f="required" ${field.required ? 'checked' : ''}>
            </label>
            <label class="fc-v2-inspector-label">Validation rules</label>
            <input class="form-control radius-8" data-f="validation" value="${esc(field.validation || '')}" placeholder="e.g. max:255">
        </div>

        <div class="fc-v2-inspector-section">
            <h5 class="fc-v2-inspector-section-title">Layout</h5>
            <label class="fc-v2-inspector-label">Width on form</label>
            <div class="fc-v2-width-toggle">
                <label class="fc-v2-width-opt ${field.width !== 'half' ? 'is-active' : ''}">
                    <input type="radio" name="field_width" data-f="width" value="full" ${field.width !== 'half' ? 'checked' : ''}>
                    <iconify-icon icon="solar:full-screen-linear"></iconify-icon> Full
                </label>
                <label class="fc-v2-width-opt ${field.width === 'half' ? 'is-active' : ''}">
                    <input type="radio" name="field_width" data-f="width" value="half" ${field.width === 'half' ? 'checked' : ''}>
                    <iconify-icon icon="solar:sidebar-minimalistic-linear"></iconify-icon> Half
                </label>
            </div>
        </div>

        ${needsOptions ? `
        <div class="fc-v2-inspector-section">
            <h5 class="fc-v2-inspector-section-title">Answer choices</h5>
            <label class="fc-v2-inspector-label">Data source</label>
            <select class="form-select radius-8" data-f="options_source">${srcOpts}</select>
            <div class="fc-option-builder">
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
                    <p class="fc-option-empty text-secondary-light text-sm mb-0 d-none" data-option-empty>No values in this module yet.</p>
                </div>
                <div class="fc-option-manual ${usesModule ? 'd-none' : ''}" data-option-manual>
                    <label class="fc-v2-inspector-label">Manual options (one per line)</label>
                    <textarea class="form-control radius-8" rows="4" data-f="options" placeholder="Option 1&#10;Option 2">${esc(optLines)}</textarea>
                </div>
            </div>
        </div>` : ''}

        <details class="fc-v2-inspector-advanced">
            <summary>Advanced</summary>
            <label class="fc-v2-inspector-label">Field key</label>
            <input class="form-control radius-8 font-monospace text-sm" data-f="key" value="${esc(field.key)}" placeholder="database_key">
            <small class="text-secondary-light">Used when storing submissions. Auto-generated from label.</small>
        </details>`;
    }

    function renderInspector() {
        const empty = document.getElementById('inspectorEmpty');
        const content = document.getElementById('inspectorContent');
        if (!empty || !content) return;

        if (!selectedField) {
            empty.classList.remove('d-none');
            content.classList.add('d-none');
            content.innerHTML = '';
            return;
        }

        const field = state.steps[selectedField.si]?.fields[selectedField.fi];
        if (!field) {
            clearFieldSelection();
            return;
        }

        empty.classList.add('d-none');
        content.classList.remove('d-none');
        content.innerHTML = inspectorHtml(selectedField.si, selectedField.fi, field);
        bindInspectorEvents(content, field);
        if (['select', 'radio'].includes(field.type)) {
            bindOptionPickerEvents(content);
            if (field.options_source) mountOptionPicker(content, field);
        }
    }

    function bindInspectorEvents(root, field) {
        root.querySelector('#closeInspectorBtn')?.addEventListener('click', () => {
            clearFieldSelection();
            setSideTab('preview');
        });

        root.querySelectorAll('input, select, textarea').forEach(el => {
            el.addEventListener('input', () => {
                collectFieldFromInspector(field);
                if (el.dataset.f === 'label') {
                    updateInspectorBadge();
                    renderFieldList();
                }
                schedulePreview();
            });
            el.addEventListener('change', function () {
                if (el.dataset.f === 'type') {
                    collectFieldFromInspector(field);
                    renderFieldList();
                    renderInspector();
                    schedulePreview();
                    return;
                }
                if (el.dataset.f === 'width') {
                    root.querySelectorAll('.fc-v2-width-opt').forEach(opt => {
                        opt.classList.toggle('is-active', opt.querySelector('input')?.checked);
                    });
                }
                schedulePreview();
            });
        });

        root.querySelector('[data-f="label"]')?.addEventListener('blur', function () {
            const keyInput = root.querySelector('[data-f="key"]');
            if (keyInput && keyInput.dataset.manual !== '1') {
                keyInput.value = slugifyKey(this.value, 'field_' + selectedField.si + '_' + selectedField.fi);
            }
        });

        root.querySelector('[data-f="key"]')?.addEventListener('input', function () {
            this.dataset.manual = '1';
        });
    }

    function renderFieldList() {
        const c = document.getElementById('fieldsContainer');
        const step = state.steps[activeFieldStep];

        if (!step) {
            c.innerHTML = '<div class="fc-v2-empty-fields"><iconify-icon icon="solar:layers-linear"></iconify-icon><h6>No steps</h6><p>Add a step to begin</p></div>';
            return;
        }

        if (!(step.fields || []).length) {
            c.innerHTML = `
            <div class="fc-v2-empty-fields">
                <iconify-icon icon="solar:text-field-linear"></iconify-icon>
                <h6>This step is empty</h6>
                <p>Pick a field type above to add your first question</p>
            </div>`;
            return;
        }

        c.innerHTML = step.fields.map((field, fi) => fieldRowHtml(activeFieldStep, fi, field)).join('');
        bindFieldListEvents(c);
        initDragSort(c, '.fc-v2-field-row', reorderFieldsFromDom);
        updateStatsBar();
    }

    function bindFieldListEvents(c) {
        c.querySelectorAll('[data-select-field]').forEach(row => {
            row.addEventListener('click', e => {
                if (e.target.closest('.fc-drag-handle, .fc-v2-row-action, .fc-v2-row-type, .fc-v2-row-label, .fc-v2-required-toggle')) return;
                const [si, fi] = row.dataset.selectField.split(',').map(Number);
                selectField(si, fi);
            });
        });

        c.querySelectorAll('[data-edit-field]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                const [si, fi] = btn.dataset.editField.split(',').map(Number);
                selectField(si, fi);
            });
        });

        c.querySelectorAll('[data-remove-field]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                collectStateFromDom();
                const si = parseInt(btn.dataset.stepIndex, 10);
                const fi = parseInt(btn.dataset.fieldIndex, 10);
                state.steps[si].fields.splice(fi, 1);
                if (selectedField?.si === si && selectedField?.fi === fi) selectedField = null;
                else if (selectedField?.si === si && selectedField.fi > fi) selectedField.fi--;
                renderFieldList();
                renderInspector();
                renderSteps();
                schedulePreview();
            });
        });

        c.querySelectorAll('[data-duplicate-field]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                collectStateFromDom();
                const si = parseInt(btn.dataset.stepIndex, 10);
                const fi = parseInt(btn.dataset.fieldIndex, 10);
                const original = state.steps[si]?.fields[fi];
                if (!original) return;
                const copy = { ...original, key: original.key + '_copy', label: original.label + ' (copy)' };
                state.steps[si].fields.splice(fi + 1, 0, copy);
                renderFieldList();
                renderSteps();
                schedulePreview();
            });
        });

        c.querySelectorAll('.fc-v2-required-toggle').forEach(toggle => {
            const cb = toggle.querySelector('[data-f="required"]');
            cb?.addEventListener('change', () => {
                toggle.classList.toggle('is-on', cb.checked);
                toggle.closest('.fc-v2-field-row')?.classList.toggle('is-required', cb.checked);
                schedulePreview();
            });
        });

        c.querySelectorAll('.fc-v2-row-label, .fc-v2-row-type').forEach(el => {
            el.addEventListener('input', schedulePreview);
            el.addEventListener('change', function () {
                if (el.dataset.f === 'type') {
                    const row = el.closest('.fc-v2-field-row');
                    const icon = row?.querySelector('.fc-v2-row-icon');
                    if (icon) icon.setAttribute('icon', FIELD_TYPE_ICONS[el.value] || 'solar:text-field-linear');
                }
                schedulePreview();
            });
        });

        c.querySelectorAll('.fc-v2-field-row').forEach(row => {
            row.querySelector('[data-f="label"]')?.addEventListener('focus', () => {
                const si = parseInt(row.dataset.stepIndex, 10);
                const fi = parseInt(row.dataset.fieldIndex, 10);
                if (!selectedField || selectedField.si !== si || selectedField.fi !== fi) {
                    selectField(si, fi, { skipListRender: true, skipSideTab: true });
                }
            });
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
                input = (getFieldOptionsForPreview(field).length ? getFieldOptionsForPreview(field) : ['Option 1', 'Option 2']).map((o, i) =>
                    `<label class="fc-preview-radio"><input type="radio" disabled ${i === 0 ? 'checked' : ''}> ${esc(o)}</label>`
                ).join('');
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
        const step = state.steps[previewStep];
        const total = state.steps.length || 1;
        const pct = Math.round(((previewStep + 1) / total) * 100);

        document.getElementById('previewFormTitle').textContent = 'STEP ' + (previewStep + 1) + ' OF ' + total;
        document.getElementById('previewProgressText').textContent = pct + '%';

        const content = document.getElementById('previewStepContent');
        const transition = step?.transition || 'slide';

        const html = step
            ? `<h4 class="fc-preview-step-title">${esc(step.title)}</h4>
               ${step.description ? `<p class="fc-preview-step-desc">${esc(step.description)}</p>` : ''}
               <div class="fc-preview-fields">${(step.fields || []).map(previewFieldHtml).join('') || '<p class="fc-preview-empty">No fields in this step</p>'}</div>`
            : '<p class="fc-preview-empty">Add steps to preview</p>';

        if (animate && transition !== 'none') {
            content.classList.add('fc-preview-anim-out', 'fc-preview-anim--' + transition);
            setTimeout(() => {
                content.classList.remove('fc-preview-anim-out', 'fc-preview-anim--' + transition);
                content.innerHTML = html;
                content.classList.add('fc-preview-anim-in', 'fc-preview-anim--' + transition);
                setTimeout(() => content.classList.remove('fc-preview-anim-in', 'fc-preview-anim--' + transition), 320);
            }, 180);
        } else {
            content.innerHTML = html;
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
            step.fields.push({ key: '_section_' + Date.now(), label: 'New Section', type: 'section', required: false, options: [], options_source: '', width: 'full' });
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
        renderFieldList();
        renderSteps();
        schedulePreview();
        selectField(activeFieldStep, fi);
        setTimeout(() => {
            document.querySelector('#fieldsContainer .fc-v2-field-row:last-child [data-f="label"]')?.focus();
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
        if (Array.isArray(config.optionSourceGroups)) OPTION_SOURCE_GROUPS = config.optionSourceGroups;
        if (config.optionSourceUrl) OPTION_SOURCE_URL = config.optionSourceUrl;

        if (config.formAction) document.getElementById('formBuilderForm').action = config.formAction;
        if (isEdit) ensurePutMethod();

        renderPalette();
        renderSteps();
        renderStepSettings();
        renderFieldList();
        renderInspector();
        renderPreview(false);
        updateStatsBar();

        document.querySelectorAll('.fc-v2-mode-btn').forEach(tab => {
            tab.addEventListener('click', () => setMode(tab.dataset.mode));
        });

        document.querySelectorAll('.fc-v2-side-tab').forEach(tab => {
            tab.addEventListener('click', () => setSideTab(tab.dataset.sideTab));
        });

        document.getElementById('addStepBtn')?.addEventListener('click', () => {
            collectStateFromDom();
            const n = state.steps.length + 1;
            state.steps.push({ key: 'step_' + n, title: 'Step ' + n, description: '', transition: 'slide', fields: [] });
            activeFieldStep = state.steps.length - 1;
            selectedField = null;
            renderSteps();
            renderStepSettings();
            renderFieldList();
            renderInspector();
            schedulePreview();
        });

        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                saveForm();
            }
            if (e.key === 'Escape' && selectedField) {
                clearFieldSelection();
                setSideTab('preview');
            }
        });

        bindStepSettingsEvents();
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

        document.querySelectorAll('.fc-v2-device-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.fc-v2-device-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const device = document.getElementById('previewDevice');
                device?.classList.toggle('fc-v2-preview-device--mobile', btn.dataset.device === 'mobile');
                device?.classList.toggle('fc-v2-preview-device--desktop', btn.dataset.device === 'desktop');
            });
        });

        document.getElementById('toggleSidePanelBtn')?.addEventListener('click', () => {
            document.getElementById('builderSidePanel')?.classList.toggle('is-open');
        });
    }

    return { init };
})();
