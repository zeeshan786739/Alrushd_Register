/**
 * Website CMS — connected (headless) website modules.
 *
 * Registers extra modules on the main editor (website-cms.js) through window.WCM_EXT:
 *   connection  – built-in site vs. external website, API token, cache refresh
 *   pages       – page list, page editor (SEO, visibility) and block builder for custom pages
 *   site_forms  – which Form Center form receives each of the website's own forms
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

    /* ═════════════════ Website forms ═════════════════ */
    function renderSiteForms(api) {
        const data = api.getData();
        data.website = data.website || {};
        data.website.forms = data.website.forms || {};
        const slots = [
            ['apply', 'Apply Now form', '/apply-now'],
            ['contact', 'Contact form', '/contact'],
            ['review', 'Review form', '/reviews'],
            ['newsletter', 'Newsletter sign-up', 'Blog and footer'],
        ];

        api.editorBody.innerHTML = `
            <div class="wcm-note">These forms are designed into the website. Choose which <a href="${esc(W.routes.formCenter)}" target="_blank">Form Center</a> form receives each one —
            submissions become CRM leads. The chosen form must contain the same field keys as the website form (the <em>Website: …</em> forms already do).
            To put any other form on a page, add a <strong>Form</strong> block to a page in <a href="#" data-goto="pages">Pages</a>.</div>
            <div class="wcm-form-grid">
                ${slots.map(([key, label, where]) => `
                <div class="wcm-field wcm-field--wide"><label class="wcm-field-label">${esc(label)} <span class="wcm-muted">· ${esc(where)}</span></label>
                    <select class="wcm-input" data-site-form="${key}">${formOptions(data.website.forms[key] || '')}</select></div>`).join('')}
            </div>`;

        api.editorBody.querySelectorAll('[data-site-form]').forEach((sel) => sel.addEventListener('change', () => {
            api.getData().website.forms[sel.dataset.siteForm] = sel.value;
            api.markDirty();
            api.schedulePreview();
        }));
        api.editorBody.querySelector('[data-goto]')?.addEventListener('click', (e) => { e.preventDefault(); api.switchModule('pages'); });
    }

    /* ═════════════════ Pages ═════════════════ */
    let pages = H.pages || [];
    let editing = null; // { id|null, kind, draft }

    function pageStatus(p) {
        if (p.draft.hidden) return ['Hidden', 'is-off'];
        if (!p.is_published) return ['Draft — not live yet', 'is-warn'];
        if (p.has_changes) return ['Live · unpublished changes', 'is-warn'];
        return ['Live', 'is-on'];
    }

    function previewPath(api, path) {
        if (!H.routes.previewUrl) return;
        fetch(H.routes.previewUrl + '?path=' + encodeURIComponent(path || '/'), { headers: { Accept: 'application/json' } })
            .then((r) => r.json()).then((res) => { if (res.url) api.setPreviewUrl(res.url); });
    }

    function renderPages(api) {
        if (editing) return renderPageEditor(api);

        const rows = pages.map((p) => {
            const [label, cls] = pageStatus(p);
            return `<li class="wcm-page-row">
                <div class="wcm-page-main">
                    <span class="wcm-page-title">${esc(p.draft.title || '(untitled)')}</span>
                    <span class="wcm-page-path">${esc(p.draft.path)}</span>
                </div>
                <span class="wcm-page-kind ${p.kind === 'custom' ? 'is-custom' : ''}">${p.kind === 'custom' ? 'Custom' : 'Built-in'}</span>
                <span class="wcm-sort-status ${cls}">${esc(label)}</span>
                <div class="wcm-page-actions">
                    <button type="button" class="wcm-sort-edit" data-preview="${esc(p.draft.path)}">Preview</button>
                    <button type="button" class="wcm-sort-edit" data-edit="${p.id}">Edit</button>
                </div>
            </li>`;
        }).join('');

        api.editorBody.innerHTML = `
            <div class="wcm-pages-head">
                <p class="wcm-muted">Built-in pages are designed in the website's code — here you manage their SEO and visibility.
                Custom pages are built from blocks right here. Changes go live when you press <strong>Publish</strong>.</p>
                <button type="button" class="btn btn-primary-600 btn-sm radius-8" id="wcmNewPage">+ New page</button>
            </div>
            <ul class="wcm-pages-list">${rows || '<li class="wcm-empty">No pages yet.</li>'}</ul>`;

        api.editorBody.querySelector('#wcmNewPage').addEventListener('click', () => {
            editing = { id: null, kind: 'custom', draft: {
                title: '', path: '', hidden: false,
                seo: { title: '', description: '', og_image: '', noindex: false },
                blocks: [
                    { id: uid(), type: 'hero', data: { title: '', lead: '' } },
                    { id: uid(), type: 'form', data: { heading: '', form_slug: '' } },
                ],
            }};
            renderPageEditor(api);
        });
        api.editorBody.querySelectorAll('[data-edit]').forEach((b) => b.addEventListener('click', () => {
            const p = pages.find((x) => String(x.id) === b.dataset.edit);
            editing = { id: p.id, kind: p.kind, draft: clone(p.draft), published: p.is_published };
            renderPageEditor(api);
            previewPath(api, p.draft.path);
        }));
        api.editorBody.querySelectorAll('[data-preview]').forEach((b) => b.addEventListener('click', () => previewPath(api, b.dataset.preview)));
    }

    function blockHtml(block, index, total) {
        const def = BLOCKS[block.type];
        if (!def) return '';
        const base = `blocks.${index}.data`;
        let html = `<div class="wcm-block" data-block="${index}">
            <div class="wcm-block-head">
                <span><iconify-icon icon="${def.icon}"></iconify-icon> ${esc(def.label)}</span>
                <span class="wcm-block-tools">
                    <button type="button" data-move="${index}" data-dir="-1" ${index === 0 ? 'disabled' : ''} title="Move up">↑</button>
                    <button type="button" data-move="${index}" data-dir="1" ${index === total - 1 ? 'disabled' : ''} title="Move down">↓</button>
                    <button type="button" data-remove-block="${index}" class="is-danger" title="Remove block">&times;</button>
                </span>
            </div>
            <div class="wcm-form-grid">${def.fields.map((f) => bpField(f, (block.data || {})[f.key], `${base}.${f.key}`)).join('')}</div>`;
        if (def.items) {
            const items = (block.data || {}).items || [];
            html += `<div class="wcm-block-items"><div class="wcm-repeater-header"><h4>${esc(def.items.label)}</h4>
                <button type="button" class="btn btn-sm btn-outline-primary-600 radius-8" data-add-item="${index}">+ Add</button></div>`;
            items.forEach((item, i) => {
                html += `<div class="wcm-repeater-item"><div class="wcm-repeater-item-header"><span>${i + 1}</span>
                    <button type="button" class="wcm-repeater-remove" data-remove-item="${index}" data-item="${i}">&times;</button></div>
                    <div class="wcm-repeater-fields">${def.items.fields.map((f) => bpField(f, item[f.key], `${base}.items.${i}.${f.key}`)).join('')}</div></div>`;
            });
            html += '</div>';
        }
        return html + '</div>';
    }

    function renderPageEditor(api) {
        const d = editing.draft;
        const custom = editing.kind === 'custom';
        const blocks = d.blocks || [];

        api.editorBody.innerHTML = `
            <div class="wcm-pages-head">
                <button type="button" class="wcm-back" id="wcmBack">← All pages</button>
                <div class="wcm-conn-actions">
                    ${editing.id ? `<button type="button" class="btn btn-outline-primary-600 btn-sm radius-8" id="wcmAddToMenu">Add to menu</button>` : ''}
                    ${editing.id && custom ? `<button type="button" class="btn btn-outline-danger btn-sm radius-8" id="wcmDeletePage">Delete</button>` : ''}
                    <button type="button" class="btn btn-primary-600 btn-sm radius-8" id="wcmSavePage">Save page</button>
                </div>
            </div>
            ${!custom ? '<div class="wcm-note">This page is designed in the website\'s code. You can change its SEO and hide it; its layout and wording are edited by your developer.</div>' : ''}
            <div class="wcm-form-grid">
                ${bpField({ key: 'title', label: 'Page title', type: 'text' }, d.title, 'title')}
                ${custom
                    ? bpField({ key: 'path', label: 'Page URL', type: 'text', help: 'For example /summer-school' }, d.path, 'path')
                    : `<div class="wcm-field"><label class="wcm-field-label">Page URL</label><input class="wcm-input" value="${esc(d.path)}" disabled></div>`}
                ${bpField({ key: 'hidden', label: custom ? 'Hide this page (returns "not found")' : 'Hide from search engines and sitemap', type: 'toggle' }, d.hidden, 'hidden')}
            </div>
            <h4 class="wcm-section-title">SEO</h4>
            <div class="wcm-form-grid">
                ${bpField({ key: 'title', label: 'Meta title', type: 'text', help: 'Leave empty to use the page\'s own title.' }, d.seo?.title, 'seo.title')}
                ${bpField({ key: 'description', label: 'Meta description', type: 'textarea' }, d.seo?.description, 'seo.description')}
                ${bpField({ key: 'og_image', label: 'Social sharing image', type: 'image' }, d.seo?.og_image, 'seo.og_image')}
                ${bpField({ key: 'noindex', label: 'Ask search engines not to index this page', type: 'toggle' }, d.seo?.noindex, 'seo.noindex')}
            </div>
            ${custom ? `
            <h4 class="wcm-section-title">Content blocks</h4>
            <div class="wcm-blocks">${blocks.map((b, i) => blockHtml(b, i, blocks.length)).join('') || '<p class="wcm-empty">No blocks yet.</p>'}</div>
            <div class="wcm-add-block">
                <select class="wcm-input" id="wcmBlockType">${Object.entries(BLOCKS).map(([k, b]) => `<option value="${k}">${esc(b.label)}</option>`).join('')}</select>
                <button type="button" class="btn btn-outline-primary-600 btn-sm radius-8" id="wcmAddBlock">+ Add block</button>
            </div>` : ''}`;

        bindPageEditor(api);
    }

    function bindPageEditor(api) {
        const body = api.editorBody;
        const d = editing.draft;

        body.querySelectorAll('[data-bp]').forEach((el) => {
            el.addEventListener(el.type === 'checkbox' || el.tagName === 'SELECT' ? 'change' : 'input', () => {
                setPath(d, el.dataset.bp, el.type === 'checkbox' ? el.checked : el.value);
                editing.dirty = true;
                api.setStatus('Unsaved page changes', 'saving');
            });
        });
        body.querySelectorAll('[data-bp-upload]').forEach((input) => input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            const fd = new FormData();
            fd.append('file', file);
            fd.append('field', 'page');
            fetch(W.routes.upload, { method: 'POST', headers: { 'X-CSRF-TOKEN': W.csrf, Accept: 'application/json' }, body: fd })
                .then((r) => r.json()).then((res) => {
                    if (!res.success) throw new Error(res.message || 'Upload failed');
                    setPath(d, input.dataset.bpUpload, res.url);
                    editing.dirty = true;
                    renderPageEditor(api);
                }).catch((e) => toast(api, e.message, 'error'));
        }));

        body.querySelector('#wcmBack').addEventListener('click', () => {
            if (editing.dirty && !confirm('Leave without saving this page?')) return;
            editing = null;
            renderPages(api);
        });
        body.querySelector('#wcmSavePage').addEventListener('click', () => savePage(api));
        body.querySelector('#wcmDeletePage')?.addEventListener('click', () => {
            if (!confirm('Delete this page? If it is live it disappears from the website immediately.')) return;
            request('DELETE', H.routes.pages + '/' + editing.id).then((res) => {
                pages = res.pages; editing = null; renderPages(api); toast(api, res.message);
            }).catch((e) => toast(api, e.message, 'error'));
        });
        body.querySelector('#wcmAddToMenu')?.addEventListener('click', () => {
            const data = api.getData();
            data.nav_menu = data.nav_menu || {};
            data.nav_menu.items = data.nav_menu.items || [];
            if (data.nav_menu.items.some((i) => i.href === d.path)) return toast(api, 'This page is already in the menu.');
            data.nav_menu.items.push({ label: d.title, href: d.path, group: '', description: '' });
            api.markDirty();
            api.schedulePreview();
            toast(api, 'Added to the main menu — arrange it in Navigation Menu, then Publish.');
        });

        body.querySelector('#wcmAddBlock')?.addEventListener('click', () => {
            const type = body.querySelector('#wcmBlockType').value;
            d.blocks = d.blocks || [];
            d.blocks.push({ id: uid(), type, data: BLOCKS[type].items ? { items: [{}] } : {} });
            editing.dirty = true;
            renderPageEditor(api);
        });
        body.querySelectorAll('[data-remove-block]').forEach((b) => b.addEventListener('click', () => {
            d.blocks.splice(+b.dataset.removeBlock, 1); editing.dirty = true; renderPageEditor(api);
        }));
        body.querySelectorAll('[data-move]').forEach((b) => b.addEventListener('click', () => {
            const i = +b.dataset.move, j = i + (+b.dataset.dir);
            [d.blocks[i], d.blocks[j]] = [d.blocks[j], d.blocks[i]]; editing.dirty = true; renderPageEditor(api);
        }));
        body.querySelectorAll('[data-add-item]').forEach((b) => b.addEventListener('click', () => {
            const blk = d.blocks[+b.dataset.addItem];
            blk.data = blk.data || {};
            blk.data.items = blk.data.items || [];
            blk.data.items.push({}); editing.dirty = true; renderPageEditor(api);
        }));
        body.querySelectorAll('[data-remove-item]').forEach((b) => b.addEventListener('click', () => {
            d.blocks[+b.dataset.removeItem].data.items.splice(+b.dataset.item, 1); editing.dirty = true; renderPageEditor(api);
        }));
    }

    function savePage(api) {
        const isNew = !editing.id;
        const url = isNew ? H.routes.pages : H.routes.pages + '/' + editing.id;
        api.setStatus('Saving page…', 'saving');
        request(isNew ? 'POST' : 'PUT', url, { draft: editing.draft }).then((res) => {
            pages = res.pages;
            editing = { id: res.page.id, kind: res.page.kind, draft: clone(res.page.draft), published: res.page.is_published };
            renderPageEditor(api);
            toast(api, res.message);
            W.hasUnpublished = true;
            previewPath(api, res.page.draft.path);
        }).catch((e) => toast(api, e.message, 'error'));
    }

    /* ═════════════════ Dashboard (connected site) ═════════════════ */
    function renderDashboard(api) {
        const c = H.connection || {};
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

    if (!H.enabled) return;

    ext.renderers.pages = renderPages;
    ext.renderers.site_forms = renderSiteForms;
    ext.renderers.dashboard = renderDashboard;
    ext.onPublished = () => {
        pages = pages.map((p) => ({ ...p, is_published: true, has_changes: false }));
    };

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
    ext.schemas.branding = {
        fields: [
            { key: 'company_name', label: 'School name', type: 'text' },
            { key: 'short_name', label: 'Short name', type: 'text' },
            { key: 'tagline', label: 'Tagline', type: 'text' },
            { key: 'website_description', label: 'Website description', type: 'textarea' },
            { key: 'copyright', label: 'Footer copyright line', type: 'text' },
        ],
    };
    ext.schemas.contact = {
        fields: [
            { key: 'phone', label: 'Phone', type: 'text' },
            { key: 'email', label: 'Email', type: 'email' },
            { key: 'whatsapp', label: 'WhatsApp link (https://wa.me/…)', type: 'url' },
            { key: 'address', label: 'Address', type: 'textarea' },
            { key: 'working_hours', label: 'Opening hours', type: 'text' },
        ],
    };
    ext.schemas.analytics = {
        fields: [
            { key: 'google_analytics', label: 'Google Analytics 4 ID (G-…)', type: 'text' },
            { key: 'google_tag_manager', label: 'Google Tag Manager ID (GTM-…)', type: 'text' },
            { key: 'facebook_pixel', label: 'Meta Pixel ID', type: 'text' },
            { key: 'microsoft_clarity', label: 'Microsoft Clarity project ID', type: 'text' },
        ],
    };
    ext.schemas.custom_code = {
        note: 'Custom CSS is added to every page. Scripts run on every page of the live website — only paste code from sources you trust.',
        fields: [
            { key: 'custom_css', label: 'Custom CSS', type: 'code' },
            { key: 'head_scripts', label: 'Head scripts (HTML)', type: 'code' },
            { key: 'footer_scripts', label: 'Footer scripts (HTML)', type: 'code' },
        ],
    };
})();
