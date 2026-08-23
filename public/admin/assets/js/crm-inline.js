/**
 * Shared CRM index inline-edit + clickable-row helpers (Customers / Projects).
 * Leads keep their dedicated crm-leads.js — do not replace it.
 */
(function () {
    'use strict';

    var pending = {};

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

    function applyTone(select, tone) {
        if (!select) return;
        select.setAttribute('data-tone', tone || 'neutral');
    }

    function bindPage(page) {
        if (!page || page.getAttribute('data-crm-inline-bound') === '1') return;
        page.setAttribute('data-crm-inline-bound', '1');

        page.addEventListener('click', function (event) {
            var row = event.target.closest('tr.crm-clickable-row[data-href]');
            if (!row || !page.contains(row)) return;
            if (isInteractiveTarget(event.target)) return;
            window.location.href = row.getAttribute('data-href');
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
            var key = recordId + ':' + field;

            if (pending[key]) {
                select.value = previous || '';
                return;
            }

            pending[key] = true;
            select.disabled = true;

            // Optimistic tone from selected option when available
            var selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.getAttribute('data-tone')) {
                applyTone(select, selectedOpt.getAttribute('data-tone'));
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
                    select.value = previous || '';
                    applyTone(select, previousTone);
                    showToast(page, (result.data && (result.data.message || result.data.error)) || 'Update failed.', true);
                    return;
                }
                select.setAttribute('data-previous', value);
                applyTone(select, (result.data && result.data.tone) || previousTone);
                showToast(page, (result.data && result.data.message) || 'Updated.', false);
            }).catch(function () {
                select.value = previous || '';
                applyTone(select, previousTone);
                showToast(page, 'Update failed. Please try again.', true);
            }).finally(function () {
                pending[key] = false;
                select.disabled = false;
            });
        });
    }

    function boot() {
        bindPage(document.getElementById('crm-customers-page'));
        bindPage(document.getElementById('crm-projects-page'));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
