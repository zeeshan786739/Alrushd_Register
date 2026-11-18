/**
 * Website CMS → Branding (connected website).
 *
 * Edits cmsData.branding: company_name, short_name, tagline, website_description, copyright,
 * copyright_auto_year, and the connected website's own images — website_logo, website_favicon
 * (+ website_favicon_icons: 32/180/192/512 PNGs made by the server on upload). Those are kept
 * apart from `logo` / `favicon`, which the built-in landing page uses; empty = the website's
 * built-in crest and icons. Previews mirror Website/src: Header Logo (short name in capitals),
 * the homepage heading (tagline split after its first full stop, second line gold), the footer
 * copyright ("© {year} …" when automatic) and browser-tab titles ("Page | Short name").
 * Loads after website-cms-headless.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc, clone } = ext.shared;
    const MODULE = 'branding';
    const REOPEN_KEY = 'wcb:reopen';
    const AUTOSAVE_MS = 900;
    const LIMITS = { short: 30, tagline: 50, taglineMax: 120, description: 160, descriptionMax: 300 };
    const KEYS = ['company_name', 'short_name', 'tagline', 'website_description', 'copyright', 'copyright_auto_year', 'website_logo', 'website_favicon', 'website_favicon_icons'];
    const IMG = {
        logo: { types: ['image/png', 'image/svg+xml', 'image/webp'], max: 2 * 1024 * 1024, typeMsg: 'Please choose a PNG, SVG or WebP image.', sizeMsg: 'That logo is larger than 2 MB.' },
        favicon: { types: ['image/png', 'image/jpeg', 'image/webp'], max: 5 * 1024 * 1024, typeMsg: 'Please choose a PNG or JPG image.', sizeMsg: 'That image is larger than 5 MB.' },
    };

    let api = null;
    let serverErrors = {};
    let otherSettingsPending = null;
    const save = { timer: null, inflight: null, state: 'idle', dirty: false };

    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;

    function b() {
        const data = api.getData();
        data.branding = data.branding || {};
        if (data.branding.copyright_auto_year === undefined || data.branding.copyright_auto_year === null) data.branding.copyright_auto_year = true;
        return data.branding;
    }

    const siteBase = () => String((H.connection && H.connection.external_url) || '').replace(/\/+$/, '');
    const builtInLogo = () => (siteBase() ? siteBase() + '/images/crest.png' : '');
    const builtInIcon = () => (siteBase() ? siteBase() + '/favicon.png' : '');
    const year = new Date().getFullYear();

    /** Same as the website's Footer: automatic → "© {year} {text without a typed ©/year}". */
    function copyrightLine(v) {
        const name = String(v.company_name || '').trim() || 'Your school';
        const text = String(v.copyright || '').trim();
        if (v.copyright_auto_year === false) return text || `© ${name} · UKRLP Registered · ICO Registered`;
        return `© ${year} ${(text || `${name} · UKRLP Registered · ICO Registered`).replace(/^©\s*(\d{4}\s*)?/, '')}`;
    }

    /** Same split as the website's homepage heading. */
    function taglineLines(tagline) {
        const m = /^(.+?[.!?])\s+(.+)$/.exec(String(tagline || '').trim());
        return m ? [m[1], m[2]] : [String(tagline || '').trim(), ''];
    }

    /* ───────────────────────── validation ───────────────────────── */

    function errors() {
        const v = b();
        const out = {};
        if (!String(v.company_name || '').trim()) out.company_name = 'Enter your school’s full name.';
        const short = String(v.short_name || '').trim();
        if (!short) out.short_name = 'Enter a short name — it sits next to your logo.';
        else if (short.length > LIMITS.short) out.short_name = `Keep the short name to ${LIMITS.short} characters so it fits next to the logo.`;
        if (String(v.tagline || '').length > LIMITS.taglineMax) out.tagline = `Keep the tagline under ${LIMITS.taglineMax} characters.`;
        if (String(v.website_description || '').length > LIMITS.descriptionMax) out.website_description = `Keep the description under ${LIMITS.descriptionMax} characters.`;
        Object.keys(serverErrors).forEach((k) => { if (!out[k]) out[k] = serverErrors[k]; });
        return out;
    }

    /* ───────────────────────── save / status / shell ───────────────────────── */

    function snapshot(src) {
        if (!src) return null;
        const o = {};
        KEYS.forEach((k) => { o[k] = k === 'website_favicon_icons' ? (src[k] || {}) : (k === 'copyright_auto_year' ? src[k] !== false : String(src[k] == null ? '' : src[k]).trim()); });
        if (!o.website_favicon) o.website_favicon_icons = {};
        return JSON.stringify(o);
    }

    const matchesLive = () => W.publishedBranding != null && snapshot(W.publishedBranding) === snapshot(b());

    function updateSavebar() {
        if (activeModule() !== MODULE) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        const saveDraft = document.getElementById('wcmSaveDraft');
        const discard = document.getElementById('wcmDiscard');
        if (saveDraft) saveDraft.style.setProperty('display', 'none', 'important'); // autosaves
        const live = matchesLive();
        let text;
        let tone = 'warn';
        if (save.state === 'invalid') text = 'Fix the highlighted field to save your changes.';
        else if (save.state === 'error') text = 'Your last change wasn’t saved — check your connection.';
        else if (save.dirty || save.timer || save.inflight) text = 'Saving your changes…';
        else if (live) { text = 'Your branding matches the live website.'; tone = 'ok'; }
        else text = 'Changes saved as a draft — publish to show them on your website.';
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = !live && W.publishedBranding != null;
            discard.disabled = !can;
            discard.title = can ? 'Put the branding back to the live version' : '';
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
                saving: ['Saving…', 'saving'], pending: ['Unsaved changes…', 'saving'], saved: ['Saved as a draft just now', 'ok'],
                invalid: ['Couldn’t save – fix the highlighted field', 'error'], error: ['Couldn’t save – check your connection', 'error'],
            };
            if (map[state]) api.setStatus(map[state][0], map[state][1]);
        }
        updateSavebar();
    }

    function scheduleSave() {
        save.dirty = true;
        clearTimeout(save.timer);
        save.timer = null;
        if (Object.keys(errors()).length) { setState('invalid'); return; }
        setState('pending');
        save.timer = setTimeout(flushSave, AUTOSAVE_MS);
    }

    function flushSave() {
        clearTimeout(save.timer);
        save.timer = null;
        if (save.inflight) return save.inflight.then(() => (save.dirty ? flushSave() : true));
        if (!save.dirty) return Promise.resolve(true);
        if (Object.keys(errors()).length) { setState('invalid'); return Promise.resolve(false); }
        save.dirty = false;
        setState('saving');
        save.inflight = fetch(W.routes.draft, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' },
            body: JSON.stringify({ data: api.getData() }),
        }).then(async (r) => {
            if (r.status === 422) {
                const json = await r.json().catch(() => ({}));
                serverErrors = {};
                Object.entries(json.errors || {}).forEach(([k, m]) => { serverErrors[k.replace(/^branding\./, '')] = m[0]; });
                save.dirty = true;
                showErrors();
                setState('invalid');
                return false;
            }
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
        if (window.__wcbSkipUnloadWarning) return;
        if (save.timer || save.inflight || (save.dirty && save.state !== 'idle')) { e.preventDefault(); e.returnValue = ''; }
    });

    function changed(field) {
        if (field) delete serverErrors[field];
        renderPreview();
        refresh();
        showErrors();
        scheduleSave();
    }

    /* ═══════════════════════════ render ═══════════════════════════ */

    function field(id, key, label, hint, opts) {
        const o = opts || {};
        const v = b();
        const counter = o.counter ? `<span class="wcp-counter" id="${id}Count" aria-live="polite"></span>` : '';
        const input = o.textarea
            ? `<textarea id="${id}" class="wcp-input wcp-textarea" rows="3" data-key="${key}" maxlength="${o.max || ''}" aria-describedby="${id}Hint wcbErr-${key}">${esc(v[key] || '')}</textarea>`
            : `<input type="text" id="${id}" class="wcp-input" data-key="${key}" value="${esc(v[key] || '')}" maxlength="${o.max || ''}" autocomplete="off" aria-describedby="${id}Hint wcbErr-${key}">`;
        return `<div class="wcp-field">
            <div class="wcp-label-row"><label for="${id}">${label}</label>${counter}</div>
            ${input}
            <p class="wcp-hint" id="${id}Hint">${hint}</p>
            <p class="wcp-error" id="wcbErr-${key}" hidden></p>
        </div>`;
    }

    function renderBranding(a) {
        api = a;
        rememberShell();
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'Branding';
        if (desc) { desc.textContent = 'Your school’s name, logo and short descriptions. They’re reused across the website, so keep them consistent.'; desc.hidden = false; }
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;
        serverErrors = {};
        const v = b();

        api.editorBody.innerHTML = `<div class="wcp wcb">
            <div class="wcb-grid wcb-grid--single">
                <div class="wcb-form">
                    <section class="wcp-card" aria-labelledby="wcbNameH">
                        <h2 id="wcbNameH" class="wcn-h2">School name</h2>
                        <p class="wcp-card-desc">How your school is named on the website.</p>
                        <div class="wcb-row-2">
                            ${field('wcbName', 'company_name', 'Full name', 'Used when your pages are shared on social media and by search engines.', { max: 120 })}
                            ${field('wcbShort', 'short_name', 'Short name', 'Shown in capitals next to your logo, and at the end of every browser tab (e.g. “Fees | Al-Rushd”).', { max: 40, counter: true })}
                        </div>
                    </section>

                    <section class="wcp-card" aria-labelledby="wcbLogoH">
                        <h2 id="wcbLogoH" class="wcn-h2">Logo and browser icon <span class="wcb-new">New</span></h2>
                        <p class="wcp-card-desc">Until you upload your own, the website keeps its built-in crest and icon.</p>
                        <div class="wcb-uploads">
                            ${['logo', 'favicon'].map((kind) => `<div class="wcb-upload" id="wcbTile-${kind}">
                                <span class="wcb-thumb ${kind === 'favicon' ? 'is-icon' : ''}" id="wcbThumb-${kind}"></span>
                                <div class="wcb-upload-text">
                                    <strong>${kind === 'logo' ? 'Logo' : 'Browser icon'}</strong>
                                    <span class="wcp-hint">${kind === 'logo' ? 'PNG, SVG or WebP with a transparent background. Up to 2 MB.' : 'A square PNG or JPG, at least 512 × 512 px. We make the small sizes for you.'}</span>
                                    <span class="wcb-source" id="wcbSource-${kind}"></span>
                                    <div class="wcb-upload-actions">
                                        <label class="wcp-btn wcp-btn--outline wcp-btn--sm" for="wcbFile-${kind}" tabindex="0" role="button" id="wcbPick-${kind}">${icon('solar:upload-linear')}<span id="wcbPickText-${kind}">Upload</span></label>
                                        <button type="button" class="wcp-btn wcp-btn--ghost wcp-btn--sm" id="wcbRemove-${kind}" hidden>${icon('solar:trash-bin-trash-linear')}Remove</button>
                                    </div>
                                    <input type="file" id="wcbFile-${kind}" class="visually-hidden" tabindex="-1" accept="${IMG[kind].types.join(',')}" aria-label="Choose a ${kind === 'logo' ? 'logo' : 'browser icon'}">
                                    <p class="wcp-error" id="wcbErr-${kind}" hidden></p>
                                </div>
                            </div>`).join('')}
                        </div>
                    </section>

                    <section class="wcp-card" aria-labelledby="wcbTagH">
                        <h2 id="wcbTagH" class="wcn-h2">Tagline and description</h2>
                        <p class="wcp-card-desc">A short motto, and a sentence that describes your school.</p>
                        ${field('wcbTagline', 'tagline', 'Tagline', 'The big heading on your homepage. Words after the first full stop go on a second, gold line.', { max: LIMITS.taglineMax, counter: true })}
                        ${field('wcbDesc', 'website_description', 'Description', 'Also used as your Google description unless you write a different one in <button type="button" class="wcs-inline-link" id="wcbToSeo">SEO</button>.', { max: LIMITS.descriptionMax, counter: true, textarea: true })}
                    </section>

                    <section class="wcp-card" aria-labelledby="wcbCopyH">
                        <h2 id="wcbCopyH" class="wcn-h2">Copyright line</h2>
                        <p class="wcp-card-desc">The small line at the very bottom of every page.</p>
                        ${field('wcbCopy', 'copyright', 'Text', '', { max: 150 })}
                        <label class="wcc-check-row wcb-year"><input type="checkbox" id="wcbYear" ${v.copyright_auto_year !== false ? 'checked' : ''}> Start with © and the current year (updates itself every January)</label>
                    </section>
                </div>

            </div>
        </div>`;

        bind();
        renderImages();
        renderPreview();
        refresh();
        showErrors();
        if (save.state === 'idle') { updateSavebar(); api.setStatus(matchesLive() ? 'Your branding matches the live website' : 'Draft saved', 'ok'); } else setState(save.state);
    }

    function counter(el, n, max) {
        if (!el) return;
        el.textContent = `${n} / ${max}`;
        el.className = 'wcp-counter ' + (n === 0 ? '' : n <= max ? 'is-ok' : 'is-over');
        el.setAttribute('aria-label', `${n} of ${max} characters${n > max ? ', longer than recommended' : ''}`);
    }

    function refresh() {
        const v = b();
        const q = (s) => api.editorBody.querySelector(s);
        counter(q('#wcbShortCount'), String(v.short_name || '').length, LIMITS.short);
        counter(q('#wcbTaglineCount'), String(v.tagline || '').length, LIMITS.tagline);
        counter(q('#wcbDescCount'), String(v.website_description || '').length, LIMITS.description);
        q('#wcbCopyHint').textContent = v.copyright_auto_year !== false
            ? 'The © and the year are added for you — no need to type them.'
            : 'Shown exactly as you type it.';
        updateSavebar();
    }

    function showErrors() {
        const errs = errors();
        ['company_name', 'short_name', 'tagline', 'website_description', 'copyright'].forEach((k) => {
            const box = api.editorBody.querySelector(`#wcbErr-${k}`);
            if (!box) return;
            const msg = errs[k];
            box.hidden = !msg;
            box.innerHTML = msg ? `${icon('solar:danger-circle-linear')}<span>${esc(msg)}</span>` : '';
            const input = api.editorBody.querySelector(`[aria-describedby~="wcbErr-${k}"]`);
            if (input) input.setAttribute('aria-invalid', msg ? 'true' : 'false');
        });
    }

    /** The image, with initials shown instead if it can't be loaded (e.g. the website is offline). */
    function imgTag(src, fallbackText) {
        return src
            ? `<img src="${esc(src)}" alt="" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span hidden>${esc(fallbackText)}</span>`
            : `<span>${esc(fallbackText)}</span>`;
    }

    function initials() {
        const name = String(b().company_name || 'School');
        return name.split(/[\s-]+/).filter((x) => /^[A-Za-z]/.test(x)).slice(0, 2).map((x) => x[0].toUpperCase()).join('') || 'S';
    }

    function renderImages() {
        const v = b();
        const q = (s) => api.editorBody.querySelector(s);
        [['logo', v.website_logo, builtInLogo()], ['favicon', v.website_favicon, builtInIcon()]].forEach(([kind, own, builtIn]) => {
            q(`#wcbThumb-${kind}`).innerHTML = imgTag(own || builtIn, initials());
            q(`#wcbSource-${kind}`).textContent = own ? 'Your upload' : 'Showing your website’s built-in ' + (kind === 'logo' ? 'crest' : 'icon');
            q(`#wcbPickText-${kind}`).textContent = own ? 'Replace' : 'Upload';
            q(`#wcbRemove-${kind}`).hidden = !own;
        });
    }

    function bind() {
        const root = api.editorBody;
        root.querySelectorAll('[data-key]').forEach((el) => {
            el.addEventListener('input', () => { b()[el.dataset.key] = el.value; changed(el.dataset.key); });
            el.addEventListener('blur', () => { if (save.dirty) flushSave(); });
        });
        root.querySelector('#wcbYear').addEventListener('change', (e) => { b().copyright_auto_year = e.target.checked; changed('copyright'); flushSave(); });
        root.querySelector('#wcbToSeo').addEventListener('click', () => flushSave().then(() => { restoreShell(); api.switchModule('seo'); }));

        ['logo', 'favicon'].forEach((kind) => {
            const input = root.querySelector(`#wcbFile-${kind}`);
            const pick = root.querySelector(`#wcbPick-${kind}`);
            pick.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
            input.addEventListener('change', () => {
                const file = input.files[0];
                input.value = '';
                if (file) upload(kind, file);
            });
            const tile = root.querySelector(`#wcbTile-${kind}`);
            ['dragenter', 'dragover'].forEach((t) => tile.addEventListener(t, (e) => { e.preventDefault(); tile.classList.add('is-over'); }));
            ['dragleave', 'drop'].forEach((t) => tile.addEventListener(t, () => tile.classList.remove('is-over')));
            tile.addEventListener('drop', (e) => { e.preventDefault(); const f = e.dataTransfer && e.dataTransfer.files[0]; if (f) upload(kind, f); });
            root.querySelector(`#wcbRemove-${kind}`).addEventListener('click', () => removeImage(kind));
        });
    }

    function imageError(kind, msg) {
        const box = api.editorBody.querySelector(`#wcbErr-${kind}`);
        box.hidden = !msg;
        box.innerHTML = msg ? `${icon('solar:danger-circle-linear')}<span>${esc(msg)}</span>` : '';
    }

    function naturalSize(file) {
        return new Promise((resolve) => {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => { resolve([img.naturalWidth, img.naturalHeight]); URL.revokeObjectURL(url); };
            img.onerror = () => { resolve([0, 0]); URL.revokeObjectURL(url); };
            img.src = url;
        });
    }

    async function upload(kind, file) {
        imageError(kind, '');
        const rules = IMG[kind];
        if (!rules.types.includes(file.type)) return imageError(kind, rules.typeMsg);
        if (file.size > rules.max) return imageError(kind, rules.sizeMsg);
        if (kind === 'favicon') {
            const [w, h] = await naturalSize(file);
            if (Math.min(w, h) < 512) return imageError(kind, `The browser icon must be at least 512 × 512 pixels${w ? ` (this one is ${w} × ${h})` : ''}.`);
        }
        const thumb = api.editorBody.querySelector(`#wcbThumb-${kind}`);
        thumb.innerHTML = '<span class="wcp-spinner wcb-spin" aria-hidden="true"></span>';
        api.setStatus('Uploading…', 'saving');

        const fd = new FormData();
        fd.append('kind', kind);
        fd.append('file', file);
        try {
            const r = await fetch(H.routes.brandingImage, { method: 'POST', headers: { 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' }, body: fd });
            const json = await r.json().catch(() => ({}));
            if (!r.ok) throw new Error((json.errors && json.errors.file && json.errors.file[0]) || json.message || 'Upload failed. Please try again.');
            const v = b();
            if (kind === 'logo') v.website_logo = json.url;
            else { v.website_favicon = json.url; v.website_favicon_icons = json.icons || {}; }
            renderImages();
            changed();
            flushSave();
            if (json.cropped) api.setStatus('Uploaded — the icon was cropped to a square', 'ok');
        } catch (e) {
            renderImages();
            imageError(kind, e.message);
            api.setStatus('Upload failed', 'error');
        }
    }

    function removeImage(kind) {
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: kind === 'logo' ? 'Remove your logo?' : 'Remove your browser icon?',
            message: `Your website goes back to its built-in ${kind === 'logo' ? 'crest' : 'icon'}.`,
            note: 'Nothing changes on your website until you publish.',
            label: 'Remove',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            const v = b();
            if (kind === 'logo') v.website_logo = '';
            else { v.website_favicon = ''; v.website_favicon_icons = {}; }
            renderImages();
            changed();
            flushSave();
        });
    }

    /* ── previews (mirror the website) ── */
    function renderPreview() {
        const box = api.editorBody.querySelector('#wcbPreview');
        if (!box) return; // the live preview panel shows these now
        if (!box) return;
        const v = b();
        const short = String(v.short_name || '').trim() || 'Short name';
        const [l1, l2] = taglineLines(v.tagline);
        const icons = v.website_favicon_icons || {};
        const tabIcon = icons['32'] || v.website_favicon || builtInIcon();
        const logo = v.website_logo || builtInLogo();
        const theme = api.getData().theme || {};

        box.innerHTML = `
            <figure class="wcp-card wcb-pv">
                <figcaption>Browser tab</figcaption>
                <div class="wcb-tabbar"><span class="wcb-tab"><span class="wcb-tab-icon">${imgTag(tabIcon, initials())}</span><span class="wcb-tab-title">About Us | ${esc(short)}</span></span></div>
                <p class="wcp-hint">Every page’s tab ends with your short name.</p>
            </figure>
            <figure class="wcp-card wcb-pv">
                <figcaption>Header and homepage</figcaption>
                <div class="wcb-site" style="--wcb-navy:${esc(theme.navbar || '#0F2646')}">
                    <div class="wcb-header">
                        <span class="wcb-logo-circle">${imgTag(logo, initials())}</span>
                        <span><span class="wcb-short">${esc(short.toUpperCase())}</span><span class="wcb-sub">INDEPENDENT ONLINE SCHOOL</span></span>
                    </div>
                    <div class="wcb-hero">
                        <p class="wcb-h1">${l1 ? esc(l1) : '<em>Your tagline</em>'}${l2 ? `<br><span class="wcb-gold">${esc(l2)}</span>` : ''}</p>
                    </div>
                </div>
            </figure>
            <figure class="wcp-card wcb-pv">
                <figcaption>Bottom of every page</figcaption>
                <div class="wcb-footer" style="background:${esc(theme.footer || '#0A1B33')}">${esc(copyrightLine(v))}</div>
            </figure>`;
    }

    /* ═══════════════════════════ shell integration ═══════════════════════════ */

    document.addEventListener('click', (e) => {
        const item = e.target.closest('.wcm-nav-item');
        if (!item || item.dataset.module === MODULE || activeModule() !== MODULE) return;
        if (save.dirty) flushSave();
        restoreShell();
    }, true);

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmDiscard');
        if (!btn || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (W.publishedBranding == null || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your branding changes?',
            message: 'Names, logo, browser icon, tagline, description and copyright go back to the live versions.',
            note: 'Only branding changes — other settings stay as they are.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            clearTimeout(save.timer);
            const pub = clone(W.publishedBranding);
            const v = b();
            KEYS.forEach((k) => { v[k] = pub[k] === undefined ? (k === 'copyright_auto_year' ? true : (k === 'website_favicon_icons' ? {} : '')) : pub[k]; });
            save.dirty = true;
            renderBranding(api);
            flushSave().then((done) => { if (done) api.setStatus('Changes discarded', 'ok'); });
        });
    }, true);

    let publishing = false;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmPublish');
        if (!btn || publishing || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (Object.keys(errors()).length) {
            setState('invalid');
            showErrors();
            const first = api.editorBody.querySelector('.wcp-error:not([hidden])');
            const input = first && api.editorBody.querySelector(`[aria-describedby~="${first.id}"]`);
            if (input) input.focus();
            return;
        }
        flushSave().then((ok) => {
            if (!ok) return;
            try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
            window.__wcbSkipUnloadWarning = window.__wcsSkipUnloadWarning = window.__wccSkipUnloadWarning = window.__wcaSkipUnloadWarning = window.__wcpSkipUnloadWarning = window.__wcnSkipUnloadWarning = window.__wcfSkipUnloadWarning = true;
            publishing = true;
            btn.click();
            publishing = false;
        });
    }, true);

    ext.renderers[MODULE] = renderBranding;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);

    ext.brandingModel = { copyrightLine, taglineLines };
})();
