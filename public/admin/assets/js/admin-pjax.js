/**
 * Admin progressive navigation (PJAX-style).
 *
 * Persistent (never replaced / never covered by overlay):
 *   - .sidebar / #sidebar-menu
 *   - .navbar-header
 *
 * Replaced:
 *   - #admin-page-content
 *   - #admin-page-styles
 *   - #admin-page-modals
 *   - #admin-page-scripts
 *
 * Evidence: #crm-page-loader is position:fixed; inset:0; z-index:99999 with blur.
 * Calling it during PJAX visually hides the sidebar/header. Do not use it here.
 */
(function () {
    'use strict';

    var CONTENT_SEL = '#admin-page-content';
    var STYLES_SEL = '#admin-page-styles';
    var MODALS_SEL = '#admin-page-modals';
    var SCRIPTS_SEL = '#admin-page-scripts';

    var abortController = null;
    var navigating = false;

    function isModifiedClick(event) {
        return event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey;
    }

    function isDownloadLike(url) {
        var path = (url.pathname || '').toLowerCase();
        if (/\.(csv|xlsx|xls|pdf|zip|docx?|png|jpe?g|gif|webp|svg)(\?|$)/i.test(path)) {
            return true;
        }
        if (/\/export(\/|$)/i.test(path) || /[?&]export=/i.test(url.search || '')) {
            return true;
        }
        return false;
    }

    function isLogoutUrl(url) {
        return /\/logout(\/|$)/i.test(url.pathname || '');
    }

    function isEligibleAnchor(anchor) {
        if (!anchor || anchor.nodeType !== 1) return false;
        if (anchor.closest('[data-admin-nav="full"]')) return false;
        if (anchor.getAttribute('data-admin-nav') === 'full') return false;
        if (anchor.getAttribute('data-full-reload') !== null) return false;
        if (anchor.hasAttribute('download')) return false;
        if (anchor.target && anchor.target.toLowerCase() !== '_self') return false;
        if (anchor.getAttribute('rel') && /\bexternal\b/i.test(anchor.getAttribute('rel'))) return false;

        var href = anchor.getAttribute('href');
        if (!href || href.charAt(0) === '#') return false;
        if (/^(mailto:|tel:|javascript:)/i.test(href)) return false;

        var url;
        try {
            url = new URL(href, window.location.origin);
        } catch (err) {
            return false;
        }

        if (url.origin !== window.location.origin) return false;
        if (!url.pathname.startsWith('/admin')) return false;
        if (isLogoutUrl(url)) return false;
        if (isDownloadLike(url)) return false;

        return true;
    }

    function isEligibleGetForm(form) {
        if (!form || form.nodeType !== 1) return false;
        if (form.getAttribute('data-admin-nav') === 'full') return false;
        if (form.getAttribute('data-full-reload') !== null) return false;
        if (form.closest('[data-admin-nav="full"]')) return false;

        var method = (form.getAttribute('method') || 'get').toLowerCase();
        if (method !== 'get') return false;
        if (form.querySelector('input[type="file"]')) return false;

        var action = form.getAttribute('action') || window.location.href;
        var url;
        try {
            url = new URL(action, window.location.origin);
        } catch (err) {
            return false;
        }

        if (url.origin !== window.location.origin) return false;
        if (!url.pathname.startsWith('/admin')) return false;
        if (isLogoutUrl(url) || isDownloadLike(url)) return false;

        return true;
    }

    function suppressFullScreenLoader() {
        if (window.CrmUI && typeof window.CrmUI.hideLoader === 'function') {
            window.CrmUI.hideLoader();
        }
        var loader = document.getElementById('crm-page-loader');
        if (loader) {
            loader.classList.remove('is-active');
            loader.setAttribute('aria-hidden', 'true');
        }
    }

    function setBusy(isBusy) {
        var content = document.querySelector(CONTENT_SEL);
        if (content) {
            content.setAttribute('aria-busy', isBusy ? 'true' : 'false');
        }
        document.documentElement.classList.toggle('admin-pjax-loading', isBusy);
        // Never allow the global overlay to cover persistent chrome during PJAX
        suppressFullScreenLoader();
    }

    function fullNavigate(url) {
        suppressFullScreenLoader();
        if (window.CrmUI && typeof window.CrmUI.showLoader === 'function') {
            window.CrmUI.showLoader();
        }
        window.location.assign(url);
    }

    function closeMobileSidebar() {
        var sidebar = document.querySelector('.sidebar');
        if (sidebar) sidebar.classList.remove('sidebar-open');
        document.body.classList.remove('overlay-active');
        var toggle = document.querySelector('.sidebar-toggle');
        if (!toggle) return;
        var isMobile = window.matchMedia('(max-width: 1199px)').matches;
        if (!isMobile) return;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open menu');
        toggle.setAttribute('title', 'Open menu');
        var icon = toggle.querySelector('.sidebar-toggle-icon');
        if (icon) icon.setAttribute('icon', 'solar:round-alt-arrow-right-linear');
    }

    function updateCsrf(doc) {
        var meta = doc.querySelector('meta[name="csrf-token"]');
        var current = document.querySelector('meta[name="csrf-token"]');
        if (meta && current && meta.getAttribute('content')) {
            current.setAttribute('content', meta.getAttribute('content'));
        }
    }

    function replaceHtml(target, source) {
        if (!target || !source) return false;
        target.innerHTML = source.innerHTML;
        return true;
    }

    function runScripts(scriptContainer, source) {
        if (!scriptContainer) return Promise.resolve();

        scriptContainer.innerHTML = '';
        if (!source) return Promise.resolve();

        var nodes = Array.prototype.slice.call(source.childNodes);
        var chain = Promise.resolve();

        nodes.forEach(function (node) {
            if (node.nodeType === Node.TEXT_NODE) {
                scriptContainer.appendChild(document.createTextNode(node.textContent || ''));
                return;
            }
            if (node.nodeType !== Node.ELEMENT_NODE) return;

            if (node.tagName !== 'SCRIPT') {
                scriptContainer.appendChild(node.cloneNode(true));
                return;
            }

            chain = chain.then(function () {
                return new Promise(function (resolve) {
                    var script = document.createElement('script');
                    Array.prototype.forEach.call(node.attributes || [], function (attr) {
                        if (attr.name === 'src') return;
                        script.setAttribute(attr.name, attr.value);
                    });

                    if (node.src) {
                        script.src = node.src;
                        script.onload = function () { resolve(); };
                        script.onerror = function () { resolve(); };
                        scriptContainer.appendChild(script);
                    } else {
                        script.text = node.textContent || '';
                        scriptContainer.appendChild(script);
                        resolve();
                    }
                });
            });
        });

        return chain;
    }

    function afterSwap() {
        var content = document.querySelector(CONTENT_SEL);
        if (window.CrmUI && typeof window.CrmUI.reinitPage === 'function') {
            window.CrmUI.reinitPage(content, { syncSidebar: false });
        }
        if (window.CrmUI && typeof window.CrmUI.syncSidebarActive === 'function') {
            window.CrmUI.syncSidebarActive();
        }
        closeMobileSidebar();

        try {
            var main = document.querySelector('.dashboard-main') || content;
            if (main && typeof main.scrollTo === 'function') {
                main.scrollTo(0, 0);
            } else {
                window.scrollTo(0, 0);
            }
        } catch (err) { /* ignore */ }
    }

    function parseDocument(html) {
        return new DOMParser().parseFromString(html, 'text/html');
    }

    function assertPersistentChrome(sidebarBefore, menuBefore, headerBefore) {
        var sidebarAfter = document.querySelector('.sidebar');
        var menuAfter = document.getElementById('sidebar-menu');
        var headerAfter = document.querySelector('.navbar-header');
        if (sidebarBefore && sidebarAfter && sidebarBefore !== sidebarAfter) {
            throw new Error('Sidebar node identity changed');
        }
        if (menuBefore && menuAfter && menuBefore !== menuAfter) {
            throw new Error('Sidebar menu node identity changed');
        }
        if (headerBefore && headerAfter && headerBefore !== headerAfter) {
            throw new Error('Header node identity changed');
        }
    }

    function navigate(url, options) {
        options = options || {};
        var push = options.push !== false;
        var absoluteUrl = typeof url === 'string' ? url : url.href;

        if (navigating && abortController) {
            try { abortController.abort(); } catch (err) { /* ignore */ }
        }

        var sidebarBefore = document.querySelector('.sidebar');
        var menuBefore = document.getElementById('sidebar-menu');
        var headerBefore = document.querySelector('.navbar-header');

        navigating = true;
        setBusy(true);
        abortController = typeof AbortController !== 'undefined' ? new AbortController() : null;

        var headers = {
            'Accept': 'text/html, application/xhtml+xml',
            'X-Requested-With': 'XMLHttpRequest',
            'X-Admin-Partial': '1',
        };

        var fetchOpts = {
            method: 'GET',
            headers: headers,
            credentials: 'same-origin',
            redirect: 'follow',
            cache: 'no-store',
        };
        if (abortController) fetchOpts.signal = abortController.signal;

        return fetch(absoluteUrl, fetchOpts)
            .then(function (response) {
                var contentType = (response.headers.get('content-type') || '').toLowerCase();
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                if (contentType && contentType.indexOf('text/html') === -1 && contentType.indexOf('application/xhtml') === -1) {
                    throw new Error('Non-HTML response');
                }
                return response.text().then(function (html) {
                    return { html: html, finalUrl: response.url || absoluteUrl };
                });
            })
            .then(function (payload) {
                var finalUrl = payload.finalUrl;
                try {
                    var finalParsed = new URL(finalUrl, window.location.origin);
                    if (!finalParsed.pathname.startsWith('/admin')) {
                        throw Object.assign(new Error('Left admin area'), { fallbackUrl: finalUrl });
                    }
                } catch (err) {
                    if (err && err.fallbackUrl) throw err;
                }

                var doc = parseDocument(payload.html);
                var nextContent = doc.querySelector(CONTENT_SEL);
                if (!nextContent) {
                    throw Object.assign(new Error('Missing admin page content'), { fallbackUrl: finalUrl });
                }

                var currentContent = document.querySelector(CONTENT_SEL);
                var currentStyles = document.querySelector(STYLES_SEL);
                var currentModals = document.querySelector(MODALS_SEL);
                var currentScripts = document.querySelector(SCRIPTS_SEL);

                if (!currentContent) {
                    throw Object.assign(new Error('Missing current content root'), { fallbackUrl: finalUrl });
                }

                replaceHtml(currentContent, nextContent);
                if (currentStyles) {
                    var nextStyles = doc.querySelector(STYLES_SEL);
                    if (nextStyles) replaceHtml(currentStyles, nextStyles);
                    else currentStyles.innerHTML = '';
                }
                if (currentModals) {
                    var nextModals = doc.querySelector(MODALS_SEL);
                    if (nextModals) replaceHtml(currentModals, nextModals);
                    else currentModals.innerHTML = '';
                }

                assertPersistentChrome(sidebarBefore, menuBefore, headerBefore);
                updateCsrf(doc);

                var titleEl = doc.querySelector('title');
                if (titleEl && titleEl.textContent) {
                    document.title = titleEl.textContent;
                }

                if (push) {
                    window.history.pushState({ adminPjax: true }, '', finalUrl);
                } else {
                    window.history.replaceState({ adminPjax: true }, '', finalUrl);
                }

                var nextScripts = doc.querySelector(SCRIPTS_SEL);
                return runScripts(currentScripts, nextScripts).then(function () {
                    afterSwap();
                    assertPersistentChrome(sidebarBefore, menuBefore, headerBefore);
                });
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') return;
                fullNavigate((err && err.fallbackUrl) || absoluteUrl);
            })
            .finally(function () {
                navigating = false;
                setBusy(false);
                abortController = null;
            });
    }

    function onClick(event) {
        if (isModifiedClick(event)) return;
        if (event.defaultPrevented) return;

        var anchor = event.target.closest('a[href]');
        if (!isEligibleAnchor(anchor)) return;

        event.preventDefault();
        navigate(anchor.href, { push: true });
    }

    function onSubmit(event) {
        var form = event.target;
        if (!isEligibleGetForm(form)) return;
        if (event.defaultPrevented) return;

        event.preventDefault();
        var action = form.getAttribute('action') || window.location.href;
        var url = new URL(action, window.location.origin);
        var data = new FormData(form);
        var params = new URLSearchParams(data);
        url.search = params.toString();
        navigate(url.toString(), { push: true });
    }

    function onPopState() {
        navigate(window.location.href, { push: false });
    }

    document.documentElement.classList.add('admin-pjax-enabled');
    suppressFullScreenLoader();

    document.addEventListener('click', onClick);
    document.addEventListener('submit', onSubmit);
    window.addEventListener('popstate', onPopState);

    if (!window.history.state || !window.history.state.adminPjax) {
        window.history.replaceState({ adminPjax: true }, '', window.location.href);
    }

    window.AdminPjax = {
        navigate: navigate,
        isEligibleAnchor: isEligibleAnchor,
        isEligibleGetForm: isEligibleGetForm,
    };
})();
