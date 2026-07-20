(function () {
    'use strict';

    var STORAGE_KEY = 'crm_leads_view';
    var page = document.getElementById('crm-leads-page');
    if (!page) return;

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
})();
