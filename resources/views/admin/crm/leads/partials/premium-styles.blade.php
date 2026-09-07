<style>
/* ── Jira-inspired design tokens (scoped) ── */
#crm-leads-page{
    --jira-text:#172B4D;
    --jira-subtle:#42526E;
    --jira-muted:#6B778C;
    --jira-border:#DFE1E6;
    --jira-border-strong:#C1C7D0;
    --jira-surface:#FFFFFF;
    --jira-bg:#F4F5F7;
    --jira-bg-neutral:#EBECF0;
    --jira-brand:#0052CC;
    --jira-brand-hover:#0065FF;
    --jira-brand-subtle:#DEEBFF;
    --jira-lozenge-radius:3px;
    --jira-font-size:14px;
    --jira-font-size-sm:12px;
    --jira-font-size-xs:11px;
    background:var(--jira-bg);
    color:var(--jira-text);
    padding:16px 20px 24px;
    border-radius:0;
    min-height:calc(100vh - 72px);
    font-size:var(--jira-font-size);
    line-height:1.4286;
    letter-spacing:normal;
}

#crm-leads-page.crm-board-view .crm-list-only{display:none!important}
#crm-leads-page.crm-list-view .crm-board-only{display:none!important}

/* Page header */
#crm-leads-page .crm-page-header{
    display:flex;align-items:center;padding:0 0 16px;margin-bottom:12px;
    border-bottom:1px solid var(--jira-border);
}
#crm-leads-page .crm-page-header__title{
    font-size:24px!important;line-height:1.25!important;font-weight:500!important;
    letter-spacing:-.01em;color:var(--jira-text);margin-bottom:4px;
}
#crm-leads-page .crm-page-header__subtitle{
    font-size:var(--jira-font-size-sm);color:var(--jira-muted);margin:0;font-weight:400;
}
#crm-leads-page .crm-breadcrumb{display:none}
#crm-leads-page .crm-page-header>div:last-child{gap:8px!important}
#crm-leads-page .crm-page-header .btn{
    min-height:32px;padding:4px 12px!important;border:1px solid var(--jira-border-strong)!important;
    border-radius:3px!important;background:var(--jira-surface)!important;color:var(--jira-subtle)!important;
    font-size:var(--jira-font-size-sm);font-weight:500;box-shadow:none!important;transition:background .1s ease,border-color .1s ease;
}
#crm-leads-page .crm-page-header .btn:hover{
    border-color:var(--jira-border-strong)!important;background:var(--jira-bg-neutral)!important;
    transform:none;color:var(--jira-text)!important;
}
#crm-leads-page .crm-page-header .btn-primary-600{
    background:var(--jira-brand)!important;border-color:var(--jira-brand)!important;
    color:#fff!important;box-shadow:none!important;
}
#crm-leads-page .crm-page-header .btn-primary-600:hover{
    background:var(--jira-brand-hover)!important;border-color:var(--jira-brand-hover)!important;color:#fff!important;
}

/* Metrics strip (inside workspace shell) */
#crm-leads-page .crm-leads-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
#crm-leads-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:10px 16px;border:0;border-bottom:1px solid var(--jira-border);border-radius:0;
    background:#FAFBFC;
}
#crm-leads-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#crm-leads-page .crm-metrics-strip__item{
    display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--jira-subtle);
    font-size:var(--jira-font-size-sm);text-decoration:none;
}
#crm-leads-page .crm-metrics-strip__item--link{border-radius:3px;transition:background .1s ease}
#crm-leads-page .crm-metrics-strip__item--link:hover{background:var(--jira-bg-neutral);color:var(--jira-text)}
#crm-leads-page .crm-metrics-strip__item--link.is-active{background:var(--jira-brand-subtle);color:var(--jira-brand)}
#crm-leads-page .crm-metrics-strip__item--link.is-active strong{color:var(--jira-brand)}
#crm-leads-page .crm-metrics-strip__label{
    font-size:var(--jira-font-size-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--jira-muted);
}
#crm-leads-page .crm-metrics-strip__item strong{
    font-size:var(--jira-font-size-sm);font-weight:600;color:var(--jira-text);font-variant-numeric:tabular-nums;
}
#crm-leads-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--jira-border);margin:0 2px}
#crm-leads-page .crm-metrics-strip__links{display:flex;align-items:center;gap:12px}
#crm-leads-page .crm-metrics-strip__link{
    font-size:var(--jira-font-size-sm);font-weight:500;color:var(--jira-brand);text-decoration:none;
}
#crm-leads-page .crm-metrics-strip__link:hover{text-decoration:underline;color:var(--jira-brand-hover)}

/* Filter workspace */
#crm-leads-page .crm-filter-workspace{padding:12px 16px;border-bottom:1px solid var(--jira-border);background:var(--jira-surface)}
#crm-leads-page .crm-filter-workspace__search-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:10px}
#crm-leads-page .crm-filter-workspace__search{
    flex:1 1 280px;display:flex;align-items:center;gap:8px;min-height:32px;
    padding:0 10px;border:2px solid var(--jira-border);border-radius:3px;background:var(--jira-surface);
    transition:border-color .15s ease;
}
#crm-leads-page .crm-filter-workspace__search:focus-within{border-color:var(--jira-brand)}
#crm-leads-page .crm-filter-workspace__search iconify-icon{color:var(--jira-muted);font-size:16px;flex-shrink:0}
#crm-leads-page .crm-filter-workspace__search-input{
    flex:1;border:0;background:transparent;padding:6px 0;font-size:var(--jira-font-size-sm);
    color:var(--jira-text);outline:none;min-width:0;
}
#crm-leads-page .crm-filter-workspace__search-input::placeholder{color:var(--jira-muted)}
#crm-leads-page .crm-filter-workspace__search-actions{display:flex;align-items:center;gap:6px;flex-shrink:0}
#crm-leads-page .crm-filter-workspace__btn{
    display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:4px 12px;
    border-radius:3px;font-size:var(--jira-font-size-sm);font-weight:500;text-decoration:none;
    border:1px solid transparent;cursor:pointer;transition:background .1s ease,border-color .1s ease;
}
#crm-leads-page .crm-filter-workspace__btn--primary{background:var(--jira-brand);border-color:var(--jira-brand);color:#fff}
#crm-leads-page .crm-filter-workspace__btn--primary:hover{background:var(--jira-brand-hover);border-color:var(--jira-brand-hover);color:#fff}
#crm-leads-page .crm-filter-workspace__btn--ghost{background:transparent;border-color:var(--jira-border-strong);color:var(--jira-subtle)}
#crm-leads-page .crm-filter-workspace__btn--ghost:hover{background:var(--jira-bg-neutral);color:var(--jira-text)}

