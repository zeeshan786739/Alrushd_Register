<style>
#crm-customers-page{font-family:var(--crm-font);color:var(--crm-text)}
#crm-customers-page .crm-customers-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}

/* Metrics strip */
#crm-customers-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#crm-customers-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#crm-customers-page .crm-metrics-strip__item{display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#crm-customers-page .crm-metrics-strip__label{font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#crm-customers-page .crm-metrics-strip__item strong{font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums}
#crm-customers-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#crm-customers-page .crm-metrics-strip__links{display:flex;align-items:center;gap:12px}
#crm-customers-page .crm-metrics-strip__link{font-size:var(--crm-text-sm);font-weight:500;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#crm-customers-page .crm-metrics-strip__link:hover{text-decoration:underline;color:var(--crm-brand-hover)}
#crm-customers-page .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}
#crm-customers-page .crm-metrics-strip--links-only .crm-metrics-strip__items{min-height:0}
#crm-customers-page .crm-metrics-strip--links-only{padding:10px 18px}

/* Filter workspace */
#crm-customers-page .crm-filter-workspace{padding:14px 18px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#crm-customers-page .crm-customer-finder{display:grid;gap:12px}
#crm-customers-page .crm-customer-finder__lookup{display:grid;gap:10px;padding-bottom:12px;border-bottom:1px solid var(--crm-border)}
#crm-customers-page .crm-ai-search{position:relative;display:grid;gap:8px}
#crm-customers-page .crm-ai-search__head{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px}
#crm-customers-page .crm-ai-search__badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;background:linear-gradient(135deg,rgba(15,39,74,.94),rgba(197,168,109,.82));color:#fff;font-size:11px;font-weight:700;letter-spacing:.03em}
#crm-customers-page .crm-ai-search__badge iconify-icon{font-size:14px}
#crm-customers-page .crm-ai-search__hint{font-size:12px;color:var(--crm-text-muted)}
#crm-customers-page .crm-ai-search__shell{padding:5px 5px 5px 10px;min-height:46px;border:1px solid rgba(197,168,109,.35);border-radius:14px;background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(197,168,109,.06));box-shadow:0 10px 28px rgba(15,39,74,.07),inset 0 1px 0 rgba(255,255,255,.8)}
#crm-customers-page .crm-ai-search__shell:focus-within{border-color:rgba(197,168,109,.75);box-shadow:0 12px 32px rgba(15,39,74,.1),0 0 0 3px rgba(197,168,109,.18)}
#crm-customers-page .crm-ai-search__icon{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,rgba(15,39,74,.08),rgba(197,168,109,.16));color:var(--crm-brand)}
#crm-customers-page .crm-ai-search__icon iconify-icon{font-size:18px}
#crm-customers-page .crm-ai-search__input{font-size:14px}
#crm-customers-page .crm-ai-search__go{background:linear-gradient(135deg,rgba(15,39,74,.96),rgba(197,168,109,.78))}
#crm-customers-page .crm-ai-search__go:hover{filter:brightness(1.05)}
#crm-customers-page .crm-smart-search__shell{display:flex;align-items:center;gap:10px}
#crm-customers-page .crm-smart-search__input{flex:1;border:0;background:transparent;min-width:0;padding:8px 0;font-size:14px;color:var(--crm-text);outline:none}
#crm-customers-page .crm-smart-search__input::placeholder{color:var(--crm-text-muted)}
#crm-customers-page .crm-smart-search__go{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:34px;padding:0 14px;border:0;border-radius:10px;color:#fff;font-size:12px;font-weight:600;cursor:pointer;flex-shrink:0}
#crm-customers-page .crm-smart-search__go iconify-icon{font-size:16px}
#crm-customers-page .crm-lead-finder__quick-pills{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
#crm-customers-page .crm-quick-pill{display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:6px 14px;border:1px solid var(--crm-border);border-radius:999px;background:var(--crm-surface);color:var(--crm-text-muted);font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;transition:background .1s ease,border-color .1s ease,color .1s ease}
#crm-customers-page .crm-quick-pill:hover{background:var(--crm-brand-soft);border-color:rgba(15,39,74,.16);color:var(--crm-brand)}
#crm-customers-page .crm-quick-pill.is-active{background:var(--crm-brand);border-color:var(--crm-brand);color:#fff}
#crm-customers-page .crm-quick-pill iconify-icon{font-size:15px;flex-shrink:0}
#crm-customers-page .crm-customer-finder__statuses{display:grid;gap:10px}
#crm-customers-page .crm-lead-finder__sources-head{display:grid;gap:2px}
#crm-customers-page .crm-lead-finder__sources-title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text);letter-spacing:-.01em}
#crm-customers-page .crm-lead-finder__sources-sub{margin:0;font-size:12px;color:var(--crm-text-muted)}
#crm-customers-page .crm-customer-finder__status-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
#crm-customers-page .crm-source-card{display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease}
#crm-customers-page .crm-source-card:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px)}
#crm-customers-page .crm-source-card.is-active{border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand)}
#crm-customers-page .crm-source-card.is-empty:not(.is-active){opacity:.72}
#crm-customers-page .crm-source-card__icon{display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken)}
#crm-customers-page .crm-source-card__icon iconify-icon{font-size:20px}
#crm-customers-page .crm-source-card.is-active .crm-source-card__icon{background:var(--crm-brand);color:#fff}
#crm-customers-page .crm-source-card--active .crm-source-card__icon{background:rgba(22,163,74,.12);color:#15803d}
#crm-customers-page .crm-source-card--active.is-active .crm-source-card__icon{background:#16a34a;color:#fff}
#crm-customers-page .crm-source-card--prospect .crm-source-card__icon{background:rgba(245,158,11,.12);color:#b45309}
#crm-customers-page .crm-source-card--prospect.is-active .crm-source-card__icon{background:#f59e0b;color:#fff}
#crm-customers-page .crm-source-card--inactive .crm-source-card__icon{background:rgba(100,116,139,.12);color:#475569}
#crm-customers-page .crm-source-card--inactive.is-active .crm-source-card__icon{background:#64748b;color:#fff}
#crm-customers-page .crm-source-card__label{font-size:12px;font-weight:700;line-height:1.2}
#crm-customers-page .crm-source-card__count{font-size:16px;font-weight:800;font-variant-numeric:tabular-nums;color:var(--crm-text);line-height:1}
#crm-customers-page .crm-source-card.is-active .crm-source-card__count{color:var(--crm-brand)}
#crm-customers-page .crm-lead-finder__more{border:1px dashed var(--crm-border);border-radius:12px;padding:0 12px 12px;background:rgba(15,39,74,.015)}
#crm-customers-page .crm-lead-finder__more-summary{display:flex;align-items:center;gap:8px;padding:10px 0;cursor:pointer;list-style:none;font-size:12px;font-weight:600;color:var(--crm-text-muted)}
#crm-customers-page .crm-lead-finder__more-summary::-webkit-details-marker{display:none}
#crm-customers-page .crm-lead-finder__more[open] .crm-lead-finder__more-summary{color:var(--crm-text)}
#crm-customers-page .crm-lead-finder__refine{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px 12px}
#crm-customers-page .crm-lead-finder__refine-field label{display:block;margin-bottom:4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#crm-customers-page .crm-lead-finder__refine-field .form-select,#crm-customers-page .crm-lead-finder__refine-field .form-control{height:34px;padding:4px 10px;border:1px solid var(--crm-border);border-radius:10px;font-size:12px;color:var(--crm-text);background:var(--crm-surface);box-shadow:none}
#crm-customers-page .crm-lead-finder__refine-field .form-select:focus,#crm-customers-page .crm-lead-finder__refine-field .form-control:focus{border-color:var(--crm-link,var(--crm-brand));box-shadow:0 0 0 3px rgba(197,168,109,.14)}
#crm-customers-page .crm-lead-finder__refine-field--wide{grid-column:1 / -1;display:flex;flex-wrap:wrap;gap:8px}
#crm-customers-page .crm-lead-finder__active{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:10px 12px;border-radius:12px;background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));border:1px solid var(--crm-border)}
#crm-customers-page .crm-lead-finder__active-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--crm-text-muted);flex-shrink:0}
#crm-customers-page .crm-lead-finder__active-list{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-width:0;flex:1}
#crm-customers-page .crm-lead-finder__clear{margin-left:auto;flex-shrink:0;font-size:11px;font-weight:600;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#crm-customers-page .crm-lead-finder__clear:hover{text-decoration:underline}
#crm-customers-page .crm-active-filter{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border:1px solid var(--crm-border);border-radius:999px;background:var(--crm-surface);color:var(--crm-text-muted);font-size:11px;font-weight:600;text-decoration:none}
#crm-customers-page .crm-active-filter strong{color:var(--crm-text);font-weight:700}
#crm-customers-page .crm-active-filter:hover{border-color:rgba(15,39,74,.2);background:var(--crm-brand-soft)}

