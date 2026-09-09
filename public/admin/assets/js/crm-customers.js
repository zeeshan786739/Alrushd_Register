/**
 * CRM Customers workspace — list row navigation and filter helpers.
 */
(function () {
    'use strict';

    var page = document.getElementById('crm-customers-page');
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
            '[data-crm-inline]',
            '.crm-inline-menu',
            '.crm-inline-option',
            '.crm-list-row__actions'
        ].join(', '));
    }

    page.addEventListener('click', function (event) {
        var row = event.target.closest('[data-crm-customer-open][data-href]');
        if (!row || !page.contains(row) || isInteractiveTarget(event.target)) return;
        window.location.href = row.getAttribute('data-href');
    });

    page.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        var row = event.target.closest('[data-crm-customer-open][data-href]');
        if (!row || event.target !== row) return;
        event.preventDefault();
        window.location.href = row.getAttribute('data-href');
    });

    page.querySelectorAll('[data-crm-filter-auto-submit]').forEach(function (field) {
        field.addEventListener('change', function () {
            var form = field.closest('form');
            if (form) form.submit();
        });
    });
})();
