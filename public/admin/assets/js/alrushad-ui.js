/**
 * Al Rushd CRM — Global UI interactions (all pages)
 */
(function () {
    'use strict';

    var loader = document.getElementById('crm-page-loader');
    var loaderTimer;

    function showLoader() {
        if (!loader) return;
        clearTimeout(loaderTimer);
        loaderTimer = setTimeout(function () {
            loader.classList.add('is-active');
        }, 100);
    }

    function hideLoader() {
        if (!loader) return;
        clearTimeout(loaderTimer);
        loader.classList.remove('is-active');
    }

    /* Navigation loader */
    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[href]');
        if (!link) return;
        var href = link.getAttribute('href');
        if (!href || href.charAt(0) === '#' || link.target === '_blank') return;
        if (link.hasAttribute('download')) return;
        if (href.indexOf('javascript:') === 0) return;
        try {
            var url = new URL(href, window.location.origin);
            if (url.origin === window.location.origin) showLoader();
        } catch (err) { /* ignore */ }
    });

    window.addEventListener('pageshow', hideLoader);

    document.addEventListener('DOMContentLoaded', function () {
        hideLoader();

        /* Safe DataTable init */
        if (typeof DataTable !== 'undefined') {
            var tableEl = document.getElementById('dataTable');
            if (tableEl && !tableEl.classList.contains('dataTable')) {
                try { new DataTable('#dataTable'); } catch (e) { /* ignore */ }
            }
        }

        /* Form submit loading */
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                if (form.classList.contains('needs-validation') && !form.checkValidity()) return;
                var btn = form.querySelector('[type="submit"]');
                if (btn && !btn.classList.contains('is-loading')) {
                    btn.classList.add('is-loading');
                    btn.disabled = true;
                }
            });
        });

        /* Active sidebar link */
        var path = window.location.pathname;
        document.querySelectorAll('.sidebar-menu a[href]').forEach(function (a) {
            try {
                var linkPath = new URL(a.href, window.location.origin).pathname;
                if (linkPath === path) {
                    a.classList.add('active-page');
                    var dropdown = a.closest('.dropdown');
                    if (dropdown) dropdown.classList.add('open', 'dropdown-open', 'active');
                }
            } catch (err) { /* ignore */ }
        });

        /* Global delete confirmation (replaces per-page scripts) */
        document.body.addEventListener('click', function (e) {
            var btn = e.target.closest('.delete-btn');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            var id = btn.getAttribute('data-id');
            if (!id || typeof Swal === 'undefined') return;
            Swal.fire({
                title: 'Delete this item?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
            }).then(function (result) {
                if (result.isConfirmed) {
                    var form = document.getElementById('delete-form-' + id);
                    if (form) form.submit();
                }
            });
        });

        /* Client-side table search (.um-table-search) */
        document.querySelectorAll('.um-table-search').forEach(function (input) {
            var card = input.closest('.card');
            var tbody = card ? card.querySelector('table tbody') : null;
            if (!tbody) return;
            input.addEventListener('input', function () {
                var q = input.value.toLowerCase().trim();
                tbody.querySelectorAll('tr').forEach(function (row) {
                    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        });
    });

    window.CrmUI = { showLoader: showLoader, hideLoader: hideLoader };
})();
