/**
 * Website CMS → Website forms (connected website).
 *
 * The website has four built-in forms (Apply Now, Contact, Review, Newsletter). Each posts its
 * answers to the Form Center form chosen here — stored as cmsData.website.forms[slot] = slug,
 * exactly as before; empty means the website uses its standard "Website: …" form.
 *
 * The status of each card comes from the server (HeadlessWebsiteService::websiteFormStatus),
 * which compares the chosen form's real field keys with the keys the website sends.
 * Loads after website-cms-headless.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc } = ext.shared;
    const MODULE = 'site_forms';
    const REOPEN_KEY = 'wcfm:reopen';
    const SLOTS = [
        { key: 'apply', title: 'Apply Now form', purpose: 'Families apply for a place at the school.', shown: [['/apply-now', true]] },
        { key: 'contact', title: 'Contact form', purpose: 'Questions from parents and visitors.', shown: [['/contact', true]] },
        { key: 'review', title: 'Review form', purpose: 'Parents leave a review of the school.', shown: [['/reviews', true]] },
        { key: 'newsletter', title: 'Newsletter sign-up', purpose: 'Visitors join your mailing list.', shown: [['Blog', false], ['Footer', false]] },
    ];
    const BLOCKING = ['not_connected', 'rejected'];

    let api = null;
    let status = H.formStatus || {};
    let checking = null;
    let otherSettingsPending = null;
    const save = { inflight: null, state: 'idle' };

    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;
    const forms = () => H.forms || [];

    function assigned() {
        const data = api.getData();
        data.website = data.website || {};
        data.website.forms = data.website.forms || {};
        return data.website.forms;
    }

    const pick = (src) => SLOTS.reduce((o, s) => ({ ...o, [s.key]: String((src || {})[s.key] || '').trim() }), {});
    const matchesLive = () => W.publishedWebsiteForms !== null && W.publishedWebsiteForms !== undefined
        && JSON.stringify(pick(W.publishedWebsiteForms)) === JSON.stringify(pick(assigned()));

    /* ───────────────────────── server status check ───────────────────────── */

    function recheck() {
        const body = JSON.stringify({ forms: pick(assigned()) });
        const req = checking = fetch(H.routes.formStatus, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' },
            body,
        }).then((r) => (r.ok ? r.json() : Promise.reject(new Error('check failed'))))
            .then((res) => {
                if (req !== checking) return; // a newer check is on its way
                status = res.forms || {};
                SLOTS.forEach((s) => renderStatus(s.key));
                updateSavebar();
            })
            .catch(() => { if (req === checking) SLOTS.forEach((s) => renderStatus(s.key, true)); })
            .finally(() => { if (req === checking) checking = null; });
        SLOTS.forEach((s) => renderStatus(s.key, false, true));
        return req;
    }

    /* ───────────────────────── save / status / shell ───────────────────────── */

    function saveDraft() {
        api.setStatus('Saving…', 'saving');
        save.state = 'saving';
        updateSavebar();
        const req = save.inflight = fetch(W.routes.draft, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' },
            body: JSON.stringify({ data: api.getData() }),
        }).then((r) => {
            if (!r.ok) throw new Error('save failed');
            if (otherSettingsPending === null) otherSettingsPending = !!W.siteHasUnpublished;
            W.siteHasUnpublished = otherSettingsPending || !matchesLive();
            W.hasUnpublished = W.siteHasUnpublished || W.hasUnpublished;
            save.state = 'saved';
            api.setStatus('Form choice saved as a draft just now', 'ok');
            return true;
        }).catch(() => {
            save.state = 'error';
            api.setStatus('Couldn’t save – check your connection and try again', 'error');
            return false;
        }).finally(() => { if (save.inflight === req) save.inflight = null; updateSavebar(); });
        return req;
    }

    function problems() {
        return SLOTS.filter((s) => status[s.key] && status[s.key].status !== 'connected');
    }

    function updateSavebar() {
        if (activeModule() !== MODULE) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        const saveDraftBtn = document.getElementById('wcmSaveDraft');
        const discard = document.getElementById('wcmDiscard');
        if (saveDraftBtn) saveDraftBtn.style.setProperty('display', 'none', 'important'); // saved on change
        const live = matchesLive();
        const issues = problems().length;
        let text;
        let tone = 'warn';
        if (save.state === 'saving') text = 'Saving your form choice…';
        else if (save.state === 'error') text = 'Your last change wasn’t saved — try again.';
        else if (!live) text = 'Form changes saved as a draft — publish to apply them on your website.';
        else if (issues) text = `Your forms match the live website, but ${issues} need${issues === 1 ? 's' : ''} attention.`;
        else { text = 'All four forms are connected and live.'; tone = 'ok'; }
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = !live && W.publishedWebsiteForms != null;
            discard.disabled = !can;
            discard.title = can ? 'Put the form choices back to the ones that are live on your website' : '';
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

    /* ═══════════════════════════ render ═══════════════════════════ */

    function options(slot) {
        const chosen = assigned()[slot] || '';
        const st = status[slot] || {};
        const rec = st.recommended;
        const empty = rec ? `Standard form (${rec.name})` : 'Not chosen';
        let html = `<option value="" ${!chosen ? 'selected' : ''}>${esc(empty)}</option>`;
        let found = !chosen;
        forms().forEach((f) => {
            if (f.slug === chosen) found = true;
            html += `<option value="${esc(f.slug)}" ${f.slug === chosen ? 'selected' : ''}>${esc(f.name)}</option>`;
        });
        if (!found) html += `<option value="${esc(chosen)}" selected>${esc(chosen)} (missing or switched off)</option>`;
        return html;
    }

    function renderForms(a) {
        api = a;
        rememberShell();
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'Website forms';
        if (desc) { desc.textContent = 'Your website has four built-in forms. Choose where each one sends its answers.'; desc.hidden = false; }
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;

        api.editorBody.innerHTML = `<div class="wcp wcfm">
            <div class="wcn-actions">
                <a class="wcp-btn wcp-btn--outline" href="${esc(W.routes.formCenter)}" target="_blank" rel="noopener">${icon('solar:square-arrow-right-up-linear')}Open Form Center<span class="visually-hidden"> (opens in a new tab)</span></a>
            </div>

            <section class="wcp-card wcfm-how" aria-labelledby="wcfmHowH">
                <h2 id="wcfmHowH" class="wcn-eyebrow">How it works</h2>
                <ol class="wcfm-steps">
                    <li><span class="wcp-tile">${icon('solar:monitor-linear')}</span><span><strong>1. A visitor fills in a form</strong><span>on your website</span></span></li>
                    <li class="wcfm-arrow" aria-hidden="true">${icon('solar:arrow-right-linear')}</li>
                    <li><span class="wcp-tile wcp-tile--gold">${icon('solar:document-text-linear')}</span><span><strong>2. Answers are saved</strong><span>in the Form Center form you pick</span></span></li>
                    <li class="wcfm-arrow" aria-hidden="true">${icon('solar:arrow-right-linear')}</li>
                    <li><span class="wcp-tile wcfm-tile-green">${icon('solar:user-check-linear')}</span><span><strong>3. A new lead appears</strong><span>in your CRM, ready to follow up</span></span></li>
                </ol>
            </section>

            <div class="wcfm-grid">
                ${SLOTS.map((s) => `<section class="wcp-card wcfm-card" aria-labelledby="wcfmH-${s.key}" data-slot="${s.key}">
                    <div class="wcfm-card-head">
                        <span class="wcp-tile wcp-tile--navy wcfm-tile">${icon('solar:document-text-linear')}</span>
                        <div>
                            <h2 id="wcfmH-${s.key}" class="wcfm-title">${esc(s.title)}</h2>
                            <p class="wcfm-purpose">${esc(s.purpose)}</p>
                            <p class="wcfm-shown"><span>Shown on</span>${s.shown.map(([t, mono]) => `<span class="wcfm-chip ${mono ? 'is-mono' : ''}">${esc(t)}</span>`).join('')}</p>
                        </div>
                    </div>
                    <div class="wcp-field">
                        <label for="wcfmSel-${s.key}">Send answers to this Form Center form</label>
                        <select id="wcfmSel-${s.key}" class="wcp-input" data-slot-select="${s.key}" aria-describedby="wcfmStatus-${s.key}">${options(s.key)}</select>
                    </div>
                    <div class="wcfm-status" id="wcfmStatus-${s.key}" aria-live="polite"></div>
                    <div class="wcfm-actions" id="wcfmActions-${s.key}"></div>
                </section>`).join('')}
            </div>

            <div class="wcfm-tip">
                <span class="wcp-tile wcp-tile--gold">${icon('solar:add-circle-linear')}</span>
                <div><strong>Need a form on another page?</strong><span>Create or edit a custom page and add a Form block to it.</span></div>
                <button type="button" class="wcp-btn wcp-btn--outline" id="wcfmToPages">Go to Pages${icon('solar:arrow-right-linear')}</button>
            </div>
        </div>`;

        const b = api.editorBody;
        b.querySelectorAll('[data-slot-select]').forEach((sel) => sel.addEventListener('change', () => {
            assigned()[sel.dataset.slotSelect] = sel.value;
            recheck();
            saveDraft();
        }));
        b.querySelector('.wcfm-grid').addEventListener('click', (e) => {
            const use = e.target.closest('[data-use-recommended]');
            if (!use) return;
            const slot = use.dataset.useRecommended;
            assigned()[slot] = use.dataset.slug;
            const sel = b.querySelector(`#wcfmSel-${slot}`);
            sel.innerHTML = options(slot);
            sel.value = use.dataset.slug;
            sel.focus();
            recheck();
            saveDraft();
        });
        b.querySelector('#wcfmToPages').addEventListener('click', () => { restoreShell(); api.switchModule('pages'); });

        SLOTS.forEach((s) => renderStatus(s.key));
        updateSavebar();
        if (save.state === 'idle') api.setStatus(matchesLive() ? 'Your form choices match the live website' : 'Form choices saved as a draft', 'ok');
        recheck(); // forms may have been edited in Form Center since the page loaded
    }

    function renderStatus(slot, failed, pending) {
        if (!api) return;
        const box = api.editorBody.querySelector(`#wcfmStatus-${slot}`);
        const actions = api.editorBody.querySelector(`#wcfmActions-${slot}`);
        if (!box) return;
        const st = status[slot];
        if (failed) {
            box.className = 'wcfm-status is-warn';
            box.innerHTML = `${icon('solar:danger-triangle-linear')}<span>Couldn’t check this form just now. Reload the page to try again.</span>`;
            return;
        }
        if (pending || !st) {
            box.className = 'wcfm-status is-checking';
            box.innerHTML = `<span class="wcp-spinner wcfm-spin" aria-hidden="true"></span><span>Checking the fields…</span>`;
            return;
        }

        const rec = st.recommended;
        const useRec = rec && (!st.form || st.form.slug !== rec.slug)
            ? `<button type="button" class="wcfm-link" data-use-recommended="${slot}" data-slug="${esc(rec.slug)}">Use the recommended form</button>` : '';
        const auto = st.automatic ? ' <span class="wcfm-auto">— using the standard form automatically</span>' : '';
        const list = (a) => a.map((x) => `“${esc(x)}”`).join(', ');

        if (st.status === 'connected') {
            box.className = 'wcfm-status is-ok';
            box.innerHTML = `${icon('solar:check-circle-linear')}<span>Connected — all the fields match${auto}</span>`;
        } else if (st.status === 'missing') {
            box.className = 'wcfm-status is-warn';
            box.innerHTML = `${icon('solar:danger-triangle-linear')}<span>This form is missing: ${list(st.missing)}. Those answers would be lost.${auto} ${useRec}</span>`;
        } else if (st.status === 'rejected') {
            box.className = 'wcfm-status is-error';
            box.innerHTML = `${icon('solar:danger-circle-linear')}<span>Every submission would be rejected: this form requires ${list(st.rejecting)}, which the website form doesn’t ask for. ${useRec}</span>`;
        } else {
            box.className = 'wcfm-status is-warn';
            box.innerHTML = `${icon('solar:link-broken-linear')}<span>${st.chosen
                ? 'Not connected — the chosen form no longer exists or is switched off, so submissions won’t be saved.'
                : 'Not connected — submissions won’t be saved.'} ${useRec}</span>`;
        }

        if (actions) {
            const id = st.form ? st.form.id : null;
            actions.innerHTML = id
                ? `<a class="wcp-btn wcp-btn--outline wcp-btn--sm" href="${esc(H.routes.formAnswers.replace('__FORM__', id))}">${icon('solar:list-linear')}View answers</a>
                   <a class="wcp-btn wcp-btn--ghost wcp-btn--sm" href="${esc(H.routes.formEdit.replace('__FORM__', id))}" target="_blank" rel="noopener">${icon('solar:pen-2-linear')}Edit fields in Form Center<span class="visually-hidden"> (opens in a new tab)</span></a>`
                : '<span class="wcp-hint">Choose a form to see its answers.</span>';
        }
    }

    /* ═══════════════════════════ shell integration ═══════════════════════════ */

    document.addEventListener('click', (e) => {
        const item = e.target.closest('.wcm-nav-item');
        if (!item || item.dataset.module === MODULE || activeModule() !== MODULE) return;
        restoreShell();
    }, true);

    // "Discard" here puts back only the form choices.
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmDiscard');
        if (!btn || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (W.publishedWebsiteForms == null || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your form changes?',
            message: 'Each website form goes back to the Form Center form that is live now.',
            note: 'Only the form choices change — nothing in Form Center is touched.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            const forms = assigned();
            SLOTS.forEach((s) => { forms[s.key] = String((W.publishedWebsiteForms || {})[s.key] || ''); });
            renderForms(api);
            saveDraft().then((done) => { if (done) api.setStatus('Form changes discarded', 'ok'); });
        });
    }, true);

    // "Publish": never publish a form that can't save answers; confirm when answers would be lost.
    let publishing = false;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmPublish');
        if (!btn || publishing || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        Promise.all([save.inflight, checking]).then(() => {
            const blocking = SLOTS.filter((s) => status[s.key] && BLOCKING.includes(status[s.key].status));
            if (blocking.length) {
                api.setStatus(`Can’t publish yet — ${blocking.map((s) => s.title).join(', ')} can’t save answers`, 'error');
                const sel = api.editorBody.querySelector(`#wcfmSel-${blocking[0].key}`);
                if (sel) { sel.closest('.wcfm-card').scrollIntoView({ block: 'nearest' }); sel.focus(); }
                return;
            }
            const lossy = SLOTS.filter((s) => status[s.key] && status[s.key].status === 'missing');
            const go = () => {
                try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
                window.__wcpSkipUnloadWarning = window.__wcnSkipUnloadWarning = window.__wcfSkipUnloadWarning = true;
                publishing = true;
                btn.click();
                publishing = false;
            };
            if (!lossy.length) return go();
            const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title + '\n\n' + o.message));
            confirmFn({
                title: 'Publish with missing fields?',
                message: lossy.map((s) => `${s.title}: ${status[s.key].missing.join(', ')}`).join(' · '),
                note: 'Those answers won’t be stored until the fields are added in Form Center.',
                label: 'Publish anyway',
                tone: 'warning',
            }).then((ok) => { if (ok) go(); });
        });
    }, true);

    ext.renderers[MODULE] = renderForms;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);
})();
