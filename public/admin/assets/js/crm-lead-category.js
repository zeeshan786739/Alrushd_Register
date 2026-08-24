/**
 * Lead import category select + create UX (icon/color pickers, live preview, search).
 * PJAX-safe: re-executed when the page script section is swapped in.
 */
(function () {
    'use strict';

    var page = document.getElementById('crm-lead-category-page');
    if (!page) return;

    function bindCreateForm(form) {
        if (!form || form.getAttribute('data-bound') === '1') return;
        form.setAttribute('data-bound', '1');

        var nameInput = form.querySelector('[data-crm-preview-name]');
        var iconInput = form.querySelector('[data-crm-preview-icon-input]');
        var toneInput = form.querySelector('[data-crm-preview-tone-input]');
        var preview = form.querySelector('[data-crm-category-preview]');
        var previewIcon = form.querySelector('[data-crm-preview-icon]');
        var previewName = form.querySelector('[data-crm-preview-name-label]');
        var iconOptions = form.querySelectorAll('[data-crm-icon-option]');
        var colorOptions = form.querySelectorAll('[data-crm-color-option]');

        function updatePreview() {
            if (!preview) return;
            var name = (nameInput && nameInput.value.trim()) || 'Category name';
            var icon = (iconInput && iconInput.value) || 'solar:folder-with-files-linear';
            var tone = (toneInput && toneInput.value) || 'info';
            if (previewName) previewName.textContent = name;
            if (previewIcon) previewIcon.setAttribute('icon', icon);
            preview.setAttribute('data-tone', tone);
        }

        if (nameInput) {
            nameInput.addEventListener('input', updatePreview);
        }

        iconOptions.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var icon = btn.getAttribute('data-icon');
                if (!icon || !iconInput) return;
                iconInput.value = icon;
                iconOptions.forEach(function (other) {
                    var selected = other === btn;
                    other.classList.toggle('is-selected', selected);
                    other.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
                updatePreview();
            });
        });

        colorOptions.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tone = btn.getAttribute('data-tone');
                if (!tone || !toneInput) return;
                toneInput.value = tone;
                colorOptions.forEach(function (other) {
                    var selected = other === btn;
                    other.classList.toggle('is-selected', selected);
                    other.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
                updatePreview();
            });
        });

        updatePreview();
    }

    function bindSubmitLocks(root) {
        root.querySelectorAll('form').forEach(function (form) {
            if (form.getAttribute('data-submit-lock-bound') === '1') return;
            form.setAttribute('data-submit-lock-bound', '1');
            form.addEventListener('submit', function () {
                var buttons = form.querySelectorAll('[data-crm-submit-lock]');
                buttons.forEach(function (btn) {
                    btn.disabled = true;
                    btn.classList.add('is-busy');
                });
            });
        });
    }

    function bindChoiceCards(root) {
        root.querySelectorAll('.crm-category-choice').forEach(function (card) {
            var input = card.querySelector('.crm-category-choice__input');
            if (!input || input.getAttribute('data-choice-bound') === '1') return;
            input.setAttribute('data-choice-bound', '1');
            input.addEventListener('change', function () {
                root.querySelectorAll('.crm-category-choice').forEach(function (other) {
                    other.classList.toggle('is-selected', other.contains(input) && input.checked);
                });
                if (input.checked) {
                    card.classList.add('is-selected');
                }
            });
        });
    }

    function bindSearch(root) {
        var input = root.querySelector('[data-crm-category-search]');
        var empty = root.querySelector('[data-crm-category-empty-search]');
        if (!input || input.getAttribute('data-search-bound') === '1') return;
        input.setAttribute('data-search-bound', '1');

        input.addEventListener('input', function () {
            var q = (input.value || '').trim().toLowerCase();
            var items = root.querySelectorAll('[data-crm-category-item]');
            var visible = 0;
            items.forEach(function (item) {
                var name = item.getAttribute('data-name') || '';
                var show = !q || name.indexOf(q) !== -1;
                item.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            if (empty) {
                empty.classList.toggle('d-none', visible > 0 || items.length === 0);
            }
        });
    }

    bindCreateForm(page.querySelector('[data-crm-category-create]'));
    bindSubmitLocks(page);
    bindChoiceCards(page);
    bindSearch(page);
})();
