<style>
/* CRM Leads workspace — uses global AL-Rushd tokens from alrushad-overrides.css */
#crm-leads-page{
    font-family:var(--crm-font);
    color:var(--crm-text);
}

#crm-leads-page.crm-board-view .crm-list-only{display:none!important}
#crm-leads-page.crm-list-view .crm-board-only{display:none!important}

/* Metrics strip (inside workspace shell) */
#crm-leads-page .crm-leads-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
#crm-leads-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#crm-leads-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#crm-leads-page .crm-metrics-strip__item{
    display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);
    font-size:var(--crm-text-sm);text-decoration:none;
}
#crm-leads-page .crm-metrics-strip__item--link{border-radius:var(--crm-radius-sm);transition:background .1s ease}
#crm-leads-page .crm-metrics-strip__item--link:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
#crm-leads-page .crm-metrics-strip__item--link.is-active{background:var(--crm-brand-soft);color:var(--crm-link, var(--crm-brand))}
#crm-leads-page .crm-metrics-strip__item--link.is-active strong{color:var(--crm-link, var(--crm-brand))}
#crm-leads-page .crm-metrics-strip__label{
    font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted);
}
#crm-leads-page .crm-metrics-strip__item strong{
    font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums;
}
#crm-leads-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#crm-leads-page .crm-metrics-strip__links{display:flex;align-items:center;gap:12px}
#crm-leads-page .crm-metrics-strip__link{
    font-size:var(--crm-text-sm);font-weight:500;color:var(--crm-link, var(--crm-brand));text-decoration:none;
}
#crm-leads-page .crm-metrics-strip__link:hover{text-decoration:underline;color:var(--crm-brand-hover)}

/* Filter workspace */
#crm-leads-page .crm-filter-workspace{padding:12px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface)}
#crm-leads-page .crm-filter-workspace__search-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:10px}
#crm-leads-page .crm-filter-workspace__search{
    flex:1 1 280px;display:flex;align-items:center;gap:8px;min-height:32px;
    padding:0 10px;border:2px solid var(--crm-border);border-radius:var(--crm-radius-sm);background:var(--crm-surface);
    transition:border-color .15s ease;
}
#crm-leads-page .crm-filter-workspace__search:focus-within{border-color:var(--crm-link, var(--crm-brand))}
#crm-leads-page .crm-filter-workspace__search iconify-icon{color:var(--crm-text-muted);font-size:16px;flex-shrink:0}
#crm-leads-page .crm-filter-workspace__search-input{
    flex:1;border:0;background:transparent;padding:6px 0;font-size:var(--crm-text-sm);
    color:var(--crm-text);outline:none;min-width:0;
}
#crm-leads-page .crm-filter-workspace__search-input::placeholder{color:var(--crm-text-muted)}
#crm-leads-page .crm-filter-workspace__search-actions{display:flex;align-items:center;gap:6px;flex-shrink:0}
#crm-leads-page .crm-filter-workspace__btn{
    display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:4px 12px;
    border-radius:var(--crm-radius-sm);font-size:var(--crm-text-sm);font-weight:500;text-decoration:none;
    border:1px solid transparent;cursor:pointer;transition:background .1s ease,border-color .1s ease;
}
#crm-leads-page .crm-filter-workspace__btn--primary{background:var(--crm-link, var(--crm-brand));border-color:var(--crm-link, var(--crm-brand));color:#fff}
#crm-leads-page .crm-filter-workspace__btn--primary:hover{background:var(--crm-brand-hover);border-color:var(--crm-brand-hover);color:#fff}
#crm-leads-page .crm-filter-workspace__btn--ghost{background:transparent;border-color:var(--crm-border-strong);color:var(--crm-text-muted)}
#crm-leads-page .crm-filter-workspace__btn--ghost:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}

#crm-leads-page .crm-filter-workspace__quick-row{display:flex;flex-wrap:wrap;align-items:flex-start;gap:8px;margin-bottom:8px}
#crm-leads-page .crm-filter-workspace__quick-label{
    flex:0 0 auto;padding-top:4px;font-size:var(--crm-text-xs);font-weight:600;
    text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted);min-width:72px;
}
#crm-leads-page .crm-filter-workspace__chips{
    flex:1 1 200px;display:flex;flex-wrap:wrap;align-items:center;gap:4px;
}
#crm-leads-page .crm-filter-chip{
    display:inline-flex;align-items:center;gap:5px;padding:4px 12px;min-height:28px;
    border:1.5px solid var(--crm-border);border-radius:999px;
    background:var(--crm-surface);color:var(--crm-text-muted);
    font-size:var(--crm-text-xs);font-weight:600;text-decoration:none;
    transition:background var(--crm-duration) var(--crm-ease),border-color var(--crm-duration) var(--crm-ease),color var(--crm-duration) var(--crm-ease),transform var(--crm-duration) var(--crm-ease);
}
#crm-leads-page .crm-filter-chip:hover{background:var(--crm-brand-soft);border-color:rgba(15,39,74,.18);color:var(--crm-brand);transform:translateY(-1px)}
#crm-leads-page .crm-filter-chip iconify-icon{font-size:14px;opacity:.85}
#crm-leads-page .crm-filter-chip.is-active{
    background:var(--crm-brand);border-color:var(--crm-brand);color:#fff;font-weight:600;
}
#crm-leads-page .crm-filter-chip.is-active iconify-icon{opacity:1;color:#fff}
#crm-leads-page .crm-filter-chip__count{
    padding:0 6px;border-radius:999px;background:rgba(0,0,0,.06);
    font-size:10px;font-weight:700;font-variant-numeric:tabular-nums;color:inherit;
}
#crm-leads-page .crm-filter-chip.is-active .crm-filter-chip__count{background:rgba(255,255,255,.22);color:#fff}
#crm-leads-page .crm-filter-chip--assigned_me.is-active{background:var(--crm-brand);border-color:var(--crm-brand);color:#fff}
#crm-leads-page .crm-filter-chip--unassigned.is-active{background:var(--crm-surface-sunken);border-color:var(--crm-text-muted);color:var(--crm-text-muted)}
#crm-leads-page .crm-filter-chip--new.is-active{background:#FFF7D6;border-color:#FF991F;color:#974F0C}
#crm-leads-page .crm-filter-chip--new.is-active .crm-filter-chip__count{background:rgba(255,153,31,.15);color:#974F0C}
#crm-leads-page .crm-filter-chip--follow_up.is-active{background:#FFEBE6;border-color:#DE350B;color:#BF2600}
#crm-leads-page .crm-filter-chip--follow_up.is-active .crm-filter-chip__count{background:rgba(222,53,11,.12);color:#BF2600}
#crm-leads-page .crm-filter-chip--high_priority.is-active{background:#FFBDAD;border-color:#BF2600;color:#BF2600}
#crm-leads-page .crm-filter-chip--urgent.is-active{background:#FEE2E2;border-color:#DC2626;color:#B91C1C}
#crm-leads-page .crm-filter-chip--urgent.is-active .crm-filter-chip__count{background:rgba(220,38,38,.12);color:#B91C1C}
#crm-leads-page .crm-filter-chip--medium.is-active{background:var(--crm-brand-soft);border-color:var(--crm-brand);color:var(--crm-brand)}
#crm-leads-page .crm-filter-chip--medium.is-active .crm-filter-chip__count{background:rgba(15,39,74,.1);color:var(--crm-brand)}
#crm-leads-page .crm-filter-chip--follow_up_overdue.is-active{background:#FFEBE6;border-color:#DE350B;color:#BF2600}
#crm-leads-page .crm-filter-chip--follow_up_overdue.is-active .crm-filter-chip__count{background:rgba(222,53,11,.12);color:#BF2600}
#crm-leads-page .crm-filter-chip--segment.is-active{background:#EAE6FF;border-color:#6554C0;color:#403294}
#crm-leads-page .crm-filter-chip--segment.is-active .crm-filter-chip__count{background:rgba(101,84,192,.12);color:#403294}
#crm-leads-page .crm-filter-workspace__chip-divider{width:1px;height:20px;background:var(--crm-border);margin:0 4px}

