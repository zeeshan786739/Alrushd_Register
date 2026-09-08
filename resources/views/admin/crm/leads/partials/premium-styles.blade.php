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

/* Filter workspace — smart search first */
#crm-leads-page .crm-filter-workspace{padding:14px 16px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#crm-leads-page .crm-smart-search{position:relative;margin-bottom:12px}
#crm-leads-page .crm-smart-search__shell{
    display:flex;align-items:center;gap:10px;min-height:46px;padding:6px 8px 6px 12px;
    border:1px solid rgba(15,39,74,.14);border-radius:14px;background:var(--crm-surface);
    box-shadow:0 8px 24px rgba(15,39,74,.06);transition:border-color .15s ease,box-shadow .15s ease;
}
#crm-leads-page .crm-smart-search__shell:focus-within{
    border-color:rgba(197,168,109,.65);box-shadow:0 10px 28px rgba(15,39,74,.08),0 0 0 3px rgba(197,168,109,.16);
}
#crm-leads-page .crm-smart-search__badge{
    display:inline-flex;align-items:center;gap:5px;flex-shrink:0;padding:4px 8px;border-radius:999px;
    background:linear-gradient(135deg,rgba(15,39,74,.92),rgba(197,168,109,.82));color:#fff;
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;
}
#crm-leads-page .crm-smart-search__badge iconify-icon{font-size:13px}
#crm-leads-page .crm-smart-search__input{
    flex:1;border:0;background:transparent;min-width:0;padding:8px 0;font-size:14px;color:var(--crm-text);outline:none;
}
#crm-leads-page .crm-smart-search__input::placeholder{color:var(--crm-text-muted)}
#crm-leads-page .crm-smart-search__go{
    width:36px;height:36px;border:0;border-radius:10px;background:var(--crm-brand);color:#fff;
    display:grid;place-items:center;cursor:pointer;flex-shrink:0;
}
#crm-leads-page .crm-smart-search__go:hover{background:var(--crm-brand-hover)}
#crm-leads-page .crm-smart-search__panel{
    position:absolute;left:0;right:0;top:calc(100% + 8px);z-index:30;padding:12px;
    border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);
    box-shadow:0 16px 40px rgba(15,39,74,.12);
}
#crm-leads-page .crm-smart-search__interpretation{
    display:flex;align-items:center;gap:8px;margin-bottom:10px;padding:8px 10px;border-radius:10px;
    background:rgba(22,163,74,.08);color:#15803d;font-size:12px;font-weight:600;
}
#crm-leads-page .crm-smart-search__section-label{
    display:block;margin-bottom:8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--crm-text-muted);
}
#crm-leads-page .crm-smart-search__suggestions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
#crm-leads-page .crm-smart-search__suggestion{
    display:flex;flex-direction:column;align-items:flex-start;gap:2px;padding:10px 12px;border:1px solid var(--crm-border);
    border-radius:12px;background:var(--crm-surface-sunken);text-align:left;cursor:pointer;transition:background .1s ease,border-color .1s ease;
}
#crm-leads-page .crm-smart-search__suggestion:hover{background:var(--crm-brand-soft);border-color:rgba(15,39,74,.16)}
#crm-leads-page .crm-smart-search__suggestion strong{font-size:12px;color:var(--crm-text)}
#crm-leads-page .crm-smart-search__suggestion span{font-size:11px;color:var(--crm-text-muted)}

#crm-leads-page .crm-smart-views{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
#crm-leads-page .crm-smart-view{
    display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:6px 12px;border:1px solid var(--crm-border);
    border-radius:999px;background:var(--crm-surface);color:var(--crm-text-muted);font-size:12px;font-weight:600;
    text-decoration:none;cursor:pointer;transition:background .1s ease,border-color .1s ease,color .1s ease;
}
#crm-leads-page .crm-smart-view:hover{background:var(--crm-brand-soft);border-color:rgba(15,39,74,.16);color:var(--crm-brand)}
#crm-leads-page .crm-smart-view.is-active{background:var(--crm-brand);border-color:var(--crm-brand);color:#fff}
#crm-leads-page .crm-smart-view--more{margin-left:auto}
#crm-leads-page .crm-smart-view--clear{border-style:dashed;color:var(--crm-text-muted);background:transparent}
#crm-leads-page .crm-smart-view__count{
    min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--crm-brand);color:#fff;
    font-size:10px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;
}
#crm-leads-page .crm-smart-view.is-active .crm-smart-view__count{background:rgba(255,255,255,.18)}

#crm-leads-page .crm-filter-workspace__btn{
    display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:4px 12px;
    border-radius:var(--crm-radius-sm);font-size:var(--crm-text-sm);font-weight:500;text-decoration:none;
    border:1px solid transparent;cursor:pointer;transition:background .1s ease,border-color .1s ease;
}
#crm-leads-page .crm-filter-workspace__btn--primary{background:var(--crm-link, var(--crm-brand));border-color:var(--crm-link, var(--crm-brand));color:#fff}
#crm-leads-page .crm-filter-workspace__btn--primary:hover{background:var(--crm-brand-hover);border-color:var(--crm-brand-hover);color:#fff}
#crm-leads-page .crm-filter-workspace__btn--ghost{background:transparent;border-color:var(--crm-border-strong);color:var(--crm-text-muted)}
#crm-leads-page .crm-filter-workspace__btn--ghost:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}

