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
        page.classList.remove('crm-list-view', 'crm-grid-view');
        page.classList.add(view === 'grid' ? 'crm-grid-view' : 'crm-list-view');

        buttons.forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-view') === view);
        });

        try {
            localStorage.setItem(STORAGE_KEY, view);
        } catch (e) {
            /* ignore storage errors */
        }
    }

    var savedView = 'list';
    try {
        savedView = localStorage.getItem(STORAGE_KEY) || 'list';
    } catch (e) {
        savedView = 'list';
    }

    applyView(savedView === 'grid' ? 'grid' : 'list');

    if (toggle) {
        toggle.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-view]');
            if (!button) return;
            applyView(button.getAttribute('data-view'));
        });
    }

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
        return !!target.closest('a, button, input, select, textarea, label, form, .fc-table-actions, [data-crm-inline], .crm-inline-menu, .crm-inline-option');
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

    page.addEventListener('click', function (event) {
        var row = event.target.closest('tr.crm-lead-row[data-href]');
        if (row && page.contains(row) && !isInteractiveTarget(event.target)) {
            window.location.href = row.getAttribute('data-href');
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
        var row = event.target.closest('tr.crm-lead-row[data-href]');
        if (!row || event.target !== row) return;
        event.preventDefault();
        window.location.href = row.getAttribute('data-href');
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
})();
