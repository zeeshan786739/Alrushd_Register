/**
 * Website CMS → Pages (connected website).
 *
 *   List    – every page with its type, menu placement, Google listing and status.
 *   Editor  – page basics, Google listing, social sharing image and visibility, with live
 *             Google / social previews and a checklist. Edits autosave as a draft (≈1s after
 *             typing stops, and on blur); they go live only with the CMS "Publish" button.
 *   Content – block builder for custom pages ("Edit page content").
 *
 * Renders inside the CMS editor pane; the CMS shell (menu, live preview, save bar) is untouched
 * apart from the save-bar hint and Discard, which act on the open page while it is open.
 * Loads after website-cms-headless.js (shared helpers) and before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc, uid, clone, BLOCKS, bpField, setPath } = ext.shared;

    const LIMITS = { title: 60, description: 160, goodDescription: 50 };
    const AUTOSAVE_MS = 1000;
    const IMAGE_TYPES = ['image/jpeg', 'image/png'];
    const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
    const REOPEN_KEY = 'wcp:reopen-page';

    let pages = H.pages || [];
    let api = null;
    let list = { filter: 'all', query: '' };
    let editing = null;
    const save = { timer: null, inflight: null, queued: false, state: 'idle', savedAt: null, message: '' };

    ext.getPages = () => pages;
    ext.onPublished = () => {
        pages = pages.map((p) => ({ ...p, is_published: true, has_changes: false, published_path: p.draft.path }));
    };

    /* ───────────────────────── helpers ───────────────────────── */

    /** The CMS section currently shown — save-bar handlers must only act while Pages is open. */
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;

    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;

    /** Mirrors WebsitePage::normalisePath() on the server. */
    function normPath(value) {
        let p = String(value || '').trim().toLowerCase();
        if (/^https?:\/\//.test(p)) {
            try { p = new URL(p).pathname; } catch (e) { /* keep as typed */ }
        }
        p = p.replace(/[^a-z0-9/_-]+/g, '-').replace(/\/+/g, '/');
        return '/' + p.replace(/^[/-]+|[/-]+$/g, '');
    }

    function slugify(text) {
        return normPath(String(text || '').normalize('NFKD').replace(/[̀-ͯ]/g, '').replace(/['’]/g, ''));
    }

    function send(method, url, body) {
        const opts = { method, headers: { 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' } };
        if (body instanceof FormData) {
            opts.body = body;
        } else if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(async (r) => {
            const json = await r.json().catch(() => ({}));
            if (!r.ok) {
                const errors = json.errors || {};
                const field = Object.keys(errors)[0] || null;
                const err = new Error(json.message || (field ? errors[field][0] : null) || 'Something went wrong. Please try again.');
                err.status = r.status;
                err.field = field;
                throw err;
            }
            return json;
        });
    }

    function confirmDialog(opts) {
        if (window.CrmUI && typeof window.CrmUI.confirm === 'function') return window.CrmUI.confirm(opts);
        return Promise.resolve(window.confirm([opts.title, opts.message].filter(Boolean).join('\n\n')));
    }

    function site() {
        const d = api ? api.getData() : W.data;
        const seo = d.seo || {};
        const b = d.branding || {};
        const base = (H.connection && H.connection.external_url) || W.publicWebsiteUrl || '';
        let host = '';
        try { host = new URL(base).host.replace(/^www\./, ''); } catch (e) { host = base; }
        const name = b.company_name || 'Your school';
        return {
            base: base.replace(/\/+$/, ''),
            host,
            name,
            short: b.short_name || name,
            logo: b.logo || b.favicon || '',
            initials: name.split(/[\s-]+/).filter((w) => /^[A-Za-z]/.test(w)).slice(0, 2).map((w) => w[0].toUpperCase()).join('') || 'W',
            title: seo.meta_title || '',
            description: seo.meta_description || '',
            ogImage: seo.og_image || '',
        };
    }

    function truncate(text, max) {
        const t = String(text || '').trim();
        if (t.length <= max) return t;
        const cut = t.slice(0, max - 1);
        const space = cut.lastIndexOf(' ');
        return (space > max * 0.6 ? cut.slice(0, space) : cut).replace(/[\s,.;:–-]+$/, '') + '…';
    }

    function visibility(kind, draft) {
        if (kind === 'custom' && draft.hidden) return 'offline';
        if (draft.hidden || (draft.seo && draft.seo.noindex)) return 'search';
        return 'visible';
    }

    function pageStatus(p) {
        if (p.kind === 'custom' && p.draft.hidden) return { label: 'Hidden', tone: 'muted' };
        if (!p.is_published) return { label: 'Draft', tone: 'warn' };
        return { label: 'Live', tone: 'ok' };
    }

    function menuPlacement(path) {
        const nav = (api ? api.getData() : W.data).nav_menu || {};
        if (path === '/') return { label: 'Logo link', inMenu: true, dropdown: false };
        const item = (nav.items || []).find((i) => i.href && normPath(i.href) === path);
        if (item) return { label: item.group || 'Top level', inMenu: true, dropdown: !!item.group };
        if (nav.cta_url && normPath(nav.cta_url) === path) return { label: 'Header button', inMenu: true, dropdown: false };
        return { label: 'Not in menu', inMenu: false, dropdown: false };
    }

    /** What Google will show when this page's own fields are empty (mirrors the website's seo.ts). */
    function fallbackTitle(kind, draft) {
        const s = site();
        if (draft.path === '/' && s.title) return s.title;
        return draft.title ? `${draft.title} | ${s.short}` : s.title;
    }

    function fallbackDescription(kind, draft) {
        const s = site();
        if (kind === 'custom' && draft.title) return `${draft.title} at ${s.name}.`;
        return s.description;
    }

    function setModuleHeading(html, desc) {
        const title = document.getElementById('wcmModuleTitle');
        const descEl = document.getElementById('wcmModuleDesc');
        if (title) title.innerHTML = html;
        if (descEl) {
            descEl.textContent = desc || '';
            descEl.hidden = !desc;
        }
    }

    /* ───────────────────── save bar & status (CMS shell) ───────────────────── */

    const shell = {
        hint: () => document.querySelector('.wcm-savebar-hint'),
        saveDraft: () => document.getElementById('wcmSaveDraft'),
        discard: () => document.getElementById('wcmDiscard'),
        publish: () => document.getElementById('wcmPublish'),
        status: () => document.getElementById('wcmStatus'),
        original: null,
    };

    function rememberShell() {
        if (shell.original) return;
        const hint = shell.hint();
        shell.original = { hint: hint ? hint.innerHTML : '' };
    }

    function updateSavebar() {
        if (activeModule() !== 'pages') return;
        rememberShell();
        const hint = shell.hint();
        const discard = shell.discard();
        const saveDraft = shell.saveDraft();
        // Pages autosave — never two save buttons. (!important: a global `.btn` rule forces display.)
        if (saveDraft) saveDraft.style.setProperty('display', 'none', 'important');

        let pending;
        let text;
        if (editing) {
            pending = !editing.isPublished || editing.hasChanges || editing.dirty;
            text = pending ? 'You have changes that aren’t on the website yet.' : 'This page matches your live website.';
            if (discard) {
                const canDiscard = !!editing.id && editing.isPublished && (editing.hasChanges || editing.dirty);
                discard.disabled = !canDiscard;
                discard.title = canDiscard ? 'Go back to the version that is live on your website'
                    : (editing.isPublished ? 'Nothing to discard on this page' : 'This page isn’t on your website yet');
            }
        } else {
            // Site settings (menus, SEO…) state from the server, plus live page state.
            pending = !!W.siteHasUnpublished || pages.some((p) => p.has_changes || !p.is_published);
            text = pending ? 'You have changes that aren’t on the website yet.' : 'No unpublished changes — your website matches what you see here.';
            if (discard) { discard.disabled = !pending; discard.title = ''; }
        }
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${pending ? 'warn' : 'ok'}" aria-hidden="true"></span>${esc(text)}`;
    }

    function restoreShell() {
        if (!shell.original) return;
        const hint = shell.hint();
        if (hint) hint.innerHTML = shell.original.hint;
        const saveDraft = shell.saveDraft();
        if (saveDraft) saveDraft.style.removeProperty('display');
        const discard = shell.discard();
        if (discard) { discard.disabled = false; discard.title = ''; }
        setRetry(false);
        shell.original = null;
    }

    function relativeTime(ts) {
        const s = Math.round((Date.now() - ts) / 1000);
        if (s < 45) return 'just now';
        const m = Math.round(s / 60);
        return m < 60 ? `${m} min ago` : `${Math.round(m / 60)} h ago`;
    }

    function setRetry(show) {
        const statusEl = shell.status();
        if (!statusEl) return;
        let btn = statusEl.querySelector('.wcp-retry');
        if (show && !btn) {
            btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'wcp-retry';
            btn.textContent = 'Retry';
            btn.addEventListener('click', () => { if (editing) { editing.dirty = true; flushSave(); } });
            statusEl.appendChild(btn);
        }
        if (btn) btn.hidden = !show;
    }

    function setSaveState(state, message) {
        save.state = state;
        save.message = message || '';
        if (!api) return;
        setRetry(state === 'error');
        switch (state) {
            case 'pending': api.setStatus('Unsaved changes…', 'saving'); break;
            case 'saving': api.setStatus('Saving…', 'saving'); break;
            case 'saved': api.setStatus('Draft saved automatically ' + relativeTime(save.savedAt), 'ok'); break;
            case 'incomplete': api.setStatus('Add a page name and web address to save', 'saving'); break;
            case 'invalid': api.setStatus('Couldn’t save – fix the highlighted field', 'error'); break;
            case 'error': api.setStatus('Couldn’t save', 'error'); break;
            default: api.setStatus('No changes yet', 'ok');
        }
        updateSavebar();
    }

    setInterval(() => { if (editing && save.state === 'saved' && activeModule() === 'pages') setSaveState('saved'); }, 30000);

    /* ───────────────────────── autosave ───────────────────────── */

    function scheduleSave() {
        if (!editing) return;
        editing.dirty = true;
        clearTimeout(save.timer);
        setSaveState('pending');
        save.timer = setTimeout(flushSave, AUTOSAVE_MS);
    }

    function draftPayload(target) {
        const draft = clone(target.draft);
        draft.path = target.kind === 'system' ? target.publishedPath || draft.path : normPath(draft.path);
        return draft;
    }

    function flushSave() {
        clearTimeout(save.timer);
        save.timer = null;
        const target = editing;
        if (!target || !target.dirty) return save.inflight || Promise.resolve(true);
        if (save.inflight) {
            save.queued = true;
            return save.inflight;
        }
        if (!target.id && (!target.draft.title.trim() || normPath(target.draft.path) === '/')) {
            setSaveState('incomplete');
            return Promise.resolve(false);
        }

        target.dirty = false;
        setSaveState('saving');
        const isNew = !target.id;
        const pathBefore = target.lastSavedPath;

        save.inflight = send(isNew ? 'POST' : 'PUT', isNew ? H.routes.pages : `${H.routes.pages}/${target.id}`, { draft: draftPayload(target) })
            .then((res) => {
                pages = res.pages;
                W.hasUnpublished = true;
                if (ext.preview) ext.preview.refresh(); // the saved page draft re-renders in the preview
                if (editing !== target) return true;
                target.id = res.page.id;
                target.isPublished = res.page.is_published;
                target.hasChanges = res.page.has_changes;
                target.publishedPath = res.page.published_path;
                target.lastSavedPath = res.page.draft.path;
                // The server keeps the visibility flags in step; adopt them without touching what is being typed.
                target.draft.hidden = res.page.draft.hidden;
                target.draft.seo.noindex = res.page.draft.seo.noindex;
                clearErrors();
                save.savedAt = Date.now();
                setSaveState(target.dirty ? 'pending' : 'saved');
                refreshEditor();
                if (target.view === 'content' || pathBefore !== target.lastSavedPath) showInLivePreview(target.lastSavedPath);
                return true;
            })
            .catch((err) => {
                target.dirty = true;
                if (editing !== target) return false;
                if (err.status === 422) {
                    showFieldError(err.field || 'title', err.message);
                    setSaveState('invalid', err.message);
                } else {
                    setSaveState('error', err.message);
                }
                return false;
            })
            .finally(() => {
                save.inflight = null;
                if (save.queued) {
                    save.queued = false;
                    if (editing && editing.dirty) flushSave();
                }
            });
        return save.inflight;
    }

    window.addEventListener('beforeunload', (e) => {
        if (!editing || window.__wcpSkipUnloadWarning) return;
        if (editing.dirty || save.inflight || save.state === 'invalid' || save.state === 'error') {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    /* ───────────────────────── live preview pane ───────────────────────── */

    function previewUrlFor(path) {
        if (!H.routes.previewUrl) return Promise.resolve(null);
        return fetch(H.routes.previewUrl + '?path=' + encodeURIComponent(path || '/'), { headers: { Accept: 'application/json' } })
            .then((r) => r.json()).then((res) => res.url || null).catch(() => null);
    }

    function showInLivePreview(path) {
        if (ext.preview) { ext.preview.showPath(path || '/'); return; } // live preview panel
        previewUrlFor(path).then((url) => { if (url && api) api.setPreviewUrl(url); });
    }

    /* ═══════════════════════════ LIST ═══════════════════════════ */

    function filteredPages() {
        const q = list.query.trim().toLowerCase();
        return pages.filter((p) => {
            if (list.filter === 'system' && p.kind !== 'system') return false;
            if (list.filter === 'custom' && p.kind !== 'custom') return false;
            if (list.filter === 'drafts' && !(p.draft.hidden || !p.is_published)) return false;
            if (!q) return true;
            return (p.draft.title || '').toLowerCase().includes(q) || (p.draft.path || '').toLowerCase().includes(q);
        });
    }

    function counts() {
        return {
            all: pages.length,
            system: pages.filter((p) => p.kind === 'system').length,
            custom: pages.filter((p) => p.kind === 'custom').length,
            drafts: pages.filter((p) => p.draft.hidden || !p.is_published).length,
        };
    }

    function chip(label, tone, iconName) {
        return `<span class="wcp-chip wcp-chip--${tone}">${iconName ? icon(iconName) : '<span class="wcp-chip-dot" aria-hidden="true"></span>'}${esc(label)}</span>`;
    }

    function kindChip(kind) {
        return kind === 'custom'
            ? `<span class="wcp-kind wcp-kind--custom">${icon('solar:widget-2-linear')}Custom</span>`
            : `<span class="wcp-kind">${icon('solar:lock-keyhole-minimalistic-linear')}Built-in</span>`;
    }

    function googleCell(p) {
        const vis = visibility(p.kind, p.draft);
        if (vis !== 'visible') return `<span class="wcp-listing wcp-listing--muted">${icon('solar:eye-closed-linear')}Hidden from search</span>`;
        if ((p.draft.seo || {}).description) return `<span class="wcp-listing wcp-listing--ok">${icon('solar:check-circle-linear')}Own description</span>`;
        // Built-in pages fall back to the description written into the website; custom pages only get a generic line.
        if (p.kind === 'custom') {
            return `<button type="button" class="wcp-listing wcp-listing--add" data-edit="${p.id}" data-jump="wcp-google">${icon('solar:info-circle-linear')}Add a description</button>`;
        }
        return `<button type="button" class="wcp-listing wcp-listing--muted" data-edit="${p.id}" data-jump="wcp-google" title="Edit this page's Google listing">Using site defaults</button>`;
    }

    function rowHtml(p) {
        const st = pageStatus(p);
        const menu = menuPlacement(p.draft.path);
        const isHome = p.draft.path === '/';
        return `<div class="wcp-tr" role="row" data-row="${p.id}">
            <div class="wcp-td wcp-td--page" role="cell">
                <span class="wcp-tile ${p.kind === 'custom' ? 'wcp-tile--gold' : ''}">${icon(isHome ? 'solar:home-2-linear' : 'solar:document-linear')}</span>
                <span class="wcp-page-text">
                    <button type="button" class="wcp-page-name" data-edit="${p.id}">${esc(p.draft.title || '(untitled)')}</button>
                    <span class="wcp-mono">${esc(p.draft.path)}</span>
                    <span class="wcp-compact-meta">${kindChip(p.kind)}<span class="wcp-menu-text">${esc(menu.label)}</span></span>
                </span>
            </div>
            <div class="wcp-td wcp-td--type" role="cell">${kindChip(p.kind)}</div>
            <div class="wcp-td wcp-td--menu" role="cell"><span class="${menu.inMenu ? '' : 'wcp-muted'}">${esc(menu.label)}${menu.dropdown ? ' ▾' : ''}</span></div>
            <div class="wcp-td wcp-td--google" role="cell">${googleCell(p)}</div>
            <div class="wcp-td wcp-td--status" role="cell">${chip(st.label, st.tone)}${st.label === 'Live' && p.has_changes ? '<span class="wcp-changed" title="Edited since it was published">Unpublished edits</span>' : ''}</div>
            <div class="wcp-td wcp-td--actions" role="cell">
                <button type="button" class="wcp-btn wcp-btn--outline wcp-btn--sm" data-edit="${p.id}" aria-label="Edit ${esc(p.draft.title)}">${icon('solar:pen-2-linear')}<span>Edit</span></button>
                <button type="button" class="wcp-btn wcp-btn--ghost wcp-btn--sm wcp-hide-compact" data-preview="${esc(p.draft.path)}" aria-label="Show ${esc(p.draft.title)} in the live preview">${icon('solar:eye-linear')}<span>Preview</span></button>
                <button type="button" class="wcp-icon-btn" data-more="${p.id}" aria-haspopup="menu" aria-expanded="false" aria-label="More actions for ${esc(p.draft.title)}">${icon('solar:menu-dots-bold')}</button>
            </div>
        </div>`;
    }

    function renderList() {
        setModuleHeading('Pages', 'Every page on your website. Click a page to change how it appears in Google, on social media and in your menu.');
        const c = counts();
        const tab = (key, label) => `<button type="button" class="wcp-tab ${list.filter === key ? 'is-active' : ''}" data-filter="${key}" aria-pressed="${list.filter === key}">${label}<span class="wcp-tab-count" data-count="${key}">${c[key]}</span></button>`;

        api.editorBody.innerHTML = `<div class="wcp">
            <div class="wcp-list-top">
                <div class="wcp-kinds">
                    <div class="wcp-kind-card">
                        <span class="wcp-tile">${icon('solar:lock-keyhole-minimalistic-linear')}</span>
                        <div><strong>Built-in pages</strong><p>Designed by your developer. You can change their Google listing, sharing image and visibility.</p></div>
                    </div>
                    <div class="wcp-kind-card">
                        <span class="wcp-tile wcp-tile--gold">${icon('solar:widget-2-linear')}</span>
                        <div><strong>Custom pages</strong><p>Built by you from ready-made blocks — text, images and forms. They go live when you press Publish.</p></div>
                    </div>
                </div>
                <button type="button" class="wcp-btn wcp-btn--primary" id="wcpNewPage">${icon('solar:add-circle-linear')}New page</button>
            </div>
            <div class="wcp-toolbar">
                <div class="wcp-tabs" role="group" aria-label="Filter pages">
                    ${tab('all', 'All pages')}${tab('system', 'Built-in')}${tab('custom', 'Custom')}${tab('drafts', 'Drafts &amp; hidden')}
                </div>
                <label class="wcp-search">${icon('solar:magnifer-linear')}
                    <span class="visually-hidden">Search pages</span>
                    <input type="search" id="wcpSearch" placeholder="Search by page name or address" value="${esc(list.query)}" autocomplete="off">
                </label>
            </div>
            <div class="wcp-table" role="table" aria-label="Website pages">
                <div class="wcp-tr wcp-tr--head" role="row">
                    <div class="wcp-th" role="columnheader">Page</div>
                    <div class="wcp-th wcp-td--type" role="columnheader">Type</div>
                    <div class="wcp-th wcp-td--menu" role="columnheader">In the menu</div>
                    <div class="wcp-th wcp-td--google" role="columnheader">Google listing</div>
                    <div class="wcp-th wcp-td--status" role="columnheader">Status</div>
                    <div class="wcp-th wcp-td--actions" role="columnheader">Actions</div>
                </div>
                <div id="wcpRows" role="rowgroup"></div>
                <div class="wcp-table-foot" id="wcpFoot" aria-live="polite"></div>
            </div>
        </div>`;

        renderRows();
        bindList();
        updateSavebar();
    }

    function renderRows() {
        const rows = filteredPages();
        const box = api.editorBody.querySelector('#wcpRows');
        if (!box) return;
        box.innerHTML = rows.length ? rows.map(rowHtml).join('')
            : `<div class="wcp-empty-row">${list.query ? `No pages match “${esc(list.query)}”.` : 'No pages here yet.'}</div>`;
        const foot = api.editorBody.querySelector('#wcpFoot');
        if (foot) foot.textContent = `Showing ${rows.length} of ${pages.length} page${pages.length === 1 ? '' : 's'}`;
        const c = counts();
        api.editorBody.querySelectorAll('[data-count]').forEach((el) => { el.textContent = c[el.dataset.count]; });
    }

    function bindList() {
        const body = api.editorBody;
        body.querySelector('#wcpNewPage').addEventListener('click', () => openEditor(null));
        body.querySelectorAll('[data-filter]').forEach((b) => b.addEventListener('click', () => {
            list.filter = b.dataset.filter;
            body.querySelectorAll('[data-filter]').forEach((x) => {
                x.classList.toggle('is-active', x === b);
                x.setAttribute('aria-pressed', String(x === b));
            });
            renderRows();
        }));
        body.querySelector('#wcpSearch').addEventListener('input', (e) => { list.query = e.target.value; renderRows(); });

        body.querySelector('#wcpRows').addEventListener('click', (e) => {
            const edit = e.target.closest('[data-edit]');
            const prev = e.target.closest('[data-preview]');
            const more = e.target.closest('[data-more]');
            if (edit) return openEditor(+edit.dataset.edit, edit.dataset.jump);
            if (prev) return showInLivePreview(prev.dataset.preview);
            if (more) return openRowMenu(more);
            const row = e.target.closest('[data-row]');
            if (row && !e.target.closest('a, button, input')) openEditor(+row.dataset.row);
        });
    }

    /* ── row "more" menu ── */
    let openMenu = null;

    function closeRowMenu() {
        if (!openMenu) return;
        openMenu.trigger.setAttribute('aria-expanded', 'false');
        openMenu.el.remove();
        document.removeEventListener('click', onDocClick, true);
        document.removeEventListener('keydown', onMenuKey, true);
        openMenu = null;
    }

    function onDocClick(e) {
        if (openMenu && !openMenu.el.contains(e.target) && !openMenu.trigger.contains(e.target)) closeRowMenu();
    }

    function onMenuKey(e) {
        if (!openMenu) return;
        const items = [...openMenu.el.querySelectorAll('[role="menuitem"]')];
        const i = items.indexOf(document.activeElement);
        if (e.key === 'Escape') { e.preventDefault(); const t = openMenu.trigger; closeRowMenu(); t.focus(); }
        if (e.key === 'ArrowDown') { e.preventDefault(); items[(i + 1) % items.length].focus(); }
        if (e.key === 'ArrowUp') { e.preventDefault(); items[(i - 1 + items.length) % items.length].focus(); }
        if (e.key === 'Tab') closeRowMenu();
    }

    function openRowMenu(trigger) {
        const same = openMenu && openMenu.trigger === trigger;
        closeRowMenu();
        if (same) return;
        const p = pages.find((x) => String(x.id) === trigger.dataset.more);
        if (!p) return;
        const s = site();
        const liveUrl = p.is_published && s.base ? s.base + (p.published_path || p.draft.path) : '';
        const el = document.createElement('div');
        el.className = 'wcp-menu';
        el.setAttribute('role', 'menu');
        el.innerHTML = `
            <button type="button" role="menuitem" data-act="preview">${icon('solar:eye-linear')}Show in live preview</button>
            ${liveUrl ? `<a role="menuitem" href="${esc(liveUrl)}" target="_blank" rel="noopener">${icon('solar:square-arrow-right-up-linear')}Open on website</a>` : ''}
            ${liveUrl ? `<button type="button" role="menuitem" data-act="copy">${icon('solar:copy-linear')}Copy page link</button>` : ''}
            ${p.kind === 'custom' ? `<hr><button type="button" role="menuitem" class="is-danger" data-act="delete">${icon('solar:trash-bin-trash-linear')}Delete page</button>` : ''}`;
        document.body.appendChild(el);
        const r = trigger.getBoundingClientRect();
        el.style.top = Math.min(r.bottom + 6, window.innerHeight - el.offsetHeight - 8) + 'px';
        el.style.left = Math.max(8, r.right - el.offsetWidth) + 'px';
        trigger.setAttribute('aria-expanded', 'true');
        openMenu = { el, trigger };
        el.querySelector('[role="menuitem"]').focus();
        document.addEventListener('click', onDocClick, true);
        document.addEventListener('keydown', onMenuKey, true);

        el.addEventListener('click', (e) => {
            const act = e.target.closest('[data-act]');
            if (!act && !e.target.closest('a')) return;
            closeRowMenu();
            if (!act) return;
            if (act.dataset.act === 'preview') showInLivePreview(p.draft.path);
            if (act.dataset.act === 'copy') navigator.clipboard?.writeText(liveUrl).then(() => api.setStatus('Page link copied', 'ok'));
            if (act.dataset.act === 'delete') deletePage(p);
        });
    }

    function deletePage(p) {
        confirmDialog({
            title: `Delete “${p.draft.title || 'this page'}”?`,
            message: p.is_published ? 'It disappears from your website straight away. This can’t be undone.' : 'This draft page will be removed. This can’t be undone.',
            label: 'Delete page',
            tone: 'danger',
            icon: 'solar:trash-bin-trash-linear',
        }).then((ok) => {
            if (!ok) return;
            send('DELETE', `${H.routes.pages}/${p.id}`).then((res) => {
                pages = res.pages;
                if (editing && editing.id === p.id) { editing = null; setSaveState('idle'); }
                renderPages(api);
                api.setStatus(res.message, 'ok');
            }).catch((e) => api.setStatus(e.message, 'error'));
        });
    }

    /* ═══════════════════════════ EDITOR ═══════════════════════════ */

    function openEditor(id, jumpTo) {
        if (id === null) {
            editing = {
                id: null, kind: 'custom', isPublished: false, hasChanges: true, publishedPath: '', lastSavedPath: '',
                view: 'settings', dirty: false, pathTouched: false,
                draft: {
                    title: '', path: '', hidden: false,
                    seo: { title: '', description: '', og_image: '', noindex: false },
                    blocks: [
                        { id: uid(), type: 'hero', data: { title: '', lead: '' } },
                        { id: uid(), type: 'form', data: { heading: '', form_slug: '' } },
                    ],
                },
            };
        } else {
            const p = pages.find((x) => x.id === id);
            if (!p) return;
            const draft = clone(p.draft);
            draft.seo = Object.assign({ title: '', description: '', og_image: '', noindex: false }, draft.seo || {});
            editing = {
                id: p.id, kind: p.kind, isPublished: p.is_published, hasChanges: p.has_changes,
                publishedPath: p.published_path || p.draft.path, lastSavedPath: p.draft.path,
                view: 'settings', dirty: false, pathTouched: true, draft,
            };
            showInLivePreview(p.draft.path);
        }
        save.savedAt = null;
        setSaveState('idle');
        renderEditor();
        if (jumpTo) jump(jumpTo);
        else api.editorBody.scrollTop = 0;
    }

    function leaveEditor() {
        flushSave().then((ok) => {
            const proceed = (ok !== false && save.state !== 'invalid' && save.state !== 'error')
                ? Promise.resolve(true)
                : confirmDialog({
                    title: 'Leave without saving?',
                    message: save.message || 'Your latest changes to this page couldn’t be saved.',
                    note: 'If you leave now, those changes are lost.',
                    label: 'Leave page',
                    tone: 'warning',
                });
            proceed.then((yes) => {
                if (!yes) return;
                editing = null;
                setSaveState('idle');
                renderList();
                api.editorBody.scrollTop = 0;
            });
        });
    }

    function crumbs(title) {
        return `<nav class="wcp-crumbs" aria-label="Breadcrumb">
            <button type="button" data-crumb="dashboard">Website CMS</button>${icon('solar:alt-arrow-right-linear', 'wcp-crumb-sep')}
            <button type="button" data-crumb="pages">Pages</button>${icon('solar:alt-arrow-right-linear', 'wcp-crumb-sep')}
            <span aria-current="page" id="wcpCrumbTitle">${esc(title)}</span></nav>`;
    }

    function bindCrumbs() {
        document.querySelectorAll('#wcmModuleTitle [data-crumb]').forEach((b) => b.addEventListener('click', () => {
            if (b.dataset.crumb === 'pages') return leaveEditor();
            flushSave().then(() => { restoreShell(); api.switchModule('dashboard'); });
        }));
    }

    function renderEditor() {
        if (editing.view === 'content') return renderContent();
        const d = editing.draft;
        const custom = editing.kind === 'custom';
        const s = site();
        const isHome = d.path === '/' && !custom;
        const pageName = d.title || (editing.id ? '(untitled)' : 'New page');

        setModuleHeading(crumbs(pageName), '');
        bindCrumbs();

        const pathHint = custom
            ? 'Lowercase words joined by hyphens, for example /summer-school.'
            : (isHome ? 'The homepage address can’t be changed.' : 'Built-in page addresses are set by your developer.');

        const vis = visibility(editing.kind, d);
        const radio = (value, title, text, badge) => `
            <label class="wcp-radio ${vis === value ? 'is-selected' : ''}">
                <input type="radio" name="wcpVisibility" value="${value}" ${vis === value ? 'checked' : ''}>
                <span class="wcp-radio-body">
                    <span class="wcp-radio-title">${esc(title)}${badge ? ` <span class="wcp-badge-ok">${esc(badge)}</span>` : ''}</span>
                    <span class="wcp-radio-text">${esc(text)}</span>
                </span>
            </label>`;

        api.editorBody.innerHTML = `<div class="wcp wcp-editor">
            <button type="button" class="wcp-back" id="wcpBack">${icon('solar:arrow-left-linear')}All pages</button>

            <header class="wcp-head">
                <div class="wcp-head-main">
                    <span class="wcp-tile wcp-tile--navy wcp-tile--lg">${icon(isHome ? 'solar:home-2-linear' : 'solar:document-linear')}</span>
                    <div class="wcp-head-text">
                        <div class="wcp-head-title"><h1 id="wcpH1">${esc(pageName)}</h1><span class="wcp-head-chips" id="wcpChips"></span></div>
                        <code class="wcp-url" id="wcpUrl"></code>
                    </div>
                </div>
                <div class="wcp-head-actions" id="wcpHeadActions"></div>
            </header>

            ${custom ? `
            <div class="wcp-notice wcp-notice--content">
                <span class="wcp-tile">${icon('solar:widget-2-linear')}</span>
                <p><strong>This page is built from blocks.</strong> Add and arrange text, images, FAQs and forms. <span class="wcp-muted" id="wcpBlockCount"></span></p>
                <button type="button" class="wcp-btn wcp-btn--primary" id="wcpEditContent">${icon('solar:pen-new-square-linear')}Edit page content</button>
            </div>` : `
            <div class="wcp-notice">
                <span class="wcp-tile">${icon('solar:info-circle-linear')}</span>
                <p><strong>This page's layout and wording come from your website's design.</strong> To change them, contact your developer. Here you can control how the page appears on Google, when shared on social media, and whether search engines can find it.</p>
            </div>`}

            <nav class="wcp-jump" aria-label="Jump to section">
                <a href="#wcp-basics" data-jump="wcp-basics">1 · Basics</a>
                <a href="#wcp-google" data-jump="wcp-google">2 · Google listing</a>
                <a href="#wcp-social" data-jump="wcp-social">3 · Social sharing</a>
                <a href="#wcp-visibility" data-jump="wcp-visibility">4 · Visibility</a>
            </nav>

            <div class="wcp-grid">
                <div class="wcp-form">
                    <section class="wcp-card" id="wcp-basics" aria-labelledby="wcp-basics-h">
                        <h2 id="wcp-basics-h" tabindex="-1">Page basics</h2>
                        <p class="wcp-card-desc">The page's name and web address.</p>
                        <div class="wcp-row-2">
                            <div class="wcp-field">
                                <label for="wcpTitle">Page name</label>
                                <input type="text" id="wcpTitle" class="wcp-input" data-field="title" value="${esc(d.title)}" maxlength="160" aria-describedby="wcpTitleHint wcpTitleErr" autocomplete="off">
                                <p class="wcp-hint" id="wcpTitleHint">Shown in this CMS and used in Google if you don't set a title below.</p>
                                <p class="wcp-error" id="wcpTitleErr" hidden></p>
                            </div>
                            <div class="wcp-field">
                                <label for="wcpPath">Web address</label>
                                <div class="wcp-input-group ${custom ? '' : 'is-locked'}">
                                    <span class="wcp-prefix">${esc(s.host || 'your-website')}</span>
                                    <input type="text" id="wcpPath" class="wcp-input" data-field="path" value="${esc(d.path)}" ${custom ? '' : 'readonly aria-readonly="true"'} spellcheck="false" autocomplete="off" aria-describedby="wcpPathHint wcpPathWarn wcpPathErr">
                                    ${custom ? '' : `<span class="wcp-lock" title="Locked">${icon('solar:lock-keyhole-minimalistic-linear')}</span>`}
                                </div>
                                <p class="wcp-hint" id="wcpPathHint">${esc(pathHint)}</p>
                                <p class="wcp-warn" id="wcpPathWarn" hidden>${icon('solar:danger-triangle-linear')}<span>Old links to this page will stop working once you publish the new address.</span></p>
                                <p class="wcp-error" id="wcpPathErr" hidden></p>
                            </div>
                        </div>
                    </section>

                    <section class="wcp-card" id="wcp-google" aria-labelledby="wcp-google-h">
                        <h2 id="wcp-google-h" tabindex="-1">Google listing</h2>
                        <p class="wcp-card-desc">What people see when this page shows up in Google search results. The preview ${'<span class="wcp-only-wide">on the right</span><span class="wcp-only-narrow">below</span>'} updates as you type.</p>
                        <div class="wcp-field">
                            <div class="wcp-label-row"><label for="wcpSeoTitle">Search title</label><span class="wcp-counter" id="wcpSeoTitleCount" aria-live="polite"></span></div>
                            <input type="text" id="wcpSeoTitle" class="wcp-input" data-field="seo.title" value="${esc(d.seo.title)}" aria-describedby="wcpSeoTitleHint" autocomplete="off">
                            <p class="wcp-hint" id="wcpSeoTitleHint">Optional. Leave empty to use the default title (shown in grey). Aim for under ${LIMITS.title} characters.</p>
                        </div>
                        <div class="wcp-field">
                            <div class="wcp-label-row"><label for="wcpSeoDesc">Search description</label><span class="wcp-counter" id="wcpSeoDescCount" aria-live="polite"></span></div>
                            <textarea id="wcpSeoDesc" class="wcp-input wcp-textarea" rows="4" data-field="seo.description" aria-describedby="wcpSeoDescTip">${esc(d.seo.description)}</textarea>
                            <p class="wcp-tip" id="wcpSeoDescTip" hidden>${icon('solar:lightbulb-minimalistic-linear')}<span>Pages with their own description tend to get more clicks. Write one or two sentences that tell parents what they'll find here (120–160 characters).</span></p>
                        </div>
                    </section>

                    <section class="wcp-card" id="wcp-social" aria-labelledby="wcp-social-h">
                        <h2 id="wcp-social-h" tabindex="-1">Social sharing image</h2>
                        <p class="wcp-card-desc">The picture shown when someone shares this page on WhatsApp, Facebook or LinkedIn.</p>
                        <div id="wcpImageSlot"></div>
                        <input type="file" id="wcpOgFile" class="visually-hidden" tabindex="-1" accept="image/jpeg,image/png" aria-label="Choose a sharing image">
                        <div class="wcp-or" aria-hidden="true"><span>or</span></div>
                        <div class="wcp-field">
                            <label for="wcpOgUrl">Paste an image link</label>
                            <input type="url" id="wcpOgUrl" class="wcp-input" data-field="seo.og_image" value="${esc(d.seo.og_image)}" placeholder="https://…" aria-describedby="wcpOgHint wcpOgErr" autocomplete="off" spellcheck="false">
                            <p class="wcp-hint" id="wcpOgHint">No image? Your site's default sharing image from SEO settings is used.</p>
                            <p class="wcp-error" id="wcpOgErr" hidden></p>
                        </div>
                    </section>

                    <section class="wcp-card" id="wcp-visibility" aria-labelledby="wcp-visibility-h">
                        <h2 id="wcp-visibility-h" tabindex="-1">Who can find this page?</h2>
                        <p class="wcp-card-desc">Choose whether Google and other search engines may list this page.</p>
                        <fieldset class="wcp-radios ${custom ? 'wcp-radios--3' : ''}">
                            <legend class="visually-hidden">Who can find this page?</legend>
                            ${radio('visible', 'Visible in search', 'Google can list this page, and it’s included in your sitemap.', 'Recommended')}
                            ${radio('search', 'Hidden from search', 'Anyone with the link can still open it, but Google won’t list it and it’s left out of the sitemap.')}
                            ${custom ? radio('offline', 'Switched off', 'Nobody can open the page — visitors see “page not found”. Use this to take a page down without deleting it.') : ''}
                        </fieldset>
                    </section>

                    ${custom && editing.id ? `<div class="wcp-danger-row"><button type="button" class="wcp-link-danger" id="wcpDelete">${icon('solar:trash-bin-trash-linear')}Delete this page</button></div>` : ''}
                </div>

                <aside class="wcp-side" aria-label="Previews">
                    <section class="wcp-card wcp-card--tight">
                        <div class="wcp-side-head"><h2 class="wcp-h3">Page checklist</h2><span class="wcp-muted" id="wcpCheckCount"></span></div>
                        <div class="wcp-progress" aria-hidden="true"><span id="wcpCheckBar"></span></div>
                        <ul class="wcp-checklist" id="wcpChecklist"></ul>
                    </section>
                    <section class="wcp-card wcp-card--tight">
                        <div class="wcp-side-head"><h2 class="wcp-h3">Google preview</h2><span class="wcp-pill" id="wcpDefaultsBadge" hidden>Using site defaults</span></div>
                        <div class="wcp-serp">
                            <div class="wcp-serp-site">
                                <span class="wcp-serp-logo">${s.logo ? `<img src="${esc(s.logo)}" alt="">` : esc(s.initials)}</span>
                                <span><span class="wcp-serp-name">${esc(s.name)}</span><span class="wcp-serp-url" id="wcpSerpUrl"></span></span>
                            </div>
                            <div class="wcp-serp-title" id="wcpSerpTitle"></div>
                            <div class="wcp-serp-desc" id="wcpSerpDesc"></div>
                        </div>
                    </section>
                    <section class="wcp-card wcp-card--tight">
                        <div class="wcp-side-head"><h2 class="wcp-h3">When shared on social media</h2></div>
                        <div class="wcp-share">
                            <div class="wcp-share-img" id="wcpShareImg"></div>
                            <div class="wcp-share-body">
                                <span class="wcp-share-domain">${esc(s.host)}</span>
                                <span class="wcp-share-title" id="wcpShareTitle"></span>
                                <span class="wcp-share-desc" id="wcpShareDesc"></span>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>`;

        renderImageSlot();
        bindEditor();
        refreshEditor();
    }

    function renderImageSlot() {
        const slot = api.editorBody.querySelector('#wcpImageSlot');
        if (!slot) return;
        const img = editing.draft.seo.og_image;
        slot.innerHTML = img
            ? `<div class="wcp-thumb">
                    <img src="${esc(img)}" alt="Sharing image for this page">
                    <div class="wcp-thumb-actions">
                        <label for="wcpOgFile" class="wcp-btn wcp-btn--outline wcp-btn--sm" tabindex="0" role="button">${icon('solar:refresh-linear')}Replace</label>
                        <button type="button" class="wcp-btn wcp-btn--ghost wcp-btn--sm" id="wcpOgRemove">${icon('solar:trash-bin-trash-linear')}Remove</button>
                    </div>
               </div>`
            : `<label for="wcpOgFile" class="wcp-drop" id="wcpDrop" tabindex="0" role="button" aria-describedby="wcpDropHint">
                    <span class="wcp-tile wcp-tile--gold">${icon('solar:gallery-add-linear')}</span>
                    <span class="wcp-drop-title">Drag an image here, or <span class="wcp-link">choose a file</span></span>
                    <span class="wcp-hint" id="wcpDropHint">JPG or PNG · 1200 × 630 px works best · up to 5 MB</span>
               </label>`;

        const remove = slot.querySelector('#wcpOgRemove');
        if (remove) remove.addEventListener('click', () => setImage(''));
        slot.querySelectorAll('label[for="wcpOgFile"]').forEach((l) => l.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); api.editorBody.querySelector('#wcpOgFile').click(); }
        }));
        const drop = slot.querySelector('#wcpDrop');
        if (drop) {
            ['dragenter', 'dragover'].forEach((t) => drop.addEventListener(t, (e) => { e.preventDefault(); drop.classList.add('is-over'); }));
            ['dragleave', 'drop'].forEach((t) => drop.addEventListener(t, () => drop.classList.remove('is-over')));
            drop.addEventListener('drop', (e) => {
                e.preventDefault();
                const file = e.dataTransfer && e.dataTransfer.files[0];
                if (file) uploadImage(file);
            });
        }
    }

    function setImage(url) {
        editing.draft.seo.og_image = url;
        const input = api.editorBody.querySelector('#wcpOgUrl');
        if (input) input.value = url;
        renderImageSlot();
        refreshEditor();
        scheduleSave();
        flushSave();
    }

    function uploadImage(file) {
        showFieldError('seo.og_image', '');
        if (!IMAGE_TYPES.includes(file.type)) return showFieldError('seo.og_image', 'Please choose a JPG or PNG image.', true);
        if (file.size > IMAGE_MAX_BYTES) return showFieldError('seo.og_image', 'That image is larger than 5 MB. Please choose a smaller one.', true);

        const slot = api.editorBody.querySelector('#wcpImageSlot');
        if (slot) slot.innerHTML = `<div class="wcp-drop is-busy" aria-live="polite"><span class="wcp-spinner" aria-hidden="true"></span><span class="wcp-drop-title">Uploading ${esc(file.name)}…</span></div>`;

        const fd = new FormData();
        fd.append('file', file);
        fd.append('field', 'page.seo.og_image');
        send('POST', W.routes.upload, fd)
            .then((res) => setImage(res.url))
            .catch((err) => {
                renderImageSlot();
                showFieldError('seo.og_image', err.message, true);
            });
    }

    /* ── errors ── */
    const ERROR_TARGETS = {
        title: ['#wcpTitle', '#wcpTitleErr'],
        path: ['#wcpPath', '#wcpPathErr'],
        'seo.og_image': ['#wcpOgUrl', '#wcpOgErr'],
    };

    function showFieldError(field, message, focusIt) {
        const target = ERROR_TARGETS[field] || ERROR_TARGETS.title;
        const input = api.editorBody.querySelector(target[0]);
        const box = api.editorBody.querySelector(target[1]);
        if (!input || !box) {
            if (message) api.setStatus(message, 'error');
            return;
        }
        box.hidden = !message;
        box.innerHTML = message ? `${icon('solar:danger-circle-linear')}<span>${esc(message)}</span>` : '';
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        if (message && (focusIt || document.activeElement !== input)) {
            scrollPaneTo(input.closest('.wcp-field'), 'center');
            if (focusIt) input.focus({ preventScroll: true });
        }
    }

    function clearErrors() {
        Object.keys(ERROR_TARGETS).forEach((f) => {
            const box = api.editorBody.querySelector(ERROR_TARGETS[f][1]);
            if (box && !box.hidden) showFieldError(f, '');
        });
    }

    /* ── derived UI (chips, counters, previews, checklist) — never re-renders inputs ── */
    function counter(el, length, max) {
        if (!el) return;
        el.textContent = `${length} / ${max}`;
        el.className = 'wcp-counter ' + (length === 0 ? '' : length <= max ? 'is-ok' : 'is-over');
        el.setAttribute('aria-label', `${length} of ${max} characters${length > max ? ', over the recommended length' : ''}`);
    }

    function refreshEditor() {
        if (!editing || editing.view !== 'settings' || !api) return;
        const b = api.editorBody;
        const d = editing.draft;
        const s = site();
        const custom = editing.kind === 'custom';
        const path = custom ? normPath(d.path) : d.path;
        const name = d.title.trim() || (editing.id ? '(untitled)' : 'New page');
        const q = (sel) => b.querySelector(sel);

        q('#wcpH1').textContent = name;
        const crumb = document.getElementById('wcpCrumbTitle');
        if (crumb) crumb.textContent = name;
        q('#wcpUrl').textContent = (s.host || '') + (path === '/' ? '/' : path);

        const st = editing.id ? pageStatus({ kind: editing.kind, draft: d, is_published: editing.isPublished }) : { label: 'Draft', tone: 'warn' };
        q('#wcpChips').innerHTML = chip(st.label, st.tone) + kindChip(editing.kind)
            + (editing.isPublished && editing.hasChanges ? '<span class="wcp-changed">Unpublished edits</span>' : '');

        // Header actions
        const menu = menuPlacement(path);
        const actionsHtml = `
            ${menu.inMenu
                ? `<button type="button" class="wcp-btn wcp-btn--outline" id="wcpMenuBtn" title="Arrange it in Navigation menu">${icon('solar:hamburger-menu-linear')}In menu: ${esc(menu.label)}${menu.dropdown ? ' ▾' : ''}</button>`
                : `<button type="button" class="wcp-btn wcp-btn--outline" id="wcpMenuBtn" ${editing.id ? '' : 'disabled title="Save the page first"'}>${icon('solar:hamburger-menu-linear')}Add to menu</button>`}
            <button type="button" class="wcp-btn wcp-btn--outline" id="wcpPreviewBtn" ${editing.id ? '' : 'disabled title="Save the page first"'}>${icon('solar:eye-linear')}Preview page</button>`;
        const actions = q('#wcpHeadActions');
        // Only rebuild when something changed, so a finishing autosave never steals keyboard focus.
        if (actions.dataset.html !== actionsHtml) {
            actions.dataset.html = actionsHtml;
            actions.innerHTML = actionsHtml;
            q('#wcpMenuBtn').addEventListener('click', () => (menuPlacement(editing.lastSavedPath || normPath(editing.draft.path)).inMenu ? goToMenu() : addToMenu()));
            q('#wcpPreviewBtn').addEventListener('click', previewInNewTab);
        }

        if (custom) {
            const n = (d.blocks || []).length;
            const bc = q('#wcpBlockCount');
            if (bc) bc.textContent = `${n} block${n === 1 ? '' : 's'}`;
        }

        // Address warning: changing the address of a live page breaks old links.
        const warn = q('#wcpPathWarn');
        if (warn) warn.hidden = !(custom && editing.isPublished && editing.publishedPath && path !== editing.publishedPath && path !== '/');

        // Counters + tip
        counter(q('#wcpSeoTitleCount'), d.seo.title.length, LIMITS.title);
        counter(q('#wcpSeoDescCount'), d.seo.description.length, LIMITS.description);
        q('#wcpSeoDescTip').hidden = d.seo.description.trim() !== '';
        const titleFallback = fallbackTitle(editing.kind, { ...d, path });
        const descFallback = fallbackDescription(editing.kind, d);
        q('#wcpSeoTitle').placeholder = titleFallback;
        q('#wcpSeoDesc').placeholder = custom || !s.description
            ? `e.g. ${d.title ? `Find out about ${d.title} at ${s.short} — what it covers and how to get started.` : 'One or two sentences about what parents will find on this page.'}`
            : `e.g. ${s.description}`;

        // Google preview
        const usingDefaults = !d.seo.title.trim() && !d.seo.description.trim();
        q('#wcpDefaultsBadge').hidden = !usingDefaults;
        q('#wcpSerpUrl').textContent = (s.base || 'https://' + s.host) + (path === '/' ? '' : path);
        q('#wcpSerpTitle').textContent = truncate(d.seo.title.trim() || titleFallback || name, LIMITS.title);
        q('#wcpSerpDesc').textContent = truncate(d.seo.description.trim() || descFallback, LIMITS.description);

        // Social preview
        const img = d.seo.og_image || s.ogImage;
        const shareImg = q('#wcpShareImg');
        const wantImg = img ? `url:${img}` : 'placeholder';
        if (shareImg.dataset.src !== wantImg) {
            shareImg.dataset.src = wantImg;
            shareImg.innerHTML = img
                ? `<img src="${esc(img)}" alt="" onerror="this.parentNode.classList.add('is-broken')">`
                : `<span class="wcp-share-logo">${s.logo ? `<img src="${esc(s.logo)}" alt="">` : esc(s.initials)}</span><span class="wcp-share-note">Default sharing image</span>`;
            shareImg.classList.toggle('is-placeholder', !img);
            shareImg.classList.remove('is-broken');
        }
        q('#wcpShareTitle').textContent = d.seo.title.trim() || titleFallback || name;
        q('#wcpShareDesc').textContent = d.seo.description.trim() || descFallback;

        // Checklist
        const vis = visibility(editing.kind, d);
        const checks = [
            ['Page name set', !!d.title.trim()],
            ['Own search description', d.seo.description.trim().length >= LIMITS.goodDescription],
            ['Social sharing image', !!d.seo.og_image],
            ['Visible in search', vis === 'visible'],
        ];
        const done = checks.filter((c) => c[1]).length;
        q('#wcpCheckCount').textContent = `${done} of ${checks.length} done`;
        q('#wcpCheckBar').style.width = `${(done / checks.length) * 100}%`;
        q('#wcpChecklist').innerHTML = checks.map(([label, ok]) => `<li class="${ok ? 'is-done' : ''}">
            <span class="wcp-check" aria-hidden="true">${ok ? icon('solar:check-read-linear') : ''}</span>
            <span>${esc(label)}<span class="visually-hidden">${ok ? ' — done' : ' — to do'}</span></span></li>`).join('');

        b.querySelectorAll('.wcp-radio').forEach((l) => l.classList.toggle('is-selected', l.querySelector('input').checked));
        updateSavebar();
    }

    function bindEditor() {
        const b = api.editorBody;
        const d = editing.draft;

        b.querySelector('#wcpBack').addEventListener('click', leaveEditor);
        b.querySelectorAll('[data-jump]').forEach((a) => a.addEventListener('click', (e) => { e.preventDefault(); jump(a.dataset.jump); }));

        b.querySelectorAll('[data-field]').forEach((el) => {
            el.addEventListener('input', () => {
                let value = el.value;
                if (el.dataset.field === 'path') {
                    // Format as you type: lowercase, spaces → hyphens; the full clean-up happens on blur.
                    const caret = el.selectionStart;
                    value = value.toLowerCase().replace(/\s+/g, '-');
                    if (value !== el.value) { el.value = value; el.setSelectionRange(caret, caret); }
                    editing.pathTouched = true;
                }
                setPath(d, el.dataset.field, value);
                if (el.dataset.field === 'title' && !editing.id && !editing.pathTouched) {
                    d.path = d.title ? slugify(d.title) : '';
                    b.querySelector('#wcpPath').value = d.path;
                }
                if (el.dataset.field === 'seo.og_image') renderImageSlot();
                const err = ERROR_TARGETS[el.dataset.field];
                if (err && el.getAttribute('aria-invalid') === 'true') showFieldError(el.dataset.field, '');
                refreshEditor();
                scheduleSave();
            });
            el.addEventListener('blur', () => {
                if (el.dataset.field === 'path' && editing.kind === 'custom' && el.value.trim()) {
                    d.path = normPath(el.value);
                    el.value = d.path;
                    refreshEditor();
                }
                if (editing.dirty) flushSave();
            });
        });

        b.querySelectorAll('input[name="wcpVisibility"]').forEach((r) => r.addEventListener('change', () => {
            if (!r.checked) return;
            d.hidden = r.value === 'offline' || (r.value === 'search' && editing.kind === 'system');
            d.seo.noindex = r.value !== 'visible';
            refreshEditor();
            scheduleSave();
            flushSave();
        }));

        b.querySelector('#wcpOgFile').addEventListener('change', (e) => {
            const file = e.target.files[0];
            e.target.value = '';
            if (file) uploadImage(file);
        });

        const content = b.querySelector('#wcpEditContent');
        if (content) content.addEventListener('click', () => { editing.view = 'content'; renderEditor(); api.editorBody.scrollTop = 0; showInLivePreview(editing.lastSavedPath || '/'); });
        const del = b.querySelector('#wcpDelete');
        if (del) del.addEventListener('click', () => deletePage({ id: editing.id, kind: editing.kind, draft: editing.draft, is_published: editing.isPublished }));
    }

    /** Scroll only the CMS editor pane (scrollIntoView would also scroll the admin page around it). */
    function scrollPaneTo(el, align) {
        const pane = api.editorBody;
        const paneRect = pane.getBoundingClientRect();
        const r = el.getBoundingClientRect();
        let top = pane.scrollTop + (r.top - paneRect.top) - 16;
        if (align === 'center') top -= (pane.clientHeight - r.height) / 2 - 16;
        pane.scrollTo({ top: Math.max(0, top), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    }

    function jump(id) {
        const target = api.editorBody.querySelector('#' + id);
        if (!target) return;
        scrollPaneTo(target, 'start');
        const heading = target.querySelector('h2');
        if (heading) heading.focus({ preventScroll: true });
    }

    /* ── header actions ── */
    function addToMenu() {
        const data = api.getData();
        const path = editing.lastSavedPath || normPath(editing.draft.path);
        data.nav_menu = data.nav_menu || {};
        data.nav_menu.items = data.nav_menu.items || [];
        if (!data.nav_menu.items.some((i) => i.href && normPath(i.href) === path)) {
            data.nav_menu.items.push({ label: editing.draft.title, href: path, group: '', description: '' });
        }
        api.setStatus('Adding to menu…', 'saving');
        send('POST', W.routes.draft, { data })
            .then(() => {
                W.hasUnpublished = true;
                W.siteHasUnpublished = true;
                api.setStatus('Added to the menu as a top-level link — arrange it in Navigation menu', 'ok');
                refreshEditor();
            })
            .catch((e) => api.setStatus(e.message, 'error'));
    }

    function goToMenu() {
        flushSave().then(() => { restoreShell(); api.switchModule('nav_menu'); });
    }

    function previewInNewTab() {
        // Open the tab synchronously (popup blockers), then point it at the draft preview.
        const win = window.open('about:blank', '_blank');
        flushSave()
            .then(() => previewUrlFor(editing.lastSavedPath || editing.draft.path))
            .then((url) => {
                if (!url) {
                    if (win) win.close();
                    api.setStatus('Preview isn’t available — check the website connection', 'error');
                    return;
                }
                if (win) { win.opener = null; win.location.href = url; } else { window.open(url, '_blank', 'noopener'); }
            });
    }

    /* ═══════════════════════════ CONTENT (block builder, custom pages) ═══════════════════════════ */

    function blockHtml(block, index, total) {
        const def = BLOCKS[block.type];
        if (!def) return '';
        const base = `blocks.${index}.data`;
        let html = `<section class="wcp-card wcp-block" data-block="${index}" aria-label="${esc(def.label)} block">
            <div class="wcp-block-head">
                <span class="wcp-block-name"><span class="wcp-tile wcp-tile--sm">${icon(def.icon)}</span>${esc(def.label)}</span>
                <span class="wcp-block-tools">
                    <button type="button" class="wcp-icon-btn" data-move="${index}" data-dir="-1" ${index === 0 ? 'disabled' : ''} aria-label="Move ${esc(def.label)} up">${icon('solar:arrow-up-linear')}</button>
                    <button type="button" class="wcp-icon-btn" data-move="${index}" data-dir="1" ${index === total - 1 ? 'disabled' : ''} aria-label="Move ${esc(def.label)} down">${icon('solar:arrow-down-linear')}</button>
                    <button type="button" class="wcp-icon-btn is-danger" data-remove-block="${index}" aria-label="Remove ${esc(def.label)} block">${icon('solar:trash-bin-trash-linear')}</button>
                </span>
            </div>
            <div class="wcm-form-grid">${def.fields.map((f) => bpField(f, (block.data || {})[f.key], `${base}.${f.key}`)).join('')}</div>`;
        if (def.items) {
            const items = (block.data || {}).items || [];
            html += `<div class="wcm-block-items"><div class="wcm-repeater-header"><h4>${esc(def.items.label)}</h4>
                <button type="button" class="wcp-btn wcp-btn--outline wcp-btn--sm" data-add-item="${index}">${icon('solar:add-circle-linear')}Add</button></div>`;
            items.forEach((item, i) => {
                html += `<div class="wcm-repeater-item"><div class="wcm-repeater-item-header"><span>${i + 1}</span>
                    <button type="button" class="wcm-repeater-remove" data-remove-item="${index}" data-item="${i}" aria-label="Remove item ${i + 1}">&times;</button></div>
                    <div class="wcm-repeater-fields">${def.items.fields.map((f) => bpField(f, item[f.key], `${base}.items.${i}.${f.key}`)).join('')}</div></div>`;
            });
            html += '</div>';
        }
        return html + '</section>';
    }

    function renderContent() {
        const d = editing.draft;
        const blocks = d.blocks || [];
        const name = d.title || 'New page';
        setModuleHeading(crumbs(name), '');
        bindCrumbs();

        api.editorBody.innerHTML = `<div class="wcp wcp-editor wcp-content">
            <button type="button" class="wcp-back" id="wcpBackSettings">${icon('solar:arrow-left-linear')}Page settings</button>
            <header class="wcp-head">
                <div class="wcp-head-main">
                    <span class="wcp-tile wcp-tile--navy wcp-tile--lg">${icon('solar:widget-2-linear')}</span>
                    <div class="wcp-head-text">
                        <div class="wcp-head-title"><h1>Page content</h1></div>
                        <span class="wcp-muted">${esc(name)} · blocks appear on the page in this order. Changes save automatically.</span>
                    </div>
                </div>
            </header>
            <div class="wcp-blocks">${blocks.map((bl, i) => blockHtml(bl, i, blocks.length)).join('') || '<p class="wcp-empty-row">No blocks yet — add the first one below.</p>'}</div>
            <div class="wcp-add-block">
                <label for="wcpBlockType" class="visually-hidden">Block type</label>
                <select class="wcp-input" id="wcpBlockType">${Object.entries(BLOCKS).map(([k, bl]) => `<option value="${k}">${esc(bl.label)}</option>`).join('')}</select>
                <button type="button" class="wcp-btn wcp-btn--primary" id="wcpAddBlock">${icon('solar:add-circle-linear')}Add block</button>
            </div>
        </div>`;
        bindContent();
        updateSavebar();
    }

    function bindContent() {
        const b = api.editorBody;
        const d = editing.draft;
        const structural = () => { editing.dirty = true; renderContent(); scheduleSave(); };

        b.querySelector('#wcpBackSettings').addEventListener('click', () => { editing.view = 'settings'; renderEditor(); api.editorBody.scrollTop = 0; });
        b.querySelectorAll('[data-bp]').forEach((el) => {
            el.addEventListener(el.type === 'checkbox' || el.tagName === 'SELECT' ? 'change' : 'input', () => {
                setPath(d, el.dataset.bp, el.type === 'checkbox' ? el.checked : el.value);
                scheduleSave();
            });
            el.addEventListener('blur', () => { if (editing.dirty) flushSave(); });
        });
        b.querySelectorAll('[data-bp-upload]').forEach((input) => input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            if (!/^image\//.test(file.type) || file.size > IMAGE_MAX_BYTES) {
                api.setStatus('Please choose an image up to 5 MB', 'error');
                return;
            }
            const fd = new FormData();
            fd.append('file', file);
            fd.append('field', 'page');
            send('POST', W.routes.upload, fd).then((res) => {
                setPath(d, input.dataset.bpUpload, res.url);
                structural();
            }).catch((e) => api.setStatus(e.message, 'error'));
        }));
        b.querySelector('#wcpAddBlock').addEventListener('click', () => {
            const type = b.querySelector('#wcpBlockType').value;
            d.blocks = d.blocks || [];
            d.blocks.push({ id: uid(), type, data: BLOCKS[type].items ? { items: [{}] } : {} });
            structural();
        });
        b.querySelectorAll('[data-remove-block]').forEach((x) => x.addEventListener('click', () => { d.blocks.splice(+x.dataset.removeBlock, 1); structural(); }));
        b.querySelectorAll('[data-move]').forEach((x) => x.addEventListener('click', () => {
            const i = +x.dataset.move;
            const j = i + (+x.dataset.dir);
            [d.blocks[i], d.blocks[j]] = [d.blocks[j], d.blocks[i]];
            structural();
        }));
        b.querySelectorAll('[data-add-item]').forEach((x) => x.addEventListener('click', () => {
            const blk = d.blocks[+x.dataset.addItem];
            blk.data = blk.data || {};
            blk.data.items = blk.data.items || [];
            blk.data.items.push({});
            structural();
        }));
        b.querySelectorAll('[data-remove-item]').forEach((x) => x.addEventListener('click', () => {
            d.blocks[+x.dataset.removeItem].data.items.splice(+x.dataset.item, 1);
            structural();
        }));
    }

    /* ═══════════════════════════ shell integration ═══════════════════════════ */

    function renderPages(a) {
        api = a;
        if (editing) {
            renderEditor();
            if (save.state === 'idle') setSaveState('idle');
        } else {
            renderList();
        }
    }

    // Leaving the Pages module: flush the page draft and give the save bar back to the shell.
    document.addEventListener('click', (e) => {
        const nav = e.target.closest('.wcm-nav-item');
        if (!nav || nav.dataset.module === 'pages') return;
        if (editing) flushSave();
        closeRowMenu();
        restoreShell();
    }, true);

    // Save bar "Discard" acts on the open page only.
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmDiscard');
        if (!btn || !editing || activeModule() !== 'pages') return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (!editing.id || !editing.isPublished) return;
        clearTimeout(save.timer);
        confirmDialog({
            title: 'Discard changes to this page?',
            message: 'The page goes back to the version that is live on your website.',
            note: 'This can’t be undone.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            const wait = save.inflight || Promise.resolve();
            wait.then(() => send('POST', `${H.routes.pages}/${editing.id}/discard`)).then((res) => {
                pages = res.pages;
                const view = editing.view;
                openEditor(res.page.id);
                editing.view = view;
                if (view === 'content') renderEditor();
                api.setStatus(res.message, 'ok');
            }).catch((err) => api.setStatus(err.message, 'error'));
        });
    }, true);

    // Save bar "Publish": make sure the page's last edits are saved first, then come back to it after the reload.
    let publishing = false;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmPublish');
        if (!btn || !editing || publishing || activeModule() !== 'pages') return;
        e.preventDefault();
        e.stopImmediatePropagation();
        flushSave().then((ok) => {
            if (ok === false || save.state === 'invalid' || save.state === 'error' || save.state === 'incomplete') {
                api.setStatus(save.state === 'incomplete' ? 'Add a page name and web address before publishing' : 'Fix the highlighted field before publishing', 'error');
                return;
            }
            try { sessionStorage.setItem(REOPEN_KEY, JSON.stringify({ id: editing.id, view: editing.view })); } catch (err) { /* optional */ }
            window.__wcpSkipUnloadWarning = true;
            publishing = true;
            btn.click();
            publishing = false;
        });
    }, true);

    ext.renderers.pages = renderPages;

    /** AI assistant: before it runs, save the page being edited so nothing typed is lost. */
    ext.flushPages = () => (editing && editing.id ? flushSave() : Promise.resolve(true));

    /** AI assistant changed pages on the server: take the fresh list and redraw what is open. */
    ext.setPages = (list) => {
        if (!Array.isArray(list)) return;
        pages = list;
        if (activeModule() !== 'pages' || !api) return;
        if (editing && editing.id && !editing.dirty) {
            const view = editing.view;
            openEditor(editing.id);
            if (editing && editing.view !== view) { editing.view = view; renderEditor(); }
        } else if (!editing) {
            renderList();
        }
    };

    // After "Publish" reloads the CMS, reopen the page that was being edited.
    setTimeout(() => {
        let reopen = null;
        try {
            reopen = JSON.parse(sessionStorage.getItem(REOPEN_KEY) || 'null');
            sessionStorage.removeItem(REOPEN_KEY);
        } catch (err) { /* optional */ }
        if (!reopen || !reopen.id || !pages.some((p) => p.id === reopen.id)) return;
        const nav = document.querySelector('.wcm-nav-item[data-module="pages"]');
        if (!nav) return;
        nav.click();
        openEditor(reopen.id);
        if (reopen.view === 'content') { editing.view = 'content'; renderEditor(); }
    }, 0);
})();
