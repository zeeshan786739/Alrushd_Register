/**
 * Import history: undo entire import batches without page reload.
 */
(function () {
    'use strict';

    var page = document.getElementById('crm-import-history-page');
    if (!page) return;

    var csrf = page.getAttribute('data-csrf') || '';
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

    page.querySelectorAll('[data-crm-import-undo]').forEach(function (button) {
        if (button.getAttribute('data-bound') === '1') return;
        button.setAttribute('data-bound', '1');

        button.addEventListener('click', function () {
            var url = button.getAttribute('data-url');
            var filename = button.getAttribute('data-import-filename') || 'this import';
            if (!url) return;

            var confirmed = window.confirm(
                'Undo "' + filename + '"?\n\nAll imported leads from this batch will disappear from the CRM board and list. Nothing is permanently deleted from the database.'
            );
            if (!confirmed) return;

            button.disabled = true;
            button.classList.add('is-busy');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ confirm: true }),
            }).then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            }).then(function (result) {
                button.disabled = false;
                button.classList.remove('is-busy');

                if (!result.ok) {
                    showToast((result.data && result.data.message) || 'Could not undo import.', true);
                    return;
                }

                showToast(result.data.message || 'Import undone.');

                setTimeout(function () {
                    window.location.reload();
                }, 900);
            }).catch(function () {
                button.disabled = false;
                button.classList.remove('is-busy');
                showToast('Could not undo import. Please try again.', true);
            });
        });
    });
})();
