/**
 * Website CMS → Navigation menu (connected website).
 *
 * Storage stays the flat list the website already reads (cmsData.nav_menu.items):
 *   { label, href, group, description } — a non-empty `group` puts the link in the dropdown of
 *   that name; the dropdown sits where its first link is. Here it is edited as a tree:
 *     top level  = link { label, href } or dropdown { label, children[] }
 *   and written back in the same flat shape, so the public site needs no change. An empty
 *   dropdown is kept as a placeholder row { label:'', href:'', group:'Heading' }, which the
 *   website skips (it ignores rows without a label or link), so empty dropdowns never show.
 *
 * Changes autosave as a site draft; the CMS "Publish" button makes them live.
 * Loads after website-cms-headless.js / website-cms-pages.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc, clone } = ext.shared;
    const MODULE = 'nav_menu';
    const AUTOSAVE_MS = 900;
    const REOPEN_KEY = 'wcn:reopen';
    const CUSTOM = '__custom__';

    let api = null;
    let tree = [];
    let selected = null; // { kind: 'node', id } | { kind: 'cta' } | null
    let seq = 0;
    let customTarget = {}; // node id → true while "Another web address…" is chosen
    const save = { timer: null, inflight: null, state: 'idle', savedAt: null, dirty: false };

    const nid = () => 'n' + (++seq);
    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;

    /* ───────────────────────── data ───────────────────────── */

    function nav() {
        const data = api.getData();
        data.nav_menu = data.nav_menu || {};
        data.nav_menu.items = Array.isArray(data.nav_menu.items) ? data.nav_menu.items : [];
        return data.nav_menu;
    }

    function fromFlat(items) {
        const out = [];
        (items || []).forEach((it) => {
            if (!it || typeof it !== 'object') return;
            const group = String(it.group || '').trim();
            const node = { id: nid(), label: String(it.label || ''), href: String(it.href || ''), description: String(it.description || '') };
            if (!group) {
                out.push({ ...node, kind: 'link' });
                return;
            }
            let dd = out.find((n) => n.kind === 'dropdown' && n.label === group);
            if (!dd) {
                dd = { id: nid(), kind: 'dropdown', label: group, open: true, children: [] };
                out.push(dd);
            }
            if (node.label || node.href) dd.children.push(node); // label + link both empty = empty-dropdown placeholder
        });
        return out;
    }

    function toFlat(nodes) {
        const out = [];
        nodes.forEach((n) => {
            if (n.kind === 'link') {
                out.push({ label: n.label, href: n.href, group: '', description: n.description || '' });
            } else if (n.children.length) {
                n.children.forEach((c) => out.push({ label: c.label, href: c.href, group: n.label, description: c.description || '' }));
            } else {
                out.push({ label: '', href: '', group: n.label, description: '' });
            }
        });
        return out;
    }

    /** Same clean-up the server applies (WebsiteCmsService::normaliseNavItems), for comparisons. */
    function normaliseFlat(items) {
        return (items || []).map((i) => ({
            label: String(i.label || '').trim(), href: String(i.href || '').trim(),
            group: String(i.group || '').trim(), description: String(i.description || '').trim(),
        })).filter((i) => i.label || i.href || i.group);
    }

    function find(id) {
        for (const n of tree) {
            if (n.id === id) return { node: n, parent: null, list: tree };
            if (n.kind === 'dropdown') {
                const c = n.children.find((x) => x.id === id);
                if (c) return { node: c, parent: n, list: n.children };
            }
        }
        return null;
    }

    function pages() {
        return (ext.getPages ? ext.getPages() : H.pages) || [];
    }

    function cleanPath(href) {
        return String(href || '').split(/[?#]/)[0].replace(/(.)\/+$/, '$1');
    }

    function pageFor(href) {
        if (!String(href || '').startsWith('/')) return null;
        const path = cleanPath(href);
        return pages().find((p) => p.draft && p.draft.path === path) || null;
    }

    function validHref(href) {
        const h = String(href || '').trim();
        return /^\/(?!\/)/.test(h) || h === '/' || /^https?:\/\/[^\s]+\.[^\s]+$/i.test(h) || /^(mailto|tel):\S+$/i.test(h) || /^#\S*$/.test(h);
    }

    function site() {
        const d = api.getData();
        const b = d.branding || {};
        const t = d.theme || {};
        const name = b.company_name || 'Your school';
        return {
            short: b.short_name || name,
            logo: b.logo || '',
            initials: name.split(/[\s-]+/).filter((w) => /^[A-Za-z]/.test(w)).slice(0, 2).map((w) => w[0].toUpperCase()).join('') || 'W',
            navbar: t.navbar || '#0F2646',
            accent: t.accent || '#AE9A66',
        };
    }

    /* ───────────────────────── validation ───────────────────────── */

    function issues() {
        const map = {};
        const add = (id, field, msg, level) => {
            map[id] = map[id] || { errors: {}, warnings: [] };
            if (level === 'warn') map[id].warnings.push(msg);
            else map[id].errors[field] = msg;
        };
        const linkChecks = (n) => {
            if (!n.label.trim()) add(n.id, 'label', 'Add the text shown in the menu.');
            if (!n.href.trim()) add(n.id, 'href', 'Choose where this link goes.');
            else if (!validHref(n.href)) add(n.id, 'href', 'Use one of your pages, a full web address (https://…) or a path starting with /.');
            const page = pageFor(n.href);
            if (page && page.kind === 'custom' && page.draft.hidden) {
                add(n.id, null, `“${page.draft.title}” is switched off, so visitors who follow this link see “page not found”.`, 'warn');
            } else if (page && !page.is_published) {
                add(n.id, null, `“${page.draft.title}” is a draft and isn’t on your website yet — publish it or the link won’t work.`, 'warn');
            }
        };
        tree.forEach((n) => {
            if (n.kind === 'link') return linkChecks(n);
            if (!n.label.trim()) add(n.id, 'label', 'Give the dropdown a heading.');
            else if (tree.some((o) => o !== n && o.kind === 'dropdown' && o.label.trim() === n.label.trim())) {
                add(n.id, 'label', 'Another dropdown already uses this heading — the website would merge them.');
            }
            if (!n.children.length) add(n.id, null, 'Empty dropdowns are hidden on the website. Add a link to show it.', 'warn');
            n.children.forEach(linkChecks);
        });
        const m = nav();
        if (!String(m.cta_text || '').trim()) add('cta', 'label', 'Add the button text.');
        if (!String(m.cta_url || '').trim()) add('cta', 'href', 'Choose where the button goes.');
        else if (!validHref(m.cta_url)) add('cta', 'href', 'Use one of your pages, a full web address (https://…) or a path starting with /.');
        return map;
    }

    const hasErrors = (map) => Object.values(map).some((v) => Object.keys(v.errors).length);

    /* ───────────────────────── save / status / shell ───────────────────────── */

    function publishedSnapshot() {
        const p = W.publishedNavMenu;
        if (!p) return null;
        return JSON.stringify({ items: normaliseFlat(p.items), cta_text: String(p.cta_text || '').trim(), cta_url: String(p.cta_url || '').trim() });
    }

    function currentSnapshot() {
        const m = nav();
        return JSON.stringify({ items: normaliseFlat(m.items), cta_text: String(m.cta_text || '').trim(), cta_url: String(m.cta_url || '').trim() });
    }

    function matchesLive() {
        const pub = publishedSnapshot();
        return pub !== null && pub === currentSnapshot();
    }

    function updateSavebar() {
        if (activeModule() !== MODULE) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        const saveDraft = document.getElementById('wcmSaveDraft');
        const discard = document.getElementById('wcmDiscard');
        if (saveDraft) saveDraft.style.setProperty('display', 'none', 'important'); // menu autosaves
        const live = matchesLive();
        let text;
        let tone = 'warn';
        if (save.state === 'invalid') text = 'Fix the highlighted menu item to save your changes.';
        else if (save.dirty || save.timer || save.inflight) text = 'Saving your menu changes…';
        else if (live) { text = 'Your menu matches the live website.'; tone = 'ok'; }
        else text = 'Menu changes saved as a draft — publish to show them on your website.';
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = !live && !!W.publishedNavMenu;
            discard.disabled = !can;
            discard.title = can ? 'Put the menu back to the version that is live on your website' : '';
        }
    }

    let shellHint = null;
    function rememberShell() {
        if (shellHint !== null) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        shellHint = hint ? hint.innerHTML : '';
    }

    function restoreShell() {
        if (shellHint === null) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        if (hint) hint.innerHTML = shellHint;
        const saveDraft = document.getElementById('wcmSaveDraft');
        if (saveDraft) saveDraft.style.removeProperty('display');
        const discard = document.getElementById('wcmDiscard');
        if (discard) { discard.disabled = false; discard.title = ''; }
        shellHint = null;
    }

    function setState(state) {
        save.state = state;
        if (api && activeModule() === MODULE) {
            if (state === 'saving') api.setStatus('Saving…', 'saving');
            else if (state === 'pending') api.setStatus('Unsaved changes…', 'saving');
            else if (state === 'saved') api.setStatus('Menu saved as a draft just now', 'ok');
            else if (state === 'invalid') api.setStatus('Couldn’t save – fix the highlighted item', 'error');
            else if (state === 'error') api.setStatus('Couldn’t save the menu – check your connection', 'error');
        }
        updateSavebar();
    }

    function scheduleSave() {
        save.dirty = true;
        clearTimeout(save.timer);
        save.timer = null;
        if (hasErrors(issues())) { setState('invalid'); return; }
        setState('pending');
        save.timer = setTimeout(flushSave, AUTOSAVE_MS);
    }

    function flushSave() {
        clearTimeout(save.timer);
        save.timer = null;
        if (save.inflight) return save.inflight.then(() => (save.dirty ? flushSave() : true));
        if (!save.dirty) return Promise.resolve(true);
        if (hasErrors(issues())) { setState('invalid'); return Promise.resolve(false); }
        save.dirty = false;
        setState('saving');
        save.inflight = fetch(W.routes.draft, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' },
            body: JSON.stringify({ data: api.getData() }),
        }).then((r) => {
            if (!r.ok) throw new Error('save failed');
            // Site settings other than the menu that were already waiting to be published.
            if (otherSettingsPending === null) otherSettingsPending = !!W.siteHasUnpublished;
            W.siteHasUnpublished = otherSettingsPending || !matchesLive();
            W.hasUnpublished = W.siteHasUnpublished || W.hasUnpublished;
            save.savedAt = Date.now();
            setState(save.dirty ? 'pending' : 'saved');
            if (api.refreshPreview) api.refreshPreview();
            return true;
        }).catch(() => {
            save.dirty = true;
            setState('error');
            return false;
        }).finally(() => { save.inflight = null; updateSavebar(); });
        return save.inflight;
    }

    window.addEventListener('beforeunload', (e) => {
        if (window.__wcnSkipUnloadWarning) return;
        if (save.timer || save.inflight || (save.dirty && save.state !== 'idle')) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    /** Every edit goes through here: write the flat list, redraw, save. */
    function commit(opts) {
        nav().items = toFlat(tree);
        renderTree(opts && opts.focus);
        renderPreview();
        refreshPanelIssues();
        scheduleSave();
    }

    /* ═══════════════════════════ render ═══════════════════════════ */

    function setModuleHeading() {
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'Navigation menu';
        if (desc) { desc.textContent = 'The links across the top of every page. The order here is the order visitors see.'; desc.hidden = false; }
    }

    // Were non-menu site settings unpublished when the page loaded? Known for sure only when the
    // menu itself matched the live site then (the server sends one flag for all site settings).
    let otherSettingsPending = null;

    function renderNav(a) {
        api = a;
        rememberShell();
        setModuleHeading();
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;
        tree = fromFlat(nav().items);
        if (selected && selected.kind === 'node') selected = null;

        api.editorBody.innerHTML = `<div class="wcp wcn">
            <div class="wcn-actions">
                <button type="button" class="wcp-btn wcp-btn--outline" id="wcnAddDropdown">${icon('solar:folder-with-files-linear')}Add dropdown</button>
                <button type="button" class="wcp-btn wcp-btn--primary" id="wcnAddLink">${icon('solar:add-circle-linear')}Add link</button>
            </div>

            <div class="wcn-grid">
                <section class="wcp-card wcn-structure" aria-labelledby="wcnTreeH">
                    <div class="wcn-structure-head">
                        <div>
                            <h2 id="wcnTreeH" class="wcn-h2">Menu structure</h2>
                            <p class="wcp-card-desc">Drag the ⠿ handle to change the order. Links can also be dragged from one dropdown to another.</p>
                        </div>
                        <div class="wcn-legend" aria-hidden="true">
                            <span><span class="wcp-tile wcn-tile-xs">${icon('solar:link-linear')}</span>Link</span>
                            <span><span class="wcp-tile wcp-tile--gold wcn-tile-xs">${icon('solar:folder-linear')}</span>Dropdown (groups links)</span>
                        </div>
                    </div>
                    <ul class="wcn-tree" id="wcnTree" aria-label="Menu items, in order"></ul>
                </section>

                <div class="wcn-side">
                    <section class="wcp-card wcn-panel" id="wcnPanel" aria-live="polite"></section>
                    <section class="wcp-card wcn-cta-card" id="wcnCtaCard" aria-labelledby="wcnCtaH">
                        <div class="wcn-cta-head">
                            <div>
                                <h2 id="wcnCtaH" class="wcn-h3">Header button</h2>
                                <p class="wcp-hint">The highlighted button at the right of the menu.</p>
                            </div>
                            <span class="wcn-cta-sample" id="wcnCtaSample"></span>
                        </div>
                        <div class="wcn-cta-fields">
                            <div class="wcp-field">
                                <label for="wcnCtaText">Button text</label>
                                <input type="text" id="wcnCtaText" class="wcp-input" maxlength="40" autocomplete="off" aria-describedby="wcnCtaTextErr">
                                <p class="wcp-error" id="wcnCtaTextErr" hidden></p>
                            </div>
                            <div class="wcp-field">
                                <label for="wcnCtaTarget">Goes to</label>
                                <select id="wcnCtaTarget" class="wcp-input"></select>
                            </div>
                        </div>
                        <div class="wcp-field" id="wcnCtaUrlField" hidden>
                            <label for="wcnCtaUrl">Web address</label>
                            <input type="text" id="wcnCtaUrl" class="wcp-input" placeholder="https://… or /page" autocomplete="off" spellcheck="false" aria-describedby="wcnCtaUrlErr">
                        </div>
                        <p class="wcp-error" id="wcnCtaUrlErr" hidden></p>
                    </section>
                </div>
            </div>
        </div>`;

        api.editorBody.querySelector('#wcnAddLink').addEventListener('click', addLink);
        api.editorBody.querySelector('#wcnAddDropdown').addEventListener('click', addDropdown);
        bindPreview();
        bindTree();
        bindCta();
        renderTree();
        renderPreview();
        renderPanel();
        renderCta();
        if (save.state === 'idle') updateSavebar(); else setState(save.state);
        if (save.state === 'idle') api.setStatus(matchesLive() ? 'Your menu matches the live website' : 'Menu draft saved', 'ok');
    }

    /* ── live header preview ── */
    function isSelected(id) { return selected && selected.kind === 'node' && selected.id === id; }

    function openDropdownId() {
        if (!selected || selected.kind !== 'node') return null;
        const f = find(selected.id);
        if (!f) return null;
        if (f.node.kind === 'dropdown') return f.node.id;
        return f.parent ? f.parent.id : null;
    }

    function renderPreview() {
        const box = api.editorBody.querySelector('#wcnPreview');
        if (!box) return; // the live preview panel shows the menu now
        if (!box) return;
        const s = site();
        const openId = openDropdownId();
        const shown = (c) => c.label.trim() && c.href.trim(); // the website skips rows without both

        const items = tree.map((n) => {
            if (n.kind === 'link') {
                if (!shown(n)) return `<button type="button" class="wcn-bar-item is-ghost ${isSelected(n.id) ? 'is-selected' : ''}" data-select="${n.id}" title="Not shown on the website until it has text and a link">${esc(n.label || 'Untitled link')}</button>`;
                return `<button type="button" class="wcn-bar-item ${isSelected(n.id) ? 'is-selected' : ''}" data-select="${n.id}" aria-label="Edit ${esc(n.label)}">${esc(n.label)}</button>`;
            }
            const visible = n.children.some(shown);
            const active = isSelected(n.id) || openId === n.id;
            return `<button type="button" class="wcn-bar-item ${active ? 'is-selected' : ''} ${visible ? '' : 'is-ghost'}" data-select="${n.id}" aria-expanded="${openId === n.id}" ${visible ? `aria-label="Edit ${esc(n.label)} dropdown"` : 'title="Hidden on the website until it has links"'}>${esc(n.label || 'Untitled dropdown')}${icon('solar:alt-arrow-down-linear', 'wcn-caret')}</button>`;
        }).join('');

        let panel = '';
        const dd = openId && find(openId);
        if (dd) {
            const kids = dd.node.children.filter(shown);
            panel = `<div class="wcn-dd" id="wcnDdPanel">
                ${kids.length ? kids.map((c) => `<button type="button" class="wcn-dd-item ${isSelected(c.id) ? 'is-selected' : ''}" data-select="${c.id}">
                    <span class="wcn-dd-label">${esc(c.label)}</span>${c.description ? `<span class="wcn-dd-desc">${esc(c.description)}</span>` : ''}</button>`).join('')
                : '<p class="wcn-dd-empty">No links yet — this dropdown is hidden on the website.</p>'}
            </div>`;
        }

        box.innerHTML = `<div class="wcn-bar" style="--wcn-bar:${esc(s.navbar)};--wcn-accent:${esc(s.accent)}">
                <span class="wcn-logo"><span class="wcn-logo-mark">${s.logo ? `<img src="${esc(s.logo)}" alt="">` : esc(s.initials)}</span><span class="wcn-logo-name">${esc(s.short)}</span></span>
                <div class="wcn-bar-items" role="group" aria-label="Menu preview">${items || '<span class="wcn-bar-empty">No links yet</span>'}</div>
                <button type="button" class="wcn-bar-cta ${selected && selected.kind === 'cta' ? 'is-selected' : ''}" data-select-cta aria-label="Edit header button">${esc(nav().cta_text || 'Button')}</button>
            </div>${panel}`;

        // Line the dropdown panel up under its item, like the real header.
        const panelEl = box.querySelector('#wcnDdPanel');
        const trigger = openId && box.querySelector(`.wcn-bar-item[data-select="${openId}"]`);
        if (panelEl && trigger) {
            const max = box.clientWidth - panelEl.offsetWidth;
            panelEl.style.marginLeft = Math.max(0, Math.min(trigger.offsetLeft - 12 - box.querySelector('.wcn-bar-items').scrollLeft, max)) + 'px';
        }
    }

    function bindPreview() {
        api.editorBody.querySelector('#wcnPreview')?.addEventListener('click', (e) => {
            const item = e.target.closest('[data-select]');
            if (item) return select({ kind: 'node', id: item.dataset.select }, { reveal: true });
            if (e.target.closest('[data-select-cta]')) select({ kind: 'cta' }, { focus: '#wcnCtaText' });
        });
    }

    /* ── structure tree ── */
    function rowIcon(kind) {
        return kind === 'dropdown'
            ? `<span class="wcp-tile wcp-tile--gold wcn-tile">${icon('solar:folder-linear')}</span>`
            : `<span class="wcp-tile wcn-tile">${icon('solar:link-linear')}</span>`;
    }

    function issueBadge(map, id) {
        const i = map[id];
        if (!i) return '';
        if (Object.keys(i.errors).length) return `<span class="wcn-issue is-error" title="${esc(Object.values(i.errors)[0])}">${icon('solar:danger-circle-bold')}<span class="visually-hidden">Needs attention: ${esc(Object.values(i.errors)[0])}</span></span>`;
        if (i.warnings.length) return `<span class="wcn-issue is-warn" title="${esc(i.warnings[0])}">${icon('solar:danger-triangle-linear')}<span class="visually-hidden">Warning: ${esc(i.warnings[0])}</span></span>`;
        return '';
    }

    /** Drag handle (replaces the up/down arrows); the arrow keys still work on it. */
    const handle = (kind, id, name) => ext.shared.dragHandle(`data-drag-${kind}="${id}"`, name);

    function renderTree(focusSel) {
        const ul = api.editorBody.querySelector('#wcnTree');
        if (!ul) return;
        const map = issues();
        if (!tree.length) {
            ul.innerHTML = '<li class="wcn-empty">Your menu is empty. Use <strong>Add link</strong> or <strong>Add dropdown</strong> to start.</li>';
            return;
        }
        ul.innerHTML = tree.map((n, i) => {
            const name = n.label || (n.kind === 'dropdown' ? 'Untitled dropdown' : 'Untitled link');
            if (n.kind === 'link') {
                return `<li class="wcn-node" data-id="${n.id}">
                    <div class="wcn-row ${isSelected(n.id) ? 'is-selected' : ''}">
                        ${handle('top', n.id, name)}
                        <span class="wcn-toggle-space" aria-hidden="true"></span>
                        <button type="button" class="wcn-row-main" data-select="${n.id}" ${isSelected(n.id) ? 'aria-current="true"' : ''}>
                            ${rowIcon('link')}
                            <span class="wcn-row-text"><span class="wcn-row-label">${esc(name)}</span><span class="wcp-mono">${esc(n.href || 'No link yet')}</span></span>
                            ${issueBadge(map, n.id)}
                        </button>
                    </div>
                </li>`;
            }
            const count = n.children.length;
            const childId = `wcn-children-${n.id}`;
            return `<li class="wcn-node wcn-node--dropdown ${n.open ? 'is-open' : ''}" data-id="${n.id}">
                <div class="wcn-row ${isSelected(n.id) ? 'is-selected' : ''}">
                    ${handle('top', n.id, name)}
                    <button type="button" class="wcn-toggle" data-toggle="${n.id}" aria-expanded="${n.open}" aria-controls="${childId}" aria-label="${n.open ? 'Collapse' : 'Expand'} ${esc(name)}">${icon('solar:alt-arrow-down-linear')}</button>
                    <button type="button" class="wcn-row-main" data-select="${n.id}" ${isSelected(n.id) ? 'aria-current="true"' : ''}>
                        ${rowIcon('dropdown')}
                        <span class="wcn-row-text"><span class="wcn-row-label">${esc(name)}</span><span class="wcn-row-sub">Dropdown · ${count} link${count === 1 ? '' : 's'}</span></span>
                        ${issueBadge(map, n.id)}
                    </button>
                </div>
                <ul class="wcn-children" data-parent="${n.id}" id="${childId}" ${n.open ? '' : 'hidden'} aria-label="Links in ${esc(name)}">
                    ${n.children.map((c, j) => {
                        const cname = c.label || 'Untitled link';
                        return `<li class="wcn-child" data-id="${c.id}"><div class="wcn-row wcn-row--child ${isSelected(c.id) ? 'is-selected' : ''}">
                            ${handle('child', c.id, cname)}
                            <button type="button" class="wcn-row-main" data-select="${c.id}" ${isSelected(c.id) ? 'aria-current="true"' : ''}>
                                ${rowIcon('link')}
                                <span class="wcn-row-text">
                                    <span class="wcn-row-line"><span class="wcn-row-label">${esc(cname)}</span><span class="wcp-mono">${esc(c.href || 'No link yet')}</span></span>
                                    <span class="wcn-row-sub ${c.description ? '' : 'is-none'}">${esc(c.description || 'No description')}</span>
                                </span>
                                ${issueBadge(map, c.id)}
                            </button>
                        </div></li>`;
                    }).join('')}
                    <li class="wcn-child-add"><button type="button" class="wcn-add-child" data-add-child="${n.id}">${icon('solar:add-circle-linear')}Add link to ${esc(name)}</button></li>
                </ul>
            </li>`;
        }).join('');

        if (focusSel) {
            const el = ul.querySelector(focusSel);
            if (el) el.focus();
        }
    }

    function bindTree() {
        api.editorBody.querySelector('#wcnTree').addEventListener('click', (e) => {
            const sel = e.target.closest('[data-select]');
            const tg = e.target.closest('[data-toggle]');
            const add = e.target.closest('[data-add-child]');
            if (tg) {
                const f = find(tg.dataset.toggle);
                f.node.open = !f.node.open;
                renderTree(`[data-toggle="${f.node.id}"]`);
                return;
            }
            if (add) return addChild(add.dataset.addChild);
            if (sel) select({ kind: 'node', id: sel.dataset.select });
        });

        // Drag and drop (or arrow keys on the handle): top-level items, and links inside dropdowns
        // (also from one dropdown to another).
        const ul = api.editorBody.querySelector('#wcnTree');
        const scroller = api.editorBody;
        ext.shared.sortable(ul, {
            item: 'li.wcn-node', handle: '[data-drag-top]', scroller,
            onDrop: ({ fromIndex, toIndex }) => {
                const [node] = tree.splice(fromIndex, 1);
                tree.splice(toIndex, 0, node);
                commit({ focus: `[data-drag-top="${node.id}"]` });
                api.setStatus(`Moved “${node.label || 'Item'}” to position ${toIndex + 1}`, 'saving');
            },
        });
        ext.shared.sortable(ul, {
            lists: '.wcn-children', item: 'li.wcn-child', handle: '[data-drag-child]', connect: true, tail: '.wcn-child-add', scroller,
            onDrop: ({ fromList, fromIndex, toList, toIndex }) => {
                const from = find(fromList.dataset.parent).node;
                const to = find(toList.dataset.parent).node;
                const [child] = from.children.splice(fromIndex, 1);
                to.children.splice(toIndex, 0, child);
                to.open = true;
                commit({ focus: `[data-drag-child="${child.id}"]` });
                if (selected && selected.id === child.id) renderPanel();
                api.setStatus(from === to ? `Moved “${child.label || 'link'}” to position ${toIndex + 1}` : `Moved “${child.label || 'link'}” to ${to.label || 'the dropdown'}`, 'saving');
            },
        });
    }

    /* ── selection & edit panel ── */
    function select(next, opts) {
        selected = next;
        if (next && next.kind === 'node') {
            const f = find(next.id);
            if (f && f.parent) f.parent.open = true;
        }
        renderTree();
        renderPreview();
        renderPanel();
        renderCta();
        const o = opts || {};
        if (o.reveal) {
            const row = api.editorBody.querySelector(`#wcnTree [data-select="${next.id}"]`);
            if (row) scrollPaneTo(row);
        }
        if (o.focus) {
            const el = api.editorBody.querySelector(o.focus);
            if (el) {
                if (o.reveal !== false) scrollPaneTo(el);
                el.focus({ preventScroll: true });
                if (o.selectText && el.select) el.select();
            }
        }
    }

    function scrollPaneTo(el) {
        const pane = api.editorBody;
        const pr = pane.getBoundingClientRect();
        const r = el.getBoundingClientRect();
        if (r.top >= pr.top + 8 && r.bottom <= pr.bottom - 8) return;
        pane.scrollTo({ top: Math.max(0, pane.scrollTop + r.top - pr.top - pane.clientHeight / 3), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    }

    function pageOptions(href, includeCustom) {
        const current = String(href || '');
        const match = pageFor(current);
        const custom = includeCustom || (current && !match);
        const list = pages().map((p) => {
            const note = p.kind === 'custom' && p.draft.hidden ? ' — switched off' : (!p.is_published ? ' — draft' : '');
            return `<option value="${esc(p.draft.path)}" ${match && match.id === p.id && !includeCustom ? 'selected' : ''}>${esc(p.draft.title || p.draft.path)} (${esc(p.draft.path)})${note}</option>`;
        }).join('');
        return `${!current && !includeCustom ? '<option value="" selected disabled>Choose a page…</option>' : ''}${list}<option value="${CUSTOM}" ${custom ? 'selected' : ''}>Another web address…</option>`;
    }

    function renderPanel() {
        const panel = api.editorBody.querySelector('#wcnPanel');
        if (!panel) return;
        const f = selected && selected.kind === 'node' ? find(selected.id) : null;
        if (!f) {
            panel.classList.add('is-empty');
            panel.innerHTML = `<div class="wcn-panel-empty">${icon('solar:cursor-linear')}<p>Select a menu item on the left to edit it.</p></div>`;
            return;
        }
        panel.classList.remove('is-empty');
        const n = f.node;

        if (n.kind === 'dropdown') {
            const count = n.children.length;
            panel.innerHTML = `
                <p class="wcn-eyebrow-sm">Editing dropdown</p>
                <h2 class="wcn-panel-title" id="wcnPanelTitle">${esc(n.label || 'Untitled dropdown')}</h2>
                <div class="wcp-field">
                    <label for="wcnHeading">Dropdown heading</label>
                    <input type="text" id="wcnHeading" class="wcp-input" value="${esc(n.label)}" maxlength="60" autocomplete="off" aria-describedby="wcnHeadingHint wcnLabelErr">
                    <p class="wcp-hint" id="wcnHeadingHint">Visitors hover or tap this to see the links inside it.</p>
                    <p class="wcp-error" id="wcnLabelErr" hidden></p>
                </div>
                <div class="wcn-info">${icon('solar:folder-linear')}<span id="wcnDdCount">This dropdown contains ${count} link${count === 1 ? '' : 's'}. Use “Where it appears” on a link to move it in or out.</span></div>
                <p class="wcp-warn" id="wcnWarn" hidden></p>
                <button type="button" class="wcp-btn wcp-btn--outline wcn-full" id="wcnAddChildBtn">${icon('solar:add-circle-linear')}Add a link to this dropdown</button>
                <div class="wcn-panel-foot">
                    <button type="button" class="wcp-link-danger" id="wcnRemove">${icon('solar:trash-bin-trash-linear')}Remove dropdown and its links</button>
                </div>`;
            panel.querySelector('#wcnHeading').addEventListener('input', (e) => {
                n.label = e.target.value;
                panel.querySelector('#wcnPanelTitle').textContent = n.label || 'Untitled dropdown';
                commit();
            });
            panel.querySelector('#wcnHeading').addEventListener('blur', () => { if (save.dirty) flushSave(); });
            panel.querySelector('#wcnAddChildBtn').addEventListener('click', () => addChild(n.id));
            panel.querySelector('#wcnRemove').addEventListener('click', () => removeDropdown(n));
            refreshPanelIssues();
            return;
        }

        const inDropdown = !!f.parent;
        const custom = !!customTarget[n.id] || (n.href && !pageFor(n.href));
        const parents = tree.filter((x) => x.kind === 'dropdown');
        panel.innerHTML = `
            <p class="wcn-eyebrow-sm">Editing link</p>
            <h2 class="wcn-panel-title" id="wcnPanelTitle">${esc(n.label || 'Untitled link')}</h2>
            <div class="wcp-field">
                <label for="wcnLabel">Text in the menu</label>
                <input type="text" id="wcnLabel" class="wcp-input" value="${esc(n.label)}" maxlength="60" autocomplete="off" aria-describedby="wcnLabelErr">
                <p class="wcp-error" id="wcnLabelErr" hidden></p>
            </div>
            <div class="wcp-field">
                <label for="wcnTarget">Goes to</label>
                <select id="wcnTarget" class="wcp-input" aria-describedby="wcnTargetHint">${pageOptions(n.href, custom)}</select>
                <p class="wcp-hint" id="wcnTargetHint">Pick one of your pages, or choose “Another web address”.</p>
            </div>
            <div class="wcp-field" id="wcnUrlField" ${custom ? '' : 'hidden'}>
                <label for="wcnUrl">Web address</label>
                <input type="text" id="wcnUrl" class="wcp-input" value="${esc(n.href)}" placeholder="https://… or /page" autocomplete="off" spellcheck="false" aria-describedby="wcnHrefErr">
            </div>
            <p class="wcp-error" id="wcnHrefErr" hidden></p>
            <p class="wcp-warn" id="wcnWarn" hidden></p>
            <div class="wcp-field">
                <label for="wcnParent">Where it appears</label>
                <select id="wcnParent" class="wcp-input">
                    <option value="" ${!inDropdown ? 'selected' : ''}>Top level of the menu</option>
                    ${parents.map((p) => `<option value="${p.id}" ${f.parent && f.parent.id === p.id ? 'selected' : ''}>Inside “${esc(p.label || 'Untitled')}” dropdown</option>`).join('')}
                </select>
            </div>
            ${inDropdown ? `<div class="wcp-field">
                <label for="wcnDesc">Short description <span class="wcp-muted">(optional)</span></label>
                <textarea id="wcnDesc" class="wcp-input wcp-textarea wcn-desc" rows="2" maxlength="120" aria-describedby="wcnDescHint">${esc(n.description)}</textarea>
                <p class="wcp-hint" id="wcnDescHint">Shown under the link inside the dropdown.</p>
            </div>` : ''}
            <div class="wcn-panel-foot">
                <button type="button" class="wcp-link-danger" id="wcnRemove">${icon('solar:trash-bin-trash-linear')}Remove from menu</button>
                <span class="wcp-hint">The page itself is kept</span>
            </div>`;

        const $ = (s) => panel.querySelector(s);
        const flushOnBlur = (el) => el.addEventListener('blur', () => { if (save.dirty) flushSave(); });
        $('#wcnLabel').addEventListener('input', (e) => {
            n.label = e.target.value;
            $('#wcnPanelTitle').textContent = n.label || 'Untitled link';
            commit();
        });
        flushOnBlur($('#wcnLabel'));
        $('#wcnTarget').addEventListener('change', (e) => {
            if (e.target.value === CUSTOM) {
                customTarget[n.id] = true;
                $('#wcnUrlField').hidden = false;
                $('#wcnUrl').focus();
                return;
            }
            delete customTarget[n.id];
            $('#wcnUrlField').hidden = true;
            const page = pageFor(e.target.value);
            // Suggest the page's name when the text is still the placeholder.
            if (page && (!n.label.trim() || n.label === 'New link')) {
                n.label = page.draft.title;
                $('#wcnLabel').value = n.label;
                $('#wcnPanelTitle').textContent = n.label;
            }
            n.href = e.target.value;
            $('#wcnUrl').value = n.href;
            commit();
            flushSave();
        });
        $('#wcnUrl').addEventListener('input', (e) => { n.href = e.target.value.trim(); commit(); });
        flushOnBlur($('#wcnUrl'));
        $('#wcnParent').addEventListener('change', (e) => reparent(n, e.target.value));
        if ($('#wcnDesc')) {
            $('#wcnDesc').addEventListener('input', (e) => { n.description = e.target.value; commit(); });
            flushOnBlur($('#wcnDesc'));
        }
        $('#wcnRemove').addEventListener('click', () => removeLink(n));
        refreshPanelIssues();
    }

    function showError(id, msg) {
        const box = api.editorBody.querySelector('#' + id);
        if (!box) return;
        box.hidden = !msg;
        box.innerHTML = msg ? `${icon('solar:danger-circle-linear')}<span>${esc(msg)}</span>` : '';
    }

    function refreshPanelIssues() {
        const map = issues();
        const f = selected && selected.kind === 'node' ? find(selected.id) : null;
        if (f) {
            const i = map[f.node.id] || { errors: {}, warnings: [] };
            showError('wcnLabelErr', i.errors.label);
            showError('wcnHrefErr', i.errors.href);
            const lbl = api.editorBody.querySelector('#wcnLabel, #wcnHeading');
            if (lbl) lbl.setAttribute('aria-invalid', i.errors.label ? 'true' : 'false');
            const url = api.editorBody.querySelector('#wcnUrl');
            if (url) url.setAttribute('aria-invalid', i.errors.href ? 'true' : 'false');
            const warn = api.editorBody.querySelector('#wcnWarn');
            if (warn) {
                warn.hidden = !i.warnings.length;
                warn.innerHTML = i.warnings.length ? `${icon('solar:danger-triangle-linear')}<span>${esc(i.warnings[0])}</span>` : '';
            }
            const count = api.editorBody.querySelector('#wcnDdCount');
            if (count && f.node.kind === 'dropdown') {
                const c = f.node.children.length;
                count.textContent = `This dropdown contains ${c} link${c === 1 ? '' : 's'}. Use “Where it appears” on a link to move it in or out.`;
            }
        }
        const cta = map.cta || { errors: {} };
        showError('wcnCtaTextErr', cta.errors.label);
        showError('wcnCtaUrlErr', cta.errors.href);
    }

    /* ── header button card ── */
    function renderCta() {
        const card = api.editorBody.querySelector('#wcnCtaCard');
        if (!card) return;
        const m = nav();
        card.classList.toggle('is-selected', !!(selected && selected.kind === 'cta'));
        const s = site();
        const sample = card.querySelector('#wcnCtaSample');
        sample.style.setProperty('--wcn-accent', s.accent);
        sample.textContent = m.cta_text || 'Button';
        const text = card.querySelector('#wcnCtaText');
        if (document.activeElement !== text) text.value = m.cta_text || '';
        const sel = card.querySelector('#wcnCtaTarget');
        const custom = !!customTarget.cta || (m.cta_url && !pageFor(m.cta_url));
        if (document.activeElement !== sel) sel.innerHTML = pageOptions(m.cta_url, custom);
        card.querySelector('#wcnCtaUrlField').hidden = !custom;
        const url = card.querySelector('#wcnCtaUrl');
        if (document.activeElement !== url) url.value = m.cta_url || '';
        refreshPanelIssues();
    }

    function bindCta() {
        const card = api.editorBody.querySelector('#wcnCtaCard');
        const m = () => nav();
        const after = () => { renderPreview(); refreshPanelIssues(); scheduleSave(); card.querySelector('#wcnCtaSample').textContent = m().cta_text || 'Button'; };
        card.querySelector('#wcnCtaText').addEventListener('input', (e) => { m().cta_text = e.target.value; after(); });
        card.querySelector('#wcnCtaTarget').addEventListener('change', (e) => {
            if (e.target.value === CUSTOM) {
                customTarget.cta = true;
                card.querySelector('#wcnCtaUrlField').hidden = false;
                card.querySelector('#wcnCtaUrl').focus();
                return;
            }
            delete customTarget.cta;
            card.querySelector('#wcnCtaUrlField').hidden = true;
            m().cta_url = e.target.value;
            card.querySelector('#wcnCtaUrl').value = e.target.value;
            after();
            flushSave();
        });
        card.querySelector('#wcnCtaUrl').addEventListener('input', (e) => { m().cta_url = e.target.value.trim(); after(); });
        card.querySelectorAll('input').forEach((el) => el.addEventListener('blur', () => { if (save.dirty) flushSave(); }));
        card.addEventListener('focusin', () => {
            if (selected && selected.kind === 'cta') return;
            selected = { kind: 'cta' };
            card.classList.add('is-selected');
            renderTree();
            renderPreview();
            renderPanel();
        });
    }

    /* ═══════════════════════════ actions ═══════════════════════════ */

    function reparent(node, targetId) {
        const f = find(node.id);
        if (!f) return;
        f.list.splice(f.list.indexOf(node), 1);
        if (!targetId) {
            // Out of a dropdown: place it right after that dropdown.
            const at = f.parent ? tree.indexOf(f.parent) + 1 : tree.length;
            tree.splice(at, 0, { id: node.id, kind: 'link', label: node.label, href: node.href, description: node.description || '' });
        } else {
            const dd = find(targetId).node;
            dd.children.push({ id: node.id, label: node.label, href: node.href, description: node.description || '' });
            dd.open = true;
        }
        commit();
        renderPanel();
        const parentSel = api.editorBody.querySelector('#wcnParent');
        if (parentSel) parentSel.focus();
    }

    function addLink() {
        const n = { id: nid(), kind: 'link', label: 'New link', href: '/', description: '' };
        tree.push(n);
        selected = { kind: 'node', id: n.id };
        commit();
        select(selected, { reveal: true, focus: '#wcnLabel', selectText: true });
    }

    function addDropdown() {
        let label = 'New dropdown';
        let k = 2;
        while (tree.some((x) => x.kind === 'dropdown' && x.label === label)) label = `New dropdown ${k++}`;
        const n = { id: nid(), kind: 'dropdown', label, open: true, children: [] };
        tree.push(n);
        selected = { kind: 'node', id: n.id };
        commit();
        select(selected, { reveal: true, focus: '#wcnHeading', selectText: true });
    }

    function addChild(dropdownId) {
        const dd = find(dropdownId).node;
        const c = { id: nid(), label: 'New link', href: '/', description: '' };
        dd.children.push(c);
        dd.open = true;
        selected = { kind: 'node', id: c.id };
        commit();
        select(selected, { reveal: true, focus: '#wcnLabel', selectText: true });
    }

    function removeLink(n) {
        const f = find(n.id);
        if (!f) return;
        const idx = f.list.indexOf(n);
        f.list.splice(idx, 1);
        const next = f.list[idx] || f.list[idx - 1] || f.parent;
        selected = next ? { kind: 'node', id: next.id } : null;
        commit();
        renderPreview();
        renderPanel();
        api.setStatus(`Removed “${n.label || 'link'}” from the menu`, 'saving');
        const focus = next && api.editorBody.querySelector(`#wcnTree [data-select="${next.id}"]`);
        if (focus) focus.focus(); else api.editorBody.querySelector('#wcnAddLink').focus();
    }

    function removeDropdown(n) {
        const names = n.children.map((c) => c.label || 'Untitled link');
        const confirmFn = window.CrmUI && window.CrmUI.confirm
            ? window.CrmUI.confirm
            : (o) => Promise.resolve(window.confirm(o.title + '\n\n' + o.message));
        confirmFn({
            title: `Remove the “${n.label || 'untitled'}” dropdown?`,
            message: names.length
                ? `It and its ${names.length} link${names.length === 1 ? '' : 's'} leave the menu: ${names.join(', ')}.`
                : 'It has no links, so nothing else is removed.',
            note: 'The pages themselves are kept. Nothing changes on your website until you publish.',
            label: 'Remove dropdown',
            tone: 'danger',
            icon: 'solar:trash-bin-trash-linear',
        }).then((ok) => {
            if (!ok) return;
            const idx = tree.indexOf(n);
            tree.splice(idx, 1);
            const next = tree[idx] || tree[idx - 1];
            selected = next ? { kind: 'node', id: next.id } : null;
            commit();
            renderPreview();
            renderPanel();
            api.setStatus(`Removed the “${n.label}” dropdown`, 'saving');
        });
    }

    /* ═══════════════════════════ shell integration ═══════════════════════════ */

    // Leaving the menu: save what's pending and give the save bar back to the shell.
    document.addEventListener('click', (e) => {
        const item = e.target.closest('.wcm-nav-item');
        if (!item || item.dataset.module === MODULE || activeModule() !== MODULE) return;
        if (save.dirty) flushSave();
        restoreShell();
    }, true);

    // "Discard" on this screen puts back only the menu (not pages or other settings).
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmDiscard');
        if (!btn || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        const published = W.publishedNavMenu;
        if (!published || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your menu changes?',
            message: 'The menu goes back to the version that is live on your website.',
            note: 'Only the menu is changed — your pages and other settings stay as they are.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            clearTimeout(save.timer);
            const m = nav();
            m.items = clone(published.items || []);
            m.cta_text = published.cta_text || '';
            m.cta_url = published.cta_url || '';
            customTarget = {};
            selected = null;
            save.dirty = true;
            renderNav(api);
            flushSave().then((done) => { if (done) api.setStatus('Menu changes discarded', 'ok'); });
        });
    }, true);

    // "Publish": save any pending menu edit first; block if something is invalid.
    let publishing = false;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmPublish');
        if (!btn || publishing || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (hasErrors(issues())) {
            setState('invalid');
            const first = api.editorBody.querySelector('.wcn-issue.is-error');
            if (first) { scrollPaneTo(first); first.closest('.wcn-row-main').focus({ preventScroll: true }); }
            return;
        }
        flushSave().then((ok) => {
            if (!ok) return;
            try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
            window.__wcnSkipUnloadWarning = true;
            window.__wcpSkipUnloadWarning = true;
            publishing = true;
            btn.click();
            publishing = false;
        });
    }, true);

    ext.renderers[MODULE] = renderNav;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    // After "Publish" reloads the CMS, come back to the menu.
    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);

    // Exposed for tests / other modules.
    ext.navTree = { fromFlat, toFlat };
})();
