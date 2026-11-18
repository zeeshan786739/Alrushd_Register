/**
 * Website CMS → Announcement & portal (connected website).
 *
 * Edits cmsData.website:
 *   announcement, announcement_url            message and optional link (plain URL or /path)
 *   announcement_enabled                      the switch (older data: on when there was text)
 *   announcement_link_text, announcement_style (gold | navy | cream)
 *   announcement_starts_at / _ends_at         optional YYYY-MM-DD, both inclusive
 *   portal_url, portal_button_text            the login button in the website header
 * The website shows the bar only when switched on, with text, inside the dates
 * (WebsiteCmsService::announcementIsActive). Saved as a site draft; "Publish" makes it live.
 * Loads after website-cms-headless.js / website-cms-pages.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc } = ext.shared;
    const MODULE = 'website';
    const REOPEN_KEY = 'wca:reopen';
    const AUTOSAVE_MS = 900;
    const CUSTOM = '__custom__';
    const RECOMMENDED = 90;
    const HARD_MAX = 120;
    const DEFAULT_LINK_TEXT = 'Find out more';
    const STYLES = [
        { key: 'gold', label: 'Gold', bg: '#AE9A66', fg: '#0F2646' },
        { key: 'navy', label: 'Navy', bg: '#143256', fg: '#FFFFFF' },
        { key: 'cream', label: 'Cream', bg: '#F6F2E9', fg: '#143256' },
    ];
    const KEYS = ['announcement', 'announcement_url', 'announcement_enabled', 'announcement_link_text', 'announcement_style',
        'announcement_starts_at', 'announcement_ends_at', 'portal_url', 'portal_button_text'];

    let api = null;
    let customLink = false;
    let serverErrors = {};
    let otherSettingsPending = null;
    const save = { timer: null, inflight: null, state: 'idle', dirty: false };

    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;
    const pages = () => (ext.getPages ? ext.getPages() : H.pages) || [];

    /* ───────────────────────── data ───────────────────────── */

    function w() {
        const data = api.getData();
        data.website = data.website || {};
        return data.website;
    }

    /** Same shape the server stores (WebsiteCmsService::normaliseWebsiteSection), for comparisons. */
    function normalised(src) {
        const v = src || {};
        const out = {};
        KEYS.forEach((k) => { out[k] = String(v[k] == null ? '' : v[k]).trim(); });
        out.announcement_enabled = v.announcement_enabled === undefined ? out.announcement !== '' : (v.announcement_enabled === true || v.announcement_enabled === 'true' || v.announcement_enabled === 1 || v.announcement_enabled === '1');
        if (!STYLES.some((s) => s.key === out.announcement_style)) out.announcement_style = 'gold';
        ['announcement_starts_at', 'announcement_ends_at'].forEach((k) => { if (!/^\d{4}-\d{2}-\d{2}$/.test(out[k])) out[k] = ''; });
        return out;
    }

    const matchesLive = () => W.publishedWebsite != null && JSON.stringify(normalised(W.publishedWebsite)) === JSON.stringify(normalised(w()));

    function pageFor(href) {
        if (!String(href || '').startsWith('/')) return null;
        const path = String(href).split(/[?#]/)[0].replace(/(.)\/+$/, '$1');
        return pages().find((p) => p.draft && p.draft.path === path) || null;
    }

    function validLink(href) {
        const h = String(href || '').trim();
        return /^\/(?!\/)\S*$/.test(h) || /^https?:\/\/[^\s/$.?#].[^\s]*$/i.test(h) || /^(mailto|tel):\S+$/i.test(h) || /^#\S*$/.test(h);
    }

    function host() {
        try { return new URL((H.connection && H.connection.external_url) || W.publicWebsiteUrl).host.replace(/^www\./, ''); } catch (e) { return 'your-website'; }
    }

    function today() {
        const d = new Date();
        const p = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
    }

    const fmt = (ymd) => new Date(ymd + 'T12:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: ymd.slice(0, 4) === today().slice(0, 4) ? undefined : 'numeric' });

    /** Showing today? and a sentence describing the schedule. */
    function schedule(v) {
        const s = v.announcement_starts_at;
        const e = v.announcement_ends_at;
        const t = today();
        const inWindow = (!s || t >= s) && (!e || t <= e);
        let note = '';
        if (s || e) {
            if (e && t > e) note = `Ended on ${fmt(e)} – no longer showing`;
            else if (s && t < s) note = `Scheduled: shows from ${fmt(s)}${e ? ` to ${fmt(e)}` : ''}`;
            else if (e) note = `Showing until ${fmt(e)}`;
            else note = `Showing since ${fmt(s)}`;
        }
        return { inWindow, note };
    }

    /* ───────────────────────── validation ───────────────────────── */

    function errors() {
        const v = w();
        const out = {};
        const text = String(v.announcement || '');
        if (v.announcement_enabled && !text.trim()) out.announcement = 'Write a message, or switch the bar off.';
        if (text.length > HARD_MAX) out.announcement = `Keep the message under ${HARD_MAX} characters.`;
        if (v.announcement_url && !validLink(v.announcement_url)) out.announcement_url = 'Choose one of your pages, or a full web address starting with https://';
        if (v.announcement_starts_at && v.announcement_ends_at && v.announcement_ends_at < v.announcement_starts_at) {
            out.announcement_ends_at = 'The end date must be on or after the start date.';
        }
        const portal = String(v.portal_url || '').trim();
        if (portal && !/^https?:\/\/[^\s/$.?#].[^\s]*$/i.test(portal)) out.portal_url = 'Enter the full login address, starting with https://';
        Object.keys(serverErrors).forEach((k) => { if (!out[k]) out[k] = serverErrors[k]; });
        return out;
    }

    /* ───────────────────────── save / status / shell ───────────────────────── */

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
        else if (live) { text = 'This matches your live website.'; tone = 'ok'; }
        else text = 'Changes saved as a draft — publish to show them on your website.';
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = !live && W.publishedWebsite != null;
            discard.disabled = !can;
            discard.title = can ? 'Put the announcement and portal settings back to the live version' : '';
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
                Object.entries(json.errors || {}).forEach(([k, m]) => { serverErrors[k.replace(/^website\./, '')] = m[0]; });
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
        if (window.__wcaSkipUnloadWarning) return;
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

    function linkOptions(href) {
        const match = pageFor(href);
        const custom = customLink || (href && !match);
        return `<option value="" ${!href && !custom ? 'selected' : ''}>No link</option>`
            + pages().map((p) => {
                const note = p.kind === 'custom' && p.draft.hidden ? ' — switched off' : (!p.is_published ? ' — draft' : '');
                return `<option value="${esc(p.draft.path)}" ${match && match.id === p.id && !custom ? 'selected' : ''}>${esc(p.draft.title || p.draft.path)} (${esc(p.draft.path)})${note}</option>`;
            }).join('')
            + `<option value="${CUSTOM}" ${custom ? 'selected' : ''}>Another web address…</option>`;
    }

    function renderAnnounce(a) {
        api = a;
        rememberShell();
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'Announcement & portal';
        if (desc) { desc.textContent = 'A message bar across the top of every page, and the login link for parents and students.'; desc.hidden = false; }
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;

        // Older settings have no switch: on when there was text (what the website did).
        const v = w();
        const norm = normalised(v);
        v.announcement_enabled = norm.announcement_enabled;
        v.announcement_style = norm.announcement_style;
        if (v.portal_button_text === undefined) v.portal_button_text = 'Portal Login';
        customLink = !!(v.announcement_url && !pageFor(v.announcement_url));
        serverErrors = {};

        api.editorBody.innerHTML = `<div class="wcp wca">
            <div class="wca-grid">
                <section class="wcp-card wca-bar-card" aria-labelledby="wcaBarH">
                    <div class="wca-card-head">
                        <div>
                            <h2 id="wcaBarH" class="wcn-h2">Announcement bar</h2>
                            <p class="wcp-card-desc">Great for open days, admissions deadlines or holiday closures.</p>
                        </div>
                        <div class="wca-switch-wrap">
                            <button type="button" class="wca-switch" id="wcaSwitch" role="switch" aria-checked="${v.announcement_enabled}" aria-labelledby="wcaBarH wcaSwitchLabel"><span></span></button>
                            <span id="wcaSwitchLabel" class="wca-switch-label">${v.announcement_enabled ? 'Showing' : 'Hidden'}</span>
                        </div>
                    </div>
                    <p class="wca-schedule-note" id="wcaScheduleNote" hidden></p>
                    <p class="wca-off-note" id="wcaOff" ${v.announcement_enabled ? 'hidden' : ''}>${icon('solar:eye-closed-linear')}The bar is hidden on your website. Switch it on to write a message.</p>
                    <div id="wcaOn" ${v.announcement_enabled ? '' : 'hidden'}>
                        <div class="wcp-field">
                            <div class="wcp-label-row"><label for="wcaText">Message</label><span class="wcp-counter" id="wcaCount" aria-live="polite"></span></div>
                            <input type="text" id="wcaText" class="wcp-input" value="${esc(v.announcement || '')}" maxlength="${HARD_MAX}" autocomplete="off" aria-describedby="wcaTextHint wcaErr-announcement">
                            <p class="wcp-hint" id="wcaTextHint">Keep it short so it fits on one line on phones (under ${RECOMMENDED} characters).</p>
                            <p class="wcp-error" id="wcaErr-announcement" hidden></p>
                        </div>
                        <div class="wca-row-2">
                            <div class="wcp-field">
                                <label for="wcaLink">Link to <span class="wcp-muted">(optional)</span></label>
                                <select id="wcaLink" class="wcp-input">${linkOptions(v.announcement_url || '')}</select>
                            </div>
                            <div class="wcp-field">
                                <label for="wcaLinkText">Link text</label>
                                <input type="text" id="wcaLinkText" class="wcp-input" value="${esc(v.announcement_link_text || '')}" maxlength="40" placeholder="${esc(DEFAULT_LINK_TEXT)}" autocomplete="off" aria-describedby="wcaLinkTextHint">
                                <p class="wcp-hint" id="wcaLinkTextHint" hidden>Leave empty to make the whole message the link.</p>
                            </div>
                        </div>
                        <div class="wcp-field" id="wcaUrlField" ${customLink ? '' : 'hidden'}>
                            <label for="wcaUrl">Web address</label>
                            <input type="text" id="wcaUrl" class="wcp-input" value="${esc(v.announcement_url || '')}" placeholder="https://… or /page" autocomplete="off" spellcheck="false" aria-describedby="wcaErr-announcement_url">
                        </div>
                        <p class="wcp-error" id="wcaErr-announcement_url" hidden></p>
                        <fieldset class="wca-colours">
                            <legend>Colour</legend>
                            <div class="wca-swatches">
                                ${STYLES.map((s) => `<label class="wca-swatch ${v.announcement_style === s.key ? 'is-selected' : ''}">
                                    <input type="radio" name="wcaStyle" value="${s.key}" ${v.announcement_style === s.key ? 'checked' : ''}>
                                    <span class="wca-swatch-chip" style="background:${s.bg}"></span>${esc(s.label)}
                                </label>`).join('')}
                            </div>
                        </fieldset>
                        <div class="wca-schedule">
                            <p class="wca-schedule-title">Schedule <span class="wcp-muted">(optional)</span></p>
                            <div class="wca-row-2">
                                <div class="wcp-field">
                                    <label for="wcaStart" class="wca-small-label">Start showing on</label>
                                    <input type="date" id="wcaStart" class="wcp-input" value="${esc(v.announcement_starts_at || '')}">
                                </div>
                                <div class="wcp-field">
                                    <label for="wcaEnd" class="wca-small-label">Hide automatically after</label>
                                    <input type="date" id="wcaEnd" class="wcp-input" value="${esc(v.announcement_ends_at || '')}" aria-describedby="wcaErr-announcement_ends_at">
                                </div>
                            </div>
                            <p class="wcp-error" id="wcaErr-announcement_ends_at" hidden></p>
                            <p class="wcp-hint">Leave both empty to show the bar until you switch it off.</p>
                        </div>
                    </div>
                </section>

                <section class="wcp-card wca-portal-card" aria-labelledby="wcaPortalH">
                    <div class="wca-portal-head">
                        <span class="wcp-tile wcp-tile--navy wcfm-tile">${icon('solar:user-linear')}</span>
                        <div>
                            <h2 id="wcaPortalH" class="wcn-h2">Parent &amp; student portal</h2>
                            <p class="wcp-card-desc">The login link shown in your website's header.</p>
                        </div>
                    </div>
                    <div class="wcp-field">
                        <label for="wcaPortalText">Button text</label>
                        <input type="text" id="wcaPortalText" class="wcp-input" value="${esc(v.portal_button_text || '')}" maxlength="40" placeholder="Parent &amp; student login" autocomplete="off">
                    </div>
                    <div class="wcp-field">
                        <label for="wcaPortalUrl">Login page address</label>
                        <input type="url" id="wcaPortalUrl" class="wcp-input" value="${esc(v.portal_url || '')}" placeholder="https://school.example.co.uk/login" autocomplete="off" spellcheck="false" aria-describedby="wcaPortalCheck wcaErr-portal_url">
                        <p class="wca-check" id="wcaPortalCheck" aria-live="polite"></p>
                        <p class="wcp-error" id="wcaErr-portal_url" hidden></p>
                    </div>
                    <button type="button" class="wcp-btn wcp-btn--outline wcn-full" id="wcaTest">${icon('solar:square-arrow-right-up-linear')}Test the link<span class="visually-hidden"> (opens in a new tab)</span></button>
                </section>
            </div>
        </div>`;

        bind();
        renderPreview();
        refresh();
        showErrors();
        if (save.state === 'idle') { updateSavebar(); api.setStatus(matchesLive() ? 'This matches your live website' : 'Draft saved', 'ok'); } else setState(save.state);
    }

    /** Parts of the form that depend on other values — never re-renders an input being typed in. */
    function refresh() {
        const b = api.editorBody;
        const v = w();
        const q = (s) => b.querySelector(s);
        const on = !!v.announcement_enabled;
        q('#wcaSwitch').setAttribute('aria-checked', String(on));
        q('#wcaSwitchLabel').textContent = on ? 'Showing' : 'Hidden';
        q('#wcaOff').hidden = on;
        q('#wcaOn').hidden = !on;

        const n = String(v.announcement || '').length;
        const count = q('#wcaCount');
        count.textContent = `${n} / ${RECOMMENDED}`;
        count.className = 'wcp-counter ' + (n === 0 ? '' : n <= RECOMMENDED ? 'is-ok' : 'is-over');
        count.setAttribute('aria-label', `${n} of ${RECOMMENDED} characters${n > RECOMMENDED ? ', may wrap onto two lines on phones' : ''}`);

        const hasLink = !!String(v.announcement_url || '').trim() || customLink;
        const lt = q('#wcaLinkText');
        lt.disabled = !hasLink;
        q('#wcaLinkTextHint').hidden = !hasLink;
        q('#wcaUrlField').hidden = !customLink;

        b.querySelectorAll('.wca-swatch').forEach((l) => l.classList.toggle('is-selected', l.querySelector('input').checked));

        const sched = schedule(v);
        const note = q('#wcaScheduleNote');
        note.hidden = !on || !sched.note;
        note.textContent = sched.note;
        note.className = 'wca-schedule-note ' + (sched.inWindow ? 'is-live' : 'is-off');

        const portal = String(v.portal_url || '').trim();
        const check = q('#wcaPortalCheck');
        if (!portal) { check.className = 'wca-check'; check.innerHTML = 'Leave empty to use the website’s standard login page.'; }
        else if (/^https:\/\//i.test(portal)) { check.className = 'wca-check is-ok'; check.innerHTML = `${icon('solar:check-read-linear')}Secure link (https)`; }
        else { check.className = 'wca-check is-warn'; check.innerHTML = `${icon('solar:danger-triangle-linear')}The address should start with https://`; }
        q('#wcaTest').disabled = !/^https?:\/\/\S+\.\S+/i.test(portal);
        updateSavebar();
    }

    function showErrors() {
        const errs = errors();
        ['announcement', 'announcement_url', 'announcement_ends_at', 'portal_url'].forEach((k) => {
            const box = api.editorBody.querySelector(`#wcaErr-${k}`);
            if (!box) return;
            const msg = errs[k];
            box.hidden = !msg;
            box.innerHTML = msg ? `${icon('solar:danger-circle-linear')}<span>${esc(msg)}</span>` : '';
            const input = api.editorBody.querySelector(`[aria-describedby~="wcaErr-${k}"]`);
            if (input) input.setAttribute('aria-invalid', msg ? 'true' : 'false');
        });
    }

    function bind() {
        const b = api.editorBody;
        const q = (s) => b.querySelector(s);
        const flushOnBlur = (el) => el.addEventListener('blur', () => { if (save.dirty) flushSave(); });

        q('#wcaSwitch').addEventListener('click', () => {
            const v = w();
            v.announcement_enabled = !v.announcement_enabled;
            changed();
            if (v.announcement_enabled) q('#wcaText').focus();
        });
        q('#wcaText').addEventListener('input', (e) => { w().announcement = e.target.value; changed('announcement'); });
        q('#wcaLink').addEventListener('change', (e) => {
            const v = w();
            if (e.target.value === CUSTOM) {
                customLink = true;
                refresh();
                q('#wcaUrl').focus();
                return;
            }
            customLink = false;
            v.announcement_url = e.target.value;
            q('#wcaUrl').value = e.target.value;
            if (e.target.value && !String(v.announcement_link_text || '').trim()) {
                v.announcement_link_text = DEFAULT_LINK_TEXT;
                q('#wcaLinkText').value = DEFAULT_LINK_TEXT;
            }
            changed('announcement_url');
            flushSave();
        });
        q('#wcaUrl').addEventListener('input', (e) => { w().announcement_url = e.target.value.trim(); changed('announcement_url'); });
        q('#wcaLinkText').addEventListener('input', (e) => { w().announcement_link_text = e.target.value; changed('announcement_link_text'); });
        b.querySelectorAll('input[name="wcaStyle"]').forEach((r) => r.addEventListener('change', () => { w().announcement_style = r.value; changed('announcement_style'); flushSave(); }));
        q('#wcaStart').addEventListener('change', (e) => { w().announcement_starts_at = e.target.value; changed('announcement_starts_at'); });
        q('#wcaEnd').addEventListener('change', (e) => { w().announcement_ends_at = e.target.value; changed('announcement_ends_at'); });
        q('#wcaPortalText').addEventListener('input', (e) => { w().portal_button_text = e.target.value; changed('portal_button_text'); });
        q('#wcaPortalUrl').addEventListener('input', (e) => { w().portal_url = e.target.value.trim(); changed('portal_url'); });
        ['#wcaText', '#wcaUrl', '#wcaLinkText', '#wcaStart', '#wcaEnd', '#wcaPortalText', '#wcaPortalUrl'].forEach((s) => flushOnBlur(q(s)));
        q('#wcaTest').addEventListener('click', () => {
            const url = String(w().portal_url || '').trim();
            if (/^https?:\/\//i.test(url)) window.open(url, '_blank', 'noopener');
        });
    }

    /* ── preview: browser frame with the bar and the site header ── */
    function topLevelMenu() {
        const items = (api.getData().nav_menu || {}).items || [];
        const out = [];
        items.forEach((it) => {
            if (!it || !it.label || !it.href) return; // the website skips these
            const g = String(it.group || '').trim();
            if (!g) out.push({ label: it.label, dropdown: false });
            else if (!out.some((x) => x.dropdown && x.label === g)) out.push({ label: g, dropdown: true });
        });
        return out;
    }

    function renderPreview() {
        const box = api.editorBody.querySelector('#wcaPreview');
        if (!box) return; // the live preview panel shows the bar now
        if (!box) return;
        const d = api.getData();
        const v = w();
        const b = d.branding || {};
        const nav = d.nav_menu || {};
        const style = STYLES.find((s) => s.key === v.announcement_style) || STYLES[0];
        const text = String(v.announcement || '').trim();
        const href = String(v.announcement_url || '').trim();
        const linkText = String(v.announcement_link_text || '').trim();
        const sched = schedule(v);
        const showBar = v.announcement_enabled && text;
        const name = b.company_name || 'Your school';
        const initials = name.split(/[\s-]+/).filter((x) => /^[A-Za-z]/.test(x)).slice(0, 2).map((x) => x[0].toUpperCase()).join('');

        let message = esc(text);
        if (href && linkText) message = `${esc(text)} <span class="wca-pv-link">${esc(linkText)} →</span>`;
        else if (href) message = `<span class="wca-pv-whole">${esc(text)} →</span>`;

        box.innerHTML = `<div class="wca-browser">
            <div class="wca-browser-top"><span class="wca-dots" aria-hidden="true"><i></i><i></i><i></i></span><span class="wca-address">${esc(host())}</span></div>
            ${showBar ? `<div class="wca-pv-bar ${sched.inWindow ? '' : 'is-scheduled'}" style="background:${style.bg};color:${style.fg}">
                <span>${message}</span>
                ${sched.inWindow ? '' : `<span class="wca-pv-tag">${sched.note.startsWith('Ended') ? 'Ended – not showing' : 'Scheduled – not showing today'}</span>`}
                ${icon('solar:close-circle-linear', 'wca-pv-close')}
            </div>` : ''}
            <div class="wca-pv-header">
                <span class="wca-pv-logo"><span class="wca-pv-mark">${b.logo ? `<img src="${esc(b.logo)}" alt="">` : esc(initials)}</span><span>${esc(b.short_name || name)}</span></span>
                <span class="wca-pv-menu">${topLevelMenu().slice(0, 6).map((m) => `<span>${esc(m.label)}${m.dropdown ? ' ▾' : ''}</span>`).join('')}</span>
                <span class="wca-pv-portal">${icon('solar:user-linear')}${esc(String(v.portal_button_text || '').trim() || 'Portal Login')}</span>
                <span class="wca-pv-cta">${esc(nav.cta_text || 'Apply Now')}</span>
            </div>
            <div class="wca-pv-page" aria-hidden="true"><i></i><i></i></div>
        </div>`;
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
        if (W.publishedWebsite == null || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your changes?',
            message: 'The announcement bar and portal button go back to the versions live on your website.',
            note: 'Only this screen’s settings change — website forms and other settings stay as they are.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            clearTimeout(save.timer);
            const v = w();
            const pub = W.publishedWebsite;
            KEYS.forEach((k) => { v[k] = pub[k] === undefined ? '' : pub[k]; });
            v.announcement_enabled = normalised(pub).announcement_enabled;
            if (pub.portal_button_text === undefined) v.portal_button_text = 'Portal Login';
            save.dirty = true;
            renderAnnounce(api);
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
            const first = api.editorBody.querySelector('.wcp-error:not([hidden])');
            const input = first && api.editorBody.querySelector(`[aria-describedby~="${first.id}"]`);
            if (input) input.focus();
            return;
        }
        flushSave().then((ok) => {
            if (!ok) return;
            try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
            window.__wcaSkipUnloadWarning = window.__wcpSkipUnloadWarning = window.__wcnSkipUnloadWarning = window.__wcfSkipUnloadWarning = true;
            publishing = true;
            btn.click();
            publishing = false;
        });
    }, true);

    ext.renderers[MODULE] = renderAnnounce;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);
})();