/* Toolbar */
#crm-customers-page .crm-leads-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:10px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#crm-customers-page .crm-leads-toolbar__left{display:flex;flex-wrap:wrap;align-items:center;gap:12px;min-width:0}
#crm-customers-page .crm-leads-toolbar__meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#crm-customers-page .crm-leads-toolbar__meta strong{color:var(--crm-text);font-weight:600;font-variant-numeric:tabular-nums}
#crm-customers-page .crm-leads-toolbar__filters{padding:1px 6px;border-radius:var(--crm-radius-sm);background:var(--crm-surface-sunken);font-size:var(--crm-text-xs);font-weight:600;color:var(--crm-text-muted)}

/* List table */
#crm-customers-page .crm-customers-list-shell{padding:0 16px 16px}
#crm-customers-page .crm-leads-table{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#crm-customers-page .crm-leads-table__head,#crm-customers-page .crm-list-row{
    display:grid;grid-template-columns:minmax(240px,2.2fr) minmax(108px,.72fr) minmax(124px,.78fr) minmax(96px,.62fr) minmax(88px,.55fr) 76px;
    gap:10px;align-items:center;padding:0 12px 0 14px;
}
#crm-customers-page .crm-leads-table__head{position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border)}
#crm-customers-page .crm-leads-list{display:flex;flex-direction:column}
#crm-customers-page .crm-list-row{position:relative;min-height:72px;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);cursor:pointer;transition:background .12s ease}
#crm-customers-page .crm-list-row:last-child{border-bottom:0}
#crm-customers-page .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0}
#crm-customers-page .crm-list-row--status-active .crm-list-row__priority-rail{background:linear-gradient(180deg,#4ade80,#16a34a)}
#crm-customers-page .crm-list-row--status-prospect .crm-list-row__priority-rail{background:linear-gradient(180deg,#fcd34d,#f59e0b)}
#crm-customers-page .crm-list-row--status-inactive .crm-list-row__priority-rail{background:linear-gradient(180deg,#cbd5e1,#94a3b8)}
#crm-customers-page .crm-list-row:hover,#crm-customers-page .crm-list-row:focus-visible{background:var(--crm-surface-sunken);outline:none}
#crm-customers-page .crm-list-row:nth-child(even){background:rgba(9,30,66,.018)}
#crm-customers-page .crm-list-row:nth-child(even):hover{background:var(--crm-surface-sunken)}
#crm-customers-page .crm-list-row .crm-inline-trigger{min-width:0;max-width:100%;min-height:28px;padding:3px 8px!important;border-radius:999px!important;font-size:11px!important;font-weight:600!important}
#crm-customers-page .crm-list-row .crm-inline-control--owner .crm-inline-trigger__label{max-width:72px}
#crm-customers-page .crm-list-row .crm-inline-trigger__label{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:84px}
#crm-customers-page .crm-list-row__identity{display:flex;align-items:flex-start;gap:10px;min-width:0}
#crm-customers-page .crm-lead-avatar--list{width:36px;height:36px;font-size:12px;flex-shrink:0}
#crm-customers-page .crm-list-row__identity-copy{display:grid;gap:4px;min-width:0}
#crm-customers-page .crm-list-row__name-row{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-width:0}
#crm-customers-page .crm-list-row__name{font-size:13px;font-weight:700;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
#crm-customers-page .crm-list-row__id{font-size:10px;font-weight:700;color:var(--crm-text-muted);letter-spacing:.02em}
#crm-customers-page .crm-list-row__contact-line{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px;min-width:0}
#crm-customers-page .crm-list-row__contact{display:inline-flex;align-items:center;gap:4px;min-width:0;color:var(--crm-text-muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#crm-customers-page .crm-list-row__contact iconify-icon{font-size:13px;flex-shrink:0;color:var(--crm-text-muted)}
#crm-customers-page .crm-list-row__contact--muted{font-style:italic}
#crm-customers-page .crm-list-row__tags{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
#crm-customers-page .crm-list-row__form-name{font-size:10px;font-weight:600;color:var(--crm-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px}
#crm-customers-page .crm-list-row__field{display:flex;align-items:center;min-width:0}
#crm-customers-page .crm-list-row__field .crm-inline-control{width:100%;max-width:100%}
#crm-customers-page .crm-list-row__assignee{display:inline-flex;align-items:center;gap:5px;min-width:0;color:var(--crm-text-muted);font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#crm-customers-page .crm-list-row__assignee iconify-icon{font-size:14px;flex-shrink:0}
#crm-customers-page .crm-list-row__ltv{font-size:12px;font-weight:700;color:var(--crm-text);font-variant-numeric:tabular-nums}
#crm-customers-page .crm-list-row__field--date{display:flex;flex-direction:column;align-items:flex-start;gap:1px}
#crm-customers-page .crm-list-row__date{font-size:12px;font-weight:600;color:var(--crm-text)}
#crm-customers-page .crm-list-row__date-sub{font-size:10px;color:var(--crm-text-muted)}
#crm-customers-page .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:4px}
#crm-customers-page .crm-list-row__action-group{display:flex;align-items:center;gap:2px;opacity:0;transition:opacity .12s ease}
#crm-customers-page .crm-list-row:hover .crm-list-row__action-group,#crm-customers-page .crm-list-row:focus-within .crm-list-row__action-group{opacity:1}
#crm-customers-page .crm-list-row__chevron{display:grid;place-items:center;width:24px;height:24px;color:var(--crm-text-muted);font-size:16px}
#crm-customers-page .crm-list-row:hover .crm-list-row__chevron{color:var(--crm-brand)}
#crm-customers-page .crm-list-action{width:28px;height:28px;border:0;border-radius:8px;display:grid;place-items:center;background:transparent;color:var(--crm-text-muted);text-decoration:none;transition:background .1s ease,color .1s ease}
#crm-customers-page .crm-list-action:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
#crm-customers-page .crm-list-action.is-delete:hover{background:#FFEBE6;color:#BF2600}
#crm-customers-page .crm-leads-list-empty{display:grid;place-items:center;gap:6px;padding:48px 16px;color:var(--crm-text-muted);text-align:center}
#crm-customers-page .crm-leads-list-empty strong{color:var(--crm-text);font-size:var(--crm-text-base);font-weight:600}
#crm-customers-page .crm-customers-list-shell + .crm-leads-pagination{margin:-1px 16px 16px;border:1px solid var(--crm-border);border-top:0;border-radius:0 0 14px 14px;box-shadow:0 8px 24px rgba(15,39,74,.04)}

