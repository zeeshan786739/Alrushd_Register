<style>
#integrations-workspace-page{font-family:var(--crm-font);color:var(--crm-text)}
#integrations-workspace-page .int-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
#integrations-workspace-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#integrations-workspace-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#integrations-workspace-page .crm-metrics-strip__item{display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#integrations-workspace-page .crm-metrics-strip__label{font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#integrations-workspace-page .crm-metrics-strip__item strong{font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums}
#integrations-workspace-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#integrations-workspace-page .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}
#integrations-workspace-page .int-tabs-workspace{padding:14px 18px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#integrations-workspace-page .int-tabs-workspace__head{display:grid;gap:2px;margin-bottom:10px}
#integrations-workspace-page .int-tabs-workspace__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#integrations-workspace-page .int-tabs-workspace__sub{margin:0;font-size:12px;color:var(--crm-text-muted)}
#integrations-workspace-page .int-module-nav{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin:0}
#integrations-workspace-page .int-module-nav .crm-source-card{
    display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;
    transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease;
}
#integrations-workspace-page .int-module-nav .crm-source-card:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px);color:var(--crm-brand)}
#integrations-workspace-page .int-module-nav .crm-source-card.is-active{
    border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));
    box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand);
}
#integrations-workspace-page .int-module-nav .crm-source-card__icon{
    display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken);color:var(--crm-text-muted);
}
#integrations-workspace-page .int-module-nav .crm-source-card__icon iconify-icon{font-size:20px;color:inherit;--iconify-color:currentColor}
#integrations-workspace-page .int-module-nav .crm-source-card__label{font-size:12px;font-weight:700;line-height:1.2}
#integrations-workspace-page .int-module-nav .crm-source-card--all .crm-source-card__icon{background:rgba(15,39,74,.08);color:var(--crm-brand)}
#integrations-workspace-page .int-module-nav .crm-source-card--all.is-active .crm-source-card__icon{background:var(--crm-brand);color:#fff}
#integrations-workspace-page .int-module-nav .crm-source-card--facebook .crm-source-card__icon{background:rgba(24,119,242,.12);color:#1877f2}
#integrations-workspace-page .int-module-nav .crm-source-card--facebook.is-active .crm-source-card__icon{background:#1877f2;color:#fff}
#integrations-workspace-page .int-module-nav .crm-source-card--tiktok .crm-source-card__icon{background:rgba(15,23,42,.08);color:#0f172a}
#integrations-workspace-page .int-module-nav .crm-source-card--tiktok.is-active .crm-source-card__icon{background:#0f172a;color:#fff}

