/**
 * Shared CRM index inline-edit + clickable-row helpers (Customers / Projects).
 * Supports premium .crm-inline-control dropdowns and legacy select[data-crm-inline].
 */
(function () {
    'use strict';

    var pending = {};
    var openMenu = null;

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function showToast(page, message, isError) {
        var toastSlot = page.querySelector('[data-crm-toast-slot]');
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
        return !!target.closest('a, button, input, select, textarea, label, form, .fc-table-actions, [data-crm-inline]');
    }

    function inlineUrl(page, id) {
        var template = page.getAttribute('data-inline-url-template') || '';
        return template.replace('__ID__', String(id));
    }

    function closeOpenMenu() {
        if (!openMenu) return;
        openMenu.classList.remove('is-open');
        var trigger = openMenu.querySelector('.crm-inline-trigger');
        var menu = openMenu.querySelector('.crm-inline-menu');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
        if (menu) menu.hidden = true;
        openMenu = null;
    }

    function applyControlVisual(control, tone, icon, label) {
        if (!control) return;
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

    function saveInline(page, controlOrSelect, recordId, field, value, previous, previousTone, previousIcon, previousLabel, applySuccess, applyFailure) {
        var key = recordId + ':' + field;
        if (pending[key]) {
            applyFailure();
            return;
        }
        pending[key] = true;
        if (controlOrSelect.classList && controlOrSelect.classList.contains('crm-inline-control')) {
            controlOrSelect.classList.add('is-busy');
        } else {
            controlOrSelect.disabled = true;
        }

        fetch(inlineUrl(page, recordId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ field: field, value: value === '' ? null : value }),
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data };
            }).catch(function () {
                return { ok: response.ok, status: response.status, data: {} };
            });
        }).then(function (result) {
            if (!result.ok) {
                applyFailure();
                showToast(page, (result.data && (result.data.message || result.data.error)) || 'Update failed.', true);
                return;
            }
            applySuccess(result.data || {});
            showToast(page, (result.data && result.data.message) || 'Updated.', false);
        }).catch(function () {
            applyFailure();
            showToast(page, 'Update failed. Please try again.', true);
        }).finally(function () {
            pending[key] = false;
            if (controlOrSelect.classList && controlOrSelect.classList.contains('crm-inline-control')) {
                controlOrSelect.classList.remove('is-busy');
            } else {
                controlOrSelect.disabled = false;
            }
        });
    }

    function bindPage(page) {
        if (!page || page.getAttribute('data-crm-inline-bound') === '1') return;
        page.setAttribute('data-crm-inline-bound', '1');

        page.addEventListener('click', function (event) {
            var row = event.target.closest('tr.crm-clickable-row[data-href]');
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
                    menu.hidden = false;
                    control.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    openMenu = control;
                }
                return;
            }

            var option = event.target.closest('.crm-inline-option');
            if (option && page.contains(option)) {
                event.preventDefault();
                event.stopPropagation();
                var dropdown = option.closest('[data-crm-inline].crm-inline-control');
                if (!dropdown || dropdown.classList.contains('is-busy')) return;

                var recordId = dropdown.getAttribute('data-record-id');
                var field = dropdown.getAttribute('data-field');
                var previous = dropdown.getAttribute('data-previous');
                var previousTone = dropdown.getAttribute('data-tone') || 'neutral';
                var previousIcon = dropdown.getAttribute('data-icon') || '';
                var previousLabel = dropdown.querySelector('.crm-inline-trigger__label')?.textContent || '';
                var value = option.getAttribute('data-value') || '';
                var tone = option.getAttribute('data-tone') || 'neutral';
                var icon = option.getAttribute('data-icon') || previousIcon;
                var label = option.getAttribute('data-label') || option.textContent.trim();

                closeOpenMenu();
                if (String(value) === String(previous || '')) return;

                applyControlVisual(dropdown, tone, icon, label);
                markSelected(dropdown, value);

                saveInline(page, dropdown, recordId, field, value, previous, previousTone, previousIcon, previousLabel, function (data) {
                    dropdown.setAttribute('data-previous', value === null ? '' : String(value));
                    applyControlVisual(
                        dropdown,
                        data.tone || tone,
                        data.icon || icon,
                        data.label || label
                    );
                    markSelected(dropdown, value);
                }, function () {
                    applyControlVisual(dropdown, previousTone, previousIcon, previousLabel);
                    markSelected(dropdown, previous || '');
                });
            }
        });

        page.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            var row = event.target.closest('tr.crm-clickable-row[data-href]');
            if (!row || event.target !== row) return;
            event.preventDefault();
            window.location.href = row.getAttribute('data-href');
        });

        page.addEventListener('change', function (event) {
            var select = event.target.closest('select[data-crm-inline]');
            if (!select || !page.contains(select)) return;

            var recordId = select.getAttribute('data-record-id');
            var field = select.getAttribute('data-field');
            var previous = select.getAttribute('data-previous');
            var previousTone = select.getAttribute('data-tone') || 'neutral';
            var value = select.value;

            saveInline(page, select, recordId, field, value, previous, previousTone, '', '', function (data) {
                select.setAttribute('data-previous', value);
                select.setAttribute('data-tone', (data && data.tone) || previousTone);
            }, function () {
                select.value = previous || '';
                select.setAttribute('data-tone', previousTone);
            });

            var selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.getAttribute('data-tone')) {
                select.setAttribute('data-tone', selectedOpt.getAttribute('data-tone'));
            }
        });
    }

    function boot(root) {
        var scope = root || document;
        bindPage(scope.querySelector ? scope.querySelector('#crm-customers-page') : null);
        bindPage(scope.querySelector ? scope.querySelector('#crm-projects-page') : null);
        if (!root) {
            bindPage(document.getElementById('crm-customers-page'));
            bindPage(document.getElementById('crm-projects-page'));
        }
    }

    document.addEventListener('click', function (event) {
        if (openMenu && !event.target.closest('.crm-inline-control')) {
            closeOpenMenu();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { boot(); });
    } else {
        boot();
    }

    document.addEventListener('admin:page-loaded', function (event) {
        boot(event.detail && event.detail.root ? event.detail.root : document);
    });
})();
