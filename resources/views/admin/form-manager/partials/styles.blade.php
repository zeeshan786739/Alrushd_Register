{{-- Global CRM styles loaded via alrushad-overrides.css in app layout --}}
<style>
    /* Reserve space for identity; wrap actions instead of squeezing form names. */
    #formsTable { table-layout: fixed; width: 100%; min-width: 960px; }
    #formsTable th { font-size: 11px; letter-spacing: .055em; padding: 14px 12px; white-space: nowrap; }
    #formsTable td { padding: 18px 12px; vertical-align: middle; }
    #formsTable th:first-child { width: 27%; }
    #formsTable th:nth-child(2), #formsTable th:nth-child(3) { width: 6%; }
    #formsTable th:nth-child(4) { width: 11%; }
    #formsTable th:nth-child(5) { width: 13%; }
    #formsTable th:nth-child(6) { width: 9%; }
    #formsTable th:last-child { width: 28%; }
    #formsTable td:last-child { width: auto; white-space: normal; }
    #formsTable .fc-form-icon { flex: 0 0 36px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; }
    #formsTable .fc-form-identity { min-width: 0; }
    #formsTable .fc-form-identity h6 { font-size: 14px; line-height: 1.5; overflow-wrap: anywhere; }
    #formsTable .fc-table-url { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; }
    #formsTable .fc-table-actions { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }
    /* Match the flex basis and both dimensions; legacy !important sizes made ovals. */
    #formsTable .fc-action-icon {
        box-sizing: border-box;
        width: 32px !important;
        height: 32px !important;
        min-width: 32px;
        max-width: 32px;
        min-height: 32px;
        max-height: 32px;
        flex: 0 0 32px;
        padding: 0 !important;
        border-radius: 50% !important;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-shadow: none;
    }
    #formsTable .fc-action-icon i { font-size: 17px; line-height: 1; display: block; }
    #formsTable .fc-action-icon svg { display: block !important; width: 18px !important; height: 18px !important; flex: 0 0 18px; color: inherit; pointer-events: none; }
    #formsTable .fc-table-actions > form { display: flex !important; flex: 0 0 32px; margin: 0; }
    #formsTable .fc-action-icon:hover { transform: none; }
    #formsTable .fc-submissions-btn { font-size: 12px; min-height: 32px; padding: 5px 10px !important; }
    #formsTable .fc-badge { font-size: 12px; line-height: 1.4; }
    #formsTable :is(a,button):focus-visible { outline: 2px solid #487fff; outline-offset: 2px; }
</style>