#crm-leads-page .crm-filter-workspace__advanced-toggle-row{margin-bottom:0}
#crm-leads-page .crm-filter-workspace__advanced-toggle{
    display:inline-flex;align-items:center;gap:6px;padding:2px 4px;border:0;background:transparent;
    color:var(--crm-text-muted);font-size:var(--crm-text-sm);font-weight:500;cursor:pointer;
}
#crm-leads-page .crm-filter-workspace__advanced-toggle:hover{color:var(--crm-link, var(--crm-brand))}
#crm-leads-page .crm-filter-workspace__advanced-toggle[aria-expanded="true"] .crm-filter-workspace__chevron{transform:rotate(180deg)}
#crm-leads-page .crm-filter-workspace__chevron{font-size:14px;transition:transform .15s ease}
#crm-leads-page .crm-filter-workspace__filter-count{
    min-width:18px;height:18px;padding:0 5px;border-radius:999px;
    background:var(--crm-link, var(--crm-brand));color:#fff;font-size:10px;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;
}
#crm-leads-page .crm-filter-workspace__advanced{
    margin-top:12px;padding-top:12px;border-top:1px solid var(--crm-border);
}
#crm-leads-page .crm-filter-workspace__advanced-grid{
    display:grid;grid-template-columns:repeat(4,minmax(140px,1fr));gap:10px 12px;margin-bottom:10px;
}
#crm-leads-page .crm-filter-workspace__field label{
    display:block;margin-bottom:4px;font-size:var(--crm-text-xs);font-weight:600;
    color:var(--crm-text-muted);text-transform:uppercase;letter-spacing:.04em;
}
#crm-leads-page .crm-filter-workspace__field .form-control,
#crm-leads-page .crm-filter-workspace__field .form-select{
    height:32px;padding:4px 8px;border:2px solid var(--crm-border);border-radius:var(--crm-radius-sm);
    font-size:var(--crm-text-sm);color:var(--crm-text);box-shadow:none;
}
#crm-leads-page .crm-filter-workspace__field .form-control:focus,
#crm-leads-page .crm-filter-workspace__field .form-select:focus{
    border-color:var(--crm-link, var(--crm-brand));box-shadow:none;
}
#crm-leads-page .crm-filter-workspace__advanced-actions{display:flex;align-items:center;gap:8px}

/* Toolbar */
#crm-leads-page .crm-leads-toolbar{
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    margin:0;padding:10px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken);
}
#crm-leads-page .crm-leads-toolbar__left{display:flex;flex-wrap:wrap;align-items:center;gap:12px;min-width:0}
#crm-leads-page .crm-leads-toolbar__right{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:6px}
#crm-leads-page .crm-leads-toolbar__meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#crm-leads-page .crm-leads-toolbar__meta strong{color:var(--crm-text);font-weight:600;font-variant-numeric:tabular-nums}
#crm-leads-page .crm-leads-toolbar__filters{
    padding:1px 6px;border-radius:var(--crm-radius-sm);background:var(--crm-surface-sunken);
    font-size:var(--crm-text-xs);font-weight:600;color:var(--crm-text-muted);
}
#crm-leads-page .crm-leads-toolbar__saved{display:flex;flex-wrap:wrap;align-items:center;gap:4px}
#crm-leads-page .crm-view-toggle{
    padding:3px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-md);background:var(--crm-surface);box-shadow:var(--crm-shadow-sm);
}
#crm-leads-page .crm-view-toggle button{
    min-width:72px;height:32px;padding:0 12px;border-radius:var(--crm-radius-sm);background:transparent;
    display:inline-flex;align-items:center;justify-content:center;gap:5px;
    font-size:var(--crm-text-xs);font-weight:600;color:var(--crm-text-muted);
}
#crm-leads-page .crm-view-toggle button[data-view="board"]::after{content:"Board"}
#crm-leads-page .crm-view-toggle button[data-view="list"]::after{content:"List"}
#crm-leads-page .crm-view-toggle button.is-active[data-view="board"]{background:var(--crm-brand);color:#fff}
#crm-leads-page .crm-view-toggle button.is-active[data-view="list"]{background:var(--crm-accent-soft);color:var(--crm-brand)}
#crm-leads-page .crm-leads-toolbar .btn{
    min-height:28px;padding:2px 10px!important;border-radius:var(--crm-radius-sm)!important;
    font-size:var(--crm-text-xs)!important;font-weight:500!important;
}
#crm-leads-page .crm-saved-filter-chip{
    display:inline-flex;align-items:center;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);
    background:var(--crm-surface);overflow:hidden;
}
#crm-leads-page .crm-saved-filter-chip__link{
    padding:2px 8px;font-size:var(--crm-text-xs);font-weight:500;color:var(--crm-link, var(--crm-brand));text-decoration:none;
}
#crm-leads-page .crm-saved-filter-chip__link:hover{text-decoration:underline}
#crm-leads-page .crm-saved-filter-chip__remove{
    padding:2px 6px;border:0;border-left:1px solid var(--crm-border);background:transparent;color:var(--crm-text-muted);cursor:pointer;
}
#crm-leads-page .crm-save-filter-inline{margin:0 16px 12px;padding:10px 12px;border:1px dashed var(--crm-border);border-radius:var(--crm-radius-sm);background:var(--crm-surface-sunken)}
#crm-leads-page .crm-save-filter-inline__inner{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
#crm-leads-page .crm-save-filter-inline .form-control{max-width:240px;height:32px;font-size:var(--crm-text-sm)}

