/**
 * Al Rushd Website CMS Manager
 */
(function () {
    'use strict';

    const W = window.__WCM;
    if (!W) return;

    let cmsData = W.data;
    let currentModule = 'dashboard';
    let previewTimer = null;
    let dirty = false;

    const $ = (sel, ctx) => (ctx || document).querySelector(sel);
    const $$ = (sel, ctx) => [...(ctx || document).querySelectorAll(sel)];

    const editorBody = $('#wcmEditorBody');
    const moduleTitle = $('#wcmModuleTitle');
    const moduleDesc = $('#wcmModuleDesc');
    const statusEl = $('#wcmStatus');
    const statusText = $('#wcmStatusText');
    const previewFrame = $('#wcmPreviewFrame');

    /* ── Field schemas per module ── */
    const SCHEMAS = {
        dashboard: { type: 'dashboard' },
        homepage_sections: { type: 'sections_order' },
        branding: {
            fields: [
                { key: 'company_name', label: 'Company Name', type: 'text' },
                { key: 'short_name', label: 'Short Name', type: 'text' },
                { key: 'tagline', label: 'Tagline', type: 'text' },
                { key: 'logo', label: 'Logo', type: 'image' },
                { key: 'logo_dark', label: 'Dark Logo', type: 'image' },
                { key: 'logo_light', label: 'Light Logo', type: 'image' },
                { key: 'footer_logo', label: 'Footer Logo', type: 'image' },
                { key: 'favicon', label: 'Favicon', type: 'image' },
                { key: 'website_title', label: 'Website Title', type: 'text' },
                { key: 'browser_title', label: 'Browser Title', type: 'text' },
                { key: 'website_description', label: 'Website Description', type: 'textarea' },
                { key: 'default_language', label: 'Default Language', type: 'text' },
                { key: 'copyright', label: 'Copyright', type: 'text' },
                { key: 'company_registration', label: 'Company Registration', type: 'text' },
            ],
        },
        theme: {
            fields: [
                { key: 'primary', label: 'Primary Color', type: 'color' },
                { key: 'secondary', label: 'Secondary Color', type: 'color' },
                { key: 'accent', label: 'Accent / Gold', type: 'color' },
                { key: 'background', label: 'Background', type: 'color' },
                { key: 'text', label: 'Text Color', type: 'color' },
                { key: 'heading', label: 'Heading Color', type: 'color' },
                { key: 'button', label: 'Button Color', type: 'color' },
                { key: 'button_hover', label: 'Button Hover', type: 'color' },
                { key: 'navbar', label: 'Navbar Color', type: 'color' },
                { key: 'footer', label: 'Footer Color', type: 'color' },
                { key: 'card_bg', label: 'Card Background', type: 'color' },
                { key: 'border', label: 'Border Color', type: 'color' },
                { key: 'cream', label: 'Cream Background', type: 'color' },
            ],
        },
        typography: {
            fields: [
                { key: 'heading_font', label: 'Heading Font', type: 'select', options: ['Libre Baskerville', 'Georgia', 'Playfair Display', 'Inter'] },
                { key: 'body_font', label: 'Body Font', type: 'select', options: ['Inter', 'Roboto', 'Open Sans', 'Lato'] },
                { key: 'button_font', label: 'Button Font', type: 'select', options: ['Inter', 'Roboto', 'Open Sans'] },
                { key: 'base_size', label: 'Base Font Size (px)', type: 'number' },
                { key: 'heading_weight', label: 'Heading Weight', type: 'select', options: ['600', '700', '800'] },
                { key: 'line_height', label: 'Line Height', type: 'text' },
                { key: 'container_width', label: 'Container Width (px)', type: 'number' },
            ],
        },
        navbar: {
            fields: [
                { key: 'sticky', label: 'Sticky Navbar', type: 'toggle' },
                { key: 'transparent', label: 'Transparent at Top', type: 'toggle' },
                { key: 'glass_effect', label: 'Glass Effect on Scroll', type: 'toggle' },
                { key: 'height', label: 'Navbar Height (px)', type: 'number' },
                { key: 'show_search', label: 'Show Search', type: 'toggle' },
                { key: 'show_login', label: 'Show Login', type: 'toggle' },
                { key: 'show_apply', label: 'Show Apply Button', type: 'toggle' },
                { key: 'login_text', label: 'Login Text', type: 'text' },
                { key: 'login_url', label: 'Login URL', type: 'text' },
                { key: 'apply_text', label: 'Apply Button Text', type: 'text' },
                { key: 'apply_url', label: 'Apply Button URL', type: 'text' },
                { key: 'apply_color', label: 'Apply Button Color', type: 'color' },
            ],
            repeater: { key: 'menu_items', label: 'Menu Items', fields: [
                { key: 'label', label: 'Label', type: 'text' },
                { key: 'href', label: 'Link', type: 'text' },
                { key: 'enabled', label: 'Enabled', type: 'toggle' },
            ]},
        },
        hero: {
            toggle: 'enabled',
            fields: [
                { key: 'badge', label: 'Badge Text', type: 'text' },
                { key: 'badge_icon', label: 'Badge Icon (FA class)', type: 'text' },
                { key: 'heading', label: 'Main Heading', type: 'text' },
                { key: 'heading_highlight', label: 'Highlighted Words', type: 'text' },
                { key: 'subheading', label: 'Sub Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
                { key: 'overlay_opacity', label: 'Overlay Opacity', type: 'text' },
                { key: 'primary_btn_text', label: 'Primary Button Text', type: 'text' },
                { key: 'primary_btn_url', label: 'Primary Button URL', type: 'text' },
                { key: 'secondary_btn_text', label: 'Secondary Button Text', type: 'text' },
                { key: 'secondary_btn_url', label: 'Secondary Button URL', type: 'text' },
            ],
            images: { key: 'images', label: 'Hero Images', max: 3 },
            repeater: { key: 'float_cards', label: 'Floating Cards', fields: [
                { key: 'icon', label: 'Icon', type: 'text' },
                { key: 'title', label: 'Title', type: 'text' },
                { key: 'subtitle', label: 'Subtitle', type: 'text' },
            ]},
        },
        about: {
            toggle: 'enabled',
            fields: [
                { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
                { key: 'image', label: 'Section Image', type: 'image' },
                { key: 'badge', label: 'Image Badge', type: 'text' },
            ],
            repeater: { key: 'features', label: 'Features', fields: [
                { key: 'text', label: 'Feature', type: 'text' },
                { key: 'icon', label: 'Icon', type: 'text' },
            ]},
        },
        features: {
            toggle: 'enabled',
            fields: [
                { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
            ],
            repeater: { key: 'items', label: 'Feature Cards', fields: [
                { key: 'icon', label: 'Icon', type: 'text' },
                { key: 'title', label: 'Title', type: 'text' },
                { key: 'desc', label: 'Description', type: 'textarea' },
            ]},
        },
        forms_section: {
            toggle: 'enabled',
            fields: [
                { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
                { key: 'use_form_center', label: 'Use Form Center Forms', type: 'toggle' },
            ],
            note: 'Form cards are managed in <a href="' + W.routes.formCenter + '">Form Center</a>. Enable "Landing page" placement on each form.',
        },
        statistics: {
            toggle: 'enabled',
            fields: [
                { key: 'animate', label: 'Counter Animation', type: 'toggle' },
            ],
            repeater: { key: 'items', label: 'Statistics', fields: [
                { key: 'value', label: 'Value', type: 'number' },
                { key: 'suffix', label: 'Suffix (+, %)', type: 'text' },
                { key: 'label', label: 'Label', type: 'text' },
            ]},
        },
        programs: {
            toggle: 'enabled',
            fields: [
                { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
            ],
            repeater: { key: 'items', label: 'Programs', fields: [
                { key: 'icon', label: 'Icon (FA)', type: 'text' },
                { key: 'title', label: 'Title', type: 'text' },
                { key: 'desc', label: 'Description', type: 'textarea' },
            ]},
        },
        gallery: {
            toggle: 'enabled',
            fields: [
                { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
            ],
            repeater: { key: 'items', label: 'Gallery Images', fields: [
                { key: 'image', label: 'Image', type: 'image' },
                { key: 'caption', label: 'Caption', type: 'text' },
            ]},
        },
        how_it_works: {
            toggle: 'enabled',
            fields: [
                { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
            ],
            list: { key: 'steps', label: 'Steps' },
        },
        testimonials: {
            toggle: 'enabled',
            fields: [
                { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
                { key: 'auto_slider', label: 'Auto Slider', type: 'toggle' },
            ],
            repeater: { key: 'items', label: 'Testimonials', fields: [
                { key: 'name', label: 'Name', type: 'text' },
                { key: 'role', label: 'Role', type: 'text' },
                { key: 'text', label: 'Review', type: 'textarea' },
                { key: 'rating', label: 'Rating (1-5)', type: 'number' },
            ]},
        },
        faq: {
            toggle: 'enabled',
            fields: [
                { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
            ],
            repeater: { key: 'items', label: 'FAQ Items', fields: [
                { key: 'q', label: 'Question', type: 'text' },
                { key: 'a', label: 'Answer', type: 'textarea' },
            ]},
        },
        contact: {
            toggle: 'enabled',
            fields: [
                { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
                { key: 'heading', label: 'Heading', type: 'text' },
                { key: 'description', label: 'Description', type: 'textarea' },
                { key: 'email', label: 'Email', type: 'email' },
                { key: 'phone', label: 'Phone', type: 'text' },
                { key: 'whatsapp', label: 'WhatsApp', type: 'text' },
                { key: 'address', label: 'Address', type: 'textarea' },
                { key: 'map_embed', label: 'Google Maps Embed URL', type: 'textarea' },
                { key: 'working_hours', label: 'Working Hours', type: 'text' },
                { key: 'support_email', label: 'Support Email', type: 'email' },
            ],
        },
        footer: {
            fields: [
                { key: 'description', label: 'Footer Description', type: 'textarea' },
                { key: 'privacy_url', label: 'Privacy Policy URL', type: 'text' },
                { key: 'terms_url', label: 'Terms URL', type: 'text' },
                { key: 'cookies_url', label: 'Cookies URL', type: 'text' },
                { key: 'newsletter_enabled', label: 'Newsletter Enabled', type: 'toggle' },
                { key: 'newsletter_text', label: 'Newsletter Text', type: 'text' },
            ],
            repeater: { key: 'quick_links', label: 'Quick Links', fields: [
                { key: 'label', label: 'Label', type: 'text' },
                { key: 'href', label: 'URL', type: 'text' },
            ]},
        },
        social: {
            fields: [
                { key: 'facebook', label: 'Facebook URL', type: 'url' },
                { key: 'instagram', label: 'Instagram URL', type: 'url' },
                { key: 'linkedin', label: 'LinkedIn URL', type: 'url' },
                { key: 'twitter', label: 'Twitter URL', type: 'url' },
                { key: 'youtube', label: 'YouTube URL', type: 'url' },
                { key: 'tiktok', label: 'TikTok URL', type: 'url' },
                { key: 'whatsapp', label: 'WhatsApp URL', type: 'url' },
                { key: 'threads', label: 'Threads URL', type: 'url' },
            ],
            toggles: { prefix: 'enabled', label: 'Enable Platforms', keys: ['facebook','instagram','linkedin','twitter','youtube','tiktok','whatsapp','threads'] },
        },
        buttons: {
            fields: [
                { key: 'primary_radius', label: 'Border Radius (px)', type: 'number' },
                { key: 'padding_x', label: 'Padding X (px)', type: 'number' },
                { key: 'padding_y', label: 'Padding Y (px)', type: 'number' },
                { key: 'shadow', label: 'Shadow', type: 'toggle' },
                { key: 'hover_lift', label: 'Hover Lift', type: 'toggle' },
            ],
        },
        animations: {
            fields: [
                { key: 'fade', label: 'Fade Animations', type: 'toggle' },
                { key: 'slide', label: 'Slide Animations', type: 'toggle' },
                { key: 'scale', label: 'Scale Animations', type: 'toggle' },
                { key: 'parallax', label: 'Parallax', type: 'toggle' },
                { key: 'counter', label: 'Counter Animation', type: 'toggle' },
                { key: 'floating_images', label: 'Floating Images', type: 'toggle' },
                { key: 'card_hover', label: 'Card Hover', type: 'toggle' },
                { key: 'button_hover', label: 'Button Hover', type: 'toggle' },
                { key: 'scroll_reveal', label: 'Scroll Reveal', type: 'toggle' },
            ],
        },
        seo: {
            fields: [
                { key: 'meta_title', label: 'Meta Title', type: 'text' },
                { key: 'meta_description', label: 'Meta Description', type: 'textarea' },
                { key: 'meta_keywords', label: 'Keywords', type: 'textarea' },
                { key: 'og_image', label: 'Open Graph Image', type: 'image' },
                { key: 'twitter_card', label: 'Twitter Card Type', type: 'select', options: ['summary', 'summary_large_image'] },
                { key: 'canonical_url', label: 'Canonical URL', type: 'url' },
                { key: 'robots', label: 'Robots', type: 'text' },
                { key: 'google_verification', label: 'Google Verification', type: 'text' },
            ],
        },
        analytics: {
            fields: [
                { key: 'google_analytics', label: 'Google Analytics ID', type: 'text' },
                { key: 'google_tag_manager', label: 'Google Tag Manager (script)', type: 'textarea' },
                { key: 'facebook_pixel', label: 'Facebook Pixel', type: 'textarea' },
                { key: 'hotjar', label: 'Hotjar', type: 'text' },
                { key: 'microsoft_clarity', label: 'Microsoft Clarity', type: 'text' },
            ],
        },
        custom_code: {
            fields: [
                { key: 'custom_css', label: 'Custom CSS', type: 'code' },
                { key: 'custom_js', label: 'Custom JavaScript', type: 'code' },
                { key: 'head_scripts', label: 'Head Scripts', type: 'code' },
                { key: 'footer_scripts', label: 'Footer Scripts', type: 'code' },
            ],
        },
        system: {
            fields: [
                { key: 'stripe_key', label: 'Stripe Public Key', type: 'text' },
                { key: 'payment_online', label: 'Online Payments Enabled', type: 'toggle' },
            ],
            note: 'Stripe secret is managed separately for security. Payment settings sync on publish.',
        },
    };

    function setStatus(msg, type) {
        if (statusText) statusText.textContent = msg;
        if (statusEl) {
            statusEl.classList.toggle('is-error', type === 'error');
            statusEl.classList.toggle('is-saving', type === 'saving');
        }
    }

    function getNested(obj, path) {
        return path.split('.').reduce((o, k) => (o && o[k] !== undefined ? o[k] : undefined), obj);
    }

    function setNested(obj, path, val) {
        const keys = path.split('.');
        let cur = obj;
        for (let i = 0; i < keys.length - 1; i++) {
            if (!cur[keys[i]]) cur[keys[i]] = {};
            cur = cur[keys[i]];
        }
        cur[keys[keys.length - 1]] = val;
    }

    function fieldHtml(module, field, value, path) {
        const id = 'wcm_' + path.replace(/\./g, '_');
        const v = value ?? '';
        switch (field.type) {
            case 'toggle':
                return `<div class="wcm-field wcm-field--toggle">
                    <label class="wcm-toggle"><input type="checkbox" data-path="${path}" ${v ? 'checked' : ''}><span class="wcm-toggle-slider"></span></label>
                    <span class="wcm-field-label">${field.label}</span></div>`;
            case 'color':
                return `<div class="wcm-field"><label class="wcm-field-label" for="${id}">${field.label}</label>
                    <div class="wcm-color-wrap"><input type="color" id="${id}" data-path="${path}" value="${v || '#0F274A'}">
                    <input type="text" class="wcm-input wcm-color-text" data-path="${path}" value="${v || ''}"></div></div>`;
            case 'textarea':
            case 'code':
                return `<div class="wcm-field"><label class="wcm-field-label" for="${id}">${field.label}</label>
                    <textarea id="${id}" class="wcm-input wcm-textarea ${field.type === 'code' ? 'wcm-code' : ''}" data-path="${path}" rows="${field.type === 'code' ? 8 : 3}">${esc(v)}</textarea></div>`;
            case 'select':
                return `<div class="wcm-field"><label class="wcm-field-label" for="${id}">${field.label}</label>
                    <select id="${id}" class="wcm-input" data-path="${path}">${(field.options || []).map(o => `<option value="${o}" ${v === o ? 'selected' : ''}>${o}</option>`).join('')}</select></div>`;
            case 'image':
                return `<div class="wcm-field wcm-field--image"><label class="wcm-field-label">${field.label}</label>
                    <div class="wcm-image-upload">${v ? `<img src="${esc(v)}" class="wcm-image-preview" alt="">` : '<div class="wcm-image-placeholder"><iconify-icon icon="solar:gallery-linear"></iconify-icon></div>'}
                    <input type="file" accept="image/*" data-path="${path}" data-upload="1">
                    <input type="hidden" data-path="${path}" value="${esc(v)}"></div></div>`;
            default:
                return `<div class="wcm-field"><label class="wcm-field-label" for="${id}">${field.label}</label>
                    <input type="${field.type || 'text'}" id="${id}" class="wcm-input" data-path="${path}" value="${esc(v)}"></div>`;
        }
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function renderRepeater(module, config, items, basePath) {
        items = items || [];
        let html = `<div class="wcm-repeater" data-repeater="${basePath}">
            <div class="wcm-repeater-header"><h4>${config.label}</h4>
            <button type="button" class="btn btn-sm btn-outline-primary-600 radius-8 wcm-repeater-add" data-repeater="${basePath}">+ Add</button></div>`;
        items.forEach((item, i) => {
            html += `<div class="wcm-repeater-item" data-index="${i}">
                <div class="wcm-repeater-item-header"><span>Item ${i + 1}</span>
                <button type="button" class="wcm-repeater-remove" data-repeater="${basePath}" data-index="${i}">&times;</button></div>
                <div class="wcm-repeater-fields">`;
            config.fields.forEach(f => {
                html += fieldHtml(module, f, item[f.key], `${basePath}.${i}.${f.key}`);
            });
            html += '</div></div>';
        });
        html += '</div>';
        return html;
    }

    const SECTION_LABELS = {
        hero: 'Hero Section',
        statistics: 'Statistics',
        about: 'About',
        features: 'Why Choose Us',
        forms_section: 'Forms',
        how_it_works: 'How It Works',
        programs: 'Programs',
        gallery: 'Gallery',
        testimonials: 'Testimonials',
        faq: 'FAQ',
        contact: 'Contact',
        cta: 'Call to Action',
    };

    function renderSectionsOrder() {
        const order = cmsData.sections_order || [];
        let html = `<div class="wcm-sections-order">
            <p class="wcm-note">Drag sections to reorder your homepage. Toggle visibility in each section's settings.</p>
            <ul class="wcm-sort-list" id="wcmSortList">`;
        order.forEach((id, i) => {
            const sectionData = cmsData[id] || {};
            const enabled = id === 'hero' || id === 'statistics' ? (sectionData.enabled !== false) : (sectionData.enabled !== false);
            html += `<li class="wcm-sort-item" draggable="true" data-id="${id}" data-index="${i}">
                <iconify-icon icon="solar:hamburger-menu-linear" class="wcm-sort-handle"></iconify-icon>
                <span class="wcm-sort-label">${SECTION_LABELS[id] || id}</span>
                <span class="wcm-sort-status ${enabled ? 'is-on' : 'is-off'}">${enabled ? 'Visible' : 'Hidden'}</span>
                <button type="button" class="wcm-sort-edit" data-goto="${id}">Edit</button>
            </li>`;
        });
        html += '</ul></div>';
        editorBody.innerHTML = html;
        bindSectionsSort();
        $$('[data-goto]', editorBody).forEach(btn => {
            btn.addEventListener('click', () => switchModule(btn.dataset.goto));
        });
    }

    function bindSectionsSort() {
        const list = $('#wcmSortList');
        if (!list) return;
        let dragItem = null;

        list.querySelectorAll('.wcm-sort-item').forEach(item => {
            item.addEventListener('dragstart', (e) => {
                dragItem = item;
                item.classList.add('is-dragging');
                e.dataTransfer.effectAllowed = 'move';
            });
            item.addEventListener('dragend', () => {
                item.classList.remove('is-dragging');
                dragItem = null;
                const order = [...list.querySelectorAll('.wcm-sort-item')].map(el => el.dataset.id);
                cmsData.sections_order = order;
                markDirty();
                schedulePreview();
            });
            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                if (!dragItem || dragItem === item) return;
                const rect = item.getBoundingClientRect();
                const after = e.clientY > rect.top + rect.height / 2;
                list.insertBefore(dragItem, after ? item.nextSibling : item);
            });
        });
    }

    function renderModule(moduleId) {
        const schema = SCHEMAS[moduleId];
        const mod = W.modules.find(m => m.id === moduleId);
        moduleTitle.textContent = mod ? mod.label : moduleId;
        if (moduleDesc) {
            moduleDesc.textContent = mod?.description || '';
            moduleDesc.hidden = !mod?.description;
        }

        // Hide reset on dashboard
        const resetBtn = $('#wcmResetSection');
        if (resetBtn) {
            resetBtn.style.display = moduleId === 'dashboard' ? 'none' : '';
        }

        if (schema?.type === 'sections_order') {
            renderSectionsOrder();
            return;
        }

        if (moduleId === 'dashboard') {
            const statusChip = W.hasUnpublished
                ? '<span class="wcm-dash-chip wcm-dash-chip--warn">Unpublished changes</span>'
                : '<span class="wcm-dash-chip wcm-dash-chip--ok">Published live</span>';
            const publishedLine = W.publishedAtHuman
                ? `<span class="wcm-dash-meta">Last published ${esc(W.publishedAtHuman)}</span>`
                : '<span class="wcm-dash-meta">Not published yet</span>';

            const versionsHtml = W.versionHistory.length
                ? `<div class="wcm-versions">
                    <div class="wcm-versions-head">
                        <h4>Version History</h4>
                        <span class="wcm-versions-count">${W.versionHistory.length} saved</span>
                    </div>
                    <ul class="wcm-versions-list">
                        ${W.versionHistory.map((v, i) => `
                            <li class="wcm-versions-item">
                                <div class="wcm-versions-info">
                                    <span class="wcm-versions-label">${esc(v.published_at || ('Version ' + (i + 1)))}</span>
                                    <span class="wcm-versions-note">Published snapshot</span>
                                </div>
                                <button type="button" class="wcm-restore-version" data-index="${i}">Restore</button>
                            </li>`).join('')}
                    </ul>
                </div>`
                : '';

            editorBody.innerHTML = `<div class="wcm-dashboard">
                <div class="wcm-dash-intro">
                    <div>
                        <h3 class="wcm-dash-welcome">Landing page studio</h3>
                        <p class="wcm-dash-welcome-sub">Edit content, theme, and SEO — preview updates on the right.</p>
                    </div>
                    <div class="wcm-dash-status-row">${statusChip}${publishedLine}</div>
                </div>
                <div class="wcm-dash-cards wcm-dash-cards--primary">
                    <div class="wcm-dash-card wcm-dash-card--primary">
                        <div class="wcm-dash-card-icon"><iconify-icon icon="solar:monitor-smartphone-linear"></iconify-icon></div>
                        <h3>Landing Page</h3>
                        <p>Edit hero, sections, and homepage content</p>
                        <button type="button" class="btn btn-primary-600 btn-sm radius-8" data-goto="hero">Edit Hero</button>
                    </div>
                    <div class="wcm-dash-card wcm-dash-card--primary">
                        <div class="wcm-dash-card-icon"><iconify-icon icon="solar:palette-linear"></iconify-icon></div>
                        <h3>Branding &amp; Theme</h3>
                        <p>Colors, logos, and typography</p>
                        <button type="button" class="btn btn-primary-600 btn-sm radius-8" data-goto="theme">Edit Theme</button>
                    </div>
                </div>
                <div class="wcm-dash-cards wcm-dash-cards--secondary">
                    <div class="wcm-dash-card">
                        <div class="wcm-dash-card-icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></div>
                        <h3>SEO</h3>
                        <p>Meta tags and search settings</p>
                        <button type="button" class="btn btn-primary-600 btn-sm radius-8" data-goto="seo">Edit SEO</button>
                    </div>
                    <div class="wcm-dash-card">
                        <div class="wcm-dash-card-icon"><iconify-icon icon="solar:document-text-linear"></iconify-icon></div>
                        <h3>Forms</h3>
                        <p>Managed in Form Center</p>
                        <a href="${W.routes.formCenter}" class="btn btn-outline-primary-600 btn-sm radius-8">Open Form Center</a>
                    </div>
                </div>
                ${versionsHtml}
            </div>`;
            bindDashboard();
            return;
        }

        if (!schema) {
            editorBody.innerHTML = '<p class="wcm-empty">Module not configured.</p>';
            return;
        }

        const data = cmsData[moduleId] || {};
        let html = '<div class="wcm-form-grid">';

        if (schema.toggle) {
            html += fieldHtml(moduleId, { label: 'Section Enabled', type: 'toggle' }, data[schema.toggle], `${moduleId}.${schema.toggle}`);
        }
        if (schema.note) {
            html += `<div class="wcm-note">${schema.note}</div>`;
        }
        (schema.fields || []).forEach(f => {
            html += fieldHtml(moduleId, f, data[f.key], `${moduleId}.${f.key}`);
        });
        if (schema.toggles) {
            schema.toggles.keys.forEach(k => {
                const enabled = (data[schema.toggles.prefix] || {})[k];
                html += fieldHtml(moduleId, { label: k.charAt(0).toUpperCase() + k.slice(1), type: 'toggle' }, enabled, `${moduleId}.${schema.toggles.prefix}.${k}`);
            });
        }
        if (schema.images) {
            const imgs = data[schema.images.key] || [];
            html += `<div class="wcm-field"><label class="wcm-field-label">${schema.images.label}</label>`;
            imgs.forEach((url, i) => {
                html += fieldHtml(moduleId, { label: 'Image ' + (i+1), type: 'image' }, url, `${moduleId}.${schema.images.key}.${i}`);
            });
            html += '</div>';
        }
        if (schema.list) {
            const steps = data[schema.list.key] || [];
            html += `<div class="wcm-field"><label class="wcm-field-label">${schema.list.label}</label>
                <textarea class="wcm-input" data-path="${moduleId}.${schema.list.key}" rows="4" placeholder="One step per line">${steps.join('\n')}</textarea></div>`;
        }
        html += '</div>';
        if (schema.repeater) {
            html += renderRepeater(moduleId, schema.repeater, data[schema.repeater.key], `${moduleId}.${schema.repeater.key}`);
        }
        editorBody.innerHTML = html;
        bindEditorEvents();
    }

    function bindDashboard() {
        $$('[data-goto]', editorBody).forEach(btn => {
            btn.addEventListener('click', () => switchModule(btn.dataset.goto));
        });
        $$('.wcm-restore-version', editorBody).forEach(btn => {
            btn.addEventListener('click', () => restoreVersion(parseInt(btn.dataset.index, 10)));
        });
    }

    function bindEditorEvents() {
        editorBody.querySelectorAll('[data-path]').forEach(el => {
            if (el.dataset.upload) return;
            const ev = el.type === 'checkbox' ? 'change' : 'input';
            el.addEventListener(ev, () => {
                let val = el.type === 'checkbox' ? el.checked : el.value;
                if (el.dataset.path.endsWith('.steps') && el.tagName === 'TEXTAREA') {
                    val = el.value.split('\n').map(s => s.trim()).filter(Boolean);
                }
                setNested(cmsData, el.dataset.path, val);
                markDirty();
                schedulePreview();
            });
        });

        editorBody.querySelectorAll('.wcm-color-wrap input[type="color"]').forEach(el => {
            el.addEventListener('input', () => {
                const text = el.parentElement.querySelector('.wcm-color-text');
                if (text) text.value = el.value;
                setNested(cmsData, el.dataset.path, el.value);
                markDirty();
                schedulePreview();
            });
        });

        editorBody.querySelectorAll('input[data-upload]').forEach(input => {
            input.addEventListener('change', () => uploadImage(input));
        });

        editorBody.querySelectorAll('.wcm-repeater-add').forEach(btn => {
            btn.addEventListener('click', () => {
                const path = btn.dataset.repeater;
                const arr = getNested(cmsData, path) || [];
                const schema = Object.values(SCHEMAS).find(s => s.repeater && `${currentModule}.${s.repeater.key}` === path);
                const empty = {};
                (schema?.repeater?.fields || []).forEach(f => { empty[f.key] = ''; });
                arr.push(empty);
                setNested(cmsData, path, arr);
                renderModule(currentModule);
                markDirty();
            });
        });

        editorBody.querySelectorAll('.wcm-repeater-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                const path = btn.dataset.repeater;
                const idx = parseInt(btn.dataset.index, 10);
                const arr = [...(getNested(cmsData, path) || [])];
                arr.splice(idx, 1);
                setNested(cmsData, path, arr);
                renderModule(currentModule);
                markDirty();
            });
        });
    }

    function markDirty() {
        dirty = true;
        setStatus('Unsaved changes', 'saving');
    }

    function schedulePreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(pushPreview, 600);
    }

    function pushPreview() {
        fetch(W.routes.preview, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ data: cmsData }),
        }).then(() => refreshPreview());
    }

    function refreshPreview() {
        if (previewFrame) previewFrame.src = previewFrame.src.split('?')[0] + '?cms_preview=1&t=' + Date.now();
    }

    function uploadImage(input) {
        const file = input.files[0];
        if (!file) return;
        const fd = new FormData();
        fd.append('file', file);
        fd.append('field', input.dataset.path);
        fetch(W.routes.upload, { method: 'POST', headers: { 'X-CSRF-TOKEN': W.csrf, 'Accept': 'application/json' }, body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    setNested(cmsData, input.dataset.path, res.url);
                    renderModule(currentModule);
                    markDirty();
                    schedulePreview();
                }
            });
    }

    function saveDraft() {
        setStatus('Saving...', 'saving');
        return fetch(W.routes.draft, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ data: cmsData }),
        }).then(r => r.json()).then(res => {
            dirty = false;
            setStatus(res.message || 'Draft saved', 'ok');
        });
    }

    function publish() {
        setStatus('Publishing...', 'saving');
        return fetch(W.routes.publish, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ data: cmsData }),
        }).then(r => r.json()).then(res => {
            dirty = false;
            setStatus(res.message || 'Published!', 'ok');
            refreshPreview();
            setTimeout(() => location.reload(), 1200);
        });
    }

    function discard() {
        if (!confirm('Discard draft and restore last published version?')) return;
        fetch(W.routes.discard, { method: 'POST', headers: { 'X-CSRF-TOKEN': W.csrf, 'Accept': 'application/json' } })
            .then(r => r.json()).then(res => {
                cmsData = res.data;
                renderModule(currentModule);
                dirty = false;
                setStatus('Draft discarded', 'ok');
                refreshPreview();
            });
    }

    function resetSection() {
        if (!confirm('Reset this section to defaults?')) return;
        fetch(W.routes.reset, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ section: currentModule }),
        }).then(r => r.json()).then(res => {
            cmsData = res.data;
            renderModule(currentModule);
            markDirty();
            schedulePreview();
        });
    }

    function restoreVersion(index) {
        fetch(W.routes.restore, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': W.csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ index }),
        }).then(r => r.json()).then(res => {
            cmsData = res.data;
            renderModule(currentModule);
            markDirty();
        });
    }

    function switchModule(id) {
        currentModule = id;
        $$('.wcm-nav-item').forEach(el => el.classList.toggle('is-active', el.dataset.module === id));
        renderModule(id);
    }

    $$('.wcm-nav-item').forEach(btn => btn.addEventListener('click', () => switchModule(btn.dataset.module)));
    $('#wcmSaveDraft')?.addEventListener('click', saveDraft);
    $('#wcmPublish')?.addEventListener('click', publish);
    $('#wcmDiscard')?.addEventListener('click', discard);
    $('#wcmResetSection')?.addEventListener('click', resetSection);
    $('#wcmRefreshPreview')?.addEventListener('click', refreshPreview);
    $('#wcmSidebarToggle')?.addEventListener('click', () => $('#wcmSidebar')?.classList.toggle('is-collapsed'));

    $$('.wcm-device').forEach(btn => {
        btn.addEventListener('click', () => {
            $$('.wcm-device').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            const wrap = $('.wcm-preview-frame-wrap');
            if (wrap) wrap.style.maxWidth = btn.dataset.width;
        });
    });

    renderModule('dashboard');
})();