#crm-leads-page .crm-filter-workspace__active{
    display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:10px;padding-top:10px;border-top:1px dashed var(--crm-border);
}
#crm-leads-page .crm-filter-workspace__active-label{
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--crm-text-muted);
}
#crm-leads-page .crm-filter-workspace__active-list{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
#crm-leads-page .crm-filter-workspace__context{margin-top:8px;font-size:12px;color:var(--crm-text-muted)}
#crm-leads-page .crm-active-filter{
    display:inline-flex;align-items:center;gap:5px;max-width:100%;
    padding:4px 8px 4px 10px;border:1px solid rgba(15,39,74,.14);border-radius:999px;
    background:linear-gradient(135deg,rgba(15,39,74,.05),rgba(197,168,109,.08));
    color:var(--crm-brand);font-size:11px;font-weight:600;text-decoration:none;
}
#crm-leads-page .crm-active-filter:hover{background:var(--crm-brand-soft);border-color:rgba(15,39,74,.2)}
#crm-leads-page .crm-active-filter__label{color:var(--crm-text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.03em;font-size:10px}
#crm-leads-page .crm-active-filter__value{color:var(--crm-text);font-weight:600}
#crm-leads-page .crm-active-filter iconify-icon{font-size:14px;color:var(--crm-text-muted);flex-shrink:0}

#crm-leads-page .crm-filter-workspace__advanced{
    margin-top:12px;padding-top:12px;border-top:1px solid var(--crm-border);
}
#crm-leads-page .crm-filter-workspace__advanced--compact .crm-filter-workspace__advanced-grid{
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
    display:grid;grid-auto-flow:column;grid-auto-columns:340px;gap:12px;
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
#crm-leads-page .crm-board-column__body{padding:12px;gap:12px}
#crm-leads-page .crm-board-card{
    position:relative;display:flex;flex:0 0 auto;width:100%;min-height:228px;overflow:hidden;
    border:1px solid var(--crm-border);border-radius:12px;
    background:linear-gradient(180deg,var(--crm-surface) 0%,rgba(248,250,252,.92) 100%);
    box-shadow:0 1px 2px rgba(15,39,74,.05),0 4px 14px rgba(15,39,74,.04);
    cursor:pointer;
    transition:background var(--crm-duration) var(--crm-ease),border-color var(--crm-duration) var(--crm-ease),box-shadow var(--crm-duration) var(--crm-ease),transform var(--crm-duration) var(--crm-ease);
}
#crm-leads-page .crm-board-card__priority-rail{
    width:5px;flex:0 0 5px;align-self:stretch;
}
#crm-leads-page .crm-board-card--priority-low .crm-board-card__priority-rail{background:linear-gradient(180deg,#cbd5e1,#94a3b8)}
#crm-leads-page .crm-board-card--priority-medium .crm-board-card__priority-rail{background:linear-gradient(180deg,#1a3a5c,var(--crm-brand))}
#crm-leads-page .crm-board-card--priority-high .crm-board-card__priority-rail{background:linear-gradient(180deg,#fb923c,#ea580c)}
#crm-leads-page .crm-board-card--priority-urgent .crm-board-card__priority-rail{background:linear-gradient(180deg,#f87171,#dc2626)}
#crm-leads-page .crm-board-card__main{
    flex:1 1 auto;min-width:0;min-height:0;padding:14px 15px 13px 13px;
    display:flex;flex-direction:column;gap:0;
}
#crm-leads-page .crm-board-card:hover,#crm-leads-page .crm-board-card:focus-visible{
    background:var(--crm-surface);border-color:rgba(15,39,74,.2);
    box-shadow:0 8px 24px rgba(15,39,74,.1);transform:translateY(-2px);outline:none;
}
#crm-leads-page .crm-board-card:hover .crm-board-card__quick-action{opacity:1;pointer-events:auto}
#crm-leads-page .crm-board-card.is-dragging{opacity:.62;cursor:grabbing;box-shadow:0 4px 12px rgba(15,39,74,.12)}
#crm-leads-page .crm-board-card[draggable="true"]{cursor:grab}
#crm-leads-page .crm-board-card__header{
    display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:12px;
}
#crm-leads-page .crm-board-card__ref{
    display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-width:0;flex:1;
}
#crm-leads-page .crm-board-card__lead-id{
    font-size:10px;font-weight:700;color:var(--crm-text-muted);letter-spacing:.05em;text-transform:uppercase;
}
#crm-leads-page .crm-board-card__category-pill{margin:0;font-size:10px;padding:3px 8px}
#crm-leads-page .crm-board-card__flags{display:flex;align-items:center;gap:5px;flex-shrink:0}
#crm-leads-page .crm-board-card__quick-action{
    width:28px;height:28px;padding:0;border:1px solid var(--crm-border);border-radius:8px;
    background:var(--crm-surface);color:var(--crm-text-muted);display:grid;place-items:center;
    opacity:0;pointer-events:none;transition:opacity .15s ease,background .15s ease,color .15s ease,border-color .15s ease;
}
    #crm-leads-page .crm-board-card__quick-action:hover{background:var(--crm-brand-soft);color:var(--crm-brand);border-color:rgba(15,39,74,.15)}
    #crm-leads-page .crm-board-card__quick-action.is-danger:hover{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
    #crm-leads-page .crm-lead-removing{opacity:0;transform:scale(.98);transition:.18s ease}
#crm-leads-page .crm-board-card__identity{
    display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;
}
#crm-leads-page .crm-lead-avatar--board{
    width:36px;height:36px;flex:0 0 36px;font-size:12px;
    border:2px solid rgba(255,255,255,.9);box-shadow:0 2px 6px rgba(15,39,74,.08);
}
#crm-leads-page .crm-board-card__identity-copy{min-width:0}
#crm-leads-page .crm-board-card__title{
    color:var(--crm-text);font-size:15px;font-weight:700;line-height:1.35;letter-spacing:-.01em;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