#crm-leads-page .crm-inline-trigger{min-width:96px;max-width:160px}
#crm-leads-page .crm-inline-control--owner .crm-inline-trigger{min-width:112px}
#crm-leads-page .crm-inline-menu{
    padding:6px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-md);
    box-shadow:var(--crm-shadow-md);background:var(--crm-surface);
}

/* Board */
#crm-leads-page .crm-workflow-board{
    display:grid;grid-auto-flow:column;grid-auto-columns:minmax(280px,1fr);gap:12px;
    overflow-x:auto;padding:14px 16px 16px;scroll-snap-type:x proximity;
    scrollbar-width:thin;scrollbar-color:var(--crm-border-strong) transparent;
    background:var(--crm-surface-sunken);
}
#crm-leads-page .crm-board-column{
    scroll-snap-align:start;display:flex;flex-direction:column;
    min-height:460px;max-height:calc(100vh - 240px);
    border:1px solid var(--crm-border);border-radius:var(--crm-radius-md);background:var(--crm-surface);overflow:hidden;
    box-shadow:var(--crm-shadow-sm);
}
#crm-leads-page .crm-board-column__head{
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    padding:10px 12px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface);flex:0 0 auto;
}
#crm-leads-page .crm-board-column__title-wrap{display:flex;align-items:center;gap:8px;min-width:0}
#crm-leads-page .crm-board-column__dot{
    width:8px;height:8px;border-radius:50%;flex:0 0 8px;background:var(--crm-border-strong);
}
#crm-leads-page .crm-board-column[data-status="new"] .crm-board-column__dot{background:#8777D9}
#crm-leads-page .crm-board-column[data-status="contacted"] .crm-board-column__dot{background:#4C9AFF}
#crm-leads-page .crm-board-column[data-status="qualified"] .crm-board-column__dot{background:#00B8D9}
#crm-leads-page .crm-board-column[data-status="proposal_sent"] .crm-board-column__dot{background:#36B37E}
#crm-leads-page .crm-board-column[data-status="negotiation"] .crm-board-column__dot{background:#FF991F}
#crm-leads-page .crm-board-column[data-status="won"] .crm-board-column__dot{background:#00875A}
#crm-leads-page .crm-board-column[data-status="lost"] .crm-board-column__dot{background:#DE350B}
#crm-leads-page .crm-board-column[data-status="on_hold"] .crm-board-column__dot{background:#8993A4}
#crm-leads-page .crm-board-column__kicker{
    display:block;font-size:11px;font-weight:600;letter-spacing:.04em;
    text-transform:uppercase;color:var(--crm-text-muted);line-height:1.2;
}
#crm-leads-page .crm-board-column__head strong{display:block;font-size:18px;font-weight:600;color:var(--crm-text);line-height:1.2;font-variant-numeric:tabular-nums}
#crm-leads-page .crm-board-column__count{
    min-width:22px;height:20px;padding:0 6px;border-radius:var(--crm-radius-sm);
    display:grid;place-items:center;background:var(--crm-surface-sunken);
    color:var(--crm-text-muted);font-size:10px;font-weight:600;font-variant-numeric:tabular-nums;
}
#crm-leads-page .crm-board-column__body{
    display:flex;flex:1 1 auto;flex-direction:column;align-items:stretch;gap:8px;min-height:0;padding:8px;
    overflow-y:auto;overflow-x:hidden;scrollbar-width:thin;
}
#crm-leads-page .crm-board-column.is-drag-over{border-color:var(--crm-link, var(--crm-brand));box-shadow:0 0 0 2px var(--crm-brand-glow)}
#crm-leads-page .crm-board-card{
    position:relative;display:flex;flex:0 0 auto;width:100%;min-height:96px;overflow:hidden;
    border:1px solid var(--crm-border);border-radius:var(--crm-radius-md);
    background:var(--crm-surface);box-shadow:var(--crm-shadow-sm);cursor:pointer;
    transition:background var(--crm-duration) var(--crm-ease),border-color var(--crm-duration) var(--crm-ease),box-shadow var(--crm-duration) var(--crm-ease),transform var(--crm-duration) var(--crm-ease);
}
#crm-leads-page .crm-board-card__priority-rail{
    width:4px;flex:0 0 4px;align-self:stretch;background:var(--crm-border-strong);
}
#crm-leads-page .crm-board-card--priority-low .crm-board-card__priority-rail{background:#94a3b8}
#crm-leads-page .crm-board-card--priority-medium .crm-board-card__priority-rail{background:var(--crm-brand)}
#crm-leads-page .crm-board-card--priority-high .crm-board-card__priority-rail{background:#f97316}
#crm-leads-page .crm-board-card--priority-urgent .crm-board-card__priority-rail{background:#dc2626}
#crm-leads-page .crm-board-card__main{flex:1 1 auto;min-width:0;min-height:0;padding:10px 11px 10px 10px;display:flex;flex-direction:column}
#crm-leads-page .crm-board-card:hover,#crm-leads-page .crm-board-card:focus-visible{
    background:var(--crm-surface);border-color:rgba(15,39,74,.18);
    box-shadow:var(--crm-shadow-md);transform:translateY(-1px);outline:none;
}
#crm-leads-page .crm-board-card:hover .crm-board-card__quick-action{opacity:1;pointer-events:auto}
#crm-leads-page .crm-board-card.is-dragging{opacity:.62;cursor:grabbing;box-shadow:0 4px 12px rgba(15,39,74,.12)}
#crm-leads-page .crm-board-card[draggable="true"]{cursor:grab}
#crm-leads-page .crm-board-card__header{
    display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;
}
#crm-leads-page .crm-board-card__ref{min-width:0;flex:1}
#crm-leads-page .crm-board-card__lead-id{font-size:10px;font-weight:700;color:var(--crm-text-muted);letter-spacing:.04em;text-transform:uppercase}
#crm-leads-page .crm-board-card__category-pill{margin:0;font-size:10px}
#crm-leads-page .crm-board-card__flags{display:flex;align-items:center;gap:4px;flex-shrink:0}
#crm-leads-page .crm-board-card__quick-action{
    width:24px;height:24px;padding:0;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);
    background:var(--crm-surface);color:var(--crm-text-muted);display:grid;place-items:center;
    opacity:0;pointer-events:none;transition:opacity .15s ease,background .15s ease,color .15s ease,border-color .15s ease;
}
#crm-leads-page .crm-board-card__quick-action:hover{background:var(--crm-brand-soft);color:var(--crm-brand);border-color:rgba(15,39,74,.15)}
#crm-leads-page .crm-board-card__body{margin-bottom:10px}
#crm-leads-page .crm-board-card__title{
    color:var(--crm-text);font-size:13px;font-weight:700;line-height:1.35;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