/* Pagination */
#crm-customers-page .crm-leads-pagination{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px 18px;margin:0;padding:14px 18px;border-top:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#crm-customers-page .crm-leads-pagination__meta{display:flex;flex-wrap:wrap;align-items:center;gap:12px 18px;min-width:0}
#crm-customers-page .crm-leads-pagination__summary{display:flex;flex-wrap:wrap;align-items:baseline;gap:4px 6px;color:var(--crm-text-muted);font-size:13px}
#crm-customers-page .crm-leads-pagination__summary strong{color:var(--crm-text);font-weight:700;font-variant-numeric:tabular-nums}
#crm-customers-page .crm-leads-pagination__per-page{display:inline-flex;align-items:center;gap:8px}
#crm-customers-page .crm-leads-pagination__per-page-label{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:var(--crm-text-muted)}
#crm-customers-page .crm-leads-pagination__per-page-select{height:32px;padding:0 28px 0 10px;border:1px solid var(--crm-border);border-radius:8px;font-size:12px;font-weight:600;color:var(--crm-text);background:var(--crm-surface)}
#crm-customers-page .crm-leads-pagination__nav{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
#crm-customers-page .crm-page-btn{display:inline-flex;align-items:center;gap:5px;min-height:32px;padding:0 10px;border:1px solid var(--crm-border);border-radius:8px;background:var(--crm-surface);color:var(--crm-text-muted);font-size:12px;font-weight:600;text-decoration:none}
#crm-customers-page .crm-page-btn:hover:not(.is-disabled){border-color:rgba(15,39,74,.2);color:var(--crm-brand);background:var(--crm-brand-soft)}
#crm-customers-page .crm-page-btn.is-disabled{opacity:.45;cursor:not-allowed}
#crm-customers-page .crm-page-btn--icon{min-width:32px;padding:0;justify-content:center}
#crm-customers-page .crm-leads-pagination__pages{display:inline-flex;align-items:center;gap:4px}
#crm-customers-page .crm-page-num{display:grid;place-items:center;min-width:32px;height:32px;padding:0 6px;border:1px solid transparent;border-radius:8px;color:var(--crm-text-muted);font-size:12px;font-weight:600;text-decoration:none}
#crm-customers-page .crm-page-num:hover{border-color:var(--crm-border);background:var(--crm-surface);color:var(--crm-brand)}
#crm-customers-page .crm-page-num.is-active{background:var(--crm-brand);border-color:var(--crm-brand);color:#fff}
#crm-customers-page .crm-page-gap{display:grid;place-items:center;min-width:24px;color:var(--crm-text-muted);font-size:12px;font-weight:700}

@media (max-width:991px){
    #crm-customers-page .crm-customer-finder__status-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    #crm-customers-page .crm-leads-toolbar{flex-direction:column;align-items:stretch}
    #crm-customers-page .crm-leads-table__head{display:none}
    #crm-customers-page .crm-list-row{grid-template-columns:1fr 24px;gap:8px;padding:12px}
    #crm-customers-page .crm-list-row__identity{grid-column:1;grid-row:1}
    #crm-customers-page .crm-list-row__field--status,#crm-customers-page .crm-list-row__field--assignee,#crm-customers-page .crm-list-row__field--ltv{grid-column:1;display:inline-flex;margin-top:6px}
    #crm-customers-page .crm-list-row__field--date{display:none}
    #crm-customers-page .crm-list-row__actions{grid-column:2;grid-row:1 / span 2;align-self:center}
    #crm-customers-page .crm-list-row__action-group{opacity:1}
    #crm-customers-page .crm-leads-pagination{flex-direction:column;align-items:stretch;gap:12px}
}
</style>
