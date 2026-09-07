(function () {
    'use strict';

    var STORAGE_KEY = 'crm_leads_view';
    var page = document.getElementById('crm-leads-page');
    if (!page) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var toastSlot = page.querySelector('[data-crm-toast-slot]');
    var pending = {};
    var openMenu = null;

    var toggle = page.querySelector('[data-crm-view-toggle]');
    var buttons = toggle ? toggle.querySelectorAll('button[data-view]') : [];

    function applyView(view) {
        var normalized = view === 'list' ? 'list' : 'board';
        page.classList.remove('crm-board-view', 'crm-list-view');
        page.classList.add(normalized === 'list' ? 'crm-list-view' : 'crm-board-view');

        buttons.forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-view') === normalized);
        });

        page.querySelectorAll('[data-crm-view-input]').forEach(function (input) {
            input.value = normalized;
        });

        try {
            localStorage.setItem(STORAGE_KEY, normalized);
        } catch (e) {
            /* ignore storage errors */
        }

        syncViewQuery(normalized);
    }

    function syncViewQuery(view) {
        try {
            var url = new URL(window.location.href);
            if (url.searchParams.get('view') === view) return;
            url.searchParams.set('view', view);
            window.history.replaceState({}, '', url.toString());
        } catch (e) {
            /* ignore history errors */
        }
    }

    var initialView = page.getAttribute('data-initial-view') || 'board';
    var savedView = initialView;
    try {
        var urlView = new URL(window.location.href).searchParams.get('view');
        savedView = urlView || localStorage.getItem(STORAGE_KEY) || initialView;
    } catch (e) {
        savedView = initialView;
    }

    applyView(savedView === 'list' ? 'list' : 'board');

    if (toggle) {
        toggle.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-view]');
            if (!button) return;
            var nextView = button.getAttribute('data-view') === 'list' ? 'list' : 'board';
            applyView(nextView);
            try {
                var url = new URL(window.location.href);
                url.searchParams.set('view', nextView);
                window.location.href = url.toString();
            } catch (e) {
                /* fallback keeps local toggle only */
            }
        });
    }

    var advancedToggle = page.querySelector('[data-crm-toggle-advanced-filters]');
    var advancedPanel = page.querySelector('[data-crm-advanced-filters]');
    if (advancedToggle && advancedPanel) {
        advancedToggle.addEventListener('click', function () {
            var expanded = advancedToggle.getAttribute('aria-expanded') === 'true';
            advancedToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            advancedPanel.hidden = expanded;
        });
    }

    page.querySelectorAll('[data-crm-toggle-save-filter]').forEach(function (button) {
        button.addEventListener('click', function () {
            var form = document.getElementById('crm-save-filter-form');
            if (!form) return;
            form.hidden = !form.hidden;
            if (!form.hidden) {
                var input = form.querySelector('input[name="name"]');
                if (input) input.focus();
            }
        });
    });

    var filterForm = document.getElementById('crm-save-filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function (event) {
            var nameInput = filterForm.querySelector('input[name="name"]');
            if (nameInput && !nameInput.value.trim()) {
                event.preventDefault();
            }
        });
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
            setTimeout(function () { el.remove(); }, 200);
        }, 2600);
    }

    function isInteractiveTarget(target) {
        return !!target.closest('a, button, input, select, textarea, label, form, .fc-table-actions, [data-crm-inline], .crm-inline-menu, .crm-inline-option, .crm-list-row__handle, .crm-list-action, .crm-list-status-drop, .crm-lead-panel__tool, .crm-lead-panel__contact-chip, .crm-lead-panel__note-form, .crm-lead-drawer__close, .crm-list-row__select, .crm-list-bulk-bar, [data-crm-bulk-clear], [data-crm-bulk-apply], [data-crm-panel-edit], [data-crm-panel-cancel], [data-crm-panel-edit-form], [data-crm-panel-save], .crm-board-card__quick-action');
    }

    function draggableLeadItem(fromTarget) {
        var handle = fromTarget.closest('[data-crm-list-drag]');
        if (handle) {
            return handle.closest('[data-crm-list-row]');
        }

        return fromTarget.closest('[data-crm-board-card]');
    }

    function statusDropZone(fromTarget) {
        return fromTarget.closest('[data-crm-dropzone], [data-crm-list-dropzone]');
    }

    function positionFixedMenu(control, menu) {
        var trigger = control.querySelector('.crm-inline-trigger');
        if (!trigger || !menu) return;
        var rect = trigger.getBoundingClientRect();
        var gap = 6;
        var maxHeight = 260;
        var viewportPadding = 12;
        var spaceBelow = window.innerHeight - rect.bottom - gap - viewportPadding;
        var spaceAbove = rect.top - gap - viewportPadding;
        var openUp = spaceBelow < 160 && spaceAbove > spaceBelow;
        var maxVisible = Math.max(120, Math.min(maxHeight, openUp ? spaceAbove : spaceBelow));

        menu.style.top = openUp
            ? Math.max(viewportPadding, rect.top - gap - maxVisible) + 'px'
            : Math.min(window.innerHeight - viewportPadding - maxVisible, rect.bottom + gap) + 'px';
        menu.style.left = Math.min(
            Math.max(viewportPadding, rect.left),
            window.innerWidth - viewportPadding - 160
        ) + 'px';
        menu.style.minWidth = Math.max(rect.width, 160) + 'px';
        menu.style.maxHeight = maxVisible + 'px';
    }

    function portalMenu(control, menu) {
        menu.__crmOwner = control;
        document.body.appendChild(menu);
        menu.classList.add('crm-inline-menu--fixed');
        positionFixedMenu(control, menu);
    }

    function restoreMenu(control, menu) {
        if (!control || !menu) return;
        menu.classList.remove('crm-inline-menu--fixed');
        menu.style.top = '';
        menu.style.left = '';
        menu.style.minWidth = '';
        menu.style.maxHeight = '';
        control.appendChild(menu);
        delete menu.__crmOwner;
    }

    function menuForControl(control) {
        if (!control) return null;
        var localMenu = control.querySelector('.crm-inline-menu');
        if (localMenu) return localMenu;
        return Array.prototype.find.call(
            document.querySelectorAll('.crm-inline-menu'),
            function (menu) { return menu.__crmOwner === control; }
        ) || null;
    }

    function controlFromOption(option) {
        if (!option) return null;
        var menu = option.closest('.crm-inline-menu');
        if (menu && menu.__crmOwner) return menu.__crmOwner;
        return option.closest('[data-crm-inline].crm-inline-control');
    }

    function onMenuViewportChange() {
        if (!openMenu) return;
        var menu = menuForControl(openMenu);
        if (!menu || menu.hidden) {
            closeOpenMenu();
            return;
        }
        positionFixedMenu(openMenu, menu);
    }

    function openMenuControl(control) {
        var trigger = control.querySelector('.crm-inline-trigger');
        var menu = control.querySelector('.crm-inline-menu');
        if (!trigger || !menu) return;
        menu.hidden = false;
        control.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        portalMenu(control, menu);
        openMenu = control;
        window.addEventListener('scroll', onMenuViewportChange, true);
        window.addEventListener('resize', onMenuViewportChange);
    }

    function closeOpenMenu() {
        if (!openMenu) return;
        openMenu.classList.remove('is-open');
        var trigger = openMenu.querySelector('.crm-inline-trigger');
        var menu = menuForControl(openMenu);
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
        if (menu) {
            menu.hidden = true;
            if (menu.__crmOwner) restoreMenu(openMenu, menu);
        }
        openMenu = null;
        window.removeEventListener('scroll', onMenuViewportChange, true);
        window.removeEventListener('resize', onMenuViewportChange);
    }

    function applyControlVisual(control, tone, icon, label) {
        if (tone) control.setAttribute('data-tone', tone);
        if (icon) control.setAttribute('data-icon', icon);
        var iconEl = control.querySelector('.crm-inline-trigger__icon');
        var labelEl = control.querySelector('.crm-inline-trigger__label');
        if (iconEl && icon) iconEl.setAttribute('icon', icon);
        if (labelEl && label != null) labelEl.textContent = label;
    }

    function markSelected(control, value) {
        control.querySelectorAll('.crm-inline-option').forEach(function (opt) {
            var selected = String(opt.getAttribute('data-value') || '') === String(value || '');
            opt.classList.toggle('is-selected', selected);
            opt.setAttribute('aria-selected', selected ? 'true' : 'false');
            var check = opt.querySelector('.crm-inline-option__check');
            if (selected && !check) {
                var icon = document.createElement('iconify-icon');
                icon.className = 'crm-inline-option__check';
                icon.setAttribute('icon', 'solar:check-circle-bold');
                opt.appendChild(icon);
            } else if (!selected && check) {
                check.remove();
            }
        });
    }

    function inlineUrl(leadId) {
        var template = page.getAttribute('data-inline-url-template') || '';
        return template.replace('__ID__', String(leadId));
    }

    function panelUrl(leadId) {
        var template = page.getAttribute('data-panel-url-template') || '';
        return template.replace('__ID__', String(leadId));
    }

    function panelEditUrl(leadId) {
        var template = page.getAttribute('data-panel-edit-url-template') || '';
        return template.replace('__ID__', String(leadId));
    }

    function updateUrl(leadId) {
        var template = page.getAttribute('data-update-url-template') || '';
        return template.replace('__ID__', String(leadId));
    }

    function panelHost() {
        return document.querySelector('[data-crm-lead-panel-host]');
    }

    function syncDrawerHeader(title, subtitle) {
        var drawerTitle = document.getElementById('crmLeadDetailDrawerLabel');
        var drawerSubtitle = document.querySelector('[data-crm-panel-subtitle]');
        if (drawerTitle && title) drawerTitle.textContent = title;
        if (drawerSubtitle) drawerSubtitle.textContent = subtitle || '';
    }

    function syncPanelHeaderFromHost(host) {
        var titleEl = host.querySelector('[data-crm-panel-title]');
        var email = host.querySelector('.crm-lead-panel__contact-chip');
        syncDrawerHeader(
            titleEl ? titleEl.textContent.trim() : 'Lead preview',
            email ? email.textContent.trim() : 'Review and update without leaving the workspace'
        );
    }

    function syncBoardCardPriority(leadId, priority) {
        if (!priority) return;
        page.querySelectorAll('[data-crm-board-card][data-lead-id="' + leadId + '"]').forEach(function (card) {
            card.classList.remove(
                'crm-board-card--priority-low',
                'crm-board-card--priority-medium',
                'crm-board-card--priority-high',
                'crm-board-card--priority-urgent'
            );
            card.classList.add('crm-board-card--priority-' + priority);
        });
    }

    function syncLeadDisplay(lead) {
        if (!lead || !lead.id) return;
        var leadId = String(lead.id);

        page.querySelectorAll('[data-lead-id="' + leadId + '"] .crm-board-card__title').forEach(function (el) {
            if (lead.full_name) el.textContent = lead.full_name;
        });
        page.querySelectorAll('[data-crm-list-row][data-lead-id="' + leadId + '"] .crm-list-row__name').forEach(function (el) {
            if (lead.full_name) el.textContent = lead.full_name;
        });
        page.querySelectorAll('[data-lead-id="' + leadId + '"] .crm-board-card__meta, [data-crm-list-row][data-lead-id="' + leadId + '"] .crm-list-row__meta').forEach(function (el) {
            if (lead.email || lead.phone) {
                el.textContent = lead.email || lead.phone;
            }
        });
        page.querySelectorAll('[data-lead-id="' + leadId + '"] .crm-lead-avatar').forEach(function (el) {
            if (lead.full_name) {
                var parts = lead.full_name.trim().split(/\s+/);
                var initials = parts.slice(0, 2).map(function (p) { return p.charAt(0).toUpperCase(); }).join('');
                el.textContent = initials || '?';
            }
        });

        if (lead.lead_status) {
            page.querySelectorAll('[data-lead-id="' + leadId + '"][data-current-status]').forEach(function (el) {
                el.setAttribute('data-current-status', lead.lead_status);
            });
        }
        if (lead.priority) {
            syncBoardCardPriority(leadId, lead.priority);
        }
    }

    function fetchPanelHtml(url) {
        return fetch(url, {
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        }).then(function (response) {
            if (!response.ok) throw new Error('Failed to load lead panel');
            return response.text();
        });
    }

    function loadPanelView(leadId, options) {
        var host = panelHost();
        var drawerEl = document.getElementById('crmLeadDetailDrawer');
        if (!host || !leadId) return Promise.resolve();

        host.innerHTML = panelLoadingMarkup();
        if (options && options.showDrawer && drawerEl && window.bootstrap) {
            window.bootstrap.Offcanvas.getOrCreateInstance(drawerEl).show();
        }

        return fetchPanelHtml(panelUrl(leadId)).then(function (html) {
            host.innerHTML = html;
            syncPanelHeaderFromHost(host);
        }).catch(function () {
            host.innerHTML = '<div class="crm-lead-panel-loading"><span>Could not load lead details. Please try again.</span></div>';
            showToast('Could not load lead details.', true);
        });
    }

    function loadPanelEdit(leadId) {
        var host = panelHost();
        var drawerEl = document.getElementById('crmLeadDetailDrawer');
        if (!host || !leadId) return;

        if (drawerEl && window.bootstrap) {
            window.bootstrap.Offcanvas.getOrCreateInstance(drawerEl).show();
        }

        host.innerHTML = panelLoadingMarkup();
        syncDrawerHeader('Edit lead', 'Update details without leaving the workspace');

        fetchPanelHtml(panelEditUrl(leadId)).then(function (html) {
            host.innerHTML = html;
            var firstInput = host.querySelector('input[name="first_name"]');
            if (firstInput) firstInput.focus();
        }).catch(function () {
            host.innerHTML = '<div class="crm-lead-panel-loading"><span>Could not load edit form. Please try again.</span></div>';
            showToast('Could not load edit form.', true);
        });
    }

    function showPanelEditErrors(form, errors) {
        var box = form.querySelector('[data-crm-panel-edit-errors]');
        if (!box) return;
        var messages = [];
        Object.keys(errors || {}).forEach(function (key) {
            (errors[key] || []).forEach(function (msg) {
                messages.push(msg);
            });
        });
        if (!messages.length) {
            box.hidden = true;
            box.textContent = '';
            return;
        }
        box.hidden = false;
        box.innerHTML = messages.map(function (msg) {
            return '<div>' + msg + '</div>';
        }).join('');
    }

    function submitPanelEdit(form) {
        var leadId = form.getAttribute('data-lead-id');
        var saveBtn = form.querySelector('[data-crm-panel-save]');
        var formData = new FormData(form);
        formData.append('_method', 'PUT');

        if (saveBtn) saveBtn.disabled = true;
        showPanelEditErrors(form, {});

        fetch(updateUrl(leadId), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data };
            }).catch(function () {
                return { ok: response.ok, status: response.status, data: {} };
            });
        }).then(function (result) {
            if (result.status === 422 && result.data && result.data.errors) {
                showPanelEditErrors(form, result.data.errors);
                showToast('Please fix the highlighted fields.', true);
                return;
            }
            if (!result.ok) {
                showToast((result.data && result.data.message) || 'Could not save lead.', true);
                return;
            }

            syncLeadDisplay(result.data.lead || { id: leadId });
            showToast((result.data && result.data.message) || 'Lead updated.', false);
            loadPanelView(leadId);
        }).catch(function () {
            showToast('Could not save lead. Please try again.', true);
        }).finally(function () {
            if (saveBtn) saveBtn.disabled = false;
        });
    }

    function panelLoadingMarkup() {
        return '<div class="crm-lead-panel-loading"><div class="crm-lead-panel-loading__spinner"></div><span>Loading lead details…</span></div>';
    }

    function openLeadPanel(leadId) {
        var drawerEl = document.getElementById('crmLeadDetailDrawer');
        if (!leadId || !window.bootstrap || !drawerEl) return;
        loadPanelView(leadId, { showDrawer: true });
    }

    function refreshBoardColumnCounts() {
        page.querySelectorAll('[data-crm-dropzone]').forEach(function (column) {
            var count = column.querySelectorAll('[data-crm-board-card]').length;
            var countEl = column.querySelector('.crm-board-column__count');
            var empty = column.querySelector('.crm-board-empty');
            if (countEl) countEl.textContent = String(count);
            if (empty) empty.hidden = count > 0;
        });
    }

    function syncLeadStatusControls(leadId, value, data) {
        page.querySelectorAll('[data-crm-inline][data-lead-id="' + leadId + '"][data-field="lead_status"]').forEach(function (control) {
            control.setAttribute('data-previous', value);
            applyControlVisual(control, data.tone, data.icon, data.label);
            markSelected(control, value);
        });
    }

    function updateLeadStatus(leadId, value) {
        return fetch(inlineUrl(leadId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ field: 'lead_status', value: value }),
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, data: data };
            }).catch(function () {
                return { ok: response.ok, data: {} };
            });
        });
    }

    var draggedItem = null;
    var dragOrigin = null;
    var dragNextSibling = null;
    var dragMoved = false;
    var suppressOpenUntil = 0;
    var canUpdateLeads = page.getAttribute('data-can-update') === '1';

    page.addEventListener('dragstart', function (event) {
        if (event.target.closest('[data-crm-inline], .crm-board-card__quick-action')) {
            event.preventDefault();
            return;
        }

        var item = draggableLeadItem(event.target);
        if (!item || !page.contains(item) || !canUpdateLeads) {
            event.preventDefault();
            return;
        }

        draggedItem = item;
        dragOrigin = item.parentElement;
        dragNextSibling = item.nextElementSibling;
        dragMoved = false;
        item.classList.add('is-dragging');
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', item.getAttribute('data-lead-id') || '');
        }
    });

    page.addEventListener('drag', function () {
        dragMoved = true;
    });

    page.addEventListener('dragover', function (event) {
        var zone = statusDropZone(event.target);
        if (!draggedItem || !zone || !page.contains(zone)) return;
        event.preventDefault();
        zone.classList.add('is-drag-over');
        if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
    });

    page.addEventListener('dragleave', function (event) {
        var zone = statusDropZone(event.target);
        if (!zone || zone.contains(event.relatedTarget)) return;
        zone.classList.remove('is-drag-over');
    });

    page.addEventListener('drop', function (event) {
        var zone = statusDropZone(event.target);
        if (!draggedItem || !zone || !page.contains(zone)) return;
        event.preventDefault();

        var targetStatus = zone.getAttribute('data-status');
        var item = draggedItem;
        var origin = dragOrigin;
        var nextSibling = dragNextSibling;
        var leadId = item.getAttribute('data-lead-id');
        var previousStatus = item.getAttribute('data-current-status');
        var isBoardCard = item.hasAttribute('data-crm-board-card');
        var targetBody = isBoardCard ? (zone.querySelector('.crm-board-column__body') || zone) : null;
        var empty = targetBody ? targetBody.querySelector('.crm-board-empty') : null;

        page.querySelectorAll('[data-crm-dropzone].is-drag-over, [data-crm-list-dropzone].is-drag-over').forEach(function (dropzone) {
            dropzone.classList.remove('is-drag-over');
        });

        if (!targetStatus || !leadId || targetStatus === previousStatus) return;

        if (isBoardCard && targetBody) {
            targetBody.insertBefore(item, empty || null);
            refreshBoardColumnCounts();
        }

        item.setAttribute('data-current-status', targetStatus);
        item.classList.add('is-status-updated');
        window.setTimeout(function () {
            item.classList.remove('is-status-updated');
        }, 900);

        updateLeadStatus(leadId, targetStatus).then(function (result) {
            if (!result.ok) {
                item.setAttribute('data-current-status', previousStatus || '');
                if (isBoardCard && origin) origin.insertBefore(item, nextSibling || null);
                refreshBoardColumnCounts();
                showToast((result.data && (result.data.message || result.data.error)) || 'Status update failed.', true);
                return;
            }

            syncLeadStatusControls(leadId, targetStatus, result.data || {});
            showToast((result.data && result.data.message) || 'Status updated.', false);
        }).catch(function () {
            item.setAttribute('data-current-status', previousStatus || '');
            if (isBoardCard && origin) origin.insertBefore(item, nextSibling || null);
            refreshBoardColumnCounts();
            showToast('Status update failed. Please try again.', true);
        });
    });

    page.addEventListener('dragend', function () {
        if (draggedItem) draggedItem.classList.remove('is-dragging');
        page.querySelectorAll('[data-crm-dropzone].is-drag-over, [data-crm-list-dropzone].is-drag-over').forEach(function (dropzone) {
            dropzone.classList.remove('is-drag-over');
        });
        draggedItem = null;
        dragOrigin = null;
        dragNextSibling = null;
        if (dragMoved) {
            suppressOpenUntil = Date.now() + 250;
        }
        dragMoved = false;
    });

    refreshBoardColumnCounts();

    page.addEventListener('click', function (event) {
        var editBtn = event.target.closest('[data-crm-panel-edit]');
        if (editBtn && page.contains(editBtn)) {
            event.preventDefault();
            event.stopPropagation();
            loadPanelEdit(editBtn.getAttribute('data-lead-id'));
            return;
        }

        var cancelBtn = event.target.closest('[data-crm-panel-cancel]');
        if (cancelBtn && page.contains(cancelBtn)) {
            event.preventDefault();
            event.stopPropagation();
            var panel = cancelBtn.closest('[data-crm-lead-panel-edit]');
            var leadId = panel ? panel.getAttribute('data-lead-id') : null;
            if (leadId) loadPanelView(leadId);
            return;
        }

        var openTarget = event.target.closest('[data-crm-lead-open]');
        if (openTarget && page.contains(openTarget) && Date.now() >= suppressOpenUntil && !isInteractiveTarget(event.target)) {
            var leadId = openTarget.getAttribute('data-lead-id') || openTarget.closest('[data-lead-id]')?.getAttribute('data-lead-id');
            if (leadId) {
                event.preventDefault();
                openLeadPanel(leadId);
            }
            return;
        }

        var trigger = event.target.closest('.crm-inline-trigger');
        if (trigger && page.contains(trigger)) {
            event.preventDefault();
            event.stopPropagation();
            var control = trigger.closest('[data-crm-inline].crm-inline-control');
            if (!control || control.classList.contains('is-busy')) return;
            var menu = control.querySelector('.crm-inline-menu');
            var willOpen = menu && menu.hidden;
            closeOpenMenu();
            if (willOpen) {
                openMenuControl(control);
            }
            return;
        }

        var option = event.target.closest('.crm-inline-option');
        if (option) {
            event.preventDefault();
            event.stopPropagation();
            var dropdown = controlFromOption(option);
            if (!dropdown || !page.contains(dropdown) || dropdown.classList.contains('is-busy')) return;

            var leadId = dropdown.getAttribute('data-lead-id');
            var field = dropdown.getAttribute('data-field');
            var previous = dropdown.getAttribute('data-previous');
            var previousTone = dropdown.getAttribute('data-tone') || 'neutral';
            var previousIcon = dropdown.getAttribute('data-icon') || '';
            var previousLabel = dropdown.querySelector('.crm-inline-trigger__label')?.textContent || '';
            var value = option.getAttribute('data-value') || '';
            var tone = option.getAttribute('data-tone') || 'neutral';
            var icon = option.getAttribute('data-icon') || previousIcon;
            var label = option.getAttribute('data-label') || option.textContent.trim();
            var key = leadId + ':' + field;

            closeOpenMenu();
            if (String(value) === String(previous || '')) return;
            if (pending[key]) return;

            pending[key] = true;
            dropdown.classList.add('is-busy');
            applyControlVisual(dropdown, tone, icon, label);
            markSelected(dropdown, value);

            fetch(inlineUrl(leadId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ field: field, value: value === '' ? null : value }),
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                }).catch(function () {
                    return { ok: response.ok, data: {} };
                });
            }).then(function (result) {
                if (!result.ok) {
                    applyControlVisual(dropdown, previousTone, previousIcon, previousLabel);
                    markSelected(dropdown, previous || '');
                    showToast((result.data && (result.data.message || result.data.error)) || 'Update failed.', true);
                    return;
                }
                dropdown.setAttribute('data-previous', value);
                applyControlVisual(
                    dropdown,
                    (result.data && result.data.tone) || tone,
                    (result.data && result.data.icon) || icon,
                    (result.data && result.data.label) || label
                );
                markSelected(dropdown, value);
                if (field === 'lead_status') {
                    page.querySelectorAll('[data-lead-id="' + leadId + '"][data-current-status]').forEach(function (el) {
                        el.setAttribute('data-current-status', value);
                    });
                }
                if (field === 'priority') {
                    syncBoardCardPriority(leadId, value);
                }
                showToast((result.data && result.data.message) || 'Updated.', false);
            }).catch(function () {
                applyControlVisual(dropdown, previousTone, previousIcon, previousLabel);
                markSelected(dropdown, previous || '');
                showToast('Update failed. Please try again.', true);
            }).finally(function () {
                pending[key] = false;
                dropdown.classList.remove('is-busy');
            });
        }
    });

    document.addEventListener('click', function (event) {
        // Open menus are portalled to <body> so they are not clipped by the
        // responsive table. Move the menu back to its control and replay the
        // option click so the page-scoped save handler receives it.
        var portalledOption = event.target.closest('.crm-inline-menu--fixed .crm-inline-option');
        if (portalledOption) {
            var portalledMenu = portalledOption.closest('.crm-inline-menu');
            var owner = portalledMenu && portalledMenu.__crmOwner;
            if (owner && page.contains(owner)) {
                event.preventDefault();
                event.stopPropagation();
                restoreMenu(owner, portalledMenu);
                portalledOption.click();
                return;
            }
        }

        if (openMenu && !event.target.closest('.crm-inline-control') && !event.target.closest('.crm-inline-menu')) {
            closeOpenMenu();
        }
    });

    page.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        var openTarget = event.target.closest('[data-crm-lead-open], [data-crm-list-row]');
        if (!openTarget || event.target !== openTarget) return;
        event.preventDefault();
        openLeadPanel(openTarget.getAttribute('data-lead-id'));
    });

    // Legacy native select support (if any remain)
    page.addEventListener('change', function (event) {
        var select = event.target.closest('select[data-crm-inline]');
        if (!select || !page.contains(select)) return;

        var leadId = select.getAttribute('data-lead-id');
        var field = select.getAttribute('data-field');
        var previous = select.getAttribute('data-previous');
        var previousTone = select.getAttribute('data-tone') || 'neutral';
        var value = select.value;
        var key = leadId + ':' + field;

        if (pending[key]) {
            select.value = previous || '';
            return;
        }

        pending[key] = true;
        select.disabled = true;
        var opt = select.options[select.selectedIndex];
        if (opt && opt.getAttribute('data-tone')) {
            select.setAttribute('data-tone', opt.getAttribute('data-tone'));
        }

        fetch(inlineUrl(leadId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ field: field, value: value === '' ? null : value }),
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, data: data };
            }).catch(function () {
                return { ok: response.ok, data: {} };
            });
        }).then(function (result) {
            if (!result.ok) {
                select.value = previous || '';
                select.setAttribute('data-tone', previousTone);
                showToast((result.data && (result.data.message || result.data.error)) || 'Update failed.', true);
                return;
            }
            select.setAttribute('data-previous', value);
            select.setAttribute('data-tone', (result.data && result.data.tone) || previousTone);
            showToast((result.data && result.data.message) || 'Updated.', false);
        }).catch(function () {
            select.value = previous || '';
            select.setAttribute('data-tone', previousTone);
            showToast('Update failed. Please try again.', true);
        }).finally(function () {
            pending[key] = false;
            select.disabled = false;
        });
    });

    function refreshClearAllVisibility(container) {
        if (!container) return;
        var chips = container.querySelectorAll('[data-saved-filter-id]');
        var clearBtn = container.querySelector('[data-crm-clear-filters]');
        if (chips.length === 0) {
            container.remove();
            return;
        }
        if (clearBtn) {
            clearBtn.hidden = chips.length < 2;
        }
    }

    page.addEventListener('click', function (event) {
        var removeBtn = event.target.closest('[data-crm-remove-filter]');
        if (removeBtn && page.contains(removeBtn)) {
            event.preventDefault();
            event.stopPropagation();

            var url = removeBtn.getAttribute('data-url');
            var chip = removeBtn.closest('[data-saved-filter-id]');
            var container = page.querySelector('[data-saved-filters]');
            if (!url || !chip || removeBtn.disabled) return;

            removeBtn.disabled = true;
            var placeholder = document.createComment('saved-filter');
            chip.parentNode.insertBefore(placeholder, chip);
            chip.remove();
            refreshClearAllVisibility(container);

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                }).catch(function () {
                    return { ok: response.ok, data: {} };
                });
            }).then(function (result) {
                if (!result.ok) {
                    placeholder.parentNode.insertBefore(chip, placeholder);
                    placeholder.remove();
                    removeBtn.disabled = false;
                    refreshClearAllVisibility(page.querySelector('[data-saved-filters]'));
                    showToast((result.data && result.data.message) || 'Could not remove filter.', true);
                    return;
                }
                placeholder.remove();
                showToast((result.data && result.data.message) || 'Saved filter removed.', false);
            }).catch(function () {
                placeholder.parentNode.insertBefore(chip, placeholder);
                placeholder.remove();
                removeBtn.disabled = false;
                refreshClearAllVisibility(page.querySelector('[data-saved-filters]'));
                showToast('Could not remove filter.', true);
            });
            return;
        }

        var clearBtn = event.target.closest('[data-crm-clear-filters]');
        if (clearBtn && page.contains(clearBtn)) {
            event.preventDefault();
            event.stopPropagation();
            var clearUrl = page.getAttribute('data-filter-clear-url');
            var filtersContainer = page.querySelector('[data-saved-filters]');
            if (!clearUrl || !filtersContainer || clearBtn.disabled) return;

            clearBtn.disabled = true;

            fetch(clearUrl, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                }).catch(function () {
                    return { ok: response.ok, data: {} };
                });
            }).then(function (result) {
                if (!result.ok) {
                    clearBtn.disabled = false;
                    showToast((result.data && result.data.message) || 'Could not clear filters.', true);
                    return;
                }
                filtersContainer.remove();
                showToast((result.data && result.data.message) || 'All saved filters cleared.', false);
            }).catch(function () {
                clearBtn.disabled = false;
                showToast('Could not clear filters.', true);
            });
        }
    });

    var canBulk = page.getAttribute('data-can-bulk') === '1';
    var bulkUrl = page.getAttribute('data-bulk-url') || '';
    var bulkBar = page.querySelector('[data-crm-bulk-bar]');
    var selectAll = page.querySelector('[data-crm-select-all]');
    var bulkCount = bulkBar ? bulkBar.querySelector('[data-crm-bulk-count]') : null;

    function selectedLeadIds() {
        return Array.prototype.map.call(
            page.querySelectorAll('[data-crm-lead-select]:checked'),
            function (cb) { return parseInt(cb.value, 10); }
        ).filter(function (id) { return !isNaN(id); });
    }

    function bulkSelectForField(field) {
        if (!bulkBar) return null;
        if (field === 'lead_status') return bulkBar.querySelector('[data-crm-bulk-status]');
        if (field === 'priority') return bulkBar.querySelector('[data-crm-bulk-priority]');
        if (field === 'assigned_to') return bulkBar.querySelector('[data-crm-bulk-assignee]');
        return null;
    }

    function syncBulkUi() {
        if (!bulkBar) return;

        var ids = selectedLeadIds();
        var count = ids.length;
        bulkBar.hidden = count === 0;
        if (bulkCount) bulkCount.textContent = String(count);

        page.querySelectorAll('[data-crm-list-row]').forEach(function (row) {
            var cb = row.querySelector('[data-crm-lead-select]');
            row.classList.toggle('is-selected', !!(cb && cb.checked));
        });

        if (selectAll) {
            var all = page.querySelectorAll('[data-crm-lead-select]');
            var checked = page.querySelectorAll('[data-crm-lead-select]:checked');
            selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
            selectAll.checked = all.length > 0 && checked.length === all.length;
        }

        bulkBar.querySelectorAll('[data-crm-bulk-apply]').forEach(function (btn) {
            var field = btn.getAttribute('data-crm-bulk-apply');
            var select = bulkSelectForField(field);
            btn.disabled = count === 0 || !select || !select.value;
        });
    }

    function applyBulkControlVisual(leadId, field, data) {
        page.querySelectorAll('[data-crm-inline][data-lead-id="' + leadId + '"][data-field="' + field + '"]').forEach(function (control) {
            control.setAttribute('data-previous', data.value == null ? '' : String(data.value));
            applyControlVisual(control, data.tone, data.icon, data.label);
            markSelected(control, data.value == null ? '' : String(data.value));
        });

        if (field === 'lead_status' && data.value) {
            page.querySelectorAll('[data-lead-id="' + leadId + '"][data-current-status]').forEach(function (el) {
                el.setAttribute('data-current-status', data.value);
            });
        }
    }

    if (canBulk && bulkBar) {
        page.addEventListener('change', function (event) {
            if (event.target.matches('[data-crm-lead-select], [data-crm-select-all]')) {
                if (event.target.matches('[data-crm-select-all]')) {
                    var checked = event.target.checked;
                    page.querySelectorAll('[data-crm-lead-select]').forEach(function (cb) {
                        cb.checked = checked;
                    });
                }
                syncBulkUi();
                return;
            }

            if (event.target.matches('[data-crm-bulk-status], [data-crm-bulk-priority], [data-crm-bulk-assignee]')) {
                syncBulkUi();
            }
        });

        page.addEventListener('click', function (event) {
            var clearBtn = event.target.closest('[data-crm-bulk-clear]');
            if (clearBtn && page.contains(clearBtn)) {
                event.preventDefault();
                page.querySelectorAll('[data-crm-lead-select]').forEach(function (cb) {
                    cb.checked = false;
                });
                if (selectAll) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }
                bulkBar.querySelectorAll('select').forEach(function (select) {
                    select.value = '';
                });
                syncBulkUi();
                return;
            }

            var applyBtn = event.target.closest('[data-crm-bulk-apply]');
            if (!applyBtn || !page.contains(applyBtn) || applyBtn.disabled) return;

            event.preventDefault();
            event.stopPropagation();

            var field = applyBtn.getAttribute('data-crm-bulk-apply');
            var select = bulkSelectForField(field);
            if (!select || !select.value) return;

            var value = select.value;
            if (field === 'assigned_to' && value === '__unassigned__') {
                value = '';
            }

            var ids = selectedLeadIds();
            if (!ids.length || !bulkUrl) return;

            applyBtn.disabled = true;

            fetch(bulkUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    lead_ids: ids,
                    field: field,
                    value: value === '' ? null : value
                }),
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                }).catch(function () {
                    return { ok: response.ok, data: {} };
                });
            }).then(function (result) {
                if (!result.ok) {
                    showToast((result.data && result.data.message) || 'Bulk update failed.', true);
                    return;
                }

                (result.data.results || []).forEach(function (item) {
                    applyBulkControlVisual(item.id, field, item);
                });

                page.querySelectorAll('[data-crm-lead-select]:checked').forEach(function (cb) {
                    var row = cb.closest('[data-crm-list-row]');
                    cb.checked = false;
                    if (row) row.classList.add('is-status-updated');
                });

                if (selectAll) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }

                select.value = '';
                showToast((result.data && result.data.message) || 'Bulk update complete.', false);
                syncBulkUi();

                window.setTimeout(function () {
                    page.querySelectorAll('.crm-list-row.is-status-updated').forEach(function (row) {
                        row.classList.remove('is-status-updated');
                    });
                }, 900);
            }).catch(function () {
                showToast('Bulk update failed. Please try again.', true);
            }).finally(function () {
                syncBulkUi();
            });
        });

        syncBulkUi();
    }

    page.addEventListener('submit', function (event) {
        var editForm = event.target.closest('[data-crm-panel-edit-form]');
        if (!editForm || !page.contains(editForm)) return;
        event.preventDefault();
        submitPanelEdit(editForm);
    });
})();