#crm-leads-page .crm-board-card__meta{color:var(--crm-text-muted);font-size:11px;margin-top:3px}
#crm-leads-page .crm-board-card__source-row{
    display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:8px;
}
#crm-leads-page .crm-board-card__form-name{
    display:inline-flex;align-items:center;gap:4px;min-width:0;max-width:100%;
    padding:2px 7px;border-radius:999px;background:var(--crm-surface-sunken);color:var(--crm-text-muted);
    font-size:10px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-leads-page .crm-board-card__form-name iconify-icon{font-size:12px;color:var(--crm-brand);flex-shrink:0}
#crm-leads-page .crm-board-card__followup-row{margin-top:8px}
#crm-leads-page .crm-board-card__followup{
    display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;
    background:var(--crm-surface-sunken);font-size:10px;font-weight:600;color:var(--crm-text-muted);
}
#crm-leads-page .crm-board-card__followup iconify-icon{font-size:12px}
#crm-leads-page .crm-board-card__followup.is-attention{background:#FFEBE6;color:#BF2600}
#crm-leads-page .crm-lead-avatar{
    width:28px;height:28px;flex:0 0 28px;border-radius:50%;
    display:grid;place-items:center;
    background:linear-gradient(135deg,var(--crm-brand-soft),rgba(197,168,109,.18));
    color:var(--crm-brand);font-size:10px;font-weight:700;box-shadow:none;
}
#crm-leads-page .crm-lead-avatar--assignee{width:22px;height:22px;flex:0 0 22px;font-size:9px}
#crm-leads-page .crm-board-card__flag{
    width:22px;height:22px;border-radius:var(--crm-radius-sm);display:grid;place-items:center;
    font-size:12px;flex:0 0 22px;
}
#crm-leads-page .crm-board-card__flag.is-converted{background:#E3FCEF;color:#006644}
#crm-leads-page .crm-board-card__flag.is-attention{background:#FFEBE6;color:#BF2600}
#crm-leads-page .crm-board-card__footer{
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    padding-top:10px;border-top:1px solid var(--crm-surface-sunken);margin-top:auto;flex-shrink:0;
}
#crm-leads-page .crm-board-card__footer-left,
#crm-leads-page .crm-board-card__footer-right{display:flex;align-items:center;min-width:0}
#crm-leads-page .crm-board-card__footer-right{justify-content:flex-end;max-width:58%}
#crm-leads-page .crm-board-card .crm-inline-control{max-width:100%}
#crm-leads-page .crm-board-card .crm-inline-trigger{
    min-width:0;max-width:140px;min-height:26px;padding:2px 8px!important;
    border-radius:999px!important;font-size:11px!important;
}
#crm-leads-page .crm-board-card .crm-inline-control--owner .crm-inline-trigger{max-width:160px}
#crm-leads-page .crm-board-card .crm-inline-trigger__icon{font-size:12px!important}
#crm-leads-page .crm-board-card .crm-inline-trigger__label{
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:88px;
}
#crm-leads-page .crm-board-card .crm-inline-control--owner .crm-inline-trigger__label{max-width:96px}
#crm-leads-page .crm-board-card__assignee-static{
    display:inline-flex;align-items:center;gap:6px;min-width:0;color:var(--crm-text-muted);font-size:11px;font-weight:600;
}
#crm-leads-page .crm-board-card__assignee-static span:last-child{
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
#crm-leads-page .crm-board-empty{
    flex:0 0 auto;width:100%;
    min-height:72px;border:1px dashed var(--crm-border-strong);border-radius:var(--crm-radius-sm);
    display:grid;place-items:center;gap:4px;color:var(--crm-text-muted);font-size:11px;background:transparent;
}

#crm-leads-page .crm-filter-chip--form iconify-icon{font-size:14px;color:var(--crm-brand)}

