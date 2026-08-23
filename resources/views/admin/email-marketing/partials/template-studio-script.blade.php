<script>
(function () {
    var STARTERS = {
        welcome: '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:24px;"><h1 style="color:#0f274a;">Welcome, @{{name}}!</h1><p>Thank you for your interest in our school. We are delighted to connect with you.</p><p>If you have any questions, simply reply to this email.</p><p style="font-size:12px;color:#666;"><a href="@{{unsubscribe_url}}">Unsubscribe</a></p></div>',
        openday: '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:24px;"><h1 style="color:#0f274a;">You\'re invited to our Open Day</h1><p>Hello @{{name}},</p><p>We would love to welcome you and your family to explore our campus, meet our teachers, and discover what makes our school special.</p><p style="text-align:center;margin:28px 0;"><a href="#" style="background:#0f274a;color:#fff;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:bold;">Reserve your place</a></p><p style="font-size:12px;color:#666;"><a href="@{{unsubscribe_url}}">Unsubscribe</a></p></div>',
        reminder: '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:24px;"><h2 style="color:#0f274a;">Friendly reminder</h2><p>Hi @{{name}},</p><p>This is a gentle reminder about your upcoming appointment with our admissions team.</p><p>We look forward to speaking with you soon.</p><p style="font-size:12px;color:#666;"><a href="@{{unsubscribe_url}}">Unsubscribe</a></p></div>',
        newsletter: '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:24px;"><h1 style="color:#0f274a;">School Newsletter</h1><p>Hello @{{name}},</p><p>Here are the latest updates from our school community:</p><ul><li>Upcoming events and activities</li><li>Student achievements</li><li>Important dates for families</li></ul><p style="font-size:12px;color:#666;"><a href="@{{unsubscribe_url}}">Unsubscribe</a></p></div>',
    };

    var BLOCKS = {
        heading: '<h2 style="color:#0f274a;font-family:Arial,sans-serif;">Your heading here</h2>',
        paragraph: '<p style="font-family:Arial,sans-serif;line-height:1.6;">Write your message here. Personalise with @{{name}}.</p>',
        button: '<p style="text-align:center;margin:24px 0;"><a href="#" style="background:#0f274a;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block;">Call to action</a></p>',
        divider: '<hr style="border:0;border-top:1px solid #e5e7eb;margin:24px 0;">',
    };

    var SAMPLE = { name: 'Sarah Ahmed', email: 'sarah@example.com', company: 'Al-Rushd School', unsubscribe_url: '#' };

    function renderPreview(html) {
        var out = html || '';
        Object.keys(SAMPLE).forEach(function (key) {
            out = out.split('@{{' + key + '}}').join(SAMPLE[key]);
            out = out.split('{{' + key + '}}').join(SAMPLE[key]);
        });
        return out;
    }

    function initStudio(root) {
        root.querySelectorAll('[data-template-studio]').forEach(function (studio) {
            if (studio.dataset.bound === '1') return;
            studio.dataset.bound = '1';

            var editorId = studio.getAttribute('data-editor-id') || 'body_html';
            var editor = studio.querySelector('[data-studio-editor]') || document.getElementById(editorId);
            var preview = studio.querySelector('[data-studio-preview]');
            var templateInput = studio.querySelector('[data-template-id-input]');

            function refreshPreview() {
                if (!preview || !editor) return;
                preview.innerHTML = renderPreview(editor.value);
            }

            function insertAtCursor(text) {
                if (!editor) return;
                var start = editor.selectionStart || 0;
                var end = editor.selectionEnd || 0;
                var val = editor.value;
                editor.value = val.slice(0, start) + text + val.slice(end);
                editor.focus();
                editor.selectionStart = editor.selectionEnd = start + text.length;
                refreshPreview();
            }

            if (editor) {
                editor.addEventListener('input', refreshPreview);
                refreshPreview();
            }

            studio.querySelectorAll('[data-insert-tag]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    insertAtCursor('@{{' + btn.getAttribute('data-insert-tag') + '}}');
                });
            });

            studio.querySelectorAll('[data-insert-block]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var key = btn.getAttribute('data-insert-block');
                    insertAtCursor('\n' + (BLOCKS[key] || '') + '\n');
                });
            });

            studio.querySelectorAll('[data-starter]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var key = btn.getAttribute('data-starter');
                    if (editor && STARTERS[key]) {
                        editor.value = STARTERS[key];
                        if (templateInput) templateInput.value = '';
                        studio.querySelectorAll('[data-template-pick]').forEach(function (c) { c.classList.remove('is-active'); });
                        var blank = studio.querySelector('[data-template-pick=""]');
                        if (blank) blank.classList.add('is-active');
                        refreshPreview();
                    }
                });
            });

            studio.querySelectorAll('[data-template-pick]').forEach(function (card) {
                card.addEventListener('click', function () {
                    studio.querySelectorAll('[data-template-pick]').forEach(function (c) { c.classList.remove('is-active'); });
                    card.classList.add('is-active');
                    var id = card.getAttribute('data-template-pick') || '';
                    if (templateInput) templateInput.value = id;
                    var url = card.getAttribute('data-template-url');
                    if (!url) {
                        if (editor) editor.value = '';
                        refreshPreview();
                        return;
                    }
                    fetch(url, { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (editor) editor.value = data.body_html || '';
                            var subject = document.getElementById('subject');
                            if (subject && data.subject && !subject.value) subject.value = data.subject;
                            refreshPreview();
                        })
                        .catch(function () {});
                });
            });
        });
    }

    initStudio(document);

    if (!window.__adminPageLoadedHookStudio) {
        window.__adminPageLoadedHookStudio = true;
        document.addEventListener('admin:page-loaded', function (e) {
            initStudio(e.detail && e.detail.root ? e.detail.root : document);
        });
    }

    window.emRenderEmailPreview = renderPreview;
})();
</script>
