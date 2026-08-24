(function () {
    const root = document.getElementById('enrollmentSetup');
    if (!root) return;

    const tabs = root.querySelectorAll('[data-tab]');
    const panels = root.querySelectorAll('[data-tab-panel]');
    const baseUrl = new URL(window.location.href);

    function showTab(tabKey, pushState) {
        if (!tabKey) return;

        let matched = false;

        tabs.forEach(tab => {
            const active = tab.dataset.tab === tabKey;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            if (active) matched = true;
        });

        if (!matched) return;

        panels.forEach(panel => {
            const active = panel.dataset.tabPanel === tabKey;
            panel.classList.toggle('is-visible', active);
            panel.hidden = !active;
            if (active) {
                panel.style.animation = 'none';
                void panel.offsetWidth;
                panel.style.animation = '';
            }
        });

        if (pushState !== false) {
            baseUrl.searchParams.set('tab', tabKey);
            window.history.replaceState({ tab: tabKey }, '', baseUrl);
        }
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => showTab(tab.dataset.tab));
    });

    window.addEventListener('popstate', event => {
        const tabKey = event.state?.tab || baseUrl.searchParams.get('tab');
        if (tabKey) showTab(tabKey, false);
    });

    const initial = root.dataset.initialTab || baseUrl.searchParams.get('tab');
    if (initial) showTab(initial, false);
})();
