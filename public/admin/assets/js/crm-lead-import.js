/**
 * Lead import: category select + add category, drag-and-drop upload.
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
    var categorySelect = page.querySelector('[data-crm-import-category-select]');
    var submitBtn = page.querySelector('[data-crm-import-submit]');
    var filePreview = page.querySelector('[data-crm-import-file-preview]');
    var dropzoneEmpty = page.querySelector('[data-crm-import-dropzone-empty]');
    var fileNameEl = page.querySelector('[data-crm-import-file-name]');
    var fileSizeEl = page.querySelector('[data-crm-import-file-size]');
    var clearFileBtn = page.querySelector('[data-crm-import-clear-file]');
    var toastSlot = page.querySelector('[data-crm-toast-slot]');
    var addPanel = page.querySelector('[data-crm-import-category-add-panel]');
    var addToggle = page.querySelector('[data-crm-import-add-category-toggle]');
    var addCancel = page.querySelector('[data-crm-import-add-category-cancel]');
    var csrf = page.getAttribute('data-csrf') || '';

    function selectedCategoryId() {
        if (categorySelect) {
            return categorySelect.value || '';
        }
        if (hiddenCategory && hiddenCategory.value) {
            return hiddenCategory.value;
        }
        return '';
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

        if (dropZone) {
            dropZone.classList.toggle('is-locked', !hasCategory);
        }
        if (submitBtn) {
            submitBtn.disabled = !hasCategory || !hasFile;
        }
    }

    function selectCategory(id) {
        if (categorySelect) {
            categorySelect.value = String(id);
            if (categorySelect.disabled && id) {
                categorySelect.disabled = false;
            }
            var placeholder = categorySelect.querySelector('option[value=""]');
            if (placeholder && id) {
                placeholder.textContent = 'Select a category…';
            }
        }
        syncUploadState();
    }

    function leadsCountLabel(count) {
        var n = parseInt(count, 10) || 0;
        return n === 1 ? '1 lead' : n.toLocaleString() + ' leads';
    }

    function addCategoryOption(category, selected) {
        if (!categorySelect) return;
        var opt = document.createElement('option');
        opt.value = String(category.id);
        opt.setAttribute('data-leads-count', String(category.leads_count || 0));
        opt.textContent = category.name + ' · ' + leadsCountLabel(category.leads_count);
        if (selected) opt.selected = true;
        categorySelect.appendChild(opt);
        categorySelect.disabled = false;
    }

    function setAddPanelOpen(open) {
        if (!addPanel) return;
        addPanel.classList.toggle('d-none', !open);
        if (addToggle) {
            addToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (open) {
            var nameInput = addPanel.querySelector('[data-crm-preview-name]');
            if (nameInput) nameInput.focus();
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
                Accept: 'application/json',
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
            if (!category) return;

            var exists = categorySelect && categorySelect.querySelector('option[value="' + category.id + '"]');
            if (!exists) {
                addCategoryOption(category, true);
            } else {
                selectCategory(category.id);
            }
            selectCategory(category.id);

            form.reset();
            setAddPanelOpen(false);
            showToast(result.data.message || 'Category created.');
        }).catch(function () {
            if (submit) {
                submit.disabled = false;
                submit.classList.remove('is-busy');
            }
            showToast('Could not create category. Please try again.', true);
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
        if (dropzoneEmpty) dropzoneEmpty.classList.add('d-none');
        if (dropZone) dropZone.classList.add('has-file');
    }

    function clearFile() {
        if (fileInput) fileInput.value = '';
        if (filePreview) filePreview.classList.add('d-none');
        if (dropzoneEmpty) dropzoneEmpty.classList.remove('d-none');
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

    if (categorySelect) {
        categorySelect.addEventListener('change', syncUploadState);
    }

    if (addToggle) {
        addToggle.addEventListener('click', function () {
            var open = addPanel && addPanel.classList.contains('d-none');
            setAddPanelOpen(!!open);
        });
    }

    if (addCancel) {
        addCancel.addEventListener('click', function () {
            setAddPanelOpen(false);
        });
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', function () {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('is-busy');
            }
        });
    }

    bindCreateForm();
    bindDropzone();
    syncUploadState();

    var preselected = page.getAttribute('data-selected-category');
    if (preselected) {
        selectCategory(preselected);
    }
})();