#integrations-workspace-page .int-page-body{padding:0 16px 16px}
#integrations-workspace-page .int-layout{display:grid;grid-template-columns:minmax(280px,.85fr) minmax(0,1.35fr);gap:16px;align-items:start}
#integrations-workspace-page .int-layout--single{grid-template-columns:1fr}
#integrations-workspace-page .int-layout__stack{display:grid;gap:16px;min-width:0}
#integrations-workspace-page .int-panel{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#integrations-workspace-page .int-panel__head{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:10px;padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#integrations-workspace-page .int-panel__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#integrations-workspace-page .int-panel__sub{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#integrations-workspace-page .int-panel__body{padding:16px 18px}
#integrations-workspace-page .int-panel__body--flush{padding:0}
#integrations-workspace-page .int-panel__actions{display:flex;flex-wrap:wrap;gap:8px}
#integrations-workspace-page .int-info-note{display:flex;gap:10px;padding:12px 14px;border:1px solid var(--crm-border);border-radius:12px;background:linear-gradient(135deg,rgba(15,39,74,.03),rgba(197,168,109,.05));color:var(--crm-text-muted);font-size:12px;line-height:1.45}
#integrations-workspace-page .int-info-note iconify-icon{flex-shrink:0;font-size:18px;color:var(--crm-brand);margin-top:1px}
#integrations-workspace-page .int-meta-list{display:grid;gap:10px;margin:0;padding:0;list-style:none}
#integrations-workspace-page .int-meta-list li{display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:13px;color:var(--crm-text)}
#integrations-workspace-page .int-status-badge{display:inline-flex;align-items:center;min-height:24px;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:700}
#integrations-workspace-page .int-status-badge--success{background:rgba(22,163,74,.12);color:#15803d}
#integrations-workspace-page .int-status-badge--warning{background:rgba(245,158,11,.12);color:#b45309}
#integrations-workspace-page .int-status-badge--danger{background:rgba(239,68,68,.12);color:#dc2626}
#integrations-workspace-page .int-status-badge--neutral{background:var(--crm-surface-sunken);color:var(--crm-text-muted)}
#integrations-workspace-page .int-platform-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
#integrations-workspace-page .int-platform-card{
    display:grid;gap:12px;padding:16px;border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);
    box-shadow:0 4px 14px rgba(15,39,74,.04);height:100%;
}
#integrations-workspace-page .int-platform-card__head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
#integrations-workspace-page .int-platform-card__identity{display:flex;align-items:center;gap:12px;min-width:0}
#integrations-workspace-page .int-platform-card__icon{display:grid;place-items:center;width:44px;height:44px;border-radius:12px;flex-shrink:0}
#integrations-workspace-page .int-platform-card__icon--facebook{background:rgba(24,119,242,.12);color:#1877f2}
#integrations-workspace-page .int-platform-card__icon--tiktok{background:rgba(15,23,42,.08);color:#0f172a}
#integrations-workspace-page .int-platform-card__icon iconify-icon{font-size:24px}
#integrations-workspace-page .int-platform-card__title{margin:0 0 4px;font-size:14px;font-weight:700;color:var(--crm-text)}
#integrations-workspace-page .int-platform-card__desc{margin:0;font-size:12px;color:var(--crm-text-muted);line-height:1.4}
#integrations-workspace-page .int-platform-card__meta{font-size:12px;color:var(--crm-text-muted)}
#integrations-workspace-page .int-platform-card__meta strong{color:var(--crm-text);font-weight:600}
#integrations-workspace-page .int-steps-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px}
#integrations-workspace-page .int-step-card{padding:14px;border:1px solid var(--crm-border);border-radius:12px;background:var(--crm-surface-sunken)}
#integrations-workspace-page .int-step-card__badges{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-bottom:10px}
#integrations-workspace-page .int-step-card__num{display:inline-flex;align-items:center;min-height:22px;padding:0 8px;border-radius:999px;background:var(--crm-brand-soft);color:var(--crm-brand);font-size:10px;font-weight:800}
#integrations-workspace-page .int-step-card__title{margin:0 0 4px;font-size:13px;font-weight:700;color:var(--crm-text)}
#integrations-workspace-page .int-step-card__text{margin:0;font-size:12px;color:var(--crm-text-muted);line-height:1.4}
#integrations-workspace-page .int-feature-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
#integrations-workspace-page .int-feature-card{padding:14px;border:1px solid var(--crm-border);border-radius:12px;background:var(--crm-surface);height:100%}
#integrations-workspace-page .int-feature-card__icon{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:rgba(15,39,74,.08);color:var(--crm-brand);margin-bottom:10px}
#integrations-workspace-page .int-feature-card__title{margin:0 0 6px;font-size:13px;font-weight:700;color:var(--crm-text)}
#integrations-workspace-page .int-feature-card__text{margin:0;font-size:12px;color:var(--crm-text-muted);line-height:1.4}
#integrations-workspace-page .int-advertiser-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
#integrations-workspace-page .int-advertiser-card{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border:1px solid var(--crm-border);border-radius:12px;background:var(--crm-surface)}
#integrations-workspace-page .int-checklist{display:grid;gap:10px;margin:0;padding:0;list-style:none}
#integrations-workspace-page .int-checklist li{display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--crm-text)}
#integrations-workspace-page .int-checklist iconify-icon{flex-shrink:0;font-size:18px;margin-top:1px}
#integrations-workspace-page .int-checklist .is-done iconify-icon{color:#16a34a}
#integrations-workspace-page .int-checklist .is-pending iconify-icon{color:#dc2626}
#integrations-workspace-page .int-checklist .is-info iconify-icon{color:var(--crm-brand)}

