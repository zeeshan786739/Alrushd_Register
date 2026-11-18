/**
 * Website CMS → Social media (connected website).
 *
 * Edits cmsData.social: one link per network + enabled[network] (the switch), and
 * whatsapp_use_contact — when on (default, and what the website always did) the WhatsApp icon
 * uses the link from Contact details. The icons appear in the website footer and on the Contact
 * page, only when switched on AND the link is valid for that network (App\Support\SocialProfiles;
 * linkProblem / handle / typoWarnings are mirrored here, and in the website's lib/social.ts).
 * Loads after website-cms-headless.js / website-cms-pages.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc, clone } = ext.shared;
    const MODULE = 'social';
    const REOPEN_KEY = 'wcs:reopen';
    const AUTOSAVE_MS = 900;
    const NETWORKS = W.socialNetworks || {};
    const KEYS = Object.keys(NETWORKS);
    // The website's icon order (siteConfig.ts SOCIAL_ORDER), WhatsApp second.
    const SITE_ORDER = ['facebook', 'whatsapp', 'instagram', 'youtube', 'tiktok', 'linkedin', 'twitter', 'threads'];
    const ICONS = {
        facebook: 'mdi:facebook', instagram: 'mdi:instagram', linkedin: 'mdi:linkedin', twitter: 'ri:twitter-x-fill',
        youtube: 'mdi:youtube', tiktok: 'ic:baseline-tiktok', whatsapp: 'mdi:whatsapp', threads: 'ri:threads-line',
    };
    const PLACEHOLDERS = {
        facebook: 'https://www.facebook.com/…', instagram: 'https://www.instagram.com/…', linkedin: 'https://www.linkedin.com/company/…',
        twitter: 'https://x.com/…', youtube: 'https://www.youtube.com/@…', tiktok: 'https://www.tiktok.com/@…',
        whatsapp: 'https://wa.me/…', threads: 'https://www.threads.net/@…',
    };

    let api = null;
    let serverErrors = {};
    let otherSettingsPending = null;
    const save = { timer: null, inflight: null, state: 'idle', dirty: false };

    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;

    /* ───────────────────────── rules (mirror App\Support\SocialProfiles) ───────────────────────── */

    function linkProblem(key, url) {
        const u = String(url || '').trim();
        if (!u) return null;
        let host = '';
        try { host = /^https?:\/\//i.test(u) ? new URL(u).hostname.toLowerCase() : ''; } catch (e) { host = ''; }
        if (!host) return 'format';
        return (NETWORKS[key]?.domains || []).some((d) => host === d || host.endsWith('.' + d)) ? null : 'domain';
    }

    function handle(key, url) {
        if (key === 'whatsapp' || !String(url || '').trim() || linkProblem(key, url)) return null;
        let segs = new URL(String(url).trim()).pathname.split('/').filter(Boolean);
        if (!segs.length || ['channel', 'watch', 'share', 'p', 'reel', 'profile.php'].includes(segs[0].toLowerCase())) return null;
        if (['company', 'school', 'in', 'c', 'user'].includes(segs[0].toLowerCase())) segs = segs.slice(1);
        const h = String(segs[0] || '').replace(/^@+/, '').toLowerCase();
        return h.length >= 3 ? h : null;
    }

    function editDistance(a, b) {
        const d = Array.from({ length: a.length + 1 }, (_, i) => [i, ...Array(b.length).fill(0)]);
        for (let j = 0; j <= b.length; j++) d[0][j] = j;
        for (let i = 1; i <= a.length; i++) {
            for (let j = 1; j <= b.length; j++) {
                const cost = a[i - 1] === b[j - 1] ? 0 : 1;
                d[i][j] = Math.min(d[i - 1][j] + 1, d[i][j - 1] + 1, d[i - 1][j - 1] + cost);
                if (i > 1 && j > 1 && a[i - 1] === b[j - 2] && a[i - 2] === b[j - 1]) d[i][j] = Math.min(d[i][j], d[i - 2][j - 2] + 1);
            }
        }
        return d[a.length][b.length];
    }

    function typoWarnings(links) {
        const handles = {};
        Object.entries(links).forEach(([k, u]) => { const h = handle(k, u); if (h) handles[k] = h; });
        const squash = (h) => h.replace(/[^a-z0-9]/g, '');
        const all = Object.values(handles);
        const support = (h) => all.filter((o) => squash(o).includes(squash(h))).length;
        const out = {};
        Object.entries(handles).forEach(([k, h]) => {
            let best = null;
            [...new Set(all)].forEach((o) => {
                if (o === h || o.length < 4) return;
                const dist = editDistance(squash(h), squash(o));
                if (dist >= 1 && dist <= 2 && support(o) > support(h) && (best === null || support(o) > support(best))) best = o;
            });
            if (best) out[k] = { handle: h, suggestion: best };
        });
        return out;
    }

    /* ───────────────────────── data ───────────────────────── */

    function s() {
        const data = api.getData();
        data.social = data.social || {};
        data.social.enabled = data.social.enabled || {};
        if (data.social.whatsapp_use_contact === undefined || data.social.whatsapp_use_contact === null) data.social.whatsapp_use_contact = true;
        return data.social;
    }

    const isOn = (k) => s().enabled[k] !== false;

    /** The WhatsApp link the icon would use, and why it might be missing. */
    function whatsappSource() {
        const contact = api.getData().contact || {};
        const link = String(contact.whatsapp || '').trim();
        const off = contact.whatsapp_enabled === false || (contact.whatsapp_enabled == null && !link);
        return { link: off ? '' : link, off };
    }

    function linkFor(k) {
        if (k === 'whatsapp' && s().whatsapp_use_contact) return whatsappSource().link;
        return String(s()[k] || '').trim();
    }

    const shown = (k) => isOn(k) && !!linkFor(k) && !linkProblem(k, linkFor(k));

    function rowInfo(k, typos) {
        const link = linkFor(k);
        const problem = linkProblem(k, link);
        const name = NETWORKS[k].name;
        let error = '';
        let warn = '';
        if (serverErrors[k]) error = serverErrors[k];
        else if (problem === 'format') error = 'Paste the full link, starting with https://';
        else if (problem === 'domain') error = `This doesn’t look like ${/^[AEIOU]/i.test(name) ? 'an' : 'a'} ${name} link.`;
        if (!error && isOn(k) && !link) {
            warn = k === 'whatsapp' && s().whatsapp_use_contact && whatsappSource().off
                ? 'WhatsApp is switched off in Contact details, so this icon can’t appear.'
                : 'Add a link — this icon can’t appear without one.';
        }
        if (!error && !warn && typos[k]) warn = `The username “${typos[k].handle}” is spelled differently from your other profiles (${typos[k].suggestion}). Is it a typo?`;
        let status = ['Not added', 'off'];
        if (error || warn) status = ['Needs attention', 'warn'];
        else if (shown(k)) status = ['Shown on website', 'ok'];
        else if (link) status = ['Saved, hidden', 'off'];
        return { error, warn, status };
    }

    function snapshot(src) {
        if (!src) return null;
        const o = { enabled: {} };
        KEYS.forEach((k) => { o[k] = String(src[k] == null ? '' : src[k]).trim(); o.enabled[k] = (src.enabled || {})[k] !== false; });
        o.whatsapp_use_contact = src.whatsapp_use_contact !== false;
        return JSON.stringify(o);
    }

    const matchesLive = () => W.publishedSocial != null && snapshot(W.publishedSocial) === snapshot(s());
    const blocking = () => KEYS.some((k) => serverErrors[k] || ((k !== 'whatsapp' || !s().whatsapp_use_contact) && linkProblem(k, s()[k])));

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
        if (save.state === 'invalid') text = 'Fix the highlighted link to save your changes.';
        else if (save.state === 'error') text = 'Your last change wasn’t saved — check your connection.';
        else if (save.dirty || save.timer || save.inflight) text = 'Saving your changes…';
        else if (live) { text = 'Your social links match the live website.'; tone = 'ok'; }
        else text = 'Changes saved as a draft — publish to show them on your website.';
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = !live && W.publishedSocial != null;
            discard.disabled = !can;
            discard.title = can ? 'Put the social links back to the live version' : '';
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
                invalid: ['Couldn’t save – fix the highlighted link', 'error'], error: ['Couldn’t save – check your connection', 'error'],
            };
            if (map[state]) api.setStatus(map[state][0], map[state][1]);
        }
        updateSavebar();
    }

    function scheduleSave() {
        save.dirty = true;
        clearTimeout(save.timer);
        save.timer = null;
        if (blocking()) { setState('invalid'); return; }
        setState('pending');
        save.timer = setTimeout(flushSave, AUTOSAVE_MS);
    }

    function flushSave() {
        clearTimeout(save.timer);
        save.timer = null;
        if (save.inflight) return save.inflight.then(() => (save.dirty ? flushSave() : true));
        if (!save.dirty) return Promise.resolve(true);
        if (blocking()) { setState('invalid'); return Promise.resolve(false); }
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
                Object.entries(json.errors || {}).forEach(([k, m]) => { serverErrors[k.replace(/^social\./, '')] = m[0]; });
                save.dirty = true;
                refreshAll();
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
        if (window.__wcsSkipUnloadWarning) return;
        if (save.timer || save.inflight || (save.dirty && save.state !== 'idle')) { e.preventDefault(); e.returnValue = ''; }
    });

    function changed(key) {
        if (key) delete serverErrors[key];
        refreshAll();
        scheduleSave();
    }

    /* ═══════════════════════════ render ═══════════════════════════ */

    function renderSocial(a) {
        api = a;
        rememberShell();
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'Social media';
        if (desc) { desc.textContent = 'Add your school’s profiles. Switch on the ones you want as icons on your website.'; desc.hidden = false; }
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;
        serverErrors = {};
        const v = s();

        api.editorBody.innerHTML = `<div class="wcp wcs">
            <section class="wcp-card wcs-profiles" aria-labelledby="wcsProfilesH">
                <div class="wcs-profiles-head">
                    <div>
                        <h2 id="wcsProfilesH" class="wcn-h2">Your profiles</h2>
                        <p class="wcp-card-desc">Paste the full link to each profile page.</p>
                    </div>
                    <p class="wcs-legend" aria-hidden="true"><span class="is-ok">Shown</span><span class="is-off">Hidden</span><span class="is-warn">Needs attention</span></p>
                </div>
                <ul class="wcs-rows">
                    ${KEYS.map((k) => {
                        const name = NETWORKS[k].name;
                        const wa = k === 'whatsapp';
                        return `<li class="wcs-row" data-row="${k}">
                            <div class="wcs-who">
                                <span class="wcp-tile wcp-tile--navy wcs-tile">${icon(ICONS[k] || 'solar:link-linear')}</span>
                                <span><strong>${esc(name)}</strong><span class="wcs-status" id="wcsStatus-${k}"></span></span>
                            </div>
                            <div class="wcs-link">
                                <div class="wcs-link-row">
                                    <label class="visually-hidden" for="wcsUrl-${k}">${esc(name)} link</label>
                                    <input type="url" id="wcsUrl-${k}" class="wcp-input" data-url="${k}" value="${esc(wa && v.whatsapp_use_contact ? linkFor(k) : (v[k] || ''))}" placeholder="${esc(PLACEHOLDERS[k] || 'https://…')}" autocomplete="off" spellcheck="false" aria-describedby="wcsMsg-${k}" ${wa && v.whatsapp_use_contact ? 'readonly' : ''}>
                                    <a class="wcp-icon-btn wcs-open" id="wcsOpen-${k}" href="#" target="_blank" rel="noopener" aria-label="Open ${esc(name)} profile (new tab)">${icon('solar:square-arrow-right-up-linear')}</a>
                                </div>
                                ${wa ? `<label class="wcc-check-row wcs-wa-check"><input type="checkbox" id="wcsWaContact" ${v.whatsapp_use_contact ? 'checked' : ''}> Use the WhatsApp number from <button type="button" class="wcs-inline-link" id="wcsToContact">Contact details</button></label>` : ''}
                                <p class="wcs-msg" id="wcsMsg-${k}" hidden></p>
                            </div>
                            <div class="wca-switch-wrap wcs-switch">
                                <button type="button" class="wca-switch" data-switch="${k}" role="switch" aria-checked="${isOn(k)}" aria-label="Show ${esc(name)} icon on website"><span></span></button>
                                <span class="wca-switch-label" id="wcsSwitchLabel-${k}" aria-hidden="true">${isOn(k) ? 'Show' : 'Hide'}</span>
                            </div>
                        </li>`;
                    }).join('')}
                </ul>
            </section>
        </div>`;

        bind();
        refreshAll();
        if (save.state === 'idle') { updateSavebar(); api.setStatus(matchesLive() ? 'Your social links match the live website' : 'Draft saved', 'ok'); } else setState(save.state);
    }

    function refreshAll() {
        const b = api.editorBody;
        const links = {};
        KEYS.forEach((k) => { links[k] = linkFor(k); });
        const typos = typoWarnings(links);

        KEYS.forEach((k) => {
            const info = rowInfo(k, typos);
            const row = b.querySelector(`[data-row="${k}"]`);
            row.classList.toggle('needs-attention', !!(info.error || info.warn));
            b.querySelector(`#wcsStatus-${k}`).innerHTML = `<span class="wcs-dot is-${info.status[1]}" aria-hidden="true"></span>${esc(info.status[0])}`;
            const msg = b.querySelector(`#wcsMsg-${k}`);
            msg.hidden = !(info.error || info.warn);
            msg.className = 'wcs-msg ' + (info.error ? 'is-error' : 'is-warn');
            msg.innerHTML = info.error || info.warn ? `${icon(info.error ? 'solar:danger-circle-linear' : 'solar:danger-triangle-linear')}<span>${esc(info.error || info.warn)}</span>` : '';
            const input = b.querySelector(`#wcsUrl-${k}`);
            input.setAttribute('aria-invalid', info.error ? 'true' : 'false');
            const open = b.querySelector(`#wcsOpen-${k}`);
            const link = links[k];
            const openable = link && !linkProblem(k, link);
            open.href = openable ? link : '#';
            open.classList.toggle('is-disabled', !openable);
            open.setAttribute('aria-disabled', String(!openable));
            b.querySelector(`[data-switch="${k}"]`).setAttribute('aria-checked', String(isOn(k)));
            b.querySelector(`#wcsSwitchLabel-${k}`).textContent = isOn(k) ? 'Show' : 'Hide';
        });

        const wa = b.querySelector('#wcsUrl-whatsapp');
        if (wa) {
            const fromContact = !!s().whatsapp_use_contact;
            wa.readOnly = fromContact;
            if (fromContact) wa.value = linkFor('whatsapp');
        }

        const visible = SITE_ORDER.filter((k) => NETWORKS[k] && shown(k));
        const count = b.querySelector('#wcsCount');
        const strip = b.querySelector('#wcsStrip'); // removed: the live preview panel shows the icons
        if (count) count.textContent = `${visible.length} of ${KEYS.length} profiles shown`;
        if (strip) strip.innerHTML = visible.length
            ? `<ul aria-label="Icons in order">${visible.map((k) => `<li title="${esc(NETWORKS[k].name)}">${icon(ICONS[k])}<span class="visually-hidden">${esc(NETWORKS[k].name)}</span></li>`).join('')}</ul>`
            : '<p>No icons shown — switch some on below.</p>';
        updateSavebar();
    }

    function bind() {
        const b = api.editorBody;
        b.querySelectorAll('[data-url]').forEach((input) => {
            input.addEventListener('input', () => {
                if (input.readOnly) return;
                s()[input.dataset.url] = input.value.trim();
                changed(input.dataset.url);
            });
            input.addEventListener('blur', () => { if (save.dirty) flushSave(); });
        });
        b.querySelectorAll('[data-switch]').forEach((btn) => btn.addEventListener('click', () => {
            const k = btn.dataset.switch;
            s().enabled[k] = !isOn(k);
            changed();
            flushSave();
        }));
        b.querySelectorAll('.wcs-open').forEach((a) => a.addEventListener('click', (e) => {
            if (a.classList.contains('is-disabled')) e.preventDefault();
        }));
        const waCheck = b.querySelector('#wcsWaContact');
        if (waCheck) {
            waCheck.addEventListener('change', () => {
                const v = s();
                v.whatsapp_use_contact = waCheck.checked;
                const input = b.querySelector('#wcsUrl-whatsapp');
                input.value = waCheck.checked ? linkFor('whatsapp') : String(v.whatsapp || '');
                changed('whatsapp');
                flushSave();
                if (!waCheck.checked) input.focus();
            });
        }
        b.querySelector('#wcsToContact')?.addEventListener('click', (e) => {
            e.preventDefault();
            flushSave().then(() => { restoreShell(); api.switchModule('contact'); });
        });
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
        if (W.publishedSocial == null || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your social media changes?',
            message: 'All links and switches go back to the versions live on your website.',
            note: 'Only social media changes — other settings stay as they are.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            clearTimeout(save.timer);
            const pub = clone(W.publishedSocial);
            const v = s();
            KEYS.forEach((k) => { v[k] = pub[k] == null ? '' : pub[k]; });
            v.enabled = pub.enabled || {};
            v.whatsapp_use_contact = pub.whatsapp_use_contact !== false;
            save.dirty = true;
            renderSocial(api);
            flushSave().then((done) => { if (done) api.setStatus('Changes discarded', 'ok'); });
        });
    }, true);

    let publishing = false;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmPublish');
        if (!btn || publishing || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (blocking()) {
            setState('invalid');
            const first = api.editorBody.querySelector('[aria-invalid="true"]');
            if (first) first.focus();
            return;
        }
        flushSave().then((ok) => {
            if (!ok) return;
            try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
            window.__wcsSkipUnloadWarning = window.__wccSkipUnloadWarning = window.__wcaSkipUnloadWarning = window.__wcpSkipUnloadWarning = window.__wcnSkipUnloadWarning = window.__wcfSkipUnloadWarning = true;
            publishing = true;
            btn.click();
            publishing = false;
        });
    }, true);

    ext.renderers[MODULE] = renderSocial;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);

    ext.socialModel = { linkProblem, handle, typoWarnings, editDistance };
})();