#crm-leads-page .crm-board-card__company{
    margin-top:2px;font-size:11px;font-weight:500;color:var(--crm-text-muted);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-leads-page .crm-board-card__contacts{
    display:flex;flex-direction:column;gap:5px;margin-bottom:10px;
}
#crm-leads-page .crm-board-card__contact{
    display:inline-flex;align-items:center;gap:6px;min-width:0;max-width:100%;
    padding:4px 8px;border-radius:8px;background:var(--crm-surface-sunken);
    color:var(--crm-text-muted);font-size:11px;font-weight:500;line-height:1.3;
}
#crm-leads-page .crm-board-card__contact iconify-icon{flex-shrink:0;font-size:14px;color:var(--crm-brand);opacity:.85}
#crm-leads-page .crm-board-card__contact span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#crm-leads-page .crm-board-card__contact--empty{background:transparent;padding-left:0;font-style:italic}
#crm-leads-page .crm-board-card__tags{
    display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-bottom:10px;
}
#crm-leads-page .crm-board-card__form-name{
    display:inline-flex;align-items:center;gap:4px;min-width:0;max-width:100%;
    padding:3px 8px;border-radius:999px;background:rgba(15,39,74,.06);color:var(--crm-text-muted);
    font-size:10px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-leads-page .crm-board-card__form-name iconify-icon{font-size:12px;color:var(--crm-brand);flex-shrink:0}
#crm-leads-page .crm-board-card__followup{
    display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:999px;
    background:var(--crm-surface-sunken);font-size:10px;font-weight:600;color:var(--crm-text-muted);
}
#crm-leads-page .crm-board-card__followup iconify-icon{font-size:12px}
#crm-leads-page .crm-board-card__followup.is-attention{background:#FFEBE6;color:#BF2600}
#crm-leads-page .crm-board-card__record-meta{
    display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:6px;
    margin-top:auto;padding-top:10px;margin-bottom:10px;
    border-top:1px dashed var(--crm-border);
    color:var(--crm-text-muted);font-size:10px;font-weight:600;
}
#crm-leads-page .crm-board-card__record-note{font-style:italic;opacity:.85}
#crm-leads-page .crm-lead-avatar{
    width:28px;height:28px;flex:0 0 28px;border-radius:50%;
    display:grid;place-items:center;
    background:linear-gradient(135deg,var(--crm-brand-soft),rgba(197,168,109,.18));
    color:var(--crm-brand);font-size:10px;font-weight:700;box-shadow:none;
}
#crm-leads-page .crm-lead-avatar--assignee{width:24px;height:24px;flex:0 0 24px;font-size:9px}
#crm-leads-page .crm-board-card__flag{
    width:24px;height:24px;border-radius:8px;display:grid;place-items:center;
    font-size:13px;flex:0 0 24px;
}
#crm-leads-page .crm-board-card__flag.is-converted{background:#E3FCEF;color:#006644}
#crm-leads-page .crm-board-card__flag.is-attention{background:#FFEBE6;color:#BF2600}
#crm-leads-page .crm-board-card__footer{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding-top:12px;border-top:1px solid var(--crm-border);flex-shrink:0;
}
#crm-leads-page .crm-board-card__footer-left,
#crm-leads-page .crm-board-card__footer-right{display:flex;align-items:center;min-width:0}
#crm-leads-page .crm-board-card__footer-right{justify-content:flex-end;max-width:56%}
#crm-leads-page .crm-board-card .crm-inline-control{max-width:100%}
#crm-leads-page .crm-board-card .crm-inline-trigger{
    min-width:0;max-width:148px;min-height:30px;padding:4px 10px!important;
    border-radius:999px!important;font-size:11px!important;font-weight:600!important;
    background:var(--crm-surface)!important;border-color:var(--crm-border)!important;
}
#crm-leads-page .crm-board-card .crm-inline-control--owner .crm-inline-trigger{max-width:168px}
#crm-leads-page .crm-board-card .crm-inline-trigger__icon{font-size:13px!important}
#crm-leads-page .crm-board-card .crm-inline-trigger__label{
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:92px;
}
#crm-leads-page .crm-board-card .crm-inline-control--owner .crm-inline-trigger__label{max-width:100px}
#crm-leads-page .crm-board-card__assignee-static{
    display:inline-flex;align-items:center;gap:7px;min-width:0;color:var(--crm-text-muted);font-size:11px;font-weight:600;
}
#crm-leads-page .crm-board-card__assignee-static span:last-child{
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
#crm-leads-page .crm-board-card__body{margin-bottom:10px}
#crm-leads-page .crm-board-card__meta{color:var(--crm-text-muted);font-size:11px;margin-top:4px}
#crm-leads-page .crm-board-card__source-row{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:8px}
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

