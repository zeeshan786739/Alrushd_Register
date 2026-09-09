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

        if (normalized === 'board') {
            initBoardInfiniteScroll();
        }
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

    page.querySelectorAll('[data-crm-filter-auto-submit]').forEach(function (select) {
        select.addEventListener('change', function () {
            var form = select.closest('form');
            if (form) {
                form.submit();
            }
        });
    });

    initSmartSearch();
    initModalMaximize();
    initLeadRemove();
    bootLeadModalFromQuery();

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
        return !!target.closest('a, button, input, select, textarea, label, form, .fc-table-actions, [data-crm-inline], .crm-inline-menu, .crm-inline-option, .crm-list-row__select, .crm-list-select, .crm-list-action, [data-crm-lead-open-trigger], .crm-list-status-drop, [data-crm-status-filter], [data-crm-filter-tag], [data-crm-filter-bulk-bar], .crm-lead-panel__tool, .crm-lead-panel__contact-chip, .crm-lead-panel__note-form, .crm-lead-drawer__close, .crm-list-bulk-bar, [data-crm-bulk-clear], [data-crm-bulk-apply], [data-crm-panel-edit], [data-crm-panel-cancel], [data-crm-panel-edit-form], [data-crm-panel-save], .crm-board-card__quick-action');
    }

    function setDragActive(active) {
        page.classList.toggle('is-dragging-lead', active);
        var rail = page.querySelector('[data-crm-list-status-rail]');
        if (rail) {
            rail.classList.toggle('is-active', active);
        }
    }

    function clearDropzoneHighlights() {
        page.querySelectorAll('[data-crm-dropzone].is-drag-over, [data-crm-list-dropzone].is-drag-over').forEach(function (dropzone) {
            dropzone.classList.remove('is-drag-over');
        });
    }

    function statusDropZone(fromTarget) {
        return fromTarget.closest('[data-crm-dropzone], [data-crm-list-dropzone], .crm-list-status-drop');
    }

    function boardCardFromTarget(fromTarget) {
        return fromTarget.closest('[data-crm-board-card]');
    }

    function boardColumnFromTarget(fromTarget) {
        return fromTarget.closest('[data-crm-dropzone]');
    }

    function clearBoardReorderHighlights() {
        page.querySelectorAll('[data-crm-board-card].is-reorder-over').forEach(function (card) {
            card.classList.remove('is-reorder-over');
        });
    }

    function persistBoardColumnOrder(column) {
        var reorderBoardUrl = page.getAttribute('data-reorder-board-url');
        if (!reorderBoardUrl || !column || !canUpdateLeads) {
            return Promise.resolve({ ok: true });
        }

        var status = column.getAttribute('data-status');
        var leadIds = Array.from(column.querySelectorAll('[data-crm-board-card][data-lead-id]'))
            .map(function (card) { return parseInt(card.getAttribute('data-lead-id'), 10); })
            .filter(function (id) { return !!id; });

        if (!status || !leadIds.length) {
            return Promise.resolve({ ok: true });
        }

        return fetch(reorderBoardUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                lead_status: status,
                lead_ids: leadIds
            }),
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, data: data };
            }).catch(function () {
                return { ok: response.ok, data: {} };
            });
        });
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

    function createPanelUrl() {
        return page.getAttribute('data-create-panel-url') || '';
    }

    function storeUrl() {
        return page.getAttribute('data-store-url') || '';
    }

    function updateUrl(leadId) {
        var template = page.getAttribute('data-update-url-template') || '';
        return template.replace('__ID__', String(leadId));
    }

    function panelHost() {
        return document.querySelector('[data-crm-lead-panel-host]');
    }

    function syncModalHeader(title, subtitle) {
        var modalTitle = document.getElementById('crmLeadDetailModalLabel')
            || document.getElementById('crmLeadDetailDrawerLabel');
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

    function leadDetailShell() {
        return document.getElementById('crmLeadDetailModal')
            || document.getElementById('crmLeadDetailDrawer');
    }

    function leadDetailShellUsesOffcanvas(shell) {
        shell = shell || leadDetailShell();
        return !!(shell && (shell.id === 'crmLeadDetailDrawer' || shell.classList.contains('offcanvas')));
    }

    function leadDetailModal() {
        return leadDetailShell();
    }

    function showLeadModal() {
        var shell = leadDetailShell();
        if (!shell || !window.bootstrap) return;

        if (leadDetailShellUsesOffcanvas(shell)) {
            window.bootstrap.Offcanvas.getOrCreateInstance(shell).show();
        } else {
            window.bootstrap.Modal.getOrCreateInstance(shell).show();
        }
    }

    function hideLeadPanelShell() {
        var shell = leadDetailShell();
        if (!shell || !window.bootstrap || !shell.classList.contains('show')) return;

        if (leadDetailShellUsesOffcanvas(shell)) {
            var offcanvas = window.bootstrap.Offcanvas.getInstance(shell);
            if (offcanvas) offcanvas.hide();
        } else {
            window.bootstrap.Modal.getOrCreateInstance(shell).hide();
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

                var meta = root.querySelector('[data-crm-comments-meta]');
                if (meta && result.data && typeof result.data.comment_count === 'number') {
                    meta.textContent = result.data.comment_count + ' total';
                }

                applyPanelActivityUpdate(root.getAttribute('data-lead-id'), result.data);

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

    var VISIBLE_ACTIVITY_LIMIT = 3;

    function bindShowMoreButton(button, root) {
        if (!button || button.getAttribute('data-bound') === '1') return;
        button.setAttribute('data-bound', '1');
        button.addEventListener('click', function () {
            var kind = button.getAttribute('data-crm-show-more');
            var hidden = root.querySelector('[data-crm-' + kind + '-hidden]');
            if (hidden) hidden.hidden = false;
            button.remove();
        });
    }

    function updateActivityShowMoreButton(root, hiddenWrap) {
        if (!root || !hiddenWrap) return;
        var hiddenCount = hiddenWrap.querySelectorAll('.crm-lead-ticket__timeline-item').length;
        var btn = root.querySelector('[data-crm-show-more="activity"]');
        if (hiddenCount <= 0) {
            if (btn) btn.remove();
            return;
        }
        var label = 'Show ' + hiddenCount + ' more activit' + (hiddenCount === 1 ? 'y' : 'ies');
        if (btn) {
            btn.textContent = label;
            return;
        }
        btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'crm-lead-ticket__show-more';
        btn.setAttribute('data-crm-show-more', 'activity');
        btn.textContent = label;
        var timeline = root.querySelector('[data-crm-activity-list]');
        if (timeline && timeline.parentNode) {
            timeline.parentNode.appendChild(btn);
            bindShowMoreButton(btn, root);
        }
    }

    function prependPanelActivity(root, html, count) {
        if (!root || !html) return;

        var list = root.querySelector('[data-crm-activity-list]');
        if (!list) return;

        var empty = list.querySelector('.crm-lead-ticket__empty');
        if (empty) empty.remove();

        var hiddenWrap = list.querySelector('[data-crm-activity-hidden]');
        var temp = document.createElement('div');
        temp.innerHTML = html.trim();
        var item = temp.firstElementChild;
        if (!item) return;

        if (hiddenWrap) {
            list.insertBefore(item, hiddenWrap);
        } else {
            list.insertBefore(item, list.firstChild);
        }

        var visibleItems = [];
        Array.prototype.forEach.call(list.children, function (node) {
            if (!node.matches || !node.matches('.crm-lead-ticket__timeline-item')) return;
            visibleItems.push(node);
        });

        while (visibleItems.length > VISIBLE_ACTIVITY_LIMIT) {
            var overflow = visibleItems.pop();
            if (!hiddenWrap) {
                hiddenWrap = document.createElement('div');
                hiddenWrap.className = 'crm-lead-ticket__timeline-hidden';
                hiddenWrap.setAttribute('data-crm-activity-hidden', '');
                hiddenWrap.hidden = true;
                list.appendChild(hiddenWrap);
            }
            hiddenWrap.insertBefore(overflow, hiddenWrap.firstChild);
        }

        updateActivityShowMoreButton(root, hiddenWrap);

        var meta = root.querySelector('[data-crm-activity-meta]');
        if (meta && typeof count === 'number') {
            meta.textContent = count + ' event' + (count === 1 ? '' : 's');
        }

        var details = root.querySelector('.crm-lead-ticket__block--collapsible');
        if (details) details.open = true;
    }

    function applyPanelActivityUpdate(leadId, data) {
        if (!data || !data.activity_html) return;
        var panelRoot = page.querySelector('[data-crm-lead-panel][data-lead-id="' + leadId + '"]');
        if (!panelRoot) return;
        prependPanelActivity(panelRoot, data.activity_html, data.activity_count);
    }

    function initShowMore(root) {
        root.querySelectorAll('[data-crm-show-more]').forEach(function (button) {
            bindShowMoreButton(button, root);
        });
    }

    function initLeadPanel(host) {
        if (!host) return;
        initCommentEditor(host);
        initShowMore(host);
        initFormTagSelects(host);
        initLeadNameEdit(host);
    }

    function updateLeadName(leadId, fullName) {
        return fetch(inlineUrl(leadId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ field: 'full_name', value: fullName }),
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data };
            }).catch(function () {
                return { ok: response.ok, status: response.status, data: {} };
            });
        });
    }

    function initLeadNameEdit(root) {
        root.querySelectorAll('[data-crm-lead-rename]').forEach(function (titleEl) {
            if (titleEl.getAttribute('data-bound') === '1') return;
            titleEl.setAttribute('data-bound', '1');

            function openEditor() {
                if (titleEl.classList.contains('is-editing') || titleEl.classList.contains('is-busy')) return;

                var panel = titleEl.closest('[data-crm-lead-panel]');
                var leadId = panel ? panel.getAttribute('data-lead-id') : null;
                if (!leadId) return;

                var original = titleEl.textContent.trim();
                var input = document.createElement('input');
                input.type = 'text';
                input.className = 'crm-lead-ticket__title-input';
                input.value = original;
                input.setAttribute('aria-label', 'Lead name');

                titleEl.classList.add('is-editing');
                titleEl.textContent = '';
                titleEl.appendChild(input);
                input.focus();
                input.select();

                var committed = false;

                function closeEditor(revert) {
                    if (committed) return;
                    committed = true;
                    titleEl.classList.remove('is-editing');
                    titleEl.textContent = revert ? original : (input.value.trim() || original);
                }

                function commit() {
                    if (committed) return;
                    var next = input.value.trim();
                    if (next === '' || next === original) {
                        closeEditor(true);
                        return;
                    }

                    committed = true;
                    titleEl.classList.remove('is-editing');
                    titleEl.classList.add('is-busy');
                    titleEl.textContent = next;

                    updateLeadName(leadId, next).then(function (result) {
                        titleEl.classList.remove('is-busy');
                        if (!result.ok) {
                            titleEl.textContent = original;
                            showToast((result.data && result.data.message) || 'Could not rename lead.', true);
                            return;
                        }

                        var lead = (result.data && result.data.lead) || { id: leadId, full_name: result.data.value || next };
                        titleEl.textContent = lead.full_name || next;
                        syncLeadDisplay(lead);
                        syncModalHeader(lead.full_name || next, 'Review and update without leaving the workspace');
                        applyPanelActivityUpdate(leadId, result.data);
                        showToast((result.data && result.data.message) || 'Lead renamed.', false);
                    }).catch(function () {
                        titleEl.classList.remove('is-busy');
                        titleEl.textContent = original;
                        showToast('Could not rename lead. Please try again.', true);
                    });
                }

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        commit();
                    } else if (event.key === 'Escape') {
                        event.preventDefault();
                        closeEditor(true);
                    }
                });

                input.addEventListener('blur', function () {
                    window.setTimeout(commit, 0);
                });
            }

            titleEl.addEventListener('click', openEditor);
            titleEl.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openEditor();
                }
            });
        });
    }

    function syncFormTagSelect(wrapper) {
        var select = wrapper.querySelector('.crm-form-tag-select__native');
        var face = wrapper.querySelector('.crm-form-tag-select__face');
        if (!select || !face) return;

        var option = select.options[select.selectedIndex];
        var tone = option ? (option.getAttribute('data-tone') || 'neutral') : 'neutral';
        var icon = option ? (option.getAttribute('data-icon') || 'solar:menu-dots-linear') : 'solar:menu-dots-linear';
        var label = option ? (option.getAttribute('data-label') || option.textContent.trim()) : '';

        wrapper.setAttribute('data-tone', tone);
        var iconEl = face.querySelector('.crm-form-tag-select__icon');
        var labelEl = face.querySelector('.crm-form-tag-select__label');
        if (iconEl) iconEl.setAttribute('icon', icon);
        if (labelEl) labelEl.textContent = label;
    }

    function initFormTagSelects(root) {
        root.querySelectorAll('[data-crm-form-tag-select]').forEach(function (wrapper) {
            if (wrapper.getAttribute('data-bound') === '1') return;
            wrapper.setAttribute('data-bound', '1');

            var select = wrapper.querySelector('.crm-form-tag-select__native');
            if (!select) return;

            syncFormTagSelect(wrapper);

            select.addEventListener('change', function () {
                syncFormTagSelect(wrapper);
            });

            select.addEventListener('focus', function () {
                wrapper.classList.add('is-open');
            });

            select.addEventListener('blur', function () {
                wrapper.classList.remove('is-open');
            });
        });
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
        page.querySelectorAll('[data-lead-id="' + leadId + '"] .crm-board-card__contact span').forEach(function (el) {
            if (lead.email || lead.phone) {
                el.textContent = lead.email || lead.phone;
            }
        });
        page.querySelectorAll('[data-crm-list-row][data-lead-id="' + leadId + '"] .crm-list-row__contact-line').forEach(function (line) {
            var emailChip = line.querySelector('.crm-list-row__contact iconify-icon[icon="solar:letter-linear"]');
            emailChip = emailChip ? emailChip.closest('.crm-list-row__contact') : null;
            var phoneChip = line.querySelector('.crm-list-row__contact iconify-icon[icon="solar:phone-linear"]');
            phoneChip = phoneChip ? phoneChip.closest('.crm-list-row__contact') : null;

            function refreshChip(chip, value, maxLen) {
                if (!chip || !value) return;
                chip.title = value;
                var icon = chip.querySelector('iconify-icon');
                chip.textContent = '';
                if (icon) chip.appendChild(icon);
                chip.appendChild(document.createTextNode(maxLen && value.length > maxLen ? value.slice(0, maxLen) + '…' : value));
            }

            refreshChip(emailChip, lead.email, 28);
            refreshChip(phoneChip, lead.phone, 0);
        });
        page.querySelectorAll('[data-lead-id="' + leadId + '"] .crm-lead-avatar').forEach(function (el) {
            if (lead.full_name) {
                var parts = lead.full_name.trim().split(/\s+/);
                var initials = parts.slice(0, 2).map(function (p) { return p.charAt(0).toUpperCase(); }).join('');
                el.textContent = initials || '?';
            }
        });
        var host = panelHost();
        if (host && lead.full_name) {
            host.querySelectorAll('[data-crm-panel-title]').forEach(function (el) {
                if (!el.classList.contains('is-editing')) {
                    el.textContent = lead.full_name;
                }
            });
        }
        var modalTitle = document.getElementById('crmLeadDetailModalLabel')
            || document.getElementById('crmLeadDetailDrawerLabel');
        if (modalTitle && lead.full_name) {
            modalTitle.textContent = lead.full_name;
        }

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
            initFormTagSelects(host);
            syncPanelHeaderFromHost(host);
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
            box.classList.add('d-none');
            box.textContent = '';
            return;
        }
        box.hidden = false;
        box.classList.remove('d-none');
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

    function loadCreatePanel() {
        var host = panelHost();
        var url = createPanelUrl();
        if (!host || !url) return Promise.resolve();

        host.innerHTML = panelLoadingMarkup();
        syncModalHeader('Create lead', 'Add a lead without leaving the board');
        showLeadModal();

        return fetchPanelHtml(url).then(function (html) {
            host.innerHTML = html;
            initFormTagSelects(host);
            syncModalHeader('Create lead', 'Add a lead without leaving the board');
            var firstInput = host.querySelector('input[name="first_name"]');
            if (firstInput) firstInput.focus();
        }).catch(function () {
            host.innerHTML = '<div class="crm-lead-panel-loading"><span>Could not load create form. Please try again.</span></div>';
            showToast('Could not load create form.', true);
        });
    }

    function openCreateLeadPanel() {
        if (!window.bootstrap || !leadDetailModal()) return;
        loadCreatePanel();
    }

    function submitPanelCreate(form) {
        var saveBtn = form.querySelector('[data-crm-panel-save]');
        var formData = new FormData(form);

        if (saveBtn) saveBtn.disabled = true;
        showPanelEditErrors(form, {});

        fetch(storeUrl() || form.getAttribute('action'), {
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
                showToast((result.data && result.data.message) || 'Could not create lead.', true);
                return;
            }

            showToast((result.data && result.data.message) || 'Lead created.', false);

            var leadId = result.data && result.data.lead && result.data.lead.id;
            try {
                var url = new URL(window.location.href);
                url.searchParams.delete('open_create');
                if (leadId) {
                    url.searchParams.set('open_lead', String(leadId));
                }
                window.location.href = url.toString();
            } catch (e) {
                window.location.reload();
            }
        }).catch(function () {
            showToast('Could not create lead. Please try again.', true);
        }).finally(function () {
            if (saveBtn) saveBtn.disabled = false;
        });
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

    function parseCount(value, fallback) {
        var parsed = parseInt(value, 10);
        return isNaN(parsed) ? fallback : parsed;
    }

    function formatLeadCount(value) {
        return Number(value || 0).toLocaleString();
    }

    function getLeadQueryFilter(key) {
        return new URLSearchParams(window.location.search).get(key) || '';
    }

    function setFilteredTotal(next) {
        var total = Math.max(0, parseCount(next, 0));
        page.setAttribute('data-filtered-total', String(total));
        page.setAttribute('data-board-total', String(total));

        page.querySelectorAll('[data-crm-toolbar-matching]').forEach(function (el) {
            el.textContent = formatLeadCount(total);
        });
        page.querySelectorAll('[data-crm-pagination-total]').forEach(function (el) {
            el.textContent = formatLeadCount(total);
        });
        page.querySelectorAll('[data-crm-filter-bulk-total]').forEach(function (el) {
            el.textContent = formatLeadCount(total);
        });
    }

    function adjustFilteredTotal(delta) {
        if (!delta) return;
        setFilteredTotal(parseCount(page.getAttribute('data-filtered-total'), 0) + delta);
    }

    function refreshListViewMetrics() {
        if (!page.classList.contains('crm-list-view')) return;

        var list = page.querySelector('[data-crm-leads-list]');
        if (!list) return;

        var rows = list.querySelectorAll('[data-crm-list-row][data-lead-id]').length;
        var total = parseCount(page.getAttribute('data-filtered-total'), rows);
        var params = new URLSearchParams(window.location.search);
        var pageNum = Math.max(1, parseCount(params.get('page'), 1));
        var perPage = Math.max(1, parseCount(params.get('per_page'), 15));
        var firstItem = rows > 0 ? ((pageNum - 1) * perPage) + 1 : 0;
        var lastItem = rows > 0 ? Math.min(total, firstItem + rows - 1) : 0;
        var rangeText = rows > 0
            ? formatLeadCount(firstItem) + '–' + formatLeadCount(lastItem) + ' of ' + formatLeadCount(total)
            : '0 of ' + formatLeadCount(total);

        page.querySelectorAll('[data-crm-list-range]').forEach(function (el) {
            if (rows > 0) {
                el.textContent = formatLeadCount(firstItem) + '–' + formatLeadCount(lastItem);
            } else {
                el.textContent = '0';
            }
        });
        page.querySelectorAll('[data-crm-toolbar-list-range]').forEach(function (el) {
            el.textContent = rangeText;
        });
    }

    function refreshBoardLoadedSummary() {
        var totalLoaded = page.querySelectorAll('[data-crm-board-card][data-lead-id]').length;
        var boardTotal = parseCount(page.getAttribute('data-board-total'), 0);

        page.querySelectorAll('[data-crm-board-loaded-count]').forEach(function (el) {
            el.textContent = formatLeadCount(totalLoaded);
        });
        page.querySelectorAll('[data-crm-toolbar-board-loaded]').forEach(function (el) {
            el.textContent = formatLeadCount(totalLoaded) + ' loaded on board';
        });

        var summaryNote = page.querySelector('.crm-leads-pagination__summary-note');
        if (summaryNote) {
            summaryNote.hidden = boardTotal > 0 && totalLoaded >= boardTotal;
        }
    }

    function syncBoardColumnBody(body) {
        if (!body) return;

        var column = body.closest('[data-crm-dropzone]');
        var colStatus = column ? column.getAttribute('data-status') : '';
        var leadCards = body.querySelectorAll('[data-crm-board-card][data-lead-id]').length;
        var submissions = body.querySelectorAll('[data-crm-submission-open]').length;
        var leadTotal = parseCount(body.getAttribute('data-total'), leadCards);
        var visible = leadCards + submissions;
        var columnTotal = leadTotal + (colStatus === 'new' ? submissions : 0);

        if (column) {
            var headStrong = column.querySelector('.crm-board-column__title-wrap strong');
            var countEl = column.querySelector('.crm-board-column__count');
            if (headStrong) headStrong.textContent = formatLeadCount(columnTotal);
            if (countEl) {
                countEl.textContent = String(visible);
                countEl.title = leadCards < leadTotal
                    ? 'Showing ' + visible + ' of ' + (leadTotal + submissions)
                    : visible + ' visible';
            }
        }

        body.setAttribute('data-has-more', leadCards < leadTotal ? '1' : '0');

        var empty = body.querySelector('.crm-board-empty');
        if (empty) empty.hidden = visible > 0;

        var loadMore = body.querySelector('[data-crm-board-load-more]');
        if (loadMore) loadMore.hidden = body.getAttribute('data-has-more') !== '1';
    }

    function refreshBoardColumnMetrics(status) {
        if (status) {
            var column = page.querySelector('[data-crm-dropzone][data-status="' + status + '"]');
            syncBoardColumnBody(column ? column.querySelector('[data-crm-board-scroll]') : null);
        } else {
            page.querySelectorAll('[data-crm-board-scroll]').forEach(syncBoardColumnBody);
        }
        refreshBoardLoadedSummary();
    }

    function adjustColumnLeadTotal(status, delta) {
        if (!status || !delta) return;

        var column = page.querySelector('[data-crm-dropzone][data-status="' + status + '"]');
        if (!column) return;

        var body = column.querySelector('[data-crm-board-scroll]');
        if (!body) return;

        var next = Math.max(0, parseCount(body.getAttribute('data-total'), 0) + delta);
        body.setAttribute('data-total', String(next));
        refreshBoardColumnMetrics(status);
    }

    function transferColumnLeadTotals(fromStatus, toStatus) {
        if (!fromStatus || !toStatus || fromStatus === toStatus) return;
        adjustColumnLeadTotal(fromStatus, -1);
        adjustColumnLeadTotal(toStatus, 1);
    }

    function handleLeadStatusMetricsChange(leadId, previousStatus, newStatus, options) {
        options = options || {};
        if (!previousStatus || !newStatus || previousStatus === newStatus) return;

        if (!options.skipColumnTransfer) {
            transferColumnLeadTotals(previousStatus, newStatus);
        }

        var statusFilter = getLeadQueryFilter('lead_status');
        if (statusFilter && statusFilter !== newStatus) {
            adjustFilteredTotal(-1);
            var row = page.querySelector('[data-crm-list-row][data-lead-id="' + leadId + '"]');
            if (row) row.remove();
        }

        refreshListViewMetrics();
        refreshBoardColumnMetrics();
    }

    function refreshBoardColumnCounts() {
        refreshBoardColumnMetrics();
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

    function closestFromEvent(event, selector) {
        if (event.composedPath) {
            var path = event.composedPath();
            for (var i = 0; i < path.length; i++) {
                var node = path[i];
                if (!node || node === document || node === window) continue;
                if (node.nodeType !== 1) continue;
                if (node.matches && node.matches(selector)) return node;
                if (node.closest) {
                    var match = node.closest(selector);
                    if (match) return match;
                }
            }
            return null;
        }

        return event.target.closest(selector);
    }

    function isDragBlockedTarget(event) {
        return !!closestFromEvent(event, [
            '[data-crm-inline]',
            '.crm-inline-menu',
            '.crm-inline-option',
            '.crm-board-card__quick-action',
            '.crm-list-action',
            '.crm-list-select',
            'label',
            'input',
            'button',
            'select',
            'textarea',
            'a'
        ].join(', '));
    }

    function insertBoardCard(item, targetBody, insertBefore) {
        if (!item || !targetBody) return;
        var empty = targetBody.querySelector('.crm-board-empty');
        if (insertBefore && insertBefore.parentElement === targetBody) {
            targetBody.insertBefore(item, insertBefore);
        } else {
            targetBody.insertBefore(item, empty || null);
        }
        refreshBoardColumnCounts();
    }

    function applyLeadStatusDrop(item, zone, insertBefore) {
        if (!item || !zone) return;

        var targetStatus = zone.getAttribute('data-status');
        var leadId = item.getAttribute('data-lead-id');
        var previousStatus = item.getAttribute('data-current-status');
        var isBoardCard = item.hasAttribute('data-crm-board-card');
        var origin = item.parentElement;
        var nextSibling = item.nextElementSibling;
        var targetBody = isBoardCard ? (zone.querySelector('.crm-board-column__body') || zone) : null;
        var originColumn = isBoardCard ? boardColumnFromTarget(item) : null;

        if (!targetStatus || !leadId) return;

        if (isBoardCard && targetBody && targetStatus === previousStatus) {
            insertBoardCard(item, targetBody, insertBefore);
            persistBoardColumnOrder(zone).then(function (result) {
                if (!result.ok) {
                    if (origin) origin.insertBefore(item, nextSibling || null);
                    refreshBoardColumnCounts();
                    showToast((result.data && result.data.message) || 'Could not save column order.', true);
                }
            });
            return;
        }

        var statusChanged = targetStatus !== previousStatus;

        if (isBoardCard && targetBody) {
            insertBoardCard(item, targetBody, insertBefore);
        }

        if (statusChanged) {
            transferColumnLeadTotals(previousStatus, targetStatus);
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
                if (statusChanged) {
                    transferColumnLeadTotals(targetStatus, previousStatus);
                }
                refreshBoardColumnMetrics();
                showToast((result.data && (result.data.message || result.data.error)) || 'Status update failed.', true);
                return;
            }

            syncLeadStatusControls(leadId, targetStatus, result.data || {});
            handleLeadStatusMetricsChange(leadId, previousStatus, targetStatus, { skipColumnTransfer: true });
            if (isBoardCard && originColumn) {
                persistBoardColumnOrder(originColumn);
            }
            if (isBoardCard && zone) {
                persistBoardColumnOrder(zone);
            }
            showToast((result.data && result.data.message) || 'Status updated.', false);
        }).catch(function () {
            item.setAttribute('data-current-status', previousStatus || '');
            if (isBoardCard && origin) origin.insertBefore(item, nextSibling || null);
            if (statusChanged) {
                transferColumnLeadTotals(targetStatus, previousStatus);
            }
            refreshBoardColumnMetrics();
            showToast('Status update failed. Please try again.', true);
        });
    }

    function handleBoardCardDrop(item, card, event) {
        if (!item || !card || item === card) return false;

        var sourceColumn = boardColumnFromTarget(item);
        var targetColumn = boardColumnFromTarget(card);
        if (!sourceColumn || !targetColumn) return false;

        var targetBody = targetColumn.querySelector('.crm-board-column__body');
        if (!targetBody) return false;

        var rect = card.getBoundingClientRect();
        var insertBefore = event.clientY < rect.top + (rect.height / 2) ? card : card.nextElementSibling;
        var sameColumn = sourceColumn === targetColumn;

        event.preventDefault();
        event.stopPropagation();
        clearBoardReorderHighlights();
        clearDropzoneHighlights();

        if (sameColumn) {
            insertBoardCard(item, targetBody, insertBefore);
            persistBoardColumnOrder(targetColumn).then(function (result) {
                if (!result.ok) {
                    showToast((result.data && result.data.message) || 'Could not save column order.', true);
                }
            });
            return true;
        }

        applyLeadStatusDrop(item, targetColumn, insertBefore);
        return true;
    }

    function tryCompleteListDrop(zone) {
        if (!listDragArm || !listDragArm.row || !zone) return false;

        var row = listDragArm.row;
        var targetStatus = zone.getAttribute('data-status');
        var previousStatus = row.getAttribute('data-current-status');
        var zoneLabel = (zone.textContent || '').trim();

        if (!targetStatus || !row.getAttribute('data-lead-id')) return false;

        if (targetStatus === previousStatus) {
            showToast('Lead is already in ' + (zoneLabel || 'this stage') + '.', false);
            disarmListDrag();
            return false;
        }

        disarmListDrag();
        suppressOpenUntil = Date.now() + 300;
        applyLeadStatusDrop(row, zone);
        return true;
    }

    function resolveListDropZone(target) {
        if (!target || !target.closest) return null;
        return target.closest('[data-crm-list-dropzone], .crm-list-status-drop');
    }

    function listDropZoneAt(clientX, clientY) {
        var hidden = [];

        if (listDragArm && listDragArm.row) {
            listDragArm.row.style.visibility = 'hidden';
            hidden.push(listDragArm.row);
        }

        var target = document.elementFromPoint(clientX, clientY);

        hidden.forEach(function (el) {
            el.style.visibility = '';
        });

        return resolveListDropZone(target);
    }

    function updateListDropHover(zone) {
        if (!listDragArm) return;
        if (zone === listDragArm.hoverZone) return;
        listDragArm.hoverZone = zone;
        clearDropzoneHighlights();
        if (zone) zone.classList.add('is-drag-over');
    }

    function clearListReorderHighlights() {
        page.querySelectorAll('[data-crm-list-row].is-reorder-over').forEach(function (row) {
            row.classList.remove('is-reorder-over');
        });
    }

    function listRowAt(clientX, clientY) {
        var hidden = [];

        if (listDragArm && listDragArm.row) {
            listDragArm.row.style.visibility = 'hidden';
            hidden.push(listDragArm.row);
        }

        var target = document.elementFromPoint(clientX, clientY);

        hidden.forEach(function (el) {
            el.style.visibility = '';
        });

        var row = target && target.closest ? target.closest('[data-crm-list-row][data-lead-id]') : null;
        if (!row || !page.contains(row) || row === listDragArm.row) return null;

        return row;
    }

    function updateListRowReorder(clientX, clientY) {
        if (!listDragArm || !listDragArm.dragging) return;

        var dragged = listDragArm.row;
        if (!dragged || !dragged.getAttribute('data-lead-id')) return;

        var targetRow = listRowAt(clientX, clientY);
        clearListReorderHighlights();
        if (!targetRow) return;

        targetRow.classList.add('is-reorder-over');
        var rect = targetRow.getBoundingClientRect();
        var insertAfter = clientY - rect.top > rect.height / 2;

        if (insertAfter) {
            if (targetRow.nextElementSibling !== dragged) {
                targetRow.after(dragged);
                listDragArm.reordered = true;
            }
        } else if (targetRow.previousElementSibling !== dragged) {
            targetRow.before(dragged);
            listDragArm.reordered = true;
        }
    }

    function persistListOrder() {
        var reorderUrl = page.getAttribute('data-reorder-url');
        var list = page.querySelector('[data-crm-leads-list]');
        if (!reorderUrl || !list) return Promise.resolve(false);

        var leadIds = Array.prototype.map.call(
            list.querySelectorAll('[data-crm-list-row][data-lead-id]'),
            function (row) { return parseInt(row.getAttribute('data-lead-id'), 10); }
        ).filter(function (id) { return !isNaN(id); });

        if (!leadIds.length) return Promise.resolve(false);

        var params = new URLSearchParams(window.location.search);

        return fetch(reorderUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                lead_ids: leadIds,
                page: parseInt(params.get('page') || '1', 10) || 1,
                per_page: parseInt(params.get('per_page') || '15', 10) || 15
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
                showToast((result.data && (result.data.message || result.data.error)) || 'Could not save row order.', true);
                return false;
            }

            showToast((result.data && result.data.message) || 'Row order updated.', false);
            return true;
        }).catch(function () {
            showToast('Could not save row order. Please try again.', true);
            return false;
        });
    }

    function beginListRowDrag(row) {
        if (!listDragArm || listDragArm.dragging || !row) return;

        listDragArm.dragging = true;
        row.classList.add('is-dragging');
    }

    function endListRowDrag() {
        if (!listDragArm || !listDragArm.row) return;

        listDragArm.row.classList.remove('is-dragging');
        listDragArm.row.style.visibility = '';
        listDragArm.dragging = false;
        stopListAutoScroll();
    }

    function armListRowForDrag(row, event) {
        page.querySelectorAll('[data-crm-list-row].is-drag-armed').forEach(function (other) {
            other.classList.remove('is-drag-armed', 'is-dragging');
        });

        var checkbox = row.querySelector('[data-crm-lead-select]');
        if (checkbox) {
            checkbox.checked = true;
        }

        listDragArm = {
            row: row,
            hoverZone: null,
            armedAt: Date.now(),
            ignoreUntil: Date.now() + 180,
            dragging: false,
            reordered: false,
            startX: event ? event.clientX : null,
            startY: event ? event.clientY : null
        };

        row.classList.add('is-drag-armed');
        page.classList.add('crm-list-drop-mode');
        setDragActive(true);
        syncBulkUi();

        if (window.getSelection) {
            var selection = window.getSelection();
            if (selection) selection.removeAllRanges();
        }
    }

    function disarmListDrag() {
        if (listDragArm && listDragArm.row) {
            listDragArm.row.classList.remove('is-drag-armed', 'is-dragging');
            listDragArm.row.style.visibility = '';
            listDragArm.row.style.pointerEvents = '';
        }
        listDragArm = null;
        page.classList.remove('crm-list-drop-mode');
        clearDropzoneHighlights();
        clearListReorderHighlights();
        stopListAutoScroll();
        setDragActive(false);
    }

    var draggedItem = null;
    var dragOrigin = null;
    var dragNextSibling = null;
    var dragMoved = false;
    var suppressOpenUntil = 0;
    var canUpdateLeads = page.getAttribute('data-can-update') === '1';
    var dragStartBlocked = false;
    var listDragArm = null;
    var rowOpenTimer = null;
    var listAutoScroll = {
        active: false,
        clientX: 0,
        clientY: 0,
        rafId: null
    };

    function stopListAutoScroll() {
        listAutoScroll.active = false;
        if (listAutoScroll.rafId) {
            cancelAnimationFrame(listAutoScroll.rafId);
            listAutoScroll.rafId = null;
        }
    }

    function tickListAutoScroll() {
        if (!listAutoScroll.active || !listDragArm || !listDragArm.dragging) {
            stopListAutoScroll();
            return;
        }

        var edge = 84;
        var maxSpeed = 20;
        var clientY = listAutoScroll.clientY;
        var viewportHeight = window.innerHeight;
        var delta = 0;

        if (clientY < edge) {
            delta = -maxSpeed * Math.pow((edge - clientY) / edge, 1.15);
        } else if (clientY > viewportHeight - edge) {
            delta = maxSpeed * Math.pow((clientY - (viewportHeight - edge)) / edge, 1.15);
        }

        if (delta !== 0) {
            window.scrollBy({ top: delta, left: 0, behavior: 'auto' });
            updateListRowReorder(listAutoScroll.clientX, listAutoScroll.clientY);
            updateListDropHover(listDropZoneAt(listAutoScroll.clientX, listAutoScroll.clientY));
        }

        listAutoScroll.rafId = requestAnimationFrame(tickListAutoScroll);
    }

    function autoScrollListWhileDragging(clientX, clientY) {
        if (!listDragArm || !listDragArm.dragging) {
            stopListAutoScroll();
            return;
        }

        listAutoScroll.clientX = clientX;
        listAutoScroll.clientY = clientY;

        if (!listAutoScroll.active) {
            listAutoScroll.active = true;
            tickListAutoScroll();
        }
    }

    function cancelRowOpenTimer() {
        if (!rowOpenTimer) return;
        clearTimeout(rowOpenTimer);
        rowOpenTimer = null;
    }

    page.addEventListener('mousedown', function (event) {
        dragStartBlocked = isDragBlockedTarget(event);

        if (page.classList.contains('crm-list-view') && event.detail >= 2) {
            cancelRowOpenTimer();
        }

        if (page.classList.contains('crm-list-view') && canUpdateLeads && event.button === 0 && !listDragArm) {
            var handle = closestFromEvent(event, '.crm-list-row__handle');
            if (handle && !isDragBlockedTarget(event)) {
                var handleRow = handle.closest('[data-crm-list-row][data-lead-id]');
                if (handleRow && page.contains(handleRow)) {
                    cancelRowOpenTimer();
                    armListRowForDrag(handleRow, event);
                    suppressOpenUntil = Date.now() + 1500;
                }
            }
        }

        if (!listDragArm || event.button !== 0) return;
        if (Date.now() < listDragArm.ignoreUntil) return;

        var dropZone = resolveListDropZone(event.target);
        if (dropZone) return;

        var armedRow = listDragArm.row;
        if (!armedRow || !armedRow.contains(event.target)) return;

        if (isDragBlockedTarget(event)) return;

        listDragArm.startX = event.clientX;
        listDragArm.startY = event.clientY;
    }, true);

    page.addEventListener('dblclick', function (event) {
        if (!canUpdateLeads || !page.classList.contains('crm-list-view')) return;
        if (isDragBlockedTarget(event)) return;

        var row = closestFromEvent(event, '[data-crm-list-row]');
        if (!row || !page.contains(row)) return;

        event.preventDefault();
        event.stopPropagation();

        cancelRowOpenTimer();

        disarmListDrag();
        armListRowForDrag(row, event);
        suppressOpenUntil = Date.now() + 1500;
    });

    page.addEventListener('selectstart', function (event) {
        if (listDragArm && page.contains(event.target)) {
            event.preventDefault();
        }
    });

    document.addEventListener('mousemove', function (event) {
        if (!listDragArm) return;
        if (Date.now() < listDragArm.ignoreUntil) return;

        if (!listDragArm.dragging && listDragArm.startX != null && listDragArm.startY != null) {
            var distance = Math.hypot(event.clientX - listDragArm.startX, event.clientY - listDragArm.startY);
            if (distance > 6) {
                beginListRowDrag(listDragArm.row);
            }
        }

        if (listDragArm.dragging) {
            updateListRowReorder(event.clientX, event.clientY);
            autoScrollListWhileDragging(event.clientX, event.clientY);
        }

        updateListDropHover(listDropZoneAt(event.clientX, event.clientY));
    });

    document.addEventListener('mouseup', function (event) {
        if (!listDragArm) return;
        if (Date.now() < listDragArm.ignoreUntil) return;

        var zone = listDragArm.dragging
            ? (listDropZoneAt(event.clientX, event.clientY) || listDragArm.hoverZone)
            : (resolveListDropZone(event.target) || listDragArm.hoverZone || listDropZoneAt(event.clientX, event.clientY));

        if (zone) {
            if (listDragArm.dragging) {
                endListRowDrag();
            }
            tryCompleteListDrop(zone);
            return;
        }

        if (listDragArm.dragging) {
            var didReorder = !!listDragArm.reordered;
            endListRowDrag();
            clearDropzoneHighlights();
            clearListReorderHighlights();

            if (didReorder) {
                suppressOpenUntil = Date.now() + 300;
                persistListOrder().finally(function () {
                    disarmListDrag();
                });
            }

            return;
        }

        if (!event.target.closest('[data-crm-list-row], [data-crm-list-status-rail], [data-crm-list-dropzone], .crm-list-status-drop, [data-crm-bulk-bar]')) {
            disarmListDrag();
        }
    });

    document.addEventListener('click', function (event) {
        if (!listDragArm) return;

        var zone = resolveListDropZone(event.target);
        if (!zone || !page.contains(zone)) return;

        event.preventDefault();
        event.stopPropagation();
        tryCompleteListDrop(zone);
    }, true);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && listDragArm) {
            disarmListDrag();
        }
    });

    page.addEventListener('dragstart', function (event) {
        if (event.target.closest('[data-crm-list-row]')) {
            event.preventDefault();
            return;
        }

        if (event.target.closest('[data-crm-inline], .crm-board-card__quick-action')) {
            event.preventDefault();
            return;
        }

        if (dragStartBlocked) {
            event.preventDefault();
            return;
        }

        var item = event.target.closest('[data-crm-board-card]');
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
            event.dataTransfer.setData('text/plain', item.getAttribute('data-lead-id') || 'lead');
        }
    });

    page.addEventListener('drag', function () {
        dragMoved = true;
    });

    page.addEventListener('dragenter', function (event) {
        if (!draggedItem) return;
        var zone = statusDropZone(event.target);
        if (!zone || !page.contains(zone)) return;
        event.preventDefault();
    });

    page.addEventListener('dragover', function (event) {
        if (!draggedItem || !draggedItem.hasAttribute('data-crm-board-card')) return;

        var card = boardCardFromTarget(event.target);
        var zone = statusDropZone(event.target);

        if (card && card !== draggedItem) {
            var sourceColumn = boardColumnFromTarget(draggedItem);
            var targetColumn = boardColumnFromTarget(card);
            if (sourceColumn && targetColumn) {
                event.preventDefault();
                clearBoardReorderHighlights();
                card.classList.add('is-reorder-over');
                if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
                return;
            }
        }

        if (!zone || !page.contains(zone)) return;
        event.preventDefault();
        clearBoardReorderHighlights();
        clearDropzoneHighlights();
        zone.classList.add('is-drag-over');
        if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
    });

    page.addEventListener('dragleave', function (event) {
        var zone = statusDropZone(event.target);
        if (!zone) return;
        var related = event.relatedTarget;
        if (related && zone.contains(related)) return;
        zone.classList.remove('is-drag-over');
    });

    page.addEventListener('drop', function (event) {
        if (!draggedItem || !draggedItem.hasAttribute('data-crm-board-card')) return;

        var card = boardCardFromTarget(event.target);
        if (card && handleBoardCardDrop(draggedItem, card, event)) {
            return;
        }

        var zone = statusDropZone(event.target);
        if (!zone || !page.contains(zone)) return;
        event.preventDefault();
        event.stopPropagation();

        var item = draggedItem;
        clearBoardReorderHighlights();
        clearDropzoneHighlights();
        applyLeadStatusDrop(item, zone);
    });

    page.addEventListener('dragend', function () {
        if (draggedItem) draggedItem.classList.remove('is-dragging');
        clearBoardReorderHighlights();
        clearDropzoneHighlights();
        setDragActive(false);
        dragStartBlocked = false;
        draggedItem = null;
        dragOrigin = null;
        dragNextSibling = null;
        if (dragMoved) {
            suppressOpenUntil = Date.now() + 250;
        }
        dragMoved = false;
    });

    var boardScrollInitialized = false;

    function initBoardInfiniteScroll() {
        if (!page.classList.contains('crm-board-view') || boardScrollInitialized) return;
        boardScrollInitialized = true;

        var boardColumnUrl = page.getAttribute('data-board-column-url');
        var batchSize = parseInt(page.getAttribute('data-board-batch-size') || '20', 10);
        if (!boardColumnUrl || typeof IntersectionObserver === 'undefined') return;

        var loadingColumns = {};

        function loadMoreBoardColumn(body) {
            if (!body || body.getAttribute('data-has-more') !== '1') return;

            var status = body.getAttribute('data-status');
            if (!status || loadingColumns[status]) return;

            var offset = parseInt(body.getAttribute('data-offset') || '0', 10);
            var loadMore = body.querySelector('[data-crm-board-load-more]');
            var idleLabel = loadMore ? loadMore.querySelector('.crm-board-column__load-idle') : null;
            var busyLabel = loadMore ? loadMore.querySelector('.crm-board-column__load-busy') : null;

            loadingColumns[status] = true;
            body.classList.add('is-loading-more');
            if (idleLabel) idleLabel.hidden = true;
            if (busyLabel) busyLabel.hidden = false;

            var params = new URLSearchParams(window.location.search);
            params.set('lead_status', status);
            params.set('offset', String(offset));
            params.set('limit', String(batchSize));
            params.delete('page');

            fetch(boardColumnUrl + '?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
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
                if (!result.ok || !result.data || !result.data.html) {
                    showToast((result.data && result.data.message) || 'Could not load more leads.', true);
                    return;
                }

                var wrap = document.createElement('div');
                wrap.innerHTML = result.data.html;
                var loadAnchor = body.querySelector('[data-crm-board-load-more]');
                wrap.querySelectorAll('[data-crm-board-card]').forEach(function (card) {
                    body.insertBefore(card, loadAnchor || null);
                });

                var empty = body.querySelector('.crm-board-empty');
                if (empty) empty.hidden = body.querySelectorAll('[data-crm-board-card]').length > 0;

                body.setAttribute('data-offset', String(result.data.loaded));
                body.setAttribute('data-has-more', result.data.has_more ? '1' : '0');
                syncBoardColumnBody(body);
                refreshBoardLoadedSummary();
            }).catch(function () {
                showToast('Could not load more leads. Please try again.', true);
            }).finally(function () {
                loadingColumns[status] = false;
                body.classList.remove('is-loading-more');
                if (idleLabel) idleLabel.hidden = false;
                if (busyLabel) busyLabel.hidden = true;
                syncBoardColumnBody(body);
            });
        }

        page.querySelectorAll('[data-crm-board-scroll]').forEach(function (body) {
            var loadMore = body.querySelector('[data-crm-board-load-more]');
            if (!loadMore || body.getAttribute('data-has-more') !== '1') return;

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        loadMoreBoardColumn(body);
                    }
                });
            }, {
                root: body,
                rootMargin: '160px 0px',
                threshold: 0
            });

            observer.observe(loadMore);
        });
    }

    refreshBoardColumnCounts();
    initBoardInfiniteScroll();

    page.addEventListener('click', function (event) {
        var createBtn = event.target.closest('[data-crm-lead-create-open]');
        if (createBtn && page.contains(createBtn)) {
            event.preventDefault();
            openCreateLeadPanel();
            return;
        }

        var openTrigger = event.target.closest('[data-crm-lead-open-trigger]');
        if (openTrigger && page.contains(openTrigger) && Date.now() >= suppressOpenUntil && !listDragArm) {
            event.preventDefault();
            event.stopPropagation();
            var triggerLeadId = openTrigger.closest('[data-lead-id]')?.getAttribute('data-lead-id');
            if (triggerLeadId) {
                cancelRowOpenTimer();
                openLeadPanel(triggerLeadId);
            }
            return;
        }

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

                if (listDragArm || event.detail > 1) {
                    cancelRowOpenTimer();
                    return;
                }

                cancelRowOpenTimer();
                var openDelay = page.classList.contains('crm-list-view') ? 450 : 320;
                rowOpenTimer = window.setTimeout(function () {
                    rowOpenTimer = null;
                    if (listDragArm || Date.now() < suppressOpenUntil) return;
                    openLeadPanel(leadId);
                }, openDelay);
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
                    var previousStatus = page.querySelector('[data-crm-list-row][data-lead-id="' + leadId + '"]')?.getAttribute('data-current-status')
                        || page.querySelector('[data-crm-board-card][data-lead-id="' + leadId + '"]')?.getAttribute('data-current-status')
                        || previous
                        || '';
                    page.querySelectorAll('[data-lead-id="' + leadId + '"][data-current-status]').forEach(function (el) {
                        el.setAttribute('data-current-status', value);
                    });
                    var targetBody = page.querySelector('[data-crm-dropzone][data-status="' + value + '"] .crm-board-column__body');
                    var card = page.querySelector('[data-crm-board-card][data-lead-id="' + leadId + '"]');
                    if (targetBody && card) {
                        targetBody.appendChild(card);
                    }
                    handleLeadStatusMetricsChange(leadId, previousStatus, value);
                }
                if (field === 'priority') {
                    syncBoardCardPriority(leadId, value);
                }
                applyPanelActivityUpdate(leadId, result.data);
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
            applyPanelActivityUpdate(leadId, result.data);
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
        var ids = selectedLeadIds();
        var count = ids.length;

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

        if (!bulkBar) return;

        bulkBar.hidden = count === 0;
        if (bulkCount) bulkCount.textContent = String(count);

        bulkBar.querySelectorAll('[data-crm-bulk-apply]').forEach(function (btn) {
            var field = btn.getAttribute('data-crm-bulk-apply');
            var select = bulkSelectForField(field);
            btn.disabled = count === 0 || !select || !select.value;
        });
    }

    page.addEventListener('change', function (event) {
        if (!event.target.matches('[data-crm-lead-select], [data-crm-select-all]')) return;

        if (event.target.matches('[data-crm-select-all]')) {
            var checked = event.target.checked;
            page.querySelectorAll('[data-crm-lead-select]').forEach(function (cb) {
                cb.checked = checked;
            });
        }
        syncBulkUi();
    });

    page.addEventListener('click', function (event) {
        if (event.target.matches('[data-crm-lead-select], [data-crm-select-all]')) {
            event.stopPropagation();
        }
    }, true);

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
                    if (field === 'lead_status') {
                        var bulkRow = page.querySelector('[data-crm-list-row][data-lead-id="' + item.id + '"]');
                        var bulkCard = page.querySelector('[data-crm-board-card][data-lead-id="' + item.id + '"]');
                        var bulkPreviousStatus = (bulkRow && bulkRow.getAttribute('data-current-status'))
                            || (bulkCard && bulkCard.getAttribute('data-current-status'))
                            || '';
                        var bulkTargetBody = page.querySelector('[data-crm-dropzone][data-status="' + item.value + '"] .crm-board-column__body');
                        if (bulkTargetBody && bulkCard) {
                            bulkTargetBody.appendChild(bulkCard);
                        }
                        applyBulkControlVisual(item.id, field, item);
                        handleLeadStatusMetricsChange(String(item.id), bulkPreviousStatus, item.value);
                    } else {
                        applyBulkControlVisual(item.id, field, item);
                    }
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

    var filterBulkBar = page.querySelector('[data-crm-filter-bulk-bar]');
    var bulkFilterUrl = page.getAttribute('data-bulk-filter-url') || '';

    function currentLeadFilters() {
        var params = new URLSearchParams(window.location.search);
        var keys = ['search', 'follow_up', 'lead_category_id', 'source', 'form_id', 'advertising_platform', 'campaign_name', 'lead_status', 'priority', 'assigned_to', 'view'];
        var filters = {};
        keys.forEach(function (key) {
            var value = params.get(key);
            if (value !== null && value !== '') {
                filters[key] = value;
            }
        });
        return filters;
    }

    function filterBulkSelectForField(field) {
        if (!filterBulkBar) return null;
        if (field === 'lead_status') return filterBulkBar.querySelector('[data-crm-filter-bulk-status]');
        if (field === 'assigned_to') return filterBulkBar.querySelector('[data-crm-filter-bulk-assignee]');
        return null;
    }

    function syncFilterBulkUi() {
        if (!filterBulkBar) return;
        filterBulkBar.querySelectorAll('[data-crm-filter-bulk-apply]').forEach(function (btn) {
            var field = btn.getAttribute('data-crm-filter-bulk-apply');
            var select = filterBulkSelectForField(field);
            btn.disabled = !select || !select.value;
        });
    }

    if (filterBulkBar && bulkFilterUrl) {
        filterBulkBar.addEventListener('change', function (event) {
            if (event.target.matches('[data-crm-filter-bulk-status], [data-crm-filter-bulk-assignee]')) {
                syncFilterBulkUi();
            }
        });

        filterBulkBar.addEventListener('click', function (event) {
            var applyBtn = event.target.closest('[data-crm-filter-bulk-apply]');
            if (!applyBtn || applyBtn.disabled) return;

            event.preventDefault();
            event.stopPropagation();

            var field = applyBtn.getAttribute('data-crm-filter-bulk-apply');
            var select = filterBulkSelectForField(field);
            if (!select || !select.value) return;

            var value = select.value;
            if (field === 'assigned_to' && value === '__unassigned__') {
                value = '';
            }

            var total = parseInt(page.getAttribute('data-filtered-total') || '0', 10);
            var confirmMessage = field === 'assigned_to'
                ? 'Assign all ' + total + ' matching leads?'
                : 'Move all ' + total + ' matching leads to the selected status?';
            if (!window.confirm(confirmMessage)) {
                return;
            }

            applyBtn.disabled = true;

            fetch(bulkFilterUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    field: field,
                    value: value === '' ? null : value,
                    filters: currentLeadFilters()
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

                showToast((result.data && result.data.message) || 'Bulk update complete.', false);
                window.setTimeout(function () {
                    window.location.reload();
                }, 450);
            }).catch(function () {
                showToast('Bulk update failed. Please try again.', true);
            }).finally(function () {
                syncFilterBulkUi();
            });
        });

        syncFilterBulkUi();
    }

    page.addEventListener('click', function (event) {
        var statusFilter = event.target.closest('[data-crm-status-filter]');
        if (!statusFilter || !page.contains(statusFilter)) return;
        if (listDragArm || page.classList.contains('is-dragging-lead') || Date.now() < suppressOpenUntil) {
            event.preventDefault();
        }
    }, true);

    page.addEventListener('submit', function (event) {
        var createForm = event.target.closest('[data-crm-lead-create-form]');
        if (createForm && page.contains(createForm)) {
            event.preventDefault();
            submitPanelCreate(createForm);
            return;
        }

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

    function bootLeadModalFromQuery() {
        try {
            var bootUrl = new URL(window.location.href);
            if (bootUrl.searchParams.get('open_create') === '1') {
                openCreateLeadPanel();
                bootUrl.searchParams.delete('open_create');
                window.history.replaceState({}, '', bootUrl.toString());
                return;
            }
            var openLeadId = bootUrl.searchParams.get('open_lead');
            if (openLeadId) {
                openLeadPanel(openLeadId);
                bootUrl.searchParams.delete('open_lead');
                window.history.replaceState({}, '', bootUrl.toString());
            }
        } catch (e) {
            /* ignore malformed URLs */
        }
    }

    function confirmLeadRemove(form) {
        var card = form.closest('[data-crm-board-card], [data-crm-list-row]');
        var leadName = card
            ? (card.querySelector('.crm-board-card__title, .crm-list-row__name')?.textContent || '').trim()
            : '';
        var message = leadName
            ? '“' + leadName + '” will disappear from the board and list.'
            : 'This lead will disappear from the board and list.';

        if (window.CrmUI && typeof window.CrmUI.confirm === 'function') {
            return window.CrmUI.confirm({
                title: 'Remove lead from view?',
                message: message,
                note: 'Nothing is permanently deleted — the record stays safely in the database.',
                label: 'Remove from view',
                tone: 'danger',
                icon: 'solar:trash-bin-minimalistic-linear',
            });
        }

        return Promise.resolve(window.confirm(
            'Remove this lead from view?\n\nIt will disappear from the board and list, but stays safely in the database.'
        ));
    }

    function initLeadRemove() {
        page.querySelectorAll('[data-crm-lead-delete]').forEach(function (form) {
            if (form.getAttribute('data-bound') === '1') return;
            form.setAttribute('data-bound', '1');

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                event.stopPropagation();

                confirmLeadRemove(form).then(function (confirmed) {
                    if (!confirmed) return;

                    var button = form.querySelector('button');
                    if (button) {
                        button.disabled = true;
                    }

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    }).then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    }).then(function (result) {
                        if (button) button.disabled = false;

                        if (!result.ok) {
                            showToast((result.data && result.data.message) || 'Could not remove lead.', true);
                            return;
                        }

                        var card = form.closest('[data-crm-board-card], [data-crm-list-row]');
                        if (card) {
                            card.classList.add('crm-lead-removing');
                            setTimeout(function () { card.remove(); }, 180);
                        }

                        var shell = leadDetailShell();
                        if (shell && shell.classList.contains('show')) {
                            var modalLeadId = shell.querySelector('[data-lead-id]');
                            var removedId = form.action.split('/').filter(Boolean).pop();
                            if (modalLeadId && String(modalLeadId.getAttribute('data-lead-id')) === String(removedId)) {
                                hideLeadPanelShell();
                            }
                        }

                        showToast(result.data.message || 'Lead removed from view.');
                    }).catch(function () {
                        if (button) button.disabled = false;
                        showToast('Could not remove lead. Please try again.', true);
                    });
                });
            });
        });
    }
})();
