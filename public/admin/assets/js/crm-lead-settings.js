/**
 * Lead Settings page. Every change saves straight away; each save returns the full
 * settings, and the page redraws from them, so what is shown is always what is stored.
 * Runs again after each pjax page swap; listeners live on the page's own root element.
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-cls-root]');
    if (!root || root.dataset.clsReady) return;
    root.dataset.clsReady = '1';

    var BASE = root.getAttribute('data-url').replace(/\/$/, '');
    var LEADS_URL = BASE.replace(/\/settings\/leads$/, '/leads');
    var PLACEMENTS = [
        { key: 'main', title: 'Always visible', hint: 'Shown at the top of the Leads page', icon: 'solar:eye-linear', empty: 'Drag a filter here to keep it in view' },
        { key: 'more', title: 'Under “More filters”', hint: 'One click away, keeps the page tidy', icon: 'solar:filter-linear', empty: 'Nothing here. The “More filters” link will not show' },
        { key: 'hidden', title: 'Hidden', hint: 'Not shown to your team', icon: 'solar:eye-closed-linear', empty: 'Drag a filter here to hide it' }
    ];

    var state = JSON.parse(root.getAttribute('data-state'));
    var panel = root.querySelector('[data-cls-panel]');
    var toasts = root.querySelector('[data-cls-toasts]');
    var savedEl = root.querySelector('[data-cls-saved]');
    var tab = readTab();
    var activeList = state.lists[0] ? state.lists[0].key : 'year_groups';
    var quickDraft = { param: '', value: '', label: '', touched: false };
    var pending = 0;
    var dragged = null;
    var menuEl = null;
    var dialogEl = null;
    var lastFocus = null;

    function readTab() {
        var value = new URLSearchParams(window.location.search).get('tab');
        return ['filters', 'quick', 'options'].indexOf(value) !== -1 ? value : 'filters';
    }

    function esc(value) {
        return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function icon(name) {
        return '<iconify-icon icon="' + esc(name) + '" aria-hidden="true"></iconify-icon>';
    }

    function plural(count, word) {
        return Number(count).toLocaleString() + ' ' + word + (Number(count) === 1 ? '' : 's');
    }

    /* ---------- saving ---------- */

    function setSaving(saving) {
        pending += saving ? 1 : -1;
        var busy = pending > 0;
        savedEl.classList.toggle('is-saving', busy);
        savedEl.querySelector('span').textContent = busy ? 'Saving…' : 'All changes saved';
    }

    function toast(message, isError) {
        var el = document.createElement('div');
        el.className = 'cls-toast' + (isError ? ' is-error' : '');
        el.innerHTML = icon(isError ? 'solar:danger-circle-linear' : 'solar:check-circle-linear') + '<span>' + esc(message) + '</span>';
        toasts.appendChild(el);
        setTimeout(function () { el.remove(); }, isError ? 6000 : 3000);
    }

    /** Resolves with the response, or rejects with a message the person can act on. */
    function send(method, path, body) {
        setSaving(true);
        var token = document.querySelector('meta[name="csrf-token"]');
        return fetch(BASE + path, {
            method: method,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body ? JSON.stringify(body) : undefined
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                if (response.ok && data.state) return data;
                if (response.status === 419 || response.status === 401) throw new Error('Your session has expired. Please refresh the page and try again.');
                if (response.status === 403) throw new Error('You do not have permission to change this.');
                var first = data.errors ? Object.keys(data.errors)[0] : null;
                throw new Error(first ? data.errors[first][0] : (data.message || 'Could not save that. Please try again.'));
            });
        }, function () {
            throw new Error('Could not reach the server. Check your connection and try again.');
        }).then(function (data) {
            setSaving(false);
            state = data.state;
            return data;
        }, function (error) {
            setSaving(false);
            throw error;
        });
    }

    /** Save, confirm, redraw. A failed save redraws too, putting things back as they were. */
    function save(method, path, body, quiet) {
        return send(method, path, body).then(function (data) {
            if (!quiet) toast(data.message);
            render();
        }, function (error) {
            toast(error.message, true);
            render();
        });
    }

    function saveLayout(body) {
        return save('PUT', '/layout', body, true);
    }

    function filtersPayload(filters) {
        return filters.map(function (filter) {
            return { key: filter.key, placement: filter.placement, label: filter.custom ? null : filter.label };
        });
    }

    function quickPayload(list) {
        return list.map(function (quick) {
            return { id: quick.id, label: quick.label, param: quick.param, value: quick.value };
        });
    }

    /* ---------- drawing ---------- */

    function render() {
        closeMenu();
        root.querySelectorAll('[data-cls-tab]').forEach(function (button) {
            var on = button.getAttribute('data-cls-tab') === tab;
            button.classList.toggle('is-active', on);
            button.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        if (tab === 'filters') panel.innerHTML = renderFilters();
        if (tab === 'quick') panel.innerHTML = renderQuick();
        if (tab === 'options') panel.innerHTML = renderOptions();
    }

    function renderFilters() {
        var columns = PLACEMENTS.map(function (place) {
            var items = state.filters.filter(function (filter) { return filter.placement === place.key; });
            var cards = items.map(function (filter) {
                var renamed = !filter.custom && filter.label !== filter.default_label;
                return '<div class="cls-card" draggable="true" data-drag="filter" data-key="' + esc(filter.key) + '">' +
                    '<span class="cls-grip" title="Drag to move">' + icon('solar:hamburger-menu-linear') + '</span>' +
                    '<span class="cls-card__label" data-label>' + esc(filter.label) +
                        (renamed ? '<span class="cls-card__original">Standard name: ' + esc(filter.default_label) + '</span>' : '') + '</span>' +
                    (filter.custom ? '<span class="cls-badge">Yours</span>' : '') +
                    '<button type="button" class="cls-icon-btn" data-action="filter-menu" data-key="' + esc(filter.key) + '" aria-label="Options for ' + esc(filter.label) + '">' + icon('solar:menu-dots-bold') + '</button>' +
                '</div>';
            }).join('');
            return '<section class="cls-col" aria-label="' + esc(place.title) + '">' +
                '<div class="cls-col__head"><span class="cls-col__icon">' + icon(place.icon) + '</span><div>' +
                    '<div class="cls-col__title">' + esc(place.title) + '<span class="cls-col__count">' + items.length + '</span></div>' +
                    '<div class="cls-col__hint">' + esc(place.hint) + '</div></div></div>' +
                '<div class="cls-col__list" data-drop="filter" data-placement="' + place.key + '">' +
                    (cards || '<div class="cls-col__empty">' + esc(place.empty) + '</div>') + '</div>' +
            '</section>';
        }).join('');

        return '<div class="cls-head"><div><h2>Filters on the Leads page</h2>' +
                '<p>Drag each filter to where your team needs it, or hide the ones you do not use. Hiding a filter never changes any lead.</p></div>' +
                '<div class="cls-head__actions">' +
                    '<button type="button" class="cls-btn cls-btn--quiet" data-action="restore" data-section="filters">' + icon('solar:restart-linear') + 'Restore standard</button>' +
                    '<button type="button" class="cls-btn cls-btn--primary" data-action="add-filter">' + icon('solar:add-circle-linear') + 'Add a filter</button>' +
                '</div></div>' +
            '<div class="cls-board">' + columns + '</div>';
    }

    function renderQuick() {
        var pills = state.quick_filters.map(function (quick) {
            return '<span class="cls-pill" draggable="true" data-drag="quick" data-id="' + esc(quick.id) + '" title="Drag to reorder">' +
                icon(quick.icon) + '<span>' + esc(quick.label) + '</span>' +
                '<button type="button" class="cls-pill__remove" data-action="remove-quick" data-id="' + esc(quick.id) + '" aria-label="Remove ' + esc(quick.label) + '">' + icon('solar:close-circle-linear') + '</button>' +
            '</span>';
        }).join('');

        var params = Object.keys(state.quick_choices);
        if (params.indexOf(quickDraft.param) === -1) {
            quickDraft = { param: params.indexOf('admission_intake') !== -1 ? 'admission_intake' : (params[0] || ''), value: '', label: '', touched: false };
        }
        var values = quickDraft.param ? state.quick_choices[quickDraft.param].options : [];
        if (!values.some(function (option) { return option.value === quickDraft.value; })) {
            quickDraft.value = values[0] ? values[0].value : '';
            if (!quickDraft.touched) quickDraft.label = '';
        }
        if (!quickDraft.touched && !quickDraft.label) quickDraft.label = suggestedLabel();

        return '<div class="cls-head"><div><h2>Quick buttons</h2><p>One-click shortcuts above the filters. Drag to reorder, or use × to remove one.</p></div>' +
                '<div class="cls-head__actions"><button type="button" class="cls-btn cls-btn--quiet" data-action="restore" data-section="quick">' + icon('solar:restart-linear') + 'Restore standard</button></div></div>' +
            '<div class="cls-panel"><div class="cls-pills" data-drop="quick">' + (pills || '<span class="cls-pills__empty">No quick buttons yet. Add one below.</span>') + '</div></div>' +
            '<form class="cls-panel" data-form="add-quick"><h3 class="cls-panel__title">Add a quick button</h3><p class="cls-panel__hint">Pick what the button should show. For example, Admission intake → Sept 28/29.</p>' +
                '<div class="cls-form-row">' +
                    '<div class="cls-field"><label for="cls-q-param">Filter</label><select id="cls-q-param" class="form-select" data-quick="param">' +
                        params.map(function (param) { return '<option value="' + esc(param) + '"' + (param === quickDraft.param ? ' selected' : '') + '>' + esc(state.quick_choices[param].label) + '</option>'; }).join('') + '</select></div>' +
                    '<div class="cls-field"><label for="cls-q-value">Shows leads with</label><select id="cls-q-value" class="form-select" data-quick="value">' +
                        values.map(function (option) { return '<option value="' + esc(option.value) + '"' + (option.value === quickDraft.value ? ' selected' : '') + '>' + esc(option.label) + '</option>'; }).join('') + '</select></div>' +
                    '<div class="cls-field"><label for="cls-q-label">Button name</label><input id="cls-q-label" class="form-control" data-quick="label" maxlength="40" value="' + esc(quickDraft.label) + '"></div>' +
                    '<button type="submit" class="cls-btn cls-btn--primary">' + icon('solar:add-circle-linear') + 'Add button</button>' +
                '</div></form>' +
            '<div class="cls-panel"><h3 class="cls-panel__title">Also on the Leads page</h3><p class="cls-panel__hint">Turn these off if your team does not use them.</p><div class="cls-toggles">' +
                toggle('show_date_picker', 'Date picker', 'The “Any date” button next to the quick buttons', state.show_date_picker) +
                toggle('show_sources', 'Lead source cards', 'Facebook, TikTok, Forms, Import and Manual totals', state.show_sources) +
            '</div></div>';
    }

    function toggle(name, title, hint, on) {
        return '<label class="cls-toggle"><span><strong>' + esc(title) + '</strong><small>' + esc(hint) + '</small></span>' +
            '<span class="cls-switch"><input type="checkbox" data-toggle="' + name + '"' + (on ? ' checked' : '') + '><span></span></span></label>';
    }

    function suggestedLabel() {
        if (!quickDraft.param) return '';
        var choice = state.quick_choices[quickDraft.param];
        var match = choice.options.filter(function (option) { return option.value === quickDraft.value; })[0];
        if (!match) return '';
        return quickDraft.param === 'admission_intake' ? match.label + ' admissions' : match.label;
    }

    function currentList() {
        return state.lists.filter(function (list) { return list.key === activeList; })[0] || null;
    }

    function renderOptions() {
        if (activeList !== 'year_groups' && !currentList()) activeList = state.lists[0].key;

        var builtIn = state.lists.filter(function (list) { return !list.custom_key; });
        var mine = state.lists.filter(function (list) { return list.custom_key; });
        var navButton = function (key, title, iconName, count) {
            return '<button type="button" class="cls-nav__btn' + (activeList === key ? ' is-active' : '') + '" data-action="pick-list" data-list="' + esc(key) + '"' + (activeList === key ? ' aria-current="true"' : '') + '>' +
                icon(iconName) + '<span class="cls-nav__name">' + esc(title) + '</span><span class="cls-nav__count">' + count + '</span></button>';
        };
        var listed = function (list) { return list.options.filter(function (option) { return !option.unlisted; }).length; };
        var nav = builtIn.map(function (list) { return navButton(list.key, list.title, list.icon, listed(list)); }).join('') +
            navButton('year_groups', state.year_groups.title, 'solar:square-academic-cap-linear', state.year_groups.options.length) +
            (mine.length ? '<div class="cls-nav__divider">Your filters</div>' + mine.map(function (list) { return navButton(list.key, list.title, list.icon, listed(list)); }).join('') : '');

        return '<div class="cls-head"><div><h2>Dropdown options</h2><p>Add, rename, reorder or remove the choices in each dropdown. Renaming an option updates every lead that uses it.</p></div></div>' +
            '<div class="cls-split"><nav class="cls-nav" aria-label="Dropdown lists">' + nav + '</nav><div>' +
                (activeList === 'year_groups' ? renderYearGroups() : renderList(currentList())) +
            '</div></div>';
    }

    function renderYearGroups() {
        var groups = state.year_groups;
        return '<div class="cls-panel"><h3 class="cls-panel__title">' + esc(groups.title) + '</h3>' +
            '<p class="cls-panel__hint">Shared with your public admission forms.</p>' +
            '<div class="cls-note">' + icon('solar:info-circle-linear') + '<span>Year groups are managed in Enrollment Setup, so your forms and leads always use the same list.</span></div>' +
            '<div class="cls-chips">' + groups.options.map(function (option) { return '<span class="cls-chip">' + esc(option) + '</span>'; }).join('') + '</div>' +
            (groups.manage_url ? '<div class="cls-panel__foot"><a class="cls-btn cls-btn--ghost" href="' + esc(groups.manage_url) + '">Manage year groups ' + icon('solar:arrow-right-linear') + '</a></div>' : '') +
        '</div>';
    }

    function renderList(list) {
        var listed = list.options.filter(function (option) { return !option.unlisted; });
        var extras = list.options.filter(function (option) { return option.unlisted; });

        var rows = listed.map(function (option) {
            return '<div class="cls-row" draggable="true" data-drag="option" data-value="' + esc(option.value) + '">' +
                '<span class="cls-grip" title="Drag to reorder">' + icon('solar:hamburger-menu-linear') + '</span>' +
                toneDot(list, option) +
                '<span class="cls-row__label" data-label>' + esc(option.label) + '</span>' +
                countLink(list, option) +
                (option.locked ? '<span class="cls-row__lock" title="Built-in statuses keep your pipeline and reports working. They can be renamed but not removed.">' + icon('solar:lock-keyhole-minimalistic-linear') + '<span>Built-in</span></span>' : '') +
                '<button type="button" class="cls-icon-btn" data-action="option-menu" data-value="' + esc(option.value) + '" aria-label="Options for ' + esc(option.label) + '">' + icon('solar:menu-dots-bold') + '</button>' +
            '</div>';
        }).join('');

        var extraRows = extras.map(function (option) {
            return '<div class="cls-row" data-value="' + esc(option.value) + '">' +
                '<span class="cls-row__label">' + esc(option.label) + '</span>' + countLink(list, option) +
                '<button type="button" class="cls-btn cls-btn--ghost" data-action="adopt-option" data-value="' + esc(option.value) + '">Add to list</button>' +
                '<button type="button" class="cls-icon-btn" data-action="remove-option" data-value="' + esc(option.value) + '" aria-label="Remove ' + esc(option.label) + ' from leads">' + icon('solar:trash-bin-minimalistic-linear') + '</button>' +
            '</div>';
        }).join('');

        return '<div class="cls-panel"><h3 class="cls-panel__title">' + esc(list.title) + '</h3><p class="cls-panel__hint">' + esc(list.hint) + '</p>' +
            '<div class="cls-rows" data-drop="option">' + (rows || '<div class="cls-rows__empty">No options yet. Add the first one below.</div>') + '</div>' +
            '<form class="cls-add" data-form="add-option"><label class="visually-hidden" for="cls-add-name">New option name</label>' +
                '<input id="cls-add-name" class="form-control" placeholder="Add a new option" autocomplete="off" maxlength="60">' +
                '<button type="submit" class="cls-btn cls-btn--primary">' + icon('solar:add-circle-linear') + 'Add</button></form>' +
            (extras.length ? '<h4 class="cls-subhead">Found on leads, but not in this list</h4><p class="cls-subhint">These came from spreadsheets or older data. Add them to the list, or clean them up.</p><div class="cls-rows">' + extraRows + '</div>' : '') +
            (list.custom_key ? '<div class="cls-panel__foot"><button type="button" class="cls-btn cls-btn--danger-quiet" data-action="remove-filter" data-key="' + esc(list.custom_key) + '">' + icon('solar:trash-bin-minimalistic-linear') + 'Remove this filter</button></div>' : '') +
        '</div>';
    }

    function toneDot(list, option) {
        if (!option.tone) return '';
        var tone = /^[a-z]+$/.test(option.tone) ? ' cls-tone-' + option.tone : '';
        var style = /^#[0-9A-Fa-f]{6}$/.test(option.tone) ? ' style="--cls-tone:' + option.tone + '"' : '';
        if (list.colors && !option.locked) {
            return '<button type="button" class="cls-dot' + tone + '"' + style + ' data-action="tone-menu" data-value="' + esc(option.value) + '" aria-label="Change colour of ' + esc(option.label) + '"></button>';
        }
        return '<span class="cls-dot' + tone + '"' + style + '></span>';
    }

    function countLink(list, option) {
        if (!option.count) return '<span class="cls-row__count">No leads</span>';
        var url = LEADS_URL + '?view=list&' + encodeURIComponent(list.key) + '=' + encodeURIComponent(option.value);
        return '<a class="cls-row__count" href="' + esc(url) + '" title="See these leads">' + plural(option.count, 'lead') + '</a>';
    }

    /* ---------- menu ---------- */

    function closeMenu() {
        if (menuEl) {
            menuEl.remove();
            menuEl = null;
        }
    }

    /** items: {label, icon, run, danger, disabled} or 'divider' */
    function openMenu(anchor, items) {
        closeMenu();
        menuEl = document.createElement('div');
        menuEl.className = 'cls-menu';
        menuEl.setAttribute('role', 'menu');
        items.forEach(function (item) {
            if (item === 'divider') {
                menuEl.appendChild(document.createElement('hr'));
                return;
            }
            var button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('role', 'menuitem');
            button.disabled = !!item.disabled;
            if (item.danger) button.className = 'is-danger';
            button.innerHTML = (item.iconHtml || icon(item.icon)) + '<span>' + esc(item.label) + '</span>';
            button.addEventListener('click', function () {
                closeMenu();
                item.run();
            });
            menuEl.appendChild(button);
        });
        root.appendChild(menuEl);

        var rect = anchor.getBoundingClientRect();
        var width = menuEl.offsetWidth;
        var height = menuEl.offsetHeight;
        menuEl.style.left = Math.max(8, Math.min(rect.right - width, window.innerWidth - width - 8)) + 'px';
        menuEl.style.top = (rect.bottom + height + 8 > window.innerHeight ? Math.max(8, rect.top - height - 4) : rect.bottom + 4) + 'px';
        var first = menuEl.querySelector('button:not(:disabled)');
        if (first) first.focus();
        lastFocus = anchor;
    }

    function filterMenu(anchor, key) {
        var filter = state.filters.filter(function (item) { return item.key === key; })[0];
        var siblings = state.filters.filter(function (item) { return item.placement === filter.placement; });
        var index = siblings.indexOf(filter);
        var items = [{ label: 'Rename', icon: 'solar:pen-linear', run: function () { startFilterRename(key); } }];
        if (siblings.length > 1) {
            items.push({ label: 'Move up', icon: 'solar:arrow-up-linear', disabled: index === 0, run: function () { swapFilter(filter, siblings[index - 1]); } });
            items.push({ label: 'Move down', icon: 'solar:arrow-down-linear', disabled: index === siblings.length - 1, run: function () { swapFilter(filter, siblings[index + 1]); } });
        }
        items.push('divider');
        PLACEMENTS.forEach(function (place) {
            if (place.key !== filter.placement) {
                items.push({ label: place.key === 'hidden' ? 'Hide' : 'Move to ' + place.title, icon: place.icon, run: function () { moveFilter(filter, place.key); } });
            }
        });
        if (filter.custom) {
            items.push('divider');
            items.push({ label: 'Edit options', icon: 'solar:list-check-linear', run: function () { tab = 'options'; activeList = key; render(); } });
            items.push({ label: 'Remove filter', icon: 'solar:trash-bin-minimalistic-linear', danger: true, run: function () { confirmRemoveFilter(key.replace(/^cf_/, '')); } });
        }
        openMenu(anchor, items);
    }

    function swapFilter(a, b) {
        var list = state.filters.slice();
        var i = list.indexOf(a);
        var j = list.indexOf(b);
        list[i] = b;
        list[j] = a;
        saveLayout({ filters: filtersPayload(list) });
    }

    function moveFilter(filter, placement) {
        var list = state.filters.map(function (item) {
            return item === filter ? Object.assign({}, item, { placement: placement }) : item;
        });
        saveLayout({ filters: filtersPayload(list) });
    }

    function optionMenu(anchor, value) {
        var list = currentList();
        var option = list.options.filter(function (item) { return item.value === value; })[0];
        var listed = list.options.filter(function (item) { return !item.unlisted; });
        var index = listed.indexOf(option);
        var items = [{ label: 'Rename', icon: 'solar:pen-linear', run: function () { startOptionRename(value); } }];
        if (listed.length > 1) {
            items.push({ label: 'Move up', icon: 'solar:arrow-up-linear', disabled: index === 0, run: function () { moveOption(index, index - 1); } });
            items.push({ label: 'Move down', icon: 'solar:arrow-down-linear', disabled: index === listed.length - 1, run: function () { moveOption(index, index + 1); } });
        }
        if (!option.locked) {
            items.push('divider');
            items.push({ label: 'Remove', icon: 'solar:trash-bin-minimalistic-linear', danger: true, run: function () { confirmRemoveOption(value); } });
        }
        openMenu(anchor, items);
    }

    function moveOption(from, to) {
        var values = currentList().options.filter(function (item) { return !item.unlisted; }).map(function (item) { return item.value; });
        values.splice(to, 0, values.splice(from, 1)[0]);
        save('PUT', '/lists/' + encodeURIComponent(activeList) + '/order', { values: values }, true);
    }

    function toneMenu(anchor, value) {
        openMenu(anchor, state.tones.map(function (tone) {
            return {
                label: tone.label,
                iconHtml: '<span class="cls-dot cls-tone-' + esc(tone.value) + '"></span>',
                run: function () { save('PATCH', '/lists/' + encodeURIComponent(activeList), { value: value, tone: tone.value }, true); }
            };
        }));
    }

    /* ---------- rename in place ---------- */

    function inlineEdit(labelEl, current, maxLength, commit) {
        var host = labelEl.closest('[draggable]');
        if (host) host.setAttribute('draggable', 'false');
        var input = document.createElement('input');
        input.className = 'cls-inline-input';
        input.value = current;
        input.maxLength = maxLength;
        input.setAttribute('aria-label', 'New name');
        labelEl.replaceWith(input);
        input.focus();
        input.select();
        var done = false;
        function finish(keep) {
            if (done) return;
            done = true;
            var value = input.value.trim();
            if (keep && value && value !== current) {
                commit(value);
            } else {
                render();
            }
        }
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') { event.preventDefault(); finish(true); }
            if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); finish(false); }
        });
        input.addEventListener('blur', function () { finish(true); });
    }

    function startFilterRename(key) {
        var filter = state.filters.filter(function (item) { return item.key === key; })[0];
        var card = panel.querySelector('[data-drag="filter"][data-key="' + CSS.escape(key) + '"] [data-label]');
        inlineEdit(card, filter.label, 40, function (value) {
            if (filter.custom) {
                // A custom filter's name lives with the filter itself.
                save('PATCH', '/custom-filters/' + encodeURIComponent(key.replace(/^cf_/, '')), { label: value }, true);
                return;
            }
            var list = state.filters.map(function (item) {
                return item === filter ? Object.assign({}, item, { label: value }) : item;
            });
            saveLayout({ filters: filtersPayload(list) });
        });
    }

    function startOptionRename(value) {
        var option = currentList().options.filter(function (item) { return item.value === value; })[0];
        var label = panel.querySelector('[data-drag="option"][data-value="' + CSS.escape(value) + '"] [data-label]');
        inlineEdit(label, option.label, activeList === 'admission_intake' ? 30 : 60, function (name) {
            save('PATCH', '/lists/' + encodeURIComponent(activeList), { value: value, name: name });
        });
    }

    /* ---------- dialogs ---------- */

    function closeDialog() {
        if (dialogEl) {
            dialogEl.remove();
            dialogEl = null;
        }
        if (lastFocus && document.contains(lastFocus)) lastFocus.focus();
    }

    /** onSubmit receives the form and must return a promise; a rejection keeps the dialog open. */
    function openDialog(html, onSubmit) {
        closeMenu();
        dialogEl = document.createElement('div');
        dialogEl.className = 'cls-overlay';
        dialogEl.innerHTML = '<form class="cls-dialog" role="dialog" aria-modal="true" novalidate>' + html + '</form>';
        root.appendChild(dialogEl);
        var form = dialogEl.querySelector('form');
        var error = form.querySelector('[data-error]');

        dialogEl.addEventListener('mousedown', function (event) {
            if (event.target === dialogEl) closeDialog();
        });
        form.addEventListener('click', function (event) {
            if (event.target.closest('[data-dismiss]')) closeDialog();
        });
        form.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !document.querySelector('.crm-select-menu')) {
                closeDialog();
                return;
            }
            if (event.key === 'Tab') {
                var focusable = form.querySelectorAll('button:not(:disabled), input, select, textarea');
                var first = focusable[0];
                var last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var button = form.querySelector('[type="submit"]');
            button.disabled = true;
            error.hidden = true;
            onSubmit(form).then(function (data) {
                closeDialog();
                if (data && data.message) toast(data.message);
                render();
            }, function (failure) {
                button.disabled = false;
                error.textContent = failure.message;
                error.hidden = false;
            });
        });
        var focus = form.querySelector('[data-autofocus]') || form.querySelector('button[type="submit"]');
        focus.focus();
    }

    function addFilterDialog() {
        lastFocus = document.activeElement;
        openDialog(
            '<h3>Add a filter</h3><p>For example “Campus” or “Referred by”. It appears on the Leads page and as a field on your lead forms.</p>' +
            '<div class="cls-dialog__error" data-error hidden></div>' +
            '<div class="cls-field"><label for="cls-f-label">Filter name</label><input id="cls-f-label" name="label" class="form-control" maxlength="40" data-autofocus placeholder="e.g. Campus"></div>' +
            '<div class="cls-field"><label for="cls-f-options">Options</label><textarea id="cls-f-options" name="options" class="form-control" rows="4" placeholder="One per line, e.g.&#10;North campus&#10;South campus"></textarea><small>You can add or change these later.</small></div>' +
            '<div class="cls-dialog__actions"><button type="button" class="cls-btn cls-btn--ghost" data-dismiss>Cancel</button><button type="submit" class="cls-btn cls-btn--primary">Add filter</button></div>',
            function (form) {
                var options = form.elements.options.value.split('\n').map(function (line) { return line.trim(); }).filter(Boolean);
                return send('POST', '/custom-filters', { label: form.elements.label.value, options: options });
            }
        );
    }

    function confirmRemoveFilter(key) {
        var list = state.lists.filter(function (item) { return item.custom_key === key; })[0];
        var used = list.options.reduce(function (sum, option) { return sum + option.count; }, 0);
        lastFocus = document.activeElement;
        openDialog(
            '<h3>Remove “' + esc(list.title) + '”?</h3>' +
            '<p>It will disappear from the Leads page and the lead forms' + (used ? '. The ' + plural(used, 'lead') + ' using it are kept, and their values come back if you add a filter with this name again.' : '.') + '</p>' +
            '<div class="cls-dialog__error" data-error hidden></div>' +
            '<div class="cls-dialog__actions"><button type="button" class="cls-btn cls-btn--ghost" data-dismiss>Cancel</button><button type="submit" class="cls-btn cls-btn--danger">Remove filter</button></div>',
            function () {
                return send('DELETE', '/custom-filters/' + encodeURIComponent(key)).then(function (data) {
                    if (activeList === 'cf_' + key) activeList = state.lists[0].key;
                    return data;
                });
            }
        );
    }

    function confirmRemoveOption(value) {
        var list = currentList();
        var option = list.options.filter(function (item) { return item.value === value; })[0];
        var others = list.options.filter(function (item) { return item.value !== value && !item.unlisted; });
        var choices = others.map(function (item) { return '<option value="' + esc(item.value) + '">Move to ' + esc(item.label) + '</option>'; }).join('');
        var field = '';

        if (option.count > 0) {
            var first = '';
            if (list.on_remove === 'optional') {
                first = (option.unlisted ? '' : '<option value="">Keep it on those leads</option>') + '<option value="' + esc(state.clear_value) + '">Clear it from those leads</option>';
            } else if (list.on_remove === 'category') {
                first = '<option value="' + esc(state.clear_value) + '">Make them uncategorized</option>';
            }
            field = '<div class="cls-field"><label for="cls-move">What should happen to ' + plural(option.count, 'lead') + ' using it?</label>' +
                '<select id="cls-move" name="move_to" class="form-select" data-autofocus>' + first + choices + '</select></div>';
        }

        lastFocus = document.activeElement;
        openDialog(
            '<h3>Remove “' + esc(option.label) + '”?</h3>' +
            '<p>' + (option.count > 0 ? 'It will no longer be offered as a choice.' : 'No leads use it, so nothing else changes.') + ' Any quick button for it is removed too.</p>' +
            '<div class="cls-dialog__error" data-error hidden></div>' + field +
            '<div class="cls-dialog__actions"><button type="button" class="cls-btn cls-btn--ghost" data-dismiss>Cancel</button><button type="submit" class="cls-btn cls-btn--danger">Remove</button></div>',
            function (form) {
                var select = form.elements.move_to;
                return send('DELETE', '/lists/' + encodeURIComponent(activeList), { value: value, move_to: select ? select.value : null });
            }
        );
    }

    /* ---------- dragging ---------- */

    function dropTarget(zone, x, y, horizontal) {
        var items = Array.prototype.slice.call(zone.querySelectorAll('[draggable="true"]:not(.is-dragging)'));
        for (var i = 0; i < items.length; i++) {
            var rect = items[i].getBoundingClientRect();
            if (horizontal) {
                if (y < rect.bottom && x < rect.left + rect.width / 2) return items[i];
                if (y < rect.top) return items[i];
            } else if (y < rect.top + rect.height / 2) {
                return items[i];
            }
        }
        return null;
    }

    root.addEventListener('dragstart', function (event) {
        var item = event.target.closest && event.target.closest('[data-drag]');
        if (!item || item.getAttribute('draggable') !== 'true') return;
        dragged = item;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', item.textContent.trim());
        setTimeout(function () { item.classList.add('is-dragging'); }, 0);
    });

    root.addEventListener('dragover', function (event) {
        if (!dragged) return;
        var zone = event.target.closest('[data-drop="' + dragged.getAttribute('data-drag') + '"]');
        if (!zone) return;
        event.preventDefault();
        var empty = zone.querySelector('.cls-col__empty');
        if (empty) empty.remove();
        var before = dropTarget(zone, event.clientX, event.clientY, dragged.getAttribute('data-drag') === 'quick');
        if (before !== dragged && (before !== dragged.nextElementSibling || dragged.parentNode !== zone)) {
            zone.insertBefore(dragged, before);
        }
    });

    root.addEventListener('drop', function (event) {
        if (dragged) event.preventDefault();
    });

    root.addEventListener('dragend', function () {
        if (!dragged) return;
        var kind = dragged.getAttribute('data-drag');
        dragged.classList.remove('is-dragging');
        dragged = null;

        if (kind === 'filter') {
            var byKey = {};
            state.filters.forEach(function (filter) { byKey[filter.key] = filter; });
            var filters = [];
            panel.querySelectorAll('[data-drop="filter"]').forEach(function (zone) {
                zone.querySelectorAll('[data-drag="filter"]').forEach(function (card) {
                    filters.push(Object.assign({}, byKey[card.getAttribute('data-key')], { placement: zone.getAttribute('data-placement') }));
                });
            });
            var changed = JSON.stringify(filtersPayload(filters)) !== JSON.stringify(filtersPayload(state.filters));
            changed ? saveLayout({ filters: filtersPayload(filters) }) : render();
        } else if (kind === 'quick') {
            var byId = {};
            state.quick_filters.forEach(function (quick) { byId[quick.id] = quick; });
            var quick = Array.prototype.map.call(panel.querySelectorAll('[data-drag="quick"]'), function (pill) { return byId[pill.getAttribute('data-id')]; });
            saveLayout({ quick_filters: quickPayload(quick) });
        } else if (kind === 'option') {
            var values = Array.prototype.map.call(panel.querySelectorAll('[data-drag="option"]'), function (row) { return row.getAttribute('data-value'); });
            save('PUT', '/lists/' + encodeURIComponent(activeList) + '/order', { values: values }, true);
        }
    });

    /* ---------- events ---------- */

    root.addEventListener('click', function (event) {
        var tabButton = event.target.closest('[data-cls-tab]');
        if (tabButton) {
            tab = tabButton.getAttribute('data-cls-tab');
            var url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState(window.history.state, '', url);
            render();
            return;
        }

        var button = event.target.closest('[data-action]');
        if (!button) return;
        var action = button.getAttribute('data-action');
        lastFocus = button;

        if (action === 'filter-menu') filterMenu(button, button.getAttribute('data-key'));
        if (action === 'option-menu') optionMenu(button, button.getAttribute('data-value'));
        if (action === 'tone-menu') toneMenu(button, button.getAttribute('data-value'));
        if (action === 'add-filter') addFilterDialog();
        if (action === 'remove-filter') confirmRemoveFilter(button.getAttribute('data-key'));
        if (action === 'remove-option') confirmRemoveOption(button.getAttribute('data-value'));
        if (action === 'pick-list') {
            activeList = button.getAttribute('data-list');
            render();
        }
        if (action === 'adopt-option') {
            save('POST', '/lists/' + encodeURIComponent(activeList), { name: button.getAttribute('data-value') });
        }
        if (action === 'remove-quick') {
            var id = button.getAttribute('data-id');
            saveLayout({ quick_filters: quickPayload(state.quick_filters.filter(function (quick) { return quick.id !== id; })) });
        }
        if (action === 'restore') {
            save('POST', '/restore', { section: button.getAttribute('data-section') });
        }
    });

    root.addEventListener('dblclick', function (event) {
        var label = event.target.closest('[data-label]');
        if (!label) return;
        var filterCard = label.closest('[data-drag="filter"]');
        var optionRow = label.closest('[data-drag="option"]');
        if (filterCard) startFilterRename(filterCard.getAttribute('data-key'));
        if (optionRow) startOptionRename(optionRow.getAttribute('data-value'));
    });

    root.addEventListener('change', function (event) {
        var target = event.target;
        if (target.matches('[data-toggle]')) {
            var body = {};
            body[target.getAttribute('data-toggle')] = target.checked;
            saveLayout(body);
            return;
        }
        var field = target.getAttribute('data-quick');
        if (field === 'param') {
            quickDraft = { param: target.value, value: '', label: '', touched: false };
            render();
        }
        if (field === 'value') {
            quickDraft.value = target.value;
            if (!quickDraft.touched) quickDraft.label = '';
            render();
        }
    });

    root.addEventListener('input', function (event) {
        if (event.target.getAttribute('data-quick') === 'label') {
            quickDraft.label = event.target.value;
            quickDraft.touched = true;
        }
    });

    root.addEventListener('submit', function (event) {
        var form = event.target;
        if (form.matches('[data-form="add-option"]')) {
            event.preventDefault();
            var input = form.querySelector('input');
            var name = input.value.trim();
            if (!name) {
                input.focus();
                return;
            }
            send('POST', '/lists/' + encodeURIComponent(activeList), { name: name }).then(function (data) {
                toast(data.message);
                render();
                var next = panel.querySelector('[data-form="add-option"] input');
                if (next) next.focus();
            }, function (error) {
                toast(error.message, true);
            });
            return;
        }
        if (form.matches('[data-form="add-quick"]')) {
            event.preventDefault();
            var label = quickDraft.label.trim();
            if (!label) {
                toast('Please give the button a name.', true);
                return;
            }
            var next = quickPayload(state.quick_filters).concat([{ id: null, label: label, param: quickDraft.param, value: quickDraft.value }]);
            send('PUT', '/layout', { quick_filters: next }).then(function () {
                toast('“' + label + '” button added');
                quickDraft.touched = false;
                quickDraft.label = '';
                render();
            }, function (error) {
                toast(error.message, true);
            });
        }
    });

    function onDocClick(event) {
        if (!document.contains(root)) {
            document.removeEventListener('mousedown', onDocClick);
            document.removeEventListener('keydown', onDocKey);
            return;
        }
        if (menuEl && !menuEl.contains(event.target)) closeMenu();
    }

    function onDocKey(event) {
        if (event.key === 'Escape' && menuEl) {
            closeMenu();
            if (lastFocus && document.contains(lastFocus)) lastFocus.focus();
        }
    }

    document.addEventListener('mousedown', onDocClick);
    document.addEventListener('keydown', onDocKey);
    window.addEventListener('scroll', closeMenu, { passive: true });

    render();
})();
