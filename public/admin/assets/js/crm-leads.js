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

    initSmartSearch();
    initModalMaximize();

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

    function submissionPanelUrl(entryId) {
        var template = page.getAttribute('data-submission-panel-url-template') || '';
        return template.replace('__ID__', String(entryId));
    }

    function convertSubmissionUrl(entryId) {
        var template = page.getAttribute('data-convert-submission-url-template') || '';
        return template.replace('__ID__', String(entryId));
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

    function syncModalHeader(title, subtitle) {
        var modalTitle = document.getElementById('crmLeadDetailModalLabel');
        var modalSubtitle = document.querySelector('[data-crm-panel-subtitle]');
        if (modalTitle && title) modalTitle.textContent = title;
        if (modalSubtitle) modalSubtitle.textContent = subtitle || '';
    }

    function syncPanelHeaderFromHost(host) {
        var titleEl = host.querySelector('[data-crm-panel-title]');
        var email = host.querySelector('.crm-lead-ticket__contact-chip, .crm-lead-panel__contact-chip');
        var isSubmission = host.querySelector('[data-crm-submission-panel]');
        syncModalHeader(
            titleEl ? titleEl.textContent.trim() : (isSubmission ? 'Form submission' : 'Lead preview'),
            isSubmission
                ? 'Review intake details and add to your pipeline'
                : (email ? email.textContent.trim() : 'Review and update without leaving the workspace')
        );
    }

    function leadDetailModal() {
        return document.getElementById('crmLeadDetailModal');
    }

    function showLeadModal() {
        var modalEl = leadDetailModal();
        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function serializeCommentInput(inputEl) {
        if (!inputEl) return '';

        var parts = [];
        function walk(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                parts.push(node.textContent);
                return;
            }
            if (node.nodeType !== Node.ELEMENT_NODE) return;

            if (node.classList && node.classList.contains('crm-mention-chip')) {
                parts.push('@' + (node.getAttribute('data-mention-name') || node.textContent.replace(/^@/, '').trim()));
                return;
            }
            if (node.tagName === 'BR') {
                parts.push('\n');
                return;
            }
            if (node.tagName === 'DIV' || node.tagName === 'P') {
                if (parts.length && !/\n$/.test(parts[parts.length - 1])) {
                    parts.push('\n');
                }
                Array.prototype.forEach.call(node.childNodes, walk);
                return;
            }
            Array.prototype.forEach.call(node.childNodes, walk);
        }

        Array.prototype.forEach.call(inputEl.childNodes, walk);
        return parts.join('').replace(/\u00a0/g, ' ').trim();
    }

    function insertMentionChip(inputEl, admin) {
        if (!inputEl || !admin) return;
        var chip = document.createElement('span');
        chip.className = 'crm-mention-chip';
        chip.contentEditable = 'false';
        chip.setAttribute('data-mention-id', String(admin.id));
        chip.setAttribute('data-mention-name', admin.name);
        chip.textContent = '@' + admin.name;

        var selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) {
            inputEl.appendChild(chip);
            inputEl.appendChild(document.createTextNode('\u00a0'));
            return;
        }

        var range = selection.getRangeAt(0);
        range.deleteContents();
        range.insertNode(document.createTextNode('\u00a0'));
        range.insertNode(chip);
        range.setStartAfter(chip);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
    }

    function initCommentEditor(root) {
        var form = root.querySelector('[data-crm-comment-form]');
        if (!form) return;

        var input = form.querySelector('[data-crm-comment-input]');
        var hidden = form.querySelector('[data-crm-comment-hidden]');
        var menu = form.querySelector('[data-crm-mention-menu]');
        var submitBtn = form.querySelector('[data-crm-comment-submit]');
        var admins = [];
        try {
            admins = JSON.parse(form.getAttribute('data-mention-admins') || '[]');
        } catch (e) {
            admins = [];
        }

        if (!input) return;

        form.querySelectorAll('[data-crm-comment-cmd]').forEach(function (btn) {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                var cmd = btn.getAttribute('data-crm-comment-cmd');
                input.focus();
                if (cmd === 'mention') {
                    openMentionMenu(input, menu, admins, '');
                    return;
                }
                document.execCommand(cmd, false, null);
            });
        });

        input.addEventListener('keydown', function (event) {
            if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                event.preventDefault();
                form.requestSubmit();
            }
        });

        input.addEventListener('input', function () {
            if (!menu || menu.hidden) return;
            var query = currentMentionQuery(input);
            if (query === null) {
                menu.hidden = true;
                return;
            }
            renderMentionMenu(input, menu, admins, query);
        });

        if (menu) {
            menu.addEventListener('click', function (event) {
                var option = event.target.closest('[data-mention-id]');
                if (!option) return;
                event.preventDefault();
                var admin = admins.find(function (item) {
                    return String(item.id) === option.getAttribute('data-mention-id');
                });
                if (admin) {
                    replaceMentionQuery(input, admin);
                }
                menu.hidden = true;
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            event.stopPropagation();

            var text = serializeCommentInput(input);
            if (!text) {
                showToast('Write a comment before posting.', true);
                return;
            }

            if (hidden) hidden.value = text;
            if (submitBtn) submitBtn.disabled = true;

            var formData = new FormData(form);
            formData.set('note', text);

            fetch(form.action, {
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
                    return { ok: response.ok, data: data };
                }).catch(function () {
                    return { ok: response.ok, data: {} };
                });
            }).then(function (result) {
                if (!result.ok) {
                    showToast((result.data && result.data.message) || 'Could not add comment.', true);
                    return;
                }

                var list = root.querySelector('[data-crm-comments-list]');
                var empty = list ? list.querySelector('.crm-lead-ticket__empty') : null;
                if (empty) empty.remove();

                if (list && result.data && result.data.comment_html) {
                    list.insertAdjacentHTML('beforeend', result.data.comment_html);
                }

                var meta = root.querySelector('.crm-lead-ticket__block-meta');
                if (meta && result.data && typeof result.data.comment_count === 'number') {
                    meta.textContent = result.data.comment_count + ' total';
                }

                input.innerHTML = '';
                if (hidden) hidden.value = '';
                showToast((result.data && result.data.message) || 'Comment added.', false);
            }).catch(function () {
                showToast('Could not add comment. Please try again.', true);
            }).finally(function () {
                if (submitBtn) submitBtn.disabled = false;
            });
        });

        function currentMentionQuery(inputEl) {
            var selection = window.getSelection();
            if (!selection || !selection.anchorNode) return null;
            if (!inputEl.contains(selection.anchorNode)) return null;

            var textBefore = '';
            var node = selection.anchorNode;
            if (node.nodeType === Node.TEXT_NODE) {
                textBefore = node.textContent.slice(0, selection.anchorOffset);
            }
            var match = textBefore.match(/@([\w\s.-]{0,40})$/);
            return match ? match[1].trim().toLowerCase() : null;
        }

        function openMentionMenu(inputEl, menuEl, adminList, query) {
            if (!menuEl) return;
            renderMentionMenu(inputEl, menuEl, adminList, query);
        }

        function renderMentionMenu(inputEl, menuEl, adminList, query) {
            var filtered = adminList.filter(function (admin) {
                return !query || admin.name.toLowerCase().indexOf(query) !== -1;
            }).slice(0, 6);

            if (!filtered.length) {
                menuEl.hidden = true;
                return;
            }

            menuEl.innerHTML = filtered.map(function (admin) {
                return '<button type="button" class="crm-mention-option" data-mention-id="' + admin.id + '">' +
                    '<span class="crm-mention-option__avatar">' + (admin.initials || '?') + '</span>' +
                    '<span class="crm-mention-option__name">' + admin.name + '</span>' +
                '</button>';
            }).join('');
            menuEl.hidden = false;
        }

        function replaceMentionQuery(inputEl, admin) {
            var selection = window.getSelection();
            if (!selection || !selection.anchorNode) {
                insertMentionChip(inputEl, admin);
                return;
            }

            var node = selection.anchorNode;
            if (node.nodeType !== Node.TEXT_NODE || !node.textContent) {
                insertMentionChip(inputEl, admin);
                return;
            }

            var offset = selection.anchorOffset;
            var before = node.textContent.slice(0, offset);
            var after = node.textContent.slice(offset);
            var atIndex = before.lastIndexOf('@');
            if (atIndex === -1) {
                insertMentionChip(inputEl, admin);
                return;
            }

            node.textContent = before.slice(0, atIndex) + after;
            var range = document.createRange();
            range.setStart(node, atIndex);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            insertMentionChip(inputEl, admin);
        }
    }

    function initShowMore(root) {
        root.querySelectorAll('[data-crm-show-more]').forEach(function (button) {
            button.addEventListener('click', function () {
                var kind = button.getAttribute('data-crm-show-more');
                var hidden = root.querySelector('[data-crm-' + kind + '-hidden]');
                if (hidden) hidden.hidden = false;
                button.remove();
            });
        });
    }

    function initLeadPanel(host) {
        if (!host) return;
        initCommentEditor(host);
        initShowMore(host);
    }

    function initModalMaximize() {
        var modalEl = leadDetailModal();
        if (!modalEl) return;

        var btn = modalEl.querySelector('[data-crm-modal-maximize]');
        var dialog = modalEl.querySelector('.crm-lead-modal__dialog');
        if (!btn || !dialog) return;

        btn.addEventListener('click', function () {
            dialog.classList.toggle('crm-lead-modal__dialog--maximized');
            var maximized = dialog.classList.contains('crm-lead-modal__dialog--maximized');
            var icon = btn.querySelector('[data-crm-maximize-icon]');
            if (icon) {
                icon.setAttribute('icon', maximized ? 'solar:minimize-square-linear' : 'solar:maximize-square-linear');
            }
            btn.setAttribute('aria-label', maximized ? 'Restore' : 'Maximize');
            btn.title = maximized ? 'Restore' : 'Maximize';
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            dialog.classList.remove('crm-lead-modal__dialog--maximized');
            var icon = btn.querySelector('[data-crm-maximize-icon]');
            if (icon) icon.setAttribute('icon', 'solar:maximize-square-linear');
            btn.setAttribute('aria-label', 'Maximize');
            btn.title = 'Maximize';
        });
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
        if (!host || !leadId) return Promise.resolve();

        host.innerHTML = panelLoadingMarkup();
        if (options && options.showModal) {
            showLeadModal();
        }

        return fetchPanelHtml(panelUrl(leadId)).then(function (html) {
            host.innerHTML = html;
            syncPanelHeaderFromHost(host);
            initLeadPanel(host);
        }).catch(function () {
            host.innerHTML = '<div class="crm-lead-panel-loading"><span>Could not load lead details. Please try again.</span></div>';
            showToast('Could not load lead details.', true);
        });
    }

    function loadPanelEdit(leadId) {
        var host = panelHost();
        if (!host || !leadId) return;

        showLeadModal();
        host.innerHTML = panelLoadingMarkup();
        syncModalHeader('Edit lead', 'Update details without leaving the workspace');

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
        if (!leadId || !window.bootstrap || !leadDetailModal()) return;
        loadPanelView(leadId, { showModal: true });
    }

    function loadSubmissionPanel(entryId, options) {
        var host = panelHost();
        if (!host || !entryId) return Promise.resolve();

        host.innerHTML = '<div class="crm-lead-panel-loading"><div class="crm-lead-panel-loading__spinner"></div><span>Loading submission…</span></div>';
        syncModalHeader('Form submission', 'Review intake details and add to your pipeline');

        if (options && options.showModal) {
            showLeadModal();
        }

        return fetch(submissionPanelUrl(entryId), {
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (response) {
            if (!response.ok) throw new Error('Failed to load submission panel');
            return response.text();
        }).then(function (html) {
            host.innerHTML = html;
            syncPanelHeaderFromHost(host);
        }).catch(function () {
            host.innerHTML = '<div class="crm-lead-panel-loading"><span>Could not load submission details. Please try again.</span></div>';
            showToast('Could not load submission details.', true);
        });
    }

    function openSubmissionPanel(entryId) {
        if (!entryId || !window.bootstrap || !leadDetailModal()) return;
        loadSubmissionPanel(entryId, { showModal: true });
    }

    function submitConvertSubmission(form) {
        var entryId = form.closest('[data-form-entry-id]')?.getAttribute('data-form-entry-id')
            || form.closest('[data-crm-submission-panel]')?.getAttribute('data-form-entry-id')
            || form.closest('[data-form-entry-id]')?.getAttribute('data-form-entry-id');
        var submitBtn = form.querySelector('[type="submit"]');
        var formData = new FormData(form);

        if (submitBtn) submitBtn.disabled = true;

        fetch(form.getAttribute('action') || convertSubmissionUrl(entryId), {
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
                return { ok: response.ok, data: data };
            }).catch(function () {
                return { ok: response.ok, data: {} };
            });
        }).then(function (result) {
            if (!result.ok) {
                showToast((result.data && result.data.message) || 'Could not add submission to pipeline.', true);
                return;
            }
            showToast((result.data && result.data.message) || 'Added to pipeline.', false);
            window.location.reload();
        }).catch(function () {
            showToast('Could not add submission to pipeline.', true);
        }).finally(function () {
            if (submitBtn) submitBtn.disabled = false;
        });
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

        var openTarget = event.target.closest('[data-crm-submission-open]');
        if (openTarget && page.contains(openTarget) && Date.now() >= suppressOpenUntil && !isInteractiveTarget(event.target)) {
            var entryId = openTarget.getAttribute('data-form-entry-id');
            if (entryId) {
                event.preventDefault();
                openSubmissionPanel(entryId);
            }
            return;
        }

        var convertForm = event.target.closest('[data-crm-convert-submission]');
        if (convertForm && page.contains(convertForm)) {
            event.preventDefault();
            event.stopPropagation();
            submitConvertSubmission(convertForm);
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
                // Keep the board and list representations of the same lead in sync.
                page.querySelectorAll('[data-crm-inline][data-lead-id="' + leadId + '"][data-field="' + field + '"]').forEach(function (peer) {
                    peer.setAttribute('data-previous', value);
                    applyControlVisual(peer, result.data.tone || tone, result.data.icon || icon, result.data.label || label);
                    markSelected(peer, value);
                });
                if (field === 'lead_status') {
                    page.querySelectorAll('[data-lead-id="' + leadId + '"][data-current-status]').forEach(function (el) {
                        el.setAttribute('data-current-status', value);
                    });
                    var targetBody = page.querySelector('[data-crm-dropzone][data-status="' + value + '"] .crm-board-column__body');
                    var card = page.querySelector('[data-crm-board-card][data-lead-id="' + leadId + '"]');
                    if (targetBody && card) {
                        targetBody.appendChild(card);
                        refreshBoardColumnCounts();
                    }
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
        var openTarget = event.target.closest('[data-crm-submission-open], [data-crm-lead-open], [data-crm-list-row]');
        if (!openTarget || event.target !== openTarget) return;
        event.preventDefault();
        var entryId = openTarget.getAttribute('data-form-entry-id');
        if (entryId) {
            openSubmissionPanel(entryId);
            return;
        }
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

    function initSmartSearch() {
        var root = page.querySelector('[data-crm-smart-search]');
        if (!root) return;

        var input = root.querySelector('[data-crm-smart-search-input]');
        var panel = root.querySelector('[data-crm-smart-search-panel]');
        var goBtn = root.querySelector('[data-crm-smart-search-go]');
        var labelBox = root.querySelector('[data-crm-smart-search-label]');
        var labelText = root.querySelector('[data-crm-smart-search-label-text]');
        var endpoint = page.getAttribute('data-smart-search-url');
        var debounceTimer = null;
        var latestUrl = null;

        if (!input || !panel || !endpoint) return;

        function currentView() {
            try {
                return new URL(window.location.href).searchParams.get('view') === 'list' ? 'list' : 'board';
            } catch (e) {
                return 'board';
            }
        }

        function setPanelOpen(open) {
            panel.hidden = !open;
            input.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function showInterpretation(text, visible) {
            if (!labelBox || !labelText) return;
            if (!visible || !text) {
                labelBox.hidden = true;
                labelText.textContent = '';
                return;
            }
            labelBox.hidden = false;
            labelText.textContent = text;
        }

        function fetchInterpretation() {
            var query = input.value.trim();
            if (!query) {
                latestUrl = null;
                showInterpretation('', false);
                return;
            }

            var url = endpoint + '?q=' + encodeURIComponent(query) + '&view=' + encodeURIComponent(currentView());
            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                latestUrl = data.url || null;
                if (data.label && data.confidence !== 'low') {
                    showInterpretation('Will show: ' + data.label, true);
                } else if (data.label) {
                    showInterpretation('Search for: ' + data.label, true);
                } else {
                    showInterpretation('', false);
                }
            }).catch(function () {
                latestUrl = null;
                showInterpretation('', false);
            });
        }

        function applySmartSearch(queryOverride) {
            var query = (queryOverride !== undefined ? queryOverride : input.value).trim();
            if (!query) return;

            var url = endpoint + '?q=' + encodeURIComponent(query) + '&view=' + encodeURIComponent(currentView());
            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data.url) {
                    window.location.href = data.url;
                }
            }).catch(function () {
                showToast('Smart search is unavailable right now.', true);
            });
        }

        input.addEventListener('focus', function () {
            setPanelOpen(true);
        });

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchInterpretation, 220);
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                if (latestUrl) {
                    window.location.href = latestUrl;
                } else {
                    applySmartSearch();
                }
            } else if (event.key === 'Escape') {
                setPanelOpen(false);
                input.blur();
            }
        });

        if (goBtn) {
            goBtn.addEventListener('click', function () {
                if (latestUrl) {
                    window.location.href = latestUrl;
                } else {
                    applySmartSearch();
                }
            });
        }

        root.querySelectorAll('[data-crm-smart-suggestion]').forEach(function (button) {
            button.addEventListener('click', function () {
                var query = button.getAttribute('data-query') || '';
                input.value = query;
                applySmartSearch(query);
            });
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                setPanelOpen(false);
            }
        });
    }
})();
