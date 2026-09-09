@php
    $pageId = $pageId ?? 'crm-list-workspace-page';
    $gridColumns = $gridColumns ?? 'minmax(240px,2.2fr) minmax(108px,.72fr) minmax(124px,.78fr) minmax(96px,.62fr) minmax(88px,.55fr) 76px';
    $statusGridCols = $statusGridCols ?? 4;
@endphp
<style>
#{{ $pageId }}{font-family:var(--crm-font);color:var(--crm-text)}
#{{ $pageId }} .crm-list-workspace-shell{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
#{{ $pageId }} .crm-metrics-strip{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08))}
#{{ $pageId }} .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#{{ $pageId }} .crm-metrics-strip__item{display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#{{ $pageId }} .crm-metrics-strip__label{font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-metrics-strip__item strong{font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums}
#{{ $pageId }} .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#{{ $pageId }} .crm-metrics-strip__links{display:flex;align-items:center;gap:12px}
#{{ $pageId }} .crm-metrics-strip__link{font-size:var(--crm-text-sm);font-weight:500;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#{{ $pageId }} .crm-metrics-strip__link:hover{text-decoration:underline;color:var(--crm-brand-hover)}
#{{ $pageId }} .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}
#{{ $pageId }} .crm-filter-workspace{padding:14px 18px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#{{ $pageId }} .crm-module-finder{display:grid;gap:12px}
#{{ $pageId }} .crm-module-finder__lookup{display:grid;gap:10px;padding-bottom:12px;border-bottom:1px solid var(--crm-border)}
#{{ $pageId }} .crm-ai-search{position:relative;display:grid;gap:8px}
#{{ $pageId }} .crm-ai-search__head{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px}
#{{ $pageId }} .crm-ai-search__badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;background:linear-gradient(135deg,rgba(15,39,74,.94),rgba(197,168,109,.82));color:#fff;font-size:11px;font-weight:700;letter-spacing:.03em}
#{{ $pageId }} .crm-ai-search__hint{font-size:12px;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-ai-search__shell{padding:5px 5px 5px 10px;min-height:46px;border:1px solid rgba(197,168,109,.35);border-radius:14px;background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(197,168,109,.06));box-shadow:0 10px 28px rgba(15,39,74,.07),inset 0 1px 0 rgba(255,255,255,.8)}
#{{ $pageId }} .crm-ai-search__shell:focus-within{border-color:rgba(197,168,109,.75);box-shadow:0 12px 32px rgba(15,39,74,.1),0 0 0 3px rgba(197,168,109,.18)}
#{{ $pageId }} .crm-ai-search__icon{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,rgba(15,39,74,.08),rgba(197,168,109,.16));color:var(--crm-brand)}
#{{ $pageId }} .crm-smart-search__shell{display:flex;align-items:center;gap:10px}
#{{ $pageId }} .crm-smart-search__input{flex:1;border:0;background:transparent;min-width:0;padding:8px 0;font-size:14px;color:var(--crm-text);outline:none}
#{{ $pageId }} .crm-smart-search__go{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:34px;padding:0 14px;border:0;border-radius:10px;background:linear-gradient(135deg,rgba(15,39,74,.96),rgba(197,168,109,.78));color:#fff;font-size:12px;font-weight:600;cursor:pointer;flex-shrink:0}
#{{ $pageId }} .crm-lead-finder__quick-pills{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
#{{ $pageId }} .crm-quick-pill{display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:6px 14px;border:1px solid var(--crm-border);border-radius:999px;background:var(--crm-surface);color:var(--crm-text-muted);font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;transition:background .1s ease,border-color .1s ease,color .1s ease}
#{{ $pageId }} .crm-quick-pill:hover{background:var(--crm-brand-soft);border-color:rgba(15,39,74,.16);color:var(--crm-brand)}
#{{ $pageId }} .crm-quick-pill.is-active{background:var(--crm-brand);border-color:var(--crm-brand);color:#fff}
#{{ $pageId }} .crm-module-finder__statuses{display:grid;gap:10px}
#{{ $pageId }} .crm-lead-finder__sources-head{display:grid;gap:2px}
#{{ $pageId }} .crm-lead-finder__sources-title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text);letter-spacing:-.01em}
#{{ $pageId }} .crm-lead-finder__sources-sub{margin:0;font-size:12px;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-module-finder__status-grid{display:grid;grid-template-columns:repeat({{ $statusGridCols }},minmax(0,1fr));gap:8px}
#{{ $pageId }} .crm-source-card{display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease}
#{{ $pageId }} .crm-source-card:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px)}
#{{ $pageId }} .crm-source-card.is-active{border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand)}
#{{ $pageId }} .crm-source-card.is-empty:not(.is-active){opacity:.72}
#{{ $pageId }} .crm-source-card__icon{display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken)}
#{{ $pageId }} .crm-source-card.is-active .crm-source-card__icon{background:var(--crm-brand);color:#fff}
#{{ $pageId }} .crm-source-card__label{font-size:12px;font-weight:700;line-height:1.2}
#{{ $pageId }} .crm-source-card__count{font-size:16px;font-weight:800;font-variant-numeric:tabular-nums;color:var(--crm-text);line-height:1}
#{{ $pageId }} .crm-source-card.is-active .crm-source-card__count{color:var(--crm-brand)}
#{{ $pageId }} .crm-lead-finder__more{border:1px dashed var(--crm-border);border-radius:12px;padding:0 12px 12px;background:rgba(15,39,74,.015)}
#{{ $pageId }} .crm-lead-finder__more-summary{display:flex;align-items:center;gap:8px;padding:10px 0;cursor:pointer;list-style:none;font-size:12px;font-weight:600;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-lead-finder__more-summary::-webkit-details-marker{display:none}
#{{ $pageId }} .crm-lead-finder__refine{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px 12px}
#{{ $pageId }} .crm-lead-finder__refine-field label{display:block;margin-bottom:4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-lead-finder__refine-field .form-select,#{{ $pageId }} .crm-lead-finder__refine-field .form-control{height:34px;padding:4px 10px;border:1px solid var(--crm-border);border-radius:10px;font-size:12px;color:var(--crm-text);background:var(--crm-surface)}
#{{ $pageId }} .crm-lead-finder__refine-field--wide{grid-column:1 / -1;display:flex;flex-wrap:wrap;gap:8px}
#{{ $pageId }} .crm-lead-finder__active{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:10px 12px;border-radius:12px;background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));border:1px solid var(--crm-border)}
#{{ $pageId }} .crm-lead-finder__active-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-lead-finder__active-list{display:flex;flex-wrap:wrap;align-items:center;gap:6px;flex:1}
#{{ $pageId }} .crm-lead-finder__clear{margin-left:auto;font-size:11px;font-weight:600;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#{{ $pageId }} .crm-active-filter{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border:1px solid var(--crm-border);border-radius:999px;background:var(--crm-surface);color:var(--crm-text-muted);font-size:11px;font-weight:600;text-decoration:none}
#{{ $pageId }} .crm-leads-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:10px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#{{ $pageId }} .crm-leads-toolbar__meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#{{ $pageId }} .crm-leads-toolbar__meta strong{color:var(--crm-text);font-weight:600;font-variant-numeric:tabular-nums}
#{{ $pageId }} .crm-leads-toolbar__filters{padding:1px 6px;border-radius:var(--crm-radius-sm);background:var(--crm-surface-sunken);font-size:var(--crm-text-xs);font-weight:600;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-list-shell{padding:0 16px 16px}
#{{ $pageId }} .crm-leads-table{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#{{ $pageId }} .crm-leads-table__head,#{{ $pageId }} .crm-list-row{display:grid;grid-template-columns:{{ $gridColumns }};gap:10px;align-items:center;padding:0 12px 0 14px}
#{{ $pageId }} .crm-leads-table__head{position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border)}
#{{ $pageId }} .crm-leads-list{display:flex;flex-direction:column}
#{{ $pageId }} .crm-list-row{position:relative;min-height:72px;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);cursor:pointer;transition:background .12s ease}
#{{ $pageId }} .crm-list-row:last-child{border-bottom:0}
#{{ $pageId }} .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0}
#{{ $pageId }} .crm-list-row--priority-low .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-pending .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-draft .crm-list-row__priority-rail{background:linear-gradient(180deg,#cbd5e1,#94a3b8)}
#{{ $pageId }} .crm-list-row--priority-medium .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-in_progress .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-sent .crm-list-row__priority-rail{background:linear-gradient(180deg,#1a3a5c,var(--crm-brand))}
#{{ $pageId }} .crm-list-row--priority-high .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-on_hold .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-partially_paid .crm-list-row__priority-rail{background:linear-gradient(180deg,#fb923c,#ea580c)}
#{{ $pageId }} .crm-list-row--priority-urgent .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-overdue .crm-list-row__priority-rail{background:linear-gradient(180deg,#f87171,#dc2626)}
#{{ $pageId }} .crm-list-row--status-completed .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-accepted .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-paid .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-active .crm-list-row__priority-rail{background:linear-gradient(180deg,#4ade80,#16a34a)}
#{{ $pageId }} .crm-list-row--status-cancelled .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-rejected .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-expired .crm-list-row__priority-rail,#{{ $pageId }} .crm-list-row--status-inactive .crm-list-row__priority-rail{background:linear-gradient(180deg,#cbd5e1,#64748b)}
#{{ $pageId }} .crm-list-row:hover,#{{ $pageId }} .crm-list-row:focus-visible{background:var(--crm-surface-sunken);outline:none}
#{{ $pageId }} .crm-list-row:nth-child(even){background:rgba(9,30,66,.018)}
#{{ $pageId }} .crm-list-row .crm-inline-trigger{min-width:0;max-width:100%;min-height:28px;padding:3px 8px!important;border-radius:999px!important;font-size:11px!important;font-weight:600!important}
#{{ $pageId }} .crm-list-row__identity{display:flex;align-items:flex-start;gap:10px;min-width:0}
#{{ $pageId }} .crm-lead-avatar--list{width:36px;height:36px;font-size:12px;flex-shrink:0}
#{{ $pageId }} .crm-list-row__identity-copy{display:grid;gap:4px;min-width:0}
#{{ $pageId }} .crm-list-row__name-row{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-width:0}
#{{ $pageId }} .crm-list-row__name{font-size:13px;font-weight:700;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
#{{ $pageId }} .crm-list-row__id{font-size:10px;font-weight:700;color:var(--crm-text-muted);letter-spacing:.02em}
#{{ $pageId }} .crm-list-row__contact-line{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px;min-width:0}
#{{ $pageId }} .crm-list-row__contact{display:inline-flex;align-items:center;gap:4px;min-width:0;color:var(--crm-text-muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#{{ $pageId }} .crm-list-row__contact iconify-icon{font-size:13px;flex-shrink:0}
#{{ $pageId }} .crm-list-row__field{display:flex;align-items:center;min-width:0}
#{{ $pageId }} .crm-list-row__field .crm-inline-control{width:100%;max-width:100%}
#{{ $pageId }} .crm-list-row__money{font-size:12px;font-weight:700;color:var(--crm-text);font-variant-numeric:tabular-nums}
#{{ $pageId }} .crm-list-row__money--muted{font-weight:600;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-list-row__progress{display:grid;gap:4px;width:100%;max-width:120px}
#{{ $pageId }} .crm-list-row__progress-bar{height:8px;border-radius:999px;background:var(--crm-border);overflow:hidden}
#{{ $pageId }} .crm-list-row__progress-bar span{display:block;height:100%;background:linear-gradient(90deg,var(--crm-brand),rgba(197,168,109,.85));border-radius:inherit}
#{{ $pageId }} .crm-list-row__progress-label{font-size:10px;font-weight:600;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-list-row__assignee{display:inline-flex;align-items:center;gap:5px;min-width:0;color:var(--crm-text-muted);font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#{{ $pageId }} .crm-list-row__field--date{display:flex;flex-direction:column;align-items:flex-start;gap:1px}
#{{ $pageId }} .crm-list-row__date{font-size:12px;font-weight:600;color:var(--crm-text)}
#{{ $pageId }} .crm-list-row__date-sub{font-size:10px;color:var(--crm-text-muted)}
#{{ $pageId }} .crm-list-row__empty{color:var(--crm-text-muted);font-size:12px}
#{{ $pageId }} .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:4px}
#{{ $pageId }} .crm-list-row__action-group{display:flex;align-items:center;gap:2px;opacity:0;transition:opacity .12s ease}
#{{ $pageId }} .crm-list-row:hover .crm-list-row__action-group,#{{ $pageId }} .crm-list-row:focus-within .crm-list-row__action-group{opacity:1}
#{{ $pageId }} .crm-list-row__chevron{display:grid;place-items:center;width:24px;height:24px;padding:0;border:0;border-radius:8px;background:transparent;color:var(--crm-text-muted);font-size:16px;cursor:pointer;transition:background .1s ease,color .1s ease}
#{{ $pageId }} .crm-list-row__chevron:hover{background:var(--crm-brand-soft);color:var(--crm-brand)}
#{{ $pageId }} .crm-list-action{width:28px;height:28px;border:0;border-radius:8px;display:grid;place-items:center;background:transparent;color:var(--crm-text-muted);text-decoration:none;transition:background .1s ease,color .1s ease}
#{{ $pageId }} .crm-list-action:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
#{{ $pageId }} .crm-list-action.is-delete:hover{background:#FFEBE6;color:#BF2600}
#{{ $pageId }} .crm-leads-list-empty{display:grid;place-items:center;gap:6px;padding:48px 16px;color:var(--crm-text-muted);text-align:center}
#{{ $pageId }} .crm-list-shell + .crm-leads-pagination{margin:-1px 16px 16px;border:1px solid var(--crm-border);border-top:0;border-radius:0 0 14px 14px;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#{{ $pageId }} .crm-leads-pagination{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px 18px;margin:0;padding:14px 18px;border-top:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#{{ $pageId }} .crm-leads-pagination__meta{display:flex;flex-wrap:wrap;align-items:center;gap:12px 18px}
#{{ $pageId }} .crm-leads-pagination__summary{display:flex;flex-wrap:wrap;align-items:baseline;gap:4px 6px;color:var(--crm-text-muted);font-size:13px}
#{{ $pageId }} .crm-leads-pagination__summary strong{color:var(--crm-text);font-weight:700;font-variant-numeric:tabular-nums}
#{{ $pageId }} .crm-leads-pagination__per-page{display:inline-flex;align-items:center;gap:8px}
#{{ $pageId }} .crm-leads-pagination__per-page-select{height:32px;padding:0 28px 0 10px;border:1px solid var(--crm-border);border-radius:8px;font-size:12px;background:var(--crm-surface)}
#{{ $pageId }} .crm-leads-pagination__nav{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
#{{ $pageId }} .crm-page-btn{display:inline-flex;align-items:center;gap:5px;min-height:32px;padding:0 10px;border:1px solid var(--crm-border);border-radius:8px;background:var(--surface,var(--crm-surface));color:var(--crm-text-muted);font-size:12px;font-weight:600;text-decoration:none}
#{{ $pageId }} .crm-page-btn:hover:not(.is-disabled){border-color:rgba(15,39,74,.2);color:var(--crm-brand);background:var(--crm-brand-soft)}
#{{ $pageId }} .crm-page-btn.is-disabled{opacity:.45;cursor:not-allowed}
#{{ $pageId }} .crm-page-num{display:grid;place-items:center;min-width:32px;height:32px;padding:0 6px;border:1px solid transparent;border-radius:8px;color:var(--crm-text-muted);font-size:12px;font-weight:600;text-decoration:none}
#{{ $pageId }} .crm-page-num.is-active{background:var(--crm-brand);border-color:var(--crm-brand);color:#fff}
#{{ $pageId }} .crm-page-gap{display:grid;place-items:center;min-width:24px;color:var(--crm-text-muted);font-size:12px;font-weight:700}
@media (max-width:991px){
    #{{ $pageId }} .crm-module-finder__status-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    #{{ $pageId }} .crm-leads-table__head{display:none}
    #{{ $pageId }} .crm-list-row{grid-template-columns:1fr 24px;gap:8px;padding:12px}
    #{{ $pageId }} .crm-list-row__identity{grid-column:1;grid-row:1}
    #{{ $pageId }} .crm-list-row__field{grid-column:1;display:inline-flex;margin-top:6px}
    #{{ $pageId }} .crm-list-row__actions{grid-column:2;grid-row:1 / span 2;align-self:center}
    #{{ $pageId }} .crm-list-row__action-group{opacity:1}
}
</style>