#integrations-workspace-page .crm-leads-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:10px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#integrations-workspace-page .crm-leads-toolbar__meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#integrations-workspace-page .crm-leads-toolbar__meta strong{color:var(--crm-text);font-weight:600}
#integrations-workspace-page .crm-list-shell{padding:0}
#integrations-workspace-page .crm-leads-table{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#integrations-workspace-page .crm-leads-table__head,#integrations-workspace-page .int-list-row{
    display:grid;gap:10px;align-items:center;padding:0 12px 0 14px;text-decoration:none;color:inherit;
}
#integrations-workspace-page .crm-leads-table__head--hub-submissions,#integrations-workspace-page .int-list-row--hub{grid-template-columns:minmax(160px,1fr) minmax(120px,.75fr) minmax(100px,.55fr) minmax(100px,.6fr)}
#integrations-workspace-page .crm-leads-table__head--facebook-submissions,#integrations-workspace-page .int-list-row--facebook{grid-template-columns:minmax(88px,.45fr) minmax(140px,.8fr) minmax(88px,.45fr) minmax(100px,.6fr) 96px}
#integrations-workspace-page .crm-leads-table__head--tiktok-submissions,#integrations-workspace-page .int-list-row--tiktok{grid-template-columns:minmax(160px,1fr) minmax(120px,.75fr) minmax(120px,.75fr) minmax(88px,.45fr) 120px}
#integrations-workspace-page .crm-leads-table__head--tiktok-forms{grid-template-columns:minmax(180px,1.2fr) minmax(88px,.45fr) minmax(88px,.45fr) minmax(110px,.55fr) minmax(110px,.55fr) 96px}
#integrations-workspace-page .int-list-row--tiktok-form{grid-template-columns:minmax(180px,1.2fr) minmax(88px,.45fr) minmax(88px,.45fr) minmax(110px,.55fr) minmax(110px,.55fr) 96px}
#integrations-workspace-page .crm-leads-table__head--mapping{grid-template-columns:minmax(160px,.75fr) minmax(0,2fr) 96px}
#integrations-workspace-page .int-list-row--mapping{grid-template-columns:minmax(160px,.75fr) minmax(0,2fr) 96px;align-items:start;padding-top:12px;padding-bottom:12px}
#integrations-workspace-page .crm-leads-table__head--fields{grid-template-columns:minmax(200px,1fr) minmax(220px,1.2fr)}
#integrations-workspace-page .int-list-row--field{grid-template-columns:minmax(200px,1fr) minmax(220px,1.2fr)}
#integrations-workspace-page .crm-leads-table__head{
    position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;
    color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
    background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border);
}
#integrations-workspace-page .crm-leads-list{display:flex;flex-direction:column;gap:0}
#integrations-workspace-page .crm-leads-list > .int-list-row,#integrations-workspace-page .crm-leads-list > a.int-list-row{
    display:grid;width:100%;margin:0;box-sizing:border-box;
}
#integrations-workspace-page .int-list-row{
    position:relative;min-height:0;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);
    transition:background .12s ease;
}
#integrations-workspace-page .int-list-row:last-child{border-bottom:0}
#integrations-workspace-page .int-list-row:hover{background:var(--crm-surface-sunken)}
#integrations-workspace-page .int-list-row:nth-child(even){background:rgba(9,30,66,.018)}
#integrations-workspace-page .int-list-row:nth-child(even):hover{background:var(--crm-surface-sunken)}
#integrations-workspace-page .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0}
#integrations-workspace-page .int-list-row--facebook .crm-list-row__priority-rail{background:linear-gradient(180deg,#1877f2,#1d4ed8)}
#integrations-workspace-page .int-list-row--tiktok .crm-list-row__priority-rail{background:linear-gradient(180deg,#0f172a,#334155)}
#integrations-workspace-page .int-list-row--success .crm-list-row__priority-rail{background:linear-gradient(180deg,#4ade80,#16a34a)}
#integrations-workspace-page .int-list-row--warning .crm-list-row__priority-rail{background:linear-gradient(180deg,#fb923c,#ea580c)}
#integrations-workspace-page .int-list-row--danger .crm-list-row__priority-rail{background:linear-gradient(180deg,#f87171,#dc2626)}
#integrations-workspace-page .crm-list-row__identity{display:flex;align-items:flex-start;gap:10px;min-width:0}
#integrations-workspace-page .crm-list-row__identity-copy{display:grid;gap:3px;min-width:0}
#integrations-workspace-page .crm-list-row__name{font-size:13px;font-weight:700;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
#integrations-workspace-page .crm-list-row__contact{font-size:11px;color:var(--crm-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#integrations-workspace-page .crm-list-row__field{display:flex;align-items:center;flex-wrap:wrap;gap:6px;min-width:0;font-size:12px;color:var(--crm-text-muted)}
#integrations-workspace-page .crm-list-row__field--date{display:flex;flex-direction:column;align-items:flex-start;gap:1px}
#integrations-workspace-page .crm-list-row__date{font-size:12px;font-weight:600;color:var(--crm-text)}
#integrations-workspace-page .crm-list-row__date-sub{font-size:10px;color:var(--crm-text-muted)}
#integrations-workspace-page .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:wrap}
#integrations-workspace-page .crm-list-row__actions form{margin:0}
#integrations-workspace-page .crm-leads-list-empty{display:grid;place-items:center;gap:6px;padding:48px 16px;color:var(--crm-text-muted);text-align:center}
#integrations-workspace-page .crm-leads-list-empty strong{color:var(--crm-text);font-weight:600}
#integrations-workspace-page .int-mapping-form .row{--bs-gutter-x:8px;--bs-gutter-y:8px;margin:0}
#integrations-workspace-page .int-mapping-form .form-control,#integrations-workspace-page .int-mapping-form .form-select{height:34px;font-size:12px;border-radius:8px}
#integrations-workspace-page .int-mapping-form .form-check-label{font-size:12px}
#integrations-workspace-page .int-inline-form{display:flex;flex-wrap:wrap;align-items:flex-end;gap:8px}
#integrations-workspace-page .int-inline-form .form-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted);margin-bottom:4px}
#integrations-workspace-page .int-inline-form__field{min-width:120px;flex:1 1 140px}
#integrations-workspace-page .int-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
#integrations-workspace-page .int-form-field label{display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:var(--crm-text)}
#integrations-workspace-page .int-form-field .form-control,#integrations-workspace-page .int-form-field .form-select{height:42px;border-radius:10px;font-size:14px}
#integrations-workspace-page .int-form-save-bar{
    display:flex;flex-wrap:wrap;justify-content:flex-end;gap:10px;margin-top:16px;padding:12px 16px;
    border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04);
}
#integrations-workspace-page .int-hero{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;padding:16px 18px}
#integrations-workspace-page .int-hero__identity{display:flex;align-items:flex-start;gap:14px;min-width:0}
#integrations-workspace-page .int-hero__icon{display:grid;place-items:center;width:48px;height:48px;border-radius:14px;background:rgba(15,23,42,.08);flex-shrink:0}
#integrations-workspace-page .int-hero__title{margin:0 0 6px;font-size:16px;font-weight:700;color:var(--crm-text)}
#integrations-workspace-page .int-hero__badges{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:6px}
#integrations-workspace-page .int-hero__text{margin:0;font-size:13px;color:var(--crm-text-muted)}

@media(max-width:1100px){
    #integrations-workspace-page .int-layout{grid-template-columns:1fr}
    #integrations-workspace-page .int-feature-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    #integrations-workspace-page .int-platform-grid{grid-template-columns:1fr}
}
@media(max-width:768px){
    #integrations-workspace-page .int-module-nav{grid-template-columns:1fr}
    #integrations-workspace-page .crm-leads-table__head{display:none}
    #integrations-workspace-page .int-list-row{grid-template-columns:1fr!important;gap:8px;padding:12px}
    #integrations-workspace-page .crm-list-row__actions{justify-content:flex-start}
    #integrations-workspace-page .int-feature-grid,#integrations-workspace-page .int-advertiser-grid,#integrations-workspace-page .int-form-grid{grid-template-columns:1fr}
}
</style>