#crm-leads-page .crm-filter-workspace__quick-row{display:flex;flex-wrap:wrap;align-items:flex-start;gap:8px;margin-bottom:8px}
#crm-leads-page .crm-filter-workspace__quick-label{
    flex:0 0 auto;padding-top:4px;font-size:var(--jira-font-size-xs);font-weight:600;
    text-transform:uppercase;letter-spacing:.04em;color:var(--jira-muted);min-width:72px;
}
#crm-leads-page .crm-filter-workspace__chips{
    flex:1 1 200px;display:flex;flex-wrap:wrap;align-items:center;gap:4px;
}
#crm-leads-page .crm-filter-chip{
    display:inline-flex;align-items:center;gap:5px;padding:2px 8px;min-height:24px;
    border:1px solid var(--jira-border);border-radius:var(--jira-lozenge-radius);
    background:var(--jira-surface);color:var(--jira-subtle);
    font-size:var(--jira-font-size-xs);font-weight:500;text-decoration:none;
    transition:background .1s ease,border-color .1s ease,color .1s ease;
}
#crm-leads-page .crm-filter-chip iconify-icon{font-size:14px;opacity:.75}
#crm-leads-page .crm-filter-chip:hover{background:var(--jira-bg-neutral);border-color:var(--jira-border-strong);color:var(--jira-text)}
#crm-leads-page .crm-filter-chip.is-active{
    background:var(--jira-brand-subtle);border-color:var(--jira-brand);color:var(--jira-brand);font-weight:600;
}
#crm-leads-page .crm-filter-chip.is-active iconify-icon{opacity:1}
#crm-leads-page .crm-filter-chip__count{
    padding:0 4px;border-radius:var(--jira-lozenge-radius);background:var(--jira-bg-neutral);
    font-size:10px;font-weight:600;font-variant-numeric:tabular-nums;color:var(--jira-muted);
}
#crm-leads-page .crm-filter-chip.is-active .crm-filter-chip__count{background:rgba(0,82,204,.12);color:var(--jira-brand)}
#crm-leads-page .crm-filter-chip--assigned_me.is-active{background:#DEEBFF;border-color:#0052CC;color:#0052CC}
#crm-leads-page .crm-filter-chip--assigned_me.is-active .crm-filter-chip__count{background:rgba(0,82,204,.15);color:#0052CC}
#crm-leads-page .crm-filter-chip--unassigned.is-active{background:#F4F5F7;border-color:#42526E;color:#42526E}
#crm-leads-page .crm-filter-chip--new.is-active{background:#FFF7D6;border-color:#FF991F;color:#974F0C}
#crm-leads-page .crm-filter-chip--new.is-active .crm-filter-chip__count{background:rgba(255,153,31,.15);color:#974F0C}
#crm-leads-page .crm-filter-chip--follow_up.is-active{background:#FFEBE6;border-color:#DE350B;color:#BF2600}
#crm-leads-page .crm-filter-chip--follow_up.is-active .crm-filter-chip__count{background:rgba(222,53,11,.12);color:#BF2600}
#crm-leads-page .crm-filter-chip--high_priority.is-active{background:#FFBDAD;border-color:#BF2600;color:#BF2600}
#crm-leads-page .crm-filter-chip--segment.is-active{background:#EAE6FF;border-color:#6554C0;color:#403294}
#crm-leads-page .crm-filter-chip--segment.is-active .crm-filter-chip__count{background:rgba(101,84,192,.12);color:#403294}
#crm-leads-page .crm-filter-workspace__chip-divider{width:1px;height:20px;background:var(--jira-border);margin:0 4px}

