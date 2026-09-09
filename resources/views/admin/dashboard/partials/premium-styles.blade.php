<style>
#admin-dashboard-page{font-family:var(--crm-font);color:var(--crm-text);max-width:1440px;margin-inline:auto}
#admin-dashboard-page .dash-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}

#admin-dashboard-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#admin-dashboard-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px}
#admin-dashboard-page .crm-metrics-strip__hint{font-size:12px;font-weight:600;color:var(--crm-text-muted);text-transform:uppercase;letter-spacing:.04em}
#admin-dashboard-page .dash-status-pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700}
#admin-dashboard-page .dash-status-pill--muted{background:var(--crm-surface-sunken);color:var(--crm-text-muted)}
#admin-dashboard-page .dash-status-pill--warn{background:#FFF7D6;color:#974F0C}

#admin-dashboard-page .dash-kpi-workspace{padding:14px 18px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#admin-dashboard-page .dash-kpi-workspace__head{display:grid;gap:2px;margin-bottom:10px}
#admin-dashboard-page .dash-kpi-workspace__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#admin-dashboard-page .dash-kpi-workspace__sub{margin:0;font-size:12px;color:var(--crm-text-muted)}
#admin-dashboard-page .dash-kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(148px,1fr));gap:8px}
#admin-dashboard-page .dash-kpi-card{
    display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;
    transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease;
}
#admin-dashboard-page .dash-kpi-card:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px);color:var(--crm-brand)}
#admin-dashboard-page .dash-kpi-card__icon{display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken);font-size:18px}
#admin-dashboard-page .dash-kpi-card:hover .dash-kpi-card__icon{background:var(--crm-brand);color:#fff}
#admin-dashboard-page .dash-kpi-card--navy .dash-kpi-card__icon{background:rgba(15,39,74,.1);color:var(--crm-brand)}
#admin-dashboard-page .dash-kpi-card--gold .dash-kpi-card__icon{background:rgba(197,168,109,.18);color:#9a7b42}
#admin-dashboard-page .dash-kpi-card--purple .dash-kpi-card__icon{background:rgba(124,58,237,.12);color:#7c3aed}
#admin-dashboard-page .dash-kpi-card--cyan .dash-kpi-card__icon{background:rgba(8,145,178,.12);color:#0891b2}
#admin-dashboard-page .dash-kpi-card--amber .dash-kpi-card__icon{background:rgba(217,119,6,.12);color:#d97706}
#admin-dashboard-page .dash-kpi-card--green .dash-kpi-card__icon{background:rgba(22,163,74,.12);color:#16a34a}
#admin-dashboard-page .dash-kpi-card__label{font-size:11px;font-weight:700;line-height:1.2;text-transform:uppercase;letter-spacing:.03em}
#admin-dashboard-page .dash-kpi-card__value{font-size:18px;font-weight:800;font-variant-numeric:tabular-nums;color:var(--crm-text);line-height:1}
#admin-dashboard-page .dash-kpi-card:hover .dash-kpi-card__value{color:var(--crm-brand)}
#admin-dashboard-page .dash-kpi-card__meta{font-size:10px;color:var(--crm-text-muted);line-height:1.3;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

#admin-dashboard-page .dash-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,320px);gap:0;align-items:start}
#admin-dashboard-page .dash-main{min-width:0;border-right:1px solid var(--crm-border)}
#admin-dashboard-page .dash-aside{padding:16px;display:grid;gap:14px;align-content:start;background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}

#admin-dashboard-page .crm-leads-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:10px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#admin-dashboard-page .crm-leads-toolbar__meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#admin-dashboard-page .crm-leads-toolbar__meta strong{display:inline-flex;align-items:center;gap:6px;color:var(--crm-text);font-weight:600}
#admin-dashboard-page .crm-leads-toolbar__meta strong iconify-icon{font-size:16px;color:var(--crm-brand)}
#admin-dashboard-page .crm-leads-toolbar__links{display:flex;flex-wrap:wrap;align-items:center;gap:12px}
#admin-dashboard-page .crm-leads-toolbar__link{font-size:12px;font-weight:600;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#admin-dashboard-page .crm-leads-toolbar__link:hover{text-decoration:underline}
#admin-dashboard-page .dash-list-row.is-inactive{opacity:.75}

