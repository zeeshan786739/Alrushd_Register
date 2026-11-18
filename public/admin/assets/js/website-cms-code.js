/**
 * Website CMS → Custom code (connected website).
 *
 * Edits cmsData.custom_code: custom_css / head_scripts / footer_scripts, each with an on/off
 * switch (custom_css_enabled / head_code_enabled / footer_code_enabled — off keeps the code
 * but the website doesn't output it). Changing anything needs the "edit website code"
 * permission (W.canEditCode); without it the screen is read-only and the server refuses too.
 * The server also limits each block to 50 KB and refuses CSS containing "</style".
 * "Preview first" saves the draft and opens the website's draft preview; publishing custom
 * code asks for confirmation. Loads after website-cms-headless.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc, clone } = ext.shared;
    const MODULE = 'custom_code';
    const REOPEN_KEY = 'wcode:reopen';
    const AUTOSAVE_MS = 1200;
    const MAX_BYTES = 51200;
    const BLOCKS = [
        { key: 'custom_css', flag: 'custom_css_enabled', tab: 'Styles (CSS)', title: 'Custom styles', desc: 'CSS added to every page, after the website’s own styles. Use it for small visual tweaks.', placeholder: '/* Example */\n.hero h1 {\n  letter-spacing: -0.01em;\n}' },
        { key: 'head_scripts', flag: 'head_code_enabled', tab: 'Head code', title: 'Head code', desc: 'HTML added to the <head> of every page once it loads — for example a tag from a booking or review service. For Google verification, use SEO.', placeholder: '<!-- Example: a snippet from a booking or review service -->' },
        { key: 'footer_scripts', flag: 'footer_code_enabled', tab: 'Footer code', title: 'Footer code', desc: 'HTML added just before </body> on every page — for example a chat widget.', placeholder: '<!-- Example: a chat widget’s snippet -->' },
    ];

    let api = null;
    let current = 'custom_css';
    // Editor geometry — must match .wcode-gutter / .wcode-text in website-cms-code.css.
    const LINE_H = 13 * 1.6;
    const PAD_Y = 14;
    const MIN_HEIGHT = 180;
    const MAX_HEIGHT = 900;
    let editorHeight = 380;
    let gutterCount = 0;
    let serverErrors = {};
    let otherSettingsPending = null;
    const save = { timer: null, inflight: null, state: 'idle', dirty: false };
    const canEdit = !!W.canEditCode;

    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;
    const bytes = (s) => new Blob([String(s || '')]).size;

    function c() {
        const data = api.getData();
        data.custom_code = data.custom_code || {};
        BLOCKS.forEach((b) => { if (data.custom_code[b.flag] === undefined || data.custom_code[b.flag] === null) data.custom_code[b.flag] = true; });
        return data.custom_code;
    }

    const lines = (text) => (String(text || '').trim() ? String(text).replace(/\n+$/, '').split('\n').length : 0);
    const isOn = (b) => c()[b.flag] !== false;

    function blockError(b) {
        if (serverErrors[b.key]) return serverErrors[b.key];
        const v = String(c()[b.key] || '');
        if (bytes(v) > MAX_BYTES) return 'This is over 50 KB. Keep custom code small — host larger files elsewhere and link to them.';
        if (b.key === 'custom_css' && /<\/\s*style/i.test(v)) return 'CSS can’t contain “</style”. Paste only the CSS rules, without <style> tags.';
        return '';
    }

    const invalid = () => BLOCKS.some((b) => blockError(b));

    function snapshot(src) {
        if (!src) return null;
        const o = {};
        BLOCKS.forEach((b) => { o[b.key] = String(src[b.key] == null ? '' : src[b.key]); o[b.flag] = src[b.flag] !== false; });
        return JSON.stringify(o);
    }

    const matchesLive = () => W.publishedCustomCode != null && snapshot(W.publishedCustomCode) === snapshot(c());
    const liveHasCode = () => BLOCKS.some((b) => String((W.publishedCustomCode || {})[b.key] || '').trim() && (W.publishedCustomCode || {})[b.flag] !== false);

    /* ───────────────────────── save / status / shell ───────────────────────── */

    function updateSavebar() {
        if (activeModule() !== MODULE) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        const saveDraft = document.getElementById('wcmSaveDraft');
        const discard = document.getElementById('wcmDiscard');
        if (saveDraft) saveDraft.style.setProperty('display', 'none', 'important'); // autosaves
        ensurePreviewButton();
        const live = matchesLive();
        let text;
        let tone = 'warn';
        if (save.state === 'invalid') text = 'Fix the highlighted code to save your changes.';
        else if (save.state === 'error') text = 'Your last change wasn’t saved — check your connection.';
        else if (save.state === 'forbidden') text = 'You can view this code but not change it.';
        else if (save.dirty || save.timer || save.inflight) text = 'Saving your changes…';
        else if (live) { text = liveHasCode() ? 'Your custom code matches the live website.' : 'No custom code on your website.'; tone = 'ok'; }
        else text = 'Changes saved as a draft — preview them, then publish to add them to your website.';
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = canEdit && !live && W.publishedCustomCode != null;
            discard.disabled = !can;
            discard.title = can ? 'Put the custom code back to the live version' : '';
        }
    }

    function ensurePreviewButton() {
        const actions = document.querySelector('.wcm-savebar-actions');
        if (!actions || document.getElementById('wcodePreview')) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'wcodePreview';
        btn.className = 'btn btn-outline-primary-600 radius-8 px-20 py-10';
        btn.innerHTML = `${icon('solar:eye-linear')} Preview first`;
        btn.title = 'Open your website with this code, without publishing';
        btn.addEventListener('click', previewFirst);
        const publish = document.getElementById('wcmPublish');
        actions.insertBefore(btn, publish || null);
    }

    let shellHint = null;
    function rememberShell() {
        if (shellHint !== null) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        shellHint = hint ? hint.innerHTML : '';
    }

    function restoreShell() {
        document.getElementById('wcodePreview')?.remove();
        document.getElementById('wcmStatus')?.removeAttribute('title');
        if (shellHint === null) return;
        const hint = document.querySelector('.wcm-savebar-hint');
        if (hint) hint.innerHTML = shellHint;
        document.getElementById('wcmSaveDraft')?.style.removeProperty('display');
        const discard = document.getElementById('wcmDiscard');
        if (discard) { discard.disabled = false; discard.title = ''; }
        shellHint = null;
    }

    /** The header pill stays short; the full sentence is its tooltip (and lives in the publish bar). */
    function pill(short, type, full) {
        if (!api) return;
        api.setStatus(short, type);
        const el = document.getElementById('wcmStatus');
        if (el) el.title = full || short;
    }

    function setState(state) {
        save.state = state;
        if (api && activeModule() === MODULE) {
            const map = {
                saving: ['Saving…', 'saving', 'Saving your changes as a draft'],
                pending: ['Unsaved changes', 'saving', 'Your changes will be saved as a draft in a moment'],
                saved: [matchesLive() ? 'Live' : 'Draft saved', 'ok', matchesLive() ? 'Your custom code matches the live website' : 'Saved as a draft — publish to add it to your website'],
                invalid: ['Couldn’t save', 'error', 'Couldn’t save – fix the highlighted code'],
                error: ['Couldn’t save', 'error', 'Couldn’t save – check your connection'],
                forbidden: ['Read only', 'error', 'You don’t have permission to change custom code'],
            };
            if (map[state]) pill(...map[state]);
        }
        updateSavebar();
    }

    function scheduleSave() {
        if (!canEdit) return;
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
            if (r.status === 403) { save.dirty = true; setState('forbidden'); return false; }
            if (r.status === 422) {
                const json = await r.json().catch(() => ({}));
                serverErrors = {};
                Object.entries(json.errors || {}).forEach(([k, m]) => { serverErrors[k.replace(/^custom_code\./, '')] = m[0]; });
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
        if (window.__wcodeSkipUnloadWarning) return;
        if (save.timer || save.inflight || (save.dirty && save.state !== 'idle' && save.state !== 'forbidden')) { e.preventDefault(); e.returnValue = ''; }
    });

    function previewFirst() {
        const win = window.open('about:blank', '_blank');
        flushSave().then((ok) => {
            if (ok === false) { if (win) win.close(); return; }
            return fetch(H.routes.previewUrl + '?path=/', { headers: { Accept: 'application/json' } })
                .then((r) => r.json())
                .then((res) => {
                    if (!res.url) throw new Error('no url');
                    if (win) { win.opener = null; win.location.href = res.url; } else window.open(res.url, '_blank', 'noopener');
                });
        }).catch(() => {
            if (win) win.close();
            pill('Preview unavailable', 'error', 'Preview isn’t available — check the website connection');
        });
    }

    /* ═══════════════════════════ render ═══════════════════════════ */

    function renderCode(a) {
        api = a;
        rememberShell();
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'Custom code';
        if (desc) { desc.textContent = 'Add your own styles or scripts to every page. Most schools never need this — the other sections cover everyday changes.'; desc.hidden = false; }
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;
        serverErrors = {};
        const last = W.customCodeLastPublished;

        api.editorBody.innerHTML = `<div class="wcp wcode">
            <div class="wcn-actions"><span class="wcode-pill">${icon('solar:code-linear')}For developers</span></div>

            <div class="wcode-warning" role="note">
                ${icon('solar:danger-triangle-linear')}
                <p><strong>Only paste code from people you trust.</strong> Code here runs on every page of your live website and can break the layout or collect visitor data. For analytics tools, use the <button type="button" class="wcs-inline-link" id="wcodeToAnalytics">Analytics</button> section instead.</p>
            </div>

            ${canEdit ? '' : `<p class="wcode-readonly">${icon('solar:lock-keyhole-minimalistic-linear')}<span>You can view this code but not change it. Ask an administrator for the “edit website code” permission.</span></p>`}

            <section class="wcp-card wcode-card">
                <div class="wcode-tabs" role="tablist" aria-label="Custom code blocks">
                    ${BLOCKS.map((b) => `<button type="button" role="tab" class="wcode-tab" id="wcodeTab-${b.key}" aria-controls="wcodePanel" aria-selected="${b.key === current}" tabindex="${b.key === current ? 0 : -1}" data-tab="${b.key}">
                        <span class="wcode-tab-name">${esc(b.tab)}</span>
                        <span class="wcode-tab-status" id="wcodeTabStatus-${b.key}"></span>
                    </button>`).join('')}
                </div>
                <div class="wcode-panel" id="wcodePanel" role="tabpanel" aria-labelledby="wcodeTab-${current}"></div>
            </section>
            ${last ? `<p class="wcp-hint wcode-last">Custom code last published by ${esc(last.by)} on ${esc(new Date(last.at).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }))}.</p>` : ''}
        </div>`;

        api.editorBody.querySelector('#wcodeToAnalytics').addEventListener('click', () => flushSave().then(() => { restoreShell(); api.switchModule('analytics'); }));
        bindTabs();
        renderPanel();
        refresh();
        if (save.state === 'idle') {
            updateSavebar();
            if (matchesLive()) pill('Live', 'ok', 'Your custom code matches the live website');
            else pill('Unpublished changes', 'saving', 'Your custom code has changes that aren’t on the website yet');
        } else setState(save.state);
    }

    function bindTabs() {
        const tabs = [...api.editorBody.querySelectorAll('[role="tab"]')];
        tabs.forEach((t, i) => {
            t.addEventListener('click', () => select(t.dataset.tab));
            t.addEventListener('keydown', (e) => {
                let j = null;
                if (e.key === 'ArrowRight') j = (i + 1) % tabs.length;
                if (e.key === 'ArrowLeft') j = (i - 1 + tabs.length) % tabs.length;
                if (e.key === 'Home') j = 0;
                if (e.key === 'End') j = tabs.length - 1;
                if (j === null) return;
                e.preventDefault();
                select(tabs[j].dataset.tab);
                tabs[j].focus();
            });
        });
    }

    function select(key) {
        current = key;
        api.editorBody.querySelectorAll('[role="tab"]').forEach((t) => {
            const on = t.dataset.tab === key;
            t.setAttribute('aria-selected', String(on));
            t.tabIndex = on ? 0 : -1;
        });
        api.editorBody.querySelector('#wcodePanel').setAttribute('aria-labelledby', `wcodeTab-${key}`);
        renderPanel();
        refresh();
    }

    function renderPanel() {
        const b = BLOCKS.find((x) => x.key === current);
        const v = c();
        const on = isOn(b);
        api.editorBody.querySelector('#wcodePanel').innerHTML = `
            <div class="wca-card-head">
                <div>
                    <h2 class="wcn-h2" id="wcodeH">${esc(b.title)}</h2>
                    <p class="wcp-card-desc">${esc(b.desc)}</p>
                </div>
                <div class="wca-switch-wrap">
                    <button type="button" class="wca-switch" id="wcodeSwitch" role="switch" aria-checked="${on}" aria-labelledby="wcodeH wcodeSwitchLabel" ${canEdit ? '' : 'disabled'}><span></span></button>
                    <span id="wcodeSwitchLabel" class="wca-switch-label">${on ? 'On' : 'Off'}</span>
                </div>
            </div>
            <p class="wcode-off" id="wcodeOff" ${on ? 'hidden' : ''}>${icon('solar:pause-circle-linear')}Switched off — the code is kept but not added to the website.</p>
            <div class="wcode-editor ${on ? '' : 'is-off'}" id="wcodeEditor" style="height:${editorHeight}px">
                <div class="wcode-gutter" id="wcodeGutter" aria-hidden="true"></div>
                <textarea id="wcodeText" class="wcode-text" spellcheck="false" autocapitalize="off" autocomplete="off" wrap="off" placeholder="${esc(b.placeholder)}" aria-label="${esc(b.title)}" aria-describedby="wcodeMeta wcodeErr" ${canEdit ? '' : 'readonly'}>${esc(v[b.key] || '')}</textarea>
            </div>
            <div class="wcode-resize" id="wcodeResize" role="separator" aria-orientation="horizontal" aria-label="Resize the code editor" aria-valuemin="${MIN_HEIGHT}" aria-valuemax="${MAX_HEIGHT}" aria-valuenow="${editorHeight}" tabindex="0"><span aria-hidden="true"></span></div>
            <p class="wcp-error" id="wcodeErr" hidden></p>
            <p class="wcp-hint wcode-meta" id="wcodeMeta"></p>`;

        const ta = api.editorBody.querySelector('#wcodeText');
        const gutter = api.editorBody.querySelector('#wcodeGutter');
        gutterCount = 0;
        ta.addEventListener('input', () => { c()[b.key] = ta.value; delete serverErrors[b.key]; refresh(); scheduleSave(); });
        ta.addEventListener('scroll', () => { gutter.scrollTop = ta.scrollTop; });
        bindResize();
        ta.addEventListener('blur', () => { if (save.dirty) flushSave(); });
        // Tab inserts two spaces (Esc then Tab moves focus on, so the editor never traps the keyboard).
        let escaped = false;
        ta.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') { escaped = true; return; }
            // Shift+Tab, or Esc then Tab, still leaves the editor (keyboard users are never trapped).
            if (e.key === 'Tab' && !e.shiftKey && !escaped && canEdit) {
                e.preventDefault();
                const s = ta.selectionStart;
                ta.setRangeText('  ', s, ta.selectionEnd, 'end');
                ta.dispatchEvent(new Event('input'));
            }
            escaped = false;
        });
        api.editorBody.querySelector('#wcodeSwitch').addEventListener('click', () => {
            if (!canEdit) return;
            c()[b.flag] = !isOn(b);
            renderPanel();
            refresh();
            scheduleSave();
            flushSave();
        });
    }

    function refresh() {
        const root = api.editorBody;
        const v = c();
        BLOCKS.forEach((b) => {
            const n = lines(v[b.key]);
            const el = root.querySelector(`#wcodeTabStatus-${b.key}`);
            if (!n) el.innerHTML = '<span class="wcode-dot is-empty"></span>Empty';
            else if (!isOn(b)) el.innerHTML = '<span class="wcode-dot is-off"></span>Off';
            else el.innerHTML = `<span class="wcode-dot is-on"></span>${n} line${n === 1 ? '' : 's'} · on`;
        });
        const b = BLOCKS.find((x) => x.key === current);
        const text = String(v[b.key] || '');
        const n = lines(text);
        renderGutter();
        const meta = root.querySelector('#wcodeMeta');
        if (meta) meta.textContent = n ? `${n} line${n === 1 ? '' : 's'} · ${text.length.toLocaleString('en-GB')} characters` : 'Nothing added yet.';
        const err = root.querySelector('#wcodeErr');
        if (err) {
            const msg = blockError(b);
            err.hidden = !msg;
            err.innerHTML = msg ? `${icon('solar:danger-circle-linear')}<span>${esc(msg)}</span>` : '';
            root.querySelector('#wcodeText').setAttribute('aria-invalid', msg ? 'true' : 'false');
        }
        updateSavebar();
    }

    /**
     * Line numbers: one <div> per line, the same font, line height and top padding as the code,
     * so each number sits next to its line. As many as the code has, but never fewer than fit.
     */
    function renderGutter() {
        const gutter = api.editorBody.querySelector('#wcodeGutter');
        const ta = api.editorBody.querySelector('#wcodeText');
        if (!gutter || !ta) return;
        const fit = Math.max(1, Math.floor((ta.clientHeight - PAD_Y * 2) / LINE_H));
        const count = Math.max(ta.value.split('\n').length, fit);
        if (count !== gutterCount) {
            const frag = document.createDocumentFragment();
            for (let i = 1; i <= count; i++) {
                const d = document.createElement('div');
                d.textContent = String(i);
                frag.appendChild(d);
            }
            gutter.replaceChildren(frag);
            gutterCount = count;
        }
        // The code area loses height to its horizontal scrollbar; pad the gutter by the same amount
        // so both can scroll to exactly the same last line.
        gutter.style.paddingBottom = `${PAD_Y + (ta.offsetHeight - ta.clientHeight)}px`;
        gutter.scrollTop = ta.scrollTop;
    }

    /** Drag (or arrow keys on) the handle under the editor to change its height. */
    function bindResize() {
        const handle = api.editorBody.querySelector('#wcodeResize');
        const editor = api.editorBody.querySelector('#wcodeEditor');
        const setHeight = (h) => {
            editorHeight = Math.round(Math.min(MAX_HEIGHT, Math.max(MIN_HEIGHT, h)));
            editor.style.height = `${editorHeight}px`;
            handle.setAttribute('aria-valuenow', String(editorHeight));
            renderGutter();
        };
        handle.addEventListener('pointerdown', (e) => {
            e.preventDefault();
            const startY = e.clientY;
            const startH = editor.offsetHeight;
            handle.setPointerCapture(e.pointerId);
            const move = (ev) => setHeight(startH + ev.clientY - startY);
            const up = () => { handle.removeEventListener('pointermove', move); handle.removeEventListener('pointerup', up); };
            handle.addEventListener('pointermove', move);
            handle.addEventListener('pointerup', up);
        });
        handle.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') { e.preventDefault(); setHeight(editorHeight + 40); }
            if (e.key === 'ArrowUp') { e.preventDefault(); setHeight(editorHeight - 40); }
        });
    }

    window.addEventListener('resize', () => { if (api && activeModule() === MODULE) renderGutter(); });

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
        if (!canEdit || W.publishedCustomCode == null || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your custom code changes?',
            message: 'All three blocks and their switches go back to the versions live on your website.',
            note: 'Only custom code changes — other settings stay as they are.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            clearTimeout(save.timer);
            const pub = clone(W.publishedCustomCode);
            const v = c();
            BLOCKS.forEach((b) => { v[b.key] = pub[b.key] == null ? '' : pub[b.key]; v[b.flag] = pub[b.flag] !== false; });
            save.dirty = true;
            renderCode(api);
            flushSave().then((done) => { if (done) pill('Live', 'ok', 'Changes discarded — your custom code matches the live website'); });
        });
    }, true);

    // Publishing custom code always asks first: it runs on every page of the live website.
    let publishing = false;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmPublish');
        if (!btn || publishing || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (invalid()) {
            setState('invalid');
            const tab = BLOCKS.find((b) => blockError(b));
            if (tab && tab.key !== current) select(tab.key);
            api.editorBody.querySelector('#wcodeText')?.focus();
            return;
        }
        flushSave().then((ok) => {
            if (ok === false) return;
            const go = () => {
                try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
                window.__wcodeSkipUnloadWarning = window.__wcanSkipUnloadWarning = window.__wcseoSkipUnloadWarning = window.__wcbSkipUnloadWarning = window.__wcsSkipUnloadWarning = window.__wccSkipUnloadWarning = window.__wcaSkipUnloadWarning = window.__wcpSkipUnloadWarning = window.__wcnSkipUnloadWarning = window.__wcfSkipUnloadWarning = true;
                publishing = true;
                btn.click();
                publishing = false;
            };
            if (matchesLive()) return go();
            const pub = W.publishedCustomCode || {};
            const changes = BLOCKS.filter((b) => String(pub[b.key] || '') !== String(c()[b.key] || '') || (pub[b.flag] !== false) !== isOn(b))
                .map((b) => `${b.tab}${isOn(b) ? '' : ' (off)'}`);
            const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title + '\n\n' + o.message));
            confirmFn({
                title: 'Publish custom code?',
                message: `Changed: ${changes.join(', ')}. This code will run on every page of your live website.`,
                note: 'Use “Preview first” if you haven’t checked it yet. Your name is recorded with this change.',
                label: 'Publish code',
                tone: 'warning',
            }).then((yes) => { if (yes) go(); });
        });
    }, true);

    ext.renderers[MODULE] = renderCode;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);
})();
