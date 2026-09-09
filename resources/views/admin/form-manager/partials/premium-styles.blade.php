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
#form-center-page .crm-leads-table__head--entries,#form-center-page .fc-list-row--entry{grid-template-columns:minmax(56px,.35fr) minmax(120px,.65fr) minmax(88px,.45fr) minmax(0,1.6fr) 96px}
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
</style>
