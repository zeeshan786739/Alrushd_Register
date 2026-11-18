/**
 * Website CMS → AI assistant (connected website).
 *
 * 1. "Ask AI" button + right-hand drawer on every CMS screen. The server runs the model and its
 *    tools (App\Services\AiAssistant); every change is validated and saved as a DRAFT exactly
 *    like the forms. Each reply that changed something gets a "Changes" card with Undo and
 *    Show in preview. Nothing is ever published from here.
 * 2. Website CMS → Advanced → AI Assistant (admins with "manage ai assistant"): on/off, API key
 *    (kept on the server, only its last 4 characters are shown), model, base URL, monthly limit.
 *
 * Loads after the other website-cms-*.js modules and before website-cms.js.
 */
(function () {
    'use strict';
    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    const A = W && W.assistant;
    if (!W || !H.enabled || !ext || !ext.shared || !A) return;
    const { esc } = ext.shared;
    const MODULE = 'ai_assistant';
    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;
    const shell = () => (ext.api ? ext.api() : null);

    const UNAVAILABLE = 'The AI service isn’t responding — your site wasn’t changed. Try again.';

    const EXAMPLES = {
        nav_menu: ['Add Fees to the menu under About', 'Move Contact to the end of the menu', 'Change the header button to “Apply today”'],
        footer_menu: ['Change the footer heading to “Learning without limits”', 'Add a Privacy policy link to the footer', 'Make the footer text shorter'],
        site_forms: ['Which form receives the website’s Contact form?', 'Which forms can I choose from?', 'Use the same form for Apply and Contact'],
        website: ['Show an announcement: “Admissions are open for September”', 'Turn the announcement off', 'Change the portal button to “Parent login”'],
        contact: ['Change our phone number to ', 'Set office hours to Monday–Friday, 9:00–17:00', 'Turn WhatsApp off'],
        social: ['Hide the Twitter icon', 'Add our YouTube channel: ', 'Which social icons are shown?'],
        branding: ['Change the tagline to ', 'Turn off the automatic year in the copyright', 'What is our short name?'],
        theme: ['What colours does the website use?', 'Make the accent colour #C5A86D', 'Make the footer a darker navy'],
        seo: ['Write a better default search description', 'Change the default search title to ', 'Is the website hidden from Google?'],
        analytics: ['Is cookie consent required before analytics?', 'Add Google Analytics ID ', 'Which analytics tools are set up?'],
        custom_code: ['Which custom code blocks are switched on?', 'Turn the footer code off', 'Turn the custom styles on'],
        pages: ['Which pages are hidden from Google?', 'Hide the Fees page', 'Change the Contact page’s search title to '],
        _: ['Change the footer heading to “Learning without limits”', 'Hide the Twitter icon', 'Add Fees to the menu under About'],
    };

    const FIELD_LABELS = {
        cta_text: 'Header button text', cta_url: 'Header button link', items: 'Links', heading: 'Heading', description: 'Text',
        announcement: 'Message', announcement_enabled: 'Announcement', announcement_link_text: 'Link text', announcement_url: 'Link',
        announcement_style: 'Colour', announcement_starts_at: 'Starts', announcement_ends_at: 'Ends', portal_url: 'Portal link', portal_button_text: 'Portal button',
        whatsapp_enabled: 'WhatsApp', whatsapp_same_as_phone: 'WhatsApp uses phone number', whatsapp_number: 'WhatsApp number', whatsapp_message: 'WhatsApp first message',
        opening_days: 'Open days', opening_from: 'Opens', opening_until: 'Closes', opening_timezone: 'Time zone',
        whatsapp_use_contact: 'WhatsApp icon uses contact number', company_name: 'School name', short_name: 'Short name',
        website_description: 'Description', copyright_auto_year: 'Automatic year', meta_title: 'Search title', meta_description: 'Search description',
        meta_keywords: 'Keywords', og_image: 'Sharing image', twitter_card: 'Picture size', canonical_url: 'Website address', google_verification: 'Google verification',
        seo_title: 'Search title', seo_description: 'Search description', seo_noindex: 'Hidden from Google', hidden: 'Hidden',
        custom_css_enabled: 'Custom styles', head_code_enabled: 'Head code', footer_code_enabled: 'Footer code', require_consent: 'Wait for cookie consent',
        apply: 'Apply form', contact: 'Contact form', review: 'Review form', newsletter: 'Newsletter form',
    };
    const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    function fieldLabel(field) {
        if (FIELD_LABELS[field]) return FIELD_LABELS[field];
        const m = /^enabled\.(\w+)$/.exec(field);
        if (m) return `${m[1].charAt(0).toUpperCase()}${m[1].slice(1)} icon`;
        const s = field.replace(/[._]/g, ' ');
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    function fmt(v) {
        if (v === null || v === undefined || v === '') return '(empty)';
        if (typeof v === 'boolean') return v ? 'On' : 'Off';
        if (Array.isArray(v)) {
            if (v.length === 7 && v.every((d) => typeof d === 'boolean')) return DAYS.filter((_, i) => v[i]).join(', ') || 'No days';
            return `${v.length} item${v.length === 1 ? '' : 's'}`;
        }
        if (typeof v === 'object') return JSON.stringify(v).slice(0, 80);
        const s = String(v);
        return s.length > 90 ? `${s.slice(0, 89)}…` : s;
    }

    function request(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' },
            body: JSON.stringify(body || {}),
        }).then((r) => r.json().catch(() => ({})).then((json) => ({ ok: r.ok, status: r.status, json })));
    }

    /* ───────────────────────── drawer ───────────────────────── */

    let drawer = null;
    let fab = null;
    let log = null;
    let input = null;
    let sendBtn = null;
    let busy = false;
    let lastFocus = null;

    function buildDrawer() {
        if (!A.canUse || drawer) return;
        fab = document.createElement('button');
        fab.type = 'button';
        fab.className = 'wca-fab';
        fab.setAttribute('aria-haspopup', 'dialog');
        fab.setAttribute('aria-expanded', 'false');
        fab.setAttribute('aria-controls', 'wcaDrawer');
        fab.innerHTML = `${icon('solar:magic-stick-3-bold')}<span>Ask AI</span>`;
        fab.addEventListener('click', () => (drawer.classList.contains('is-open') ? close() : open()));

        drawer = document.createElement('aside');
        drawer.className = 'wca-drawer';
        drawer.id = 'wcaDrawer';
        drawer.setAttribute('role', 'dialog');
        drawer.setAttribute('aria-modal', 'false');
        drawer.setAttribute('aria-labelledby', 'wcaTitle');
        drawer.hidden = true;
        drawer.innerHTML = `
            <header class="wca-head">
                <span class="wca-head-icon">${icon('solar:magic-stick-3-bold')}</span>
                <div class="wca-head-text">
                    <h2 id="wcaTitle">AI assistant</h2>
                    <p>Changes are saved as a draft — you publish.</p>
                </div>
                <button type="button" class="wca-icon-btn" id="wcaNew" title="Start a new chat" aria-label="Start a new chat">${icon('solar:restart-linear')}</button>
                <button type="button" class="wca-icon-btn" id="wcaClose" title="Close (Esc)" aria-label="Close the AI assistant">${icon('solar:close-circle-linear')}</button>
            </header>
            <div class="wca-log" id="wcaLog" role="log" aria-live="polite" aria-relevant="additions"></div>
            <div class="wca-foot" id="wcaFoot"></div>`;
        document.body.appendChild(fab);
        document.body.appendChild(drawer);
        log = drawer.querySelector('#wcaLog');
        drawer.querySelector('#wcaClose').addEventListener('click', close);
        drawer.querySelector('#wcaNew').addEventListener('click', newChat);
        drawer.addEventListener('keydown', (e) => { if (e.key === 'Escape') { e.preventDefault(); close(); } });
        renderFoot();
        renderIntro();
    }

    function renderFoot() {
        const foot = drawer.querySelector('#wcaFoot');
        if (A.blocked) {
            foot.innerHTML = `<div class="wca-blocked" role="status">${icon('solar:info-circle-linear')}<p>${esc(A.blocked)}</p>
                ${A.canManage ? '<button type="button" class="wca-link" id="wcaSettings">Open AI assistant settings</button>' : ''}</div>`;
            foot.querySelector('#wcaSettings')?.addEventListener('click', () => { close(); document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click(); });
            input = sendBtn = null;
            return;
        }
        foot.innerHTML = `<form class="wca-form" id="wcaForm">
                <label for="wcaInput" class="wca-sr">Message the AI assistant</label>
                <textarea id="wcaInput" class="wca-input" rows="2" maxlength="2000" placeholder="Ask for a change, e.g. “Hide the Twitter icon”"></textarea>
                <button type="submit" class="wca-send" id="wcaSend" aria-label="Send">${icon('solar:plain-2-bold')}</button>
            </form>
            <p class="wca-foot-note">The assistant only changes your draft. Check the preview, then press Publish.</p>`;
        input = foot.querySelector('#wcaInput');
        sendBtn = foot.querySelector('#wcaSend');
        foot.querySelector('#wcaForm').addEventListener('submit', (e) => { e.preventDefault(); send(); });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); send(); }
        });
        input.addEventListener('input', autoGrow);
    }

    function autoGrow() {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 160)}px`;
    }

    function renderIntro() {
        const module = activeModule();
        const examples = EXAMPLES[module] || EXAMPLES._;
        log.innerHTML = `<div class="wca-intro">
            <p><strong>Tell me what to change on your website</strong>, in your own words. I’ll make the change in your draft and show you exactly what changed.</p>
            ${A.blocked ? '' : `<div class="wca-chips" aria-label="Examples">${examples.map((x) => `<button type="button" class="wca-chip" data-example="${esc(x)}">${esc(x.trim())}${/\s$/.test(x) ? '…' : ''}</button>`).join('')}</div>`}
        </div>`;
        log.querySelectorAll('[data-example]').forEach((b) => b.addEventListener('click', () => {
            if (!input) return;
            input.value = b.dataset.example;
            autoGrow();
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        }));
    }

    function open() {
        lastFocus = document.activeElement;
        drawer.hidden = false;
        requestAnimationFrame(() => drawer.classList.add('is-open'));
        fab.setAttribute('aria-expanded', 'true');
        fab.classList.add('is-hidden');
        document.body.classList.add('wca-open');
        if (!log.querySelector('.wca-msg')) renderIntro(); // examples for the section open now
        (input || drawer.querySelector('#wcaClose')).focus();
    }

    function close() {
        drawer.classList.remove('is-open');
        fab.setAttribute('aria-expanded', 'false');
        fab.classList.remove('is-hidden');
        document.body.classList.remove('wca-open');
        setTimeout(() => { if (!drawer.classList.contains('is-open')) drawer.hidden = true; }, 220);
        const back = lastFocus && lastFocus !== document.body && document.contains(lastFocus) && lastFocus !== fab ? lastFocus : fab;
        back.focus();
    }

    function newChat() {
        request(A.routes.reset).catch(() => null);
        renderIntro();
        input?.focus();
    }

    function addMessage(role, text, extraClass) {
        log.querySelector('.wca-intro')?.remove();
        const el = document.createElement('div');
        el.className = `wca-msg wca-msg--${role} ${extraClass || ''}`;
        el.innerHTML = `<div class="wca-bubble">${esc(text).replace(/\n/g, '<br>')}</div>`;
        log.appendChild(el);
        scrollDown();
        return el;
    }

    function scrollDown() { log.scrollTop = log.scrollHeight; }

    function addTyping() {
        const el = document.createElement('div');
        el.className = 'wca-msg wca-msg--assistant wca-typing';
        el.setAttribute('aria-label', 'The assistant is working');
        el.innerHTML = '<div class="wca-bubble"><span></span><span></span><span></span></div>';
        log.appendChild(el);
        scrollDown();
        return el;
    }

    function setBusy(on) {
        busy = on;
        if (sendBtn) sendBtn.disabled = on;
        if (input) input.setAttribute('aria-busy', on ? 'true' : 'false');
    }

    function send() {
        if (busy || !input) return;
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        autoGrow();
        addMessage('user', text);
        setBusy(true);
        const typing = addTyping();
        const section = activeModule();
        // Save the page being edited first, so the assistant sees (and can't overwrite) what was typed.
        Promise.resolve(ext.flushPages ? ext.flushPages() : true)
            .then(() => request(A.routes.chat, { message: text, section: section && section !== MODULE ? section : null }))
            .then(({ ok, json }) => {
                typing.remove();
                if (!ok) {
                    addMessage('assistant', json.message || UNAVAILABLE, 'is-error');
                    if (json.blocked) { A.blocked = json.message; renderFoot(); }
                    return;
                }
                applyServerData(json);
                addMessage('assistant', json.reply || 'Done.');
                if (json.changes && json.changes.length) addChangesCard(json);
            })
            .catch(() => { typing.remove(); addMessage('assistant', UNAVAILABLE, 'is-error'); })
            .finally(() => { setBusy(false); input?.focus(); });
    }

    /** The server saved these drafts: hand them to the CMS screen, then refresh the preview. */
    function applyServerData(json) {
        const api = shell();
        const sections = json.data && typeof json.data === 'object' ? json.data : {};
        if (api && Object.keys(sections).length) api.replaceSections(sections);
        if (Array.isArray(json.pages) && ext.setPages) ext.setPages(json.pages);
        if (Object.keys(sections).length || json.pages) {
            W.hasUnpublished = true;
            if (Object.keys(sections).length) W.siteHasUnpublished = true;
            markUnpublished();
            if (api) api.setStatus('Changes saved as a draft — press Publish to put them live', 'ok');
            if (ext.preview) ext.preview.refresh();
        }
    }

    function markUnpublished() {
        if (document.querySelector('.wcm-badge--warn')) return;
        const status = document.getElementById('wcmStatus');
        if (!status) return;
        const badge = document.createElement('span');
        badge.className = 'wcm-badge wcm-badge--warn';
        badge.textContent = 'Unpublished changes';
        status.after(badge);
    }

    function addChangesCard(json) {
        const card = document.createElement('section');
        card.className = 'wca-changes';
        card.setAttribute('aria-label', 'Changes');
        const first = json.changes[0].section;
        card.innerHTML = `
            <header>${icon('solar:pen-new-square-linear')}<h3>Changes</h3><span class="wca-draft-tag">Saved as a draft</span></header>
            <ul>${json.changes.map((c) => `<li>
                <span class="wca-change-where">${esc(c.label)} · ${esc(fieldLabel(c.field))}</span>
                <span class="wca-change-vals"><del>${esc(fmt(c.old))}</del><span aria-hidden="true">→</span><span class="wca-sr">changed to</span><ins>${esc(fmt(c.new))}</ins></span>
            </li>`).join('')}</ul>
            <div class="wca-changes-actions">
                <button type="button" class="wca-btn" data-undo>${icon('solar:undo-left-round-linear')}Undo</button>
                <button type="button" class="wca-btn wca-btn--primary" data-show>${icon('solar:eye-linear')}Show in preview</button>
            </div>`;
        log.appendChild(card);
        scrollDown();
        card.querySelector('[data-show]').addEventListener('click', () => {
            if (window.matchMedia('(max-width: 640px)').matches) close();
            if (ext.preview) ext.preview.focus(first);
        });
        const undo = card.querySelector('[data-undo]');
        undo.addEventListener('click', () => {
            undo.disabled = true;
            request(A.routes.undo, { change_set: json.change_set }).then(({ ok, json: res }) => {
                if (!ok) { undo.disabled = false; addMessage('assistant', res.message || 'That couldn’t be undone. Try again.', 'is-error'); return; }
                applyServerData(res);
                card.classList.add('is-undone');
                undo.innerHTML = `${icon('solar:check-circle-linear')}Undone`;
                addMessage('assistant', res.message || 'Undone.', res.errors && res.errors.length ? 'is-error' : '');
            }).catch(() => { undo.disabled = false; addMessage('assistant', 'That couldn’t be undone. Try again.', 'is-error'); });
        });
    }

    /* ───────────────────────── settings screen (admins) ───────────────────────── */

    let shellHint = null;

    function restoreShell() {
        if (shellHint === null) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        if (hint) hint.innerHTML = shellHint;
        document.getElementById('wcmSaveDraft')?.style.removeProperty('display');
        shellHint = null;
    }

    document.addEventListener('click', (e) => {
        const item = e.target.closest('.wcm-nav-item');
        if (item && item.dataset.module !== MODULE) { restoreShell(); settingsFresh = false; }
    }, true);

    let settingsFresh = false;

    function renderSettings(api) {
        // The page-load copy goes stale ("Used this month"); fetch the current one once per visit.
        if (!settingsFresh && A.settings) {
            settingsFresh = true;
            fetch(A.routes.settings, { headers: { Accept: 'application/json' } })
                .then((r) => (r.ok ? r.json() : null))
                .then((fresh) => { if (fresh && activeModule() === MODULE) { A.settings = fresh; renderSettings(api); } })
                .catch(() => null);
        }
        const s = A.settings;
        if (!s) { api.editorBody.innerHTML = '<p class="wcp-hint">Only administrators can change the AI assistant.</p>'; return; }
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'AI assistant';
        if (desc) { desc.textContent = 'Let your team change the website by asking in plain language. Changes are saved as drafts; only the Publish button publishes.'; desc.hidden = false; }
        if (shellHint === null) shellHint = document.querySelector('.wcm-savebar-hint')?.innerHTML ?? '';
        const hint = document.querySelector('.wcm-savebar-hint');
        if (hint) hint.textContent = 'AI assistant settings save with their own button — they aren’t part of your website.';
        document.getElementById('wcmSaveDraft')?.style.setProperty('display', 'none', 'important');

        const known = Object.keys(s.models);
        const custom = s.saved_model && !known.includes(s.saved_model);
        const keyNote = s.has_key
            ? (s.key_from_env ? `Using the key from the server’s settings file (ends in ${esc(s.key_last4)}). Paste a key here to use your own.` : `A key is saved (ends in ${esc(s.key_last4)}). Leave this empty to keep it.`)
            : 'No key yet. Paste the API key from your AI provider.';
        const used = s.limit_type === 'usd'
            ? `$${Number(s.used.usd).toFixed(4)} (${Number(s.used.tokens).toLocaleString('en-GB')} tokens)`
            : `${Number(s.used.tokens).toLocaleString('en-GB')} tokens (≈ $${Number(s.used.usd).toFixed(4)})`;

        api.editorBody.innerHTML = `<div class="wcp wca-settings">
            <section class="wcp-card wca-card">
                <div class="wca-row">
                    <div><h3 class="wca-card-title">Turn on the AI assistant</h3><p class="wcp-hint">Shows the “Ask AI” button on every Website CMS screen for people who can edit the website.</p></div>
                    <button type="button" class="wca-switch" role="switch" id="wcaEnabled" aria-checked="${s.enabled}" aria-label="AI assistant on"><span></span></button>
                </div>
            </section>

            <section class="wcp-card wca-card">
                <h3 class="wca-card-title">Connection</h3>
                <label class="wca-label" for="wcaKey">API key</label>
                <div class="wca-key-row">
                    <input type="password" id="wcaKey" class="wca-field" autocomplete="off" spellcheck="false" placeholder="${s.has_key ? `•••• ${esc(s.key_last4)}` : 'sk-…'}">
                    ${s.has_key && !s.key_from_env ? '<button type="button" class="wca-btn" id="wcaClearKey">Remove key</button>' : ''}
                </div>
                <p class="wcp-hint">${keyNote} It’s stored encrypted and never shown again.</p>

                <label class="wca-label" for="wcaModel">Model</label>
                <select id="wcaModel" class="wca-field">
                    ${known.map((m) => `<option value="${esc(m)}" ${!custom && s.model === m ? 'selected' : ''}>${esc(s.models[m])}</option>`).join('')}
                    <option value="__custom" ${custom ? 'selected' : ''}>Custom model name…</option>
                </select>
                <input type="text" id="wcaCustomModel" class="wca-field" placeholder="Model name, e.g. gpt-4o" value="${custom ? esc(s.saved_model) : ''}" ${custom ? '' : 'hidden'} aria-label="Custom model name">

                <details class="wca-advanced" ${s.base_url !== 'https://api.openai.com/v1' ? 'open' : ''}>
                    <summary>Advanced</summary>
                    <label class="wca-label" for="wcaBase">Base URL</label>
                    <input type="url" id="wcaBase" class="wca-field" value="${esc(s.base_url)}" placeholder="https://api.openai.com/v1">
                    <p class="wcp-hint">Any service with an OpenAI-compatible Chat Completions API.</p>
                </details>

                <div class="wca-test">
                    <button type="button" class="wca-btn" id="wcaTest">${icon('solar:plug-circle-linear')}Test connection</button>
                    <span id="wcaTestResult" role="status"></span>
                </div>
            </section>

            <section class="wcp-card wca-card">
                <h3 class="wca-card-title">Monthly limit</h3>
                <p class="wcp-hint">When the limit is reached, the assistant pauses until next month.</p>
                <div class="wca-limit-row">
                    <input type="number" id="wcaLimit" class="wca-field" min="0" step="any" value="${s.monthly_limit ?? ''}" placeholder="No limit" aria-label="Monthly limit">
                    <select id="wcaLimitType" class="wca-field" aria-label="Limit in">
                        <option value="tokens" ${s.limit_type === 'tokens' ? 'selected' : ''}>tokens</option>
                        <option value="usd" ${s.limit_type === 'usd' ? 'selected' : ''}>US dollars</option>
                    </select>
                </div>
                <p class="wca-used ${s.limit_reached ? 'is-over' : ''}">Used this month: <strong>${used}</strong>${s.limit_reached ? ' — limit reached' : ''}</p>
            </section>

            <div class="wca-save-row">
                <span id="wcaSaveMsg" role="status"></span>
                <button type="button" class="wca-btn wca-btn--primary" id="wcaSave">${icon('solar:diskette-linear')}Save settings</button>
            </div>
        </div>`;

        const $ = (id) => api.editorBody.querySelector(id);
        const sw = $('#wcaEnabled');
        sw.addEventListener('click', () => sw.setAttribute('aria-checked', sw.getAttribute('aria-checked') === 'true' ? 'false' : 'true'));
        $('#wcaModel').addEventListener('change', (e) => { $('#wcaCustomModel').hidden = e.target.value !== '__custom'; if (!$('#wcaCustomModel').hidden) $('#wcaCustomModel').focus(); });
        let clearKey = false;
        $('#wcaClearKey')?.addEventListener('click', (e) => { clearKey = true; e.target.textContent = 'Will be removed when you save'; e.target.disabled = true; });

        const values = () => {
            const model = $('#wcaModel').value === '__custom' ? $('#wcaCustomModel').value.trim() : $('#wcaModel').value;
            return { api_key: $('#wcaKey').value.trim(), model, base_url: $('#wcaBase').value.trim() };
        };
        const fail = (res) => (res.errors ? Object.values(res.errors).flat()[0] : res.message) || 'That didn’t work. Try again.';

        $('#wcaTest').addEventListener('click', () => {
            const out = $('#wcaTestResult');
            out.className = '';
            out.textContent = 'Testing…';
            request(A.routes.test, values()).then(({ ok, json }) => {
                const good = ok && json.success;
                out.className = good ? 'is-ok' : 'is-error';
                out.textContent = good ? json.message : fail(json);
            }).catch(() => { out.className = 'is-error'; out.textContent = 'The test couldn’t run. Try again.'; });
        });

        $('#wcaSave').addEventListener('click', () => {
            const msg = $('#wcaSaveMsg');
            const limit = $('#wcaLimit').value.trim();
            msg.className = '';
            msg.textContent = 'Saving…';
            request(A.routes.settings, Object.assign(values(), {
                enabled: sw.getAttribute('aria-checked') === 'true',
                clear_key: clearKey,
                limit_type: $('#wcaLimitType').value,
                monthly_limit: limit === '' ? null : Number(limit),
            })).then(({ ok, json }) => {
                if (!ok) { msg.className = 'is-error'; msg.textContent = fail(json); return; }
                A.settings = json.settings;
                A.blocked = json.blocked;
                if (drawer) { renderFoot(); if (!log.querySelector('.wca-msg')) renderIntro(); }
                renderSettings(api);
                const again = api.editorBody.querySelector('#wcaSaveMsg');
                again.className = 'is-ok';
                again.textContent = json.message;
            }).catch(() => { msg.className = 'is-error'; msg.textContent = 'Couldn’t save. Try again.'; });
        });
    }

    /** For the preview panel: its toolbar "Ask AI" button and the hover chip's "Ask AI". */
    if (A.canUse) {
        ext.assistant = {
            open(prefill) {
                if (!drawer) buildDrawer();
                if (!drawer.classList.contains('is-open')) open();
                if (input && prefill) {
                    input.value = prefill;
                    autoGrow();
                    input.focus();
                    input.setSelectionRange(input.value.length, input.value.length);
                }
            },
        };
    }

    ext.renderers[MODULE] = (api) => renderSettings(api);
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', buildDrawer);
    else buildDrawer();
})();