#crm-leads-page .crm-board-card--submission{
    border-style:dashed;border-color:rgba(8,145,178,.35);background:linear-gradient(180deg,rgba(8,145,178,.04),var(--crm-surface));
}
#crm-leads-page .crm-board-card__priority-rail--submission{background:#0891b2}
#crm-leads-page .crm-board-card__submission-badge{
    display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;
    background:rgba(8,145,178,.12);color:#0e7490;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;
}
#crm-leads-page .crm-board-card__flag.is-pending{background:#FFF7D6;color:#974F0C}
#crm-leads-page .crm-board-card__submission-preview{
    margin-top:8px;font-size:10px;line-height:1.4;color:var(--crm-text-muted);
}
#crm-leads-page .crm-board-card__submitted-at{
    display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;color:var(--crm-text-muted);
}
#crm-leads-page .crm-board-card__submitted-at iconify-icon{font-size:12px}
#crm-leads-page .crm-board-card__convert-form{margin:0}
#crm-leads-page .crm-board-card__convert-btn{
    min-height:26px;padding:2px 10px;border:1px solid rgba(8,145,178,.25);border-radius:999px;
    background:rgba(8,145,178,.1);color:#0e7490;font-size:10px;font-weight:700;cursor:pointer;
}
#crm-leads-page .crm-board-card__convert-btn:hover{background:rgba(8,145,178,.18);border-color:rgba(8,145,178,.35)}
#crm-leads-page .crm-lead-avatar--submission{background:rgba(8,145,178,.12);color:#0e7490}
#crm-leads-page .crm-submission-row{background:linear-gradient(90deg,rgba(8,145,178,.05),var(--crm-surface))}
#crm-leads-page .crm-list-row__intake-badge{
    padding:1px 7px;border-radius:999px;background:#FFF7D6;color:#974F0C;font-size:10px;font-weight:700;
}
#crm-leads-page .crm-list-action.is-convert{color:#0e7490}
#crm-leads-page .crm-list-action.is-convert:hover{background:rgba(8,145,178,.12);color:#0e7490}

.crm-submission-panel .crm-lead-panel__alert{
    background:linear-gradient(135deg,rgba(8,145,178,.08),rgba(197,168,109,.08));color:#0e7490;border-bottom:1px solid rgba(8,145,178,.15);
}
.crm-submission-panel .crm-lead-panel__alert strong{display:block;color:var(--crm-text)}
.crm-submission-panel .crm-lead-panel__alert span{color:var(--crm-text-muted)}

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
.crm-lead-modal .modal-dialog{max-width:min(980px,96vw);margin:1.25rem auto}
.crm-lead-modal__dialog--maximized{max-width:min(1280px,98vw)!important;width:98vw;margin:.75rem auto}
.crm-lead-modal__dialog--maximized .crm-lead-modal__body{max-height:calc(100vh - 88px)}
.crm-lead-modal__content{border:1px solid var(--crm-border);border-radius:var(--crm-radius-md);box-shadow:0 24px 64px rgba(9,30,66,.18);overflow:hidden;background:var(--crm-surface)}
.crm-lead-modal__header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:14px 18px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface)}
.crm-lead-modal__heading{min-width:0}
.crm-lead-modal__eyebrow{font-size:15px;line-height:1.3;font-weight:600;color:var(--crm-text);letter-spacing:-.01em}
.crm-lead-modal__subtitle{margin-top:2px;color:var(--crm-text-muted);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.crm-lead-modal__actions{display:flex;align-items:center;gap:4px;flex-shrink:0}
.crm-lead-modal__action{width:34px;height:34px;padding:0;border:0;border-radius:var(--crm-radius-sm);background:transparent;color:var(--crm-text-muted);display:grid;place-items:center;cursor:pointer}
.crm-lead-modal__action:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
.crm-lead-modal__body{padding:0;background:var(--crm-surface-sunken);max-height:min(78vh,860px);overflow-y:auto}

.crm-lead-ticket{background:var(--crm-surface-sunken)}
.crm-lead-ticket__header{padding:18px 20px;background:var(--crm-surface);border-bottom:1px solid var(--crm-border)}
.crm-lead-ticket__identity{display:flex;gap:12px;align-items:flex-start}
.crm-lead-ticket__ref{font-size:11px;font-weight:600;color:var(--crm-text-muted);text-transform:uppercase;letter-spacing:.04em}
.crm-lead-ticket__title{margin:2px 0 8px;font-size:24px;line-height:1.2;font-weight:600;color:var(--crm-text);letter-spacing:-.02em}
.crm-lead-ticket__title--editable{
    display:inline-block;cursor:text;border-radius:8px;padding:2px 6px;margin-left:-6px;
    transition:background .15s ease, box-shadow .15s ease;
}
.crm-lead-ticket__title--editable:hover{background:var(--crm-brand-soft);box-shadow:0 0 0 1px rgba(97,48,204,.12)}
.crm-lead-ticket__title--editable:focus{outline:none;background:var(--crm-brand-soft);box-shadow:0 0 0 3px var(--crm-brand-soft)}
.crm-lead-ticket__title--editable.is-editing{padding:0;background:transparent;box-shadow:none}
.crm-lead-ticket__title--editable.is-busy{opacity:.65;pointer-events:none}
.crm-lead-ticket__title-input{
    width:min(100%,420px);font:inherit;font-size:24px;line-height:1.2;font-weight:600;color:var(--crm-text);
    letter-spacing:-.02em;padding:2px 8px;border:2px solid var(--crm-brand);border-radius:10px;background:#fff;
    box-shadow:0 0 0 3px var(--crm-brand-soft);
}
.crm-lead-ticket__title-hint{display:block;margin:-4px 0 8px;font-size:11px;color:var(--crm-text-muted)}
.crm-lead-ticket__contact{display:flex;flex-wrap:wrap;gap:6px}
.crm-lead-ticket__contact-chip{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:var(--crm-radius-sm);background:var(--crm-surface-sunken);color:var(--crm-text-muted);font-size:12px;text-decoration:none;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.crm-lead-ticket__contact-chip:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
.crm-lead-ticket__controls{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:12px}
.crm-lead-ticket__alert{display:flex;gap:10px;align-items:flex-start;margin:0;padding:10px 20px;background:#FFEBE6;color:#BF2600;font-size:12px;border-bottom:1px solid #FFBDAD}
.crm-lead-ticket__alert strong{display:block;margin-bottom:2px;font-weight:600}
.crm-lead-ticket__alert span{color:#DE350B}
.crm-lead-ticket__layout{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:0;align-items:start}
.crm-lead-ticket__main{min-width:0;border-right:1px solid var(--crm-border)}
.crm-lead-ticket__toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:4px;padding:10px 20px;background:var(--crm-surface);border-bottom:1px solid var(--crm-border)}
.crm-lead-ticket__tool,.crm-lead-ticket__tool-form button{
    display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:4px 10px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);
    background:var(--crm-surface);color:var(--crm-text-muted);font-size:12px;font-weight:500;cursor:pointer;text-decoration:none
}
.crm-lead-ticket__tool:hover{background:var(--crm-surface-sunken);color:var(--crm-text);border-color:var(--crm-border-strong)}
.crm-lead-ticket__tool--primary{background:var(--crm-link, var(--crm-brand));border-color:var(--crm-link, var(--crm-brand));color:#fff}
.crm-lead-ticket__tool--primary:hover{background:var(--crm-brand-hover);border-color:var(--crm-brand-hover);color:#fff}
.crm-lead-ticket__tool--success{background:#E3FCEF;border-color:#36B37E;color:#006644}
.crm-lead-ticket__tool-form{margin:0;padding:0;border:0;background:transparent}
.crm-lead-ticket__block{padding:16px 20px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface)}
.crm-lead-ticket__block-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}
.crm-lead-ticket__block-title{display:flex;align-items:center;gap:6px;margin:0;font-size:14px;font-weight:600;color:var(--crm-text)}
.crm-lead-ticket__block-meta{font-size:11px;font-weight:500;color:var(--crm-text-muted)}
.crm-lead-ticket__block-link{font-size:11px;font-weight:600;color:var(--crm-link, var(--crm-brand));text-decoration:none}
.crm-lead-ticket__block-link:hover{text-decoration:underline}
.crm-lead-ticket__description{font-size:14px;line-height:1.55;color:var(--crm-text-muted);white-space:pre-wrap}
.crm-lead-ticket__form-meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:10px}
.crm-lead-ticket__form-name{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:500;color:var(--crm-text)}
.crm-lead-ticket__form-date{font-size:11px;color:var(--crm-text-muted)}
.crm-lead-ticket__empty{margin:0;color:var(--crm-text-muted);font-size:13px}
.crm-lead-ticket__show-more{display:block;width:100%;margin-top:10px;padding:8px 0;border:0;border-top:1px dashed var(--crm-border);background:transparent;color:var(--crm-link, var(--crm-brand));font-size:12px;font-weight:600;cursor:pointer;text-align:left}
.crm-lead-ticket__show-more:hover{text-decoration:underline}
.crm-lead-ticket__sidebar{padding:16px 18px;background:var(--crm-surface-sunken);position:sticky;top:0}
.crm-lead-ticket__sidebar-title{margin:0 0 12px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--crm-text-muted)}
.crm-lead-ticket__properties{display:grid;gap:10px;margin:0}
.crm-lead-ticket__property{display:grid;gap:3px;padding:8px 10px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);background:var(--crm-surface)}
.crm-lead-ticket__property dt{margin:0;font-size:10px;font-weight:700;color:var(--crm-text-muted);text-transform:uppercase;letter-spacing:.04em}
.crm-lead-ticket__property dd{margin:0;font-size:13px;font-weight:500;color:var(--crm-text);line-height:1.4;word-break:break-word}