.crm-source-badge{
    display:inline-flex;align-items:center;gap:5px;max-width:100%;
    padding:2px 8px;border-radius:999px;border:1px solid transparent;
    font-size:10px;font-weight:700;letter-spacing:.02em;white-space:nowrap;
}
.crm-source-badge iconify-icon{font-size:12px;flex-shrink:0}
.crm-source-badge--compact{padding:1px 7px;font-size:10px}
.crm-source-badge--form-submission{background:rgba(8,145,178,.12);color:#0e7490;border-color:rgba(8,145,178,.18)}
.crm-source-badge--facebook-lead-ads{background:rgba(37,99,235,.1);color:#1d4ed8;border-color:rgba(37,99,235,.16)}
.crm-source-badge--tiktok-lead-ads{background:rgba(15,23,42,.08);color:#0f172a;border-color:rgba(15,23,42,.12)}
.crm-source-badge--student-admission{background:rgba(22,163,74,.12);color:#15803d;border-color:rgba(22,163,74,.18)}
.crm-source-badge--file-import{background:rgba(197,168,109,.18);color:#9a7b42;border-color:rgba(197,168,109,.28)}
.crm-source-badge--manual{background:var(--crm-surface-sunken);color:var(--crm-text-muted);border-color:var(--crm-border)}

.crm-form-preview{display:grid;gap:8px;margin:0;padding:0}
.crm-form-preview__row{
    display:grid;grid-template-columns:minmax(96px,.9fr) minmax(0,1.4fr);gap:8px;
    padding:8px 10px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);background:var(--crm-surface-sunken);
}
.crm-form-preview__row dt{margin:0;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
.crm-form-preview__row dd{margin:0;font-size:12px;font-weight:500;color:var(--crm-text);line-height:1.4;word-break:break-word}
.crm-form-preview__empty{margin:0;font-size:12px;color:var(--crm-text-muted)}

.crm-lead-panel__section--form .crm-lead-panel__section-body{padding:12px}
.crm-lead-panel__section-link{font-size:11px;font-weight:600;color:var(--crm-link, var(--crm-brand));text-decoration:none}
.crm-lead-panel__section-link:hover{text-decoration:underline}
.crm-lead-panel__form-meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:10px}
.crm-lead-panel__form-name{
    display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:999px;
    background:var(--crm-brand-soft);color:var(--crm-brand);font-size:11px;font-weight:600;
}
.crm-lead-panel__form-date{font-size:11px;color:var(--crm-text-muted)}

/* List */
#crm-leads-page .crm-leads-list-shell{padding:0 16px 16px}
#crm-leads-page .crm-list-bulk-bar{
    display:flex;flex-wrap:wrap;align-items:center;gap:10px 14px;
    margin:12px 0 8px;padding:12px 14px;border:1px solid rgba(15,39,74,.15);border-radius:var(--crm-radius-md);
    background:linear-gradient(135deg,var(--crm-brand-soft),rgba(197,168,109,.12));
}
#crm-leads-page .crm-list-bulk-bar[hidden]{display:none!important}
#crm-leads-page .crm-list-bulk-bar__summary{display:inline-flex;align-items:baseline;gap:4px;color:var(--crm-brand);font-size:12px;font-weight:500}
#crm-leads-page .crm-list-bulk-bar__summary strong{font-size:14px;font-weight:700;font-variant-numeric:tabular-nums}
#crm-leads-page .crm-list-bulk-bar__actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px;flex:1 1 auto}
#crm-leads-page .crm-list-bulk-bar__field{display:inline-flex;align-items:center;gap:6px}
#crm-leads-page .crm-list-bulk-bar__field>span{font-size:11px;font-weight:600;color:var(--crm-text-muted);text-transform:uppercase;letter-spacing:.04em}
#crm-leads-page .crm-list-bulk-bar__field select{min-width:128px;height:28px;border:1px solid var(--crm-border-strong);border-radius:var(--crm-radius-sm);font-size:12px;background:var(--crm-surface)}
#crm-leads-page .crm-list-bulk-bar__btn{
    min-height:28px;padding:2px 10px;border:1px solid var(--crm-link, var(--crm-brand));border-radius:var(--crm-radius-sm);
    background:var(--crm-link, var(--crm-brand));color:#fff;font-size:12px;font-weight:600;cursor:pointer;
}
#crm-leads-page .crm-list-bulk-bar__btn:hover:not(:disabled){background:var(--crm-brand-hover);border-color:var(--crm-brand-hover)}
#crm-leads-page .crm-list-bulk-bar__btn:disabled{opacity:.45;cursor:not-allowed}
#crm-leads-page .crm-list-bulk-bar__divider{width:1px;height:22px;background:var(--crm-border)}
#crm-leads-page .crm-list-bulk-bar__clear{
    display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border:0;border-radius:var(--crm-radius-sm);
    background:transparent;color:var(--crm-text-muted);font-size:12px;font-weight:500;cursor:pointer;
}
#crm-leads-page .crm-list-bulk-bar__clear:hover{background:rgba(9,30,66,.06);color:var(--crm-text)}
#crm-leads-page .crm-list-row__select{display:flex;align-items:center;justify-content:center}
#crm-leads-page .crm-list-row__select--spacer{visibility:hidden}
#crm-leads-page .crm-list-select,#crm-leads-page .crm-list-select-all{
    width:16px;height:16px;margin:0;border:2px solid var(--crm-border-strong);border-radius:var(--crm-radius-sm);cursor:pointer;accent-color:var(--crm-link, var(--crm-brand));
}
#crm-leads-page .crm-leads-list-head__select{display:flex;align-items:center;justify-content:center}
#crm-leads-page .crm-list-status-rail{
    margin:12px 0 8px;padding:8px 10px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);background:var(--crm-surface-sunken);
}
#crm-leads-page .crm-list-status-rail__label{
    display:flex;align-items:center;gap:6px;margin-bottom:6px;
    color:var(--crm-text-muted);font-size:var(--crm-text-xs);font-weight:500;
}
#crm-leads-page .crm-list-status-rail__zones{display:flex;flex-wrap:wrap;gap:4px}
#crm-leads-page .crm-list-status-drop{
    padding:2px 8px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);
    background:var(--crm-surface);color:var(--crm-text-muted);
    font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.02em;
}
#crm-leads-page .crm-list-status-drop[data-status="new"]{background:#FFF7D6;color:#974F0C;border-color:#FF991F}
#crm-leads-page .crm-list-status-drop[data-status="contacted"]{background:var(--crm-brand-soft);color:var(--crm-brand);border-color:#4C9AFF}
#crm-leads-page .crm-list-status-drop[data-status="qualified"]{background:#EAE6FF;color:#403294;border-color:#8777D9}
#crm-leads-page .crm-list-status-drop[data-status="proposal_sent"]{background:#E6FCFF;color:#008DA6;border-color:#00B8D9}
#crm-leads-page .crm-list-status-drop[data-status="negotiation"]{background:#FFF0B3;color:var(--crm-text);border-color:#FF991F}
#crm-leads-page .crm-list-status-drop[data-status="won"]{background:#E3FCEF;color:#006644;border-color:#36B37E}
#crm-leads-page .crm-list-status-drop[data-status="lost"]{background:#FFEBE6;color:#BF2600;border-color:#FF5630}
#crm-leads-page .crm-list-status-drop[data-status="on_hold"]{background:var(--crm-border);color:var(--crm-text-muted);border-color:var(--crm-border-strong)}
#crm-leads-page .crm-list-status-drop.is-drag-over{border-color:var(--crm-link, var(--crm-brand))!important;background:var(--crm-brand-soft)!important;color:var(--crm-link, var(--crm-brand))!important}
#crm-leads-page .crm-leads-list-head{
    display:grid;grid-template-columns:28px 28px minmax(210px,1.5fr) repeat(4,minmax(96px,.72fr)) minmax(76px,.48fr) 56px;
    gap:8px;padding:6px 12px;color:var(--crm-text-muted);font-size:var(--crm-text-xs);font-weight:600;
    letter-spacing:.04em;text-transform:uppercase;border-bottom:1px solid var(--crm-border);
}
#crm-leads-page .crm-leads-list{display:flex;flex-direction:column;gap:0}
#crm-leads-page .crm-list-row{
    display:grid;grid-template-columns:28px 28px minmax(210px,1.5fr) repeat(4,minmax(96px,.72fr)) minmax(76px,.48fr) 56px;
    gap:8px;align-items:center;padding:8px 12px;border-bottom:1px solid var(--crm-border);
    background:var(--crm-surface);box-shadow:none;cursor:pointer;transition:background .1s ease;
}
#crm-leads-page .crm-list-row:hover,#crm-leads-page .crm-list-row:focus-visible{
    background:var(--crm-surface-sunken);outline:none;transform:none;border-color:var(--crm-border);
}
#crm-leads-page .crm-list-row.is-selected{background:var(--crm-brand-soft)}
#crm-leads-page .crm-list-row.is-selected:hover{background:rgba(15,39,74,.1)}
#crm-leads-page .crm-list-row.is-dragging{opacity:.55;cursor:grabbing}
#crm-leads-page .crm-list-row.is-status-updated{background:#E3FCEF}
#crm-leads-page .crm-list-row__handle{
    width:28px;height:28px;border-radius:var(--crm-radius-sm);display:grid;place-items:center;
    background:transparent;border:1px solid transparent;color:var(--crm-text-muted);cursor:grab;
}
#crm-leads-page .crm-list-row__handle:hover{background:var(--crm-surface-sunken);border-color:var(--crm-border);color:var(--crm-text-muted)}
#crm-leads-page .crm-list-row__identity{display:flex;align-items:center;gap:8px;min-width:0}
#crm-leads-page .crm-list-row__name{font-size:var(--crm-text-sm);font-weight:500;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#crm-leads-page .crm-list-row__meta{color:var(--crm-text-muted);font-size:var(--crm-text-xs);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#crm-leads-page .crm-list-row__source{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:4px}
#crm-leads-page .crm-list-row__form-name{font-size:10px;font-weight:600;color:var(--crm-text-muted)}
#crm-leads-page .crm-list-row__field{display:flex;flex-direction:column;gap:2px;min-width:0}
#crm-leads-page .crm-list-row__label{display:none}
#crm-leads-page .crm-list-row__value{color:var(--crm-text-muted);font-size:var(--crm-text-sm);font-weight:400}
#crm-leads-page .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:2px}
#crm-leads-page .crm-list-action{
    width:28px;height:28px;border:0;border-radius:var(--crm-radius-sm);display:grid;place-items:center;
    background:transparent;color:var(--crm-text-muted);transition:background .1s ease,color .1s ease;
}
#crm-leads-page .crm-list-action:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
#crm-leads-page .crm-leads-list-empty{
    display:grid;place-items:center;gap:6px;padding:40px 16px;color:var(--crm-text-muted);text-align:center;
}
#crm-leads-page .crm-leads-list-empty strong{color:var(--crm-text);font-size:var(--crm-text-base));font-weight:500}

