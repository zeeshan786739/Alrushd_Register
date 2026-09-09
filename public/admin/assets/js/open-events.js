/**
 * Open Events workspace — row navigation and status filters.
 */
(function () {
    'use strict';

    var page = document.getElementById('oe-workspace-page');
    if (!page) return;

    function isInteractiveTarget(target) {
        return !!target.closest([
            'a',
            'button',
            'input',
            'select',
            'textarea',
            'label',
            'form',
            '.crm-list-row__actions'
        ].join(', '));
    }

    page.addEventListener('click', function (event) {
        var row = event.target.closest('[data-oe-row-open][data-href]');
        if (!row || !page.contains(row) || isInteractiveTarget(event.target)) return;
        window.location.href = row.getAttribute('data-href');
    });

    page.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        var row = event.target.closest('[data-oe-row-open][data-href]');
        if (!row || event.target !== row) return;
        event.preventDefault();
        window.location.href = row.getAttribute('data-href');
    });

    var filterRoot = page.querySelector('#oeStatusFilters');
    if (!filterRoot) return;

    var rows = page.querySelectorAll('.oe-list-row[data-oe-status]');
    var buttons = filterRoot.querySelectorAll('[data-oe-stat-filter]');

    function applyFilter(filter) {
        buttons.forEach(function (button) {
            var active = button.getAttribute('data-oe-stat-filter') === filter;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        rows.forEach(function (row) {
            var status = row.getAttribute('data-oe-status');
            var visible = filter === 'all' || status === filter;
            row.style.display = visible ? '' : 'none';
        });
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            applyFilter(button.getAttribute('data-oe-stat-filter') || 'all');
        });
    });
})();
