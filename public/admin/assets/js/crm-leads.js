(function () {
    'use strict';

    var STORAGE_KEY = 'crm_leads_view';
    var page = document.getElementById('crm-leads-page');
    if (!page) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var toastSlot = page.querySelector('[data-crm-toast-slot]');
    var pending = {};

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
        return !!target.closest('a, button, input, select, textarea, label, form, .fc-table-actions, [data-crm-inline]');
    }

    page.addEventListener('click', function (event) {
        var row = event.target.closest('tr.crm-lead-row[data-href]');
        if (!row || !page.contains(row)) return;
        if (isInteractiveTarget(event.target)) return;
        window.location.href = row.getAttribute('data-href');
    });

    page.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        var row = event.target.closest('tr.crm-lead-row[data-href]');
        if (!row || event.target !== row) return;
        event.preventDefault();
        window.location.href = row.getAttribute('data-href');
    });

    function inlineUrl(leadId) {
        var template = page.getAttribute('data-inline-url-template') || '';
        return template.replace('__ID__', String(leadId));
    }

    function applyTone(select, tone) {
        if (!select) return;
        select.setAttribute('data-tone', tone || 'neutral');
    }

    function selectedOptionTone(select) {
        var opt = select.options[select.selectedIndex];
        return (opt && opt.getAttribute('data-tone')) || select.getAttribute('data-tone') || 'neutral';
    }

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
        applyTone(select, selectedOptionTone(select));

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
                return { ok: response.ok, status: response.status, data: data };
            }).catch(function () {
                return { ok: response.ok, status: response.status, data: {} };
            });
        }).then(function (result) {
            if (!result.ok) {
                select.value = previous || '';
                applyTone(select, previousTone);
                showToast((result.data && (result.data.message || result.data.error)) || 'Update failed.', true);
                return;
            }
            select.setAttribute('data-previous', value);
            applyTone(select, selectedOptionTone(select));
            showToast((result.data && result.data.message) || 'Updated.', false);
        }).catch(function () {
            select.value = previous || '';
            applyTone(select, previousTone);
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
            var snapshot = filtersContainer.cloneNode(true);

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
                if (!page.querySelector('[data-saved-filters]') && snapshot) {
                    var toggleWrap = page.querySelector('[data-crm-view-toggle]');
                    if (toggleWrap && toggleWrap.parentNode) {
                        toggleWrap.parentNode.insertAdjacentElement('afterend', snapshot);
                    }
                }
                showToast('Could not clear filters.', true);
            });
        }
    });
})();
