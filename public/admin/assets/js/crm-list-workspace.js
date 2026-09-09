/**
 * Shared CRM list workspace — row navigation and filter auto-submit.
 */
(function () {
    'use strict';

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
            '.crm-list-row__actions',
            '.crm-list-action'
        ].join(', '));
    }

    function bindListWorkspace(page) {
        if (!page || page.getAttribute('data-crm-list-workspace-bound') === '1') return;
        page.setAttribute('data-crm-list-workspace-bound', '1');

        var openSelector = page.getAttribute('data-list-open-selector') || '[data-crm-record-open][data-href]';

        page.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-crm-record-open-trigger]');
            if (trigger && page.contains(trigger)) {
                event.preventDefault();
                event.stopPropagation();
                var href = trigger.closest('[data-href]')?.getAttribute('data-href');
                if (href) window.location.href = href;
                return;
            }

            var row = event.target.closest(openSelector);
            if (!row || !page.contains(row) || isInteractiveTarget(event.target)) return;
            window.location.href = row.getAttribute('data-href');
        });

        page.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            var row = event.target.closest(openSelector);
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
    }

    function boot(root) {
        var scope = root || document;
        scope.querySelectorAll('[data-crm-list-workspace]').forEach(bindListWorkspace);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { boot(); });
    } else {
        boot();
    }

    document.addEventListener('admin:page-loaded', function (event) {
        boot(event.detail && event.detail.root ? event.detail.root : document);
    });
})();
