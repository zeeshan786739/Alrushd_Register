<style>
#oe-workspace-page{font-family:var(--crm-font);color:var(--crm-text)}
#oe-workspace-page .oe-workspace{margin-bottom:16px;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
#oe-workspace-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#oe-workspace-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#oe-workspace-page .crm-metrics-strip__item{display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#oe-workspace-page .crm-metrics-strip__label{font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#oe-workspace-page .crm-metrics-strip__item strong{font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums}
#oe-workspace-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#oe-workspace-page .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}
#oe-workspace-page .oe-module-nav .crm-source-card{
    display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;
    transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease;
}
#oe-workspace-page .oe-module-nav .crm-source-card:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px);color:var(--crm-brand)}
#oe-workspace-page .oe-module-nav .crm-source-card.is-active{
    border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));
    box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand);
}
#oe-workspace-page .oe-module-nav .crm-source-card__icon{
    display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken);color:var(--crm-text-muted);
}
#oe-workspace-page .oe-module-nav .crm-source-card__icon iconify-icon{font-size:20px;color:inherit;--iconify-color:currentColor}
#oe-workspace-page .oe-module-nav .crm-source-card__label{font-size:12px;font-weight:700;line-height:1.2}
#oe-workspace-page .oe-module-nav .crm-source-card__count{font-size:16px;font-weight:800;font-variant-numeric:tabular-nums;color:var(--crm-text);line-height:1}
#oe-workspace-page .oe-module-nav .crm-source-card.is-active .crm-source-card__count{color:var(--crm-brand)}
#oe-workspace-page .oe-module-nav .crm-source-card--events .crm-source-card__icon{background:rgba(15,39,74,.08);color:var(--crm-brand)}
#oe-workspace-page .oe-module-nav .crm-source-card--events.is-active .crm-source-card__icon{background:var(--crm-brand);color:#fff}
#oe-workspace-page .oe-module-nav .crm-source-card--items .crm-source-card__icon{background:rgba(8,145,178,.12);color:#0e7490}
#oe-workspace-page .oe-module-nav .crm-source-card--items.is-active .crm-source-card__icon{background:#0891b2;color:#fff}
#oe-workspace-page .oe-module-nav .crm-source-card--speakers .crm-source-card__icon{background:rgba(124,58,237,.12);color:#6d28d9}
#oe-workspace-page .oe-module-nav .crm-source-card--speakers.is-active .crm-source-card__icon{background:#7c3aed;color:#fff}
#oe-workspace-page .oe-module-nav .crm-source-card--submissions .crm-source-card__icon{background:rgba(22,163,74,.12);color:#15803d}
#oe-workspace-page .oe-module-nav .crm-source-card--submissions.is-active .crm-source-card__icon{background:#16a34a;color:#fff}
#oe-workspace-page .crm-leads-toolbar{margin:0 16px 12px}
#oe-workspace-page .oe-tabs-workspace{padding:0 16px 16px}
#oe-workspace-page .oe-tabs-workspace__head{margin-bottom:10px}
#oe-workspace-page .oe-tabs-workspace__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#oe-workspace-page .oe-tabs-workspace__sub{margin:2px 0 0;font-size:12px;color:var(--crm-text-muted)}
#oe-workspace-page .oe-module-nav{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
#oe-workspace-page .oe-filter-workspace{margin:0 16px 16px;padding:14px 16px;border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04)}
#oe-workspace-page .oe-filter-grid{display:grid;grid-template-columns:minmax(0,1.4fr) auto;gap:12px;align-items:end}
#oe-workspace-page .oe-filter-field{display:grid;gap:6px}
#oe-workspace-page .oe-filter-field label{font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--crm-text-muted)}
#oe-workspace-page .oe-ai-search{display:grid;gap:8px}
#oe-workspace-page .oe-ai-search__badge{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--crm-text-muted)}
#oe-workspace-page .oe-ai-search__shell{position:relative}
#oe-workspace-page .oe-ai-search__icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--crm-text-muted);font-size:16px;pointer-events:none}
#oe-workspace-page .oe-ai-search__input{width:100%;min-height:42px;padding:0 12px 0 38px;border:1px solid var(--crm-border);border-radius:10px;background:var(--crm-surface-sunken);color:var(--crm-text);font-size:13px}
#oe-workspace-page .oe-ai-search__input:focus{outline:none;border-color:rgba(69,105,230,.45);box-shadow:0 0 0 3px rgba(69,105,230,.12)}
#oe-workspace-page .oe-stat-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin:0 16px 16px}
#oe-workspace-page .oe-stat-card{display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid var(--crm-border);border-radius:12px;background:var(--crm-surface);cursor:pointer;transition:border-color .12s ease,box-shadow .12s ease,background .12s ease;text-align:left}
#oe-workspace-page .oe-stat-card.is-active{border-color:var(--crm-brand);background:var(--crm-brand-soft);box-shadow:0 0 0 3px rgba(69,105,230,.08)}
#oe-workspace-page .oe-stat-card__icon{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;font-size:17px;flex-shrink:0}
#oe-workspace-page .oe-stat-card--all .oe-stat-card__icon{background:rgba(69,105,230,.12);color:var(--crm-brand)}
#oe-workspace-page .oe-stat-card--active .oe-stat-card__icon{background:rgba(22,163,74,.12);color:#16a34a}
#oe-workspace-page .oe-stat-card--inactive .oe-stat-card__icon{background:rgba(239,68,68,.12);color:#dc2626}
#oe-workspace-page .oe-stat-card__label{display:block;font-size:11px;font-weight:600;color:var(--crm-text-muted)}
#oe-workspace-page .oe-stat-card strong{font-size:18px;line-height:1;color:var(--crm-text)}
#oe-workspace-page .crm-list-shell{margin:0 16px 16px}
#oe-workspace-page .crm-leads-table{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#oe-workspace-page .crm-leads-table__head,#oe-workspace-page .oe-list-row{display:grid;gap:10px;align-items:center;padding:0 12px 0 14px;text-decoration:none;color:inherit}
#oe-workspace-page .crm-leads-table__head--events,#oe-workspace-page .oe-list-row--event{grid-template-columns:minmax(220px,1.8fr) minmax(140px,1fr) minmax(90px,.55fr) 96px}
#oe-workspace-page .crm-leads-table__head--items,#oe-workspace-page .oe-list-row--item{grid-template-columns:minmax(140px,.9fr) minmax(180px,1.2fr) minmax(70px,.45fr) minmax(70px,.45fr) minmax(90px,.55fr) 96px}
#oe-workspace-page .crm-leads-table__head--speakers,#oe-workspace-page .oe-list-row--speaker{grid-template-columns:minmax(220px,1.6fr) minmax(140px,1fr) minmax(72px,.55fr) minmax(90px,.55fr) 96px}
#oe-workspace-page .crm-leads-table__head--submissions,#oe-workspace-page .oe-list-row--submission{grid-template-columns:minmax(120px,.75fr) minmax(130px,.85fr) minmax(140px,1fr) minmax(140px,1fr) 96px}
#oe-workspace-page .crm-leads-table__head{position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border)}
#oe-workspace-page .crm-leads-list{display:flex;flex-direction:column;gap:0}
#oe-workspace-page .crm-leads-list > .oe-list-row{display:grid!important;width:100%;margin:0!important;box-sizing:border-box;line-height:normal}
#oe-workspace-page .oe-list-row{position:relative;min-height:0;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);cursor:pointer;transition:background .12s ease;align-items:center}
#oe-workspace-page .crm-leads-list .oe-list-row:hover{background:var(--crm-surface-sunken)!important}
#oe-workspace-page .oe-list-row:last-child{border-bottom:0}
#oe-workspace-page .oe-list-row:nth-child(even){background:rgba(9,30,66,.018)}
#oe-workspace-page .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0;background:linear-gradient(180deg,#1a3a5c,var(--crm-brand))}
#oe-workspace-page .oe-list-row--item .crm-list-row__priority-rail{background:linear-gradient(180deg,#0891b2,#06b6d4)}
#oe-workspace-page .oe-list-row--speaker .crm-list-row__priority-rail{background:linear-gradient(180deg,#7c3aed,#a78bfa)}
#oe-workspace-page .oe-list-row--submission .crm-list-row__priority-rail{background:linear-gradient(180deg,#16a34a,#4ade80)}
#oe-workspace-page .oe-list-row.is-inactive .crm-list-row__priority-rail{background:linear-gradient(180deg,#cbd5e1,#94a3b8)}
#oe-workspace-page .oe-list-row--event .crm-list-row__identity,#oe-workspace-page .oe-list-row--item .crm-list-row__identity,#oe-workspace-page .oe-list-row--speaker .crm-list-row__identity,#oe-workspace-page .oe-list-row--submission .crm-list-row__identity{grid-column:1;grid-row:1}
#oe-workspace-page .oe-list-row--event .crm-list-row__actions,#oe-workspace-page .oe-list-row--item .crm-list-row__actions,#oe-workspace-page .oe-list-row--speaker .crm-list-row__actions,#oe-workspace-page .oe-list-row--submission .crm-list-row__actions{grid-column:-1;grid-row:1}
#oe-workspace-page .crm-list-row__identity{display:flex;align-items:flex-start;gap:10px;min-width:0}
#oe-workspace-page .oe-row-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;font-size:17px;flex-shrink:0;background:var(--crm-brand-soft);color:var(--crm-brand)}
#oe-workspace-page .oe-row-icon--speaker img{width:100%;height:100%;object-fit:cover;border-radius:inherit}
#oe-workspace-page .crm-list-row__identity-copy{display:grid;gap:4px;min-width:0}
#oe-workspace-page .crm-list-row__name-row{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-width:0}
#oe-workspace-page .crm-list-row__name{font-size:13px;font-weight:700;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
#oe-workspace-page .crm-list-row__contact-line{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px;min-width:0}
#oe-workspace-page .crm-list-row__contact{display:inline-flex;align-items:center;gap:4px;min-width:0;color:var(--crm-text-muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#oe-workspace-page .crm-list-row__field{display:flex;align-items:center;flex-wrap:wrap;gap:6px;min-width:0}
#oe-workspace-page .crm-list-row__field--date{display:flex;flex-direction:column;align-items:flex-start;gap:1px}
#oe-workspace-page .crm-list-row__date{font-size:12px;font-weight:600;color:var(--crm-text)}
#oe-workspace-page .crm-list-row__date-sub{font-size:10px;color:var(--crm-text-muted)}
#oe-workspace-page .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:4px}
#oe-workspace-page .crm-list-row__action-group{display:flex;align-items:center;gap:2px;opacity:0;transition:opacity .12s ease}
#oe-workspace-page .oe-list-row:hover .crm-list-row__action-group,#oe-workspace-page .oe-list-row:focus-within .crm-list-row__action-group{opacity:1}
#oe-workspace-page .crm-list-row__chevron{display:grid;place-items:center;width:24px;height:24px;padding:0;border:0;border-radius:8px;background:transparent;color:var(--crm-text-muted);font-size:16px;text-decoration:none}
#oe-workspace-page .oe-list-row:hover .crm-list-row__chevron{color:var(--crm-brand)}
#oe-workspace-page .oe-status-pill{display:inline-flex;align-items:center;min-height:24px;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:700}
#oe-workspace-page .oe-status-pill--active{background:rgba(22,163,74,.12);color:#15803d}
#oe-workspace-page .oe-status-pill--inactive{background:rgba(239,68,68,.12);color:#dc2626}
#oe-workspace-page .crm-list-action{width:28px;height:28px;border:0;border-radius:8px;display:grid;place-items:center;background:transparent;color:var(--crm-text-muted);text-decoration:none}
#oe-workspace-page .crm-list-action:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
#oe-workspace-page .crm-list-action.is-delete:hover{background:#FFEBE6;color:#BF2600}
#oe-workspace-page .crm-leads-list-empty{display:grid;place-items:center;gap:6px;padding:48px 16px;color:var(--crm-text-muted);text-align:center}
#oe-workspace-page .crm-leads-list-empty strong{color:var(--crm-text);font-weight:600}
#oe-workspace-page .oe-form-page{padding:0 16px 24px}
#oe-workspace-page .oe-form-grid{display:grid;gap:16px}
#oe-workspace-page .oe-form-card{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04);overflow:hidden}
#oe-workspace-page .oe-form-card__head{display:flex;align-items:flex-start;gap:12px;padding:16px 18px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#oe-workspace-page .oe-form-card__icon{width:38px;height:38px;border-radius:10px;display:grid;place-items:center;background:var(--crm-brand-soft);color:var(--crm-brand);font-size:18px;flex-shrink:0}
#oe-workspace-page .oe-form-card__title{margin:0;font-size:15px;font-weight:700;color:var(--crm-text)}
#oe-workspace-page .oe-form-card__sub{margin:2px 0 0;font-size:12px;color:var(--crm-text-muted)}
#oe-workspace-page .oe-form-card__body{padding:18px}
#oe-workspace-page .oe-form-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
#oe-workspace-page .oe-form-field{display:grid;gap:6px}
#oe-workspace-page .oe-form-field--full{grid-column:1 / -1}
#oe-workspace-page .oe-form-field__label{font-size:12px;font-weight:600;color:var(--crm-text)}
#oe-workspace-page .oe-form-save-bar{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:10px;padding:14px 16px;border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04)}
#oe-workspace-page .oe-detail-grid{display:grid;gap:16px;padding:0 16px 24px}
#oe-workspace-page .oe-detail-card{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#oe-workspace-page .oe-detail-card__head{padding:14px 18px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#oe-workspace-page .oe-detail-card__head h2{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#oe-workspace-page .oe-detail-card__head p{margin:2px 0 0;font-size:12px;color:var(--crm-text-muted)}
#oe-workspace-page .oe-detail-table{width:100%;border-collapse:collapse}
#oe-workspace-page .oe-detail-table th,#oe-workspace-page .oe-detail-table td{padding:12px 18px;border-bottom:1px solid var(--crm-border);font-size:13px;vertical-align:top}
#oe-workspace-page .oe-detail-table th{width:38%;font-weight:600;color:var(--crm-text-muted);background:rgba(9,30,66,.02)}
#oe-workspace-page .oe-detail-table tr:last-child th,#oe-workspace-page .oe-detail-table tr:last-child td{border-bottom:0}
#oe-workspace-page .oe-image-preview{margin-top:8px;max-width:160px;border:1px solid var(--crm-border);border-radius:10px;padding:4px;background:var(--crm-surface-sunken)}
@media(max-width:992px){
    #oe-workspace-page .oe-module-nav{grid-template-columns:repeat(2,minmax(0,1fr))}
    #oe-workspace-page .oe-stat-grid{grid-template-columns:1fr}
    #oe-workspace-page .oe-filter-grid{grid-template-columns:1fr}
    #oe-workspace-page .oe-form-fields{grid-template-columns:1fr}
}
@media(max-width:768px){
    #oe-workspace-page .crm-leads-table__head{display:none}
    #oe-workspace-page .oe-list-row{grid-template-columns:1fr 24px!important;gap:8px;padding:12px}
    #oe-workspace-page .oe-list-row .crm-list-row__identity{grid-column:1;grid-row:1}
    #oe-workspace-page .oe-list-row .crm-list-row__field{grid-column:1;display:inline-flex;margin-top:6px}
    #oe-workspace-page .oe-list-row .crm-list-row__actions{grid-column:2;grid-row:1 / span 2;align-self:center}
    #oe-workspace-page .oe-list-row .crm-list-row__action-group{opacity:1}
}
</style>