#crm-leads-page .crm-filter-workspace__advanced-toggle-row{margin-bottom:0}
#crm-leads-page .crm-filter-workspace__advanced-toggle{
    display:inline-flex;align-items:center;gap:6px;padding:2px 4px;border:0;background:transparent;
    color:var(--jira-subtle);font-size:var(--jira-font-size-sm);font-weight:500;cursor:pointer;
}
#crm-leads-page .crm-filter-workspace__advanced-toggle:hover{color:var(--jira-brand)}
#crm-leads-page .crm-filter-workspace__advanced-toggle[aria-expanded="true"] .crm-filter-workspace__chevron{transform:rotate(180deg)}
#crm-leads-page .crm-filter-workspace__chevron{font-size:14px;transition:transform .15s ease}
#crm-leads-page .crm-filter-workspace__filter-count{
    min-width:18px;height:18px;padding:0 5px;border-radius:var(--jira-lozenge-radius);
    background:var(--jira-brand);color:#fff;font-size:10px;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;
}
#crm-leads-page .crm-filter-workspace__advanced{
    margin-top:12px;padding-top:12px;border-top:1px solid var(--jira-border);
}
#crm-leads-page .crm-filter-workspace__advanced-grid{
    display:grid;grid-template-columns:repeat(4,minmax(140px,1fr));gap:10px 12px;margin-bottom:10px;
}
#crm-leads-page .crm-filter-workspace__field label{
    display:block;margin-bottom:4px;font-size:var(--jira-font-size-xs);font-weight:600;
    color:var(--jira-muted);text-transform:uppercase;letter-spacing:.04em;
}
#crm-leads-page .crm-filter-workspace__field .form-control,
#crm-leads-page .crm-filter-workspace__field .form-select{
    height:32px;padding:4px 8px;border:2px solid var(--jira-border);border-radius:3px;
    font-size:var(--jira-font-size-sm);color:var(--jira-text);box-shadow:none;
}
#crm-leads-page .crm-filter-workspace__field .form-control:focus,
#crm-leads-page .crm-filter-workspace__field .form-select:focus{
    border-color:var(--jira-brand);box-shadow:none;
}
#crm-leads-page .crm-filter-workspace__advanced-actions{display:flex;align-items:center;gap:8px}

/* Toolbar */
#crm-leads-page .crm-leads-toolbar{
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    margin:0;padding:10px 16px;border-bottom:1px solid var(--jira-border);background:var(--jira-bg);
}
#crm-leads-page .crm-leads-toolbar__left{display:flex;flex-wrap:wrap;align-items:center;gap:12px;min-width:0}
#crm-leads-page .crm-leads-toolbar__right{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:6px}
#crm-leads-page .crm-leads-toolbar__meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--jira-muted);font-size:var(--jira-font-size-sm)}
#crm-leads-page .crm-leads-toolbar__meta strong{color:var(--jira-text);font-weight:600;font-variant-numeric:tabular-nums}
#crm-leads-page .crm-leads-toolbar__filters{
    padding:1px 6px;border-radius:var(--jira-lozenge-radius);background:var(--jira-bg-neutral);
    font-size:var(--jira-font-size-xs);font-weight:600;color:var(--jira-subtle);
}
#crm-leads-page .crm-leads-toolbar__saved{display:flex;flex-wrap:wrap;align-items:center;gap:4px}
#crm-leads-page .crm-view-toggle{
    padding:2px;border:1px solid var(--jira-border);border-radius:3px;background:var(--jira-surface);box-shadow:none;
}
#crm-leads-page .crm-view-toggle button{
    min-width:64px;height:28px;padding:0 10px;border-radius:2px;background:transparent;
    display:inline-flex;align-items:center;justify-content:center;gap:5px;
    font-size:var(--jira-font-size-xs);font-weight:600;color:var(--jira-muted);
}
#crm-leads-page .crm-view-toggle button[data-view="board"]::after{content:"Board"}
#crm-leads-page .crm-view-toggle button[data-view="list"]::after{content:"List"}
#crm-leads-page .crm-view-toggle button.is-active[data-view="board"]{background:#DEEBFF;color:#0052CC}
#crm-leads-page .crm-view-toggle button.is-active[data-view="list"]{background:#E3FCEF;color:#006644}
#crm-leads-page .crm-leads-toolbar .btn{
    min-height:28px;padding:2px 10px!important;border-radius:3px!important;
    font-size:var(--jira-font-size-xs)!important;font-weight:500!important;
}
#crm-leads-page .crm-saved-filter-chip{
    display:inline-flex;align-items:center;border:1px solid var(--jira-border);border-radius:var(--jira-lozenge-radius);
    background:var(--jira-surface);overflow:hidden;
}
#crm-leads-page .crm-saved-filter-chip__link{
    padding:2px 8px;font-size:var(--jira-font-size-xs);font-weight:500;color:var(--jira-brand);text-decoration:none;
}
#crm-leads-page .crm-saved-filter-chip__link:hover{text-decoration:underline}
#crm-leads-page .crm-saved-filter-chip__remove{
    padding:2px 6px;border:0;border-left:1px solid var(--jira-border);background:transparent;color:var(--jira-muted);cursor:pointer;
}
#crm-leads-page .crm-save-filter-inline{margin:0 16px 12px;padding:10px 12px;border:1px dashed var(--jira-border);border-radius:3px;background:var(--jira-bg)}
#crm-leads-page .crm-save-filter-inline__inner{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
#crm-leads-page .crm-save-filter-inline .form-control{max-width:240px;height:32px;font-size:var(--jira-font-size-sm)}

