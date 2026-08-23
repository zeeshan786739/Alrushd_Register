<script>
(function () {
    function initAudiencePicker(root) {
        var scope = root || document;
        scope.querySelectorAll('[data-audience-picker]').forEach(function (picker) {
            if (picker.dataset.bound === '1') return;
            picker.dataset.bound = '1';

            var sourceInput = picker.querySelector('[data-audience-source]');
            var panels = picker.querySelectorAll('[data-audience-panel]');
            var integrationFilter = picker.querySelector('[data-audience-integration-filter]');
            var leadFilters = picker.querySelectorAll('[data-audience-lead-filters]');
            var formSelect = picker.querySelector('[data-audience-form-select]');
            var searchInput = picker.querySelector('[data-audience-search]');
            var searchResults = picker.querySelector('[data-audience-search-results]');
            var selectedWrap = picker.querySelector('[data-audience-selected]');
            var preflightUrl = @json(route('admin.email.audience.preflight'));
            var searchUrl = @json(route('admin.email.audience.search'));
            var formsUrl = @json(route('admin.email.audience.forms'));
            var debounceTimer;

            function currentSource() {
                return sourceInput.value || 'leads';
            }

            function showPanel(source) {
                panels.forEach(function (panel) {
                    var keys = (panel.getAttribute('data-audience-panel') || '').split(/\s+/);
                    panel.hidden = keys.indexOf(source) === -1;
                });
                if (integrationFilter) {
                    integrationFilter.hidden = source !== 'integration_leads';
                }
                leadFilters.forEach(function (block) {
                    block.hidden = source === 'customers';
                });
                if (source !== 'integration_leads') {
                    picker.querySelectorAll('input[name="lead_source"]').forEach(function (radio) {
                        radio.checked = radio.value === '';
                    });
                }
            }

            function formData() {
                var form = picker.closest('form');
                return form ? new FormData(form) : new FormData();
            }

            function refreshPreflight() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    var params = new URLSearchParams(formData());
                    fetch(preflightUrl + '?' + params.toString(), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            var card = picker.querySelector('[data-audience-preflight]');
                            if (!card) return;
                            card.querySelector('[data-pf-eligible]').textContent = data.eligible ?? 0;
                            card.querySelector('[data-pf-selected]').textContent = data.selected ?? 0;
                            card.querySelector('[data-pf-unsub]').textContent = (data.unsubscribed ?? 0) + (data.suppressed ?? 0);
                            card.querySelector('[data-pf-invalid]').textContent = data.invalid ?? 0;
                            card.querySelector('[data-pf-message]').textContent =
                                (data.eligible ?? 0) + ' contacts are eligible after suppressions and validation.';

                            var sampleWrap = card.querySelector('[data-pf-sample]');
                            var sampleList = card.querySelector('[data-pf-sample-list]');
                            if (sampleWrap && sampleList && data.sample && data.sample.length) {
                                sampleWrap.hidden = false;
                                sampleList.innerHTML = '';
                                data.sample.forEach(function (row) {
                                    var li = document.createElement('li');
                                    li.textContent = (row.name || row.email) + ' · ' + row.email;
                                    sampleList.appendChild(li);
                                });
                            } else if (sampleWrap) {
                                sampleWrap.hidden = true;
                            }
                        })
                        .catch(function () {});
                }, 350);
            }

            picker.querySelectorAll('[data-audience-source-option]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    sourceInput.value = btn.dataset.audienceSourceOption;
                    picker.querySelectorAll('[data-audience-source-option]').forEach(function (chip) {
                        chip.classList.toggle('is-active', chip === btn);
                    });
                    showPanel(sourceInput.value);
                    refreshPreflight();
                });
            });

            picker.querySelectorAll('[data-audience-input]').forEach(function (input) {
                input.addEventListener('input', refreshPreflight);
                input.addEventListener('change', refreshPreflight);
            });

            if (formSelect) {
                fetch(formsUrl, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (payload) {
                        (payload.data || []).forEach(function (form) {
                            var opt = document.createElement('option');
                            opt.value = form.id;
                            opt.textContent = form.name;
                            opt.selected = String(form.id) === String(formSelect.getAttribute('data-selected-form-id') || '');
                            formSelect.appendChild(opt);
                        });
                    })
                    .catch(function () {});
            }

            function renderSelectedChip(type, item) {
                var chip = document.createElement('span');
                chip.className = 'em-selected-chip';
                chip.innerHTML = '<span>' + item.name + ' · ' + item.email + '</span><button type="button" aria-label="Remove">&times;</button>';
                chip.querySelector('button').addEventListener('click', function () {
                    chip.remove();
                    var hidden = selectedWrap.querySelector('[data-' + type + '-id="' + item.id + '"]');
                    if (hidden) hidden.remove();
                    refreshPreflight();
                });
                selectedWrap.appendChild(chip);
            }

            function addSelection(item) {
                if (item.type === 'customer') {
                    if (selectedWrap.querySelector('[data-customer-id="' + item.id + '"]')) return;
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'customer_ids[]';
                    input.value = item.id;
                    input.dataset.customerId = item.id;
                    selectedWrap.appendChild(input);
                } else {
                    if (selectedWrap.querySelector('[data-lead-id="' + item.id + '"]')) return;
                    var leadInput = document.createElement('input');
                    leadInput.type = 'hidden';
                    leadInput.name = 'lead_ids[]';
                    leadInput.value = item.id;
                    leadInput.dataset.leadId = item.id;
                    selectedWrap.appendChild(leadInput);
                }
                renderSelectedChip(item.type === 'customer' ? 'customer' : 'lead', item);
                refreshPreflight();
            }

            if (searchInput && searchResults) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        var q = searchInput.value.trim();
                        if (q.length < 2) {
                            searchResults.innerHTML = '';
                            return;
                        }
                        Promise.all([
                            fetch(searchUrl + '?type=leads&q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }),
                            fetch(searchUrl + '?type=customers&q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }),
                        ]).then(function (results) {
                            var rows = (results[0].data || []).concat(results[1].data || []);
                            searchResults.innerHTML = '';
                            rows.forEach(function (item) {
                                var btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'em-audience-result';
                                btn.innerHTML = '<strong>' + item.name + '</strong><span>' + item.email + '</span><small>' + item.meta + '</small>';
                                btn.addEventListener('click', function () { addSelection(item); });
                                searchResults.appendChild(btn);
                            });
                            if (!rows.length) {
                                searchResults.innerHTML = '<p class="text-secondary-light text-sm mb-0 px-8">No contacts found.</p>';
                            }
                        });
                    }, 300);
                });
            }

            showPanel(currentSource());
            refreshPreflight();
        });
    }

    initAudiencePicker(document);

    if (!window.__adminPageLoadedHookAudience) {
        window.__adminPageLoadedHookAudience = true;
        document.addEventListener('admin:page-loaded', function (e) {
            initAudiencePicker(e.detail && e.detail.root ? e.detail.root : document);
        });
    }
})();
</script>