#admin-dashboard-page .crm-list-shell{padding:0 16px 16px}
#admin-dashboard-page .crm-list-shell--flush{padding:0}
#admin-dashboard-page .crm-leads-table{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#admin-dashboard-page .crm-leads-table--inset{margin:0 16px 16px}
#admin-dashboard-page .crm-leads-table__head,#admin-dashboard-page .dash-list-row{
    display:grid;grid-template-columns:minmax(240px,2fr) minmax(96px,.62fr) minmax(160px,1fr) 76px;
    gap:10px;align-items:center;padding:0 12px 0 14px;text-decoration:none;color:inherit;
}
#admin-dashboard-page .crm-leads-table__head{
    position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;
    color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
    background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border);
}
#admin-dashboard-page .crm-leads-list{display:flex;flex-direction:column}
#admin-dashboard-page .dash-list-row{position:relative;min-height:68px;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);cursor:pointer;transition:background .12s ease}
#admin-dashboard-page .dash-list-row:last-child{border-bottom:0}
#admin-dashboard-page .dash-list-row:hover{background:var(--crm-surface-sunken)}
#admin-dashboard-page .dash-list-row:nth-child(even){background:rgba(9,30,66,.018)}
#admin-dashboard-page .dash-list-row:nth-child(even):hover{background:var(--crm-surface-sunken)}
#admin-dashboard-page .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0}
#admin-dashboard-page .dash-list-row--danger .crm-list-row__priority-rail{background:linear-gradient(180deg,#f87171,#dc2626)}
#admin-dashboard-page .dash-list-row--warning .crm-list-row__priority-rail{background:linear-gradient(180deg,#fb923c,#ea580c)}
#admin-dashboard-page .dash-list-row--info .crm-list-row__priority-rail,#admin-dashboard-page .dash-list-row--neutral .crm-list-row__priority-rail{background:linear-gradient(180deg,#1a3a5c,var(--crm-brand))}
#admin-dashboard-page .dash-list-row--activity .crm-list-row__priority-rail{background:linear-gradient(180deg,#0891b2,#0e7490)}
#admin-dashboard-page .dash-list-row--lead .crm-list-row__priority-rail{background:linear-gradient(180deg,#4ade80,#16a34a)}
#admin-dashboard-page .dash-list-row--form .crm-list-row__priority-rail{background:linear-gradient(180deg,#7c3aed,#a78bfa)}
#admin-dashboard-page .crm-list-row__identity{display:flex;align-items:flex-start;gap:10px;min-width:0}
#admin-dashboard-page .crm-lead-avatar--list{width:36px;height:36px;font-size:12px;flex-shrink:0;display:grid;place-items:center;border-radius:999px;font-weight:700;background:linear-gradient(135deg,rgba(15,39,74,.08),rgba(197,168,109,.16));color:var(--crm-brand)}
#admin-dashboard-page .crm-list-row__identity-copy{display:grid;gap:4px;min-width:0}
#admin-dashboard-page .crm-list-row__name-row{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-width:0}
#admin-dashboard-page .crm-list-row__name{font-size:13px;font-weight:700;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
#admin-dashboard-page .crm-list-row__contact-line{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px;min-width:0}
#admin-dashboard-page .crm-list-row__contact{display:inline-flex;align-items:center;gap:4px;min-width:0;color:var(--crm-text-muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#admin-dashboard-page .crm-list-row__contact iconify-icon{font-size:13px;flex-shrink:0}
#admin-dashboard-page .crm-list-row__field{display:flex;align-items:center;min-width:0}
#admin-dashboard-page .crm-list-row__field--date{display:flex;flex-direction:column;align-items:flex-start;gap:1px}
#admin-dashboard-page .crm-list-row__date{font-size:12px;font-weight:600;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
#admin-dashboard-page .crm-list-row__date-sub{font-size:10px;color:var(--crm-text-muted)}
#admin-dashboard-page .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:4px}
#admin-dashboard-page .crm-list-row__chevron{display:grid;place-items:center;width:24px;height:24px;color:var(--crm-text-muted);font-size:16px}
#admin-dashboard-page .dash-list-row:hover .crm-list-row__chevron{color:var(--crm-brand)}
#admin-dashboard-page .dash-row-badge{display:inline-flex;align-items:center;min-height:24px;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.03em}
#admin-dashboard-page .dash-row-badge.is-danger{background:#FFEBE6;color:#BF2600}
#admin-dashboard-page .dash-row-badge.is-warning{background:#FFF7D6;color:#974F0C}
#admin-dashboard-page .dash-row-badge.is-info,#admin-dashboard-page .dash-row-badge.is-neutral{background:var(--crm-brand-soft);color:var(--crm-brand)}
#admin-dashboard-page .dash-row-badge.is-live{background:rgba(22,163,74,.12);color:#15803d}
#admin-dashboard-page .dash-row-badge.is-draft{background:rgba(100,116,139,.12);color:#64748b}
#admin-dashboard-page .crm-leads-list-empty{display:grid;place-items:center;gap:6px;padding:48px 16px;color:var(--crm-text-muted);text-align:center}
#admin-dashboard-page .crm-leads-list-empty strong{color:var(--crm-text);font-size:var(--crm-text-base);font-weight:600}

