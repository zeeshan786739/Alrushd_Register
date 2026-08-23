<script>
(function () {
    function titleCase(text) {
        return String(text || '')
            .toLowerCase()
            .replace(/\b\w/g, function (char) { return char.toUpperCase(); });
    }

    function initPermissionBuilder(root) {
        var scope = root || document;
        scope.querySelectorAll('[data-perm-builder]').forEach(function (builder) {
            if (builder.dataset.bound === '1') return;
            builder.dataset.bound = '1';

            var modules = [];
            try {
                modules = JSON.parse(builder.querySelector('[data-builder-modules-json]').textContent || '[]');
            } catch (e) {
                modules = [];
            }

            var existing = [];
            try {
                existing = JSON.parse(builder.dataset.existingNames || '[]');
            } catch (e) {
                existing = [];
            }

            var actionInput = builder.querySelector('[data-builder-action]');
            var resourceInput = builder.querySelector('[data-builder-resource]');
            var moduleInput = builder.querySelector('[data-builder-module]');
            var manualInput = builder.querySelector('[data-builder-manual-name]');
            var previewName = builder.querySelector('[data-builder-preview-name]');
            var previewMeta = builder.querySelector('[data-builder-preview-meta]');
            var previewStatus = builder.querySelector('[data-builder-preview-status]');
            var resourcePanel = builder.querySelector('[data-builder-resource-panel]');
            var resourceList = builder.querySelector('[data-builder-resource-list]');
            var customWrap = builder.querySelector('[data-builder-custom-resource]');
            var customInput = builder.querySelector('[data-builder-custom-input]');
            var placeholder = builder.querySelector('[data-builder-resource-placeholder]');
            var submitBtn = builder.querySelector('[data-builder-submit]');
            var form = builder.closest('form');

            function selectedModule() {
                return modules.find(function (item) { return item.key === moduleInput.value; }) || null;
            }

            function builtName() {
                if (manualInput && manualInput.value.trim()) {
                    return manualInput.value.trim().toLowerCase().replace(/\s+/g, ' ');
                }
                var action = (actionInput.value || '').trim().toLowerCase();
                var resource = (resourceInput.value || '').trim().toLowerCase();
                if (customInput && customInput.value.trim() && selectedModule() && selectedModule().allow_custom) {
                    resource = customInput.value.trim().toLowerCase().replace(/\s+/g, ' ');
                }
                if (!action || !resource) return '';
                return action + ' ' + resource;
            }

            function setStatus(state, message, icon) {
                if (!previewStatus) return;
                previewStatus.dataset.state = state;
                previewStatus.innerHTML =
                    '<iconify-icon icon="' + icon + '"></iconify-icon><span>' + message + '</span>';
            }

            function refreshPreview() {
                var name = builtName();
                if (manualInput && manualInput.value.trim()) {
                    previewName.textContent = titleCase(manualInput.value.trim());
                    previewMeta.textContent = 'Custom permission name';
                } else if (name) {
                    previewName.textContent = titleCase(name);
                    previewMeta.textContent = 'Will be saved as “' + name + '”';
                } else {
                    previewName.textContent = 'Choose options to build a permission';
                    previewMeta.textContent = 'This is what roles will show to admins.';
                }

                if (!name) {
                    setStatus('idle', 'Complete the steps to preview your permission.', 'solar:info-circle-linear');
                    if (submitBtn) submitBtn.disabled = false;
                    return;
                }

                if (existing.indexOf(name) !== -1) {
                    setStatus('duplicate', 'This permission already exists. Assign it to a role instead of creating a duplicate.', 'solar:close-circle-linear');
                    if (submitBtn) submitBtn.disabled = true;
                    return;
                }

                setStatus('ready', 'Looks good — ready to create.', 'solar:check-circle-linear');
                if (submitBtn) submitBtn.disabled = false;
            }

            function renderResources() {
                var mod = selectedModule();
                resourceList.innerHTML = '';

                if (!mod) {
                    resourcePanel.hidden = true;
                    customWrap.hidden = true;
                    placeholder.hidden = false;
                    resourceInput.value = '';
                    refreshPreview();
                    return;
                }

                placeholder.hidden = true;

                if (mod.allow_custom) {
                    resourcePanel.hidden = true;
                    customWrap.hidden = false;
                    if (customInput && customInput.value.trim()) {
                        resourceInput.value = customInput.value.trim().toLowerCase().replace(/\s+/g, ' ');
                    }
                    refreshPreview();
                    return;
                }

                customWrap.hidden = true;
                resourcePanel.hidden = false;

                mod.resources.forEach(function (resource) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'um-resource-chip' + (resourceInput.value === resource.value ? ' is-selected' : '');
                    btn.dataset.builderResourceOption = resource.value;
                    btn.setAttribute('aria-pressed', resourceInput.value === resource.value ? 'true' : 'false');
                    btn.innerHTML = '<strong>' + resource.label + '</strong><span>' + resource.value + '</span>';
                    btn.addEventListener('click', function () {
                        resourceInput.value = resource.value;
                        resourceList.querySelectorAll('.um-resource-chip').forEach(function (chip) {
                            chip.classList.remove('is-selected');
                            chip.setAttribute('aria-pressed', 'false');
                        });
                        btn.classList.add('is-selected');
                        btn.setAttribute('aria-pressed', 'true');
                        refreshPreview();
                    });
                    resourceList.appendChild(btn);
                });

                refreshPreview();
            }

            builder.querySelectorAll('[data-builder-action-option]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    actionInput.value = btn.dataset.builderActionOption;
                    builder.querySelectorAll('[data-builder-action-option]').forEach(function (chip) {
                        chip.classList.remove('is-selected');
                        chip.setAttribute('aria-pressed', 'false');
                    });
                    btn.classList.add('is-selected');
                    btn.setAttribute('aria-pressed', 'true');
                    refreshPreview();
                });
            });

            builder.querySelectorAll('[data-builder-module-option]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    moduleInput.value = btn.dataset.builderModuleOption;
                    resourceInput.value = '';
                    builder.querySelectorAll('[data-builder-module-option]').forEach(function (chip) {
                        chip.classList.remove('is-selected');
                        chip.setAttribute('aria-pressed', 'false');
                    });
                    btn.classList.add('is-selected');
                    btn.setAttribute('aria-pressed', 'true');
                    renderResources();
                });
            });

            builder.querySelectorAll('[data-builder-preset]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var parts = (btn.dataset.builderPreset || '').split('|');
                    if (parts.length !== 2) return;
                    actionInput.value = parts[0];
                    resourceInput.value = parts[1];
                    moduleInput.value = '';

                    builder.querySelectorAll('[data-builder-action-option]').forEach(function (chip) {
                        var active = chip.dataset.builderActionOption === parts[0];
                        chip.classList.toggle('is-selected', active);
                        chip.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });

                    modules.forEach(function (mod) {
                        var match = (mod.resources || []).some(function (resource) {
                            return resource.value === parts[1];
                        });
                        if (match) {
                            moduleInput.value = mod.key;
                        }
                    });

                    builder.querySelectorAll('[data-builder-module-option]').forEach(function (chip) {
                        var active = chip.dataset.builderModuleOption === moduleInput.value;
                        chip.classList.toggle('is-selected', active);
                        chip.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });

                    if (manualInput) manualInput.value = '';
                    renderResources();
                });
            });

            if (customInput) {
                customInput.addEventListener('input', function () {
                    resourceInput.value = customInput.value.trim().toLowerCase().replace(/\s+/g, ' ');
                    refreshPreview();
                });
            }

            if (manualInput) {
                manualInput.addEventListener('input', refreshPreview);
            }

            if (form) {
                form.addEventListener('submit', function (event) {
                    var name = builtName();
                    if (!manualInput || !manualInput.value.trim()) {
                        if (!name) {
                            event.preventDefault();
                            setStatus('idle', 'Choose an action, module, and feature before saving.', 'solar:info-circle-linear');
                            return;
                        }
                        if (existing.indexOf(name) !== -1) {
                            event.preventDefault();
                            setStatus('duplicate', 'This permission already exists.', 'solar:close-circle-linear');
                        }
                    }
                });
            }

            renderResources();
            refreshPreview();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initPermissionBuilder(document);
    });

    document.addEventListener('admin:page-loaded', function (e) {
        initPermissionBuilder(e.detail && e.detail.root ? e.detail.root : document);
    });
})();
</script>
