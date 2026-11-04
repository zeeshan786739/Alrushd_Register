<style>
#form-center-page{font-family:var(--crm-font);color:var(--crm-text)}
#form-center-page .fc-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
#form-center-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#form-center-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#form-center-page .crm-metrics-strip__item{display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#form-center-page .crm-metrics-strip__label{font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#form-center-page .crm-metrics-strip__item strong{font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums}
#form-center-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#form-center-page .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}
#form-center-page .fc-filter-workspace{padding:14px 18px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#form-center-page .fc-filter-workspace__head{display:grid;gap:2px;margin-bottom:10px}
#form-center-page .fc-filter-workspace__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#form-center-page .fc-filter-workspace__sub{margin:0;font-size:12px;color:var(--crm-text-muted)}
#form-center-page .fc-source-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
#form-center-page .fc-stat-filter{
    display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;cursor:pointer;
    transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease;
}
#form-center-page .fc-stat-filter:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px);color:var(--crm-brand)}
#form-center-page .fc-stat-filter.is-active{border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand)}
#form-center-page .fc-stat-filter__icon{display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken);color:inherit}
#form-center-page .fc-stat-filter__icon iconify-icon{font-size:20px}
#form-center-page .fc-stat-filter--all .fc-stat-filter__icon{background:rgba(15,39,74,.08);color:var(--crm-brand)}
#form-center-page .fc-stat-filter--all.is-active .fc-stat-filter__icon{background:var(--crm-brand);color:#fff}
#form-center-page .fc-stat-filter--active .fc-stat-filter__icon{background:rgba(22,163,74,.12);color:#15803d}
#form-center-page .fc-stat-filter--active.is-active .fc-stat-filter__icon{background:#16a34a;color:#fff}
#form-center-page .fc-stat-filter--landing .fc-stat-filter__icon{background:rgba(245,158,11,.12);color:#b45309}
#form-center-page .fc-stat-filter--landing.is-active .fc-stat-filter__icon{background:#f59e0b;color:#fff}
#form-center-page .fc-stat-filter--submissions .fc-stat-filter__icon{background:rgba(124,58,237,.12);color:#6d28d9}
#form-center-page .fc-stat-filter--submissions.is-active .fc-stat-filter__icon{background:#7c3aed;color:#fff}
#form-center-page .fc-stat-filter__label{font-size:12px;font-weight:700;line-height:1.2}
#form-center-page .fc-stat-filter__count{font-size:16px;font-weight:800;font-variant-numeric:tabular-nums;color:var(--crm-text);line-height:1}
#form-center-page .fc-stat-filter.is-active .fc-stat-filter__count{color:var(--crm-brand)}
#form-center-page .fc-stat-filter__meta{font-size:10px;color:var(--crm-text-muted);line-height:1.2}
#form-center-page .crm-leads-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:10px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#form-center-page .crm-leads-toolbar__meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#form-center-page .crm-leads-toolbar__meta strong{color:var(--crm-text);font-weight:600}
#form-center-page .fc-page-body{padding:0 16px 16px}
#form-center-page .crm-list-shell{padding:0}
#form-center-page .crm-leads-table{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#form-center-page .crm-leads-table__head,#form-center-page .fc-list-row{
    display:grid;grid-template-columns:minmax(220px,1.5fr) minmax(56px,.35fr) minmax(56px,.35fr) minmax(72px,.45fr) minmax(120px,.7fr) minmax(88px,.45fr) minmax(200px,1fr);
    gap:10px;align-items:center;padding:0 12px 0 14px;
}
#form-center-page .crm-leads-table__head{
    position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;
    color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
    background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border);
}
#form-center-page .crm-leads-list{display:flex;flex-direction:column;gap:0}
#form-center-page .crm-leads-list > .fc-list-row,#form-center-page .crm-leads-list > .fc-form-row{
    display:grid;width:100%;margin:0;box-sizing:border-box;cursor:pointer;
}
#form-center-page .fc-list-row{
    position:relative;min-height:0;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);
    transition:background .12s ease;
}
#form-center-page .fc-list-row:last-child{border-bottom:0}
#form-center-page .fc-list-row:hover{background:var(--crm-surface-sunken)}
#form-center-page .fc-list-row:nth-child(even){background:rgba(9,30,66,.018)}
#form-center-page .fc-list-row:nth-child(even):hover{background:var(--crm-surface-sunken)}
#form-center-page .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0;background:linear-gradient(180deg,#1a3a5c,var(--crm-brand))}
#form-center-page .fc-list-row__identity{display:flex;align-items:flex-start;gap:10px;min-width:0}
#form-center-page .fc-form-icon{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;flex-shrink:0}
#form-center-page .fc-form-icon iconify-icon{font-size:18px}
#form-center-page .fc-form-identity{min-width:0;display:grid;gap:3px}
#form-center-page .fc-form-identity h6{margin:0;font-size:13px;font-weight:700;color:var(--crm-text);overflow-wrap:anywhere;line-height:1.35}
#form-center-page .fc-table-url{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;color:var(--crm-text-muted)}
#form-center-page .fc-list-row__field{display:flex;align-items:center;flex-wrap:wrap;gap:6px;min-width:0;font-size:12px;color:var(--crm-text)}
#form-center-page .fc-list-row__field--date{display:flex;flex-direction:column;align-items:flex-start;gap:1px}
#form-center-page .fc-list-row__date{font-size:12px;font-weight:600;color:var(--crm-text)}
#form-center-page .fc-list-row__date-sub{font-size:10px;color:var(--crm-text-muted)}
#form-center-page .fc-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:wrap}
#form-center-page .fc-list-row__actions form{margin:0}
#form-center-page .fc-table-actions{display:flex;flex-wrap:wrap;gap:6px;justify-content:flex-end;align-items:center}
#form-center-page .fc-action-icon{
    box-sizing:border-box;width:32px!important;height:32px!important;min-width:32px;max-width:32px;min-height:32px;max-height:32px;
    flex:0 0 32px;padding:0!important;border-radius:50%!important;display:inline-grid;place-items:center;line-height:1;box-shadow:none;
}
#form-center-page .fc-action-icon i{font-size:17px;line-height:1;display:block}
#form-center-page .fc-action-icon svg{display:block!important;width:18px!important;height:18px!important;flex:0 0 18px;color:inherit;pointer-events:none}
#form-center-page .fc-table-actions > form{display:flex!important;flex:0 0 32px;margin:0}
#form-center-page .fc-submissions-btn{font-size:12px;min-height:32px;padding:5px 10px!important}
#form-center-page .fc-badge{font-size:11px;line-height:1.4;padding:2px 8px;border-radius:999px;font-weight:700;border:0;cursor:pointer}
#form-center-page .fc-badge-primary{background:var(--crm-brand-soft);color:var(--crm-brand)}
#form-center-page .fc-badge-neutral{background:var(--crm-surface-sunken);color:var(--crm-text-muted)}
#form-center-page .fc-badge-interactive:hover{filter:brightness(.96)}
#form-center-page .fc-preview-text{font-size:12px;color:var(--crm-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;margin:0}
#form-center-page .crm-leads-list-empty{display:grid;place-items:center;gap:6px;padding:48px 16px;color:var(--crm-text-muted);text-align:center}
#form-center-page .crm-leads-list-empty strong{color:var(--crm-text);font-weight:600}
#form-center-page .fc-pagination{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;border-top:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#form-center-page .fc-pagination-info{font-size:12px;color:var(--crm-text-muted)}
#form-center-page .fc-filter-workspace .um-ai-search{display:grid;gap:8px}
#form-center-page .fc-filter-workspace .um-ai-search__head{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px}
#form-center-page .fc-filter-workspace .um-ai-search__badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;background:linear-gradient(135deg,rgba(15,39,74,.94),rgba(197,168,109,.82));color:#fff;font-size:11px;font-weight:700}
#form-center-page .fc-filter-workspace .um-ai-search__shell{display:flex;align-items:center;gap:10px;padding:5px 5px 5px 10px;min-height:46px;border:1px solid rgba(197,168,109,.35);border-radius:14px;background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(197,168,109,.06));box-shadow:0 10px 28px rgba(15,39,74,.07),inset 0 1px 0 rgba(255,255,255,.8)}
#form-center-page .fc-filter-workspace .um-ai-search__icon{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,rgba(15,39,74,.08),rgba(197,168,109,.16));color:var(--crm-brand);flex-shrink:0}
#form-center-page .fc-filter-workspace .um-ai-search__input,#form-center-page .fc-filter-workspace .form-control,#form-center-page .fc-filter-workspace .form-select{flex:1;border:0;background:transparent;min-width:0;padding:8px 0;font-size:14px;color:var(--crm-text);outline:none;height:38px}
#form-center-page .fc-filter-workspace .form-control,#form-center-page .fc-filter-workspace .form-select{border:1px solid var(--crm-border);border-radius:10px;padding:6px 12px;background:var(--crm-surface)}
#form-center-page .fc-filter-grid{display:grid;grid-template-columns:minmax(0,1.2fr) repeat(3,minmax(120px,.55fr)) auto;gap:10px;align-items:end}
#form-center-page .fc-filter-field label{display:block;margin-bottom:4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#form-center-page .fc-stat-pill-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:16px}
#form-center-page .fc-stat-pill{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border:2px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);text-decoration:none;color:inherit;transition:border-color .12s ease,background .12s ease,box-shadow .12s ease;
}
#form-center-page .fc-stat-pill:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft)}
#form-center-page .fc-stat-pill.active-filter{border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));box-shadow:0 8px 22px rgba(15,39,74,.1)}
#form-center-page .fc-stat-pill-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;flex-shrink:0}
#form-center-page .fc-stat-pill-icon iconify-icon{font-size:18px}
@media(max-width:1100px){
    #form-center-page .fc-source-grid,#form-center-page .fc-stat-pill-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    #form-center-page .fc-filter-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:768px){
    #form-center-page .crm-leads-table__head{display:none}
    #form-center-page .fc-list-row{grid-template-columns:1fr!important;gap:8px;padding:12px}
    #form-center-page .fc-list-row__actions{justify-content:flex-start}
    #form-center-page .fc-source-grid,#form-center-page .fc-stat-pill-grid,#form-center-page .fc-filter-grid{grid-template-columns:1fr}
}
/* Submissions workspace */
#form-center-page .fc-subs{--fc-pending:#b45309;--fc-pending-bg:rgba(217,119,6,.12);--fc-approved:#15803d;--fc-approved-bg:rgba(22,163,74,.12);--fc-rejected:#b91c1c;--fc-rejected-bg:rgba(220,38,38,.1);border:1px solid var(--crm-border);border-radius:16px;background:var(--crm-surface);box-shadow:0 1px 2px rgba(15,39,74,.04),0 12px 32px rgba(15,39,74,.05);overflow:hidden}
html[data-theme="dark"] #form-center-page .fc-subs{--fc-pending:#fbbf24;--fc-pending-bg:rgba(251,191,36,.14);--fc-approved:#4ade80;--fc-approved-bg:rgba(74,222,128,.13);--fc-rejected:#fca5a5;--fc-rejected-bg:rgba(248,113,113,.14);box-shadow:none}
#form-center-page .fc-subs-tabs{display:flex;gap:4px;padding:0 16px;border-bottom:1px solid var(--crm-border);overflow-x:auto;scrollbar-width:none}
#form-center-page .fc-subs-tabs::-webkit-scrollbar{display:none}
#form-center-page .fc-subs-tab{position:relative;display:inline-flex;align-items:center;gap:8px;min-height:50px;padding:0 12px;color:var(--crm-text-muted);font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;transition:color .12s ease}
#form-center-page .fc-subs-tab:hover{color:var(--crm-text)}
#form-center-page .fc-subs-tab:focus-visible{outline:2px solid var(--crm-brand);outline-offset:-4px;border-radius:8px}
#form-center-page .fc-subs-tab.is-active{color:var(--crm-text)}
#form-center-page .fc-subs-tab.is-active::after{content:"";position:absolute;left:8px;right:8px;bottom:0;height:2px;border-radius:2px 2px 0 0;background:var(--crm-brand)}
html[data-theme="dark"] #form-center-page .fc-subs-tab.is-active::after{background:var(--crm-text)}
#form-center-page .fc-subs-tab__dot{width:7px;height:7px;border-radius:50%;background:var(--crm-text-muted)}
#form-center-page .fc-subs-tab[data-status="all"] .fc-subs-tab__dot{display:none}
#form-center-page .fc-subs-tab[data-status="pending"] .fc-subs-tab__dot{background:var(--fc-pending)}
#form-center-page .fc-subs-tab[data-status="approved"] .fc-subs-tab__dot{background:var(--fc-approved)}
#form-center-page .fc-subs-tab[data-status="rejected"] .fc-subs-tab__dot{background:var(--fc-rejected)}
#form-center-page .fc-subs-tab__count{min-width:22px;padding:1px 7px;border-radius:999px;background:var(--crm-surface-sunken);color:var(--crm-text-muted);font-size:11px;font-weight:700;text-align:center;font-variant-numeric:tabular-nums}
#form-center-page .fc-subs-tab.is-active .fc-subs-tab__count{background:var(--crm-brand-soft);color:var(--crm-text)}
#form-center-page .fc-subs-controls{padding:14px 16px 0}
#form-center-page .fc-subs-toolbar{display:flex;align-items:center;gap:10px}
#form-center-page .fc-subs-search{flex:1;display:flex;align-items:center;gap:10px;min-width:0;height:42px;margin:0;padding:0 14px;border:1px solid var(--crm-border);border-radius:10px;background:var(--crm-surface);color:var(--crm-text-muted);cursor:text;transition:border-color .12s ease,box-shadow .12s ease}
#form-center-page .fc-subs-search:focus-within{border-color:var(--crm-brand);box-shadow:0 0 0 3px var(--crm-brand-soft)}
#form-center-page .fc-subs-search iconify-icon{font-size:17px;flex-shrink:0}
#form-center-page .fc-subs-search input{flex:1;min-width:0;height:100%;border:0;outline:0;background:transparent;color:var(--crm-text);font-size:14px}
#form-center-page .fc-subs-search input::placeholder{color:var(--crm-text-muted)}
#form-center-page .fc-subs-filter-toggle{display:inline-flex;align-items:center;gap:7px;height:42px;padding:0 14px;border:1px solid var(--crm-border);border-radius:10px;background:var(--crm-surface);color:var(--crm-text);font-size:13px;font-weight:600;white-space:nowrap;transition:background .12s ease,border-color .12s ease}
#form-center-page .fc-subs-filter-toggle:hover,#form-center-page .fc-subs-filter-toggle[aria-expanded="true"]{background:var(--crm-surface-sunken)}
#form-center-page .fc-subs-filter-toggle.is-active{border-color:var(--crm-brand)}
#form-center-page .fc-subs-filter-toggle iconify-icon{font-size:16px}
#form-center-page .fc-subs-filter-toggle__count{display:inline-grid;place-items:center;min-width:19px;height:19px;padding:0 5px;border-radius:999px;background:var(--crm-brand);color:#fff;font-size:11px;font-weight:700}
#form-center-page .fc-subs-sort{display:flex;align-items:center;gap:8px;flex-shrink:0}
#form-center-page .fc-subs-sort label{margin:0;color:var(--crm-text-muted);font-size:12px;font-weight:600;white-space:nowrap}
#form-center-page .fc-subs .form-select,#form-center-page .fc-subs .form-control{height:42px;border:1px solid var(--crm-border);border-radius:10px;background-color:var(--crm-surface);color:var(--crm-text);font-size:13px;box-shadow:none}
#form-center-page .fc-subs .form-select{padding:0 32px 0 12px;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12' fill='none'%3E%3Cpath d='M3 4.5 6 7.5 9 4.5' stroke='%235b6b7f' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 11px center;background-size:12px 12px}
html[data-theme="dark"] #form-center-page .fc-subs .form-select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12' fill='none'%3E%3Cpath d='M3 4.5 6 7.5 9 4.5' stroke='%23c3cedb' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E")}
#form-center-page .fc-subs .form-control::placeholder{color:var(--crm-text-muted);font-size:13px!important;opacity:1}
html[data-theme="dark"] #form-center-page .fc-subs input[type="date"]{color-scheme:dark}
#form-center-page .fc-subs .form-control{padding:0 12px}
#form-center-page .fc-subs .form-select:focus,#form-center-page .fc-subs .form-control:focus{border-color:var(--crm-brand);box-shadow:0 0 0 3px var(--crm-brand-soft)}
#form-center-page .fc-subs-sort .form-select{width:190px}
#form-center-page .fc-subs-panel{margin-top:12px;padding:14px;border:1px solid var(--crm-border);border-radius:12px;background:var(--crm-surface-sunken)}
#form-center-page .fc-subs-panel[hidden]{display:none}
#form-center-page .fc-subs-panel__fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}
#form-center-page .fc-subs-field label{display:block;margin-bottom:5px;color:var(--crm-text);font-size:12px;font-weight:600}
#form-center-page .fc-subs-panel__actions{display:flex;justify-content:flex-end;margin-top:12px}
#form-center-page .fc-subs-btn{display:inline-flex;align-items:center;justify-content:center;height:38px;padding:0 16px;border:1px solid var(--crm-border);border-radius:9px;background:var(--crm-surface);color:var(--crm-text);font-size:13px;font-weight:600;text-decoration:none;transition:background .12s ease}
#form-center-page .fc-subs-btn:hover{background:var(--crm-surface-sunken)}
#form-center-page .fc-subs-btn--primary{border-color:var(--crm-brand);background:var(--crm-brand);color:#fff}
#form-center-page .fc-subs-btn--primary:hover{background:var(--crm-brand);filter:brightness(1.15)}
#form-center-page .fc-subs-summary{display:flex;flex-wrap:wrap;align-items:center;gap:8px;min-height:30px;padding:12px 18px}
#form-center-page .fc-subs-summary__count{margin-right:4px;color:var(--crm-text-muted);font-size:12px}
#form-center-page .fc-subs-summary__count strong{color:var(--crm-text);font-weight:700;font-variant-numeric:tabular-nums}
#form-center-page .fc-subs-chip{display:inline-flex;align-items:center;gap:5px;padding:3px 8px 3px 10px;border-radius:999px;background:var(--crm-brand-soft);color:var(--crm-text);font-size:12px;font-weight:600;text-decoration:none}
#form-center-page .fc-subs-chip__label{color:var(--crm-text-muted);font-weight:500}
#form-center-page .fc-subs-chip iconify-icon{font-size:14px;opacity:.65}
#form-center-page .fc-subs-chip:hover iconify-icon{opacity:1}
#form-center-page .fc-subs-clear{color:var(--crm-text-muted);font-size:12px;font-weight:600;text-decoration:underline;text-underline-offset:2px}
#form-center-page .fc-subs-clear:hover{color:var(--crm-text)}
#form-center-page .fc-subs-head,#form-center-page .fc-subs-row{display:grid;grid-template-columns:minmax(240px,1.7fr) minmax(150px,1.2fr) minmax(120px,.7fr) 128px 84px;gap:16px;align-items:center;padding-left:18px;padding-right:14px}
#form-center-page .fc-subs-head{min-height:38px;border-top:1px solid var(--crm-border);border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken);color:var(--crm-text-muted);font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
#form-center-page .fc-sort-link{display:inline-flex;align-items:center;gap:4px;color:inherit;text-decoration:none;font:inherit;letter-spacing:inherit;text-transform:inherit;min-width:0}
#form-center-page .fc-sort-link:hover,#form-center-page .fc-sort-link.is-active{color:var(--crm-text)}
#form-center-page .fc-sort-link iconify-icon{font-size:13px;flex-shrink:0}
#form-center-page .fc-subs-row{position:relative;min-height:74px;padding-top:12px;padding-bottom:12px;border-bottom:1px solid var(--crm-border);cursor:pointer;transition:background .12s ease}
#form-center-page .fc-subs-rows .fc-subs-row:last-child{border-bottom:0}
#form-center-page .fc-subs-row:hover{background:var(--crm-surface-sunken)}
#form-center-page .fc-subs-applicant{display:flex;align-items:center;gap:12px;min-width:0}
#form-center-page .fc-subs-avatar{display:grid;place-items:center;width:40px;height:40px;flex-shrink:0;border-radius:50%;background:var(--crm-brand-soft);color:var(--crm-text);font-size:13px;font-weight:700;letter-spacing:.02em}
#form-center-page .fc-subs-avatar.is-empty{background:var(--crm-surface-sunken);color:var(--crm-text-muted);font-size:18px}
#form-center-page .fc-subs-applicant__copy{display:flex;flex-direction:column;gap:1px;min-width:0}
#form-center-page .fc-applicant__name{color:var(--crm-text);font-size:14px;font-weight:700;line-height:1.35;text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#form-center-page .fc-applicant__name:hover{text-decoration:underline;text-underline-offset:2px}
#form-center-page .fc-applicant__contact{color:var(--crm-text-muted);font-size:12px;line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#form-center-page .fc-applied{display:flex;flex-direction:column;align-items:flex-start;gap:2px;min-width:0}
#form-center-page .fc-applied__role{max-width:100%;color:var(--crm-text);font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#form-center-page .fc-applied__sub{max-width:100%;color:var(--crm-text-muted);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#form-center-page .fc-subs-date{display:flex;flex-direction:column;gap:2px;min-width:0}
#form-center-page .fc-subs-date .fc-list-row__date{font-size:13px}
#form-center-page .fc-subs-date .fc-list-row__date-sub{font-size:12px}
#form-center-page .fc-subs-status form{margin:0}
#form-center-page .fc-subs-status-select{--fc-tone:var(--fc-pending);--fc-tone-bg:var(--fc-pending-bg);display:inline-block;width:auto;max-width:100%;height:30px;padding:0 26px 0 12px;border:0;border-radius:999px;background-color:var(--fc-tone-bg);background-image:linear-gradient(45deg,transparent 50%,var(--fc-tone) 50%),linear-gradient(135deg,var(--fc-tone) 50%,transparent 50%);background-position:calc(100% - 14px) 13px,calc(100% - 10px) 13px;background-size:4px 4px;background-repeat:no-repeat;color:var(--fc-tone);font-size:12px;font-weight:700;line-height:30px;appearance:none;-webkit-appearance:none;cursor:pointer}
#form-center-page .fc-subs-status-select.is-static{padding-right:12px;background-image:none;cursor:default}
#form-center-page .fc-subs-status-select[data-status="approved"]{--fc-tone:var(--fc-approved);--fc-tone-bg:var(--fc-approved-bg)}
#form-center-page .fc-subs-status-select[data-status="rejected"]{--fc-tone:var(--fc-rejected);--fc-tone-bg:var(--fc-rejected-bg)}
#form-center-page .fc-subs-status-select:focus-visible{outline:2px solid var(--fc-tone);outline-offset:2px}
#form-center-page .fc-subs-status-select option{background:var(--crm-surface);color:var(--crm-text)}
#form-center-page .fc-subs-actions{display:flex;align-items:center;justify-content:flex-end;gap:4px}
#form-center-page .fc-subs-actions form{display:flex;margin:0}
#form-center-page .fc-subs-icon-btn{display:inline-grid;place-items:center;width:34px;height:34px;padding:0;border:0;border-radius:9px;background:transparent;color:var(--crm-text-muted);font-size:18px;transition:background .12s ease,color .12s ease}
#form-center-page .fc-subs-icon-btn:hover{background:var(--crm-brand-soft);color:var(--crm-text)}
#form-center-page .fc-subs-icon-btn.is-danger:hover{background:var(--fc-rejected-bg);color:var(--fc-rejected)}
#form-center-page .fc-subs-icon-btn:focus-visible,#form-center-page .fc-subs-filter-toggle:focus-visible,#form-center-page .fc-sort-link:focus-visible,#form-center-page .fc-applicant__name:focus-visible{outline:2px solid var(--crm-brand);outline-offset:2px}
#form-center-page .fc-subs-empty{display:flex;flex-direction:column;align-items:center;gap:6px;padding:56px 20px 64px;border-top:1px solid var(--crm-border);text-align:center}
#form-center-page .fc-subs-empty__icon{display:grid;place-items:center;width:56px;height:56px;margin-bottom:8px;border-radius:50%;background:var(--crm-surface-sunken);color:var(--crm-text-muted);font-size:26px}
#form-center-page .fc-subs-empty strong{color:var(--crm-text);font-size:15px}
#form-center-page .fc-subs-empty p{margin:0 0 10px;color:var(--crm-text-muted);font-size:13px}
#form-center-page .fc-subs .fc-pagination{justify-content:flex-end}
#form-center-page .fc-subs .fc-pagination nav{width:100%}
#form-center-page .fc-subs .fc-pagination nav .d-sm-flex{justify-content:flex-end!important}
#form-center-page .fc-subs .fc-pagination nav .d-sm-flex > div:first-child{display:none}
#form-center-page .fc-subs .fc-pagination .pagination{margin:0}
@media(max-width:1100px){
    #form-center-page .fc-subs-head,#form-center-page .fc-subs-row{grid-template-columns:minmax(200px,1.5fr) minmax(130px,1fr) 104px 118px 76px;gap:12px}
}
@media(max-width:860px){
    #form-center-page .fc-subs-toolbar{flex-wrap:wrap}
    #form-center-page .fc-subs-search{flex:1 1 100%}
    #form-center-page .fc-subs-filter-toggle{flex:0 0 auto}
    #form-center-page .fc-subs-sort{flex:1;min-width:0}
    #form-center-page .fc-subs-sort .form-select{flex:1;width:auto;min-width:0}
    #form-center-page .fc-subs-head{display:none}
    #form-center-page .fc-subs-table{border-top:1px solid var(--crm-border)}
    #form-center-page .fc-subs-row{grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px;padding:14px 16px}
    #form-center-page .fc-subs-applicant{grid-column:1 / -1}
    #form-center-page .fc-applied::before,#form-center-page .fc-subs-date::before{content:attr(data-label);color:var(--crm-text-muted);font-size:11px;font-weight:600}
}
@media(max-width:480px){
    #form-center-page .fc-subs-sort label{display:none}
    #form-center-page .fc-subs-panel__fields{grid-template-columns:1fr}
    #form-center-page .fc-subs-btn--primary{width:100%}
}
</style>
