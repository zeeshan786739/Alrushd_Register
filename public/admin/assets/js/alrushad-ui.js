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

    function normalizePath(pathname) {
        var path = (pathname || '/').replace(/\/+$/, '');
        return path || '/';
    }

    function linkPathname(anchor) {
        var href = anchor.getAttribute('href') || '';
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) {
            return null;
        }
        try {
            return normalizePath(new URL(anchor.href, window.location.origin).pathname);
        } catch (err) {
            return null;
        }
    }

    function matchScore(linkPath, currentPath) {
        if (!linkPath || !currentPath) return -1;
        if (linkPath === currentPath) return 100000 + linkPath.length;
        // Prefix match for create/edit/show nested routes (boundary at '/')
        if (linkPath.length > 1 && currentPath.indexOf(linkPath + '/') === 0) {
            return linkPath.length;
        }
        return -1;
    }

    function ensureDropdownOpen(dropdown) {
        if (!dropdown) return;
        var wasOpen = dropdown.classList.contains('open') || dropdown.classList.contains('dropdown-open');
        dropdown.classList.add('open', 'dropdown-open');
        var toggle = dropdown.querySelector(":scope > a[role='button']");
        if (toggle) toggle.setAttribute('aria-expanded', 'true');

        // Only force-show when newly opened — avoid resetting an already-visible submenu
        if (!wasOpen) {
            if (typeof window.jQuery !== 'undefined') {
                window.jQuery(dropdown).children('.sidebar-submenu').stop(true, true).show();
            } else {
                var sub = dropdown.querySelector(':scope > .sidebar-submenu');
                if (sub) sub.style.display = 'block';
            }
        }
    }

    /**
     * Update active/open classes on the existing sidebar DOM only.
     * Never recreates nodes — preserves scroll, collapse, focus, and open menus.
     */
    function syncSidebarActive() {
        var menu = document.getElementById('sidebar-menu');
        if (!menu) return;

        var currentPath = normalizePath(window.location.pathname);
        var links = Array.prototype.slice.call(menu.querySelectorAll('a[href]'));
        var best = null;
        var bestScore = -1;

        links.forEach(function (a) {
            a.classList.remove('active-page');
            var linkPath = linkPathname(a);
            if (!linkPath) return;
            var score = matchScore(linkPath, currentPath);
            if (score > bestScore) {
                bestScore = score;
                best = a;
            }
        });

        if (!best) {
            menu.querySelectorAll('.dropdown').forEach(function (dropdown) {
                var toggle = dropdown.querySelector(":scope > a[role='button']");
                if (!toggle) return;
                var open = dropdown.classList.contains('open') || dropdown.classList.contains('dropdown-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            return;
        }

        best.classList.add('active-page');

        // Open ancestor dropdowns for the active item; leave other open menus alone
        var node = best.parentElement;
        while (node && node !== menu) {
            if (node.classList && node.classList.contains('dropdown')) {
                ensureDropdownOpen(node);
            }
            node = node.parentElement;
        }

        menu.querySelectorAll('.dropdown').forEach(function (dropdown) {
            var toggle = dropdown.querySelector(":scope > a[role='button']");
            if (!toggle) return;
            var open = dropdown.classList.contains('open') || dropdown.classList.contains('dropdown-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    function bindFormSubmitLoading(root) {
        (root || document).querySelectorAll('form').forEach(function (form) {
            if (form.dataset.crmSubmitBound === '1') return;
            form.dataset.crmSubmitBound = '1';
            form.addEventListener('submit', function () {
                if (form.classList.contains('needs-validation') && !form.checkValidity()) return;
                var btn = form.querySelector('[type="submit"]');
                if (btn && !btn.classList.contains('is-loading')) {
                    btn.classList.add('is-loading');
                    btn.disabled = true;
                }
            });
        });
    }

    function initDataTable(root) {
        if (typeof DataTable === 'undefined') return;
        var scope = root || document;
        var tableEl = scope.querySelector ? scope.querySelector('#dataTable') : document.getElementById('dataTable');
        if (!tableEl) return;
        if (tableEl.classList.contains('dataTable')) return;
        try {
            var dt = new DataTable(tableEl);
            dt.on('draw', function () {
                if (typeof Iconify !== 'undefined' && typeof Iconify.scan === 'function') {
                    Iconify.scan(tableEl);
                }
            });
        } catch (e) { /* ignore */ }
    }

    function initSummernote(root) {
        if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.summernote) return;
        var $ = window.jQuery;
        var $scope = root ? $(root) : $(document);
        $scope.find('.summernote').each(function () {
            var $el = $(this);
            if ($el.next('.note-editor').length) return;
            $el.summernote({
                placeholder: 'Write your content here...',
                tabsize: 2,
                height: 300,
                fontSizes: ['8', '10', '12', '14', '16', '18', '20'],
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    }

    function initValidation(root) {
        var scope = root || document;
        scope.querySelectorAll('.needs-validation').forEach(function (form) {
            if (form.dataset.crmValidationBound === '1') return;
            form.dataset.crmValidationBound = '1';
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }

    function initTableSearch(root) {
        var scope = root || document;
        scope.querySelectorAll('.um-table-search').forEach(function (input) {
            if (input.dataset.crmSearchBound === '1') return;
            input.dataset.crmSearchBound = '1';
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
    }

    function reinitPage(root, options) {
        options = options || {};
        var scope = root || document.querySelector('#admin-page-content') || document;
        if (typeof Iconify !== 'undefined' && typeof Iconify.scan === 'function') {
            try { Iconify.scan(scope); } catch (e) { /* ignore */ }
        }
        initDataTable(scope);
        initSummernote(scope);
        initValidation(scope);
        bindFormSubmitLoading(scope);
        initTableSearch(scope);
        if (options.syncSidebar !== false) {
            syncSidebarActive();
        }
    }

    /* Navigation loader — skip links handled by AdminPjax */
    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[href]');
        if (!link) return;
        if (window.AdminPjax && typeof window.AdminPjax.isEligibleAnchor === 'function' && window.AdminPjax.isEligibleAnchor(link)) {
            return;
        }
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
        reinitPage(document);

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
    });

    window.CrmUI = {
        showLoader: showLoader,
        hideLoader: hideLoader,
        reinitPage: reinitPage,
        syncSidebarActive: syncSidebarActive,
    };
})();
