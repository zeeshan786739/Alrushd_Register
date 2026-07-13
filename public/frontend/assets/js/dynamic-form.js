(function ($) {
    'use strict';

    const app = document.getElementById('dynamicFormApp');
    if (!app) return;

    const slug = app.dataset.slug;
    const apiBase = app.dataset.apiBase;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const RING_CIRCUMFERENCE = 2 * Math.PI * 24;

    let schema = null;
    let currentStep = 0;
    let repeaterCounts = {};
    let stripeInstances = {};

    const $loading = $('#formLoading');
    const $error = $('#formError');
    const $errorMsg = $('#formErrorMessage');
    const $container = $('#formContainer');
    const $form = $('#dynamicForm');
    const $stepsContainer = $('#stepsContainer');
    const $stepSidebar = $('#stepSidebar');
    const $pageTitle = $('#pageFormTitle');
    const $stepIndicator = $('#stepIndicator');
    const $sectionTitle = $('#sectionTitle');
    const $progressRingFill = $('#progressRingFill');
    const $progressText = $('#progressText');
    const $btnNext = $('#btnNext');
    const $btnBack = $('#btnBack');
    const $btnSubmit = $('#btnSubmit');

    function showError(message) {
        $loading.addClass('d-none');
        $container.addClass('d-none');
        $error.removeClass('d-none');
        $errorMsg.text(message);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function fieldWidthClass(colSpan) {
        return (colSpan === 1) ? 'ar-field' : 'ar-field ar-field--full';
    }

    function reqMark(field) {
        return field.required ? '<span class="ar-required">*</span>' : '';
    }

    function renderOptions(field) {
        return (field.options || []).map(function (opt) {
            const value = typeof opt === 'object' ? opt.value : opt;
            const label = typeof opt === 'object' ? opt.label : opt;
            return `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`;
        }).join('');
    }

    function renderRadioOptions(field) {
        return (field.options || []).map(function (opt, i) {
            const value = typeof opt === 'object' ? opt.value : opt;
            const label = typeof opt === 'object' ? opt.label : opt;
            const id = `${field.key}_${i}`;
            return `
                <label class="ar-field-radio" for="${id}">
                    <input type="radio" name="${escapeHtml(field.key)}" id="${id}" value="${escapeHtml(value)}" ${field.required ? 'required' : ''}>
                    <span>${escapeHtml(label)}</span>
                </label>`;
        }).join('');
    }

    function renderRepeaterChild(child, parentKey, rowIndex) {
        const name = `${parentKey}[${rowIndex}][${child.key}]`;
        const id = `${parentKey}_${rowIndex}_${child.key}`;
        const required = child.required ? 'required' : '';
        const placeholder = child.placeholder ? `placeholder="${escapeHtml(child.placeholder)}"` : '';

        switch (child.type) {
            case 'textarea':
                return `<textarea name="${name}" id="${id}" class="ar-field-textarea" rows="3" ${required} ${placeholder}></textarea>`;
            case 'select':
                return `<select name="${name}" id="${id}" class="ar-field-select" ${required}>
                    <option value="">-- Select --</option>${renderOptions(child)}</select>`;
            case 'file':
                return `<input type="file" name="${name}" id="${id}" class="ar-field-input" ${required}>`;
            default:
                return `<input type="${child.type || 'text'}" name="${name}" id="${id}" class="ar-field-input" ${required} ${placeholder}>`;
        }
    }

    function renderRepeaterRow(field, rowIndex) {
        const children = (field.settings && field.settings.fields) || [];
        const cols = children.map(function (child) {
            return `<div class="ar-field ar-field--full">
                <label class="ar-field-label">${escapeHtml(child.label)}</label>
                ${renderRepeaterChild(child, field.key, rowIndex)}
            </div>`;
        }).join('');

        return `
            <div class="ar-repeater-row" data-index="${rowIndex}">
                <div class="ar-repeater-header">
                    <span>Entry ${rowIndex + 1}</span>
                    <button type="button" class="ar-btn ar-btn--ghost btn-remove-repeater" style="padding:4px 12px;font-size:12px;" data-field="${escapeHtml(field.key)}" data-index="${rowIndex}">Remove</button>
                </div>
                <div class="ar-fields-grid">${cols}</div>
            </div>`;
    }

    function renderRepeater(field) {
        repeaterCounts[field.key] = repeaterCounts[field.key] || 1;
        const rows = [];
        for (let i = 0; i < repeaterCounts[field.key]; i++) {
            rows.push(renderRepeaterRow(field, i));
        }

        return `
            <div class="ar-field ar-field--full repeater-group" data-key="${escapeHtml(field.key)}">
                <label class="ar-field-label">${escapeHtml(field.label)}${reqMark(field)}</label>
                <div class="repeater-rows">${rows.join('')}</div>
                <button type="button" class="ar-btn ar-btn--ghost mt-2 btn-add-repeater" style="padding:8px 16px;font-size:13px;" data-field="${escapeHtml(field.key)}">+ Add another</button>
                <div class="ar-field-error" data-for="${escapeHtml(field.key)}"></div>
            </div>`;
    }

    function sectionSettings(field) {
        const s = field.settings || {};
        return {
            section_style: s.section_style || 'divider',
            section_spacing: s.section_spacing || 'normal',
            break_before: s.break_before !== false,
            break_after: s.break_after !== false,
        };
    }

    function sectionClassList(field) {
        const ss = sectionSettings(field);
        const classes = ['ar-form-section', 'ar-field--full'];
        if (ss.section_style === 'heading') classes.push('ar-form-section--heading');
        if (ss.section_spacing) classes.push('ar-form-section--' + ss.section_spacing);
        if (ss.break_before) classes.push('ar-form-section--break-before');
        if (ss.break_after) classes.push('ar-form-section--break-after');
        return classes.join(' ');
    }

    function paymentSettings(field) {
        const s = field.settings || {};
        return {
            amount: parseFloat(s.amount ?? 15) || 15,
            currency: (s.currency || 'gbp').toLowerCase(),
            fee_label: s.fee_label || 'Application Fee',
            allow_stripe: s.allow_stripe !== false,
            allow_offline: !!s.allow_offline,
            show_summary: s.show_summary !== false,
        };
    }

    function currencySymbol(currency) {
        return { gbp: '£', usd: '$', eur: '€' }[currency] || '';
    }

    function renderPaymentField(field) {
        const ps = paymentSettings(field);
        const sym = currencySymbol(ps.currency);
        const key = field.key;
        const onlineEnabled = schema.payment_config?.online_enabled !== false;
        const methods = [];
        if (ps.allow_stripe && onlineEnabled && schema.payment_config?.stripe_key) {
            methods.push(['stripe', 'Pay by card (Stripe)']);
        }
        if (ps.allow_offline) {
            methods.push(['offline', 'Offline payment']);
        }
        if (!methods.length) {
            methods.push(['offline', 'Offline payment']);
        }

        const methodsHtml = methods.length > 1
            ? `<div class="ar-payment-methods">${methods.map(([val, label], i) => `
                <label class="ar-payment-method">
                    <input type="radio" name="${escapeHtml(key)}_payment_method" value="${val}" ${i === 0 ? 'checked' : ''}>
                    <span>${escapeHtml(label)}</span>
                </label>`).join('')}</div>`
            : `<input type="hidden" name="${escapeHtml(key)}_payment_method" value="${methods[0][0]}">`;

        const stripePanel = methods.some(m => m[0] === 'stripe')
            ? `<div class="ar-payment-stripe-panel" data-stripe-panel="${escapeHtml(key)}">
                <label class="ar-field-label" for="${escapeHtml(key)}_card_holder_name">Card holder name</label>
                <input type="text" class="ar-field-input" name="${escapeHtml(key)}_card_holder_name" id="${escapeHtml(key)}_card_holder_name" ${field.required ? 'required' : ''}>
                <label class="ar-field-label">Card details</label>
                <div id="${escapeHtml(key)}_card_element" class="ar-payment-card-element"></div>
                <div class="ar-field-error" data-for="${escapeHtml(key)}_card"></div>
                <input type="hidden" name="${escapeHtml(key)}_stripe_token" id="${escapeHtml(key)}_stripe_token">
            </div>`
            : '';

        return `<div class="ar-payment-field ar-field--full" data-payment-field="${escapeHtml(key)}">
            <div class="ar-payment-header">
                <h3 class="ar-payment-title">${escapeHtml(field.label)}${reqMark(field)}</h3>
                ${field.help_text ? `<p class="ar-payment-desc">${escapeHtml(field.help_text)}</p>` : ''}
            </div>
            ${ps.show_summary ? `<div class="ar-payment-summary">
                <div class="ar-payment-line"><span>${escapeHtml(ps.fee_label)}</span><strong>${sym}${ps.amount.toFixed(2)}</strong></div>
                <div class="ar-payment-line ar-payment-total"><span>Total payable</span><strong>${sym}${ps.amount.toFixed(2)}</strong></div>
            </div>` : ''}
            ${methodsHtml}
            <label class="ar-field-label" for="${escapeHtml(key)}_payment_email">Payment email</label>
            <input type="email" class="ar-field-input" name="${escapeHtml(key)}_payment_email" id="${escapeHtml(key)}_payment_email" ${field.required ? 'required' : ''}>
            ${stripePanel}
            <div class="ar-payment-address row g-3">
                <div class="col-md-6">
                    <label class="ar-field-label" for="${escapeHtml(key)}_payment_country">Country</label>
                    <input type="text" class="ar-field-input" name="${escapeHtml(key)}_payment_country" id="${escapeHtml(key)}_payment_country" ${field.required ? 'required' : ''}>
                </div>
                <div class="col-md-6">
                    <label class="ar-field-label" for="${escapeHtml(key)}_payment_postal_code">Postal code</label>
                    <input type="text" class="ar-field-input" name="${escapeHtml(key)}_payment_postal_code" id="${escapeHtml(key)}_payment_postal_code" ${field.required ? 'required' : ''}>
                </div>
            </div>
            <label class="ar-field-check ar-payment-terms">
                <input type="checkbox" name="${escapeHtml(key)}_payment_accept" id="${escapeHtml(key)}_payment_accept" value="1" ${field.required ? 'required' : ''}>
                <span>I agree to the payment terms and authorise this charge.</span>
            </label>
            <div class="ar-field-error" data-for="${escapeHtml(field.key)}"></div>
        </div>`;
    }

    function mountPaymentStripe() {
        stripeInstances = {};
        if (!window.Stripe || !schema.payment_config?.stripe_key) return;

        $('.ar-payment-field').each(function () {
            const key = $(this).data('payment-field');
            const $panel = $(this).find(`[data-stripe-panel="${key}"]`);
            if (!$panel.length || $panel.is(':hidden')) return;

            const stripe = window.Stripe(schema.payment_config.stripe_key);
            const elements = stripe.elements();
            const card = elements.create('card', {
                style: {
                    base: {
                        fontSize: '15px',
                        color: '#1A2B4B',
                        '::placeholder': { color: '#9ca3af' },
                    },
                },
            });
            const mountEl = document.getElementById(`${key}_card_element`);
            if (!mountEl) return;
            mountEl.innerHTML = '';
            card.mount(`#${key}_card_element`);
            stripeInstances[key] = { stripe, card };
        });
    }

    function renderField(field) {
        if (field.type === 'section') {
            const desc = field.settings?.help_text || field.help_text;
            const descHtml = desc
                ? `<p class="ar-form-section__desc">${escapeHtml(desc)}</p>`
                : '';
            return `<div class="${sectionClassList(field)}">
                <h3 class="ar-form-section__title">${escapeHtml(field.label)}</h3>
                ${descHtml}
            </div>`;
        }

        if (field.type === 'payment') {
            return renderPaymentField(field);
        }

        const col = fieldWidthClass(field.col_span || 2);
        const required = field.required ? 'required' : '';
        const placeholder = field.placeholder ? `placeholder="${escapeHtml(field.placeholder)}"` : '';
        const help = field.settings?.help_text || field.help_text
            ? `<p class="ar-field-help">${escapeHtml(field.settings?.help_text || field.help_text)}</p>` : '';

        let input = '';

        switch (field.type) {
            case 'textarea':
                input = `<textarea name="${escapeHtml(field.key)}" id="field_${escapeHtml(field.key)}" class="ar-field-textarea" rows="4" ${required} ${placeholder}></textarea>`;
                break;
            case 'select':
                input = `<select name="${escapeHtml(field.key)}" id="field_${escapeHtml(field.key)}" class="ar-field-select select2-field" ${required}>
                    <option value="">-- Select --</option>${renderOptions(field)}</select>`;
                break;
            case 'radio':
                input = `<div class="ar-radio-group">${renderRadioOptions(field)}</div>`;
                break;
            case 'checkbox':
                const text = (field.settings && field.settings.text) || field.label;
                return `<div class="${col}">
                    <label class="ar-field-check" for="field_${escapeHtml(field.key)}">
                        <input type="checkbox" name="${escapeHtml(field.key)}" id="field_${escapeHtml(field.key)}" value="1" ${required}>
                        <span>${escapeHtml(text)}${reqMark(field)}</span>
                    </label>
                    ${help}
                    <div class="ar-field-error" data-for="${escapeHtml(field.key)}"></div>
                </div>`;
            case 'file':
                const multiple = (field.settings && field.settings.multiple) ? 'multiple' : '';
                const accept = (field.settings && field.settings.accept) ? `accept="${escapeHtml(field.settings.accept)}"` : '';
                const fileName = multiple ? field.key + '[]' : field.key;
                input = `<label class="ar-file-upload" for="field_${escapeHtml(field.key)}">
                    <i class="fa fa-cloud-upload-alt"></i>
                    <span>Choose file${multiple ? 's' : ''} to upload</span>
                    <input type="file" name="${escapeHtml(fileName)}" id="field_${escapeHtml(field.key)}" ${required} ${multiple} ${accept}>
                </label>`;
                break;
            case 'repeater':
                return renderRepeater(field);
            case 'date':
                input = `<input type="date" name="${escapeHtml(field.key)}" id="field_${escapeHtml(field.key)}" class="ar-field-input" ${required}>`;
                break;
            default:
                input = `<input type="${field.type || 'text'}" name="${escapeHtml(field.key)}" id="field_${escapeHtml(field.key)}" class="ar-field-input" ${required} ${placeholder}>`;
        }

        return `
            <div class="${col}">
                ${field.type !== 'checkbox' ? `<label class="ar-field-label" for="field_${escapeHtml(field.key)}">${escapeHtml(field.label)}${reqMark(field)}</label>` : ''}
                ${input}
                ${help}
                <div class="ar-field-error" data-for="${escapeHtml(field.key)}"></div>
            </div>`;
    }

    function renderSidebar() {
        $stepSidebar.empty();
        (schema.steps || []).forEach(function (step, index) {
            const isActive = index === currentStep;
            const isDone = index < currentStep;
            const fieldCount = (step.fields || []).length;
            $stepSidebar.append(`
                <li class="ar-step-nav-item">
                    <button type="button"
                            class="ar-step-nav-btn ${isActive ? 'is-active' : ''} ${isDone ? 'is-done' : ''}"
                            data-goto-step="${index}"
                            ${index > currentStep ? 'disabled' : ''}>
                        <span class="ar-step-num">${isDone ? '✓' : index + 1}</span>
                        <span class="ar-step-nav-text">
                            <span class="ar-step-nav-label">${escapeHtml(step.title || 'Step ' + (index + 1))}</span>
                            <span class="ar-step-nav-sub">${fieldCount} field${fieldCount === 1 ? '' : 's'}</span>
                        </span>
                    </button>
                </li>
            `);
        });

        $stepSidebar.find('[data-goto-step]').on('click', function () {
            const step = parseInt($(this).data('gotoStep'), 10);
            if (step <= currentStep) {
                currentStep = step;
                showStep(true);
            }
        });
    }

    function renderSteps() {
        $stepsContainer.empty();
        repeaterCounts = {};

        (schema.steps || []).forEach(function (step, index) {
            const fields = (step.fields || []).map(renderField).join('');
            const transition = step.transition || 'slide';
            $stepsContainer.append(`
                <div class="ar-form-step${index === 0 ? ' active' : ''} ar-anim-${transition}" data-step="${index}">
                    <div class="ar-fields-grid">${fields || '<p class="text-muted">No fields in this step.</p>'}</div>
                </div>
            `);
        });

        if ($.fn.select2) {
            $('.select2-field').select2({ width: '100%', minimumResultsForSearch: 10 });
        }
        mountPaymentStripe();
    }

    function totalSteps() {
        return (schema.steps || []).length || 1;
    }

    function updateProgressRing(pct) {
        const offset = RING_CIRCUMFERENCE - (pct / 100) * RING_CIRCUMFERENCE;
        $progressRingFill.css('stroke-dashoffset', offset);
        $progressText.text(pct + '%');
    }

    function showStep(animate) {
        const total = totalSteps();
        const pct = Math.round(((currentStep + 1) / total) * 100);
        const step = schema.steps[currentStep];

        $stepIndicator.text(`STEP ${currentStep + 1} OF ${total}`);
        $sectionTitle.text((step?.title || 'Step ' + (currentStep + 1)).toUpperCase());
        updateProgressRing(pct);

        $('.ar-form-step').removeClass('active');
        const $active = $(`.ar-form-step[data-step="${currentStep}"]`);
        if (animate) {
            $active.addClass('ar-anim-slide');
        }
        $active.addClass('active');

        $btnBack.toggleClass('d-none', currentStep === 0);
        const isLast = currentStep >= total - 1;
        $btnNext.toggleClass('d-none', isLast);
        $btnSubmit.toggleClass('d-none', !isLast);

        renderSidebar();
        mountPaymentStripe();
    }

    function clearErrors() {
        $('.ar-field-error').removeClass('is-visible').text('');
    }

    function showFieldError(key, message) {
        $(`.ar-field-error[data-for="${key}"]`).addClass('is-visible').text(message);
    }

    function validateCurrentStep() {
        clearErrors();
        const step = schema.steps[currentStep];
        if (!step) return true;

        let valid = true;
        (step.fields || []).forEach(function (field) {
            if (field.type === 'section' || !field.required) return;

            if (field.type === 'payment') {
                const key = field.key;
                const method = $(`[name="${key}_payment_method"]:checked`).val() || $(`[name="${key}_payment_method"]`).val();
                if (!$('#' + key + '_payment_email').val()) {
                    showFieldError(field.key, 'Payment email is required.');
                    valid = false;
                }
                if (!$('#' + key + '_payment_country').val() || !$('#' + key + '_payment_postal_code').val()) {
                    showFieldError(field.key, 'Country and postal code are required.');
                    valid = false;
                }
                if (!$('#' + key + '_payment_accept').is(':checked')) {
                    showFieldError(field.key, 'You must accept the payment terms.');
                    valid = false;
                }
                if (method === 'stripe' && !$('#' + key + '_card_holder_name').val()) {
                    showFieldError(field.key, 'Card holder name is required.');
                    valid = false;
                }
                return;
            }

            if (field.type === 'checkbox') {
                if (!$('#field_' + field.key).is(':checked')) {
                    showFieldError(field.key, field.label + ' is required.');
                    valid = false;
                }
                return;
            }

            if (field.type === 'repeater') {
                if (!$(`.repeater-group[data-key="${field.key}"] .ar-repeater-row`).length) {
                    showFieldError(field.key, field.label + ' is required.');
                    valid = false;
                }
                return;
            }

            if (field.type === 'file') {
                const el = document.getElementById('field_' + field.key);
                if (!el?.files?.length) {
                    showFieldError(field.key, field.label + ' is required.');
                    valid = false;
                }
                return;
            }

            const $el = $(`[name="${field.key}"]`);
            const val = $el.val();
            if (!val || (Array.isArray(val) && !val.length)) {
                showFieldError(field.key, field.label + ' is required.');
                valid = false;
            }
        });

        return valid;
    }

    function buildFormData() {
        const formData = new FormData($form[0]);
        formData.append('_token', csrfToken);
        return formData;
    }

    function processPaymentBeforeSubmit() {
        return new Promise(function (resolve, reject) {
            const paymentFields = [];
            (schema.steps || []).forEach(function (step) {
                (step.fields || []).forEach(function (field) {
                    if (field.type === 'payment') paymentFields.push(field);
                });
            });

            if (!paymentFields.length) {
                resolve();
                return;
            }

            const field = paymentFields[0];
            const key = field.key;
            const method = $(`[name="${key}_payment_method"]:checked`).val() || $(`[name="${key}_payment_method"]`).val();

            if (method === 'offline') {
                resolve();
                return;
            }

            const inst = stripeInstances[key];
            if (!inst) {
                reject('Card payments are not available. Please contact the school.');
                return;
            }

            inst.stripe.createToken(inst.card).then(function (result) {
                if (result.error) {
                    reject(result.error.message);
                } else {
                    $('#' + key + '_stripe_token').val(result.token.id);
                    resolve();
                }
            });
        });
    }

    function submitForm() {
        if (!validateCurrentStep()) return;

        $btnSubmit.prop('disabled', true).text('Submitting...');

        processPaymentBeforeSubmit()
            .then(function () {
                $.ajax({
                    url: `${apiBase}/${slug}/submit`,
                    method: 'POST',
                    data: buildFormData(),
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function (res) {
                        window.location.href = res.success_route || `/forms/${slug}/success`;
                    },
                    error: function (xhr) {
                        $btnSubmit.prop('disabled', false).text('Submit');
                        const msg = xhr.responseJSON?.message || 'Submission failed. Please check your entries and try again.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: msg });
                        } else {
                            alert(msg);
                        }
                    }
                });
            })
            .catch(function (msg) {
                $btnSubmit.prop('disabled', false).text('Submit');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: msg });
                } else {
                    alert(msg);
                }
            });
    }

    function bindEvents() {
        $btnNext.on('click', function () {
            if (!validateCurrentStep()) return;
            if (currentStep < totalSteps() - 1) {
                currentStep++;
                showStep(true);
                document.querySelector('.ar-form-main')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        $btnBack.on('click', function () {
            if (currentStep > 0) {
                currentStep--;
                showStep(true);
            }
        });

        $form.on('submit', function (e) {
            e.preventDefault();
            submitForm();
        });

        $stepsContainer.on('click', '.btn-add-repeater', function () {
            const key = $(this).data('field');
            const field = findField(key);
            if (!field) return;
            repeaterCounts[key] = (repeaterCounts[key] || 1) + 1;
            $(this).siblings('.repeater-rows').append(renderRepeaterRow(field, repeaterCounts[key] - 1));
        });

        $stepsContainer.on('click', '.btn-remove-repeater', function () {
            const $group = $(this).closest('.repeater-group');
            if ($group.find('.ar-repeater-row').length <= 1) return;
            $(this).closest('.ar-repeater-row').remove();
        });

        $stepsContainer.on('change', 'input[type="file"]', function () {
            const $label = $(this).closest('.ar-file-upload');
            const name = this.files?.[0]?.name;
            if (name && $label.length) {
                $label.find('span').first().text(name);
            }
        });

        $stepsContainer.on('change', 'input[name$="_payment_method"]', function () {
            const key = this.name.replace('_payment_method', '');
            const method = $(this).val();
            const $panel = $(`[data-stripe-panel="${key}"]`);
            if (method === 'stripe') {
                $panel.show();
                mountPaymentStripe();
            } else {
                $panel.hide();
            }
        });
    }

    function findField(key) {
        for (const step of (schema.steps || [])) {
            for (const field of (step.fields || [])) {
                if (field.key === key) return field;
            }
        }
        return null;
    }

    function init() {
        bindEvents();

        $.getJSON(`${apiBase}/${slug}`)
            .done(function (data) {
                schema = data;
                if ($pageTitle.length) {
                    $pageTitle.text(schema.name || $pageTitle.text());
                }
                $loading.addClass('d-none');
                $container.removeClass('d-none');
                renderSteps();
                showStep(false);
            })
            .fail(function (xhr) {
                const msg = xhr.status === 404
                    ? 'This form is not available or has been deactivated.'
                    : 'Unable to load this form. Please try again later.';
                showError(msg);
            });
    }

    $(init);
})(jQuery);
