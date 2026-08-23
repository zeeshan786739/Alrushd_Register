<script>
(function () {
    var SOURCE_LABELS = {
        leads: 'All CRM leads',
        customers: 'All customers',
        form_entries: 'Form Center submissions',
        integration_leads: 'Integrations & imports',
        selected_leads: 'Selected contacts',
        manual: 'Pasted email list',
    };

    function pageRoot(root) {
        return root || document.querySelector('#admin-page-content') || document;
    }

    function initWizard(root) {
        var scope = pageRoot(root);
        var form = scope.querySelector('[data-campaign-form]');
        if (!form || form.dataset.wizardBound === '1') return;
        form.dataset.wizardBound = '1';

        var wizard = form.querySelector('[data-campaign-wizard]');
        if (!wizard) return;

        var step = 1;
        var maxStep = 4;
        var panels = wizard.querySelectorAll('[data-wizard-panel]');
        var stepBtns = wizard.querySelectorAll('[data-wizard-goto]');
        var backBtn = form.querySelector('[data-wizard-back]');
        var nextBtn = form.querySelector('[data-wizard-next]');
        var submitBtn = form.querySelector('[data-wizard-submit]');
        var sendBtn = form.querySelector('[data-wizard-send]');
        var errorBox = form.querySelector('[data-wizard-error]');

        function clearError() {
            if (!errorBox) return;
            errorBox.hidden = true;
            errorBox.textContent = '';
        }

        function showError(message) {
            if (!errorBox) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Check this step', text: message, confirmButtonText: 'OK' });
                }
                return;
            }
            errorBox.hidden = false;
            errorBox.textContent = message;
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function showStep(n) {
            step = n;
            clearError();
            panels.forEach(function (panel) {
                var num = parseInt(panel.getAttribute('data-wizard-panel'), 10);
                var active = num === step;
                panel.classList.toggle('is-active', active);
                panel.hidden = !active;
            });
            stepBtns.forEach(function (btn) {
                var num = parseInt(btn.getAttribute('data-wizard-goto'), 10);
                btn.classList.toggle('is-active', num === step);
                btn.classList.toggle('is-complete', num < step);
            });
            if (backBtn) {
                backBtn.hidden = step <= 1;
                backBtn.style.display = step <= 1 ? 'none' : '';
            }
            if (nextBtn) {
                nextBtn.hidden = step >= maxStep;
                nextBtn.style.display = step >= maxStep ? 'none' : '';
            }
            if (submitBtn) {
                submitBtn.hidden = step < maxStep;
                submitBtn.style.display = step < maxStep ? 'none' : '';
            }
            if (sendBtn) {
                sendBtn.hidden = step < maxStep;
                sendBtn.style.display = step < maxStep ? 'none' : '';
            }
            if (step === maxStep) populateReview();
            try {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (err) { /* ignore */ }
        }

        function validateStep(n) {
            var panel = wizard.querySelector('[data-wizard-panel="' + n + '"]');
            if (!panel) return true;

            var fields = panel.querySelectorAll('[data-wizard-required]');
            for (var i = 0; i < fields.length; i++) {
                if (!fields[i].checkValidity()) {
                    fields[i].reportValidity();
                    showError('Please fill in all required fields before continuing.');
                    return false;
                }
            }

            if (n === 3) {
                var body = form.querySelector('[data-studio-editor]');
                if (body && !body.value.trim()) {
                    body.focus();
                    showError('Email content is required. Add HTML in the editor or pick a starter template.');
                    return false;
                }
            }

            return true;
        }

        function populateReview() {
            var name = form.querySelector('#name');
            var subject = form.querySelector('#subject');
            var fromName = form.querySelector('#from_name');
            var fromEmail = form.querySelector('#from_email');
            var sourceInput = form.querySelector('[data-audience-source]');
            var tracking = form.querySelector('#tracking_enabled');
            var body = form.querySelector('[data-studio-editor]');

            setText('[data-review-name]', name ? name.value : '—');
            setText('[data-review-subject]', subject ? subject.value : '—');
            var from = [(fromName && fromName.value) || 'Default sender', (fromEmail && fromEmail.value) ? '<' + fromEmail.value + '>' : ''].filter(Boolean).join(' ');
            setText('[data-review-from]', from || 'Default sender');
            var src = sourceInput ? sourceInput.value : 'manual';
            setText('[data-review-audience]', SOURCE_LABELS[src] || src);
            setText('[data-review-tracking]', tracking && tracking.checked ? 'Enabled' : 'Disabled');

            var pf = form.querySelector('[data-audience-preflight]');
            if (pf) {
                setText('[data-review-eligible]', textOf(pf, '[data-pf-eligible]'));
                setText('[data-review-selected]', textOf(pf, '[data-pf-selected]'));
                setText('[data-review-excluded]', textOf(pf, '[data-pf-unsub]'));
                setText('[data-review-preflight-msg]', textOf(pf, '[data-pf-message]'));
            }

            var preview = form.querySelector('[data-review-preview]');
            if (preview && body && window.emRenderEmailPreview) {
                preview.innerHTML = window.emRenderEmailPreview(body.value);
            }
        }

        function setText(sel, val) {
            var el = form.querySelector(sel);
            if (el) el.textContent = val || '—';
        }

        function textOf(parent, sel) {
            var el = parent.querySelector(sel);
            return el ? el.textContent : '—';
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                if (!validateStep(step)) return;
                if (step < maxStep) showStep(step + 1);
            });
        }
        if (backBtn) {
            backBtn.addEventListener('click', function () {
                if (step > 1) showStep(step - 1);
            });
        }
        stepBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = parseInt(btn.getAttribute('data-wizard-goto'), 10);
                if (target <= step) {
                    showStep(target);
                    return;
                }
                for (var s = step; s < target; s++) {
                    if (!validateStep(s)) return;
                }
                showStep(target);
            });
        });

        form.addEventListener('submit', function (e) {
            if (step < maxStep) {
                e.preventDefault();
                showError('Complete all steps before saving. Use Continue to reach the review step.');
            }
        });

        showStep(1);
    }

    function boot(root) {
        initWizard(pageRoot(root));
    }

    boot(document);

    if (!window.__adminPageLoadedHook) {
        window.__adminPageLoadedHook = true;
        document.addEventListener('admin:page-loaded', function (e) {
            boot(e.detail && e.detail.root ? e.detail.root : document);
        });
    }
})();
</script>
