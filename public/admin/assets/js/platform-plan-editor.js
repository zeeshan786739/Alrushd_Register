(function () {
    const moduleRoot = document.getElementById('planModules');
    const moduleCountEl = document.getElementById('moduleCount');
    const selectAllBtn = document.getElementById('selectAllModules');
    const clearAllBtn = document.getElementById('clearAllModules');

    function updateModuleUi() {
        if (!moduleRoot) return;
        const boxes = moduleRoot.querySelectorAll('input[name="modules[]"]');
        let checked = 0;
        boxes.forEach(box => {
            const card = box.closest('.plan-module-card');
            if (card) card.classList.toggle('is-checked', box.checked);
            if (box.checked) checked += 1;
        });
        if (moduleCountEl) moduleCountEl.textContent = String(checked);
    }

    if (moduleRoot) {
        moduleRoot.addEventListener('change', updateModuleUi);
        updateModuleUi();

        selectAllBtn?.addEventListener('click', () => {
            moduleRoot.querySelectorAll('input[name="modules[]"]').forEach(box => { box.checked = true; });
            updateModuleUi();
        });

        clearAllBtn?.addEventListener('click', () => {
            moduleRoot.querySelectorAll('input[name="modules[]"]').forEach(box => { box.checked = false; });
            updateModuleUi();
        });
    }

    const container = document.getElementById('planFeatures');
    const addBtn = document.getElementById('addFeatureBtn');
    if (!container || !addBtn) return;

    function bindRemove(btn) {
        btn.addEventListener('click', () => {
            const rows = container.querySelectorAll('.plan-feature-row');
            if (rows.length <= 1) {
                const input = rows[0]?.querySelector('input');
                if (input) input.value = '';
                return;
            }
            btn.closest('.plan-feature-row')?.remove();
        });
    }

    container.querySelectorAll('.plan-feature-row__remove').forEach(bindRemove);

    function addFeatureRow(value) {
        const row = document.createElement('div');
        row.className = 'plan-feature-row';
        row.innerHTML =
            '<span class="plan-feature-row__drag" aria-hidden="true"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>' +
            '<input type="text" name="extra_features[]" class="plan-feature-row__input" placeholder="e.g. Email support">' +
            '<button type="button" class="plan-feature-row__remove" title="Remove line" aria-label="Remove line">' +
            '<iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>';
        if (value) row.querySelector('input').value = value;
        container.appendChild(row);
        bindRemove(row.querySelector('.plan-feature-row__remove'));
        row.querySelector('input')?.focus();
    }

    addBtn.addEventListener('click', () => addFeatureRow(''));

    document.querySelectorAll('.plan-interval input').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.plan-interval').forEach(label => label.classList.remove('is-active'));
            radio.closest('.plan-interval')?.classList.add('is-active');
            const trialField = document.getElementById('trialDaysField');
            if (trialField) {
                trialField.style.opacity = radio.value === 'lifetime' ? '.5' : '1';
            }
        });
    });

    const checked = document.querySelector('.plan-interval input:checked');
    checked?.dispatchEvent(new Event('change'));
})();
