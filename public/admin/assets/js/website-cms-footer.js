/**
 * Website CMS → Footer (connected website).
 *
 * Storage stays what the website reads (cmsData.footer_menu):
 *   heading      "First line|Second line" — the part after "|" is shown in gold
 *   description  intro text
 *   items        flat [{ group, label, href }] — `group` is the column heading; columns appear
 *                in the order of their first link. An empty column is kept as a placeholder
 *                row { group, label:'', href:'' }, which the website skips.
 * Here the heading is two inputs and the links are column cards; both are written back in
 * that same shape, so the public site needs no change.
 *
 * Mirrors Website/src/components/layout/Footer.tsx + lib/siteConfig.ts for the preview.
 * Loads after website-cms-headless.js / website-cms-pages.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc, clone } = ext.shared;
    const MODULE = 'footer_menu';
    const AUTOSAVE_MS = 900;
    const REOPEN_KEY = 'wcf:reopen';
    const CUSTOM = '__custom__';
    const TEXT_MAX = 160;
    // Website fallbacks (siteConfig.ts) — shown in the preview when the CMS value is empty.
    const DEFAULT_LINE_1 = 'Give Your Child the';
    const DEFAULT_TEXT = 'An award-winning online Islamic school teaching the British curriculum from Year 1 to A‑Level.';
    const SOCIAL_ORDER = ['facebook', 'instagram', 'youtube', 'tiktok', 'linkedin', 'twitter', 'threads'];
    const SOCIAL_ICONS = {
        facebook: 'mdi:facebook', instagram: 'mdi:instagram', youtube: 'mdi:youtube', tiktok: 'ic:baseline-tiktok',
        linkedin: 'mdi:linkedin', twitter: 'ri:twitter-x-fill', threads: 'ri:threads-line', whatsapp: 'mdi:whatsapp',
    };

    let api = null;
    let cols = [];
    let seq = 0;
    let customTarget = {};
    let otherSettingsPending = null;
    const save = { timer: null, inflight: null, state: 'idle', dirty: false };

    const nid = () => 'f' + (++seq);
    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;

    /* ───────────────────────── data ───────────────────────── */

    function footer() {
        const data = api.getData();
        data.footer_menu = data.footer_menu || {};
        if (!Array.isArray(data.footer_menu.items)) data.footer_menu.items = [];
        return data.footer_menu;
    }

    /** "A|B" → ['A', 'B']; "A" → ['A', '']. Only the first "|" splits, like the website. */
    function splitHeading(heading) {
        const h = String(heading || '');
        const i = h.indexOf('|');
        return i < 0 ? [h.trim(), ''] : [h.slice(0, i).trim(), h.slice(i + 1).trim()];
    }

    /** Mirrors WebsiteCmsService::normaliseFooterHeading(). */
    function joinHeading(first, second) {
        const a = String(first || '').replace(/\|/g, ' ').trim();
        const b = String(second || '').replace(/\|/g, ' ').trim();
        return b ? `${a}|${b}` : a;
    }

    function fromFlat(items) {
        const out = [];
        (items || []).forEach((it) => {
            if (!it || typeof it !== 'object') return;
            const title = String(it.group || '').trim() || 'Links'; // the website's name for links without a column
            let col = out.find((c) => c.title === title);
            if (!col) out.push((col = { id: nid(), title, links: [] }));
            const label = String(it.label || '');
            const href = String(it.href || '');
            if (label || href) col.links.push({ id: nid(), label, href });
        });
        return out;
    }

    function toFlat(columns) {
        const out = [];
        columns.forEach((c) => {
            if (!c.links.length) out.push({ group: c.title, label: '', href: '' });
            c.links.forEach((l) => out.push({ group: c.title, label: l.label, href: l.href }));
        });
        return out;
    }

    function normaliseFlat(items) {
        return (items || []).map((i) => ({
            group: String(i.group || '').trim(), label: String(i.label || '').trim(), href: String(i.href || '').trim(),
        })).filter((i) => i.group || i.label || i.href);
    }

    function findLink(id) {
        for (const c of cols) {
            const l = c.links.find((x) => x.id === id);
            if (l) return { col: c, link: l };
        }
        return null;
    }

    const findCol = (id) => cols.find((c) => c.id === id);
    const pages = () => (ext.getPages ? ext.getPages() : H.pages) || [];

    function pageFor(href) {
        if (!String(href || '').startsWith('/')) return null;
        const path = String(href).split(/[?#]/)[0].replace(/(.)\/+$/, '$1');
        return pages().find((p) => p.draft && p.draft.path === path) || null;
    }

    function validHref(href) {
        const h = String(href || '').trim();
        return /^\/(?!\/)/.test(h) || h === '/' || /^https?:\/\/[^\s]+\.[^\s]+$/i.test(h) || /^(mailto|tel):\S+$/i.test(h) || /^#\S*$/.test(h);
    }

    function pageOptions(href, forceCustom) {
        const current = String(href || '');
        const match = pageFor(current);
        const custom = forceCustom || (current && !match);
        return `${!current && !forceCustom ? '<option value="" selected disabled>Choose a page…</option>' : ''}`
            + pages().map((p) => {
                const note = p.kind === 'custom' && p.draft.hidden ? ' — switched off' : (!p.is_published ? ' — draft' : '');
                return `<option value="${esc(p.draft.path)}" ${match && match.id === p.id && !custom ? 'selected' : ''}>${esc(p.draft.title || p.draft.path)} (${esc(p.draft.path)})${note}</option>`;
            }).join('')
            + `<option value="${CUSTOM}" ${custom ? 'selected' : ''}>Another web address…</option>`;
    }

    /* ───────────────────────── validation ───────────────────────── */

    function issues() {
        const errors = {};   // key → message   (keys: line1, col:<id>, label:<id>, href:<id>)
        const warnings = {}; // key → message   (keys: col:<id>, columns, links:<id>)
        const [line1] = splitHeading(footer().heading);
        if (!line1) errors.line1 = 'Add the first line — without it the website shows its default heading.';

        cols.forEach((c) => {
            if (!c.title.trim()) errors['col:' + c.id] = 'Give this column a heading.';
            else if (cols.some((o) => o !== c && o.title.trim() === c.title.trim())) errors['col:' + c.id] = 'Another column already uses this heading — the website would merge them.';
            if (!c.links.length) warnings['col:' + c.id] = 'Columns with no links are hidden on the website.';
            c.links.forEach((l) => {
                if (!l.label.trim()) errors['label:' + l.id] = 'Add the link text.';
                if (!l.href.trim()) errors['href:' + l.id] = 'Choose where this link goes.';
                else if (!validHref(l.href)) errors['href:' + l.id] = 'Use a page, a full web address (https://…) or a path starting with /.';
                const page = pageFor(l.href);
                if (page && page.kind === 'custom' && page.draft.hidden) warnings['links:' + l.id] = `“${page.draft.title}” is switched off — this link would show “page not found”.`;
                else if (page && !page.is_published) warnings['links:' + l.id] = `“${page.draft.title}” is a draft and isn’t on your website yet.`;
            });
        });
        if (!cols.some((c) => c.links.some((l) => l.label.trim() && l.href.trim()))) {
            warnings.columns = 'With no links, the website shows its own built-in footer columns instead.';
        } else if (cols.filter((c) => c.links.length).length > 3) {
            warnings.columns = 'The website footer is designed for three columns — more will wrap onto a second row.';
        }
        return { errors, warnings };
    }

    const hasErrors = (i) => Object.keys(i.errors).length > 0;

    /* ───────────────────────── save / status / shell ───────────────────────── */

    function snapshot(fm) {
        if (!fm) return null;
        const [a, b] = splitHeading(fm.heading);
        return JSON.stringify({ heading: joinHeading(a, b), description: String(fm.description || '').trim(), items: normaliseFlat(fm.items) });
    }

    const matchesLive = () => { const pub = snapshot(W.publishedFooterMenu); return pub !== null && pub === snapshot(footer()); };

    function updateSavebar() {
        if (activeModule() !== MODULE) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        const saveDraft = document.getElementById('wcmSaveDraft');
        const discard = document.getElementById('wcmDiscard');
        if (saveDraft) saveDraft.style.setProperty('display', 'none', 'important'); // footer autosaves
        const live = matchesLive();
        let text;
        let tone = 'warn';
        if (save.state === 'invalid') text = 'Fix the highlighted field to save your footer changes.';
        else if (save.dirty || save.timer || save.inflight) text = 'Saving your footer changes…';
        else if (live) { text = 'Your footer matches the live website.'; tone = 'ok'; }
        else text = 'Footer changes saved as a draft — publish to show them on your website.';
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = !live && !!W.publishedFooterMenu;
            discard.disabled = !can;
            discard.title = can ? 'Put the footer back to the version that is live on your website' : '';
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
        document.getElementById('wcmSaveDraft')?.style.removeProperty('display');
        const discard = document.getElementById('wcmDiscard');
        if (discard) { discard.disabled = false; discard.title = ''; }
        shellHint = null;
    }

    function setState(state) {
        save.state = state;
        if (api && activeModule() === MODULE) {
            const map = {
                saving: ['Saving…', 'saving'], pending: ['Unsaved changes…', 'saving'], saved: ['Footer saved as a draft just now', 'ok'],
                invalid: ['Couldn’t save – fix the highlighted field', 'error'], error: ['Couldn’t save the footer – check your connection', 'error'],
            };
            if (map[state]) api.setStatus(map[state][0], map[state][1]);
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
            if (otherSettingsPending === null) otherSettingsPending = !!W.siteHasUnpublished;
            W.siteHasUnpublished = otherSettingsPending || !matchesLive();
            W.hasUnpublished = W.siteHasUnpublished || W.hasUnpublished;
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
        if (window.__wcfSkipUnloadWarning) return;
        if (save.timer || save.inflight || (save.dirty && save.state !== 'idle')) { e.preventDefault(); e.returnValue = ''; }
    });

    /** Every edit: write back to storage shape, refresh preview + messages, save. */
    function commit() {
        footer().items = toFlat(cols);
        renderPreview();
        refreshIssues();
        scheduleSave();
    }

    /* ═══════════════════════════ render ═══════════════════════════ */

    function renderFooter(a) {
        api = a;
        rememberShell();
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'Footer';
        if (desc) { desc.textContent = 'The section at the bottom of every page. Edit it below — the preview updates as you type.'; desc.hidden = false; }
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;

        cols = fromFlat(footer().items);
        customTarget = {};
        const [line1, line2] = splitHeading(footer().heading);
        const text = String(footer().description || '');

        api.editorBody.innerHTML = `<div class="wcp wcf">
            <section class="wcp-card" aria-labelledby="wcfIntroH">
                <h2 id="wcfIntroH" class="wcn-h2">Heading and intro</h2>
                <p class="wcp-card-desc">The big two-line heading and short text on the left of the footer.</p>
                <div class="wcp-row-2 wcf-heading-row">
                    <div class="wcp-field">
                        <label for="wcfLine1">Heading – first line <span class="wcp-muted">(white)</span></label>
                        <input type="text" id="wcfLine1" class="wcp-input" value="${esc(line1)}" maxlength="120" autocomplete="off" aria-describedby="wcfLine1Err">
                        <p class="wcp-error" id="wcfLine1Err" hidden></p>
                    </div>
                    <div class="wcp-field">
                        <label for="wcfLine2">Heading – second line <span class="wcf-swatch" aria-hidden="true"></span><span class="wcf-gold-word">gold</span></label>
                        <input type="text" id="wcfLine2" class="wcp-input" value="${esc(line2)}" maxlength="120" autocomplete="off" aria-describedby="wcfLine2Hint">
                        <p class="wcp-hint" id="wcfLine2Hint">Optional.</p>
                    </div>
                </div>
                <div class="wcp-field">
                    <div class="wcp-label-row"><label for="wcfText">Intro text</label><span class="wcp-counter" id="wcfTextCount" aria-live="polite"></span></div>
                    <textarea id="wcfText" class="wcp-input wcp-textarea" rows="3" aria-describedby="wcfTextHint">${esc(text)}</textarea>
                    <p class="wcp-hint" id="wcfTextHint">One or two sentences work best. Leave empty to use the website’s standard text.</p>
                </div>
            </section>

            <section class="wcp-card" aria-labelledby="wcfColsH">
                <div class="wcf-cols-head">
                    <div>
                        <h2 id="wcfColsH" class="wcn-h2">Link columns</h2>
                        <p class="wcp-card-desc">Each column has a heading and a list of links. Drag the ⠿ handle to change the order — columns side to side, links within or between columns.</p>
                    </div>
                    <button type="button" class="wcp-btn wcp-btn--outline" id="wcfAddCol">${icon('solar:add-circle-linear')}Add column</button>
                </div>
                <p class="wcp-warn wcf-cols-warn" id="wcfColsWarn" hidden></p>
                <ul class="wcf-cols" id="wcfCols" aria-label="Footer columns, in order"></ul>
            </section>

            <section class="wcp-card" aria-labelledby="wcfAlsoH">
                <h2 id="wcfAlsoH" class="wcn-h2">Also shown in the footer</h2>
                <p class="wcp-card-desc">These come from other settings, so you only update them in one place.</p>
                <ul class="wcf-also">
                    ${[
                        ['solar:phone-linear', 'Address, phone and email', 'From Contact details', 'contact'],
                        ['solar:share-linear', 'Social media icons', 'From Social media · only the ones switched on', 'social'],
                        ['solar:copyright-linear', 'Copyright line', 'From Branding', 'branding'],
                        ['solar:login-2-linear', 'Student, parent and teacher login links', 'From Announcement & portal', 'website'],
                        ['solar:cursor-square-linear', '“Start Your Application” button', 'Goes where the header button goes · Navigation menu', 'nav_menu'],
                    ].map(([ic, t, src, mod]) => `<li>
                        <span class="wcp-tile">${icon(ic)}</span>
                        <span class="wcf-also-text"><strong>${esc(t)}</strong><span>${esc(src)}</span></span>
                        <button type="button" class="wcf-also-edit" data-goto="${mod}" aria-label="Edit ${esc(t)} in ${esc(src.replace(/^From |^Goes where the header button goes · /, ''))}">Edit${icon('solar:arrow-right-linear')}</button>
                    </li>`).join('')}
                </ul>
            </section>
        </div>`;

        bindIntro();
        bindColumns();
        api.editorBody.querySelectorAll('[data-goto]').forEach((b) => b.addEventListener('click', () => {
            flushSave().then(() => { restoreShell(); api.switchModule(b.dataset.goto); });
        }));
        renderColumns();
        renderPreview();
        refreshIssues();
        updateCounter();
        if (save.state === 'idle') { updateSavebar(); api.setStatus(matchesLive() ? 'Your footer matches the live website' : 'Footer draft saved', 'ok'); } else setState(save.state);
    }

    /* ── preview (mirrors the website's Footer.tsx) ── */
    function renderPreview() {
        const box = api.editorBody.querySelector('#wcfPreview');
        if (!box) return; // the live preview panel shows the footer now
        if (!box) return;
        const d = api.getData();
        const b = d.branding || {};
        const c = d.contact || {};
        const social = d.social || {};
        const theme = d.theme || {};
        const fm = footer();
        const [h1Raw, h2] = splitHeading(fm.heading);
        const h1 = h1Raw || DEFAULT_LINE_1;
        const text = String(fm.description || '').trim() || DEFAULT_TEXT;
        const name = b.company_name || 'Your school';
        const copyright = String(b.copyright || '').trim() || `${name} · UKRLP Registered · ICO Registered`;
        const year = new Date().getFullYear();

        const enabled = social.enabled || {};
        const socials = SOCIAL_ORDER.filter((k) => enabled[k] !== false && typeof social[k] === 'string' && social[k].trim());
        if (enabled.whatsapp !== false && String(c.whatsapp || '').trim()) socials.splice(1, 0, 'whatsapp');

        const address = String(c.address || '').trim().replace(/\s*\n\s*/g, ', ');
        const contactLine = (ic, v) => `<p class="wcf-pv-contact">${icon(ic, 'wcf-pv-gold')}<span>${v ? esc(v) : '<em>Website default</em>'}</span></p>`;

        const shownCols = cols
            .map((col) => ({ col, links: col.links.filter((l) => l.label.trim() && l.href.trim()) }))
            .filter((x) => x.links.length);

        box.innerHTML = `<div class="wcf-pv" style="--wcf-bg:${esc(theme.footer || '#0A1B33')}">
            <div class="wcf-pv-top">
                <h3 class="wcf-pv-heading"><button type="button" data-focus="#wcfLine1">${esc(h1)}</button>${h2 ? `<br><button type="button" class="wcf-pv-heading-gold" data-focus="#wcfLine2">${esc(h2)}</button>` : ''}</h3>
                <div class="wcf-pv-ctas" aria-hidden="true">
                    <span class="wcf-pv-btn wcf-pv-btn--gold">Start Your Application</span>
                    <span class="wcf-pv-btn wcf-pv-btn--glass">Book a Call</span>
                    <span class="wcf-pv-btn wcf-pv-btn--wa">${icon('mdi:whatsapp')}WhatsApp Channel</span>
                </div>
            </div>
            <div class="wcf-pv-grid">
                <div class="wcf-pv-brand">
                    <span class="wcf-pv-logo"><span class="wcf-pv-logo-tile">${b.logo ? `<img src="${esc(b.logo)}" alt="">` : ''}</span><span class="wcf-pv-logo-name">${esc((b.short_name || name).toUpperCase())}</span></span>
                    <p class="wcf-pv-text"><button type="button" data-focus="#wcfText">${esc(text)}</button></p>
                    ${contactLine('solar:map-point-linear', address)}
                    ${contactLine('solar:phone-linear', String(c.phone || '').trim())}
                    ${contactLine('solar:letter-linear', String(c.email || '').trim())}
                    <ul class="wcf-pv-socials" aria-label="Social media icons">
                        ${socials.length ? socials.map((k) => `<li title="${esc(k)}">${icon(SOCIAL_ICONS[k] || 'solar:link-linear')}</li>`).join('') : '<li class="wcf-pv-muted">Website default icons</li>'}
                    </ul>
                </div>
                ${shownCols.length ? shownCols.map(({ col, links }) => `<nav class="wcf-pv-col" aria-label="${esc(col.title)}">
                    <h4><button type="button" data-focus="#wcfTitle-${col.id}">${esc(col.title)}</button></h4>
                    <ul>${links.map((l) => `<li><button type="button" data-focus="#wcfLabel-${l.id}">${esc(l.label)}</button></li>`).join('')}</ul>
                </nav>`).join('') : '<p class="wcf-pv-muted wcf-pv-defaultcols">The website’s built-in columns are shown until you add links.</p>'}
            </div>
            <div class="wcf-pv-bottom">
                <p>© ${year} ${esc(copyright.replace(/^©\s*(\d{4}\s*)?/, ''))}</p>
                <p>Student Login · Parent Login · Teacher Login</p>
            </div>
        </div>`;
    }

    function focusField(sel) {
        const el = api.editorBody.querySelector(sel);
        if (!el) return;
        const pane = api.editorBody;
        const pr = pane.getBoundingClientRect();
        const r = el.getBoundingClientRect();
        pane.scrollTo({ top: Math.max(0, pane.scrollTop + r.top - pr.top - pane.clientHeight / 3), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        el.focus({ preventScroll: true });
    }

    /* ── heading + intro ── */
    function updateCounter() {
        const el = api.editorBody.querySelector('#wcfTextCount');
        const n = String(footer().description || '').length;
        el.textContent = `${n} / ${TEXT_MAX}`;
        el.className = 'wcp-counter ' + (n === 0 ? '' : n <= TEXT_MAX ? 'is-ok' : 'is-over');
        el.setAttribute('aria-label', `${n} of ${TEXT_MAX} characters${n > TEXT_MAX ? ', longer than recommended' : ''}`);
    }

    function bindIntro() {
        const b = api.editorBody;
        const l1 = b.querySelector('#wcfLine1');
        const l2 = b.querySelector('#wcfLine2');
        const heading = () => { footer().heading = joinHeading(l1.value, l2.value); commit(); };
        [l1, l2].forEach((el) => {
            el.addEventListener('input', () => {
                if (el.value.includes('|')) { const p = el.selectionStart; el.value = el.value.replace(/\|/g, ' '); el.setSelectionRange(p, p); }
                heading();
            });
            el.addEventListener('blur', () => { if (save.dirty) flushSave(); });
        });
        const t = b.querySelector('#wcfText');
        t.addEventListener('input', () => { footer().description = t.value; updateCounter(); commit(); });
        t.addEventListener('blur', () => { if (save.dirty) flushSave(); });
        b.querySelector('#wcfPreview')?.addEventListener('click', (e) => {
            const f = e.target.closest('[data-focus]');
            if (f) focusField(f.dataset.focus);
        });
    }

    /* ── columns ── */
    function renderColumns(focusSel) {
        const ul = api.editorBody.querySelector('#wcfCols');
        if (!ul) return;
        if (!cols.length) {
            ul.innerHTML = '<li class="wcn-empty">No columns yet. Use <strong>Add column</strong> to start.</li>';
        } else {
            ul.innerHTML = cols.map((c, ci) => {
                const cname = c.title || 'Untitled column';
                return `<li class="wcf-col" data-col="${c.id}">
                    <label class="wcf-col-label" for="wcfTitle-${c.id}">Column heading</label>
                    <div class="wcf-col-headrow">
                        ${ext.shared.dragHandle(`data-drag-col="${c.id}"`, `${cname} column`)}
                        <input type="text" id="wcfTitle-${c.id}" class="wcp-input wcf-col-title" data-col-title="${c.id}" value="${esc(c.title)}" maxlength="80" autocomplete="off" aria-describedby="wcfErr-col-${c.id}">
                        <button type="button" class="wcp-icon-btn is-danger" data-col-remove="${c.id}" aria-label="Delete ${esc(cname)} column">${icon('solar:trash-bin-trash-linear')}</button>
                    </div>
                    <p class="wcp-error" id="wcfErr-col-${c.id}" data-err="col:${c.id}" hidden></p>
                    <p class="wcp-warn" data-warn="col:${c.id}" hidden></p>
                    <ul class="wcf-links" data-col-links="${c.id}" aria-label="Links in ${esc(cname)}">
                        ${c.links.map((l, li) => {
                            const lname = l.label || 'link';
                            const custom = !!customTarget[l.id] || (l.href && !pageFor(l.href));
                            return `<li class="wcf-link" data-id="${l.id}">
                                <div class="wcf-link-fields">
                                    <label class="visually-hidden" for="wcfLabel-${l.id}">Link text</label>
                                    <input type="text" id="wcfLabel-${l.id}" class="wcp-input" data-link-label="${l.id}" value="${esc(l.label)}" placeholder="Link text" maxlength="120" autocomplete="off" aria-describedby="wcfErr-label-${l.id}">
                                    <p class="wcp-error" id="wcfErr-label-${l.id}" data-err="label:${l.id}" hidden></p>
                                    <label class="visually-hidden" for="wcfTarget-${l.id}">Goes to</label>
                                    <select id="wcfTarget-${l.id}" class="wcp-input wcf-select" data-link-target="${l.id}">${pageOptions(l.href, !!customTarget[l.id])}</select>
                                    <label class="visually-hidden" for="wcfUrl-${l.id}">Web address</label>
                                    <input type="text" id="wcfUrl-${l.id}" class="wcp-input wcf-url" data-link-url="${l.id}" value="${esc(l.href)}" placeholder="https://… or /page" ${custom ? '' : 'hidden'} autocomplete="off" spellcheck="false" aria-describedby="wcfErr-href-${l.id}">
                                    <p class="wcp-error" id="wcfErr-href-${l.id}" data-err="href:${l.id}" hidden></p>
                                    <p class="wcp-warn" data-warn="links:${l.id}" hidden></p>
                                </div>
                                <div class="wcf-link-tools">
                                    ${ext.shared.dragHandle(`data-drag-link="${l.id}"`, lname)}
                                    <button type="button" class="wcp-icon-btn is-danger" data-link-remove="${l.id}" aria-label="Remove ${esc(lname)}">${icon('solar:close-circle-linear')}</button>
                                </div>
                            </li>`;
                        }).join('')}
                    </ul>
                    <button type="button" class="wcn-add-child wcf-add-link" data-add-link="${c.id}">${icon('solar:add-circle-linear')}Add link</button>
                </li>`;
            }).join('');
        }
        refreshIssues();
        if (focusSel) {
            const el = ul.querySelector(focusSel);
            if (el) el.focus();
        }
    }

    function refreshIssues() {
        const b = api.editorBody;
        const { errors, warnings } = issues();
        const put = (el, msg, ic) => {
            el.hidden = !msg;
            el.innerHTML = msg ? `${icon(ic)}<span>${esc(msg)}</span>` : '';
        };
        b.querySelectorAll('[data-err]').forEach((el) => {
            const msg = errors[el.dataset.err];
            put(el, msg, 'solar:danger-circle-linear');
            const input = b.querySelector(`[aria-describedby~="${el.id}"]`);
            if (input) input.setAttribute('aria-invalid', msg ? 'true' : 'false');
        });
        b.querySelectorAll('[data-warn]').forEach((el) => put(el, warnings[el.dataset.warn], 'solar:danger-triangle-linear'));
        const line1Err = b.querySelector('#wcfLine1Err');
        if (line1Err) {
            put(line1Err, errors.line1, 'solar:danger-circle-linear');
            b.querySelector('#wcfLine1').setAttribute('aria-invalid', errors.line1 ? 'true' : 'false');
        }
        const colsWarn = b.querySelector('#wcfColsWarn');
        if (colsWarn) put(colsWarn, warnings.columns, 'solar:info-circle-linear');
    }

    function bindColumns() {
        const b = api.editorBody;
        b.querySelector('#wcfAddCol').addEventListener('click', () => {
            let title = 'New column';
            let k = 2;
            while (cols.some((c) => c.title === title)) title = `New column ${k++}`;
            const col = { id: nid(), title, links: [] };
            cols.push(col);
            commit();
            renderColumns();
            const el = b.querySelector(`#wcfTitle-${col.id}`);
            focusField(`#wcfTitle-${col.id}`);
            el.select();
        });

        const list = b.querySelector('#wcfCols');

        // Drag and drop (or arrow keys on the handle): whole columns, and links within or between columns.
        ext.shared.sortable(list, {
            item: 'li.wcf-col', handle: '[data-drag-col]', axis: 'grid', scroller: b,
            onDrop: ({ fromIndex, toIndex }) => {
                const [col] = cols.splice(fromIndex, 1);
                cols.splice(toIndex, 0, col);
                commit();
                renderColumns(`[data-drag-col="${col.id}"]`);
                api.setStatus(`Moved the “${col.title || 'Untitled'}” column to position ${toIndex + 1}`, 'saving');
            },
        });
        ext.shared.sortable(list, {
            lists: '.wcf-links', item: 'li.wcf-link', handle: '[data-drag-link]', connect: true, scroller: b,
            onDrop: ({ fromList, fromIndex, toList, toIndex }) => {
                const from = findCol(fromList.dataset.colLinks);
                const to = findCol(toList.dataset.colLinks);
                const [link] = from.links.splice(fromIndex, 1);
                to.links.splice(toIndex, 0, link);
                commit();
                renderColumns(`[data-drag-link="${link.id}"]`);
                api.setStatus(from === to ? `Moved “${link.label || 'link'}” to position ${toIndex + 1}` : `Moved “${link.label || 'link'}” to ${to.title || 'another column'}`, 'saving');
            },
        });
        list.addEventListener('input', (e) => {
            const t = e.target;
            if (t.dataset.colTitle) { findCol(t.dataset.colTitle).title = t.value; commit(); return; }
            if (t.dataset.linkLabel) { findLink(t.dataset.linkLabel).link.label = t.value; commit(); return; }
            if (t.dataset.linkUrl) { findLink(t.dataset.linkUrl).link.href = t.value.trim(); commit(); }
        });
        list.addEventListener('focusout', (e) => { if (e.target.matches('input') && save.dirty) flushSave(); });
        list.addEventListener('change', (e) => {
            const t = e.target;
            if (!t.dataset.linkTarget) return;
            const id = t.dataset.linkTarget;
            const { link } = findLink(id);
            const url = list.querySelector(`#wcfUrl-${id}`);
            if (t.value === CUSTOM) {
                customTarget[id] = true;
                url.hidden = false;
                url.focus();
                return;
            }
            delete customTarget[id];
            url.hidden = true;
            link.href = t.value;
            url.value = t.value;
            const page = pageFor(t.value);
            if (page && (!link.label.trim() || link.label === 'New link')) {
                link.label = page.draft.title;
                list.querySelector(`#wcfLabel-${id}`).value = link.label;
            }
            commit();
            flushSave();
        });
        list.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn || btn.disabled) return;
            const ds = btn.dataset;
            if (ds.colRemove) {
                removeColumn(findCol(ds.colRemove));
            } else if (ds.linkRemove) {
                const { col, link } = findLink(ds.linkRemove);
                const i = col.links.indexOf(link);
                col.links.splice(i, 1);
                commit();
                const next = col.links[i] || col.links[i - 1];
                renderColumns(next ? `#wcfLabel-${next.id}` : `[data-add-link="${col.id}"]`);
                api.setStatus(`Removed “${link.label || 'link'}”`, 'saving');
            } else if (ds.addLink) {
                const col = findCol(ds.addLink);
                const link = { id: nid(), label: 'New link', href: '' };
                col.links.push(link);
                commit();
                renderColumns();
                focusField(`#wcfLabel-${link.id}`);
                b.querySelector(`#wcfLabel-${link.id}`).select();
            }
        });
    }

    function removeColumn(col) {
        const doRemove = () => {
            const i = cols.indexOf(col);
            cols.splice(i, 1);
            commit();
            const next = cols[i] || cols[i - 1];
            renderColumns(next ? `#wcfTitle-${next.id}` : null);
            if (!next) api.editorBody.querySelector('#wcfAddCol').focus();
            api.setStatus(`Removed the “${col.title}” column`, 'saving');
        };
        if (!col.links.length) return doRemove();
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title + '\n\n' + o.message));
        confirmFn({
            title: `Delete the “${col.title || 'untitled'}” column?`,
            message: `Its ${col.links.length} link${col.links.length === 1 ? '' : 's'} leave the footer: ${col.links.map((l) => l.label || 'untitled').join(', ')}.`,
            note: 'The pages themselves are kept. Nothing changes on your website until you publish.',
            label: 'Delete column',
            tone: 'danger',
            icon: 'solar:trash-bin-trash-linear',
        }).then((ok) => { if (ok) doRemove(); });
    }

    /* ═══════════════════════════ shell integration ═══════════════════════════ */

    document.addEventListener('click', (e) => {
        const item = e.target.closest('.wcm-nav-item');
        if (!item || item.dataset.module === MODULE || activeModule() !== MODULE) return;
        if (save.dirty) flushSave();
        restoreShell();
    }, true);

    // "Discard" here puts back only the footer.
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmDiscard');
        if (!btn || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        const published = W.publishedFooterMenu;
        if (!published || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your footer changes?',
            message: 'The footer goes back to the version that is live on your website.',
            note: 'Only the footer is changed — your pages and other settings stay as they are.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            clearTimeout(save.timer);
            const fm = footer();
            fm.heading = published.heading || '';
            fm.description = published.description || '';
            fm.items = clone(published.items || []);
            save.dirty = true;
            renderFooter(api);
            flushSave().then((done) => { if (done) api.setStatus('Footer changes discarded', 'ok'); });
        });
    }, true);

    // "Publish": save pending footer edits first; block while something is invalid.
    let publishing = false;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmPublish');
        if (!btn || publishing || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (hasErrors(issues())) {
            setState('invalid');
            const first = api.editorBody.querySelector('.wcp-error:not([hidden])');
            if (first) {
                const input = api.editorBody.querySelector(`[aria-describedby~="${first.id}"]`) || first;
                focusField('#' + (input.id || first.id));
            }
            return;
        }
        flushSave().then((ok) => {
            if (!ok) return;
            try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
            window.__wcfSkipUnloadWarning = true;
            window.__wcnSkipUnloadWarning = true;
            window.__wcpSkipUnloadWarning = true;
            publishing = true;
            btn.click();
            publishing = false;
        });
    }, true);

    ext.renderers[MODULE] = renderFooter;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);

    ext.footerModel = { splitHeading, joinHeading, fromFlat, toFlat };
})();