/* Jira lozenges — override pills, badges, inline controls within leads page */
#crm-leads-page .crm-status-pill,
#crm-leads-page .crm-inline-trigger{
    --crm-tone-bg:#DFE1E6;--crm-tone-border:transparent;--crm-tone-text:#42526E;--crm-tone-ring:rgba(0,82,204,.25);
    min-height:20px;height:auto;padding:0 4px;border-radius:var(--jira-lozenge-radius)!important;
    border:none!important;font-size:var(--jira-font-size-xs)!important;font-weight:700!important;
    letter-spacing:.02em;text-transform:uppercase;line-height:20px;
    box-shadow:none!important;
}
#crm-leads-page .crm-status-pill--tone-neutral,#crm-leads-page .crm-inline-control[data-tone="neutral"] .crm-inline-trigger{--crm-tone-bg:#DFE1E6;--crm-tone-text:#42526E}
#crm-leads-page .crm-status-pill--tone-success,#crm-leads-page .crm-inline-control[data-tone="success"] .crm-inline-trigger{--crm-tone-bg:#E3FCEF;--crm-tone-text:#006644}
#crm-leads-page .crm-status-pill--tone-info,#crm-leads-page .crm-inline-control[data-tone="info"] .crm-inline-trigger{--crm-tone-bg:#DEEBFF;--crm-tone-text:#0747A6}
#crm-leads-page .crm-status-pill--tone-warning,#crm-leads-page .crm-inline-control[data-tone="warning"] .crm-inline-trigger{--crm-tone-bg:#FFF0B3;--crm-tone-text:#172B4D}
#crm-leads-page .crm-status-pill--tone-caution,#crm-leads-page .crm-inline-control[data-tone="caution"] .crm-inline-trigger{--crm-tone-bg:#FFEBE6;--crm-tone-text:#BF2600}
#crm-leads-page .crm-status-pill--tone-danger,#crm-leads-page .crm-inline-control[data-tone="danger"] .crm-inline-trigger{--crm-tone-bg:#FFEBE6;--crm-tone-text:#BF2600}
#crm-leads-page .crm-status-pill--tone-indigo,#crm-leads-page .crm-inline-control[data-tone="indigo"] .crm-inline-trigger{--crm-tone-bg:#EAE6FF;--crm-tone-text:#403294}
#crm-leads-page .crm-status-pill{text-transform:uppercase;font-weight:700}
#crm-leads-page .crm-inline-trigger{min-width:96px;max-width:160px;padding:0 6px!important;gap:4px!important;text-transform:none;font-weight:600!important}
#crm-leads-page .crm-inline-trigger__icon{font-size:12px!important;opacity:.85}
#crm-leads-page .crm-inline-trigger__label{font-size:var(--jira-font-size-xs)!important;font-weight:600!important;text-transform:capitalize}
#crm-leads-page .crm-inline-control--owner .crm-inline-trigger{min-width:112px}
#crm-leads-page .crm-inline-menu{padding:4px;border:1px solid var(--jira-border);border-radius:3px;box-shadow:0 4px 8px rgba(9,30,66,.15)}
#crm-leads-page .crm-inline-option{padding:6px 8px;border-radius:2px;font-size:var(--jira-font-size-sm);font-weight:400;color:var(--jira-text)}
#crm-leads-page .crm-inline-option:hover{background:var(--jira-bg-neutral)}
#crm-leads-page .badge{
    border-radius:var(--jira-lozenge-radius)!important;padding:0 4px!important;min-height:20px;
    border:none!important;background:var(--jira-bg-neutral)!important;color:var(--jira-subtle)!important;
    font-size:var(--jira-font-size-xs)!important;font-weight:600!important;letter-spacing:.01em;
    box-shadow:none!important;
}
#crm-leads-page .crm-category-badge{
    margin-top:4px;padding:0 6px;border-radius:var(--jira-lozenge-radius);
    background:#EBECF0!important;color:#42526E!important;
    font-size:10px!important;font-weight:600!important;border:none!important;
    display:inline-flex;align-items:center;gap:4px;
}
#crm-leads-page .crm-list-row__identity .crm-category-badge{margin-top:3px;background:#DEEBFF!important;color:#0747A6!important}
#crm-leads-page .crm-category-badge--green{background:#E3FCEF!important;color:#006644!important}
#crm-leads-page .crm-category-badge--gold,#crm-leads-page .crm-category-badge--amber{background:#FFF7D6!important;color:#974F0C!important}
#crm-leads-page .crm-category-badge--purple,#crm-leads-page .crm-category-badge--indigo{background:#EAE6FF!important;color:#403294!important}
#crm-leads-page .crm-category-badge--navy{background:#DEEBFF!important;color:#0747A6!important}
#crm-leads-page .crm-followup-badge{
    min-height:20px;padding:0 6px;border-radius:var(--jira-lozenge-radius);border:none;
    font-size:var(--jira-font-size-xs);font-weight:600;box-shadow:none;
}

/* Per-value status & priority colors (list + board) */
#crm-leads-page .crm-inline-control[data-field="lead_status"][data-previous="new"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--new{--crm-tone-bg:#EAE6FF;--crm-tone-text:#403294}
#crm-leads-page .crm-inline-control[data-field="lead_status"][data-previous="contacted"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--contacted{--crm-tone-bg:#DEEBFF;--crm-tone-text:#0747A6}
#crm-leads-page .crm-inline-control[data-field="lead_status"][data-previous="qualified"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--qualified{--crm-tone-bg:#E6FCFF;--crm-tone-text:#008DA6}
#crm-leads-page .crm-inline-control[data-field="lead_status"][data-previous="proposal_sent"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--proposal_sent{--crm-tone-bg:#E3FCEF;--crm-tone-text:#006644}
#crm-leads-page .crm-inline-control[data-field="lead_status"][data-previous="negotiation"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--negotiation{--crm-tone-bg:#FFF0B3;--crm-tone-text:#974F0C}
#crm-leads-page .crm-inline-control[data-field="lead_status"][data-previous="won"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--won{--crm-tone-bg:#ABF5D1;--crm-tone-text:#006644}
#crm-leads-page .crm-inline-control[data-field="lead_status"][data-previous="lost"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--lost{--crm-tone-bg:#FFEBE6;--crm-tone-text:#BF2600}
#crm-leads-page .crm-inline-control[data-field="lead_status"][data-previous="on_hold"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--on_hold{--crm-tone-bg:#DFE1E6;--crm-tone-text:#42526E}
#crm-leads-page .crm-inline-control[data-field="priority"][data-previous="low"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--low{--crm-tone-bg:#EBECF0;--crm-tone-text:#42526E}
#crm-leads-page .crm-inline-control[data-field="priority"][data-previous="medium"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--medium{--crm-tone-bg:#DEEBFF;--crm-tone-text:#0747A6}
#crm-leads-page .crm-inline-control[data-field="priority"][data-previous="high"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--high{--crm-tone-bg:#FFEBE6;--crm-tone-text:#BF2600}
#crm-leads-page .crm-inline-control[data-field="priority"][data-previous="urgent"] .crm-inline-trigger,
#crm-leads-page .crm-status-pill--urgent{--crm-tone-bg:#FFBDAD;--crm-tone-text:#BF2600}
#crm-leads-page .crm-inline-control[data-field="assigned_to"] .crm-inline-trigger{--crm-tone-bg:#EBECF0;--crm-tone-text:#42526E}
#crm-leads-page .crm-inline-trigger__label{text-transform:capitalize}

