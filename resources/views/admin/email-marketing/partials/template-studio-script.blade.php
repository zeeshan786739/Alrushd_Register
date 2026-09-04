<script>
(() => {
    const sample = {name: 'Sarah Ahmed', email: 'sarah@example.com', company: 'Shining Star School', unsubscribe_url: '#'};
    const blocks = {
        heading: '<h2 style="margin:0 0 16px;color:#0f274a;font-family:Arial,sans-serif;font-size:28px;line-height:1.25">Your heading</h2>',
        paragraph: '<p style="margin:0 0 16px;color:#334155;font-family:Arial,sans-serif;font-size:16px;line-height:1.65">Write an engaging message for @{{name}}.</p>',
        columns: '<table style="width:100%;border-collapse:collapse;margin:20px 0"><tr><td style="width:50%;padding:16px;background-color:#f8fafc;vertical-align:top"><h3>First column</h3><p>Add content here.</p></td><td style="width:50%;padding:16px;background-color:#eef2f7;vertical-align:top"><h3>Second column</h3><p>Add content here.</p></td></tr></table>',
        divider: '<hr style="border:0;border-top:1px solid #dbe3ee;margin:28px 0">',
        spacer: '<div style="height:32px">&nbsp;</div>',
        footer: '<div style="margin-top:32px;padding-top:20px;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px;text-align:center">Sent by @{{company}} &middot; <a href="@{{unsubscribe_url}}">Unsubscribe</a></div>'
    };

    const escapeHtml = value => {
        const element = document.createElement('div');
        element.textContent = value;
        return element.innerHTML;
    };
    const validUrl = value => {
        try { return ['http:', 'https:', 'mailto:'].includes(new URL(value, location.origin).protocol); }
        catch (_) { return false; }
    };
    const render = html => {
        Object.entries(sample).forEach(([key, value]) => {
            html = (html || '').split('@{{' + key + '}}').join(value).split('{{' + key + '}}').join(value);
        });
        return html || '';
    };
    window.emRenderEmailPreview = render;

    function initialize(root) {
        root.querySelectorAll('[data-template-studio]').forEach(studio => {
            if (studio.dataset.bound) return;
            studio.dataset.bound = '1';

            const source = studio.querySelector('[data-studio-editor]');
            const editor = studio.querySelector('[data-visual-editor]');
            const preview = studio.querySelector('[data-studio-preview]');
            const dialog = studio.querySelector('[data-builder-modal]');
            const modal = studio.querySelector('[data-modal-form]');
            const modalBody = studio.querySelector('[data-modal-body]');
            const modalError = studio.querySelector('[data-modal-error]');
            const modalSubmit = studio.querySelector('[data-modal-submit]');
            let editorRange = null;
            let action = null;

            const sync = () => {
                if (!studio.classList.contains('is-source')) source.value = editor.innerHTML;
                preview.innerHTML = render(source.value);
            };
            const rememberSelection = () => {
                const selection = window.getSelection();
                if (selection.rangeCount && editor.contains(selection.anchorNode)) editorRange = selection.getRangeAt(0).cloneRange();
            };
            const restoreSelection = () => {
                editor.focus();
                if (!editorRange || !editor.contains(editorRange.commonAncestorContainer)) {
                    editorRange = document.createRange();
                    editorRange.selectNodeContents(editor);
                    editorRange.collapse(false);
                }
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(editorRange);
            };
            const closeDialog = () => {
                if (dialog.open) dialog.close();
                modalError.hidden = true;
                action = null;
            };
            const insertHtml = html => {
                closeDialog();
                restoreSelection();
                document.execCommand('insertHTML', false, html);
                sync();
                rememberSelection();
            };
            const field = (label, name, type, initial, help = '') => '<label class="em-builder-modal__field"><span>' + label + '</span><input class="form-control" type="' + type + '" name="' + name + '" value="' + escapeHtml(initial) + '">' + (help ? '<small>' + help + '</small>' : '') + '</label>';
            const formData = () => {
                const data = new FormData();
                modalBody.querySelectorAll('input,select,textarea').forEach(control => {
                    if (!control.name) return;
                    if (control.type === 'file') Array.from(control.files || []).forEach(file => data.append(control.name, file));
                    else data.append(control.name, control.value);
                });
                return data;
            };
            const openDialog = options => {
                rememberSelection();
                action = options.action;
                studio.querySelector('[data-modal-eyebrow]').textContent = options.eyebrow;
                studio.querySelector('[data-modal-title]').textContent = options.title;
                modalSubmit.textContent = options.submit;
                modalSubmit.disabled = false;
                modalBody.innerHTML = options.body;
                modalError.hidden = true;
                dialog.showModal();
                requestAnimationFrame(() => modalBody.querySelector('input')?.focus());
            };
            const executeAction = async () => {
                if (!action || modalSubmit.disabled) return;
                modalSubmit.disabled = true;
                try {
                    await action(formData());
                    closeDialog();
                    requestAnimationFrame(restoreSelection);
                } catch (error) {
                    modalError.textContent = error.message || 'Unable to complete this action.';
                    modalError.hidden = false;
                } finally {
                    modalSubmit.disabled = false;
                }
            };

            editor.innerHTML = source.value || '<div style="max-width:640px;margin:auto;padding:32px;font-family:Arial,sans-serif"><h1 style="color:#0f274a">Create something memorable</h1><p style="color:#475569">Start typing or add a content block.</p></div>';
            sync();
            try { document.execCommand('styleWithCSS', false, true); } catch (_) {}
            editor.addEventListener('input', sync);
            editor.addEventListener('keyup', rememberSelection);
            editor.addEventListener('mouseup', rememberSelection);
            source.addEventListener('input', () => { editor.innerHTML = source.value; preview.innerHTML = render(source.value); });

            studio.querySelectorAll('[data-command]').forEach(button => button.addEventListener('mousedown', event => {
                event.preventDefault(); restoreSelection(); document.execCommand(button.dataset.command, false, null); sync(); rememberSelection();
            }));
            studio.querySelector('[data-format-block]').addEventListener('change', event => { restoreSelection(); document.execCommand('formatBlock', false, event.target.value); sync(); });
            studio.querySelector('[data-text-color]').addEventListener('input', event => { restoreSelection(); document.execCommand('foreColor', false, event.target.value); sync(); });
            studio.querySelector('[data-bg-color]').addEventListener('input', event => { restoreSelection(); document.execCommand('hiliteColor', false, event.target.value); sync(); });

            studio.querySelectorAll('[data-insert-block]').forEach(button => button.addEventListener('click', () => {
                const type = button.dataset.insertBlock;
                if (type === 'button') {
                    openDialog({eyebrow: 'Button block', title: 'Add a call-to-action button', submit: 'Add button',
                        body: field('Button text', 'label', 'text', 'Learn more') + field('Destination URL', 'url', 'url', 'https://example.com', 'Use a full link beginning with https://.') + '<div class="em-builder-modal__grid"><label class="em-builder-modal__field"><span>Button color</span><input class="form-control" type="color" name="background" value="#0f274a"></label><label class="em-builder-modal__field"><span>Text color</span><input class="form-control" type="color" name="color" value="#ffffff"></label><label class="em-builder-modal__field"><span>Alignment</span><select class="form-select" name="alignment"><option value="left">Left</option><option value="center" selected>Center</option><option value="right">Right</option></select></label><label class="em-builder-modal__field"><span>Corners</span><select class="form-select" name="radius"><option value="4px">Subtle</option><option value="8px" selected>Rounded</option><option value="999px">Pill</option></select></label></div>',
                        action: async data => {
                            const label = data.get('label').trim(), url = data.get('url').trim();
                            if (!label || !validUrl(url)) throw new Error('Enter button text and a valid destination URL.');
                            insertHtml('<p style="margin:24px 0;text-align:' + data.get('alignment') + '"><a href="' + escapeHtml(url) + '" style="display:inline-block;background-color:' + data.get('background') + ';color:' + data.get('color') + ';padding:13px 26px;border-radius:' + data.get('radius') + ';text-decoration:none;font-weight:700">' + escapeHtml(label) + '</a></p>');
                        }});
                } else if (type === 'image') {
                    openDialog({eyebrow: 'Image block', title: 'Upload an image', submit: 'Upload and insert',
                        body: '<label class="em-builder-modal__drop"><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"><iconify-icon icon="solar:gallery-add-linear"></iconify-icon><strong>Choose an image from your device</strong><span>JPG, PNG, WebP or GIF · maximum 5 MB</span></label>' + field('Alternative text', 'alt', 'text', ''),
                        action: async data => {
                            const file = data.get('image');
                            if (!(file instanceof File) || !file.size) throw new Error('Choose an image to upload.');
                            modalSubmit.textContent = 'Uploading…';
                            const upload = new FormData(); upload.append('image', file);
                            const response = await fetch(studio.dataset.imageUploadUrl, {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, Accept: 'application/json'}, body: upload});
                            const result = await response.json();
                            if (!response.ok) throw new Error(result.message || Object.values(result.errors || {})[0]?.[0] || 'Image upload failed.');
                            insertHtml('<p style="margin:20px 0;text-align:center"><img src="' + escapeHtml(result.url) + '" alt="' + escapeHtml(data.get('alt') || '') + '" style="display:block;width:100%;max-width:640px;height:auto;border-radius:12px"></p>');
                        }});
                } else insertHtml(blocks[type] || '');
            }));

            studio.querySelectorAll('[data-insert-tag]').forEach(button => button.addEventListener('click', () => insertHtml('@{{' + button.dataset.insertTag + '}}')));
            const linkButton = studio.querySelector('[data-action="link"]');
            linkButton.addEventListener('mousedown', event => { event.preventDefault(); rememberSelection(); });
            linkButton.addEventListener('click', () => {
                const selected = editorRange && !editorRange.collapsed ? editorRange.toString() : '';
                openDialog({eyebrow: 'Text link', title: selected ? 'Link selected text' : 'Add a text link', submit: 'Apply link',
                    body: field('Link text', 'label', 'text', selected || 'Click here', selected ? 'Your selected text will be linked.' : '') + field('Destination URL', 'url', 'url', 'https://example.com'),
                    action: async data => {
                        const url = data.get('url').trim();
                        const label = data.get('label').trim() || 'Click here';
                        if (!validUrl(url)) throw new Error('Enter a valid website or email link.');
                        closeDialog();
                        if (editorRange && !editorRange.collapsed && editor.contains(editorRange.commonAncestorContainer)) {
                            const anchor = document.createElement('a');
                            anchor.href = url;
                            anchor.textContent = label;
                            editorRange.deleteContents();
                            editorRange.insertNode(anchor);
                            editorRange.setStartAfter(anchor);
                            editorRange.collapse(true);
                            restoreSelection();
                            sync();
                            rememberSelection();
                        } else {
                            insertHtml('<a href="' + escapeHtml(url) + '">' + escapeHtml(label) + '</a>');
                        }
                    }});
            });

            modalSubmit.addEventListener('click', event => { event.preventDefault(); event.stopPropagation(); executeAction(); });
            modal.addEventListener('keydown', event => {
                if (event.key === 'Enter' && !event.shiftKey && event.target.tagName !== 'TEXTAREA') {
                    event.preventDefault(); event.stopPropagation(); executeAction();
                }
            });
            dialog.addEventListener('click', event => {
                const closeButton = event.target.closest('[data-modal-close]');
                if (!closeButton) return;
                event.preventDefault();
                event.stopImmediatePropagation();
                action = null;
                closeDialog();
                requestAnimationFrame(restoreSelection);
            });
            dialog.addEventListener('cancel', event => { event.preventDefault(); action = null; closeDialog(); requestAnimationFrame(restoreSelection); });
            dialog.addEventListener('close', () => { action = null; modalSubmit.disabled = false; requestAnimationFrame(restoreSelection); });
            dialog.addEventListener('click', event => { if (event.target === dialog) { action = null; closeDialog(); requestAnimationFrame(restoreSelection); } });

            studio.querySelector('[data-toggle-source]').addEventListener('click', function () { if (studio.classList.contains('is-source')) editor.innerHTML = source.value; else source.value = editor.innerHTML; studio.classList.toggle('is-source'); this.classList.toggle('is-active'); sync(); });
            studio.querySelectorAll('[data-preview-size]').forEach(button => button.addEventListener('click', () => { studio.querySelectorAll('[data-preview-size]').forEach(item => item.classList.remove('is-active')); button.classList.add('is-active'); studio.querySelector('[data-preview-viewport]').classList.toggle('is-mobile', button.dataset.previewSize === 'mobile'); }));
            studio.querySelectorAll('[data-template-pick]').forEach(card => card.addEventListener('click', () => { const input = studio.querySelector('[data-template-id-input]'); if (input) input.value = card.dataset.templatePick || ''; if (!card.dataset.templateUrl) { source.value = ''; editor.innerHTML = ''; sync(); return; } fetch(card.dataset.templateUrl, {headers: {Accept: 'application/json'}}).then(response => response.json()).then(data => { source.value = data.body_html || ''; editor.innerHTML = source.value; sync(); }); }));
            studio.closest('form')?.addEventListener('submit', sync);
        });
    }
    initialize(document);
    document.addEventListener('admin:page-loaded', event => initialize(event.detail?.root || document));
})();
</script>