.crm-ticket-comment{display:flex;gap:10px;padding:12px 0;border-bottom:1px solid var(--crm-surface-sunken)}
.crm-ticket-comment:last-child{border-bottom:0;padding-bottom:0}
.crm-ticket-comment__avatar{width:32px;height:32px;border-radius:50%;background:var(--crm-brand-soft);color:var(--crm-brand);font-size:11px;font-weight:700;display:grid;place-items:center;flex:0 0 32px}
.crm-ticket-comment__head{display:flex;align-items:center;gap:8px;margin-bottom:4px}
.crm-ticket-comment__head strong{font-size:13px;font-weight:600;color:var(--crm-text)}
.crm-ticket-comment__head time{font-size:11px;color:var(--crm-text-muted)}
.crm-ticket-comment__content{font-size:13px;line-height:1.5;color:var(--crm-text-muted);word-break:break-word}
.crm-ticket-comment__content p{margin:0 0 6px}
.crm-ticket-comment__content ul,.crm-ticket-comment__content ol{margin:0 0 6px 18px;padding:0}
.crm-mention{display:inline;padding:1px 4px;border-radius:4px;background:var(--crm-brand-soft);color:var(--crm-brand);font-weight:600}

.crm-ticket-comment-editor{margin-top:14px;padding-top:14px;border-top:1px solid var(--crm-surface-sunken)}
.crm-ticket-comment-editor__composer{display:flex;gap:10px;align-items:flex-start}
.crm-ticket-comment-editor__avatar{width:32px;height:32px;border-radius:50%;background:var(--crm-brand-soft);color:var(--crm-brand);font-size:11px;font-weight:700;display:grid;place-items:center;flex:0 0 32px}
.crm-ticket-comment-editor__field{position:relative;flex:1;min-width:0;border:2px solid var(--crm-border);border-radius:var(--crm-radius-sm);background:var(--crm-surface);overflow:hidden}
.crm-ticket-comment-editor__field:focus-within{border-color:var(--crm-link, var(--crm-brand))}
.crm-ticket-comment-editor__toolbar{display:flex;align-items:center;gap:2px;padding:6px 8px;border-bottom:1px solid var(--crm-surface-sunken);background:var(--crm-surface-sunken)}
.crm-ticket-comment-editor__tool{width:28px;height:28px;padding:0;border:0;border-radius:var(--crm-radius-sm);background:transparent;color:var(--crm-text-muted);display:grid;place-items:center;cursor:pointer}
.crm-ticket-comment-editor__tool:hover{background:var(--crm-surface);color:var(--crm-text)}
.crm-ticket-comment-editor__input{min-height:88px;max-height:220px;padding:10px 12px;font-size:13px;line-height:1.5;color:var(--crm-text);overflow-y:auto;outline:none}
.crm-ticket-comment-editor__input:empty:before{content:attr(data-placeholder);color:var(--crm-text-muted);pointer-events:none}
.crm-ticket-comment-editor__actions{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 10px;border-top:1px solid var(--crm-surface-sunken);background:var(--crm-surface-sunken)}
.crm-ticket-comment-editor__hint{font-size:11px;color:var(--crm-text-muted)}
.crm-ticket-comment-editor__hint kbd{display:inline-block;padding:1px 5px;border:1px solid var(--crm-border);border-radius:4px;background:var(--crm-surface);font-size:10px;font-family:inherit}
.crm-ticket-comment-editor__submit{min-height:32px;padding:4px 14px;border:0;border-radius:var(--crm-radius-sm);background:var(--crm-link, var(--crm-brand));color:#fff;font-size:12px;font-weight:600;cursor:pointer}
.crm-ticket-comment-editor__submit:hover{background:var(--crm-brand-hover)}
.crm-ticket-comment-editor__submit:disabled{opacity:.6;cursor:not-allowed}
.crm-ticket-comment-editor__mention-menu{
    position:absolute;left:10px;right:10px;bottom:calc(100% + 4px);z-index:20;display:grid;gap:2px;padding:4px;
    border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);background:var(--crm-surface);box-shadow:0 8px 24px rgba(9,30,66,.12)
}
.crm-mention-option{display:flex;align-items:center;gap:8px;width:100%;padding:6px 8px;border:0;border-radius:var(--crm-radius-sm);background:transparent;text-align:left;cursor:pointer}
.crm-mention-option:hover,.crm-mention-option:focus{background:var(--crm-surface-sunken);outline:none}
.crm-mention-option__avatar{width:24px;height:24px;border-radius:50%;background:var(--crm-brand-soft);color:var(--crm-brand);font-size:10px;font-weight:700;display:grid;place-items:center;flex:0 0 24px}
.crm-mention-option__name{font-size:12px;font-weight:500;color:var(--crm-text)}
.crm-mention-chip{display:inline;padding:1px 4px;border-radius:4px;background:var(--crm-brand-soft);color:var(--crm-brand);font-weight:600}

