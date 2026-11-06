/**
 * Date range picker for the Leads filters. Listens on the document, so it keeps
 * working after pjax swaps the page. Choosing a date simply changes the URL, the
 * same way every other filter on the page works.
 */
(function () {
    'use strict';

    if (window.__crmDateFilterBound) return;
    window.__crmDateFilterBound = true;

    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var WEEKDAYS = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
    var SINGLE_MONTH_WIDTH = 820; // Matches the breakpoint in the Leads page styles.
    var open = null;

    function iso(date) {
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return date.getFullYear() + '-' + month + '-' + day;
    }

    function parse(value) {
        var parts = String(value || '').split('-').map(Number);
        return parts.length === 3 && parts[0] ? new Date(parts[0], parts[1] - 1, parts[2]) : null;
    }

    function addDays(date, days) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate() + days);
    }

    function presets(today, field) {
        var y = today.getFullYear();
        var m = today.getMonth();
        if (field === 'follow_up') {
            return [
                { label: 'Today', from: today, to: today },
                { label: 'Tomorrow', from: addDays(today, 1), to: addDays(today, 1) },
                { label: 'Next 7 days', from: today, to: addDays(today, 6) },
                { label: 'Next 30 days', from: today, to: addDays(today, 29) },
                { label: 'This month', from: new Date(y, m, 1), to: new Date(y, m + 1, 0) },
                { label: 'Next month', from: new Date(y, m + 1, 1), to: new Date(y, m + 2, 0) }
            ];
        }
        return [
            { label: 'Today', from: today, to: today },
            { label: 'Yesterday', from: addDays(today, -1), to: addDays(today, -1) },
            { label: 'Last 7 days', from: addDays(today, -6), to: today },
            { label: 'Last 30 days', from: addDays(today, -29), to: today },
            { label: 'This month', from: new Date(y, m, 1), to: today },
            { label: 'Last month', from: new Date(y, m - 1, 1), to: new Date(y, m, 0) },
            { label: 'This year', from: new Date(y, 0, 1), to: today }
        ];
    }

    function go(state, from, to) {
        var url = new URL(window.location.href);
        ['date_from', 'date_to', 'date_field', 'page'].forEach(function (key) {
            url.searchParams.delete(key);
        });
        if (from) {
            url.searchParams.set('date_from', from);
            url.searchParams.set('date_to', to || from);
            if (state.field !== 'created') url.searchParams.set('date_field', state.field);
        }
        window.location.href = url.toString();
    }

    function button(className, text) {
        var el = document.createElement('button');
        el.type = 'button';
        el.className = className;
        el.textContent = text;
        return el;
    }

    function renderPresets(state) {
        var box = state.root.querySelector('[data-crm-date-presets]');
        box.textContent = '';
        presets(state.today, state.field).forEach(function (preset) {
            var from = iso(preset.from);
            var to = iso(preset.to);
            var el = button('crm-date-filter__preset', preset.label);
            el.setAttribute('data-from', from);
            el.setAttribute('data-to', to);
            el.classList.toggle('is-active', state.start === from && state.end === to);
            box.appendChild(el);
        });
    }

    function renderMonth(state, first) {
        var wrap = document.createElement('div');
        wrap.className = 'crm-date-filter__month';

        var title = document.createElement('div');
        title.className = 'crm-date-filter__month-title';
        title.textContent = MONTHS[first.getMonth()] + ' ' + first.getFullYear();
        wrap.appendChild(title);

        var grid = document.createElement('div');
        grid.className = 'crm-date-filter__grid';
        WEEKDAYS.forEach(function (day) {
            var head = document.createElement('span');
            head.className = 'crm-date-filter__weekday';
            head.textContent = day;
            grid.appendChild(head);
        });

        var lead = (first.getDay() + 6) % 7;
        for (var i = 0; i < lead; i++) grid.appendChild(document.createElement('span'));

        var days = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate();
        var todayIso = iso(state.today);
        for (var d = 1; d <= days; d++) {
            var value = iso(new Date(first.getFullYear(), first.getMonth(), d));
            var el = button('crm-date-filter__day', String(d));
            el.setAttribute('data-date', value);
            if (value === todayIso) el.classList.add('is-today');
            grid.appendChild(el);
        }

        wrap.appendChild(grid);
        return wrap;
    }

    function paintRange(state) {
        var end = state.end || (state.start && state.hover) || state.start;
        var low = state.start && end && end < state.start ? end : state.start;
        var high = state.start && end && end < state.start ? state.start : end;

        state.root.querySelectorAll('[data-date]').forEach(function (day) {
            var value = day.getAttribute('data-date');
            day.classList.toggle('is-edge', !!low && (value === low || value === high));
            day.classList.toggle('is-between', !!low && value > low && value < high);
        });

        state.root.querySelector('[data-crm-date-from]').value = state.start || '';
        state.root.querySelector('[data-crm-date-to]').value = state.end || '';
        state.root.querySelector('[data-crm-date-apply]').disabled = !state.start;
    }

    function renderCalendar(state) {
        var box = state.root.querySelector('[data-crm-date-months]');
        box.textContent = '';
        box.appendChild(renderMonth(state, state.view));
        box.appendChild(renderMonth(state, new Date(state.view.getFullYear(), state.view.getMonth() + 1, 1)));
        paintRange(state);
    }

    function pickDay(state, value) {
        if (!state.start || state.end) {
            state.start = value;
            state.end = null;
        } else if (value < state.start) {
            state.end = state.start;
            state.start = value;
        } else {
            state.end = value;
        }
        state.hover = null;
        paintRange(state);
        renderPresets(state);
    }

    function setField(state, field) {
        state.field = field;
        state.root.querySelectorAll('[data-crm-date-field]').forEach(function (tab) {
            var active = tab.getAttribute('data-crm-date-field') === field;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        renderPresets(state);
    }

    function openPanel(root) {
        closePanel();
        var today = parse(root.getAttribute('data-today')) || new Date();
        var start = root.getAttribute('data-from') || null;
        var end = root.getAttribute('data-to') || null;
        var anchor = parse(start) || today;
        // Keep today's month in view when nothing is chosen. Wide screens show two months, so last month sits beside it.
        var monthsBack = window.innerWidth > SINGLE_MONTH_WIDTH ? 1 : 0;
        var view = start
            ? new Date(anchor.getFullYear(), anchor.getMonth(), 1)
            : new Date(today.getFullYear(), today.getMonth() - monthsBack, 1);

        var state = { root: root, today: today, start: start, end: end, hover: null, view: view, field: root.getAttribute('data-field') || 'created' };
        open = state;

        root.querySelector('[data-crm-date-panel]').hidden = false;
        root.querySelector('[data-crm-date-trigger]').setAttribute('aria-expanded', 'true');
        setField(state, state.field);
        renderCalendar(state);

        var panel = root.querySelector('[data-crm-date-panel]');
        panel.classList.remove('is-right');
        if (panel.getBoundingClientRect().right > window.innerWidth - 12) panel.classList.add('is-right');
    }

    function closePanel() {
        if (!open) return;
        open.root.querySelector('[data-crm-date-panel]').hidden = true;
        open.root.querySelector('[data-crm-date-trigger]').setAttribute('aria-expanded', 'false');
        open = null;
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-crm-date-trigger]');
        if (trigger) {
            event.preventDefault();
            var root = trigger.closest('[data-crm-date-filter]');
            if (open && open.root === root) closePanel(); else openPanel(root);
            return;
        }
        if (!open) return;
        if (!event.target.closest('[data-crm-date-panel]')) {
            closePanel();
            return;
        }

        var state = open;
        var day = event.target.closest('[data-date]');
        if (day) return pickDay(state, day.getAttribute('data-date'));

        var preset = event.target.closest('.crm-date-filter__preset');
        if (preset) return go(state, preset.getAttribute('data-from'), preset.getAttribute('data-to'));

        var tab = event.target.closest('[data-crm-date-field]');
        if (tab) return setField(state, tab.getAttribute('data-crm-date-field'));

        var nav = event.target.closest('[data-crm-date-nav]');
        if (nav) {
            state.view = new Date(state.view.getFullYear(), state.view.getMonth() + Number(nav.getAttribute('data-crm-date-nav')), 1);
            return renderCalendar(state);
        }

        if (event.target.closest('[data-crm-date-apply]')) return go(state, state.start, state.end);
        if (event.target.closest('[data-crm-date-clear]')) return go(state, null, null);
    });

    document.addEventListener('mouseover', function (event) {
        if (!open || !open.start || open.end) return;
        var day = event.target.closest && event.target.closest('[data-date]');
        if (day && open.root.contains(day)) {
            open.hover = day.getAttribute('data-date');
            paintRange(open);
        }
    });

    document.addEventListener('change', function (event) {
        if (!open || !event.target.matches('[data-crm-date-from], [data-crm-date-to]')) return;
        var state = open;
        var from = state.root.querySelector('[data-crm-date-from]').value || null;
        var to = state.root.querySelector('[data-crm-date-to]').value || null;
        if (from && to && to < from) {
            var swap = from;
            from = to;
            to = swap;
        }
        state.start = from || to;
        state.end = from && to ? to : null;
        var shown = parse(state.start);
        if (shown) state.view = new Date(shown.getFullYear(), shown.getMonth(), 1);
        renderCalendar(state);
        renderPresets(state);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && open && event.target.closest('[data-crm-date-panel]') && event.target.tagName === 'INPUT') {
            event.preventDefault();
            if (open.start) go(open, open.start, open.end);
            return;
        }
        if (event.key === 'Escape' && open) {
            var trigger = open.root.querySelector('[data-crm-date-trigger]');
            closePanel();
            trigger.focus();
        }
    });

    document.addEventListener('admin:page-loaded', closePanel);
})();