/* Board */
#crm-leads-page .crm-workflow-board{
    display:grid;grid-auto-flow:column;grid-auto-columns:minmax(260px,1fr);gap:10px;
    overflow-x:auto;padding:14px 16px 16px;scroll-snap-type:x proximity;
    scrollbar-width:thin;scrollbar-color:var(--jira-border-strong) transparent;
    background:#F4F5F7;
}
#crm-leads-page .crm-board-column{
    scroll-snap-align:start;display:flex;flex-direction:column;
    min-height:460px;max-height:calc(100vh - 240px);
    border:1px solid var(--jira-border);border-radius:3px;background:#EBECF0;overflow:hidden;
}
#crm-leads-page .crm-board-column__head{
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    padding:10px 12px;border-bottom:1px solid var(--jira-border);background:#fff;flex:0 0 auto;
}
#crm-leads-page .crm-board-column__title-wrap{display:flex;align-items:center;gap:8px;min-width:0}
#crm-leads-page .crm-board-column__dot{
    width:8px;height:8px;border-radius:50%;flex:0 0 8px;background:#C1C7D0;
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
    text-transform:uppercase;color:var(--jira-muted);line-height:1.2;
}
#crm-leads-page .crm-board-column__head strong{display:block;font-size:18px;font-weight:600;color:var(--jira-text);line-height:1.2;font-variant-numeric:tabular-nums}
#crm-leads-page .crm-board-column__count{
    min-width:22px;height:20px;padding:0 6px;border-radius:var(--jira-lozenge-radius);
    display:grid;place-items:center;background:#EBECF0;
    color:var(--jira-subtle);font-size:10px;font-weight:600;font-variant-numeric:tabular-nums;
}
#crm-leads-page .crm-board-column__body{
    display:flex;flex:1 1 auto;flex-direction:column;gap:8px;min-height:0;padding:8px;
    overflow-y:auto;scrollbar-width:thin;
}
#crm-leads-page .crm-board-column.is-drag-over{border-color:var(--jira-brand);box-shadow:0 0 0 2px rgba(0,82,204,.18)}
#crm-leads-page .crm-board-card{
    padding:10px 11px;border:1px solid transparent;border-radius:3px;
    background:var(--jira-surface);box-shadow:0 1px 0 rgba(9,30,66,.06);cursor:pointer;
    transition:background .12s ease,border-color .12s ease,box-shadow .12s ease;
}
#crm-leads-page .crm-board-card:hover,#crm-leads-page .crm-board-card:focus-visible{
    background:#fff;border-color:var(--jira-border-strong);
    box-shadow:0 2px 6px rgba(9,30,66,.08);outline:none;
}
#crm-leads-page .crm-board-card.is-dragging{opacity:.62;cursor:grabbing;box-shadow:0 4px 12px rgba(9,30,66,.12)}
#crm-leads-page .crm-board-card[draggable="true"]{cursor:grab}
#crm-leads-page .crm-board-card__top{display:flex;gap:8px;align-items:flex-start;margin-bottom:8px}
#crm-leads-page .crm-board-card__title{
    color:var(--jira-text);font-size:13px;font-weight:600;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-leads-page .crm-board-card__meta{color:var(--jira-muted);font-size:11px;margin-top:2px}
#crm-leads-page .crm-lead-avatar{
    width:28px;height:28px;flex:0 0 28px;border-radius:50%;
    display:grid;place-items:center;background:#EBECF0;color:#42526E;
    font-size:10px;font-weight:700;box-shadow:none;
}
#crm-leads-page .crm-board-card__flag{
    width:18px;height:18px;border-radius:var(--jira-lozenge-radius);display:grid;place-items:center;
    font-size:10px;font-weight:700;flex:0 0 18px;
}
#crm-leads-page .crm-board-card__flag.is-converted{background:#E3FCEF;color:#006644}
#crm-leads-page .crm-board-card__flag.is-attention{background:#FFEBE6;color:#BF2600}
#crm-leads-page .crm-board-card__footer{
    display:flex;flex-direction:column;gap:6px;padding-top:8px;border-top:1px solid #EBECF0;
}
#crm-leads-page .crm-board-card__footer-main{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
#crm-leads-page .crm-board-card__category{
    font-size:10px;font-weight:600;color:var(--jira-muted);text-transform:uppercase;letter-spacing:.03em;
}
#crm-leads-page .crm-board-card__footer-meta{
    display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--jira-muted);font-size:10px;
}
#crm-leads-page .crm-board-card__footer-meta>span{display:inline-flex;align-items:center;gap:4px}
#crm-leads-page .crm-board-card__followup.is-attention{color:#BF2600;font-weight:600}
#crm-leads-page .crm-board-empty{
    min-height:72px;border:1px dashed #C1C7D0;border-radius:3px;
    display:grid;place-items:center;gap:4px;color:var(--jira-muted);font-size:11px;background:transparent;
}

