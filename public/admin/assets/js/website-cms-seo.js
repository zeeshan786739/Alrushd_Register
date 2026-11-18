/**
 * Website CMS → SEO (connected website).
 *
 * Edits cmsData.seo, keeping the formats the website reads (App\Support\SeoSettings):
 *   twitter_card   Large picture → "summary_large_image" · Small square → "summary"
 *   robots         Allow search engines → "index, follow" · Hide the whole website → "noindex, nofollow"
 *   canonical_url  the main address (https, not a test address); one-click fix from the server's suggestion
 *   google_verification  the bare code (a pasted <meta> tag is reduced to its content="…")
 * The Quick check mirrors SeoSettings::checks(). Publishing while hidden or with a test address
 * asks for confirmation. Loads after website-cms-headless.js, before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    const H = (W && W.headless) || {};
    const ext = window.WCM_EXT;
    if (!W || !H.enabled || !ext || !ext.shared) return;

    const { esc, clone } = ext.shared;
    const MODULE = 'seo';
    const REOPEN_KEY = 'wcseo:reopen';
    const AUTOSAVE_MS = 900;
    const ALLOW = 'index, follow';
    const HIDE = 'noindex, nofollow';
    const LIMITS = { title: 60, descMin: 70, descMax: 160, descIdealMin: 120 };
    const KEYS = ['meta_title', 'meta_description', 'meta_keywords', 'og_image', 'twitter_card', 'canonical_url', 'robots', 'google_verification'];
    const IMAGE_TYPES = ['image/jpeg', 'image/png'];
    const IMAGE_MAX = 5 * 1024 * 1024;

    let api = null;
    let serverErrors = {};
    let otherSettingsPending = null;
    const save = { timer: null, inflight: null, state: 'idle', dirty: false };

    const icon = (name, cls) => `<iconify-icon icon="${name}" class="${cls || ''}" aria-hidden="true"></iconify-icon>`;
    const activeModule = () => (document.querySelector('.wcm-nav-item.is-active') || {}).dataset?.module;

    /* ───────────────────────── rules (mirror App\Support\SeoSettings) ───────────────────────── */

    const isHidden = (robots) => /\bnoindex\b/i.test(String(robots || ''));

    function isTestHost(host) {
        const h = String(host || '').toLowerCase().replace(/^\[|\]$/g, '');
        if (['localhost', '0.0.0.0', '::1'].includes(h) || (!h.includes('.') && !h.includes(':'))) return true;
        if (/\.(localhost|local|test|example|invalid|internal)$/.test(h)) return true;
        // A bare IP address (127.0.0.1, 192.168.x, any IPv6…) is never a school's website address.
        return /^\d+\.\d+\.\d+\.\d+$/.test(h) || h.includes(':');
    }

    function canonicalProblem(url) {
        const u = String(url || '').trim();
        if (!u) return 'empty';
        let parsed;
        try { parsed = new URL(u); } catch (e) { return 'format'; }
        if (!['http:', 'https:'].includes(parsed.protocol) || !parsed.hostname) return 'format';
        if (isTestHost(parsed.hostname)) return 'test';
        return parsed.protocol === 'https:' ? null : 'not_https';
    }

    function verificationCode(input) {
        let v = String(input || '').trim();
        const m = /content\s*=\s*["']([^"']*)["']/i.exec(v);
        if (m) v = m[1];
        return v.replace(/[^A-Za-z0-9_-]/g, '');
    }

    function checks(seo) {
        const out = [];
        const canonical = String(seo.canonical_url || '').trim();
        const problem = canonicalProblem(canonical);
        if (problem) {
            out.push({
                key: 'canonical', level: 'error',
                title: problem === 'empty' ? 'Your website address is missing' : (problem === 'test' ? 'Your website address is set to a test address' : 'Your website address isn’t a secure https address'),
                text: problem === 'empty' ? 'Add the address your website is on, so Google links to the right place.' : `It says “${canonical}”, so Google may ignore your pages or link to the wrong place.`,
                fix: W.seoSuggestedAddress || null,
            });
        }
        out.push(isHidden(seo.robots)
            ? { key: 'robots', level: 'error', title: 'Your whole website is hidden from Google', text: 'Families searching for your school won’t find it.', fix: 'allow' }
            : { key: 'robots', level: 'ok', title: 'Search engines can list your website', text: 'Families can find you on Google.' });
        if (!String(seo.og_image || '').trim()) {
            out.push(defaultImage()
                ? { key: 'image', level: 'info', title: 'Using your website’s built-in sharing image', text: 'Add your own to choose the picture people see when your site is shared.', fix: 'image' }
                : { key: 'image', level: 'warning', title: 'No sharing image', text: 'Links shared on WhatsApp and Facebook will show without a picture.', fix: 'image' });
        }
        const t = String(seo.meta_title || '').trim().length;
        const d = String(seo.meta_description || '').trim().length;
        const parts = [];
        if (!t) parts.push('no title'); else if (t > LIMITS.title) parts.push(`title ${t} characters (aim ≤ ${LIMITS.title})`);
        if (!d) parts.push('no description'); else if (d < LIMITS.descMin || d > LIMITS.descMax) parts.push(`description ${d} characters (aim ${LIMITS.descMin}–${LIMITS.descMax})`);
        if (parts.length) {
            const text = parts.join(' · ');
            out.push({ key: 'length', level: 'warning', title: 'Title and description length', text: text.charAt(0).toUpperCase() + text.slice(1) + '.' });
        }
        if (!verificationCode(seo.google_verification)) {
            out.push({ key: 'console', level: 'info', title: 'Google Search Console not connected', text: 'Optional, but lets you see what people search to find you.' });
        }
        return out;
    }

    /* ───────────────────────── data ───────────────────────── */

    function seo() {
        const data = api.getData();
        data.seo = data.seo || {};
        return data.seo;
    }

    const siteBase = () => String((H.connection && H.connection.external_url) || '').replace(/\/+$/, '');
    const defaultImage = () => (siteBase() ? siteBase() + '/og.jpg' : '');

    function displayAddress() {
        const c = String(seo().canonical_url || '').trim();
        if (!canonicalProblem(c)) return c;
        return W.seoSuggestedAddress || siteBase() || 'https://your-website';
    }

    function host(url) {
        try { return new URL(url).host.replace(/^www\./, ''); } catch (e) { return String(url || ''); }
    }

    function truncate(text, max) {
        const t = String(text || '').trim();
        if (t.length <= max) return t;
        const cut = t.slice(0, max - 1);
        const sp = cut.lastIndexOf(' ');
        return (sp > max * 0.6 ? cut.slice(0, sp) : cut).replace(/[\s,.;:–|-]+$/, '') + '…';
    }

    function snapshot(src) {
        if (!src) return null;
        const o = {};
        KEYS.forEach((k) => { o[k] = String(src[k] == null ? '' : src[k]).trim(); });
        o.google_verification = verificationCode(o.google_verification);
        o.canonical_url = o.canonical_url.replace(/\/+$/, '');
        if (!['summary_large_image', 'summary'].includes(o.twitter_card)) o.twitter_card = 'summary_large_image';
        if (!o.robots) o.robots = ALLOW;
        return JSON.stringify(o);
    }

    const matchesLive = () => W.publishedSeo != null && snapshot(W.publishedSeo) === snapshot(seo());

    function errors() {
        const v = seo();
        const out = {};
        const c = String(v.canonical_url || '').trim();
        if (c && canonicalProblem(c) === 'format') out.canonical_url = 'Enter your website’s full address, like https://www.yourschool.co.uk';
        const raw = String(v.google_verification || '').trim();
        if (raw && verificationCode(raw).length < 10) out.google_verification = 'That doesn’t look like a Google verification code. Paste the code, or the whole tag Google shows.';
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
        else if (live) { text = 'Your SEO settings match the live website.'; tone = 'ok'; }
        else text = 'Changes saved as a draft — publish to show them on your website.';
        if (hint) hint.innerHTML = `<span class="wcp-dot wcp-dot--${tone}" aria-hidden="true"></span>${esc(text)}`;
        if (discard) {
            const can = !live && W.publishedSeo != null;
            discard.disabled = !can;
            discard.title = can ? 'Put the SEO settings back to the live version' : '';
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
                Object.entries(json.errors || {}).forEach(([k, m]) => { serverErrors[k.replace(/^seo\./, '')] = m[0]; });
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
        if (window.__wcseoSkipUnloadWarning) return;
        if (save.timer || save.inflight || (save.dirty && save.state !== 'idle')) { e.preventDefault(); e.returnValue = ''; }
    });

    function changed(field) {
        if (field) delete serverErrors[field];
        refresh();
        showErrors();
        scheduleSave();
    }

    /* ═══════════════════════════ render ═══════════════════════════ */

    function radio(name, value, current, title, text, badge, danger) {
        const on = value === current;
        return `<label class="wcp-radio ${on ? 'is-selected' : ''} ${danger ? 'is-danger' : ''}">
            <input type="radio" name="${name}" value="${value}" ${on ? 'checked' : ''}>
            <span class="wcp-radio-body">
                <span class="wcp-radio-title">${esc(title)}${badge ? ` <span class="wcp-badge-ok">${esc(badge)}</span>` : ''}</span>
                <span class="wcp-radio-text">${esc(text)}</span>
            </span>
        </label>`;
    }

    function renderSeo(a) {
        api = a;
        rememberShell();
        const title = document.getElementById('wcmModuleTitle');
        const desc = document.getElementById('wcmModuleDesc');
        if (title) title.textContent = 'SEO';
        if (desc) { desc.textContent = 'How your whole website appears on Google and social media. Each page uses these defaults unless you change it in Pages.'; desc.hidden = false; }
        if (otherSettingsPending === null && matchesLive()) otherSettingsPending = !!W.siteHasUnpublished;
        serverErrors = {};
        const v = seo();
        const card = v.twitter_card === 'summary' ? 'summary' : 'summary_large_image';
        const access = isHidden(v.robots) ? 'hide' : 'allow';

        api.editorBody.innerHTML = `<div class="wcp wcseo">
            <div class="wcn-actions">
                <button type="button" class="wcp-btn wcp-btn--outline" id="wcseoToPages">SEO for single pages${icon('solar:arrow-right-linear')}</button>
            </div>

            <section class="wcp-card wcseo-check" aria-labelledby="wcseoCheckH">
                <div class="wcseo-check-head">
                    <h2 id="wcseoCheckH" class="wcn-h2">Quick check</h2>
                    <span class="wcseo-summary" id="wcseoSummary" aria-live="polite"></span>
                </div>
                <ul class="wcseo-checks" id="wcseoChecks"></ul>
            </section>

            <div class="wcb-grid">
                <div class="wcb-form">
                    <section class="wcp-card" id="wcseo-listing" aria-labelledby="wcseoListingH">
                        <h2 id="wcseoListingH" class="wcn-h2">Google listing</h2>
                        <p class="wcp-card-desc">The default title and description Google shows for your website.</p>
                        <div class="wcp-field">
                            <div class="wcp-label-row"><label for="wcseoTitle">Search title</label><span class="wcp-counter" id="wcseoTitleCount" aria-live="polite"></span></div>
                            <input type="text" id="wcseoTitle" class="wcp-input" data-key="meta_title" value="${esc(v.meta_title || '')}" maxlength="200" autocomplete="off" aria-describedby="wcseoTitleHint">
                            <p class="wcp-hint" id="wcseoTitleHint">Aim for under 60 characters. Put the most important words first.</p>
                        </div>
                        <div class="wcp-field">
                            <div class="wcp-label-row"><label for="wcseoDesc">Search description</label><span class="wcp-counter" id="wcseoDescCount" aria-live="polite"></span></div>
                            <textarea id="wcseoDesc" class="wcp-input wcp-textarea" rows="3" data-key="meta_description" maxlength="500" aria-describedby="wcseoDescHint">${esc(v.meta_description || '')}</textarea>
                            <p class="wcp-hint" id="wcseoDescHint">120–160 characters works best.</p>
                        </div>
                    </section>

                    <section class="wcp-card" id="wcseo-social" aria-labelledby="wcseoSocialH">
                        <h2 id="wcseoSocialH" class="wcn-h2">Social sharing</h2>
                        <p class="wcp-card-desc">The picture shown when anyone shares a link to your website.</p>
                        <div id="wcseoImageSlot"></div>
                        <input type="file" id="wcseoFile" class="visually-hidden" tabindex="-1" accept="image/jpeg,image/png" aria-label="Choose a sharing image">
                        <p class="wcp-error" id="wcseoErr-og_image" hidden></p>
                        <fieldset class="wcseo-radios">
                            <legend>Picture size on X (Twitter)</legend>
                            <div class="wcp-radios">
                                ${radio('wcseoCard', 'summary_large_image', card, 'Large picture', 'Recommended — a big image above the text.', '')}
                                ${radio('wcseoCard', 'summary', card, 'Small square', 'A small picture next to the text.', '')}
                            </div>
                        </fieldset>
                    </section>

                    <section class="wcp-card" id="wcseo-address" aria-labelledby="wcseoAddrH">
                        <h2 id="wcseoAddrH" class="wcn-h2">Your website address</h2>
                        <p class="wcp-card-desc">The main address Google should use for your pages. It must be the address your website is really on.</p>
                        <div class="wcp-field">
                            <label for="wcseoCanonical">Main address</label>
                            <input type="url" id="wcseoCanonical" class="wcp-input wcseo-mono" data-key="canonical_url" value="${esc(v.canonical_url || '')}" placeholder="https://www.yourschool.co.uk" autocomplete="off" spellcheck="false" aria-describedby="wcseoCanonicalCheck">
                            <p class="wca-check" id="wcseoCanonicalCheck" aria-live="polite"></p>
                        </div>
                    </section>

                    <section class="wcp-card" id="wcseo-access" aria-labelledby="wcseoAccessH">
                        <h2 id="wcseoAccessH" class="wcn-h2">Search engine access</h2>
                        <p class="wcp-card-desc">Whether Google may list your website at all.</p>
                        <fieldset class="wcseo-radios">
                            <legend class="visually-hidden">Search engine access</legend>
                            <div class="wcp-radios" id="wcseoAccess">
                                ${radio('wcseoAccess', 'allow', access, 'Allow search engines', 'Google can find and list your pages.', 'Recommended')}
                                ${radio('wcseoAccess', 'hide', access, 'Hide the whole website', 'Only for a site that isn’t ready yet. Families won’t find you on Google.', '', true)}
                            </div>
                        </fieldset>
                    </section>

                    <section class="wcp-card" id="wcseo-console" aria-labelledby="wcseoConsoleH">
                        <h2 id="wcseoConsoleH" class="wcn-h2">Google Search Console</h2>
                        <p class="wcp-card-desc">Prove to Google that you own this website, so you can see search reports.</p>
                        <div class="wcp-field">
                            <label for="wcseoVerify">Verification code</label>
                            <input type="text" id="wcseoVerify" class="wcp-input wcseo-mono" data-key="google_verification" value="${esc(v.google_verification || '')}" placeholder="Paste the code from Google" autocomplete="off" spellcheck="false" aria-describedby="wcseoVerifyNote wcseoErr-google_verification">
                            <p class="wcp-hint" id="wcseoVerifyNote" aria-live="polite"></p>
                            <p class="wcp-error" id="wcseoErr-google_verification" hidden></p>
                        </div>
                        <ol class="wcseo-steps">
                            <li>Open <a href="https://search.google.com/search-console" target="_blank" rel="noopener">Google Search Console</a> and add your website.</li>
                            <li>Choose <strong>HTML tag</strong> as the way to verify.</li>
                            <li>Copy the code inside <code>content="…"</code> (or the whole tag) and paste it above, then publish.</li>
                        </ol>
                    </section>

                    <details class="wcp-card wcseo-advanced">
                        <summary><span><strong class="wcn-h2">Advanced</strong><span class="wcp-hint">Keywords — Google no longer uses these, so they’re optional</span></span>${icon('solar:alt-arrow-down-linear', 'wcseo-chevron')}</summary>
                        <div class="wcp-field">
                            <label for="wcseoKeywords">Keywords</label>
                            <textarea id="wcseoKeywords" class="wcp-input wcp-textarea" rows="2" data-key="meta_keywords" maxlength="500" aria-describedby="wcseoKeywordsHint">${esc(v.meta_keywords || '')}</textarea>
                            <p class="wcp-hint" id="wcseoKeywordsHint">Separate them with commas.</p>
                        </div>
                    </details>
                </div>

                <aside class="wcb-side" aria-label="Previews">
                    <section class="wcp-card wcp-card--tight">
                        <div class="wcp-side-head"><h2 class="wcp-h3">Google preview</h2></div>
                        <div class="wcp-serp" id="wcseoSerp"></div>
                    </section>
                    <section class="wcp-card wcp-card--tight">
                        <div class="wcp-side-head"><h2 class="wcp-h3">When shared</h2></div>
                        <div class="wcp-share" id="wcseoShare"></div>
                    </section>
                </aside>
            </div>
        </div>`;

        bind();
        renderImage();
        refresh();
        showErrors();
        if (save.state === 'idle') { updateSavebar(); api.setStatus(matchesLive() ? 'Your SEO settings match the live website' : 'Draft saved', 'ok'); } else setState(save.state);
    }

    function counter(el, n, ok) {
        el.textContent = el.dataset.max ? `${n} / ${el.dataset.max}` : String(n);
        el.className = 'wcp-counter ' + (n === 0 ? '' : ok ? 'is-ok' : 'is-over');
    }

    function refresh() {
        const v = seo();
        const b = api.editorBody;
        const q = (s) => b.querySelector(s);
        const t = String(v.meta_title || '').length;
        const d = String(v.meta_description || '').length;
        const tc = q('#wcseoTitleCount'); tc.dataset.max = LIMITS.title; counter(tc, t, t <= LIMITS.title);
        const dc = q('#wcseoDescCount'); dc.dataset.max = LIMITS.descMax; counter(dc, d, d >= LIMITS.descMin && d <= LIMITS.descMax);

        // Address check
        const problem = canonicalProblem(v.canonical_url);
        const check = q('#wcseoCanonicalCheck');
        const input = q('#wcseoCanonical');
        const msgs = {
            empty: 'Add your website’s address, starting with https://',
            format: 'Enter your website’s full address, like https://www.yourschool.co.uk',
            test: 'This must be your real web address starting with https:// — not a test address like localhost.',
            not_https: 'Use the secure address, starting with https://',
        };
        check.className = 'wca-check ' + (problem ? 'is-bad' : 'is-ok');
        check.innerHTML = problem ? `${icon('solar:danger-circle-linear')}${esc(msgs[problem])}` : `${icon('solar:check-read-linear')}Looks right`;
        input.setAttribute('aria-invalid', problem ? 'true' : 'false');

        b.querySelectorAll('.wcp-radio').forEach((l) => l.classList.toggle('is-selected', l.querySelector('input').checked));
        const raw = String(v.google_verification || '').trim();
        const code = verificationCode(raw);
        q('#wcseoVerifyNote').textContent = raw && code && code !== raw ? `We’ll use just the code: ${code}` : (code ? 'Connected once you publish.' : '');

        renderChecks();
        renderPreviews();
        updateSavebar();
    }

    function renderChecks() {
        const list = checks(seo());
        const issues = list.filter((c) => c.level === 'error' || c.level === 'warning');
        const errorsN = list.filter((c) => c.level === 'error').length;
        const summary = api.editorBody.querySelector('#wcseoSummary');
        summary.className = 'wcseo-summary ' + (!issues.length ? 'is-ok' : errorsN ? 'is-error' : 'is-warn');
        summary.textContent = issues.length ? `${issues.length} thing${issues.length === 1 ? '' : 's'} to fix` : 'All good';
        const icons = { error: 'solar:danger-circle-bold', warning: 'solar:danger-triangle-bold', info: 'solar:info-circle-bold', ok: 'solar:check-circle-bold' };
        api.editorBody.querySelector('#wcseoChecks').innerHTML = list.map((c) => {
            let fix = '';
            if (c.key === 'canonical' && c.fix) fix = `<button type="button" class="wcp-btn wcp-btn--primary wcp-btn--sm" data-fix="canonical">Use ${esc(c.fix)}</button>`;
            if (c.key === 'canonical' && !c.fix) fix = '<button type="button" class="wcp-btn wcp-btn--primary wcp-btn--sm" data-fix="goto-address">Fix it</button>';
            if (c.key === 'robots' && c.fix) fix = '<button type="button" class="wcp-btn wcp-btn--primary wcp-btn--sm" data-fix="allow">Allow search engines</button>';
            if (c.key === 'image') fix = `<button type="button" class="wcp-btn wcp-btn--${c.level === 'info' ? 'outline' : 'primary'} wcp-btn--sm" data-fix="image">Add image</button>`;
            if (c.key === 'length') fix = '<button type="button" class="wcp-btn wcp-btn--outline wcp-btn--sm" data-fix="length">Edit</button>';
            return `<li class="wcseo-row is-${c.level}">
                ${icon(icons[c.level], 'wcseo-row-icon')}
                <span class="wcseo-row-text"><strong>${esc(c.title)}</strong><span>${esc(c.text)}</span></span>
                ${fix}
            </li>`;
        }).join('');
    }

    function renderPreviews() {
        const v = seo();
        const d = api.getData();
        const b = d.branding || {};
        const address = displayAddress();
        const name = b.company_name || 'Your school';
        const initials = name.split(/[\s-]+/).filter((x) => /^[A-Za-z]/.test(x)).slice(0, 2).map((x) => x[0].toUpperCase()).join('');
        const logo = b.website_logo || (siteBase() ? siteBase() + '/images/crest.png' : '');
        const title = String(v.meta_title || '').trim() || name;
        const desc = String(v.meta_description || '').trim() || String(b.website_description || '').trim();

        api.editorBody.querySelector('#wcseoSerp').innerHTML = `
            <div class="wcp-serp-site">
                <span class="wcp-serp-logo">${logo ? `<img src="${esc(logo)}" alt="" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span hidden>${esc(initials)}</span>` : esc(initials)}</span>
                <span><span class="wcp-serp-name">${esc(name)}</span><span class="wcp-serp-url">${esc(address)}</span></span>
            </div>
            <div class="wcp-serp-title">${esc(truncate(title, LIMITS.title))}</div>
            <div class="wcp-serp-desc">${esc(truncate(desc, LIMITS.descMax))}</div>`;

        const img = String(v.og_image || '').trim() || defaultImage();
        api.editorBody.querySelector('#wcseoShare').innerHTML = `
            <div class="wcp-share-img ${img ? '' : 'is-placeholder'}">${img
                ? `<img src="${esc(img)}" alt="" onerror="this.parentNode.classList.add('is-broken')">`
                : `${icon('solar:gallery-linear', 'wcseo-empty-icon')}<span class="wcp-share-note">No image — links look plain and get fewer clicks</span>`}</div>
            <div class="wcp-share-body">
                <span class="wcp-share-domain">${esc(host(address))}</span>
                <span class="wcp-share-title">${esc(truncate(title, 70))}</span>
                ${v.og_image ? '' : (img ? '<span class="wcp-share-desc">Your website’s built-in sharing image</span>' : '')}
            </div>`;
    }

    function renderImage() {
        const slot = api.editorBody.querySelector('#wcseoImageSlot');
        const img = String(seo().og_image || '').trim();
        slot.innerHTML = img
            ? `<div class="wcp-thumb">
                    <img src="${esc(img)}" alt="Sharing image for your website">
                    <div class="wcp-thumb-actions">
                        <label for="wcseoFile" class="wcp-btn wcp-btn--outline wcp-btn--sm" tabindex="0" role="button">${icon('solar:refresh-linear')}Replace</label>
                        <button type="button" class="wcp-btn wcp-btn--ghost wcp-btn--sm" id="wcseoRemove">${icon('solar:trash-bin-trash-linear')}Remove</button>
                    </div>
               </div>`
            : `<label for="wcseoFile" class="wcp-drop" id="wcseoDrop" tabindex="0" role="button" aria-describedby="wcseoDropHint">
                    <span class="wcp-tile wcp-tile--gold">${icon('solar:gallery-add-linear')}</span>
                    <span class="wcp-drop-title">Drag an image here, or <span class="wcp-link">choose a file</span></span>
                    <span class="wcp-hint" id="wcseoDropHint">JPG or PNG · 1200 × 630 px · up to 5 MB${defaultImage() ? ' · until then your website’s built-in image is used' : ''}</span>
               </label>`;
        slot.querySelectorAll('label[for="wcseoFile"]').forEach((l) => l.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); api.editorBody.querySelector('#wcseoFile').click(); }
        }));
        slot.querySelector('#wcseoRemove')?.addEventListener('click', () => { seo().og_image = ''; renderImage(); changed('og_image'); flushSave(); });
        const drop = slot.querySelector('#wcseoDrop');
        if (drop) {
            ['dragenter', 'dragover'].forEach((t) => drop.addEventListener(t, (e) => { e.preventDefault(); drop.classList.add('is-over'); }));
            ['dragleave', 'drop'].forEach((t) => drop.addEventListener(t, () => drop.classList.remove('is-over')));
            drop.addEventListener('drop', (e) => { e.preventDefault(); const f = e.dataTransfer && e.dataTransfer.files[0]; if (f) upload(f); });
        }
    }

    function imageError(msg) {
        const box = api.editorBody.querySelector('#wcseoErr-og_image');
        box.hidden = !msg;
        box.innerHTML = msg ? `${icon('solar:danger-circle-linear')}<span>${esc(msg)}</span>` : '';
    }

    function upload(file) {
        imageError('');
        if (!IMAGE_TYPES.includes(file.type)) return imageError('Please choose a JPG or PNG image.');
        if (file.size > IMAGE_MAX) return imageError('That image is larger than 5 MB. Please choose a smaller one.');
        const slot = api.editorBody.querySelector('#wcseoImageSlot');
        slot.innerHTML = `<div class="wcp-drop is-busy" aria-live="polite"><span class="wcp-spinner" aria-hidden="true"></span><span class="wcp-drop-title">Uploading ${esc(file.name)}…</span></div>`;
        const fd = new FormData();
        fd.append('file', file);
        fd.append('field', 'seo.og_image');
        fetch(W.routes.upload, { method: 'POST', headers: { 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' }, body: fd })
            .then(async (r) => {
                const json = await r.json().catch(() => ({}));
                if (!r.ok) throw new Error((json.errors && json.errors.file && json.errors.file[0]) || json.message || 'Upload failed. Please try again.');
                seo().og_image = json.url;
                renderImage();
                changed('og_image');
                flushSave();
            })
            .catch((e) => { renderImage(); imageError(e.message); });
    }

    function showErrors() {
        const errs = errors();
        ['google_verification'].forEach((k) => {
            const box = api.editorBody.querySelector(`#wcseoErr-${k}`);
            const msg = errs[k];
            box.hidden = !msg;
            box.innerHTML = msg ? `${icon('solar:danger-circle-linear')}<span>${esc(msg)}</span>` : '';
            const input = api.editorBody.querySelector(`[aria-describedby~="wcseoErr-${k}"]`);
            if (input) input.setAttribute('aria-invalid', msg ? 'true' : 'false');
        });
        if (serverErrors.canonical_url) {
            const check = api.editorBody.querySelector('#wcseoCanonicalCheck');
            check.className = 'wca-check is-bad';
            check.innerHTML = `${icon('solar:danger-circle-linear')}${esc(serverErrors.canonical_url)}`;
        }
    }

    function scrollToCard(id, focusSel) {
        const el = api.editorBody.querySelector('#' + id);
        if (!el) return;
        const pane = api.editorBody;
        pane.scrollTo({ top: Math.max(0, pane.scrollTop + el.getBoundingClientRect().top - pane.getBoundingClientRect().top - 16), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        const f = focusSel && api.editorBody.querySelector(focusSel);
        if (f) f.focus({ preventScroll: true });
    }

    function bind() {
        const b = api.editorBody;
        b.querySelectorAll('[data-key]').forEach((el) => {
            el.addEventListener('input', () => { seo()[el.dataset.key] = el.value; changed(el.dataset.key); });
            el.addEventListener('blur', () => { if (save.dirty) flushSave(); });
        });
        b.querySelectorAll('input[name="wcseoCard"]').forEach((r) => r.addEventListener('change', () => { if (r.checked) { seo().twitter_card = r.value; changed('twitter_card'); flushSave(); } }));
        b.querySelectorAll('input[name="wcseoAccess"]').forEach((r) => r.addEventListener('change', () => { if (r.checked) { seo().robots = r.value === 'hide' ? HIDE : ALLOW; changed('robots'); flushSave(); } }));
        b.querySelector('#wcseoFile').addEventListener('change', (e) => { const f = e.target.files[0]; e.target.value = ''; if (f) upload(f); });
        b.querySelector('#wcseoToPages').addEventListener('click', () => flushSave().then(() => { restoreShell(); api.switchModule('pages'); }));

        b.querySelector('#wcseoChecks').addEventListener('click', (e) => {
            const btn = e.target.closest('[data-fix]');
            if (!btn) return;
            const fix = btn.dataset.fix;
            if (fix === 'canonical' && W.seoSuggestedAddress) {
                seo().canonical_url = W.seoSuggestedAddress;
                b.querySelector('#wcseoCanonical').value = W.seoSuggestedAddress;
                changed('canonical_url');
                flushSave();
                api.setStatus(`Website address set to ${W.seoSuggestedAddress}`, 'ok');
            } else if (fix === 'goto-address') {
                scrollToCard('wcseo-address', '#wcseoCanonical');
            } else if (fix === 'allow') {
                seo().robots = ALLOW;
                b.querySelector('input[name="wcseoAccess"][value="allow"]').checked = true;
                changed('robots');
                flushSave();
            } else if (fix === 'image') {
                scrollToCard('wcseo-social', '#wcseoDrop, label[for="wcseoFile"]');
            } else if (fix === 'length') {
                scrollToCard('wcseo-listing', '#wcseoTitle');
            }
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
        if (W.publishedSeo == null || matchesLive()) return;
        const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title));
        confirmFn({
            title: 'Discard your SEO changes?',
            message: 'All SEO settings go back to the versions live on your website.',
            note: 'Only SEO changes — other settings stay as they are.',
            label: 'Discard changes',
            tone: 'danger',
        }).then((ok) => {
            if (!ok) return;
            clearTimeout(save.timer);
            const pub = clone(W.publishedSeo);
            const v = seo();
            KEYS.forEach((k) => { v[k] = pub[k] == null ? '' : pub[k]; });
            save.dirty = true;
            renderSeo(api);
            flushSave().then((done) => { if (done) api.setStatus('Changes discarded', 'ok'); });
        });
    }, true);

    // Publishing while hidden from Google, or with a test address, needs a clear yes.
    let publishing = false;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wcmPublish');
        if (!btn || publishing || activeModule() !== MODULE) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (Object.keys(errors()).length) {
            setState('invalid');
            const first = api.editorBody.querySelector('[aria-invalid="true"]');
            if (first) first.focus();
            return;
        }
        flushSave().then((ok) => {
            if (!ok) return;
            const v = seo();
            const risks = [];
            if (isHidden(v.robots)) risks.push('Your whole website will be hidden from Google.');
            if (canonicalProblem(v.canonical_url)) risks.push(`Your website address is “${String(v.canonical_url || '').trim() || 'empty'}”, not your real https address.`);
            const go = () => {
                try { sessionStorage.setItem(REOPEN_KEY, '1'); } catch (err) { /* optional */ }
                window.__wcseoSkipUnloadWarning = window.__wcbSkipUnloadWarning = window.__wcsSkipUnloadWarning = window.__wccSkipUnloadWarning = window.__wcaSkipUnloadWarning = window.__wcpSkipUnloadWarning = window.__wcnSkipUnloadWarning = window.__wcfSkipUnloadWarning = true;
                publishing = true;
                btn.click();
                publishing = false;
            };
            if (!risks.length) return go();
            const confirmFn = window.CrmUI && window.CrmUI.confirm ? window.CrmUI.confirm : (o) => Promise.resolve(window.confirm(o.title + '\n\n' + o.message));
            confirmFn({
                title: 'Publish with these SEO problems?',
                message: risks.join(' '),
                note: 'Families may not find your website on Google until this is fixed.',
                label: 'Publish anyway',
                tone: 'danger',
            }).then((yes) => { if (yes) go(); });
        });
    }, true);

    ext.renderers[MODULE] = renderSeo;
    ext.noReset = ext.noReset || [];
    if (!ext.noReset.includes(MODULE)) ext.noReset.push(MODULE);

    setTimeout(() => {
        let back = false;
        try { back = sessionStorage.getItem(REOPEN_KEY) === '1'; sessionStorage.removeItem(REOPEN_KEY); } catch (err) { /* optional */ }
        if (back) document.querySelector(`.wcm-nav-item[data-module="${MODULE}"]`)?.click();
    }, 0);

    ext.seoModel = { checks, canonicalProblem, verificationCode, isHidden, isTestHost };
})();