.crm-lead-ticket__timeline{padding:0}
.crm-lead-ticket__timeline-item{display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--crm-surface-sunken)}
.crm-lead-ticket__timeline-item:last-child{border-bottom:0;padding-bottom:0}
.crm-lead-ticket__timeline-icon{width:28px;height:28px;border-radius:var(--crm-radius-sm);background:var(--crm-brand-soft);color:var(--crm-brand);display:grid;place-items:center;flex:0 0 28px}
.crm-lead-ticket__timeline-title{font-size:12px;font-weight:600;color:var(--crm-text);text-transform:capitalize}
.crm-lead-ticket__timeline-body p{margin:2px 0;font-size:13px;color:var(--crm-text-muted);line-height:1.4}
.crm-lead-ticket__timeline-body small{font-size:11px;color:var(--crm-text-muted)}

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

.crm-admission-tags{
    display:flex;flex-wrap:wrap;gap:6px;align-items:center;
}
.crm-admission-tags--compact{gap:4px}
.crm-admission-tag{
    display:inline-flex;align-items:center;gap:5px;max-width:100%;
    padding:4px 9px;border-radius:999px;border:1px solid transparent;
    font-size:11px;font-weight:650;line-height:1.2;white-space:nowrap;
}
.crm-admission-tags--compact .crm-admission-tag{padding:3px 7px;font-size:10px}
.crm-admission-tag iconify-icon{font-size:13px;flex:0 0 auto;opacity:.88}
.crm-admission-tag__label{overflow:hidden;text-overflow:ellipsis}
.crm-admission-tag__value{
    padding:1px 6px;border-radius:999px;background:rgba(255,255,255,.55);
    font-size:10px;font-weight:750;
}
.crm-admission-tag--success{color:#166534;background:#ecfdf3;border-color:#bbf7d0}
.crm-admission-tag--success .crm-admission-tag__value{background:rgba(22,101,52,.12);color:#166534}
.crm-admission-tag--neutral{color:#475569;background:#f8fafc;border-color:#e2e8f0}
.crm-admission-tag--neutral .crm-admission-tag__value{background:#e2e8f0;color:#475569}
.crm-admission-tag--info{color:#1d4ed8;background:#eff6ff;border-color:#bfdbfe}
.crm-admission-tag--warning{color:#b45309;background:#fffbeb;border-color:#fde68a}
.crm-admission-tag--indigo{color:#4338ca;background:#eef2ff;border-color:#c7d2fe}
.crm-admission-tag--purple{color:#7e22ce;background:#faf5ff;border-color:#e9d5ff}
.crm-admission-tag--mini{padding:3px 8px;font-size:10px;gap:4px}
.crm-admission-tag__dot{width:6px;height:6px;border-radius:50%;flex:0 0 auto}
.crm-admission-tag__dot--done{background:#16a34a}
.crm-admission-tag__dot--pending{background:#cbd5e1}
.crm-board-card__tags .crm-admission-tags{width:100%}

.crm-import-summary{
    margin:16px 20px 0;padding:14px 16px;border:1px solid var(--crm-border);border-radius:14px;
    background:linear-gradient(180deg,#fff,#fafbff);
}
.crm-import-summary__head{
    display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;
}
.crm-import-summary__title{
    display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:750;color:var(--crm-text);
}
.crm-import-summary__title iconify-icon{color:#6130cc;font-size:16px}
.crm-import-summary__file{
    font-size:11px;color:var(--crm-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:52%;
}
.crm-import-summary__strip{
    display:flex;flex-wrap:wrap;align-items:center;gap:8px;
}
.crm-field-chip{
    display:inline-flex;align-items:center;gap:6px;max-width:100%;
    padding:4px 10px;border-radius:999px;border:1px solid transparent;
    font-size:11px;font-weight:650;line-height:1.2;
}
.crm-field-chip__key{
    opacity:.72;font-size:10px;font-weight:700;letter-spacing:.02em;text-transform:uppercase;
}
.crm-field-chip__val{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.crm-field-chip--value-only .crm-field-chip__val{font-size:11px;font-weight:650;text-transform:none}
.crm-field-chip--info{color:#1d4ed8;background:#eff6ff;border-color:#bfdbfe}
.crm-field-chip--indigo{color:#4338ca;background:#eef2ff;border-color:#c7d2fe}
.crm-field-chip--warning{color:#b45309;background:#fffbeb;border-color:#fde68a}
.crm-field-chip--success{color:#166534;background:#ecfdf3;border-color:#bbf7d0}
.crm-field-chip--danger{color:#b91c1c;background:#fef2f2;border-color:#fecaca}
.crm-field-chip--purple{color:#7e22ce;background:#faf5ff;border-color:#e9d5ff}
.crm-field-chip--neutral{color:#475569;background:#f8fafc;border-color:#e2e8f0}
.crm-import-checklist{
    display:inline-flex;align-items:center;gap:6px;padding:3px;border-radius:999px;
    background:#fff;border:1px solid var(--crm-border);
}
.crm-import-check{
    display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:999px;
    font-size:10px;font-weight:750;letter-spacing:.02em;
}
.crm-import-check iconify-icon{font-size:14px}
.crm-import-check--done{color:#166534;background:#ecfdf3}
.crm-import-check--pending{color:#64748b;background:#f8fafc}
.crm-import-summary__details{margin-top:12px;border-top:1px dashed var(--crm-border);padding-top:10px}
.crm-import-summary__details summary{
    display:flex;align-items:center;justify-content:space-between;gap:8px;cursor:pointer;
    list-style:none;font-size:12px;font-weight:650;color:#6130cc;
}
.crm-import-summary__details summary::-webkit-details-marker{display:none}
.crm-import-summary__details[open] summary iconify-icon{transform:rotate(180deg)}
.crm-import-summary__details summary iconify-icon{transition:transform .18s ease;font-size:16px}
.crm-import-summary__details-body{padding-top:12px}
.crm-import-summary__group{margin-top:12px}
.crm-import-summary__group:first-child{margin-top:0}
.crm-import-summary__group h4{
    margin:0 0 8px;font-size:11px;font-weight:700;color:var(--crm-text-muted);
    letter-spacing:.04em;text-transform:uppercase;
}
.crm-import-summary__grid{
    display:grid;grid-template-columns:repeat(auto-fit,minmax(min(180px,100%),1fr));gap:8px;margin:0;
}
.crm-import-summary__field{
    padding:8px 10px;border:1px solid var(--crm-border);border-radius:10px;background:#fff;
}
.crm-import-summary__field dt{
    margin:0 0 4px;font-size:10px;font-weight:650;color:var(--crm-text-muted);
}
.crm-import-summary__field dd{margin:0}
.crm-lead-ticket__import-chips{display:flex;flex-wrap:wrap;gap:6px}
.crm-lead-ticket__property--stack dd{margin-top:4px}
.crm-lead-ticket__block--collapsible{border:0;padding:0 20px 16px}
.crm-lead-ticket__block--collapsible summary{list-style:none;cursor:pointer}
.crm-lead-ticket__block--collapsible summary::-webkit-details-marker{display:none}
.crm-lead-ticket__block-head--toggle{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding:12px 0;border-top:1px solid var(--crm-border);
}
.crm-lead-ticket__block--collapsible[open] .crm-lead-ticket__block-head--toggle{margin-bottom:8px}
.crm-lead-ticket--create .crm-lead-ticket__layout--create{
    display:grid;grid-template-columns:minmax(0,1fr) minmax(240px,320px);gap:0;
}
.crm-lead-ticket--create .crm-lead-ticket__sidebar{
    padding:16px 18px;border-left:1px solid var(--crm-border);background:var(--crm-surface-sunken);
}
.crm-lead-ticket--create .crm-lead-panel__field{margin-bottom:12px}
.crm-lead-ticket--create .crm-lead-panel__field span{
    display:block;margin-bottom:5px;font-size:11px;font-weight:650;color:var(--crm-text-muted);
}

.crm-lead-ticket--form{background:var(--crm-surface-sunken)}
.crm-lead-ticket__form{margin:0}
.crm-lead-ticket__form-hint{margin:0;font-size:12px;color:var(--crm-text-muted);line-height:1.45}
.crm-lead-ticket__controls--form{margin-top:12px}
.crm-lead-ticket__toolbar--form{border-top:1px solid var(--crm-border)}
.crm-lead-form-block .crm-lead-ticket__block-title{margin-bottom:12px}
.crm-lead-form-grid{
    display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 14px;
}
.crm-lead-form-field{display:grid;gap:5px;min-width:0}
.crm-lead-form-field--wide{grid-column:1/-1}
.crm-lead-form-field__label{
    font-size:10px;font-weight:700;color:var(--crm-text-muted);
    text-transform:uppercase;letter-spacing:.04em;
}
.crm-lead-form-field__input{
    min-height:38px;border:1px solid var(--crm-border);border-radius:10px;
    background:#fff;font-size:13px;color:var(--crm-text);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.8);
}
.crm-lead-form-field__input:focus{
    border-color:var(--crm-brand);box-shadow:0 0 0 3px var(--crm-brand-soft);
}
.crm-lead-form-field textarea.crm-lead-form-field__input{min-height:96px;resize:vertical}

.crm-form-tag-select{
    position:relative;display:inline-flex;max-width:100%;vertical-align:middle;
}
.crm-form-tag-select__native{
    position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;z-index:2;
    appearance:none;border:0;background:transparent;
}
.crm-form-tag-select__face{
    display:inline-flex;align-items:center;gap:5px;min-height:28px;padding:0 10px;
    border-radius:999px;border:1px solid var(--crm-tone-border);background:var(--crm-tone-bg);color:var(--crm-tone-text);
    font-size:12px;font-weight:650;line-height:1;pointer-events:none;max-width:100%;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.55);
}
.crm-form-tag-select__icon,.crm-form-tag-select__chevron{font-size:14px;flex-shrink:0}
.crm-form-tag-select__label{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:130px}
.crm-form-tag-select--owner .crm-form-tag-select__label{max-width:150px}
.crm-form-tag-select.is-open .crm-form-tag-select__face,
.crm-form-tag-select:focus-within .crm-form-tag-select__face{
    border-color:var(--crm-tone-text);box-shadow:0 0 0 3px var(--crm-tone-ring), inset 0 1px 0 rgba(255,255,255,.55);
}
.crm-form-tag-select[data-tone="neutral"]{--crm-tone-bg:#f1f5f9;--crm-tone-border:#cbd5e1;--crm-tone-text:#475569;--crm-tone-ring:rgba(100,116,139,.22)}
.crm-form-tag-select[data-tone="success"]{--crm-tone-bg:#ecfdf5;--crm-tone-border:#86efac;--crm-tone-text:#15803d;--crm-tone-ring:rgba(21,128,61,.2)}
.crm-form-tag-select[data-tone="info"]{--crm-tone-bg:#eff6ff;--crm-tone-border:#93c5fd;--crm-tone-text:#1d4ed8;--crm-tone-ring:rgba(29,78,216,.2)}
.crm-form-tag-select[data-tone="warning"]{--crm-tone-bg:#fffbeb;--crm-tone-border:#fcd34d;--crm-tone-text:#b45309;--crm-tone-ring:rgba(180,83,9,.2)}
.crm-form-tag-select[data-tone="caution"]{--crm-tone-bg:#fff7ed;--crm-tone-border:#fdba74;--crm-tone-text:#c2410c;--crm-tone-ring:rgba(194,65,12,.2)}
.crm-form-tag-select[data-tone="danger"]{--crm-tone-bg:#fef2f2;--crm-tone-border:#fca5a5;--crm-tone-text:#b91c1c;--crm-tone-ring:rgba(185,28,28,.2)}
.crm-form-tag-select[data-tone="indigo"]{--crm-tone-bg:#eef2ff;--crm-tone-border:#a5b4fc;--crm-tone-text:#4338ca;--crm-tone-ring:rgba(67,56,202,.2)}
.crm-lead-ticket__property .crm-form-tag-select{width:100%}
.crm-lead-ticket__property .crm-form-tag-select__face{width:100%;justify-content:flex-start}

@media(max-width:480px){
    .crm-lead-form-grid{grid-template-columns:1fr}
    .crm-lead-panel__edit-grid{grid-template-columns:1fr}
}

@media(max-width:1199px){
    #crm-leads-page .crm-filter-workspace__advanced--compact .crm-filter-workspace__advanced-grid{grid-template-columns:repeat(2,minmax(130px,1fr))}
    #crm-leads-page .crm-page-header{align-items:flex-start;flex-direction:column}
    #crm-leads-page .crm-page-header>div:last-child{width:100%}
}
@media(max-width:900px){
    .crm-lead-ticket__layout{grid-template-columns:1fr}
    .crm-lead-ticket__main{border-right:0;border-bottom:1px solid var(--crm-border)}
    .crm-lead-ticket__sidebar{position:static}
}
@media(max-width:767px){
    #crm-leads-page{padding:12px}
    #crm-leads-page .crm-smart-search__suggestions{grid-template-columns:1fr}
    #crm-leads-page .crm-smart-view--more{margin-left:0}
    #crm-leads-page .crm-filter-workspace__advanced--compact .crm-filter-workspace__advanced-grid{grid-template-columns:1fr 1fr}
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
    #crm-leads-page .crm-filter-workspace__advanced--compact .crm-filter-workspace__advanced-grid{grid-template-columns:1fr}
    #crm-leads-page .crm-metrics-strip__items{width:100%}
}
@media(max-width:575px){#crm-leads-page .crm-workflow-board{grid-auto-columns:minmax(300px,88vw)}}
</style>
