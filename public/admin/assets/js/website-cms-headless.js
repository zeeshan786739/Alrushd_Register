/**
 * Website CMS — connected (headless) website modules.
 *
 * Registers extra modules on the main editor (website-cms.js) through window.WCM_EXT:
 *   connection  – built-in site vs. external website, API token, cache refresh
 *   pages       – see website-cms-pages.js (uses the BLOCKS / bpField helpers shared below)
 *   site_forms  – see website-cms-forms.js
 *   nav_menu / footer_menu / website – plain schemas rendered by the main editor
 *
 * Must load before website-cms.js.
 */
(function () {
    'use strict';

    const W = window.__WCM;
    if (!W) return;

    const H = W.headless || {};
    const forms = H.forms || [];

    /* ── helpers ── */
    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    };
    const uid = () => Math.random().toString(36).slice(2, 12);
    const clone = (o) => JSON.parse(JSON.stringify(o));

    function request(method, url, body) {
        const opts = {
            method,
            headers: { 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' },
        };
        if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(async (r) => {
            const json = await r.json().catch(() => ({}));
            if (!r.ok) {
                const msg = json.message || (json.errors ? Object.values(json.errors).flat()[0] : null) || 'Request failed.';
                throw new Error(msg);
            }
            return json;
        });
    }

    function toast(api, msg, type) {
        api.setStatus(msg, type === 'error' ? 'error' : 'ok');
    }

    function formOptions(selected, emptyLabel) {
        const opts = [`<option value="">${esc(emptyLabel || '— Not connected —')}</option>`];
        let found = !selected;
        forms.forEach((f) => {
            if (f.slug === selected) found = true;
            opts.push(`<option value="${esc(f.slug)}" ${f.slug === selected ? 'selected' : ''}>${esc(f.name)} (${esc(f.slug)})</option>`);
        });
        if (!found) opts.push(`<option value="${esc(selected)}" selected>${esc(selected)} (missing or inactive)</option>`);
        return opts.join('');
    }

    /* ── Block definitions for custom pages ── */
    const BLOCKS = {
        hero: {
            label: 'Hero banner', icon: 'solar:gallery-wide-linear',
            fields: [
                { key: 'eyebrow', label: 'Small heading', type: 'text' },
                { key: 'title', label: 'Title', type: 'text' },
                { key: 'lead', label: 'Intro text', type: 'textarea' },
                { key: 'image', label: 'Background image', type: 'image' },
                { key: 'primary_text', label: 'Button text', type: 'text' },
                { key: 'primary_href', label: 'Button link', type: 'text' },
                { key: 'secondary_text', label: 'Second button text', type: 'text' },
                { key: 'secondary_href', label: 'Second button link', type: 'text' },
            ],
        },
        rich_text: {
            label: 'Text', icon: 'solar:text-field-linear',
            fields: [
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'body', label: 'Text', type: 'textarea', rows: 10, help: 'Blank line = new paragraph. ## Heading, - bullet, **bold**, [link text](/url).' },
                { key: 'background', label: 'Background', type: 'select', options: ['white', 'cream'] },
            ],
        },
        image_text: {
            label: 'Image + text', icon: 'solar:gallery-minimalistic-linear',
            fields: [
                { key: 'eyebrow', label: 'Small heading', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'body', label: 'Text', type: 'textarea', rows: 6, help: 'Same formatting as the Text block.' },
                { key: 'image', label: 'Image', type: 'image' },
                { key: 'image_side', label: 'Image side', type: 'select', options: ['left', 'right'] },
                { key: 'button_text', label: 'Button text', type: 'text' },
                { key: 'button_href', label: 'Button link', type: 'text' },
                { key: 'background', label: 'Background', type: 'select', options: ['white', 'cream'] },
            ],
        },
        features: {
            label: 'Feature cards', icon: 'solar:star-linear',
            fields: [
                { key: 'eyebrow', label: 'Small heading', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'lead', label: 'Intro text', type: 'textarea' },
                { key: 'background', label: 'Background', type: 'select', options: ['white', 'cream'] },
            ],
            items: { label: 'Cards', fields: [
                { key: 'icon', label: 'Icon', type: 'text', help: 'Lucide icon name, e.g. book-open, users, globe' },
                { key: 'title', label: 'Title', type: 'text' },
                { key: 'text', label: 'Text', type: 'textarea' },
            ]},
        },
        stats: {
            label: 'Statistics', icon: 'solar:chart-2-linear',
            fields: [{ key: 'heading', label: 'Heading', type: 'text' }],
            items: { label: 'Numbers', fields: [
                { key: 'value', label: 'Value (e.g. 60+)', type: 'text' },
                { key: 'label', label: 'Label', type: 'text' },
            ]},
        },
        faq: {
            label: 'FAQ', icon: 'solar:question-circle-linear',
            fields: [{ key: 'heading', label: 'Heading', type: 'text' }],
            items: { label: 'Questions', fields: [
                { key: 'q', label: 'Question', type: 'text' },
                { key: 'a', label: 'Answer', type: 'textarea' },
            ]},
        },
        form: {
            label: 'Form (from Form Center)', icon: 'solar:document-text-linear',
            fields: [
                { key: 'eyebrow', label: 'Small heading', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'lead', label: 'Intro text', type: 'textarea' },
                { key: 'form_slug', label: 'Form', type: 'form' },
                { key: 'success_message', label: 'Thank-you message', type: 'text' },
            ],
        },
        cta: {
            label: 'Call to action', icon: 'solar:cursor-square-linear',
            fields: [
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'text', label: 'Text', type: 'textarea' },
                { key: 'button_text', label: 'Button text', type: 'text' },
                { key: 'button_href', label: 'Button link', type: 'text' },
                { key: 'secondary_text', label: 'Second button text', type: 'text' },
                { key: 'secondary_href', label: 'Second button link', type: 'text' },
            ],
        },
    };

    /* ── Generic field renderer bound to an in-memory object via data-bp (block path) ── */
    function bpField(f, value, path) {
        const v = value == null ? '' : value;
        const help = f.help ? `<small class="wcm-help">${esc(f.help)}</small>` : '';
        const label = `<label class="wcm-field-label">${esc(f.label)}</label>`;
        switch (f.type) {
            case 'textarea':
                return `<div class="wcm-field wcm-field--wide">${label}<textarea class="wcm-input wcm-textarea" rows="${f.rows || 3}" data-bp="${path}">${esc(v)}</textarea>${help}</div>`;
            case 'select':
                return `<div class="wcm-field">${label}<select class="wcm-input" data-bp="${path}">${f.options.map((o) => `<option value="${esc(o)}" ${o === v ? 'selected' : ''}>${esc(o)}</option>`).join('')}</select>${help}</div>`;
            case 'form':
                return `<div class="wcm-field wcm-field--wide">${label}<select class="wcm-input" data-bp="${path}">${formOptions(v, '— Choose a form —')}</select>
                    <small class="wcm-help">Create or edit forms in <a href="${esc(W.routes.formCenter)}" target="_blank">Form Center</a>. Submissions arrive as CRM leads.</small></div>`;
            case 'toggle':
                return `<div class="wcm-field wcm-field--toggle"><label class="wcm-toggle"><input type="checkbox" data-bp="${path}" ${v ? 'checked' : ''}><span class="wcm-toggle-slider"></span></label><span class="wcm-field-label">${esc(f.label)}</span></div>`;
            case 'image':
                return `<div class="wcm-field wcm-field--image">${label}<div class="wcm-image-upload">
                    ${v ? `<img src="${esc(v)}" class="wcm-image-preview" alt="">` : '<div class="wcm-image-placeholder"><iconify-icon icon="solar:gallery-linear"></iconify-icon></div>'}
                    <input type="file" accept="image/*" data-bp-upload="${path}">
                    <input type="text" class="wcm-input" placeholder="or paste an image URL" data-bp="${path}" value="${esc(v)}"></div></div>`;
            default:
                return `<div class="wcm-field">${label}<input type="text" class="wcm-input" data-bp="${path}" value="${esc(v)}">${help}</div>`;
        }
    }

    function getPath(obj, path) {
        return path.split('.').reduce((o, k) => (o == null ? undefined : o[k]), obj);
    }

    function setPath(obj, path, val) {
        const keys = path.split('.');
        let cur = obj;
        keys.slice(0, -1).forEach((k, i) => {
            if (cur[k] == null) cur[k] = /^\d+$/.test(keys[i + 1]) ? [] : {};
            cur = cur[k];
        });
        cur[keys[keys.length - 1]] = val;
    }

    /* ═════════════════ Connection ═════════════════ */
    function renderConnection(api) {
        const c = H.connection || W.connection || {};
        const headless = c.mode === 'headless';
        const token = c.api_token || '';
        const env = token
            ? `CRM_API_URL=${c.api_base}\nCRM_API_TOKEN=${token}\nCRM_ORIGIN=${c.crm_origin}`
            : 'Save the connection to generate an API token.';

        api.editorBody.innerHTML = `
        <div class="wcm-conn">
            <div class="wcm-conn-modes">
                <label class="wcm-conn-mode ${!headless ? 'is-active' : ''}">
                    <input type="radio" name="wcmMode" value="builtin" ${!headless ? 'checked' : ''}>
                    <iconify-icon icon="solar:monitor-linear"></iconify-icon>
                    <strong>Built-in website</strong>
                    <span>The one-page landing site hosted by the CRM.</span>
                </label>
                <label class="wcm-conn-mode ${headless ? 'is-active' : ''}">
                    <input type="radio" name="wcmMode" value="headless" ${headless ? 'checked' : ''}>
                    <iconify-icon icon="solar:link-round-linear"></iconify-icon>
                    <strong>Connected website</strong>
                    <span>Your own multi-page website reads its content, pages and forms from this CMS.</span>
                </label>
            </div>
            <div class="wcm-form-grid">
                <div class="wcm-field wcm-field--wide"><label class="wcm-field-label">Website URL</label>
                    <input type="url" class="wcm-input" id="wcmConnUrl" placeholder="https://www.yourschool.co.uk" value="${esc(c.external_url || '')}"></div>
                <div class="wcm-field wcm-field--toggle"><label class="wcm-toggle"><input type="checkbox" id="wcmConnRedirect" ${c.redirect_legacy !== false ? 'checked' : ''}><span class="wcm-toggle-slider"></span></label>
                    <span class="wcm-field-label">Send visitors of the old built-in site to the connected website (forms and payment pages keep working)</span></div>
            </div>
            <div class="wcm-conn-actions">
                <button type="button" class="btn btn-primary-600 btn-sm radius-8" id="wcmConnSave">Save connection</button>
            </div>
            ${headless ? `
            <div class="wcm-card">
                <h4>Website environment variables</h4>
                <p class="wcm-muted">Add these to the website's hosting settings (or <code>.env.local</code>), then redeploy it.</p>
                <pre class="wcm-env" id="wcmEnv">${esc(env)}</pre>
                <div class="wcm-conn-actions">
                    <button type="button" class="btn btn-outline-primary-600 btn-sm radius-8" id="wcmCopyEnv">Copy</button>
                    <button type="button" class="btn btn-outline-neutral-500 btn-sm radius-8" id="wcmRotate">Create new token</button>
                </div>
            </div>
            <div class="wcm-card">
                <h4>Status</h4>
                <p class="wcm-muted">Last cache refresh: ${esc(c.last_revalidated_at || 'never')} ${c.last_revalidate_status ? '(' + esc(c.last_revalidate_status) + ')' : ''}</p>
                <div class="wcm-conn-actions">
                    <button type="button" class="btn btn-outline-primary-600 btn-sm radius-8" id="wcmTest">Test connection</button>
                    <button type="button" class="btn btn-outline-primary-600 btn-sm radius-8" id="wcmRevalidate">Refresh website now</button>
                </div>
                <p class="wcm-conn-result" id="wcmConnResult"></p>
            </div>` : ''}
        </div>`;

        const body = api.editorBody;
        body.querySelectorAll('input[name="wcmMode"]').forEach((r) => r.addEventListener('change', () => {
            body.querySelectorAll('.wcm-conn-mode').forEach((el) => el.classList.toggle('is-active', el.querySelector('input').checked));
        }));

        body.querySelector('#wcmConnSave').addEventListener('click', () => {
            const mode = body.querySelector('input[name="wcmMode"]:checked').value;
            if (mode === 'builtin' && headless && !confirm('Switch back to the built-in website? The connected website will stop receiving content updates.')) return;
            request('POST', H.routes.connection, {
                mode,
                external_url: body.querySelector('#wcmConnUrl').value.trim() || null,
                redirect_legacy: body.querySelector('#wcmConnRedirect').checked,
            }).then((res) => {
                toast(api, res.message);
                if (res.reload) setTimeout(() => location.reload(), 700);
            }).catch((e) => toast(api, e.message, 'error'));
        });

        if (!headless) return;
        const result = body.querySelector('#wcmConnResult');
        const show = (msg, ok) => { result.textContent = msg; result.className = 'wcm-conn-result ' + (ok ? 'is-ok' : 'is-error'); };

        body.querySelector('#wcmCopyEnv').addEventListener('click', () => {
            navigator.clipboard?.writeText(body.querySelector('#wcmEnv').textContent).then(() => toast(api, 'Copied to clipboard'));
        });
        body.querySelector('#wcmRotate').addEventListener('click', () => {
            if (!confirm('Create a new API token? The website stops loading content until you update CRM_API_TOKEN there.')) return;
            request('POST', H.routes.token).then((res) => {
                H.connection = res.connection;
                renderConnection(api);
                toast(api, res.message);
            }).catch((e) => toast(api, e.message, 'error'));
        });
        body.querySelector('#wcmTest').addEventListener('click', () => {
            show('Testing…', true);
            request('POST', H.routes.test).then((res) => show(res.message, res.success)).catch((e) => show(e.message, false));
        });
        body.querySelector('#wcmRevalidate').addEventListener('click', () => {
            show('Refreshing…', true);
            request('POST', H.routes.revalidate).then((res) => show(res.message, res.success)).catch((e) => show(e.message, false));
        });
    }

    /* ═════════════════ Dashboard (connected site) ═════════════════ */
    function renderDashboard(api) {
        const c = H.connection || {};
        const pages = (ext.getPages ? ext.getPages() : H.pages) || [];
        const live = pages.filter((p) => p.is_published && !p.draft.hidden).length;
        const custom = pages.filter((p) => p.kind === 'custom').length;
        const changed = pages.filter((p) => p.has_changes).length;
        const versions = W.versionHistory || [];

        api.editorBody.innerHTML = `<div class="wcm-dashboard">
            <div class="wcm-dash-intro">
                <div>
                    <h3 class="wcm-dash-welcome">Connected website</h3>
                    <p class="wcm-dash-welcome-sub"><a href="${esc(c.external_url)}" target="_blank" rel="noopener">${esc(c.external_url)}</a> reads its content, pages and forms from this CMS.</p>
                </div>
                <div class="wcm-dash-status-row">
                    ${W.hasUnpublished ? '<span class="wcm-dash-chip wcm-dash-chip--warn">Unpublished changes</span>' : '<span class="wcm-dash-chip wcm-dash-chip--ok">Published live</span>'}
                    <span class="wcm-dash-meta">${W.publishedAtHuman ? 'Last published ' + esc(W.publishedAtHuman) : 'Not published yet'}</span>
                </div>
            </div>
            <div class="wcm-dash-cards wcm-dash-cards--primary">
                <div class="wcm-dash-card wcm-dash-card--primary">
                    <div class="wcm-dash-card-icon"><iconify-icon icon="solar:documents-linear"></iconify-icon></div>
                    <h3>Pages</h3>
                    <p>${live} live · ${custom} custom · ${changed} with unpublished changes</p>
                    <button type="button" class="btn btn-primary-600 btn-sm radius-8" data-goto="pages">Manage pages</button>
                </div>
                <div class="wcm-dash-card wcm-dash-card--primary">
                    <div class="wcm-dash-card-icon"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></div>
                    <h3>Menus</h3>
                    <p>Header navigation and footer links</p>
                    <button type="button" class="btn btn-primary-600 btn-sm radius-8" data-goto="nav_menu">Edit menus</button>
                </div>
            </div>
            <div class="wcm-dash-cards wcm-dash-cards--secondary">
                <div class="wcm-dash-card">
                    <div class="wcm-dash-card-icon"><iconify-icon icon="solar:document-text-linear"></iconify-icon></div>
                    <h3>Website Forms</h3>
                    <p>Connect the website's forms to Form Center</p>
                    <button type="button" class="btn btn-primary-600 btn-sm radius-8" data-goto="site_forms">Connect forms</button>
                </div>
                <div class="wcm-dash-card">
                    <div class="wcm-dash-card-icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></div>
                    <h3>SEO</h3>
                    <p>Default meta tags and verification</p>
                    <button type="button" class="btn btn-primary-600 btn-sm radius-8" data-goto="seo">Edit SEO</button>
                </div>
            </div>
            ${versions.length ? `<div class="wcm-versions"><div class="wcm-versions-head"><h4>Version History</h4><span class="wcm-versions-count">${versions.length} saved</span></div>
                <ul class="wcm-versions-list">${versions.map((v, i) => `<li class="wcm-versions-item"><div class="wcm-versions-info">
                <span class="wcm-versions-label">${esc(v.published_at || 'Version ' + (i + 1))}</span><span class="wcm-versions-note">Site settings snapshot (pages are versioned separately)</span></div>
                <button type="button" class="wcm-restore-version" data-index="${i}">Restore</button></li>`).join('')}</ul></div>` : ''}
        </div>`;

        api.editorBody.querySelectorAll('[data-goto]').forEach((b) => b.addEventListener('click', () => api.switchModule(b.dataset.goto)));
        api.editorBody.querySelectorAll('.wcm-restore-version').forEach((b) => b.addEventListener('click', () => {
            fetch(W.routes.restore, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' },
                body: JSON.stringify({ index: +b.dataset.index }),
            }).then((r) => r.json()).then(() => location.reload());
        }));
    }

    /* ── Registration ── */
    const ext = window.WCM_EXT = window.WCM_EXT || { schemas: {}, renderers: {}, noReset: [] };
    ext.noReset.push('connection', 'pages', 'site_forms');
    ext.renderers.connection = renderConnection;

    // Shared with website-cms-pages.js (Pages list, page editor and block builder).
    /**
     * Drag-and-drop reordering for CMS lists (Navigation menu, Footer). Pointer events, so it works
     * with a mouse, touch and pen. The dragged row stays in the list as a dashed placeholder that moves
     * as you drag; a copy follows the pointer. The editor pane scrolls when you drag near its edges.
     * Keyboard: the drag handle is a button; the arrow keys move the item by one place.
     *
     *   sortable(root, {
     *     lists: '.wcf-links',          // containers inside root (omit: root itself is the list)
     *     item: 'li.wcf-link',          // a direct child of a list
     *     handle: '[data-drag]',
     *     connect: true,                // items may move between the lists
     *     axis: 'y' | 'grid',           // grid: items side by side (left/right counts)
     *     tail: '.add-button-li',       // inserted before this when dropped at the end
     *     scroller: element,            // scrolled while dragging near its edges
     *     onDrop({ el, fromList, fromIndex, toList, toIndex, keyboard }),
     *   })
     * onDrop must update the data and re-render; on cancel (Esc) the row goes back.
     */
    function sortable(root, o) {
        const listsOf = () => (o.lists ? [...root.querySelectorAll(o.lists)] : [root]);
        const itemsOf = (list) => [...list.children].filter((c) => c.matches(o.item));
        const ownerList = (el) => listsOf().find((l) => l === el.parentElement);

        root.addEventListener('keydown', (e) => {
            const h = e.target.closest(o.handle);
            if (!h || !root.contains(h)) return;
            const dir = { ArrowUp: -1, ArrowLeft: -1, ArrowDown: 1, ArrowRight: 1 }[e.key];
            if (!dir || (o.axis !== 'grid' && (e.key === 'ArrowLeft' || e.key === 'ArrowRight'))) return;
            const el = h.closest(o.item);
            const list = el && ownerList(el);
            if (!list) return;
            e.preventDefault();
            const from = itemsOf(list).indexOf(el);
            const to = from + dir;
            if (to < 0 || to >= itemsOf(list).length) return;
            o.onDrop({ el, fromList: list, fromIndex: from, toList: list, toIndex: to, keyboard: true });
        });

        root.addEventListener('pointerdown', (e) => {
            const h = e.target.closest(o.handle);
            if (!h || !root.contains(h) || e.button !== 0) return;
            const el = h.closest(o.item);
            const fromList = el && ownerList(el);
            if (!fromList) return;
            e.preventDefault();
            // Listen on window: moving the row in the DOM would release pointer capture on the handle.
            const fromIndex = itemsOf(fromList).indexOf(el);
            const origNext = el.nextSibling;
            const r0 = el.getBoundingClientRect();
            const sx = e.clientX;
            const sy = e.clientY;
            let x = sx;
            let y = sy;
            let ghost = null;
            let frame = 0;

            const place = () => {
                const lists = o.connect ? listsOf() : [fromList];
                const inside = (l) => { const r = l.getBoundingClientRect(); return x >= r.left - 12 && x <= r.right + 12 && y >= r.top - 12 && y <= r.bottom + 12; };
                const list = lists.find(inside) || el.parentElement;
                const sibs = itemsOf(list).filter((s) => s !== el);
                const before = sibs.find((s) => {
                    const r = s.getBoundingClientRect();
                    return o.axis === 'grid' ? (y < r.top || (y <= r.bottom && x < r.left + r.width / 2)) : y < r.top + r.height / 2;
                });
                if (before) { if (el.nextElementSibling !== before) list.insertBefore(el, before); return; }
                const last = sibs[sibs.length - 1];
                if (last) { if (last.nextElementSibling !== el) last.after(el); return; }
                const tail = o.tail && [...list.children].find((c) => c.matches(o.tail));
                if (el.parentElement !== list || (tail && el.nextElementSibling !== tail)) list.insertBefore(el, tail || null);
            };
            const tick = () => {
                const pane = o.scroller;
                if (pane) {
                    const pr = pane.getBoundingClientRect();
                    const edge = 48;
                    if (y < pr.top + edge) pane.scrollTop -= Math.ceil((pr.top + edge - y) / 4);
                    else if (y > pr.bottom - edge) pane.scrollTop += Math.ceil((y - pr.bottom + edge) / 4);
                }
                place();
                frame = requestAnimationFrame(tick);
            };
            const start = () => {
                ghost = el.cloneNode(true);
                ghost.querySelectorAll('[id]').forEach((n) => n.removeAttribute('id'));
                ghost.removeAttribute('id');
                ghost.classList.add('wcp-drag-ghost');
                ghost.setAttribute('aria-hidden', 'true');
                Object.assign(ghost.style, { position: 'fixed', left: `${r0.left}px`, top: `${r0.top}px`, width: `${r0.width}px`, height: `${r0.height}px`, margin: '0', pointerEvents: 'none', zIndex: '2000' });
                document.body.appendChild(ghost);
                el.classList.add('is-drag-source');
                document.body.classList.add('wcp-dragging');
                frame = requestAnimationFrame(tick);
            };
            const move = (ev) => {
                if (ev.pointerId !== e.pointerId) return;
                x = ev.clientX;
                y = ev.clientY;
                if (!ghost) { if (Math.hypot(x - sx, y - sy) < 5) return; start(); }
                ghost.style.transform = `translate(${x - sx}px, ${y - sy}px)`;
            };
            const end = (cancel) => {
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', up);
                window.removeEventListener('pointercancel', abort);
                document.removeEventListener('keydown', esc);
                cancelAnimationFrame(frame);
                if (!ghost) return;
                if (!cancel) place(); // a quick drag can end before the next animation frame
                ghost.remove();
                el.classList.remove('is-drag-source');
                document.body.classList.remove('wcp-dragging');
                if (cancel) { fromList.insertBefore(el, origNext); return; }
                const toList = el.parentElement;
                const toIndex = itemsOf(toList).indexOf(el);
                if (toList !== fromList || toIndex !== fromIndex) o.onDrop({ el, fromList, fromIndex, toList, toIndex, keyboard: false });
            };
            const up = () => end(false);
            const abort = () => end(true);
            const esc = (ev) => { if (ev.key === 'Escape') { ev.preventDefault(); end(true); } };
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
            window.addEventListener('pointercancel', abort);
            document.addEventListener('keydown', esc);
        });
    }

    /** The drag handle button (six dots). */
    const dragHandle = (attrs, name) => `<button type="button" class="wcp-drag" ${attrs} aria-label="Reorder ${esc(name)}: drag, or press the arrow keys" title="Drag to reorder"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg></button>`;

    ext.shared = { esc, uid, clone, formOptions, BLOCKS, bpField, getPath, setPath, sortable, dragHandle };

    if (!H.enabled) return;

    ext.renderers.dashboard = renderDashboard;

    ext.schemas.nav_menu = {
        fields: [
            { key: 'cta_text', label: 'Header button text', type: 'text' },
            { key: 'cta_url', label: 'Header button link', type: 'text' },
        ],
        note: 'Items with a <strong>Dropdown</strong> name are grouped into that dropdown (e.g. "About", "Key Stages"). Leave it empty for a top-level link. The order here is the order on the website.',
        repeater: { key: 'items', label: 'Menu items', fields: [
            { key: 'label', label: 'Label', type: 'text' },
            { key: 'href', label: 'Link (e.g. /fees)', type: 'text' },
            { key: 'group', label: 'Dropdown (optional)', type: 'text' },
            { key: 'description', label: 'Description (dropdowns)', type: 'text' },
        ]},
    };
    ext.schemas.footer_menu = {
        fields: [
            { key: 'heading', label: 'Footer heading', type: 'text' },
            { key: 'description', label: 'Footer text', type: 'textarea' },
        ],
        note: 'Use a <strong>|</strong> in the heading to start the gold second line. Links with the same <strong>Column</strong> are shown together.',
        repeater: { key: 'items', label: 'Footer links', fields: [
            { key: 'group', label: 'Column', type: 'text' },
            { key: 'label', label: 'Label', type: 'text' },
            { key: 'href', label: 'Link', type: 'text' },
        ]},
    };
    ext.schemas.website = {
        fields: [
            { key: 'announcement', label: 'Announcement bar text (leave empty to hide)', type: 'text' },
            { key: 'announcement_url', label: 'Announcement link', type: 'text' },
            { key: 'portal_url', label: 'Student / parent portal login URL', type: 'url' },
        ],
    };
    ext.schemas.theme = {
        note: 'Brand colours used across the connected website. Fonts are part of the website\'s design and are not changed here.',
        fields: [
            { key: 'primary', label: 'Primary (navy)', type: 'color' },
            { key: 'navbar', label: 'Dark sections / hero', type: 'color' },
            { key: 'footer', label: 'Footer / darkest', type: 'color' },
            { key: 'accent', label: 'Accent (gold)', type: 'color' },
            { key: 'cream', label: 'Light background', type: 'color' },
            { key: 'text', label: 'Body text', type: 'color' },
            { key: 'heading', label: 'Dark text', type: 'color' },
        ],
    };
    // Branding: see website-cms-branding.js.
    // Contact details: see website-cms-contact.js.
    // Analytics: see website-cms-analytics.js.
    // Custom code: see website-cms-code.js.
})();
