/**
 * Styled option list for native <select> fields across the admin.
 *
 * The <select> stays in the page as the source of truth: this only replaces the
 * list the browser would draw when it opens. Form submission, validation, reset,
 * inline onchange handlers and scripts that set the value all keep working, and
 * listening on the document means selects added later (pjax, modals, AJAX panels)
 * need no setup.
 *
 * Touch devices keep the native picker, which suits them better.
 * Opt a field out with the data-native-select attribute.
 */
(function () {
    'use strict';

    if (window.__crmSelectBound) return;
    window.__crmSelectBound = true;

    var SEARCH_MIN_OPTIONS = 10;
    var MAX_MENU_HEIGHT = 320;
    var MIN_MENU_HEIGHT = 140;
    var MENU_MAX_WIDTH = 360;
    var GAP = 6;
    var VIEWPORT_PADDING = 12;
    var TYPEAHEAD_RESET_MS = 600;
    var OPEN_KEYS = [' ', 'Enter', 'ArrowDown', 'ArrowUp', 'F4'];
    var EXCLUDED = '[data-native-select], .select2, .select2-hidden-accessible, .swal2-select';
    var HEX_TONE = /^#[0-9A-Fa-f]{6}$/;

    var open = null;
    var lastPointerType = 'mouse';
    var nextId = 0;

    function isEnhanceable(target) {
        return target instanceof HTMLSelectElement
            && !target.multiple
            && target.size <= 1
            && !target.matches(':disabled')
            && !target.matches(EXCLUDED)
            && !target.closest('[data-native-select], .note-editor, .swal2-container');
    }

    function anchorFor(select) {
        return select.closest('[data-crm-form-tag-select]') || select;
    }

    function accessibleName(select) {
        var label = select.getAttribute('aria-label');
        if (label) return label;
        var labels = select.labels;
        if (labels && labels.length) return labels[0].textContent.trim();
        return select.name || 'Options';
    }

    function normalize(text) {
        return String(text || '').toLocaleLowerCase().replace(/\s+/g, ' ').trim();
    }

    function optionLabel(option) {
        return option.getAttribute('data-label') || option.text;
    }

    function buildLeading(option) {
        var tone = option.getAttribute('data-tone');
        var icon = option.getAttribute('data-icon');
        if (!tone && !icon) return null;

        var leading;
        if (icon) {
            leading = document.createElement('iconify-icon');
            leading.setAttribute('icon', icon);
            leading.className = 'crm-select-option__icon';
        } else {
            leading = document.createElement('span');
            leading.className = 'crm-select-option__dot';
        }
        return leading;
    }

    function buildOption(state, option) {
        var row = document.createElement('div');
        var tone = option.getAttribute('data-tone');

        row.id = 'crm-select-option-' + (++nextId);
        row.className = 'crm-select-option';
        row.setAttribute('role', 'option');
        row.setAttribute('aria-selected', option.selected ? 'true' : 'false');
        // "Select…" and "Any status" mean nothing is chosen. "Unassigned" (data-label) is a real choice.
        var isPlaceholder = option.value === '' && !option.hasAttribute('data-label');
        if (isPlaceholder) row.classList.add('is-placeholder');
        var showAsChosen = option.selected && !isPlaceholder;
        if (showAsChosen) row.classList.add('is-selected');
        if (option.disabled) {
            row.classList.add('is-disabled');
            row.setAttribute('aria-disabled', 'true');
        }
        if (tone) {
            if (HEX_TONE.test(tone)) {
                row.style.setProperty('--crm-select-tone', tone);
            } else {
                row.setAttribute('data-tone', tone);
            }
        }

        var leading = buildLeading(option);
        if (leading) row.appendChild(leading);

        var label = document.createElement('span');
        label.className = 'crm-select-option__label';
        label.textContent = optionLabel(option);
        row.appendChild(label);

        if (showAsChosen) {
            var check = document.createElement('iconify-icon');
            check.setAttribute('icon', 'solar:check-circle-bold');
            check.className = 'crm-select-option__check';
            row.appendChild(check);
        }

        var item = { row: row, option: option, text: normalize(optionLabel(option)), group: null };
        state.items.push(item);
        state.list.appendChild(row);
        return item;
    }

    function buildGroup(state, group) {
        var heading = document.createElement('div');
        heading.className = 'crm-select-group';
        heading.setAttribute('role', 'presentation');
        heading.textContent = group.label;
        state.list.appendChild(heading);

        Array.prototype.forEach.call(group.children, function (child) {
            if (child.hidden) return;
            buildOption(state, child).group = heading;
        });
    }

    function buildMenu(select, anchor) {
        var state = {
            select: select,
            anchor: anchor,
            items: [],
            activeIndex: -1,
            search: null,
            typed: '',
            typedAt: 0
        };

        var menu = document.createElement('div');
        menu.className = 'crm-select-menu';
        state.menu = menu;

        var list = document.createElement('div');
        list.className = 'crm-select-list';
        list.id = 'crm-select-list-' + (++nextId);
        list.setAttribute('role', 'listbox');
        list.setAttribute('tabindex', '-1');
        list.setAttribute('aria-label', accessibleName(select));
        state.list = list;

        if (select.options.length >= SEARCH_MIN_OPTIONS) {
            var searchWrap = document.createElement('div');
            searchWrap.className = 'crm-select-search';
            var searchIcon = document.createElement('iconify-icon');
            searchIcon.setAttribute('icon', 'solar:magnifer-linear');
            var search = document.createElement('input');
            search.type = 'text';
            search.placeholder = 'Search…';
            search.autocomplete = 'off';
            search.spellcheck = false;
            search.setAttribute('role', 'combobox');
            search.setAttribute('aria-expanded', 'true');
            search.setAttribute('aria-controls', list.id);
            search.setAttribute('aria-label', 'Search ' + accessibleName(select));
            searchWrap.appendChild(searchIcon);
            searchWrap.appendChild(search);
            menu.appendChild(searchWrap);
            state.search = search;
            search.addEventListener('input', function () { applyFilter(state); });
        }

        Array.prototype.forEach.call(select.children, function (child) {
            if (child.hidden) return;
            if (child.tagName === 'OPTGROUP') {
                buildGroup(state, child);
            } else if (child.tagName === 'OPTION') {
                buildOption(state, child);
            }
        });

        var empty = document.createElement('div');
        empty.className = 'crm-select-empty';
        empty.textContent = 'No matches';
        empty.hidden = true;
        list.appendChild(empty);
        state.empty = empty;

        menu.appendChild(list);

        menu.addEventListener('mousedown', function (event) {
            event.stopPropagation();
            if (event.target !== state.search) event.preventDefault();
        });
        menu.addEventListener('click', function (event) {
            event.stopPropagation();
            var row = event.target.closest('.crm-select-option');
            var item = row && state.items.find(function (candidate) { return candidate.row === row; });
            if (item) choose(state, item);
        });
        menu.addEventListener('mousemove', function (event) {
            var row = event.target.closest('.crm-select-option');
            if (!row) return;
            var index = state.items.findIndex(function (candidate) { return candidate.row === row; });
            if (index !== -1 && index !== state.activeIndex && isUsable(state.items[index])) {
                setActive(state, index, false);
            }
        });
        menu.addEventListener('keydown', function (event) { onMenuKeydown(state, event); });

        return state;
    }

    function isUsable(item) {
        return !item.option.disabled && !item.row.hidden;
    }

    function setActive(state, index, scroll) {
        var current = state.items[state.activeIndex];
        if (current) current.row.classList.remove('is-active');

        state.activeIndex = index;
        var item = state.items[index];
        var focusTarget = state.search || state.list;

        if (!item) {
            focusTarget.removeAttribute('aria-activedescendant');
            return;
        }

        item.row.classList.add('is-active');
        focusTarget.setAttribute('aria-activedescendant', item.row.id);
        if (scroll) item.row.scrollIntoView({ block: 'nearest' });
    }

    function step(state, from, direction) {
        var index = from + direction;
        while (index >= 0 && index < state.items.length) {
            if (isUsable(state.items[index])) return index;
            index += direction;
        }
        return -1;
    }

    function move(state, direction, distance) {
        var index = state.activeIndex;
        for (var count = 0; count < distance; count++) {
            var next = step(state, index, direction);
            if (next === -1) break;
            index = next;
        }
        if (index !== -1) setActive(state, index, true);
    }

    function applyFilter(state) {
        var needle = normalize(state.search.value);
        var visibleGroups = [];

        state.items.forEach(function (item) {
            var match = needle === '' || item.text.indexOf(needle) !== -1;
            item.row.hidden = !match;
            if (match && item.group) visibleGroups.push(item.group);
        });

        state.list.querySelectorAll('.crm-select-group').forEach(function (group) {
            group.hidden = visibleGroups.indexOf(group) === -1;
        });

        var first = step(state, -1, 1);
        state.empty.hidden = first !== -1;
        setActive(state, first, false);
        state.list.scrollTop = 0;
    }

    function typeahead(state, key) {
        var now = Date.now();
        state.typed = now - state.typedAt > TYPEAHEAD_RESET_MS ? key : state.typed + key;
        state.typedAt = now;

        var needle = normalize(state.typed);
        var index = state.items.findIndex(function (item) {
            return isUsable(item) && item.text.indexOf(needle) === 0;
        });
        if (index !== -1) setActive(state, index, true);
    }

    function onMenuKeydown(state, event) {
        var key = event.key;
        var searching = !!state.search;
        var handled = true;

        if (key === 'ArrowDown') {
            move(state, 1, 1);
        } else if (key === 'ArrowUp') {
            move(state, -1, 1);
        } else if (key === 'PageDown') {
            move(state, 1, 8);
        } else if (key === 'PageUp') {
            move(state, -1, 8);
        } else if (key === 'Home' && !searching) {
            setActive(state, step(state, -1, 1), true);
        } else if (key === 'End' && !searching) {
            setActive(state, step(state, state.items.length, -1), true);
        } else if (key === 'Enter' || (key === ' ' && !searching)) {
            var item = state.items[state.activeIndex];
            if (item) choose(state, item);
        } else if (key === 'Escape') {
            closeMenu(true);
        } else if (key === 'Tab') {
            // Focus returns to the field first, so Tab moves on from there as usual.
            closeMenu(true);
            event.stopPropagation();
            return;
        } else if (!searching && key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
            typeahead(state, key);
        } else {
            handled = false;
        }

        if (handled) {
            event.preventDefault();
            event.stopPropagation();
        }
    }

    function choose(state, item) {
        if (!isUsable(item)) return;

        var select = state.select;
        var changed = select.selectedIndex !== item.option.index;
        closeMenu(true);

        if (changed) {
            select.selectedIndex = item.option.index;
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    /** position:fixed is relative to a transformed ancestor when there is one, so measure the offset. */
    function containerOffset(menu) {
        menu.style.left = '0px';
        menu.style.top = '0px';
        var rect = menu.getBoundingClientRect();
        return { x: rect.left, y: rect.top };
    }

    function position(state) {
        var menu = state.menu;
        var rect = state.anchor.getBoundingClientRect();
        var searchHeight = state.search ? state.search.parentNode.offsetHeight : 0;

        menu.style.minWidth = Math.min(rect.width, window.innerWidth - VIEWPORT_PADDING * 2) + 'px';
        menu.style.maxWidth = Math.min(Math.max(rect.width, MENU_MAX_WIDTH), window.innerWidth - VIEWPORT_PADDING * 2) + 'px';

        var spaceBelow = window.innerHeight - rect.bottom - GAP - VIEWPORT_PADDING;
        var spaceAbove = rect.top - GAP - VIEWPORT_PADDING;
        var natural = Math.min(MAX_MENU_HEIGHT, state.list.scrollHeight + searchHeight + 2);
        var openUp = spaceBelow < natural && spaceAbove > spaceBelow;
        var height = Math.max(MIN_MENU_HEIGHT, Math.min(natural, openUp ? spaceAbove : spaceBelow));

        state.list.style.maxHeight = Math.max(0, height - searchHeight - 2) + 'px';
        menu.classList.toggle('is-above', openUp);

        var offset = containerOffset(menu);
        var width = menu.offsetWidth;
        var left = Math.max(VIEWPORT_PADDING, Math.min(rect.left, window.innerWidth - VIEWPORT_PADDING - width));
        var top = openUp ? rect.top - GAP - menu.offsetHeight : rect.bottom + GAP;
        top = Math.max(VIEWPORT_PADDING, top);

        menu.style.left = (left - offset.x) + 'px';
        menu.style.top = (top - offset.y) + 'px';
    }

    function onViewportChange(event) {
        if (!open) return;
        if (event && event.target && event.target.nodeType === 1 && open.menu.contains(event.target)) return;

        var rect = open.anchor.getBoundingClientRect();
        var gone = !open.select.isConnected || rect.bottom < 0 || rect.top > window.innerHeight;
        if (gone) {
            closeMenu(false);
        } else {
            position(open);
        }
    }

    function openMenu(select) {
        closeMenu(false);

        var anchor = anchorFor(select);
        var state = buildMenu(select, anchor);
        // Inside a dialog the menu has to stay in it, or the dialog's focus trap blocks the search box.
        var container = anchor.closest('.modal, .offcanvas, dialog') || document.body;

        select.focus({ preventScroll: true });
        select.setAttribute('aria-expanded', 'true');
        container.appendChild(state.menu);
        open = state;
        position(state);

        var selected = state.items.findIndex(function (item) { return item.option.selected && isUsable(item); });
        var focusTarget = state.search || state.list;
        focusTarget.focus({ preventScroll: true });
        setActive(state, selected !== -1 ? selected : step(state, -1, 1), false);
        var active = state.items[state.activeIndex];
        if (active) active.row.scrollIntoView({ block: 'center' });

        // After the focus move: the tag select clears its open look when the <select> blurs.
        select.classList.add('is-crm-select-open');
        if (anchor !== select) anchor.classList.add('is-open');

        window.addEventListener('scroll', onViewportChange, true);
        window.addEventListener('resize', onViewportChange);
    }

    function closeMenu(restoreFocus) {
        if (!open) return;

        var state = open;
        open = null;

        window.removeEventListener('scroll', onViewportChange, true);
        window.removeEventListener('resize', onViewportChange);

        state.menu.remove();
        state.select.removeAttribute('aria-expanded');
        state.select.classList.remove('is-crm-select-open');
        if (state.anchor !== state.select) state.anchor.classList.remove('is-open');
        if (restoreFocus && state.select.isConnected) state.select.focus({ preventScroll: true });
    }

    document.addEventListener('pointerdown', function (event) {
        lastPointerType = event.pointerType || 'mouse';
    }, true);

    document.addEventListener('mousedown', function (event) {
        var wasOpenOn = open ? open.select : null;

        if (open && !open.menu.contains(event.target)) {
            closeMenu(false);
        }

        var select = event.target;
        if (event.button !== 0 || lastPointerType === 'touch' || !isEnhanceable(select)) return;

        // Stops the browser drawing its own list.
        event.preventDefault();
        if (select === wasOpenOn) {
            select.focus({ preventScroll: true });
            return;
        }
        openMenu(select);
    }, true);

    document.addEventListener('keydown', function (event) {
        var select = event.target;
        if (open || !isEnhanceable(select) || OPEN_KEYS.indexOf(event.key) === -1) return;

        event.preventDefault();
        event.stopPropagation();
        openMenu(select);
    }, true);

    document.addEventListener('admin:page-loaded', function () { closeMenu(false); });
    window.addEventListener('blur', function () { closeMenu(false); });
})();