/* List */
#crm-leads-page .crm-leads-list-shell{padding:0 16px 16px}
#crm-leads-page .crm-list-bulk-bar{
    display:flex;flex-wrap:wrap;align-items:center;gap:10px 14px;
    margin:12px 0 8px;padding:10px 12px;border:1px solid #B3D4FF;border-radius:3px;
    background:linear-gradient(180deg,#DEEBFF 0%,#EAF2FF 100%);
}
#crm-leads-page .crm-list-bulk-bar[hidden]{display:none!important}
#crm-leads-page .crm-list-bulk-bar__summary{display:inline-flex;align-items:baseline;gap:4px;color:#0747A6;font-size:12px;font-weight:500}
#crm-leads-page .crm-list-bulk-bar__summary strong{font-size:14px;font-weight:700;font-variant-numeric:tabular-nums}
#crm-leads-page .crm-list-bulk-bar__actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px;flex:1 1 auto}
#crm-leads-page .crm-list-bulk-bar__field{display:inline-flex;align-items:center;gap:6px}
#crm-leads-page .crm-list-bulk-bar__field>span{font-size:11px;font-weight:600;color:#42526E;text-transform:uppercase;letter-spacing:.04em}
#crm-leads-page .crm-list-bulk-bar__field select{min-width:128px;height:28px;border:1px solid #C1C7D0;border-radius:3px;font-size:12px;background:#fff}
#crm-leads-page .crm-list-bulk-bar__btn{
    min-height:28px;padding:2px 10px;border:1px solid #0052CC;border-radius:3px;
    background:#0052CC;color:#fff;font-size:12px;font-weight:600;cursor:pointer;
}
#crm-leads-page .crm-list-bulk-bar__btn:hover:not(:disabled){background:#0065FF;border-color:#0065FF}
#crm-leads-page .crm-list-bulk-bar__btn:disabled{opacity:.45;cursor:not-allowed}
#crm-leads-page .crm-list-bulk-bar__divider{width:1px;height:22px;background:#B3D4FF}
#crm-leads-page .crm-list-bulk-bar__clear{
    display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border:0;border-radius:3px;
    background:transparent;color:#42526E;font-size:12px;font-weight:500;cursor:pointer;
}
#crm-leads-page .crm-list-bulk-bar__clear:hover{background:rgba(9,30,66,.06);color:#172B4D}
#crm-leads-page .crm-list-row__select{display:flex;align-items:center;justify-content:center}
#crm-leads-page .crm-list-row__select--spacer{visibility:hidden}
#crm-leads-page .crm-list-select,#crm-leads-page .crm-list-select-all{
    width:16px;height:16px;margin:0;border:2px solid #C1C7D0;border-radius:3px;cursor:pointer;accent-color:#0052CC;
}
#crm-leads-page .crm-leads-list-head__select{display:flex;align-items:center;justify-content:center}
#crm-leads-page .crm-list-status-rail{
    margin:12px 0 8px;padding:8px 10px;border:1px solid var(--jira-border);border-radius:3px;background:var(--jira-bg);
}
#crm-leads-page .crm-list-status-rail__label{
    display:flex;align-items:center;gap:6px;margin-bottom:6px;
    color:var(--jira-muted);font-size:var(--jira-font-size-xs);font-weight:500;
}
#crm-leads-page .crm-list-status-rail__zones{display:flex;flex-wrap:wrap;gap:4px}
#crm-leads-page .crm-list-status-drop{
    padding:2px 8px;border:1px solid var(--jira-border);border-radius:var(--jira-lozenge-radius);
    background:var(--jira-surface);color:var(--jira-subtle);
    font-size:var(--jira-font-size-xs);font-weight:600;text-transform:uppercase;letter-spacing:.02em;
}
#crm-leads-page .crm-list-status-drop[data-status="new"]{background:#FFF7D6;color:#974F0C;border-color:#FF991F}
#crm-leads-page .crm-list-status-drop[data-status="contacted"]{background:#DEEBFF;color:#0747A6;border-color:#4C9AFF}
#crm-leads-page .crm-list-status-drop[data-status="qualified"]{background:#EAE6FF;color:#403294;border-color:#8777D9}
#crm-leads-page .crm-list-status-drop[data-status="proposal_sent"]{background:#E6FCFF;color:#008DA6;border-color:#00B8D9}
#crm-leads-page .crm-list-status-drop[data-status="negotiation"]{background:#FFF0B3;color:#172B4D;border-color:#FF991F}
#crm-leads-page .crm-list-status-drop[data-status="won"]{background:#E3FCEF;color:#006644;border-color:#36B37E}
#crm-leads-page .crm-list-status-drop[data-status="lost"]{background:#FFEBE6;color:#BF2600;border-color:#FF5630}
#crm-leads-page .crm-list-status-drop[data-status="on_hold"]{background:#DFE1E6;color:#42526E;border-color:#C1C7D0}
#crm-leads-page .crm-list-status-drop.is-drag-over{border-color:var(--jira-brand)!important;background:var(--jira-brand-subtle)!important;color:var(--jira-brand)!important}
#crm-leads-page .crm-leads-list-head{
    display:grid;grid-template-columns:28px 28px minmax(210px,1.5fr) repeat(4,minmax(96px,.72fr)) minmax(76px,.48fr) 56px;
    gap:8px;padding:6px 12px;color:var(--jira-muted);font-size:var(--jira-font-size-xs);font-weight:600;
    letter-spacing:.04em;text-transform:uppercase;border-bottom:1px solid var(--jira-border);
}
#crm-leads-page .crm-leads-list{display:flex;flex-direction:column;gap:0}
#crm-leads-page .crm-list-row{
    display:grid;grid-template-columns:28px 28px minmax(210px,1.5fr) repeat(4,minmax(96px,.72fr)) minmax(76px,.48fr) 56px;
    gap:8px;align-items:center;padding:8px 12px;border-bottom:1px solid var(--jira-border);
    background:var(--jira-surface);box-shadow:none;cursor:pointer;transition:background .1s ease;
}
#crm-leads-page .crm-list-row:hover,#crm-leads-page .crm-list-row:focus-visible{
    background:var(--jira-bg);outline:none;transform:none;border-color:var(--jira-border);
}
#crm-leads-page .crm-list-row.is-selected{background:#F0F6FF}
#crm-leads-page .crm-list-row.is-selected:hover{background:#E6F0FF}
#crm-leads-page .crm-list-row.is-dragging{opacity:.55;cursor:grabbing}
#crm-leads-page .crm-list-row.is-status-updated{background:#E3FCEF}
#crm-leads-page .crm-list-row__handle{
    width:28px;height:28px;border-radius:3px;display:grid;place-items:center;
    background:transparent;border:1px solid transparent;color:var(--jira-muted);cursor:grab;
}
#crm-leads-page .crm-list-row__handle:hover{background:var(--jira-bg-neutral);border-color:var(--jira-border);color:var(--jira-subtle)}
#crm-leads-page .crm-list-row__identity{display:flex;align-items:center;gap:8px;min-width:0}
#crm-leads-page .crm-list-row__name{font-size:var(--jira-font-size-sm);font-weight:500;color:var(--jira-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#crm-leads-page .crm-list-row__meta{color:var(--jira-muted);font-size:var(--jira-font-size-xs);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#crm-leads-page .crm-list-row__field{display:flex;flex-direction:column;gap:2px;min-width:0}
#crm-leads-page .crm-list-row__label{display:none}
#crm-leads-page .crm-list-row__value{color:var(--jira-subtle);font-size:var(--jira-font-size-sm);font-weight:400}
#crm-leads-page .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:2px}
#crm-leads-page .crm-list-action{
    width:28px;height:28px;border:0;border-radius:3px;display:grid;place-items:center;
    background:transparent;color:var(--jira-muted);transition:background .1s ease,color .1s ease;
}
#crm-leads-page .crm-list-action:hover{background:var(--jira-bg-neutral);color:var(--jira-text)}
#crm-leads-page .crm-leads-list-empty{
    display:grid;place-items:center;gap:6px;padding:40px 16px;color:var(--jira-muted);text-align:center;
}
#crm-leads-page .crm-leads-list-empty strong{color:var(--jira-text);font-size:var(--jira-font-size);font-weight:500}

