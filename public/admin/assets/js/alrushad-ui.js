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

    /* CRM line items: document-level delegation so Add Item works after PJAX swaps. */
    function crmLineItemNextIndex(tbody) {
        var max = -1;
        tbody.querySelectorAll('input[name^="items["]').forEach(function (input) {
            var match = String(input.name || '').match(/^items\[(\d+)\]/);
            if (match) {
                max = Math.max(max, parseInt(match[1], 10));
            }
        });
        return max + 1;
    }

    function crmRecalcLineItemRow(row) {
        if (!row) return;
        var qty = parseFloat((row.querySelector('.item-qty') || {}).value);
        var price = parseFloat((row.querySelector('.item-price') || {}).value);
        if (!isFinite(qty) || qty < 0) qty = 0;
        if (!isFinite(price) || price < 0) price = 0;
        var totalCell = row.querySelector('.item-total');
        if (totalCell) {
            totalCell.textContent = (qty * price).toFixed(2);
        }
    }

    function crmAppendLineItemRow(tbody) {
        var index = crmLineItemNextIndex(tbody);
        var row = document.createElement('tr');
        row.className = 'line-item-row';
        row.innerHTML =
            '<td><input type="text" name="items[' + index + '][description]" class="form-control radius-8" required></td>' +
            '<td><input type="number" name="items[' + index + '][quantity]" class="form-control radius-8 item-qty" min="1" value="1" required></td>' +
            '<td><input type="number" step="0.01" name="items[' + index + '][unit_price]" class="form-control radius-8 item-price" min="0" value="0" required></td>' +
            '<td class="item-total fw-semibold">0.00</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-line-item" data-crm-remove-line-item aria-label="Remove item">&times;</button></td>';
        tbody.appendChild(row);
        return row;
    }

    if (!window.__crmLineItemsDelegated) {
        window.__crmLineItemsDelegated = true;

        document.addEventListener('click', function (e) {
            var addBtn = e.target.closest('[data-crm-add-line-item], #add-line-item');
            if (addBtn) {
                e.preventDefault();
                var shell = addBtn.closest('[data-crm-line-items]') || document;
                var table = shell.querySelector('#line-items-table') || document.getElementById('line-items-table');
                var tbody = table ? table.querySelector('tbody') : null;
                if (!tbody) return;
                crmAppendLineItemRow(tbody);
                return;
            }

            var removeBtn = e.target.closest('[data-crm-remove-line-item], .remove-line-item');
            if (!removeBtn) return;
            e.preventDefault();
            var row = removeBtn.closest('tr.line-item-row');
            var table = removeBtn.closest('#line-items-table') || document.getElementById('line-items-table');
            var tbody = table ? table.querySelector('tbody') : null;
            if (!row || !tbody) return;
            if (tbody.querySelectorAll('tr.line-item-row').length <= 1) return;
            row.remove();
        });

        document.addEventListener('input', function (e) {
            if (!e.target || !e.target.classList) return;
            if (!e.target.classList.contains('item-qty') && !e.target.classList.contains('item-price')) return;
            crmRecalcLineItemRow(e.target.closest('tr.line-item-row'));
        });
    }

    /* Navigation loader — skip PJAX-handled links and file-download links */
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

        var isDownload = false;
        if (window.AdminPjax && typeof window.AdminPjax.isDownloadLikeHref === 'function') {
            isDownload = window.AdminPjax.isDownloadLikeHref(href);
        } else {
            try {
                var probe = new URL(href, window.location.origin);
                var path = (probe.pathname || '').toLowerCase();
                isDownload = /\/export(\/|$)/i.test(path)
                    || /\/(pdf|failed-rows)(\/|$)/i.test(path)
                    || /\.(csv|xlsx|xls|pdf|zip)(\?|$)/i.test(path)
                    || /[?&]export=/i.test(probe.search || '');
            } catch (err) {
                isDownload = false;
            }
        }

        /* File downloads keep the current document mounted — never leave the full-screen loader on. */
        if (isDownload) {
            hideLoader();
            if (link.dataset.crmDownloadLock === '1') {
                e.preventDefault();
                return;
            }
            link.dataset.crmDownloadLock = '1';
            link.classList.add('is-loading');
            window.setTimeout(function () {
                link.dataset.crmDownloadLock = '0';
                link.classList.remove('is-loading');
            }, 1500);
            return;
        }

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

        initCrmConfirmModal();
    });

    /**
     * Reusable CRM confirm modal (Bootstrap).
     * Forms opt in with [data-crm-confirm] + data-confirm-* attributes.
     * Modal DOM lives outside PJAX-replaced regions; handler is document-delegated once.
     */
    function initCrmConfirmModal() {
        if (window.__crmConfirmBound) return;
        window.__crmConfirmBound = true;

        var modalEl = document.getElementById('crmConfirmModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: true, keyboard: true, focus: true });
        var titleEl = modalEl.querySelector('[data-crm-confirm-title]');
        var messageEl = modalEl.querySelector('[data-crm-confirm-message]');
        var noteWrap = modalEl.querySelector('[data-crm-confirm-note-wrap]');
        var noteEl = modalEl.querySelector('[data-crm-confirm-note]');
        var iconWrap = modalEl.querySelector('[data-crm-confirm-icon-wrap]');
        var iconEl = modalEl.querySelector('[data-crm-confirm-icon]');
        var submitBtn = modalEl.querySelector('[data-crm-confirm-submit]');
        var pendingForm = null;
        var opener = null;

        var toneBtnClass = {
            info: 'btn-primary-600',
            success: 'btn-success',
            warning: 'btn-warning',
            danger: 'btn-danger',
            primary: 'btn-primary-600'
        };

        function resetSubmitButton() {
            if (!submitBtn) return;
            submitBtn.disabled = false;
            submitBtn.classList.remove('is-loading');
        }

        function applyTone(tone) {
            tone = tone || 'info';
            if (iconWrap) {
                iconWrap.className = 'crm-confirm-icon is-' + (tone === 'primary' ? 'info' : tone);
            }
            if (submitBtn) {
                submitBtn.className = 'btn radius-8 px-18 py-10 ' + (toneBtnClass[tone] || toneBtnClass.info);
            }
        }

        function openForForm(form) {
            pendingForm = form;
            opener = document.activeElement;

            var title = form.getAttribute('data-confirm-title') || 'Confirm action?';
            var message = form.getAttribute('data-confirm-message') || '';
            var note = form.getAttribute('data-confirm-note') || '';
            var label = form.getAttribute('data-confirm-label') || 'Confirm';
            var tone = form.getAttribute('data-confirm-tone') || 'info';
            var icon = form.getAttribute('data-confirm-icon') || 'solar:info-circle-linear';

            if (titleEl) titleEl.textContent = title;
            if (messageEl) messageEl.textContent = message;
            if (noteWrap && noteEl) {
                if (note) {
                    noteEl.textContent = note;
                    noteWrap.classList.remove('d-none');
                } else {
                    noteEl.textContent = '';
                    noteWrap.classList.add('d-none');
                }
            }
            if (iconEl) iconEl.setAttribute('icon', icon);
            if (submitBtn) submitBtn.textContent = label;
            applyTone(tone);
            resetSubmitButton();
            modal.show();
        }

        document.addEventListener('submit', function (event) {
            var form = event.target.closest('form[data-crm-confirm]');
            if (!form) return;
            if (form.getAttribute('data-crm-confirm-approved') === '1') {
                form.removeAttribute('data-crm-confirm-approved');
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            openForForm(form);
        }, true);

        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                if (!pendingForm || submitBtn.disabled) return;
                submitBtn.disabled = true;
                submitBtn.classList.add('is-loading');
                var form = pendingForm;
                pendingForm = null;
                form.setAttribute('data-crm-confirm-approved', '1');
                modal.hide();
                // Native submit() does not re-fire the submit event — avoids confirm loop.
                HTMLFormElement.prototype.submit.call(form);
            });
        }

        modalEl.addEventListener('hidden.bs.modal', function () {
            pendingForm = null;
            resetSubmitButton();
            if (opener && typeof opener.focus === 'function') {
                try { opener.focus(); } catch (err) { /* ignore */ }
            }
            opener = null;
        });
    }

    window.CrmUI = {
        showLoader: showLoader,
        hideLoader: hideLoader,
        reinitPage: reinitPage,
        syncSidebarActive: syncSidebarActive,
    };
})();
