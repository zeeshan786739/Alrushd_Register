/**
 * Import history: remove imported lead batches without page reload.
 */
(function () {
    'use strict';

    var page = document.getElementById('crm-import-history-page');
    if (!page) return;

    var metaCsrf = document.querySelector('meta[name="csrf-token"]');
    var csrf = page.getAttribute('data-csrf') || (metaCsrf ? metaCsrf.getAttribute('content') : '') || '';
    var undoAllUrl = page.getAttribute('data-undo-all-url') || '';
    var toastSlot = page.querySelector('[data-crm-toast-slot]');

    function showToast(message, isError) {
        if (!toastSlot || !message) return;
        var el = document.createElement('div');
        el.className = 'crm-toast' + (isError ? ' is-error' : '');
        el.textContent = message;
        toastSlot.appendChild(el);
        requestAnimationFrame(function () { el.classList.add('is-visible'); });
        setTimeout(function () {
            el.classList.remove('is-visible');
            setTimeout(function () { el.remove(); }, 220);
        }, 3200);
    }

    function parseResponse(response) {
        return response.text().then(function (text) {
            var data = {};
            if (text) {
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    data = { message: response.ok ? 'Done.' : (text.slice(0, 180) || 'Unexpected server response.') };
                }
            }
            return { ok: response.ok, status: response.status, data: data };
        });
    }

    function postUndo(url, onDone) {
        var body = new FormData();
        body.append('_token', csrf);
        body.append('confirm', '1');

        return fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: body,
        }).then(parseResponse).then(function (result) {
            if (typeof onDone === 'function') onDone(result);
            return result;
        });
    }

    page.querySelectorAll('[data-crm-import-undo]').forEach(function (button) {
        if (button.getAttribute('data-bound') === '1') return;
        button.setAttribute('data-bound', '1');

        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            var url = button.getAttribute('data-url');
            var filename = button.getAttribute('data-import-filename') || 'this import';
            var count = button.getAttribute('data-import-count');
            if (!url) return;

            if (!csrf) {
                showToast('Session expired. Please refresh the page and try again.', true);
                return;
            }

            var countLine = count ? '\n\n' + count + ' lead(s) will disappear from the board and list.' : '';
            var confirmed = window.confirm(
                'Remove all imported leads from "' + filename + '"?' + countLine + '\n\nNothing is permanently deleted from the database.'
            );
            if (!confirmed) return;

            button.disabled = true;
            button.classList.add('is-busy');

            postUndo(url).then(function (result) {
                button.disabled = false;
                button.classList.remove('is-busy');

                if (!result.ok) {
                    var message = (result.data && result.data.message) || 'Could not remove imported leads.';
                    if (result.status === 419) {
                        message = 'Session expired. Please refresh the page and try again.';
                    }
                    showToast(message, true);
                    return;
                }

                showToast(result.data.message || 'Imported leads removed.');
                setTimeout(function () { window.location.reload(); }, 900);
            }).catch(function () {
                button.disabled = false;
                button.classList.remove('is-busy');
                showToast('Could not remove imported leads. Please try again.', true);
            });
        });
    });

    page.querySelectorAll('[data-crm-import-undo-all]').forEach(function (button) {
        if (button.getAttribute('data-bound') === '1') return;
        button.setAttribute('data-bound', '1');

        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!undoAllUrl) return;

            if (!csrf) {
                showToast('Session expired. Please refresh the page and try again.', true);
                return;
            }

            var activeLeads = button.getAttribute('data-active-leads') || '0';
            var batchCount = button.getAttribute('data-batch-count') || '0';

            var confirmed = window.confirm(
                'Remove ALL imported leads?\n\n' +
                activeLeads + ' lead(s) across ' + batchCount + ' batch(es) will disappear from the CRM board and list.\n\n' +
                'Manually created leads are not affected. Nothing is permanently deleted from the database.'
            );
            if (!confirmed) return;

            button.disabled = true;
            button.classList.add('is-busy');

            postUndo(undoAllUrl).then(function (result) {
                button.disabled = false;
                button.classList.remove('is-busy');

                if (!result.ok) {
                    var message = (result.data && result.data.message) || 'Could not remove imported leads.';
                    if (result.status === 419) {
                        message = 'Session expired. Please refresh the page and try again.';
                    }
                    showToast(message, true);
                    return;
                }

                showToast(result.data.message || 'All imported leads removed.');
                setTimeout(function () { window.location.reload(); }, 900);
            }).catch(function () {
                button.disabled = false;
                button.classList.remove('is-busy');
                showToast('Could not remove imported leads. Please try again.', true);
            });
        });
    });
})();