#admin-dashboard-page .dash-quick-workspace{padding:14px 18px 16px;border-bottom:1px solid var(--crm-border)}
#admin-dashboard-page .dash-quick-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:8px}
#admin-dashboard-page .dash-quick-card{
    display:flex;align-items:flex-start;gap:10px;padding:12px;border:1px solid var(--crm-border);border-radius:12px;
    background:var(--crm-surface);text-decoration:none;color:inherit;transition:border-color .12s ease,background .12s ease,transform .12s ease;
}
#admin-dashboard-page .dash-quick-card:hover{border-color:rgba(15,39,74,.2);background:var(--crm-brand-soft);transform:translateY(-1px)}
#admin-dashboard-page .dash-quick-card__icon{display:grid;place-items:center;width:32px;height:32px;border-radius:10px;background:var(--crm-surface-sunken);color:var(--crm-brand);font-size:17px;flex-shrink:0}
#admin-dashboard-page .dash-quick-card:hover .dash-quick-card__icon{background:var(--crm-brand);color:#fff}
#admin-dashboard-page .dash-quick-card__text{display:grid;gap:2px;min-width:0}
#admin-dashboard-page .dash-quick-card__text strong{font-size:12px;font-weight:700;color:var(--crm-text)}
#admin-dashboard-page .dash-quick-card__text small{font-size:11px;color:var(--crm-text-muted);line-height:1.35}

#admin-dashboard-page .dash-side-panel{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#admin-dashboard-page .dash-side-panel__head{padding:12px 14px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#admin-dashboard-page .dash-side-panel__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#admin-dashboard-page .dash-side-panel__sub{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#admin-dashboard-page .dash-setup-progress{height:6px;margin:0 14px 12px;border-radius:999px;background:var(--crm-surface-sunken);overflow:hidden}
#admin-dashboard-page .dash-setup-progress span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--crm-brand),rgba(197,168,109,.85))}
#admin-dashboard-page .dash-setup-done{display:grid;place-items:center;gap:6px;padding:24px 16px;text-align:center;color:var(--crm-text-muted)}
#admin-dashboard-page .dash-setup-done iconify-icon{font-size:32px;color:#16a34a}
#admin-dashboard-page .dash-setup-done strong{color:var(--crm-text)}
#admin-dashboard-page .dash-setup-done p{margin:0;font-size:12px;line-height:1.45}
#admin-dashboard-page .dash-side-list .dash-list-row{grid-template-columns:minmax(0,1fr) 24px;min-height:58px;padding:10px 14px}
#admin-dashboard-page .dash-side-list .crm-list-row__field,#admin-dashboard-page .dash-side-list .crm-list-row__priority-rail{display:none}
#admin-dashboard-page .dash-module-card{display:block;padding:12px 14px;border-bottom:1px solid var(--crm-border);text-decoration:none;color:inherit;transition:background .12s ease}
#admin-dashboard-page .dash-module-card:last-child{border-bottom:0}
#admin-dashboard-page .dash-module-card:hover{background:var(--crm-surface-sunken)}
#admin-dashboard-page .dash-module-card__head{display:flex;align-items:flex-start;gap:10px}
#admin-dashboard-page .dash-module-card__icon{display:grid;place-items:center;width:32px;height:32px;border-radius:10px;background:var(--crm-brand-soft);color:var(--crm-brand);font-size:17px;flex-shrink:0}
#admin-dashboard-page .dash-module-card__head strong{display:block;font-size:13px;color:var(--crm-text)}
#admin-dashboard-page .dash-module-card__head small{display:block;font-size:11px;color:var(--crm-text-muted);margin-top:2px}
#admin-dashboard-page .dash-module-card__stats{display:flex;flex-wrap:wrap;gap:8px 12px;margin-top:10px;padding-top:10px;border-top:1px dashed var(--crm-border)}
#admin-dashboard-page .dash-module-card__stats span{font-size:11px;color:var(--crm-text-muted)}
#admin-dashboard-page .dash-module-card__stats em{font-style:normal;font-weight:800;color:var(--crm-brand);margin-right:4px}
#admin-dashboard-page .dash-account-card{padding:16px;text-align:center}
#admin-dashboard-page .dash-account-card__icon{width:48px;height:48px;margin:0 auto 10px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(135deg,rgba(15,39,74,.08),rgba(197,168,109,.16));color:var(--crm-brand);font-size:24px}
#admin-dashboard-page .dash-account-card h3{margin:0 0 4px;font-size:15px;font-weight:700}
#admin-dashboard-page .dash-account-card p{margin:0 0 12px;font-size:12px;color:var(--crm-text-muted)}
#admin-dashboard-page .dash-account-card__meta{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-bottom:14px}
#admin-dashboard-page .dash-account-card__actions{display:grid;gap:8px}

@media(max-width:1200px){
    #admin-dashboard-page .dash-layout{grid-template-columns:1fr}
    #admin-dashboard-page .dash-main{border-right:0;border-bottom:1px solid var(--crm-border)}
    #admin-dashboard-page .dash-aside{padding:16px}
}
@media(max-width:768px){
    #admin-dashboard-page .crm-leads-table__head{display:none}
    #admin-dashboard-page .dash-list-row{grid-template-columns:1fr 24px;gap:8px;padding:12px}
    #admin-dashboard-page .dash-list-row .crm-list-row__identity{grid-column:1}
    #admin-dashboard-page .dash-list-row .crm-list-row__field{display:none}
    #admin-dashboard-page .dash-list-row .crm-list-row__actions{grid-column:2;align-self:center}
    #admin-dashboard-page .dash-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
</style>