/* Pagination */
#crm-leads-page .crm-leads-pagination{
    display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;
    margin:0;padding:10px 16px;border-top:1px solid var(--jira-border);background:var(--jira-bg);box-shadow:none;
}
#crm-leads-page .crm-leads-pagination__summary{color:var(--jira-muted);font-size:var(--jira-font-size-sm)}
#crm-leads-page .crm-leads-pagination__summary strong{color:var(--jira-text);font-weight:600}
#crm-leads-page .crm-leads-pagination__per-page{display:flex;align-items:center;gap:6px;color:var(--jira-muted);font-size:var(--jira-font-size-sm)}
#crm-leads-page .crm-leads-pagination__per-page select{min-width:96px;height:28px;border-color:var(--jira-border);font-size:var(--jira-font-size-sm);border-radius:3px}
#crm-leads-page .crm-page-btn{
    min-height:28px;padding:0 10px;border:1px solid var(--jira-border);border-radius:3px;
    background:var(--jira-surface);color:var(--jira-subtle);font-size:var(--jira-font-size-sm);font-weight:500;
}
#crm-leads-page .crm-page-btn:hover{background:var(--jira-bg-neutral);color:var(--jira-text)}
#crm-leads-page .crm-page-indicator{color:var(--jira-muted);font-size:var(--jira-font-size-sm)}

