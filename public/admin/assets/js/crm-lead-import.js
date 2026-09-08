/**
 * Lead import wizard: category selection unlocks premium drag-and-drop upload.
 * Category create/delete run over AJAX — no page reload.
 */
(function () {
    'use strict';

    var page = document.getElementById('crm-lead-import-page');
    if (!page) return;

    var categoryRequired = page.getAttribute('data-category-required') === '1';
    var uploadForm = page.querySelector('[data-crm-import-upload-form]');
    var dropZone = page.querySelector('[data-crm-import-dropzone]');
    var fileInput = page.querySelector('[data-crm-import-file-input]');
    var hiddenCategory = page.querySelector('[data-crm-import-category-hidden]');
    var submitBtn = page.querySelector('[data-crm-import-submit]');
    var filePreview = page.querySelector('[data-crm-import-file-preview]');
    var fileNameEl = page.querySelector('[data-crm-import-file-name]');
    var fileSizeEl = page.querySelector('[data-crm-import-file-size]');
    var clearFileBtn = page.querySelector('[data-crm-import-clear-file]');
    var uploadLock = page.querySelector('[data-crm-import-upload-lock]');
    var toastSlot = page.querySelector('[data-crm-toast-slot]');
    var categoryList = page.querySelector('[data-crm-category-list]');
    var categoryEmpty = page.querySelector('[data-crm-import-category-empty]');
    var categoryToolbar = page.querySelector('[data-crm-import-category-toolbar]');
    var createPanel = page.querySelector('[data-crm-import-category-create-panel]');
    var csrf = page.getAttribute('data-csrf') || '';

    function selectedCategoryId() {
        if (hiddenCategory && hiddenCategory.value) {
            return hiddenCategory.value;
        }
        var checked = page.querySelector('[data-crm-import-category-input]:checked');
        return checked ? checked.value : '';
    }

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function extensionIcon(name) {
        var lower = (name || '').toLowerCase();
        if (lower.endsWith('.csv')) return 'solar:document-text-linear';
        return 'solar:document-linear';
    }

    function showToast(message, isError) {
        if (!toastSlot || !message) return;
        var el = document.createElement('div');
        el.className = 'crm-toast' + (isError ? ' is-error' : '');
        el.textContent = message;
        toastSlot.appendChild(el);
        requestAnimationFrame(function () {
            el.classList.add('is-visible');
        });
        setTimeout(function () {
            el.classList.remove('is-visible');
            setTimeout(function () { el.remove(); }, 220);
        }, 2800);
    }

    function syncUploadState() {
        var hasCategory = !categoryRequired || selectedCategoryId() !== '';
        var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

        if (hiddenCategory) {
            hiddenCategory.value = selectedCategoryId();
        }

        if (uploadLock) {
            uploadLock.classList.toggle('is-hidden', hasCategory);
        }
        if (dropZone) {
            dropZone.classList.toggle('is-locked', !hasCategory);
        }
        if (submitBtn) {
            submitBtn.disabled = !hasCategory || !hasFile;
        }

        page.querySelectorAll('[data-crm-import-step]').forEach(function (step) {
            var n = step.getAttribute('data-crm-import-step');
            step.classList.toggle('is-complete', n === '1' && hasCategory);
            step.classList.toggle('is-active', (n === '1' && !hasCategory) || (n === '2' && hasCategory && !hasFile) || (n === '2' && hasCategory && hasFile));
        });
    }

    function selectCategory(id) {
        page.querySelectorAll('[data-crm-import-category-input]').forEach(function (input) {
            input.checked = input.value === String(id);
        });
        page.querySelectorAll('[data-crm-import-category-card]').forEach(function (card) {
            card.classList.toggle('is-selected', card.getAttribute('data-category-id') === String(id));
        });
        page.querySelectorAll('.crm-category-choice').forEach(function (choice) {
            var inner = choice.querySelector('[data-crm-import-category-input]');
            choice.classList.toggle('is-selected', inner && inner.checked);
        });
        syncUploadState();
    }

    function clearCategorySelection() {
        page.querySelectorAll('[data-crm-import-category-input]').forEach(function (input) {
            input.checked = false;
        });
        page.querySelectorAll('[data-crm-import-category-card], .crm-category-choice').forEach(function (el) {
            el.classList.remove('is-selected');
        });
        if (hiddenCategory) hiddenCategory.value = '';
        syncUploadState();
    }

    function ensureCategoryListVisible() {
        if (categoryEmpty) categoryEmpty.classList.add('d-none');
        if (categoryToolbar) categoryToolbar.classList.remove('d-none');
        if (categoryList) categoryList.classList.remove('d-none');
    }

    function updateEmptyState() {
        var count = categoryList ? categoryList.querySelectorAll('[data-crm-import-category-card]').length : 0;
        if (count === 0) {
            if (categoryEmpty) categoryEmpty.classList.remove('d-none');
            if (categoryToolbar) categoryToolbar.classList.add('d-none');
            if (categoryList) categoryList.classList.add('d-none');
            clearCategorySelection();
        }
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function buildCategoryCard(category, selected) {
        var leadsLabel = category.leads_count === 1 ? 'lead' : 'leads';
        var deleteForm = category.leads_count === 0
            ? '<form method="POST" action="' + escapeHtml(category.destroy_url) + '" class="crm-import-category-delete" data-crm-category-delete-form data-crm-category-delete-ajax="1">'
                + '<input type="hidden" name="_token" value="' + escapeHtml(csrf) + '">'
                + '<input type="hidden" name="_method" value="DELETE">'
                + '<button type="submit" class="crm-import-category-delete__btn" title="Delete empty category">'
                + '<iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button></form>'
            : '';

        var html = ''
            + '<div class="crm-import-category-card crm-import-category-card--enter' + (selected ? ' is-selected' : '') + '"'
            + ' data-crm-category-item data-crm-import-category-card'
            + ' data-category-id="' + category.id + '"'
            + ' data-name="' + escapeHtml((category.name || '').toLowerCase()) + '"'
            + ' data-tone="' + escapeHtml(category.tone) + '">'
            + '<label class="crm-category-choice' + (selected ? ' is-selected' : '') + '" data-tone="' + escapeHtml(category.tone) + '">'
            + '<input type="radio" name="lead_category_id" value="' + category.id + '" class="crm-category-choice__input" data-crm-import-category-input' + (selected ? ' checked' : '') + '>'
            + '<span class="crm-category-choice__icon"><iconify-icon icon="' + escapeHtml(category.icon) + '"></iconify-icon></span>'
            + '<span class="crm-category-choice__body">'
            + '<span class="crm-category-choice__name">' + escapeHtml(category.name) + '</span>'
            + '<span class="crm-category-choice__meta">' + category.leads_count + ' ' + leadsLabel + '</span>'
            + '</span>'
            + '<span class="crm-category-choice__check" aria-hidden="true"><iconify-icon icon="solar:check-circle-bold"></iconify-icon></span>'
            + '</label>'
            + deleteForm
            + '</div>';

        var wrap = document.createElement('div');
        wrap.innerHTML = html;
        return wrap.firstElementChild;
    }

    function bindCategoryCard(card) {
        if (!card || card.getAttribute('data-bound') === '1') return;
        card.setAttribute('data-bound', '1');

        var input = card.querySelector('[data-crm-import-category-input]');
        if (input) {
            input.addEventListener('change', function () {
                selectCategory(input.value);
            });
        }

        var deleteForm = card.querySelector('[data-crm-category-delete-form]');
        if (deleteForm) {
            deleteForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var nameEl = card.querySelector('.crm-category-choice__name');
                var label = nameEl ? nameEl.textContent : 'this category';
                if (!window.confirm('Delete category “' + label + '”? This cannot be undone.')) {
                    return;
                }
                deleteCategory(deleteForm, card);
            });
        }
    }

    function bindCategoryCards(root) {
        (root || page).querySelectorAll('[data-crm-import-category-card]').forEach(bindCategoryCard);
    }

    function scrollToUpload() {
        var panel = page.querySelector('[data-crm-import-upload-panel]');
        if (panel) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function fetchJson(url, options) {
        return fetch(url, options).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data };
            }).catch(function () {
                return { ok: response.ok, status: response.status, data: {} };
            });
        });
    }

    function createCategory(form) {
        var submit = form.querySelector('[data-crm-category-create-submit]');
        var nameInput = form.querySelector('[data-crm-preview-name]');
        var nameError = form.querySelector('[data-crm-category-name-error]');

        if (nameError) {
            nameError.classList.add('d-none');
            nameError.textContent = '';
        }
        if (nameInput) nameInput.classList.remove('is-invalid');

        if (submit) {
            submit.disabled = true;
            submit.classList.add('is-busy');
        }

        fetchJson(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        }).then(function (result) {
            if (submit) {
                submit.disabled = false;
                submit.classList.remove('is-busy');
            }

            if (!result.ok) {
                var msg = (result.data && result.data.message) || 'Could not create category.';
                if (result.data && result.data.errors && result.data.errors.name && result.data.errors.name[0]) {
                    msg = result.data.errors.name[0];
                    if (nameError) {
                        nameError.textContent = msg;
                        nameError.classList.remove('d-none');
                    }
                    if (nameInput) nameInput.classList.add('is-invalid');
                }
                showToast(msg, true);
                return;
            }

            var category = result.data.category;
            if (!category || !categoryList) return;

            ensureCategoryListVisible();
            var card = buildCategoryCard(category, true);
            categoryList.prepend(card);
            bindCategoryCard(card);
            selectCategory(category.id);

            form.reset();
            var iconInput = form.querySelector('[data-crm-preview-icon-input]');
            var toneInput = form.querySelector('[data-crm-preview-tone-input]');
            if (iconInput) iconInput.value = 'solar:folder-with-files-linear';
            if (toneInput) toneInput.value = 'info';
            form.querySelectorAll('[data-crm-icon-option]').forEach(function (btn, i) {
                btn.classList.toggle('is-selected', i === 0);
            });
            form.querySelectorAll('[data-crm-color-option]').forEach(function (btn, i) {
                btn.classList.toggle('is-selected', i === 0);
            });
            var previewName = form.querySelector('[data-crm-preview-name-label]');
            if (previewName) previewName.textContent = 'Category name';

            if (createPanel) createPanel.open = false;

            showToast(result.data.message || 'Category created.');
            scrollToUpload();
        }).catch(function () {
            if (submit) {
                submit.disabled = false;
                submit.classList.remove('is-busy');
            }
            showToast('Could not create category. Please try again.', true);
        });
    }

    function deleteCategory(form, card) {
        var btn = form.querySelector('button');
        if (btn) btn.disabled = true;

        fetchJson(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(new FormData(form)).toString(),
        }).then(function (result) {
            if (btn) btn.disabled = false;

            if (!result.ok) {
                showToast((result.data && result.data.message) || 'Could not delete category.', true);
                return;
            }

            var wasSelected = card.classList.contains('is-selected');
            card.classList.add('crm-import-category-card--leave');
            setTimeout(function () {
                card.remove();
                if (wasSelected) clearCategorySelection();
                updateEmptyState();
            }, 180);

            showToast(result.data.message || 'Category deleted.');
        }).catch(function () {
            if (btn) btn.disabled = false;
            showToast('Could not delete category. Please try again.', true);
        });
    }

    function bindCreateForm() {
        var form = page.querySelector('[data-crm-category-create-ajax]');
        if (!form || form.getAttribute('data-ajax-bound') === '1') return;
        form.setAttribute('data-ajax-bound', '1');
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            createCategory(form);
        });
    }

    function showFilePreview(file) {
        if (!filePreview || !fileNameEl || !fileSizeEl) return;
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = formatBytes(file.size);
        var icon = filePreview.querySelector('[data-crm-import-file-icon]');
        if (icon) icon.setAttribute('icon', extensionIcon(file.name));
        filePreview.classList.remove('d-none');
        if (dropZone) dropZone.classList.add('has-file');
    }

    function clearFile() {
        if (fileInput) fileInput.value = '';
        if (filePreview) filePreview.classList.add('d-none');
        if (dropZone) dropZone.classList.remove('has-file');
        syncUploadState();
    }

    function assignFile(file) {
        if (!file || !fileInput) return;
        var dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        showFilePreview(file);
        syncUploadState();
    }

    function bindDropzone() {
        if (!dropZone || !fileInput) return;

        dropZone.addEventListener('click', function (e) {
            if (dropZone.classList.contains('is-locked')) return;
            if (e.target.closest('[data-crm-import-clear-file]')) return;
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files[0]) {
                showFilePreview(fileInput.files[0]);
            }
            syncUploadState();
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropZone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (!dropZone.classList.contains('is-locked')) {
                    dropZone.classList.add('is-dragover');
                }
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropZone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('is-dragover');
            });
        });

        dropZone.addEventListener('drop', function (e) {
            if (dropZone.classList.contains('is-locked')) return;
            var files = e.dataTransfer && e.dataTransfer.files;
            if (files && files[0]) assignFile(files[0]);
        });

        if (clearFileBtn) {
            clearFileBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                clearFile();
            });
        }
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', function () {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('is-busy');
            }
        });
    }

    bindCategoryCards(page);
    bindCreateForm();
    bindDropzone();
    syncUploadState();

    var preselected = page.getAttribute('data-selected-category');
    if (preselected) {
        selectCategory(preselected);
    }
})();
