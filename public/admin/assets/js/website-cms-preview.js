/**
 * Website CMS → Live preview panel (connected website). One component for every CMS section.
 *
 * Loads the real website in an iframe at the device's real width, shrunk with CSS `zoom` (sharp
 * text; transform: scale only where zoom isn't supported) to Fit / 50 / 75 / 100 %:
 *   Draft  {site}/api/preview?cms_preview=TOKEN  (30-minute token tied to this admin; the website
 *          checks it with the CRM, then shows unpublished content)
 *   Live   {site}/api/preview/exit?path=…&frame=1 (published site)
 * Live updates (Draft): on every change in the current section the unsaved settings are pushed to
 * the CRM's preview store (POST website-cms/preview) and the frame is told to re-render:
 *   CMS → site  cms:preview-update {section, values} · cms:highlight {section, targets, label, changed}
 *               cms:scroll-to {section, targets}
 *               cms:mode {mode: "edit" | "browse"} (click-to-edit, Draft only)
 *   site → CMS  cms:ready {path, height} · cms:height {height} · cms:navigate {path}
 *               cms:edit / cms:ask-ai {section, field, item, label} (see docs/cms-click-to-edit.md)
 * Messages are accepted only from the connected website's origin AND from the preview frame itself.
 * Loads after the section modules, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const P = W && W.previewPanel;
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !P || !ext) return;

    const esc = (ext.shared && ext.shared.esc) || ((s) => String(s).replace(/[&<>"']/g, (c) => `&#${c.charCodeAt(0)};`));
    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module || 'dashboard';
    const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const DEVICES = {
        desktop: { label: 'Desktop', width: 1280, icon: 'solar:monitor-linear' },
        tablet: { label: 'Tablet', width: 768, icon: 'solar:tablet-linear' },
        phone: { label: 'Phone', width: 390, icon: 'solar:smartphone-linear' },
    };
    /** CMS section → the part of the page it changes (data-cms-section on the website). */
    const SECTIONS = {
        nav_menu: { label: 'Navigation menu', data: 'nav_menu', targets: ['header'] },
        website: { label: 'Announcement bar', data: 'website', targets: ['announcement', 'header-portal'] },
        footer_menu: { label: 'Footer', data: 'footer_menu', targets: ['footer'] },
        contact: { label: 'Contact details', data: 'contact', targets: ['footer-contact', 'whatsapp'] },
        social: { label: 'Social icons', data: 'social', targets: ['footer-social'] },
        branding: { label: 'Branding', data: 'branding', targets: ['header-logo', 'hero', 'copyright'] },
        theme: { label: 'Theme colours', data: 'theme', targets: [] },
        seo: { label: 'SEO', data: 'seo', targets: [], note: 'SEO changes don’t change how the page looks — see the Google preview in the form.' },
        analytics: { label: 'Analytics', data: 'analytics', targets: [] },
        custom_code: { label: 'Custom code', data: 'custom_code', targets: [] },
        site_forms: { label: 'Website forms', data: 'website', targets: [] },
        pages: { label: 'Pages', data: null, targets: [] },
    };
    /**
     * Click-to-edit: CMS section → field → the form control to focus (a selector, or a function of
     * the clicked item). Keep in step with the website's src/lib/cmsEditable.ts.
     */
    const norm = (v) => String(v || '').trim().toLowerCase();
    const byValue = (sel, item) => [...document.querySelectorAll(sel)].find((el) => norm(el.value) === norm(item));
    const FIELD_TARGETS = {
        website: { announcement: '#wcaText', portal_button_text: '#wcaPortalText', portal_url: '#wcaPortalUrl' },
        nav_menu: {
            cta_text: '#wcnCtaText',
            items: (item) => {
                const row = [...document.querySelectorAll('#wcnTree .wcn-row-main')].find((b) => norm(b.querySelector('.wcn-row-label')?.textContent) === norm(item));
                if (!row) return null;
                row.click(); // selects the link; its editor opens below the tree
                return document.querySelector('#wcnLabel');
            },
        },
        footer_menu: {
            heading: '#wcfLine1', description: '#wcfText',
            group: (item) => byValue('[data-col-title]', item),
            items: (item) => byValue('[data-link-label]', item),
        },
        contact: { address: '#wccAddress', phone: '#wccPhone', email: '#wccEmail', whatsapp_enabled: '#wccWaSwitch' },
        social: { '*': (item, field) => document.getElementById(`wcsUrl-${field}`) },
        branding: { short_name: '#wcbShort', company_name: '#wcbName', tagline: '#wcbTagline', copyright: '#wcbCopy', website_logo: '#wcbPick-logo' },
    };
    const ZOOMS = { fit: 'Fit', 0.5: '50%', 0.75: '75%', 1: '100%' };
    const ZOOM_OK = !!(window.CSS && CSS.supports && CSS.supports('zoom', '0.5'));
    const MIN_W = 380;
    const READY_TIMEOUT = 10000;
    const PUSH_DEBOUNCE = 250;
    const store = {
        get(k, d) { try { const v = localStorage.getItem('wcpv:' + k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } },
        set(k, v) { try { localStorage.setItem('wcpv:' + k, JSON.stringify(v)); } catch (e) { /* storage blocked */ } },
    };

    const state = {
        open: store.get('open', true),
        width: store.get('width', 600),
        zoom: ZOOMS[store.get('zoom', 'fit')] ? String(store.get('zoom', 'fit')) : 'fit',
        clickEdit: store.get('clickEdit', true),
        expanded: false,
        mode: 'draft',
        device: store.get('device', 'desktop'),
        path: '/',
        pathFromSection: true,
        phase: P.connected ? 'loading' : 'disconnected', // loading | ready | error | disconnected
        lastPushed: null,
        overlay: false,
    };
    let aside;
    let layout;
    let frame;
    let readyTimer = null;
    let pushTimer = null;
    let loadSeq = 0;

    /* ───────────────────────── data helpers ───────────────────────── */

    const data = () => (ext.lastApi ? ext.lastApi.getData() : W.data) || W.data;
    const section = () => SECTIONS[activeModule()] || { label: '', data: null, targets: [] };

    /** Count leaf fields that differ between draft and published (null and '' count as equal). */
    function diffCount(a, b) {
        const norm = (v) => (v === null || v === undefined ? '' : v);
        a = norm(a); b = norm(b);
        if (typeof a !== 'object' || typeof b !== 'object' || a === null || b === null) return String(a) === String(b) ? 0 : 1;
        const keys = new Set([...Object.keys(a), ...Object.keys(b)]);
        let n = 0;
        keys.forEach((k) => { n += diffCount(a[k], b[k]); });
        return n;
    }

    function changes() {
        const s = section();
        if (activeModule() === 'pages') {
            const pages = (ext.getPages ? ext.getPages() : H.pages) || [];
            const p = pages.find((x) => x.draft && x.draft.path === state.path);
            return p && (p.has_changes || !p.is_published) ? 1 : 0;
        }
        if (!s.data || !P.published) return 0;
        return diffCount((data() || {})[s.data], P.published[s.data]);
    }

    const host = () => { try { return new URL(P.base).host; } catch (e) { return ''; } };

    /* ───────────────────────── layout / collapse / resize ───────────────────────── */

    function maxWidth() { return Math.max(MIN_W, Math.floor(window.innerWidth * 0.6)); }

    function applyLayout() {
        const narrow = window.innerWidth < 900;
        layout.classList.toggle('wcpv-narrow', narrow);
        aside.classList.toggle('is-collapsed', !state.open && !narrow);
        aside.classList.toggle('is-overlay', narrow && state.overlay);
        document.body.classList.toggle('wcpv-overlay-open', narrow && state.overlay);
        // The floating "Ask AI" button steps aside while the panel (with its own Ask AI button) is open.
        document.body.classList.toggle('wcpv-panel-open', state.open && !narrow);
        const w = Math.min(Math.max(state.width, MIN_W), maxWidth());
        layout.style.setProperty('--wcpv-w', (state.open && !narrow ? w : 64) + 'px');
        const handle = aside.querySelector('.wcpv-resize');
        if (handle) { handle.setAttribute('aria-valuenow', String(w)); handle.setAttribute('aria-valuemax', String(maxWidth())); }
        renderFloating();
        requestAnimationFrame(fit);
    }

    function setOpen(open) {
        state.open = open;
        store.set('open', open);
        applyLayout();
        if (open && state.phase !== 'disconnected' && !frame) load();
        if (open) aside.querySelector('[data-act="hide"]')?.focus(); else aside.querySelector('.wcpv-show')?.focus();
    }

    function bindResize() {
        const handle = aside.querySelector('.wcpv-resize');
        const setW = (w) => {
            state.width = Math.round(Math.min(Math.max(w, MIN_W), maxWidth()));
            store.set('width', state.width);
            applyLayout();
        };
        handle.addEventListener('pointerdown', (e) => {
            e.preventDefault();
            handle.setPointerCapture(e.pointerId);
            const startX = e.clientX;
            const startW = aside.getBoundingClientRect().width;
            document.body.classList.add('wcpv-resizing');
            const move = (ev) => setW(startW + (startX - ev.clientX));
            const up = () => {
                document.body.classList.remove('wcpv-resizing');
                handle.removeEventListener('pointermove', move);
                handle.removeEventListener('pointerup', up);
            };
            handle.addEventListener('pointermove', move);
            handle.addEventListener('pointerup', up);
        });
        handle.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') { e.preventDefault(); setW(state.width + 40); }
            if (e.key === 'ArrowRight') { e.preventDefault(); setW(state.width - 40); }
        });
    }

    /* ───────────────────────── render ───────────────────────── */

    function render() {
        aside.className = 'wcm-preview wcpv';
        aside.setAttribute('aria-label', 'Live preview');
        aside.innerHTML = `
            <div class="wcpv-resize" role="separator" aria-orientation="vertical" aria-label="Resize the preview" tabindex="0" aria-valuemin="${MIN_W}"><span></span></div>
            <div class="wcpv-collapsed">
                <button type="button" class="wcpv-show" aria-label="Show preview" title="Show preview">${icon('solar:eye-linear')}<span class="wcpv-show-dot" hidden></span></button>
                <span class="wcpv-vertical" aria-hidden="true">Show preview</span>
            </div>
            <div class="wcpv-main">
                <div class="wcpv-toolbar">
                    <div class="wcpv-row">
                        <h2 class="wcpv-title">Preview</h2>
                        <span class="wcpv-chip" id="wcpvChip"></span>
                        <span class="wcpv-spacer"></span>
                        ${ext.assistant ? `<button type="button" class="wcpv-ask" data-act="ask" title="Ask the AI assistant">${icon('solar:magic-stick-3-bold')}<span>Ask AI</span></button>` : ''}
                        <button type="button" class="wcpv-icon" data-act="refresh" aria-label="Refresh preview" title="Refresh">${icon('solar:refresh-linear')}</button>
                        <button type="button" class="wcpv-icon" data-act="open" aria-label="Open preview in a new tab" title="Open in new tab">${icon('solar:square-arrow-right-up-linear')}</button>
                        <button type="button" class="wcpv-icon" data-act="expand" aria-label="Expand the preview" title="Expand">${icon('solar:maximize-square-linear')}</button>
                        <button type="button" class="wcpv-icon wcpv-hide-btn" data-act="hide" aria-label="Hide preview" title="Hide preview">${icon('solar:sidebar-minimalistic-linear')}</button>
                        <button type="button" class="wcpv-icon wcpv-close-btn" data-act="close" aria-label="Close preview" title="Close">${icon('solar:close-circle-linear')}</button>
                    </div>
                    <div class="wcpv-row wcpv-row--2">
                        <label class="wcpv-page">${icon('solar:document-linear')}<span class="visually-hidden">Page to preview</span><select id="wcpvPage"></select></label>
                        <div class="wcpv-seg" role="group" aria-label="Which version">
                            <button type="button" data-mode="draft" aria-pressed="true">Your draft</button>
                            <button type="button" data-mode="live" aria-pressed="false">Live site</button>
                        </div>
                        <div class="wcpv-seg wcpv-seg--icons" role="group" aria-label="Device">
                            ${Object.entries(DEVICES).map(([k, d]) => `<button type="button" data-device="${k}" aria-pressed="${state.device === k}" aria-label="${d.label} (${d.width} px)" title="${d.label} · ${d.width} px">${icon(d.icon)}</button>`).join('')}
                        </div>
                    </div>
                    <div class="wcpv-row wcpv-row--3">
                        <div class="wcpv-seg" role="group" aria-label="What clicking the preview does" id="wcpvClickMode">
                            <button type="button" data-click="edit" aria-pressed="${state.clickEdit}" title="Click any outlined part of the page to edit it">${icon('solar:cursor-linear')}Click to edit</button>
                            <button type="button" data-click="browse" aria-pressed="${!state.clickEdit}" title="Click links to move around the website">Browse</button>
                        </div>
                        <span class="wcpv-spacer"></span>
                        <label class="wcpv-zoom">${icon('solar:magnifer-zoom-in-linear')}<span class="visually-hidden">Zoom</span>
                            <select id="wcpvZoom">${Object.entries(ZOOMS).map(([v, l]) => `<option value="${v}" ${state.zoom === v ? 'selected' : ''}>${l}</option>`).join('')}</select>
                        </label>
                    </div>
                </div>
                <div class="wcpv-hint" id="wcpvHint" hidden></div>
                <div class="wcpv-stage" id="wcpvStage"></div>
                <div class="wcpv-status">
                    <span id="wcpvSize"></span>
                    <span class="wcpv-live" id="wcpvLive"></span>
                </div>
            </div>`;

        aside.querySelector('.wcpv-show').addEventListener('click', () => (window.innerWidth < 900 ? openOverlay() : setOpen(true)));
        aside.querySelector('[data-act="hide"]').addEventListener('click', () => setOpen(false));
        aside.querySelector('[data-act="close"]').addEventListener('click', () => (state.expanded ? setExpanded(false) : closeOverlay()));
        aside.querySelector('[data-act="expand"]').addEventListener('click', () => setExpanded(!state.expanded));
        aside.querySelector('[data-act="ask"]')?.addEventListener('click', () => ext.assistant && ext.assistant.open(''));
        aside.querySelectorAll('[data-click]').forEach((b) => b.addEventListener('click', () => setClickEdit(b.dataset.click === 'edit')));
        aside.querySelector('#wcpvZoom').addEventListener('change', (e) => { state.zoom = e.target.value; store.set('zoom', state.zoom); fit(); renderChrome(); });
        aside.querySelector('[data-act="refresh"]').addEventListener('click', () => load());
        aside.querySelector('[data-act="open"]').addEventListener('click', openInTab);
        aside.querySelectorAll('[data-mode]').forEach((b) => b.addEventListener('click', () => setMode(b.dataset.mode)));
        aside.querySelectorAll('[data-device]').forEach((b) => b.addEventListener('click', () => setDevice(b.dataset.device)));
        aside.querySelector('#wcpvPage').addEventListener('change', (e) => { state.path = e.target.value; state.pathFromSection = false; load(); });
        bindResize();
        renderPages();
        renderStage();
        renderChrome();
    }

    function renderPages() {
        const select = aside.querySelector('#wcpvPage');
        const pages = (ext.getPages ? ext.getPages() : H.pages) || [];
        const seen = new Set();
        const options = pages
            .filter((p) => p.draft && p.draft.path && !seen.has(p.draft.path) && seen.add(p.draft.path))
            .map((p) => `<option value="${esc(p.draft.path)}">${esc(p.draft.title || p.draft.path)} (${esc(p.draft.path)})${p.is_published ? '' : ' · draft'}</option>`);
        if (!seen.has('/')) options.unshift('<option value="/">Home (/)</option>');
        if (!seen.has(state.path)) options.push(`<option value="${esc(state.path)}">${esc(state.path)}</option>`);
        select.innerHTML = options.join('');
        select.value = state.path;
    }

    /** Toolbar chip, hint bar, status strip, collapsed dot — cheap, safe to call often. */
    function renderChrome() {
        if (!aside) return;
        const n = changes();
        const chip = aside.querySelector('#wcpvChip');
        if (state.mode === 'live') { chip.className = 'wcpv-chip is-live'; chip.textContent = 'Live site'; }
        else if (n) { chip.className = 'wcpv-chip is-draft'; chip.textContent = `Draft · ${n} change${n === 1 ? '' : 's'}`; }
        else { chip.className = 'wcpv-chip is-clean'; chip.textContent = 'Draft · no changes'; }

        const s = section();
        const hint = aside.querySelector('#wcpvHint');
        // Always shown, fixed height (CSS): the preview below never changes size, so it never jumps.
        let html;
        let key;
        if (state.mode === 'draft' && state.clickEdit && s.targets.length) {
            key = `outline:${s.label}`;
            html = `<span class="wcpv-hint-mark" aria-hidden="true"></span><span><strong>${esc(s.label)}</strong> is outlined. Click any part of the page to edit it.</span><button type="button" class="wcpv-jump">Jump to it ${icon('solar:arrow-down-linear')}</button>`;
        } else if (state.mode === 'draft' && s.note) {
            key = `note:${s.note}`;
            html = `${icon('solar:info-circle-linear')}<span>${esc(s.note)}</span>`;
        } else if (state.mode === 'draft' && state.clickEdit) {
            key = 'edit';
            html = `${icon('solar:cursor-linear')}<span>Click any outlined part of the page to edit it.</span>`;
        } else if (state.mode === 'draft') {
            key = 'browse';
            html = `${icon('solar:link-round-linear')}<span>Browsing — links take you around the website.</span>`;
        } else {
            key = 'live';
            html = `${icon('solar:global-linear')}<span>This is the live website, exactly as visitors see it.</span>`;
        }
        hint.hidden = false;
        if (hint.dataset.key !== key) {
            hint.dataset.key = key;
            hint.innerHTML = html;
            hint.querySelector('.wcpv-jump')?.addEventListener('click', () => post({ type: 'cms:scroll-to', section: activeModule(), targets: section().targets }));
        }

        const d = DEVICES[state.device];
        aside.querySelector('#wcpvSize').textContent = `${d.label} · ${d.width} px wide · shown at ${Math.round(currentScale() * 100)}%`;
        const clickSeg = aside.querySelector('#wcpvClickMode');
        clickSeg.hidden = state.mode !== 'draft';
        clickSeg.querySelectorAll('[data-click]').forEach((b) => b.setAttribute('aria-pressed', String((b.dataset.click === 'edit') === state.clickEdit)));
        aside.querySelector('[data-act="expand"]').setAttribute('aria-pressed', String(state.expanded));
        aside.querySelector('#wcpvLive').innerHTML = state.mode === 'draft'
            ? '<span class="wcpv-dot is-ok" aria-hidden="true"></span>Updates as you type'
            : '<span class="wcpv-dot is-ok" aria-hidden="true"></span>Showing what visitors see now';
        aside.querySelectorAll('[data-mode]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.mode === state.mode)));
        aside.querySelectorAll('[data-device]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.device === state.device)));
        const dot = aside.querySelector('.wcpv-show-dot');
        dot.hidden = !(n || W.hasUnpublished);
        renderFloating();
    }

    function renderStage() {
        const stage = aside.querySelector('#wcpvStage');
        if (state.phase === 'disconnected') {
            frame = null;
            stage.innerHTML = card('solar:link-broken-linear', 'Connect your website to see a preview', 'Once your website is connected, you’ll see it here as you edit.',
                '<button type="button" class="wcpv-btn wcpv-btn--primary" data-go="connection">Website connection</button>', false);
            stage.querySelector('[data-go]').addEventListener('click', () => goConnection());
            return;
        }
        const d = DEVICES[state.device];
        stage.innerHTML = `
            <div class="wcpv-device wcpv-device--${state.device}" id="wcpvDevice">
                ${state.device === 'desktop' ? `<div class="wcpv-browserbar"><span class="wcpv-dots" aria-hidden="true"><i></i><i></i><i></i></span><span class="wcpv-address">${icon('solar:lock-keyhole-minimalistic-linear')}<span id="wcpvAddress"></span></span></div>` : ''}
                <div class="wcpv-viewport" id="wcpvViewport">
                    <div class="wcpv-scaler" id="wcpvScaler">
                        <iframe id="wcpvFrame" title="Website preview" sandbox="allow-scripts allow-same-origin allow-popups allow-forms" referrerpolicy="strict-origin-when-cross-origin" style="width:${d.width}px"></iframe>
                    </div>
                    <div class="wcpv-skeleton" id="wcpvSkeleton" role="status" aria-label="Loading preview">
                        <div class="wcpv-sk-bar"></div><div class="wcpv-sk-hero"></div><div class="wcpv-sk-line"></div><div class="wcpv-sk-line is-short"></div>
                        <div class="wcpv-sk-cards"><i></i><i></i><i></i></div>
                        <p>Loading your website…</p>
                    </div>
                    <div class="wcpv-error-wrap" id="wcpvError" hidden></div>
                </div>
            </div>`;
        frame = stage.querySelector('#wcpvFrame');
        fit();
    }

    function card(ic, title, text, actions, isError) {
        return `<div class="wcpv-card" ${isError ? 'role="alert"' : ''}>
            <span class="wcpv-card-icon ${isError ? 'is-error' : ''}">${icon(ic)}</span>
            <h3>${esc(title)}</h3><p>${esc(text)}</p>
            <div class="wcpv-card-actions">${actions}</div></div>`;
    }

    function showError() {
        state.phase = 'error';
        const box = aside.querySelector('#wcpvError');
        if (!box) return;
        aside.querySelector('#wcpvSkeleton').hidden = true;
        box.hidden = false;
        box.innerHTML = card('solar:wi-fi-router-minimalistic-linear', 'We couldn’t load your website',
            `${host() || 'Your website'} didn’t respond. Your changes are still saved as a draft — nothing is lost.`,
            '<button type="button" class="wcpv-btn wcpv-btn--primary" data-retry>Try again</button><button type="button" class="wcpv-btn" data-go="connection">Check website connection</button>', true);
        box.querySelector('[data-retry]').addEventListener('click', () => load());
        box.querySelector('[data-go]').addEventListener('click', () => goConnection());
        renderChrome();
        diagnose(box);
    }

    // Ask the CRM to test the connection both ways and, if a setting is wrong, say which one.
    function diagnose(box) {
        if (!H.routes || !H.routes.test) return;
        const p = box.querySelector('.wcpv-card p');
        if (p) p.textContent = 'Checking the connection to your website…';
        fetch(H.routes.test, { method: 'POST', headers: { 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' } })
            .then((r) => r.json())
            .then((res) => {
                if (state.phase !== 'error' || !p || !p.isConnected) return;
                p.textContent = res && !res.success && res.message
                    ? `${res.message} Your changes are still saved as a draft — nothing is lost.`
                    : `${host() || 'Your website'} didn’t respond in time. Your changes are still saved as a draft — nothing is lost.`;
            })
            .catch(() => {
                if (state.phase === 'error' && p && p.isConnected) {
                    p.textContent = `${host() || 'Your website'} didn’t respond. Your changes are still saved as a draft — nothing is lost.`;
                }
            });
    }

    function goConnection() {
        closeOverlay();
        document.querySelector('.wcm-nav-item[data-module="connection"]')?.click();
    }

    /* ───────────────────────── scaling ───────────────────────── */

    let shownScale = 1;

    function currentScale() { return shownScale; }

    /**
     * Device-width iframe shrunk to the chosen zoom. CSS `zoom` re-lays the page out at the smaller
     * size, so text is rasterised sharp (transform: scale() blurs it at fractional sizes). The zoom is
     * rounded down to 2 decimals. Nothing between the iframe and the page may use transform, filter
     * or blur — they would make the browser rasterise the frame as a bitmap again.
     */
    function fit() {
        if (!aside || !frame) return;
        const vp = aside.querySelector('#wcpvViewport');
        const scaler = aside.querySelector('#wcpvScaler');
        const stage = aside.querySelector('#wcpvStage');
        const device = aside.querySelector('#wcpvDevice');
        const d = DEVICES[state.device];
        // Width: the stage minus its padding and the device's bezel.
        const bezel = state.device === 'desktop' ? 0 : (state.device === 'tablet' ? 24 : 20);
        const avail = Math.max(120, stage.clientWidth - 28 - bezel);
        const fitZoom = Math.max(0.1, Math.floor(Math.min(1, avail / d.width) * 100) / 100);
        const z = state.zoom === 'fit' ? fitZoom : Number(state.zoom);
        shownScale = z;
        device.style.width = Math.round(d.width * z + bezel) + 'px';
        // Height: fill the stage; the website scrolls inside its frame.
        const chrome = state.device === 'desktop' ? 30 : 0;
        const availH = Math.max(200, stage.clientHeight - 28 - bezel - chrome);
        const frameH = Math.round(availH / z);
        frame.style.width = d.width + 'px';
        frame.style.height = frameH + 'px';
        if (ZOOM_OK) {
            frame.style.zoom = String(z);
            frame.style.transform = '';
        } else {
            frame.style.zoom = '';
            frame.style.transform = `scale(${z})`;
        }
        scaler.style.width = Math.round(d.width * z) + 'px';
        scaler.style.height = Math.round(frameH * z) + 'px';
        vp.style.height = Math.round(frameH * z) + 'px';
        stage.classList.toggle('is-scrolling', z > fitZoom);
        const size = aside.querySelector('#wcpvSize');
        if (size) size.textContent = `${d.label} · ${d.width} px wide · shown at ${Math.round(z * 100)}%`;
    }

    /* ───────────────────────── expand / click-to-edit ───────────────────────── */

    function setExpanded(on) {
        state.expanded = on;
        aside.classList.toggle('is-expanded', on);
        document.body.classList.toggle('wcpv-expanded', on);
        let backdrop = document.getElementById('wcpvBackdrop');
        if (on && !backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'wcpvBackdrop';
            backdrop.className = 'wcpv-backdrop';
            backdrop.addEventListener('click', () => setExpanded(false));
            document.body.appendChild(backdrop);
        }
        if (backdrop) backdrop.hidden = !on;
        if (on && (!state.open || window.innerWidth < 900)) { if (!frame || state.phase !== 'ready') load(); }
        renderChrome();
        requestAnimationFrame(fit);
        aside.querySelector(on ? '[data-act="close"]' : '[data-act="expand"]')?.focus();
    }

    function setClickEdit(on) {
        state.clickEdit = on;
        store.set('clickEdit', on);
        sendMode();
        sendHighlight();
        renderChrome();
    }

    function sendMode() {
        post({ type: 'cms:mode', mode: state.mode === 'draft' && state.clickEdit ? 'edit' : 'browse' });
    }

    const clean = (v, max) => (typeof v === 'string' ? v.replace(/[\u0000-\u001f]/g, '').slice(0, max) : '');

    /** Ask AI from the hover chip: open the assistant with "Change the {label}: ". */
    function askAi(label) {
        if (!ext.assistant) return;
        const l = clean(label, 60).replace(/^([A-Z])(?![A-Z])/, (c) => c.toLowerCase());
        if (state.expanded) setExpanded(false);
        ext.assistant.open(l ? `Change the ${l}: ` : '');
    }

    /** cms:edit: open that CMS section, then scroll to, focus and flash the field. */
    function goToField(section, field, item) {
        const fields = FIELD_TARGETS[section];
        const nav = document.querySelector(`.wcm-nav-item[data-module="${section}"]`);
        if (!fields || !nav) return;
        if (state.expanded) setExpanded(false);
        if (state.overlay) closeOverlay();
        const switched = activeModule() !== section;
        if (switched) nav.click(); // the CMS keeps unsaved edits as a draft
        const pane = document.getElementById('wcmEditorBody');
        let tries = 0;
        const attempt = () => {
            const how = fields[field] || fields['*'];
            let el = null;
            try { el = typeof how === 'function' ? how(item, field) : (how ? document.querySelector(how) : null); } catch (e) { el = null; }
            if (!el && ++tries < 15) { setTimeout(attempt, 80); return; }
            reveal(el || pane?.querySelector('.wcp-card') || null, pane, switched);
        };
        requestAnimationFrame(attempt);
    }

    function reveal(el, pane, switched) {
        if (!el) return;
        // Scroll only the editor pane (scrollIntoView would also move the whole admin page). Skip it when
        // the field is already comfortably in view; after a section switch go there at once.
        if (pane && pane.contains(el)) {
            const pr = pane.getBoundingClientRect();
            const r = el.getBoundingClientRect();
            const inView = r.top >= pr.top + 24 && r.bottom <= pr.bottom - 24;
            if (!inView) {
                const top = Math.max(0, pane.scrollTop + r.top - pr.top - 96);
                const smooth = !switched && !reducedMotion() && Math.abs(top - pane.scrollTop) < 600;
                pane.scrollTo({ top, behavior: smooth ? 'smooth' : 'auto' });
            }
        }
        if (typeof el.focus === 'function') el.focus({ preventScroll: true });
        const ring = el.closest('.wcp-field, .wcf-link, .wcs-row, .wcn-panel, .wcb-upload') || el;
        ring.classList.remove('wcpv-flash');
        void ring.offsetWidth; // restart the animation
        ring.classList.add('wcpv-flash');
        setTimeout(() => ring.classList.remove('wcpv-flash'), 1300);
    }

    /* ───────────────────────── loading ───────────────────────── */

    function urlFor(path, mode) {
        return fetch(`${H.routes.previewUrl}?path=${encodeURIComponent(path)}&mode=${mode}`, { headers: { Accept: 'application/json' } })
            .then((r) => (r.ok ? r.json() : Promise.reject(new Error('url'))))
            .then((res) => res.url || Promise.reject(new Error('url')));
    }

    function load() {
        if (state.phase === 'disconnected' || !state.open && window.innerWidth >= 900 && !state.overlay) return Promise.resolve();
        const seq = ++loadSeq;
        state.phase = 'loading';
        if (!frame) renderStage();
        aside.querySelector('#wcpvSkeleton').hidden = false;
        aside.querySelector('#wcpvError').hidden = true;
        const addr = aside.querySelector('#wcpvAddress');
        if (addr) addr.textContent = `${host()}${state.path === '/' ? '/' : state.path}${state.mode === 'draft' ? ' · draft' : ''}`;
        renderChrome();
        clearTimeout(readyTimer);
        readyTimer = setTimeout(() => { if (seq === loadSeq && state.phase === 'loading') showError(); }, READY_TIMEOUT);
        // Draft: make sure the website sees the latest unsaved settings before it renders.
        const before = state.mode === 'draft' ? pushDraft() : Promise.resolve();
        return before.then(() => urlFor(state.path, state.mode)).then((url) => {
            if (seq !== loadSeq) return;
            frame.src = url;
        }).catch(() => { if (seq === loadSeq) showError(); });
    }

    function setMode(mode) {
        if (mode === state.mode) return;
        state.mode = mode;
        load();
    }

    function setDevice(device) {
        if (!DEVICES[device] || device === state.device) return;
        state.device = device;
        store.set('device', device);
        frame = null;
        renderStage();
        load();
    }

    function openInTab() {
        const win = window.open('about:blank', '_blank');
        urlFor(state.path, state.mode).then((url) => {
            if (win) { win.opener = null; win.location.href = url; } else window.open(url, '_blank', 'noopener');
        }).catch(() => { if (win) win.close(); });
    }

    /* ───────────────────────── messages ───────────────────────── */

    function post(message) {
        if (frame && frame.contentWindow && P.origin && state.phase === 'ready') frame.contentWindow.postMessage(message, P.origin);
    }

    function sendHighlight() {
        const s = section();
        // Browse and Live: nothing is outlined.
        post({ type: 'cms:highlight', section: activeModule(), targets: state.mode === 'draft' && state.clickEdit ? s.targets : [], label: s.label, changed: changes() > 0 });
    }

    window.addEventListener('message', (e) => {
        // Only the connected website, and only our own preview frame.
        if (!P.origin || e.origin !== P.origin || !frame || e.source !== frame.contentWindow) return;
        const m = e.data;
        if (!m || typeof m !== 'object') return;
        if (m.type === 'cms:ready') {
            state.phase = 'ready';
            clearTimeout(readyTimer);
            aside.querySelector('#wcpvSkeleton').hidden = true;
            aside.querySelector('#wcpvError').hidden = true;
            if (typeof m.path === 'string' && m.path.startsWith('/') && m.path !== state.path) {
                state.path = m.path;
                renderPages();
            }
            sendHighlight();
            sendMode();
            renderChrome();
        } else if ((m.type === 'cms:edit' || m.type === 'cms:ask-ai') && state.mode === 'draft' && state.clickEdit) {
            const sec = clean(m.section, 40);
            if (!FIELD_TARGETS[sec]) return;
            if (m.type === 'cms:ask-ai') askAi(m.label);
            else goToField(sec, clean(m.field, 60), clean(m.item, 120));
        } else if (m.type === 'cms:navigate' && typeof m.path === 'string' && /^\/(?!\/)/.test(m.path)) {
            state.path = m.path;
            state.pathFromSection = false;
            renderPages();
            load();
        }
        // cms:height is accepted but not needed: the website scrolls inside its frame.
    });

    /* ───────────────────────── live updates (Draft) ───────────────────────── */

    function pushDraft() {
        return fetch(W.routes.preview, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' },
            body: JSON.stringify({ data: data() }),
        }).catch(() => null);
    }

    function scheduleUpdate() {
        renderChrome();
        clearTimeout(pushTimer);
        pushTimer = setTimeout(sendUpdate, PUSH_DEBOUNCE);
    }

    /** Push the unsaved settings, then let the website re-render — never a frame reload. */
    function sendUpdate() {
        const s = section();
        const snapshot = JSON.stringify(s.data ? (data() || {})[s.data] : null);
        renderChrome();
        if (state.mode !== 'draft' || state.phase !== 'ready') return;
        if (activeModule() !== 'pages' && snapshot === state.lastPushed) { sendHighlight(); return; }
        state.lastPushed = snapshot;
        pushDraft().then(() => {
            post({ type: 'cms:preview-update', section: activeModule(), values: s.data ? (data() || {})[s.data] : {} });
            sendHighlight();
        });
    }

    function onSectionChange() {
        const mod = activeModule();
        // Opening a section changes nothing on the website: remember its current settings so the next
        // click in the form doesn't push them again and re-render the preview (a visible jerk).
        const s = section();
        state.lastPushed = JSON.stringify(s.data ? (data() || {})[s.data] : null);
        // Footer / navigation / announcement / branding … are on every page: show Home.
        if (mod !== 'pages' && state.pathFromSection && state.path !== '/') { state.path = '/'; renderPages(); load(); return; }
        renderChrome();
        sendHighlight();
    }

    /* ───────────────────────── small screens ───────────────────────── */

    function renderFloating() {
        let btn = document.getElementById('wcpvFloating');
        const narrow = window.innerWidth < 900;
        if (!narrow || state.overlay) { if (btn) btn.hidden = true; return; }
        if (!btn) {
            btn = document.createElement('button');
            btn.type = 'button';
            btn.id = 'wcpvFloating';
            btn.className = 'wcpv-floating';
            btn.innerHTML = `${icon('solar:eye-linear')}<span>Show preview</span>`;
            btn.addEventListener('click', openOverlay);
            document.body.appendChild(btn);
        }
        btn.hidden = false;
    }

    function openOverlay() {
        state.overlay = true;
        applyLayout();
        if (!frame || state.phase !== 'ready') load();
        aside.querySelector('[data-act="close"]').focus();
    }

    function closeOverlay() {
        if (!state.overlay) return;
        state.overlay = false;
        applyLayout();
        document.getElementById('wcpvFloating')?.focus();
    }

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (state.expanded) setExpanded(false);
        else if (state.overlay) closeOverlay();
    });

    /* ───────────────────────── wiring ───────────────────────── */

    ext.preview = {
        /** Pages: show the page being edited. */
        showPath(path) {
            if (typeof path !== 'string' || !path.startsWith('/')) return;
            state.pathFromSection = false;
            if (path === state.path && state.phase === 'ready') { scheduleUpdate(); return; }
            state.path = path;
            renderPages();
            load();
        },
        /** After a section saved its draft: re-render in place. */
        refresh() { state.lastPushed = null; scheduleUpdate(); },
        /** AI assistant "Show in preview": re-render, then outline and scroll to that section. */
        focus(key) {
            const s = SECTIONS[key];
            this.refresh();
            if (!s || !s.targets.length) return;
            setTimeout(() => {
                post({ type: 'cms:highlight', section: key, targets: state.mode === 'draft' ? s.targets : [], label: s.label, changed: true });
                post({ type: 'cms:scroll-to', section: key, targets: s.targets });
            }, 900);
        },
    };

    function init() {
        aside = document.querySelector('.wcm-preview');
        layout = document.querySelector('.wcm-layout');
        if (!aside || !layout) return;
        document.querySelector('.wcm-app')?.classList.add('wcpv-on');
        if (window.innerWidth < 1100 && window.innerWidth >= 900) state.open = false; // start folded on smaller screens
        render();
        applyLayout();
        if (state.open && window.innerWidth >= 900) load();

        // Refit when the panel or the stage changes size (e.g. the hint bar appears).
        const ro = new ResizeObserver(() => fit());
        ro.observe(aside);
        ro.observe(aside.querySelector('#wcpvStage'));
        window.addEventListener('resize', () => applyLayout());

        // Any edit in a section (typing, choices, reordering) → live update.
        const body = document.getElementById('wcmEditorBody');
        ['input', 'change', 'click'].forEach((t) => body.addEventListener(t, () => scheduleUpdate(), true));
        // Section switches.
        document.addEventListener('click', (e) => {
            if (e.target.closest('.wcm-nav-item')) setTimeout(onSectionChange, 0);
        });
        setInterval(renderChrome, 4000); // keeps the change count honest after saves/discards
    }

    // Capture the CMS api object when sections render, so we always read the current settings.
    const wrap = (name) => {
        const fn = ext.renderers[name];
        if (typeof fn !== 'function' || fn.__wcpvWrapped) return;
        ext.renderers[name] = function (api) { ext.lastApi = api; return fn.apply(this, arguments); };
        ext.renderers[name].__wcpvWrapped = true;
    };
    Object.keys(ext.renderers).forEach(wrap);

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else setTimeout(init, 0);
})();
