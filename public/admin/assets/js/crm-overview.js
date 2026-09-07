(function () {
    'use strict';

    var page = document.getElementById('crm-overview-page');
    if (!page) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var endpoint = page.getAttribute('data-smart-search-url');
    var root = page.querySelector('[data-crm-overview-smart-search]');
    if (!root || !endpoint) return;

    var input = root.querySelector('[data-crm-overview-smart-input]');
    var panel = root.querySelector('[data-crm-overview-smart-panel]');
    var goBtn = root.querySelector('[data-crm-overview-smart-go]');
    var labelBox = root.querySelector('[data-crm-overview-smart-label]');
    var labelText = root.querySelector('[data-crm-overview-smart-label-text]');
    var debounceTimer = null;
    var latestUrl = null;

    function setPanelOpen(open) {
        if (!panel || !input) return;
        panel.hidden = !open;
        input.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function fetchInterpretation() {
        var query = (input.value || '').trim();
        if (!query) {
            latestUrl = null;
            if (labelBox) labelBox.hidden = true;
            return;
        }

        var url = endpoint + '?q=' + encodeURIComponent(query);
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            latestUrl = data.url || null;
            if (labelBox && labelText && data.label) {
                labelBox.hidden = false;
                labelText.textContent = 'Showing: ' + data.label;
            }
        }).catch(function () {
            latestUrl = null;
        });
    }

    function goToResults(query) {
        if (query) input.value = query;
        if (latestUrl) {
            window.location.href = latestUrl;
            return;
        }

        fetch(endpoint + '?q=' + encodeURIComponent(query || input.value || ''), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (data.url) window.location.href = data.url;
        }).catch(function () {
            /* ignore */
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
            goToResults();
        } else if (event.key === 'Escape') {
            setPanelOpen(false);
            input.blur();
        }
    });

    if (goBtn) {
        goBtn.addEventListener('click', function () {
            goToResults();
        });
    }

    root.querySelectorAll('[data-crm-overview-suggestion]').forEach(function (button) {
        button.addEventListener('click', function () {
            var query = button.getAttribute('data-query') || '';
            input.value = query;
            goToResults(query);
        });
    });

    document.addEventListener('click', function (event) {
        if (!root.contains(event.target)) {
            setPanelOpen(false);
        }
    });
})();