/* Drawer & lead preview panel */
.crm-lead-drawer{width:min(540px,96vw)!important;border-left:1px solid #DFE1E6;box-shadow:-12px 0 32px rgba(9,30,66,.14)}
.crm-lead-drawer__header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid #DFE1E6;background:#fff}
.crm-lead-drawer__heading{min-width:0}
.crm-lead-drawer__eyebrow{font-size:16px;line-height:1.3;font-weight:600;color:#172B4D;letter-spacing:-.01em;text-transform:none}
.crm-lead-drawer__subtitle{margin-top:2px;color:#6B778C;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.crm-lead-drawer__close{width:32px;height:32px;padding:0;border:0;border-radius:3px;background:transparent;color:#6B778C;display:grid;place-items:center;cursor:pointer;flex-shrink:0}
.crm-lead-drawer__close:hover{background:#F4F5F7;color:#172B4D}
.crm-lead-drawer__body{padding:0;background:#F4F5F7;overflow-y:auto}

.crm-lead-panel{padding:0}
.crm-lead-panel__header{padding:16px;background:#fff;border-bottom:1px solid #DFE1E6}
.crm-lead-panel__identity{display:flex;gap:12px;align-items:flex-start}
.crm-lead-avatar--panel{width:40px;height:40px;flex:0 0 40px;font-size:12px;background:#EBECF0;color:#42526E}
.crm-lead-panel__ref{font-size:11px;font-weight:600;color:#6B778C;text-transform:uppercase;letter-spacing:.04em}
.crm-lead-panel__title{margin:2px 0 8px;font-size:22px;line-height:1.2;font-weight:500;color:#172B4D;letter-spacing:-.02em}
.crm-lead-panel__contact{display:flex;flex-wrap:wrap;gap:6px}
.crm-lead-panel__contact-chip{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:3px;background:#F4F5F7;color:#42526E;font-size:12px;text-decoration:none;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.crm-lead-panel__contact-chip:hover{background:#EBECF0;color:#172B4D}
.crm-lead-panel__controls{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:12px}
.crm-lead-panel__controls .crm-status-pill,.crm-lead-panel__controls .crm-inline-trigger{min-height:22px;line-height:22px}
.crm-lead-panel__controls .crm-category-badge{margin-top:0}

.crm-lead-panel__alert{display:flex;gap:10px;align-items:flex-start;margin:0;padding:10px 16px;background:#FFEBE6;color:#BF2600;font-size:12px;border-bottom:1px solid #FFBDAD}
.crm-lead-panel__alert strong{display:block;margin-bottom:2px;font-weight:600}
.crm-lead-panel__alert span{color:#DE350B}

.crm-lead-panel__toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:4px;padding:10px 16px;background:#fff;border-bottom:1px solid #DFE1E6}
.crm-lead-panel__tool,.crm-lead-panel__tool-form button{
    display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:4px 12px;border:1px solid #DFE1E6;border-radius:3px;
    background:#fff;color:#42526E;font-size:12px;font-weight:500;text-decoration:none;cursor:pointer;transition:background .1s ease,border-color .1s ease;
}
.crm-lead-panel__tool:hover{background:#F4F5F7;color:#172B4D;border-color:#C1C7D0}
.crm-lead-panel__tool--primary{background:#0052CC;border-color:#0052CC;color:#fff}
.crm-lead-panel__tool--primary:hover{background:#0065FF;border-color:#0065FF;color:#fff}
.crm-lead-panel__tool--success{background:#E3FCEF;border-color:#36B37E;color:#006644}
.crm-lead-panel__tool-form{margin:0;padding:0;border:0;background:transparent}

.crm-lead-panel__properties{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0;margin:0;padding:12px 16px;background:#fff;border-bottom:1px solid #DFE1E6}
.crm-lead-panel__property{display:grid;gap:2px;padding:8px 10px;border:1px solid #EBECF0;border-radius:3px;background:#FAFBFC}
.crm-lead-panel__property--wide{grid-column:1/-1}
.crm-lead-panel__property dt{margin:0;font-size:11px;font-weight:600;color:#6B778C;text-transform:uppercase;letter-spacing:.04em}
.crm-lead-panel__property dd{margin:0;font-size:13px;font-weight:500;color:#172B4D;line-height:1.4;word-break:break-word}
.crm-lead-panel__property dd a{color:#0052CC;text-decoration:none}
.crm-lead-panel__property dd a:hover{text-decoration:underline}

.crm-lead-panel__section{margin:12px 16px;border:1px solid #DFE1E6;border-radius:3px;background:#fff;overflow:hidden}
.crm-lead-panel__section-head{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 12px;border-bottom:1px solid #EBECF0;background:#FAFBFC}
.crm-lead-panel__section-head h3{display:flex;align-items:center;gap:6px;margin:0;font-size:13px;font-weight:600;color:#172B4D}
.crm-lead-panel__section-meta{font-size:11px;font-weight:500;color:#6B778C}
.crm-lead-panel__section-body{padding:0 12px 12px}
.crm-lead-panel__note{padding:10px 0;border-bottom:1px solid #EBECF0}
.crm-lead-panel__note:last-of-type{border-bottom:0}
.crm-lead-panel__note-head{display:flex;justify-content:space-between;gap:8px;margin-bottom:4px}
.crm-lead-panel__note-head strong{font-size:12px;font-weight:600;color:#172B4D}
.crm-lead-panel__note-head time{font-size:11px;color:#6B778C}
.crm-lead-panel__note p{margin:0;font-size:13px;line-height:1.45;color:#42526E}
.crm-lead-panel__note-form{margin-top:10px;padding-top:10px;border-top:1px solid #EBECF0}
.crm-lead-panel__note-form textarea{border:2px solid #DFE1E6;border-radius:3px;font-size:13px;resize:vertical}
.crm-lead-panel__note-form textarea:focus{border-color:#0052CC;box-shadow:none}
.crm-lead-panel__note-submit{margin-top:8px;min-height:32px;padding:4px 12px;border:0;border-radius:3px;background:#0052CC;color:#fff;font-size:12px;font-weight:500;cursor:pointer}
.crm-lead-panel__note-submit:hover{background:#0065FF}

.crm-lead-panel__timeline{padding:8px 12px 12px}
.crm-lead-panel__timeline-item{display:flex;gap:10px;padding:8px 0;border-bottom:1px solid #EBECF0}
.crm-lead-panel__timeline-item:last-child{border-bottom:0;padding-bottom:0}
.crm-lead-panel__timeline-icon{width:28px;height:28px;border-radius:3px;background:#DEEBFF;color:#0052CC;display:grid;place-items:center;flex:0 0 28px}
.crm-lead-panel__timeline-title{font-size:12px;font-weight:600;color:#172B4D;text-transform:capitalize}
.crm-lead-panel__timeline-body p{margin:2px 0;font-size:13px;color:#42526E;line-height:1.4}
.crm-lead-panel__timeline-body small{font-size:11px;color:#6B778C}
.crm-lead-panel__empty{margin:8px 0;color:#6B778C;font-size:13px}

.crm-lead-panel .crm-status-pill,.crm-lead-panel .crm-inline-trigger{
    min-height:20px;padding:0 6px;border-radius:3px;border:none;font-size:11px;font-weight:600;
    box-shadow:none;text-transform:capitalize;
}
.crm-lead-panel-loading{display:grid;place-items:center;gap:10px;padding:48px 24px;color:#6B778C;font-size:13px}
.crm-lead-panel-loading__spinner{width:24px;height:24px;border:2px solid #DFE1E6;border-top-color:#0052CC;border-radius:50%;animation:crmLeadSpin .8s linear infinite}
@keyframes crmLeadSpin{to{transform:rotate(360deg)}}

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