/* Pagination */
#crm-leads-page .crm-leads-pagination{
    display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;
    margin:0;padding:10px 16px;border-top:1px solid var(--crm-border);background:var(--crm-surface-sunken);box-shadow:none;
}
#crm-leads-page .crm-leads-pagination__summary{color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#crm-leads-page .crm-leads-pagination__summary strong{color:var(--crm-text);font-weight:600}
#crm-leads-page .crm-leads-pagination__per-page{display:flex;align-items:center;gap:6px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#crm-leads-page .crm-leads-pagination__per-page select{min-width:96px;height:28px;border-color:var(--crm-border);font-size:var(--crm-text-sm);border-radius:var(--crm-radius-sm)}
#crm-leads-page .crm-page-btn{
    min-height:28px;padding:0 10px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);
    background:var(--crm-surface);color:var(--crm-text-muted);font-size:var(--crm-text-sm);font-weight:500;
}
#crm-leads-page .crm-page-btn:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
#crm-leads-page .crm-page-indicator{color:var(--crm-text-muted);font-size:var(--crm-text-sm)}

/* Drawer & lead preview panel */
.crm-lead-drawer{width:min(540px,96vw)!important;border-left:1px solid var(--crm-border);box-shadow:-12px 0 32px rgba(9,30,66,.14)}
.crm-lead-drawer__header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface)}
.crm-lead-drawer__heading{min-width:0}
.crm-lead-drawer__eyebrow{font-size:16px;line-height:1.3;font-weight:600;color:var(--crm-text);letter-spacing:-.01em;text-transform:none}
.crm-lead-drawer__subtitle{margin-top:2px;color:var(--crm-text-muted);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.crm-lead-drawer__close{width:32px;height:32px;padding:0;border:0;border-radius:var(--crm-radius-sm);background:transparent;color:var(--crm-text-muted);display:grid;place-items:center;cursor:pointer;flex-shrink:0}
.crm-lead-drawer__close:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
.crm-lead-drawer__body{padding:0;background:var(--crm-surface-sunken);overflow-y:auto}

.crm-lead-panel{padding:0}
.crm-lead-panel__header{padding:16px;background:var(--crm-surface);border-bottom:1px solid var(--crm-border)}
.crm-lead-panel__identity{display:flex;gap:12px;align-items:flex-start}
.crm-lead-avatar--panel{width:40px;height:40px;flex:0 0 40px;font-size:12px;background:var(--crm-surface-sunken);color:var(--crm-text-muted)}
.crm-lead-panel__ref{font-size:11px;font-weight:600;color:var(--crm-text-muted);text-transform:uppercase;letter-spacing:.04em}
.crm-lead-panel__title{margin:2px 0 8px;font-size:22px;line-height:1.2;font-weight:500;color:var(--crm-text);letter-spacing:-.02em}
.crm-lead-panel__contact{display:flex;flex-wrap:wrap;gap:6px}
.crm-lead-panel__contact-chip{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:var(--crm-radius-sm);background:var(--crm-surface-sunken);color:var(--crm-text-muted);font-size:12px;text-decoration:none;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.crm-lead-panel__contact-chip:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
.crm-lead-panel__controls{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:12px}
.crm-lead-panel__controls .crm-status-pill,.crm-lead-panel__controls .crm-inline-trigger{min-height:22px;line-height:22px}
.crm-lead-panel__controls .crm-category-badge{margin-top:0}

.crm-lead-panel__alert{display:flex;gap:10px;align-items:flex-start;margin:0;padding:10px 16px;background:#FFEBE6;color:#BF2600;font-size:12px;border-bottom:1px solid #FFBDAD}
.crm-lead-panel__alert strong{display:block;margin-bottom:2px;font-weight:600}
.crm-lead-panel__alert span{color:#DE350B}

.crm-lead-panel__toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:4px;padding:10px 16px;background:var(--crm-surface);border-bottom:1px solid var(--crm-border)}
.crm-lead-panel__tool,.crm-lead-panel__tool-form button{
    display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:4px 12px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);
    background:var(--crm-surface);color:var(--crm-text-muted);font-size:12px;font-weight:500;text-decoration:none;cursor:pointer;transition:background .1s ease,border-color .1s ease;
}
.crm-lead-panel__tool:hover{background:var(--crm-surface-sunken);color:var(--crm-text);border-color:var(--crm-border-strong)}
.crm-lead-panel__tool--primary{background:var(--crm-link, var(--crm-brand));border-color:var(--crm-link, var(--crm-brand));color:#fff}
.crm-lead-panel__tool--primary:hover{background:var(--crm-brand-hover);border-color:var(--crm-brand-hover);color:#fff}
.crm-lead-panel__tool--success{background:#E3FCEF;border-color:#36B37E;color:#006644}
.crm-lead-panel__tool-form{margin:0;padding:0;border:0;background:transparent}

.crm-lead-panel__properties{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0;margin:0;padding:12px 16px;background:var(--crm-surface);border-bottom:1px solid var(--crm-border)}
.crm-lead-panel__property{display:grid;gap:2px;padding:8px 10px;border:1px solid var(--crm-surface-sunken);border-radius:var(--crm-radius-sm);background:var(--crm-surface-sunken)}
.crm-lead-panel__property--wide{grid-column:1/-1}
.crm-lead-panel__property dt{margin:0;font-size:11px;font-weight:600;color:var(--crm-text-muted);text-transform:uppercase;letter-spacing:.04em}
.crm-lead-panel__property dd{margin:0;font-size:13px;font-weight:500;color:var(--crm-text);line-height:1.4;word-break:break-word}
.crm-lead-panel__property dd a{color:var(--crm-link, var(--crm-brand));text-decoration:none}
.crm-lead-panel__property dd a:hover{text-decoration:underline}

.crm-lead-panel__section{margin:12px 16px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);background:var(--crm-surface);overflow:hidden}
.crm-lead-panel__section-head{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 12px;border-bottom:1px solid var(--crm-surface-sunken);background:var(--crm-surface-sunken)}
.crm-lead-panel__section-head h3{display:flex;align-items:center;gap:6px;margin:0;font-size:13px;font-weight:600;color:var(--crm-text)}
.crm-lead-panel__section-meta{font-size:11px;font-weight:500;color:var(--crm-text-muted)}
.crm-lead-panel__section-body{padding:0 12px 12px}
.crm-lead-panel__note{padding:10px 0;border-bottom:1px solid var(--crm-surface-sunken)}
.crm-lead-panel__note:last-of-type{border-bottom:0}
.crm-lead-panel__note-head{display:flex;justify-content:space-between;gap:8px;margin-bottom:4px}
.crm-lead-panel__note-head strong{font-size:12px;font-weight:600;color:var(--crm-text)}
.crm-lead-panel__note-head time{font-size:11px;color:var(--crm-text-muted)}
.crm-lead-panel__note p{margin:0;font-size:13px;line-height:1.45;color:var(--crm-text-muted)}
.crm-lead-panel__note-form{margin-top:10px;padding-top:10px;border-top:1px solid var(--crm-surface-sunken)}
.crm-lead-panel__note-form textarea{border:2px solid var(--crm-border);border-radius:var(--crm-radius-sm);font-size:13px;resize:vertical}
.crm-lead-panel__note-form textarea:focus{border-color:var(--crm-link, var(--crm-brand));box-shadow:none}
.crm-lead-panel__note-submit{margin-top:8px;min-height:32px;padding:4px 12px;border:0;border-radius:var(--crm-radius-sm);background:var(--crm-link, var(--crm-brand));color:var(--crm-surface);font-size:12px;font-weight:500;cursor:pointer}
.crm-lead-panel__note-submit:hover{background:var(--crm-brand-hover)}

.crm-lead-panel__timeline{padding:8px 12px 12px}
.crm-lead-panel__timeline-item{display:flex;gap:10px;padding:8px 0;border-bottom:1px solid var(--crm-surface-sunken)}
.crm-lead-panel__timeline-item:last-child{border-bottom:0;padding-bottom:0}
.crm-lead-panel__timeline-icon{width:28px;height:28px;border-radius:var(--crm-radius-sm);background:var(--crm-brand-soft);color:var(--crm-brand);display:grid;place-items:center;flex:0 0 28px}
.crm-lead-panel__timeline-title{font-size:12px;font-weight:600;color:var(--crm-text);text-transform:capitalize}
.crm-lead-panel__timeline-body p{margin:2px 0;font-size:13px;color:var(--crm-text-muted);line-height:1.4}
.crm-lead-panel__timeline-body small{font-size:11px;color:var(--crm-text-muted)}
.crm-lead-panel__empty{margin:8px 0;color:var(--crm-text-muted);font-size:13px}

.crm-lead-panel .crm-status-pill,.crm-lead-panel .crm-inline-trigger{
    min-height:20px;padding:0 6px;border-radius:var(--crm-radius-sm);border:none;font-size:11px;font-weight:600;
    box-shadow:none;text-transform:capitalize;
}
.crm-lead-panel-loading{display:grid;place-items:center;gap:10px;padding:48px 24px;color:var(--crm-text-muted);font-size:13px}
.crm-lead-panel-loading__spinner{width:24px;height:24px;border:2px solid var(--crm-border);border-top-color:var(--crm-brand);border-radius:50%;animation:crmLeadSpin .8s linear infinite}
@keyframes crmLeadSpin{to{transform:rotate(360deg)}}

.crm-lead-panel--edit{background:var(--crm-surface)}
.crm-lead-panel__header--edit{padding:16px 16px 12px;border-bottom:1px solid var(--crm-border)}
.crm-lead-panel__edit-subtitle{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
.crm-lead-panel__edit-body{padding:16px}
.crm-lead-panel__edit-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.crm-lead-panel__field{display:grid;gap:6px;min-width:0}
.crm-lead-panel__field--wide{grid-column:1/-1}
.crm-lead-panel__field>span{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
.crm-lead-panel__field .form-control,
.crm-lead-panel__field .form-select{
    min-height:36px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);
    font-size:13px;color:var(--crm-text);box-shadow:none;
}
.crm-lead-panel__field .form-control:focus,
.crm-lead-panel__field .form-select:focus{border-color:var(--crm-brand);box-shadow:0 0 0 3px var(--crm-brand-soft)}
.crm-lead-panel__field textarea.form-control{min-height:96px;resize:vertical}
.crm-lead-panel__edit-errors{
    margin-top:12px;padding:10px 12px;border:1px solid rgba(220,38,38,.25);border-radius:var(--crm-radius-sm);
    background:rgba(254,242,242,.9);color:#b91c1c;font-size:12px;line-height:1.45;
}
.crm-lead-panel__edit-actions{
    display:flex;justify-content:flex-end;gap:8px;padding:12px 16px 16px;
    border-top:1px solid var(--crm-border);background:var(--crm-surface-sunken);
}
.crm-lead-panel__edit-actions .crm-lead-panel__tool{min-height:36px}
@media(max-width:480px){
    .crm-lead-panel__edit-grid{grid-template-columns:1fr}
}

@media(max-width:1199px){
    #crm-leads-page .crm-filter-workspace__advanced-grid{grid-template-columns:repeat(3,minmax(130px,1fr))}
    #crm-leads-page .crm-page-header{align-items:flex-start;flex-direction:column}
    #crm-leads-page .crm-page-header>div:last-child{width:100%}
}
@media(max-width:767px){
    #crm-leads-page{padding:12px}
    #crm-leads-page .crm-filter-workspace__search-row{flex-direction:column;align-items:stretch}
    #crm-leads-page .crm-filter-workspace__search-actions{width:100%}
    #crm-leads-page .crm-filter-workspace__btn{flex:1}
    #crm-leads-page .crm-filter-workspace__quick-label{width:100%;min-width:0}
    #crm-leads-page .crm-filter-workspace__advanced-grid{grid-template-columns:1fr 1fr}
    #crm-leads-page .crm-leads-toolbar{flex-direction:column;align-items:stretch}
    #crm-leads-page .crm-workflow-board{grid-auto-columns:minmax(260px,88vw)}
    #crm-leads-page .crm-leads-list-head{display:none}
    #crm-leads-page .crm-list-row{grid-template-columns:28px 1fr;gap:8px;padding:12px}
    #crm-leads-page .crm-list-row__select{grid-column:1;grid-row:1}
    #crm-leads-page .crm-list-row__handle{grid-column:1;grid-row:2}
    #crm-leads-page .crm-list-row__identity{grid-column:2;grid-row:1 / span 2}
    #crm-leads-page .crm-list-row__label{display:block}
    #crm-leads-page .crm-list-row__actions{justify-content:flex-start}
    #crm-leads-page .crm-leads-pagination{flex-direction:column;align-items:stretch}
}
@media(max-width:480px){
    #crm-leads-page .crm-filter-workspace__advanced-grid{grid-template-columns:1fr}
    #crm-leads-page .crm-metrics-strip__items{width:100%}
}
</style>
