/**
 * Website CMS → Contact details (connected website).
 *
 * Edits cmsData.contact. The website reads `phone`, `email`, `address`, plus two GENERATED
 * values, which the server rebuilds on every save (App\Support\ContactDetails):
 *   whatsapp       https://wa.me/{digits}[?text=…]   from whatsapp_same_as_phone / _number / _message
 *   working_hours  "Mon–Fri, 8:30am–4:30pm (UK)"     from opening_days / _from / _until / _timezone
 * Until someone edits them, older values that can't be read back (null structured fields)
 * are kept exactly as they are. hoursText() / whatsappLink() below mirror the PHP.
 *
 * WhatsApp is not on every page: the website shows it on the Contact page, in the footer
 * ("WhatsApp Channel" button + icon) and as a small link in the chat assistant.
 * Loads after website-cms-headless.js / website-cms-pages.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc } = ext.shared;
    const MODULE = 'contact';
    const REOPEN_KEY = 'wcc:reopen';
    const AUTOSAVE_MS = 900;
    const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const DAY_NAMES = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const DEFAULT_DAYS = [true, true, true, true, true, false, false];
    const TIMEZONES = W.contactTimezones || ['UK'];
    const KEYS = ['phone', 'email', 'address', 'whatsapp', 'working_hours', 'whatsapp_enabled', 'whatsapp_same_as_phone', 'whatsapp_number',
        'whatsapp_message', 'opening_days', 'opening_from', 'opening_until', 'opening_timezone'];

    let api = null;
    let serverErrors = {};
    let otherSettingsPending = null;
    let legacyHours = '';   // text kept as it is until the hours are edited
    let legacyLink = '';    // WhatsApp link kept as it is until WhatsApp is edited
    let ui = null;          // what the hours / WhatsApp controls show
    let emailBlurred = false; // only show the email error once the person has left the field
    const save = { timer: null, inflight: null, state: 'idle', dirty: false };

    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;

    /* ───────────────────────── generation (mirrors App\Support\ContactDetails) ───────────────────────── */

    const digits = (v) => String(v || '').replace(/\D+/g, '');
    const rawurlencode = (s) => encodeURIComponent(s).replace(/[!'()*]/g, (c) => '%' + c.charCodeAt(0).toString(16).toUpperCase());

    function whatsappLink(number, message) {
        const d = digits(number);
        if (!d) return '';
        const m = String(message || '').trim();
        return `https://wa.me/${d}${m ? `?text=${rawurlencode(m)}` : ''}`;
    }

    function dayList(days) {
        const parts = [];
        for (let i = 0; i < 7; i++) {
            if (!days[i]) continue;
            let j = i;
            while (j + 1 < 7 && days[j + 1]) j++;
            if (j - i + 1 >= 3) parts.push(`${DAYS[i]}–${DAYS[j]}`);
            else for (let k = i; k <= j; k++) parts.push(DAYS[k]);
            i = j;
        }
        return parts.length ? parts.join(', ') : 'Closed';
    }

    function timeText(hhmm) {
        const m = /^([01]\d|2[0-3]):([0-5]\d)$/.exec(String(hhmm || ''));
        if (!m) return '';
        const h = +m[1];
        return `${h % 12 || 12}${m[2] === '00' ? '' : ':' + m[2]}${h >= 12 ? 'pm' : 'am'}`;
    }

    function hoursText(days, from, until, tz) {
        const list = dayList(days);
        return list === 'Closed' ? 'Closed' : `${list}, ${timeText(from)}–${timeText(until)} (${tz})`;
    }

    /** Rows of the website's "Office hours" table: open runs as ranges, closed days one by one. */
    function officeRows(days, from, until) {
        const rows = [];
        for (let i = 0; i < 7; i++) {
            let j = i;
            while (j + 1 < 7 && days[j + 1] === days[i]) j++;
            if (days[i]) {
                rows.push([i === j ? DAY_NAMES[i] : `${DAY_NAMES[i]} – ${DAY_NAMES[j]}`, `${timeText(from)} – ${timeText(until)}`, true]);
            } else if (j - i + 1 <= 2) {
                for (let k = i; k <= j; k++) rows.push([DAY_NAMES[k], 'Closed', false]);
            } else {
                rows.push([`${DAY_NAMES[i]} – ${DAY_NAMES[j]}`, 'Closed', false]);
            }
            i = j;
        }
        return rows;
    }

    /* ───────────────────────── data ───────────────────────── */

    function c() {
        const data = api.getData();
        data.contact = data.contact || {};
        return data.contact;
    }

    /** Apply the controls' values to the settings, then rebuild the generated fields like the server does. */
    function writeHours() {
        const v = c();
        v.opening_days = ui.days.slice();
        v.opening_from = ui.from;
        v.opening_until = ui.until;
        v.opening_timezone = ui.tz;
        v.working_hours = hoursText(ui.days, ui.from, ui.until, ui.tz);
        legacyHours = '';
    }

    function writeWhatsapp() {
        const v = c();
        v.whatsapp_enabled = ui.waOn;
        v.whatsapp_same_as_phone = ui.waSame;
        v.whatsapp_number = ui.waNumber;
        v.whatsapp_message = ui.waMessage;
        v.whatsapp = whatsappLink(ui.waSame ? v.phone : ui.waNumber, ui.waMessage);
        legacyLink = '';
    }

    function snapshot(src) {
        if (!src) return null;
        const o = {};
        ['phone', 'email', 'address', 'whatsapp', 'working_hours'].forEach((k) => { o[k] = String(src[k] == null ? '' : src[k]).trim(); });
        o.whatsapp_enabled = src.whatsapp_enabled == null ? o.whatsapp !== '' : !!src.whatsapp_enabled;
        return JSON.stringify(o);
    }

    const matchesLive = () => W.publishedContact != null && snapshot(W.publishedContact) === snapshot(c());

    /* ───────────────────────── validation ───────────────────────── */

    function errors() {
        const v = c();
        const out = {};
        const phone = String(v.phone || '').trim();
        if (phone && digits(phone).length < 7) out.phone = 'Enter a phone number with at least 7 digits, including the country code.';
        const email = String(v.email || '').trim();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) out.email = 'Please enter a full email address, like name@school.co.uk';
        if (ui.waOn && ui.waTouched) {
            if (!ui.waSame && digits(ui.waNumber).length < 7) out.whatsapp_number = 'Enter the WhatsApp number, with the country code.';
            else if (ui.waSame && digits(phone).length < 7) out.phone = out.phone || 'Add your phone number to use it for WhatsApp.';
        }
        if (ui.hoursTouched && ui.days.some(Boolean) && ui.until <= ui.from) out.opening_until = 'The closing time must be after the opening time.';
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
        else if (live) { text = 'Your contact details match the live website.'; tone = 'ok'; }
        else text = 'Changes saved as a draft — publish to show them on your website.';
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = !live && W.publishedContact != null;
            discard.disabled = !can;
            discard.title = can ? 'Put the contact details back to the live version' : '';
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
                Object.entries(json.errors || {}).forEach(([k, m]) => { serverErrors[k.replace(/^contact\./, '')] = m[0]; });
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
        if (window.__wccSkipUnloadWarning) return;
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

    function loadUi() {
        const v = c();
        const hasHours = Array.isArray(v.opening_days);
        const hasWa = v.whatsapp_same_as_phone !== null && v.whatsapp_same_as_phone !== undefined;
        legacyHours = hasHours ? '' : String(v.working_hours || '').trim();
        legacyLink = hasWa ? '' : String(v.whatsapp || '').trim();
        ui = {
            days: hasHours ? v.opening_days.map(Boolean) : DEFAULT_DAYS.slice(),
            from: hasHours && v.opening_from ? v.opening_from : '08:30',
            until: hasHours && v.opening_until ? v.opening_until : '16:30',
            tz: hasHours && TIMEZONES.includes(v.opening_timezone) ? v.opening_timezone : 'UK',
            hoursTouched: hasHours,
            waOn: v.whatsapp_enabled == null ? String(v.whatsapp || '').trim() !== '' : !!v.whatsapp_enabled,
            waSame: hasWa ? !!v.whatsapp_same_as_phone : true,
            waNumber: String(v.whatsapp_number || ''),
            waMessage: String(v.whatsapp_message || ''),
            waTouched: hasWa,
        };
    }

    function renderContact(a) {
        api = a;
        rememberShell();
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'Contact details';
        if (desc) { desc.textContent = 'How families reach you. Change them once here and they update everywhere on your website.'; desc.hidden = false; }
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;
        loadUi();
        serverErrors = {};
        emailBlurred = false;
        const v = c();

        api.editorBody.innerHTML = `<div class="wcp wcc">
            <div class="wcc-grid">
                <section class="wcp-card" aria-labelledby="wccPhoneH">
                    <h2 id="wccPhoneH" class="wcn-h2">Phone and email</h2>
                    <p class="wcp-card-desc">Visitors can tap these to call or email you.</p>
                    <div class="wcp-field">
                        <label for="wccPhone">Phone number</label>
                        <div class="wcc-inline">
                            <input type="tel" id="wccPhone" class="wcp-input" value="${esc(v.phone || '')}" autocomplete="off" aria-describedby="wccPhoneHint wccErr-phone">
                            <a class="wcp-btn wcp-btn--outline" id="wccPhoneTest" href="#">Test<span class="visually-hidden"> by calling this number</span></a>
                        </div>
                        <p class="wcp-hint" id="wccPhoneHint">Include the country code, e.g. +44 20 3633 0757.</p>
                        <p class="wcp-error" id="wccErr-phone" hidden></p>
                    </div>
                    <div class="wcp-field">
                        <label for="wccEmail">Email address</label>
                        <input type="email" id="wccEmail" class="wcp-input" value="${esc(v.email || '')}" autocomplete="off" aria-describedby="wccEmailCheck wccErr-email">
                        <p class="wca-check" id="wccEmailCheck" aria-live="polite"></p>
                        <p class="wcp-error" id="wccErr-email" hidden></p>
                    </div>
                </section>

                <section class="wcp-card" aria-labelledby="wccWaH">
                    <div class="wca-card-head">
                        <div>
                            <h2 id="wccWaH" class="wcn-h2">WhatsApp</h2>
                            <p class="wcp-card-desc">On your Contact page, in the footer and in the chat assistant.</p>
                        </div>
                        <div class="wca-switch-wrap">
                            <button type="button" class="wca-switch" id="wccWaSwitch" role="switch" aria-checked="${ui.waOn}" aria-labelledby="wccWaH wccWaSwitchLabel"><span></span></button>
                            <span id="wccWaSwitchLabel" class="wca-switch-label">${ui.waOn ? 'Showing' : 'Hidden'}</span>
                        </div>
                    </div>
                    <p class="wca-off-note" id="wccWaOff" hidden>${icon('solar:eye-closed-linear')}WhatsApp is hidden on your website. Switch it on to let parents message you.</p>
                    <div id="wccWaOn">
                        <p class="wcc-legacy" id="wccWaLegacy" hidden></p>
                        <label class="wcc-check-row"><input type="checkbox" id="wccWaSame" ${ui.waSame ? 'checked' : ''}> Use the same number as my phone</label>
                        <div class="wcp-field" id="wccWaNumberField" hidden>
                            <label for="wccWaNumber">WhatsApp number</label>
                            <input type="tel" id="wccWaNumber" class="wcp-input" value="${esc(ui.waNumber)}" placeholder="+44 7700 900123" autocomplete="off" aria-describedby="wccErr-whatsapp_number">
                            <p class="wcp-error" id="wccErr-whatsapp_number" hidden></p>
                        </div>
                        <div class="wcp-field">
                            <label for="wccWaMessage">First message <span class="wcp-muted">(optional)</span></label>
                            <input type="text" id="wccWaMessage" class="wcp-input" value="${esc(ui.waMessage)}" maxlength="300" placeholder="e.g. Hello, I’d like to ask about admissions" autocomplete="off" aria-describedby="wccWaMessageHint wccErr-whatsapp_message">
                            <p class="wcp-hint" id="wccWaMessageHint">Typed in for the parent, so they only need to press send.</p>
                            <p class="wcp-error" id="wccErr-whatsapp_message" hidden></p>
                        </div>
                        <div class="wcc-linkbox">
                            <code id="wccWaLink" aria-label="WhatsApp link"></code>
                            <a class="wcp-btn wcp-btn--outline wcp-btn--sm" id="wccWaTest" href="#" target="_blank" rel="noopener">Test<span class="visually-hidden"> the WhatsApp link (opens in a new tab)</span></a>
                        </div>
                    </div>
                </section>

                <section class="wcp-card" aria-labelledby="wccAddrH">
                    <h2 id="wccAddrH" class="wcn-h2">Address</h2>
                    <p class="wcp-card-desc">Your registered school address.</p>
                    <div class="wcp-field">
                        <label for="wccAddress">Address</label>
                        <textarea id="wccAddress" class="wcp-input wcp-textarea" rows="3" aria-describedby="wccAddressHint">${esc(v.address || '')}</textarea>
                        <p class="wcp-hint" id="wccAddressHint">Put each part on its own line. The website shows it as one line with commas, and the last line (town and postcode) under the street on the Contact page.</p>
                    </div>
                    <a class="wcp-btn wcp-btn--outline wcc-maps" id="wccMaps" href="#" target="_blank" rel="noopener">${icon('solar:map-point-linear')}Check on Google Maps<span class="visually-hidden"> (opens in a new tab)</span></a>
                </section>

                <section class="wcp-card" aria-labelledby="wccHoursH">
                    <h2 id="wccHoursH" class="wcn-h2">Opening hours</h2>
                    <p class="wcp-card-desc">When parents can phone or visit the office.</p>
                    <p class="wcc-legacy" id="wccHoursLegacy" hidden></p>
                    <fieldset class="wcc-days">
                        <legend>Open on</legend>
                        <div class="wcc-day-row">${DAYS.map((d, i) => `<button type="button" class="wcc-day" data-day="${i}" aria-pressed="${ui.days[i]}" aria-label="${DAY_NAMES[i]}">${d}</button>`).join('')}</div>
                    </fieldset>
                    <div class="wcc-times">
                        <div class="wcp-field">
                            <label for="wccFrom">From</label>
                            <input type="time" id="wccFrom" class="wcp-input" value="${esc(ui.from)}">
                        </div>
                        <div class="wcp-field">
                            <label for="wccUntil">Until</label>
                            <input type="time" id="wccUntil" class="wcp-input" value="${esc(ui.until)}" aria-describedby="wccErr-opening_until">
                        </div>
                        <div class="wcp-field">
                            <label for="wccTz">Time zone</label>
                            <select id="wccTz" class="wcp-input">${TIMEZONES.map((t) => `<option value="${esc(t)}" ${t === ui.tz ? 'selected' : ''}>${esc(t)} time</option>`).join('')}</select>
                        </div>
                    </div>
                    <p class="wcp-error" id="wccErr-opening_until" hidden></p>
                    <p class="wcc-shown" id="wccShown" aria-live="polite"></p>
                </section>
            </div>
        </div>`;

        bind();
        renderPreview();
        refresh();
        showErrors();
        if (save.state === 'idle') { updateSavebar(); api.setStatus(matchesLive() ? 'Your contact details match the live website' : 'Draft saved', 'ok'); } else setState(save.state);
    }

    function currentHoursText() {
        return legacyHours || hoursText(ui.days, ui.from, ui.until, ui.tz);
    }

    function currentWhatsapp() {
        if (!ui.waOn) return '';
        return legacyLink || whatsappLink(ui.waSame ? c().phone : ui.waNumber, ui.waMessage);
    }

    function refresh() {
        const b = api.editorBody;
        const q = (s) => b.querySelector(s);
        const v = c();

        const phone = String(v.phone || '').trim();
        q('#wccPhoneTest').href = phone ? 'tel:' + phone.replace(/[^\d+]/g, '') : '#';
        q('#wccPhoneTest').classList.toggle('is-disabled', digits(phone).length < 7);
        const email = String(v.email || '').trim();
        const check = q('#wccEmailCheck');
        if (!email) { check.className = 'wca-check'; check.textContent = ''; }
        else if (/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) { check.className = 'wca-check is-ok'; check.innerHTML = `${icon('solar:check-read-linear')}Looks good`; }
        else { check.className = 'wca-check'; check.textContent = ''; }

        q('#wccWaSwitch').setAttribute('aria-checked', String(ui.waOn));
        q('#wccWaSwitchLabel').textContent = ui.waOn ? 'Showing' : 'Hidden';
        q('#wccWaOff').hidden = ui.waOn;
        q('#wccWaOn').hidden = !ui.waOn;
        q('#wccWaNumberField').hidden = ui.waSame;
        const legacyWa = q('#wccWaLegacy');
        legacyWa.hidden = !legacyLink;
        if (legacyLink) legacyWa.innerHTML = `${icon('solar:info-circle-linear')}<span>Your website uses this WhatsApp link: <code>${esc(legacyLink)}</code>. It stays as it is until you change the settings below.</span>`;
        const link = currentWhatsapp();
        q('#wccWaLink').textContent = link || 'Add a number to create the link';
        q('#wccWaTest').href = link || '#';
        q('#wccWaTest').classList.toggle('is-disabled', !link);

        const addr = String(v.address || '').trim();
        q('#wccMaps').href = addr ? 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(addr.replace(/\s*\n\s*/g, ', ')) : '#';
        q('#wccMaps').classList.toggle('is-disabled', !addr);

        b.querySelectorAll('.wcc-day').forEach((d) => d.setAttribute('aria-pressed', String(ui.days[+d.dataset.day])));
        const legacyH = q('#wccHoursLegacy');
        legacyH.hidden = !legacyHours;
        if (legacyHours) legacyH.innerHTML = `${icon('solar:info-circle-linear')}<span>Your website currently shows “${esc(legacyHours)}”. It stays exactly as it is until you change the hours below.</span>`;
        q('#wccShown').innerHTML = `Shown on your website as: <strong>${esc(currentHoursText())}</strong>`;
        updateSavebar();
    }

    function showErrors() {
        const errs = errors();
        ['phone', 'email', 'whatsapp_number', 'whatsapp_message', 'opening_until'].forEach((k) => {
            const box = api.editorBody.querySelector(`#wccErr-${k}`);
            if (!box) return;
            const msg = k === 'email' && !emailBlurred && !serverErrors.email ? '' : errs[k];
            box.hidden = !msg;
            box.innerHTML = msg ? `${icon('solar:danger-circle-linear')}<span>${esc(msg)}</span>` : '';
            const input = api.editorBody.querySelector(`[aria-describedby~="wccErr-${k}"]`);
            if (input) input.setAttribute('aria-invalid', msg ? 'true' : 'false');
        });
    }

    function bind() {
        const b = api.editorBody;
        const q = (s) => b.querySelector(s);
        const flushOnBlur = (el) => el.addEventListener('blur', () => { if (save.dirty) flushSave(); });
        const touchWa = () => { ui.waTouched = true; writeWhatsapp(); };
        const touchHours = () => { ui.hoursTouched = true; writeHours(); };

        q('#wccPhone').addEventListener('input', (e) => {
            c().phone = e.target.value;
            if (ui.waTouched && ui.waSame) writeWhatsapp(); // the WhatsApp link follows the phone
            changed('phone');
        });
        q('#wccEmail').addEventListener('input', (e) => { c().email = e.target.value; changed('email'); });
        q('#wccEmail').addEventListener('blur', () => { emailBlurred = true; showErrors(); });
        q('#wccAddress').addEventListener('input', (e) => { c().address = e.target.value; changed('address'); });

        q('#wccWaSwitch').addEventListener('click', () => { ui.waOn = !ui.waOn; touchWa(); changed('whatsapp_number'); });
        q('#wccWaSame').addEventListener('change', (e) => {
            ui.waSame = e.target.checked;
            touchWa();
            changed('whatsapp_number');
            if (!ui.waSame) q('#wccWaNumber').focus();
        });
        q('#wccWaNumber').addEventListener('input', (e) => { ui.waNumber = e.target.value; touchWa(); changed('whatsapp_number'); });
        q('#wccWaMessage').addEventListener('input', (e) => { ui.waMessage = e.target.value; touchWa(); changed('whatsapp_message'); });

        b.querySelectorAll('.wcc-day').forEach((d) => d.addEventListener('click', () => {
            const i = +d.dataset.day;
            ui.days[i] = !ui.days[i];
            touchHours();
            changed('opening_until');
        }));
        q('#wccFrom').addEventListener('change', (e) => { ui.from = e.target.value; touchHours(); changed('opening_until'); });
        q('#wccUntil').addEventListener('change', (e) => { ui.until = e.target.value; touchHours(); changed('opening_until'); });
        q('#wccTz').addEventListener('change', (e) => { ui.tz = e.target.value; touchHours(); changed('opening_timezone'); });

        ['#wccPhoneTest', '#wccWaTest', '#wccMaps'].forEach((s) => q(s).addEventListener('click', (e) => {
            if (e.currentTarget.classList.contains('is-disabled')) e.preventDefault();
        }));
        ['#wccPhone', '#wccEmail', '#wccAddress', '#wccWaNumber', '#wccWaMessage', '#wccFrom', '#wccUntil'].forEach((s) => flushOnBlur(q(s)));
    }

    /* ── previews: the three places these details appear ── */
    function renderPreview() {
        const box = api.editorBody.querySelector('#wccPreview');
        if (!box) return; // the live preview panel shows these now
        if (!box) return;
        const v = c();
        const theme = api.getData().theme || {};
        const phone = String(v.phone || '').trim();
        const email = String(v.email || '').trim();
        const addressLine = String(v.address || '').trim().replace(/\s*\n\s*/g, ', ');
        const comma = addressLine.lastIndexOf(',');
        const street = comma > 0 ? addressLine.slice(0, comma).trim() : addressLine;
        const rest = comma > 0 ? addressLine.slice(comma + 1).trim() : '';
        const hours = currentHoursText();
        const wa = currentWhatsapp();
        const tz = legacyHours ? '' : ui.tz;
        const or = (x) => (x ? esc(x) : '<em>Website default</em>');
        const row = (ic, label, value, sub) => `<li><span class="wcc-pv-icon">${icon(ic)}</span><span><span class="wcc-pv-label">${esc(label)}</span><span class="wcc-pv-value">${value}</span>${sub ? `<span class="wcc-pv-sub">${sub}</span>` : ''}</span></li>`;
        const table = legacyHours ? '' : `<div class="wcc-pv-hours"><p>${icon('solar:clock-circle-linear')}Office hours (${esc(tz)} time)</p><dl>${officeRows(ui.days, ui.from, ui.until).map(([d, t, on]) => `<div><dt>${esc(d)}</dt><dd class="${on ? 'is-open' : ''}">${esc(t)}</dd></div>`).join('')}</dl></div>`;

        box.innerHTML = `
            <figure class="wcc-pv">
                <figcaption>Contact page</figcaption>
                <div class="wcc-pv-contact">
                    <ul>
                        ${row('solar:phone-linear', 'Call admissions', or(phone), esc(hours))}
                        ${wa ? row('mdi:whatsapp', 'WhatsApp', 'Chat with us', 'Quick replies in office hours') : ''}
                        ${row('solar:letter-linear', 'Email', or(email), '')}
                        ${row('solar:map-point-linear', 'Office', or(street), esc(rest))}
                    </ul>
                    ${table}
                </div>
            </figure>
            <figure class="wcc-pv">
                <figcaption>Footer</figcaption>
                <div class="wcc-pv-footer" style="background:${esc(theme.footer || '#0A1B33')}">
                    <p>${icon('solar:map-point-linear')}<span>${or(addressLine)}</span></p>
                    <p>${icon('solar:phone-linear')}<span>${or(phone)}</span></p>
                    <p>${icon('solar:letter-linear')}<span>${or(email)}</span></p>
                    ${wa ? `<span class="wcc-pv-wa-btn">${icon('mdi:whatsapp')}WhatsApp Channel</span>` : ''}
                    <p class="wcc-pv-note">Opening hours aren’t shown in the footer.</p>
                </div>
            </figure>
            <figure class="wcc-pv">
                <figcaption>Chat assistant</figcaption>
                <div class="wcc-pv-chat">
                    <div class="wcc-pv-bubble">Hi! How can I help you today?</div>
                    <div class="wcc-pv-chatfoot">
                        <span>AI can make mistakes. Check important info.</span>
                        ${wa ? `<span class="wcc-pv-wa-link">${icon('mdi:whatsapp')}WhatsApp us</span>` : ''}
                    </div>
                </div>
            </figure>
            ${wa ? '' : '<p class="wcc-pv-off">WhatsApp is switched off, so it’s hidden in all three places.</p>'}`;
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
        if (W.publishedContact == null || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your contact changes?',
            message: 'Phone, email, WhatsApp, address and opening hours go back to the versions live on your website.',
            note: 'Only contact details change — other settings stay as they are.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            clearTimeout(save.timer);
            const v = c();
            const pub = W.publishedContact;
            KEYS.forEach((k) => { v[k] = pub[k] === undefined ? null : JSON.parse(JSON.stringify(pub[k])); });
            save.dirty = true;
            renderContact(api);
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
            emailBlurred = true;
            showErrors();
            const first = api.editorBody.querySelector('.wcp-error:not([hidden])');
            const input = first && api.editorBody.querySelector(`[aria-describedby~="${first.id}"]`);
            if (input) input.focus();
            return;
        }
        flushSave().then((ok) => {
            if (!ok) return;
            try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
            window.__wccSkipUnloadWarning = window.__wcaSkipUnloadWarning = window.__wcpSkipUnloadWarning = window.__wcnSkipUnloadWarning = window.__wcfSkipUnloadWarning = true;
            publishing = true;
            btn.click();
            publishing = false;
        });
    }, true);

    ext.renderers[MODULE] = renderContact;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);

    ext.contactModel = { hoursText, whatsappLink, officeRows, dayList, timeText };
})();
