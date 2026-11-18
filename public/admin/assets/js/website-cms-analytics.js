/**
 * Website CMS → Analytics (connected website).
 *
 * Edits cmsData.analytics: google_analytics, google_tag_manager, facebook_pixel,
 * microsoft_clarity (formats in App\Support\AnalyticsSettings, mirrored below) and
 * require_consent (default on: the website shows a cookie banner and loads the tools only after
 * a visitor accepts). Saved as a site draft; publishing is blocked while a filled ID is invalid.
 * Loads after website-cms-headless.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc, clone } = ext.shared;
    const MODULE = 'analytics';
    const REOPEN_KEY = 'wcan:reopen';
    const AUTOSAVE_MS = 900;
    const TOOLS = [
        {
            key: 'google_analytics', name: 'Google Analytics 4', initials: 'GA', start: true, label: 'Measurement ID', placeholder: 'G-XXXXXXXXXX',
            purpose: 'See how many people visit, where they come from and which pages they read.',
            pattern: /^G-[A-Z0-9]{6,12}$/, kase: 'upper', example: 'G-ABC123DEF4',
            help: 'In Google Analytics, go to Admin › Data streams › your website. The Measurement ID is at the top right.',
        },
        {
            key: 'google_tag_manager', name: 'Google Tag Manager', initials: 'TM', label: 'Container ID', placeholder: 'GTM-XXXXXXX',
            purpose: 'For your developer or agency to manage tracking tags without changing the website.',
            pattern: /^GTM-[A-Z0-9]{5,9}$/, kase: 'upper', example: 'GTM-ABC1234',
            help: 'It’s at the top of the Tag Manager workspace, next to the container name.',
        },
        {
            key: 'facebook_pixel', name: 'Meta Pixel', initials: 'MP', label: 'Pixel ID', placeholder: '15–16 digit number',
            purpose: 'Measure the results of your Facebook and Instagram adverts.',
            pattern: /^\d{15,16}$/, kase: null, example: '123456789012345',
            help: 'In Meta Events Manager, open Data sources and choose your pixel. The ID is under its name.',
        },
        {
            key: 'microsoft_clarity', name: 'Microsoft Clarity', initials: 'MC', label: 'Project ID', placeholder: 'e.g. abcd1234ef',
            purpose: 'Free heatmaps and recordings that show how visitors use your pages.',
            pattern: /^[a-z0-9]{8,12}$/, kase: 'lower', example: 'abcd1234ef',
            help: 'In Clarity, open your project and go to Settings › Overview. Copy the Project ID.',
        },
    ];
    const KEYS = TOOLS.map((t) => t.key);

    let api = null;
    let serverErrors = {};
    let otherSettingsPending = null;
    const save = { timer: null, inflight: null, state: 'idle', dirty: false };

    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;

    /* ───────────────────────── rules (mirror App\Support\AnalyticsSettings) ───────────────────────── */

    function normalise(tool, value) {
        const v = String(value == null ? '' : value).replace(/\s+/g, '');
        if (tool.kase === 'upper') return v.toUpperCase();
        if (tool.kase === 'lower') return v.toLowerCase();
        return v;
    }

    function status(tool) {
        const v = normalise(tool, a()[tool.key]);
        if (!v) return 'empty';
        return tool.pattern.test(v) ? 'ok' : 'bad';
    }

    /* ───────────────────────── data ───────────────────────── */

    function a() {
        const data = api.getData();
        data.analytics = data.analytics || {};
        if (data.analytics.require_consent === undefined || data.analytics.require_consent === null) data.analytics.require_consent = true;
        return data.analytics;
    }

    function snapshot(src) {
        if (!src) return null;
        const o = {};
        TOOLS.forEach((t) => { o[t.key] = normalise(t, src[t.key]); });
        o.require_consent = src.require_consent !== false;
        return JSON.stringify(o);
    }

    const matchesLive = () => W.publishedAnalytics != null && snapshot(W.publishedAnalytics) === snapshot(a());
    const invalid = () => TOOLS.some((t) => status(t) === 'bad') || Object.keys(serverErrors).length > 0;

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
        if (save.state === 'invalid') text = 'Fix the highlighted ID to save your changes.';
        else if (save.state === 'error') text = 'Your last change wasn’t saved — check your connection.';
        else if (save.dirty || save.timer || save.inflight) text = 'Saving your changes…';
        else if (live) { text = 'Your analytics settings match the live website.'; tone = 'ok'; }
        else text = 'Changes saved as a draft — publish to show them on your website.';
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = !live && W.publishedAnalytics != null;
            discard.disabled = !can;
            discard.title = can ? 'Put the analytics settings back to the live version' : '';
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
                invalid: ['Couldn’t save – fix the highlighted ID', 'error'], error: ['Couldn’t save – check your connection', 'error'],
            };
            if (map[state]) api.setStatus(map[state][0], map[state][1]);
        }
        updateSavebar();
    }

    function scheduleSave() {
        save.dirty = true;
        clearTimeout(save.timer);
        save.timer = null;
        if (invalid()) { setState('invalid'); return; }
        setState('pending');
        save.timer = setTimeout(flushSave, AUTOSAVE_MS);
    }

    function flushSave() {
        clearTimeout(save.timer);
        save.timer = null;
        if (save.inflight) return save.inflight.then(() => (save.dirty ? flushSave() : true));
        if (!save.dirty) return Promise.resolve(true);
        if (invalid()) { setState('invalid'); return Promise.resolve(false); }
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
                Object.entries(json.errors || {}).forEach(([k, m]) => { serverErrors[k.replace(/^analytics\./, '')] = m[0]; });
                save.dirty = true;
                refresh();
                setState('invalid');
                return false;
            }
            if (!r.ok) throw new Error('save failed');
            if (otherSettingsPending === null) otherSettingsPending = !!W.siteHasUnpublished;
            W.siteHasUnpublished = otherSettingsPending || !matchesLive();
            W.hasUnpublished = W.siteHasUnpublished || W.hasUnpublished;
            setState(save.dirty ? 'pending' : 'saved');
            return true;
        }).catch(() => {
            save.dirty = true;
            setState('error');
            return false;
        }).finally(() => { save.inflight = null; updateSavebar(); });
        return save.inflight;
    }

    window.addEventListener('beforeunload', (e) => {
        if (window.__wcanSkipUnloadWarning) return;
        if (save.timer || save.inflight || (save.dirty && save.state !== 'idle')) { e.preventDefault(); e.returnValue = ''; }
    });

    /* ═══════════════════════════ render ═══════════════════════════ */

    function renderAnalytics(apiArg) {
        api = apiArg;
        rememberShell();
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'Analytics';
        if (desc) { desc.textContent = 'Connect tools that show how many people visit your website and what they do. Paste the ID from each tool — no code needed.'; desc.hidden = false; }
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;
        serverErrors = {};
        const v = a();

        api.editorBody.innerHTML = `<div class="wcp wcan">
            <div class="wcn-actions"><span class="wcan-pill" id="wcanCount" aria-live="polite"></span></div>

            <section class="wcp-card wcan-consent" aria-labelledby="wcanConsentH">
                <span class="wcp-tile">${icon('solar:shield-check-linear')}</span>
                <div class="wcan-consent-text">
                    <h2 id="wcanConsentH" class="wcn-h2">Ask visitors before tracking <span class="wcp-badge-ok">Recommended in the UK</span></h2>
                    <p class="wcp-card-desc" id="wcanConsentDesc">Shows a small cookie banner. The tools below only start once a visitor agrees.</p>
                    <p class="wcp-hint" id="wcanConsentNote"></p>
                </div>
                <div class="wca-switch-wrap">
                    <button type="button" class="wca-switch" id="wcanConsent" role="switch" aria-checked="${v.require_consent !== false}" aria-labelledby="wcanConsentH wcanConsentLabel" aria-describedby="wcanConsentDesc"><span></span></button>
                    <span id="wcanConsentLabel" class="wca-switch-label">${v.require_consent !== false ? 'On' : 'Off'}</span>
                </div>
            </section>

            <p class="wcan-double" id="wcanDouble" role="status" hidden>${icon('solar:info-circle-linear')}<span>You’ve added both Google Analytics and Tag Manager. If Google Analytics is already set up inside Tag Manager, remove the ID here so visits aren’t counted twice.</span></p>

            <div class="wcan-grid">
                ${TOOLS.map((t) => `<section class="wcp-card wcan-tool" aria-labelledby="wcanH-${t.key}">
                    <div class="wcan-tool-head">
                        <span class="wcp-tile wcp-tile--navy wcan-initials" aria-hidden="true">${t.initials}</span>
                        <div class="wcan-tool-title">
                            <h2 id="wcanH-${t.key}" class="wcn-h3">${esc(t.name)}${t.start ? ' <span class="wcan-start">Start here</span>' : ''}</h2>
                            <p class="wcp-hint">${esc(t.purpose)}</p>
                        </div>
                        <span class="wcan-chip" id="wcanChip-${t.key}"></span>
                    </div>
                    <div class="wcp-field">
                        <label for="wcanId-${t.key}">${esc(t.label)}</label>
                        <input type="text" id="wcanId-${t.key}" class="wcp-input wcseo-mono" data-tool="${t.key}" value="${esc(v[t.key] || '')}" placeholder="${esc(t.placeholder)}" autocomplete="off" spellcheck="false" aria-describedby="wcanErr-${t.key}">
                        <p class="wcp-error" id="wcanErr-${t.key}" hidden></p>
                    </div>
                    <details class="wcan-help">
                        <summary>Where do I find this?</summary>
                        <p>${esc(t.help)}</p>
                    </details>
                </section>`).join('')}
            </div>
        </div>`;

        bind();
        refresh();
        if (save.state === 'idle') { updateSavebar(); api.setStatus(matchesLive() ? 'Your analytics settings match the live website' : 'Draft saved', 'ok'); } else setState(save.state);
    }

    function refresh() {
        const b = api.editorBody;
        const v = a();
        let connected = 0;
        TOOLS.forEach((t) => {
            const st = serverErrors[t.key] ? 'bad' : status(t);
            if (st === 'ok') connected++;
            const chip = b.querySelector(`#wcanChip-${t.key}`);
            chip.className = 'wcan-chip is-' + st;
            chip.textContent = st === 'ok' ? 'Connected' : st === 'bad' ? 'Check the ID' : 'Not connected';
            const err = b.querySelector(`#wcanErr-${t.key}`);
            const msg = st === 'bad' ? (serverErrors[t.key] || `It should look like ${t.example}.`) : '';
            err.hidden = !msg;
            err.innerHTML = msg ? `${icon('solar:danger-circle-linear')}<span>${esc(msg)}</span>` : '';
            b.querySelector(`#wcanId-${t.key}`).setAttribute('aria-invalid', st === 'bad' ? 'true' : 'false');
        });
        b.querySelector('#wcanCount').textContent = `${connected} of ${TOOLS.length} tools connected`;
        b.querySelector('#wcanDouble').hidden = !(status(TOOLS[0]) === 'ok' && status(TOOLS[1]) === 'ok');

        const on = v.require_consent !== false;
        b.querySelector('#wcanConsent').setAttribute('aria-checked', String(on));
        b.querySelector('#wcanConsentLabel').textContent = on ? 'On' : 'Off';
        b.querySelector('#wcanConsentNote').textContent = on
            ? (connected ? 'Please check the banner wording with whoever handles data protection at your school.' : 'The banner appears once you connect a tool below.')
            : 'The tools below start as soon as a page opens, without asking. In the UK you normally need to ask first.';
        updateSavebar();
    }

    function bind() {
        const b = api.editorBody;
        b.querySelectorAll('[data-tool]').forEach((input) => {
            const tool = TOOLS.find((t) => t.key === input.dataset.tool);
            input.addEventListener('input', () => {
                a()[tool.key] = input.value;
                delete serverErrors[tool.key];
                refresh();
                scheduleSave();
            });
            // Tidy the ID once the person leaves the field (spaces, letter case), like the server does.
            input.addEventListener('blur', () => {
                const clean = normalise(tool, input.value);
                if (clean !== input.value) { input.value = clean; a()[tool.key] = clean; refresh(); }
                if (save.dirty) flushSave();
            });
        });
        b.querySelector('#wcanConsent').addEventListener('click', () => {
            const v = a();
            v.require_consent = v.require_consent === false;
            refresh();
            scheduleSave();
            flushSave();
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
        if (W.publishedAnalytics == null || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your analytics changes?',
            message: 'The IDs and the cookie banner setting go back to the live versions.',
            note: 'Only analytics changes — other settings stay as they are.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            clearTimeout(save.timer);
            const pub = clone(W.publishedAnalytics);
            const v = a();
            KEYS.forEach((k) => { v[k] = pub[k] == null ? '' : pub[k]; });
            v.require_consent = pub.require_consent !== false;
            save.dirty = true;
            renderAnalytics(api);
            flushSave().then((done) => { if (done) api.setStatus('Changes discarded', 'ok'); });
        });
    }, true);

    let publishing = false;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmPublish');
        if (!btn || publishing || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (invalid()) {
            setState('invalid');
            api.setStatus('Can’t publish yet — fix the highlighted ID', 'error');
            const first = api.editorBody.querySelector('[aria-invalid="true"]');
            if (first) first.focus();
            return;
        }
        flushSave().then((ok) => {
            if (!ok) return;
            try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
            window.__wcanSkipUnloadWarning = window.__wcseoSkipUnloadWarning = window.__wcbSkipUnloadWarning = window.__wcsSkipUnloadWarning = window.__wccSkipUnloadWarning = window.__wcaSkipUnloadWarning = window.__wcpSkipUnloadWarning = window.__wcnSkipUnloadWarning = window.__wcfSkipUnloadWarning = true;
            publishing = true;
            btn.click();
            publishing = false;
        });
    }, true);

    ext.renderers[MODULE] = renderAnalytics;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);

    ext.analyticsModel = { TOOLS, normalise };
})();
