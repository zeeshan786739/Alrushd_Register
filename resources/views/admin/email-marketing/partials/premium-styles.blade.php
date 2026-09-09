<style>
#em-workspace-page,.em-workspace-page{font-family:var(--crm-font);color:var(--crm-text)}
#em-workspace-page .em-workspace,.em-workspace-page .em-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none;margin-bottom:16px}
#em-workspace-page .crm-metrics-strip,.em-workspace-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#em-workspace-page .crm-metrics-strip__items,.em-workspace-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#em-workspace-page .crm-metrics-strip__item,.em-workspace-page .crm-metrics-strip__item{display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#em-workspace-page .crm-metrics-strip__label,.em-workspace-page .crm-metrics-strip__label{font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#em-workspace-page .crm-metrics-strip__item strong,.em-workspace-page .crm-metrics-strip__item strong{font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums}
#em-workspace-page .crm-metrics-strip__sep,.em-workspace-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#em-workspace-page .crm-metrics-strip__hint,.em-workspace-page .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}
#em-workspace-page .em-tabs-workspace,.em-workspace-page .em-tabs-workspace{padding:16px 16px 16px}
#em-workspace-page .em-tabs-workspace__head,.em-workspace-page .em-tabs-workspace__head{margin-bottom:10px}
#em-workspace-page .em-tabs-workspace__title,.em-workspace-page .em-tabs-workspace__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#em-workspace-page .em-tabs-workspace__sub,.em-workspace-page .em-tabs-workspace__sub{margin:2px 0 0;font-size:12px;color:var(--crm-text-muted)}
#em-workspace-page .em-module-nav,.em-workspace-page .em-module-nav{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}
#em-workspace-page .em-module-nav .crm-source-card,.em-workspace-page .em-module-nav .crm-source-card{
    display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;
    transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease;
}
#em-workspace-page .em-module-nav .crm-source-card:hover,.em-workspace-page .em-module-nav .crm-source-card:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px);color:var(--crm-brand)}
#em-workspace-page .em-module-nav .crm-source-card.is-active,.em-workspace-page .em-module-nav .crm-source-card.is-active{
    border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));
    box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand);
}
#em-workspace-page .em-module-nav .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card__icon{
    display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken);color:var(--crm-text-muted);
}
#em-workspace-page .em-module-nav .crm-source-card__icon iconify-icon,.em-workspace-page .em-module-nav .crm-source-card__icon iconify-icon{font-size:20px;color:inherit;--iconify-color:currentColor}
#em-workspace-page .em-module-nav .crm-source-card__label,.em-workspace-page .em-module-nav .crm-source-card__label{font-size:12px;font-weight:700;line-height:1.2}
#em-workspace-page .em-module-nav .crm-source-card__count,.em-workspace-page .em-module-nav .crm-source-card__count{font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;background:rgba(15,39,74,.08);color:var(--crm-text)}
#em-workspace-page .em-module-nav .crm-source-card.is-active .crm-source-card__count,.em-workspace-page .em-module-nav .crm-source-card.is-active .crm-source-card__count{background:var(--crm-brand);color:#fff}
#em-workspace-page .em-module-nav .crm-source-card--overview .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--overview .crm-source-card__icon{background:rgba(15,39,74,.08);color:var(--crm-brand)}
#em-workspace-page .em-module-nav .crm-source-card--overview.is-active .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--overview.is-active .crm-source-card__icon{background:var(--crm-brand);color:#fff}
#em-workspace-page .em-module-nav .crm-source-card--inbox .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--inbox .crm-source-card__icon{background:rgba(8,145,178,.12);color:#0e7490}
#em-workspace-page .em-module-nav .crm-source-card--inbox.is-active .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--inbox.is-active .crm-source-card__icon{background:#0891b2;color:#fff}
#em-workspace-page .em-module-nav .crm-source-card--campaigns .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--campaigns .crm-source-card__icon{background:rgba(124,58,237,.12);color:#6d28d9}
#em-workspace-page .em-module-nav .crm-source-card--campaigns.is-active .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--campaigns.is-active .crm-source-card__icon{background:#7c3aed;color:#fff}
#em-workspace-page .em-module-nav .crm-source-card--templates .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--templates .crm-source-card__icon{background:rgba(197,168,109,.18);color:#9a7b42}
#em-workspace-page .em-module-nav .crm-source-card--templates.is-active .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--templates.is-active .crm-source-card__icon{background:linear-gradient(135deg,#0f274a,#c5a86d);color:#fff}
#em-workspace-page .em-module-nav .crm-source-card--settings .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--settings .crm-source-card__icon{background:rgba(100,116,139,.12);color:#475569}
#em-workspace-page .em-module-nav .crm-source-card--settings.is-active .crm-source-card__icon,.em-workspace-page .em-module-nav .crm-source-card--settings.is-active .crm-source-card__icon{background:#475569;color:#fff}
#em-workspace-page .em-setup-banner,.em-workspace-page .em-setup-banner{
    display:flex;align-items:center;gap:14px;margin:0 16px 16px;padding:14px 16px;border:1px solid rgba(217,119,6,.25);
    border-radius:14px;background:linear-gradient(135deg,rgba(217,119,6,.08),rgba(255,247,214,.45));
}
#em-workspace-page .em-setup-banner__icon,.em-workspace-page .em-setup-banner__icon{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;background:rgba(217,119,6,.14);color:#b45309;font-size:20px;flex-shrink:0}
#em-workspace-page .em-setup-banner__body,.em-workspace-page .em-setup-banner__body{flex:1;min-width:0}
#em-workspace-page .em-setup-banner__body strong,.em-workspace-page .em-setup-banner__body strong{display:block;font-size:14px;color:var(--crm-text)}
#em-workspace-page .em-setup-banner__body p,.em-workspace-page .em-setup-banner__body p{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#em-workspace-page .em-quick-grid,.em-workspace-page .em-quick-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0 16px 16px}
#em-workspace-page .em-quick-card,.em-workspace-page .em-quick-card{
    display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);text-decoration:none;color:inherit;box-shadow:0 8px 24px rgba(15,39,74,.04);
    transition:border-color .12s ease,transform .12s ease,box-shadow .12s ease;
}
#em-workspace-page .em-quick-card:hover,.em-workspace-page .em-quick-card:hover{border-color:rgba(69,105,230,.35);transform:translateY(-1px);box-shadow:0 10px 28px rgba(15,39,74,.08);color:var(--crm-brand)}
#em-workspace-page .em-quick-card__icon,.em-workspace-page .em-quick-card__icon{width:38px;height:38px;border-radius:10px;display:grid;place-items:center;font-size:18px;flex-shrink:0;background:var(--crm-brand-soft);color:var(--crm-brand)}
#em-workspace-page .em-quick-card__text,.em-workspace-page .em-quick-card__text{display:grid;gap:2px;min-width:0}
#em-workspace-page .em-quick-card__text strong,.em-workspace-page .em-quick-card__text strong{font-size:13px;color:var(--crm-text)}
#em-workspace-page .em-quick-card__text small,.em-workspace-page .em-quick-card__text small{font-size:11px;color:var(--crm-text-muted)}
#em-workspace-page .em-filter-workspace,.em-workspace-page .em-filter-workspace{margin:0 16px 16px;padding:14px 16px;border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04)}
#em-workspace-page .em-filter-workspace__head,.em-workspace-page .em-filter-workspace__head{margin-bottom:12px}
#em-workspace-page .em-filter-workspace__title,.em-workspace-page .em-filter-workspace__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#em-workspace-page .em-filter-workspace__sub,.em-workspace-page .em-filter-workspace__sub{margin:2px 0 0;font-size:12px;color:var(--crm-text-muted)}
#em-workspace-page .em-audience-grid,.em-workspace-page .em-audience-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
#em-workspace-page .em-audience-card,.em-workspace-page .em-audience-card{
    display:grid;justify-items:center;gap:6px;padding:14px 10px;border:2px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface-sunken);text-align:center;
}
#em-workspace-page .em-audience-card__icon,.em-workspace-page .em-audience-card__icon{width:36px;height:36px;border-radius:12px;display:grid;place-items:center;font-size:18px}
#em-workspace-page .em-audience-card__icon--leads,.em-workspace-page .em-audience-card__icon--leads{background:rgba(69,105,230,.12);color:var(--crm-brand)}
#em-workspace-page .em-audience-card__icon--customers,.em-workspace-page .em-audience-card__icon--customers{background:rgba(22,163,74,.12);color:#16a34a}
#em-workspace-page .em-audience-card__icon--forms,.em-workspace-page .em-audience-card__icon--forms{background:rgba(8,145,178,.12);color:#0891b2}
#em-workspace-page .em-audience-card__icon--integrations,.em-workspace-page .em-audience-card__icon--integrations{background:rgba(124,58,237,.12);color:#7c3aed}
#em-workspace-page .em-audience-card__count,.em-workspace-page .em-audience-card__count{font-size:20px;font-weight:800;color:var(--crm-text);line-height:1}
#em-workspace-page .em-audience-card__label,.em-workspace-page .em-audience-card__label{font-size:11px;font-weight:700;color:var(--crm-text-muted)}
#em-workspace-page .em-audience-card__sub,.em-workspace-page .em-audience-card__sub{font-size:10px;color:var(--crm-text-muted)}
#em-workspace-page .em-layout,.em-workspace-page .em-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,340px);gap:16px;margin:0 16px 24px;align-items:start}
#em-workspace-page .em-layout__main,.em-workspace-page .em-layout__main,#em-workspace-page .em-layout__side,.em-workspace-page .em-layout__side{display:grid;gap:16px;min-width:0}
#em-workspace-page .em-panel,.em-workspace-page .em-panel{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#em-workspace-page .em-panel__head,.em-workspace-page .em-panel__head{
    display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:10px;
    padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));
}
#em-workspace-page .em-panel__title,.em-workspace-page .em-panel__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#em-workspace-page .em-panel__desc,.em-workspace-page .em-panel__desc{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#em-workspace-page .em-panel__meta,.em-workspace-page .em-panel__meta{font-size:11px;font-weight:700;color:var(--crm-text-muted);text-transform:uppercase;letter-spacing:.04em}
#em-workspace-page .em-panel__link,.em-workspace-page .em-panel__link{font-size:12px;font-weight:700;color:var(--crm-brand);text-decoration:none}
#em-workspace-page .em-panel__link:hover,.em-workspace-page .em-panel__link:hover{text-decoration:underline}
#em-workspace-page .crm-leads-table,.em-workspace-page .crm-leads-table{border:0;border-radius:0;box-shadow:none;background:transparent}
#em-workspace-page .crm-leads-table__head,.em-workspace-page .crm-leads-table__head,#em-workspace-page .em-list-row,.em-workspace-page .em-list-row{
    display:grid;grid-template-columns:minmax(220px,1.8fr) minmax(100px,.65fr) minmax(80px,.5fr) minmax(80px,.5fr) 96px;
    gap:10px;align-items:center;padding:0 12px 0 14px;text-decoration:none;color:inherit;
}
#em-workspace-page .crm-leads-table__head,.em-workspace-page .crm-leads-table__head{
    position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;
    color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
    background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border);
}
#em-workspace-page .crm-leads-list,.em-workspace-page .crm-leads-list{display:flex;flex-direction:column;gap:0}
#em-workspace-page .em-list-row,.em-workspace-page .em-list-row{position:relative;min-height:0;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);cursor:pointer;transition:background .12s ease;align-items:center}
#em-workspace-page .em-list-row:last-child,.em-workspace-page .em-list-row:last-child{border-bottom:0}
#em-workspace-page .em-list-row:hover,.em-workspace-page .em-list-row:hover{background:var(--crm-surface-sunken)}
#em-workspace-page .em-list-row:nth-child(even),.em-workspace-page .em-list-row:nth-child(even){background:rgba(9,30,66,.018)}
#em-workspace-page .crm-list-row__priority-rail,.em-workspace-page .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0;background:linear-gradient(180deg,#7c3aed,#a78bfa)}
#em-workspace-page .em-list-row--draft .crm-list-row__priority-rail,.em-workspace-page .em-list-row--draft .crm-list-row__priority-rail{background:linear-gradient(180deg,#cbd5e1,#94a3b8)}
#em-workspace-page .em-list-row--scheduled .crm-list-row__priority-rail,.em-workspace-page .em-list-row--scheduled .crm-list-row__priority-rail{background:linear-gradient(180deg,#3b82f6,#2563eb)}
#em-workspace-page .em-list-row--sending .crm-list-row__priority-rail,.em-workspace-page .em-list-row--sending .crm-list-row__priority-rail{background:linear-gradient(180deg,#fb923c,#ea580c)}
#em-workspace-page .em-list-row--sent .crm-list-row__priority-rail,.em-workspace-page .em-list-row--sent .crm-list-row__priority-rail{background:linear-gradient(180deg,#4ade80,#16a34a)}
#em-workspace-page .em-list-row--failed .crm-list-row__priority-rail,.em-workspace-page .em-list-row--failed .crm-list-row__priority-rail{background:linear-gradient(180deg,#f87171,#dc2626)}
#em-workspace-page .em-list-row--cancelled .crm-list-row__priority-rail,.em-workspace-page .em-list-row--cancelled .crm-list-row__priority-rail{background:linear-gradient(180deg,#cbd5e1,#64748b)}
#em-workspace-page .crm-list-row__identity,.em-workspace-page .crm-list-row__identity{display:flex;align-items:flex-start;gap:10px;min-width:0}
#em-workspace-page .em-row-icon,.em-workspace-page .em-row-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;font-size:17px;flex-shrink:0;background:rgba(124,58,237,.12);color:#7c3aed}
#em-workspace-page .crm-list-row__identity-copy,.em-workspace-page .crm-list-row__identity-copy{display:grid;gap:4px;min-width:0}
#em-workspace-page .crm-list-row__name,.em-workspace-page .crm-list-row__name{font-size:13px;font-weight:700;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
#em-workspace-page .crm-list-row__contact,.em-workspace-page .crm-list-row__contact{display:inline-flex;align-items:center;gap:4px;min-width:0;color:var(--crm-text-muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#em-workspace-page .crm-list-row__field,.em-workspace-page .crm-list-row__field{display:flex;align-items:center;flex-wrap:wrap;gap:6px;min-width:0}
#em-workspace-page .crm-list-row__field--metric,.em-workspace-page .crm-list-row__field--metric{display:flex;flex-direction:column;align-items:flex-start;gap:1px}
#em-workspace-page .crm-list-row__date,.em-workspace-page .crm-list-row__date{font-size:12px;font-weight:600;color:var(--crm-text);font-variant-numeric:tabular-nums}
#em-workspace-page .crm-list-row__date-sub,.em-workspace-page .crm-list-row__date-sub{font-size:10px;color:var(--crm-text-muted)}
#em-workspace-page .crm-list-row__actions,.em-workspace-page .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:4px}
#em-workspace-page .crm-list-row__chevron,.em-workspace-page .crm-list-row__chevron{display:grid;place-items:center;width:24px;height:24px;color:var(--crm-text-muted);font-size:16px;text-decoration:none}
#em-workspace-page .em-list-row:hover .crm-list-row__chevron,.em-workspace-page .em-list-row:hover .crm-list-row__chevron{color:var(--crm-brand)}
#em-workspace-page .em-status-pill,.em-workspace-page .em-status-pill{display:inline-flex;align-items:center;min-height:24px;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:capitalize}
#em-workspace-page .em-status-pill--draft,.em-workspace-page .em-status-pill--draft{background:rgba(100,116,139,.12);color:#475569}
#em-workspace-page .em-status-pill--scheduled,.em-workspace-page .em-status-pill--scheduled{background:rgba(59,130,246,.12);color:#2563eb}
#em-workspace-page .em-status-pill--sending,.em-workspace-page .em-status-pill--sending{background:rgba(245,158,11,.12);color:#d97706}
#em-workspace-page .em-status-pill--sent,.em-workspace-page .em-status-pill--sent{background:rgba(22,163,74,.12);color:#16a34a}
#em-workspace-page .em-status-pill--failed,.em-workspace-page .em-status-pill--failed{background:rgba(239,68,68,.12);color:#dc2626}
#em-workspace-page .em-status-pill--cancelled,.em-workspace-page .em-status-pill--cancelled{background:rgba(100,116,139,.12);color:#64748b}
#em-workspace-page .crm-leads-list-empty,.em-workspace-page .crm-leads-list-empty{display:grid;place-items:center;gap:6px;padding:48px 16px;color:var(--crm-text-muted);text-align:center}
#em-workspace-page .crm-leads-list-empty strong,.em-workspace-page .crm-leads-list-empty strong{color:var(--crm-text);font-weight:600}
#em-workspace-page .em-checklist,.em-workspace-page .em-checklist{display:grid;gap:10px;padding:16px}
#em-workspace-page .em-checklist__item,.em-workspace-page .em-checklist__item{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--crm-border);border-radius:12px;background:var(--crm-surface-sunken)}
#em-workspace-page .em-checklist__item.is-done,.em-workspace-page .em-checklist__item.is-done{border-color:rgba(22,163,74,.25);background:rgba(22,163,74,.06)}
#em-workspace-page .em-checklist__item.is-pending,.em-workspace-page .em-checklist__item.is-pending{border-color:rgba(217,119,6,.25);background:rgba(217,119,6,.06)}
#em-workspace-page .em-checklist__icon,.em-workspace-page .em-checklist__icon{font-size:18px;color:var(--crm-text-muted);flex-shrink:0}
#em-workspace-page .em-checklist__item.is-done .em-checklist__icon,.em-workspace-page .em-checklist__item.is-done .em-checklist__icon{color:#16a34a}
#em-workspace-page .em-checklist__item.is-pending .em-checklist__icon,.em-workspace-page .em-checklist__item.is-pending .em-checklist__icon{color:#d97706}
#em-workspace-page .em-checklist__body,.em-workspace-page .em-checklist__body{display:grid;gap:2px;min-width:0}
#em-workspace-page .em-checklist__body strong,.em-workspace-page .em-checklist__body strong{font-size:13px;color:var(--crm-text)}
#em-workspace-page .em-checklist__body span,.em-workspace-page .em-checklist__body span{font-size:11px;color:var(--crm-text-muted)}
#em-workspace-page .em-attention-row,.em-workspace-page .em-attention-row{
    display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-bottom:1px solid var(--crm-border);
    text-decoration:none;color:inherit;transition:background .12s ease;
}
#em-workspace-page .em-attention-row:last-child,.em-workspace-page .em-attention-row:last-child{border-bottom:0}
#em-workspace-page .em-attention-row:hover,.em-workspace-page .em-attention-row:hover{background:var(--crm-surface-sunken)}
#em-workspace-page .em-attention-row iconify-icon,.em-workspace-page .em-attention-row iconify-icon{font-size:18px;flex-shrink:0;margin-top:2px}
#em-workspace-page .em-attention-row--warning iconify-icon,.em-workspace-page .em-attention-row--warning iconify-icon{color:#d97706}
#em-workspace-page .em-attention-row--danger iconify-icon,.em-workspace-page .em-attention-row--danger iconify-icon{color:#dc2626}
#em-workspace-page .em-attention-row span,.em-workspace-page .em-attention-row span{display:grid;gap:2px;min-width:0}
#em-workspace-page .em-attention-row strong,.em-workspace-page .em-attention-row strong{font-size:13px;color:var(--crm-text)}
#em-workspace-page .em-attention-row small,.em-workspace-page .em-attention-row small{font-size:11px;color:var(--crm-text-muted)}
#em-workspace-page .em-hero-status__pill,.em-workspace-page .em-hero-status__pill{
    display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;margin-top:8px;
}
#em-workspace-page .em-hero-status__pill--ok,.em-workspace-page .em-hero-status__pill--ok{background:rgba(22,163,74,.12);color:#15803d}
#em-workspace-page .em-hero-status__pill--warn,.em-workspace-page .em-hero-status__pill--warn{background:rgba(217,119,6,.12);color:#b45309}
#em-workspace-page .crm-metrics-strip--compact,.em-workspace-page .crm-metrics-strip--compact{padding:10px 18px}
#em-workspace-page .crm-metrics-strip--compact .em-hero-status__pill,.em-workspace-page .crm-metrics-strip--compact .em-hero-status__pill{margin-top:0}

/* Filter workspace (Leads-style finder) */
#em-workspace-page .crm-filter-workspace,.em-workspace-page .crm-filter-workspace{padding:14px 18px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#em-workspace-page .em-campaign-finder,#em-workspace-page .em-template-finder,#em-workspace-page .em-inbox-finder,.em-workspace-page .em-campaign-finder,.em-workspace-page .em-template-finder,.em-workspace-page .em-inbox-finder{display:grid;gap:12px}
#em-workspace-page .em-campaign-finder__lookup,#em-workspace-page .em-template-finder__lookup,#em-workspace-page .em-inbox-finder__lookup,.em-workspace-page .em-campaign-finder__lookup,.em-workspace-page .em-template-finder__lookup,.em-workspace-page .em-inbox-finder__lookup{display:grid;gap:10px;padding-bottom:12px;border-bottom:1px solid var(--crm-border)}
#em-workspace-page .em-inbox-finder__mailbox,.em-workspace-page .em-inbox-finder__mailbox{display:grid;gap:6px;max-width:320px}
#em-workspace-page .em-inbox-finder__label,.em-workspace-page .em-inbox-finder__label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#em-workspace-page .crm-ai-search,.em-workspace-page .crm-ai-search{position:relative;display:grid;gap:8px}
#em-workspace-page .crm-ai-search__head,.em-workspace-page .crm-ai-search__head{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px}
#em-workspace-page .crm-ai-search__badge,.em-workspace-page .crm-ai-search__badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;background:linear-gradient(135deg,rgba(15,39,74,.94),rgba(197,168,109,.82));color:#fff;font-size:11px;font-weight:700;letter-spacing:.03em}
#em-workspace-page .crm-ai-search__hint,.em-workspace-page .crm-ai-search__hint{font-size:12px;color:var(--crm-text-muted)}
#em-workspace-page .crm-ai-search__shell,.em-workspace-page .crm-ai-search__shell{padding:5px 5px 5px 10px;min-height:46px;border:1px solid rgba(197,168,109,.35);border-radius:14px;background:var(--crm-surface);box-shadow:0 10px 28px rgba(15,39,74,.07),inset 0 1px 0 rgba(255,255,255,.04)}
#em-workspace-page .crm-ai-search__shell:focus-within,.em-workspace-page .crm-ai-search__shell:focus-within{border-color:rgba(197,168,109,.75);box-shadow:0 12px 32px rgba(15,39,74,.1),0 0 0 3px rgba(197,168,109,.18)}
#em-workspace-page .crm-ai-search__icon,.em-workspace-page .crm-ai-search__icon{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,rgba(15,39,74,.08),rgba(197,168,109,.16));color:var(--crm-brand)}
#em-workspace-page .crm-smart-search__shell,.em-workspace-page .crm-smart-search__shell{display:flex;align-items:center;gap:10px}
#em-workspace-page .crm-smart-search__input,.em-workspace-page .crm-smart-search__input{flex:1;border:0;background:transparent;min-width:0;padding:8px 0;font-size:14px;color:var(--crm-text);outline:none}
#em-workspace-page .crm-smart-search__go,.em-workspace-page .crm-smart-search__go{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:34px;padding:0 14px;border:0;border-radius:10px;background:linear-gradient(135deg,rgba(15,39,74,.96),rgba(197,168,109,.78));color:#fff;font-size:12px;font-weight:600;cursor:pointer;flex-shrink:0}
#em-workspace-page .em-campaign-finder__statuses,.em-workspace-page .em-campaign-finder__statuses{display:grid;gap:10px}
#em-workspace-page .crm-lead-finder__sources-head,.em-workspace-page .crm-lead-finder__sources-head{display:grid;gap:2px}
#em-workspace-page .crm-lead-finder__sources-title,.em-workspace-page .crm-lead-finder__sources-title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#em-workspace-page .crm-lead-finder__sources-sub,.em-workspace-page .crm-lead-finder__sources-sub{margin:0;font-size:12px;color:var(--crm-text-muted)}
#em-workspace-page .em-campaign-finder__status-grid,.em-workspace-page .em-campaign-finder__status-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
#em-workspace-page .em-campaign-finder .crm-source-card,.em-workspace-page .em-campaign-finder .crm-source-card{display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease}
#em-workspace-page .em-campaign-finder .crm-source-card:hover,.em-workspace-page .em-campaign-finder .crm-source-card:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px);color:var(--crm-brand)}
#em-workspace-page .em-campaign-finder .crm-source-card.is-active,.em-workspace-page .em-campaign-finder .crm-source-card.is-active{border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand)}
#em-workspace-page .em-campaign-finder .crm-source-card__icon,.em-workspace-page .em-campaign-finder .crm-source-card__icon{display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken)}
#em-workspace-page .em-campaign-finder .crm-source-card.is-active .crm-source-card__icon,.em-workspace-page .em-campaign-finder .crm-source-card.is-active .crm-source-card__icon{background:var(--crm-brand);color:#fff}
#em-workspace-page .em-campaign-finder .crm-source-card__label,.em-workspace-page .em-campaign-finder .crm-source-card__label{font-size:12px;font-weight:700;line-height:1.2}
#em-workspace-page .crm-lead-avatar--list,.em-workspace-page .crm-lead-avatar--list{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0}
#em-workspace-page .em-inbox-row__avatar,.em-workspace-page .em-inbox-row__avatar{background:linear-gradient(135deg,var(--crm-brand-soft),rgba(197,168,109,.2));color:var(--crm-brand)}
#em-workspace-page .crm-lead-finder__active,.em-workspace-page .crm-lead-finder__active{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:10px 12px;border-radius:12px;background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));border:1px solid var(--crm-border)}
#em-workspace-page .crm-lead-finder__active-label,.em-workspace-page .crm-lead-finder__active-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--crm-text-muted);flex-shrink:0}
#em-workspace-page .crm-lead-finder__active-list,.em-workspace-page .crm-lead-finder__active-list{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-width:0;flex:1}
#em-workspace-page .crm-lead-finder__clear,.em-workspace-page .crm-lead-finder__clear{margin-left:auto;flex-shrink:0;font-size:11px;font-weight:600;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#em-workspace-page .crm-active-filter,.em-workspace-page .crm-active-filter{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border:1px solid var(--crm-border);border-radius:999px;background:var(--crm-surface);color:var(--crm-text-muted);font-size:11px;font-weight:600;text-decoration:none}
#em-workspace-page .crm-active-filter strong,.em-workspace-page .crm-active-filter strong{color:var(--crm-text);font-weight:700}

/* Toolbar + list shell */
#em-workspace-page .crm-leads-toolbar,.em-workspace-page .crm-leads-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:10px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#em-workspace-page .crm-leads-toolbar__left,.em-workspace-page .crm-leads-toolbar__left{display:flex;flex-wrap:wrap;align-items:center;gap:12px;min-width:0}
#em-workspace-page .crm-leads-toolbar__right,.em-workspace-page .crm-leads-toolbar__right{display:flex;align-items:center;gap:8px;flex-shrink:0}
#em-workspace-page .crm-leads-toolbar__meta,.em-workspace-page .crm-leads-toolbar__meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#em-workspace-page .crm-leads-toolbar__meta strong,.em-workspace-page .crm-leads-toolbar__meta strong{color:var(--crm-text);font-weight:600;font-variant-numeric:tabular-nums}
#em-workspace-page .crm-leads-toolbar__filters,.em-workspace-page .crm-leads-toolbar__filters{padding:1px 6px;border-radius:var(--crm-radius-sm);background:var(--crm-surface);font-size:var(--crm-text-xs);font-weight:600;color:var(--crm-text-muted)}
#em-workspace-page .em-list-shell,.em-workspace-page .em-list-shell{padding:0 16px 16px}
#em-workspace-page .em-list-shell .crm-leads-table,.em-workspace-page .em-list-shell .crm-leads-table{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#em-workspace-page .em-list-shell + .crm-leads-pagination,.em-workspace-page .em-list-shell + .crm-leads-pagination{margin:-1px 16px 16px;border:1px solid var(--crm-border);border-top:0;border-radius:0 0 14px 14px;box-shadow:0 8px 24px rgba(15,39,74,.04)}

/* Inbox layout */
#em-workspace-page .em-inbox-layout,.em-workspace-page .em-inbox-layout{display:grid;grid-template-columns:220px minmax(0,1fr);gap:0;border-bottom:1px solid var(--crm-border);min-height:420px}
#em-workspace-page .em-folder-nav,.em-workspace-page .em-folder-nav{padding:16px;border-right:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#em-workspace-page .em-folder-nav__label,.em-workspace-page .em-folder-nav__label{margin:0 0 12px;padding:0 8px;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--crm-text-muted)}
#em-workspace-page .em-folder-nav__link,.em-workspace-page .em-folder-nav__link{display:flex;align-items:center;gap:10px;padding:11px 12px;border-radius:12px;color:var(--crm-text);text-decoration:none;margin-bottom:4px;font-size:13px;font-weight:600;transition:background .12s ease,color .12s ease}
#em-workspace-page .em-folder-nav__link:hover,.em-workspace-page .em-folder-nav__link:hover{background:var(--crm-brand-soft);color:var(--crm-brand)}
#em-workspace-page .em-folder-nav__link.is-active,.em-workspace-page .em-folder-nav__link.is-active{background:var(--crm-brand-soft);color:var(--crm-brand);box-shadow:inset 3px 0 0 var(--crm-brand)}
#em-workspace-page .em-folder-nav__link span:not(.em-folder-nav__badge),.em-workspace-page .em-folder-nav__link span:not(.em-folder-nav__badge){flex:1;min-width:0}
#em-workspace-page .em-folder-nav__badge,.em-workspace-page .em-folder-nav__badge{margin-left:auto;min-width:22px;height:22px;padding:0 6px;border-radius:999px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;background:var(--crm-surface-sunken);color:var(--crm-text-muted)}
#em-workspace-page .em-folder-nav__link.is-active .em-folder-nav__badge,.em-workspace-page .em-folder-nav__link.is-active .em-folder-nav__badge{background:rgba(15,39,74,.12);color:var(--crm-brand)}
#em-workspace-page .em-inbox-main,.em-workspace-page .em-inbox-main{min-width:0;display:flex;flex-direction:column}

/* Inline alerts */
#em-workspace-page .em-inline-alert,.em-workspace-page .em-inline-alert{display:flex;align-items:flex-start;gap:10px;margin:12px 16px 0;padding:12px 14px;border-radius:12px;font-size:13px}
#em-workspace-page .em-inline-alert iconify-icon,.em-workspace-page .em-inline-alert iconify-icon{font-size:18px;flex-shrink:0;margin-top:2px}
#em-workspace-page .em-inline-alert--warning,.em-workspace-page .em-inline-alert--warning{border:1px solid rgba(217,119,6,.25);background:rgba(217,119,6,.08);color:var(--crm-text)}
#em-workspace-page .em-inline-alert--danger,.em-workspace-page .em-inline-alert--danger{border:1px solid rgba(239,68,68,.25);background:rgba(239,68,68,.08);color:var(--crm-text)}
#em-workspace-page .em-inline-alert__link,.em-workspace-page .em-inline-alert__link{margin-left:4px;font-weight:600;color:var(--crm-brand);text-decoration:none}
#em-workspace-page .em-inline-alert__link:hover,.em-workspace-page .em-inline-alert__link:hover{text-decoration:underline}

/* List rows — campaigns index */
#em-workspace-page .crm-leads-table__head--campaigns,#em-workspace-page .em-list-row.em-list-row,.em-workspace-page .crm-leads-table__head--campaigns,.em-workspace-page .em-list-row.em-list-row{display:grid;grid-template-columns:minmax(220px,1.8fr) minmax(100px,.65fr) minmax(80px,.55fr) minmax(70px,.5fr) minmax(70px,.5fr) minmax(70px,.5fr) 40px;gap:10px;align-items:center;padding:0 12px 0 14px}
#em-workspace-page .crm-leads-table__head--templates,#em-workspace-page .em-template-row,.em-workspace-page .crm-leads-table__head--templates,.em-workspace-page .em-template-row{display:grid;grid-template-columns:minmax(220px,1.4fr) minmax(180px,1.4fr) minmax(100px,.7fr) minmax(90px,.65fr) 88px;gap:10px;align-items:center;padding:0 12px 0 14px}
#em-workspace-page .crm-leads-table__head--inbox,#em-workspace-page .em-inbox-row,.em-workspace-page .crm-leads-table__head--inbox,.em-workspace-page .em-inbox-row{display:grid;grid-template-columns:minmax(200px,1.2fr) minmax(240px,2fr) minmax(90px,.6fr) minmax(110px,.75fr) 40px;gap:10px;align-items:center;padding:0 12px 0 14px}
#em-workspace-page .crm-list-row,.em-workspace-page .crm-list-row{position:relative;min-height:0;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);cursor:pointer;transition:background .12s ease;text-decoration:none;color:inherit}
#em-workspace-page .crm-list-row:last-child,.em-workspace-page .crm-list-row:last-child{border-bottom:0}
#em-workspace-page .crm-list-row:hover,#em-workspace-page .crm-list-row:focus-visible,.em-workspace-page .crm-list-row:hover,.em-workspace-page .crm-list-row:focus-visible{background:var(--crm-surface-sunken);outline:none}
#em-workspace-page .crm-list-row:nth-child(even),.em-workspace-page .crm-list-row:nth-child(even){background:rgba(9,30,66,.018)}
#em-workspace-page .em-inbox-row.is-unread .crm-list-row__name,.em-workspace-page .em-inbox-row.is-unread .crm-list-row__name{color:var(--crm-brand);font-weight:800}
#em-workspace-page .em-inbox-row__unread-badge,.em-workspace-page .em-inbox-row__unread-badge{display:inline-flex;align-items:center;padding:1px 7px;border-radius:999px;background:rgba(15,39,74,.08);color:var(--crm-brand);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.03em}
#em-workspace-page .em-inbox-row__subject,.em-workspace-page .em-inbox-row__subject{display:block;font-size:13px;font-weight:600;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#em-workspace-page .em-inbox-row.is-unread .em-inbox-row__subject,.em-workspace-page .em-inbox-row.is-unread .em-inbox-row__subject{font-weight:700}
#em-workspace-page .em-inbox-row__preview,.em-workspace-page .em-inbox-row__preview{display:block;font-size:11px;color:var(--crm-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
#em-workspace-page .em-inbox-row__indicators,.em-workspace-page .em-inbox-row__indicators{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
#em-workspace-page .em-inbox-row__icon,.em-workspace-page .em-inbox-row__icon{font-size:16px;color:var(--crm-text-muted)}
#em-workspace-page .em-inbox-row__icon--star,.em-workspace-page .em-inbox-row__icon--star{color:#d97706}
#em-workspace-page .crm-list-row__action-btn,.em-workspace-page .crm-list-row__action-btn{display:grid;place-items:center;width:28px;height:28px;border-radius:8px;color:var(--crm-text-muted);text-decoration:none;transition:background .1s ease,color .1s ease}
#em-workspace-page .crm-list-row__action-btn:hover,.em-workspace-page .crm-list-row__action-btn:hover{background:var(--crm-surface-sunken);color:var(--crm-brand)}
#em-workspace-page .em-template-row.is-active .crm-list-row__priority-rail,.em-workspace-page .em-template-row.is-active .crm-list-row__priority-rail{background:linear-gradient(180deg,#4ade80,#16a34a)}
#em-workspace-page .em-template-row.is-inactive .crm-list-row__priority-rail,.em-workspace-page .em-template-row.is-inactive .crm-list-row__priority-rail{background:linear-gradient(180deg,#cbd5e1,#94a3b8)}

/* Pagination */
#em-workspace-page .crm-leads-pagination,.em-workspace-page .crm-leads-pagination{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px 18px;margin:0;padding:14px 18px;border-top:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#em-workspace-page .crm-leads-pagination__summary,.em-workspace-page .crm-leads-pagination__summary{display:flex;flex-wrap:wrap;align-items:baseline;gap:4px 6px;color:var(--crm-text-muted);font-size:13px}
#em-workspace-page .crm-leads-pagination__summary strong,.em-workspace-page .crm-leads-pagination__summary strong{color:var(--crm-text);font-weight:700}
#em-workspace-page .crm-page-btn,.em-workspace-page .crm-page-btn{display:inline-flex;align-items:center;gap:5px;min-height:32px;padding:0 10px;border:1px solid var(--crm-border);border-radius:8px;background:var(--crm-surface);color:var(--crm-text-muted);font-size:12px;font-weight:600;text-decoration:none}
#em-workspace-page .crm-page-num.is-active,.em-workspace-page .crm-page-num.is-active{background:var(--crm-brand);border-color:var(--crm-brand);color:#fff}

/* Settings workspace */
#em-workspace-page .em-settings-workspace,.em-workspace-page .em-settings-workspace{display:grid;gap:16px;padding:0 16px 16px}
#em-workspace-page .em-settings-card,.em-workspace-page .em-settings-card{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#em-workspace-page .em-settings-card__head,.em-workspace-page .em-settings-card__head{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:10px;padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#em-workspace-page .em-settings-card__title,.em-workspace-page .em-settings-card__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#em-workspace-page .em-settings-card__sub,.em-workspace-page .em-settings-card__sub{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#em-workspace-page .em-settings-card__body,.em-workspace-page .em-settings-card__body{padding:16px 18px}
#em-workspace-page .em-settings-footer,.em-workspace-page .em-settings-footer{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;padding:12px 16px;border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04)}
#em-workspace-page .em-settings-footer__hint,.em-workspace-page .em-settings-footer__hint{margin:0;font-size:12px;color:var(--crm-text-muted)}
#em-workspace-page .em-form-block,.em-workspace-page .em-form-block{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface-sunken);overflow:hidden;margin-bottom:12px}
#em-workspace-page .em-form-block__summary,.em-workspace-page .em-form-block__summary{display:flex;align-items:center;gap:10px;padding:14px 16px;cursor:pointer;list-style:none}
#em-workspace-page .em-form-block__collapse-body,.em-workspace-page .em-form-block__collapse-body{padding:0 16px 16px}
#em-workspace-page .em-form-block__head,.em-workspace-page .em-form-block__head{display:flex;align-items:flex-start;gap:12px}
#em-workspace-page .em-form-block__icon,.em-workspace-page .em-form-block__icon{display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:rgba(15,39,74,.08);color:var(--crm-brand);flex-shrink:0}
#em-workspace-page .em-form-block__title,.em-workspace-page .em-form-block__title{display:block;font-size:14px;font-weight:700;color:var(--crm-text)}
#em-workspace-page .em-form-block__desc,.em-workspace-page .em-form-block__desc{display:block;font-size:12px;color:var(--crm-text-muted)}

@media(max-width:1200px){
    #em-workspace-page .em-module-nav,.em-workspace-page .em-module-nav{grid-template-columns:repeat(3,minmax(0,1fr))}
    #em-workspace-page .em-quick-grid,.em-workspace-page .em-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    #em-workspace-page .em-audience-grid,.em-workspace-page .em-audience-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:992px){
    #em-workspace-page .em-layout,.em-workspace-page .em-layout{grid-template-columns:1fr}
    #em-workspace-page .em-module-nav,.em-workspace-page .em-module-nav{grid-template-columns:repeat(2,minmax(0,1fr))}
    #em-workspace-page .em-inbox-layout,.em-workspace-page .em-inbox-layout{grid-template-columns:1fr}
    #em-workspace-page .em-folder-nav,.em-workspace-page .em-folder-nav{border-right:0;border-bottom:1px solid var(--crm-border)}
    #em-workspace-page .em-campaign-finder__status-grid,.em-workspace-page .em-campaign-finder__status-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:768px){
    #em-workspace-page .crm-leads-table__head,.em-workspace-page .crm-leads-table__head{display:none}
    #em-workspace-page .em-list-row,#em-workspace-page .em-inbox-row,#em-workspace-page .em-template-row,.em-workspace-page .em-list-row,.em-workspace-page .em-inbox-row,.em-workspace-page .em-template-row{grid-template-columns:1fr 24px!important;gap:8px;padding:12px}
    #em-workspace-page .em-list-row .crm-list-row__identity,.em-workspace-page .em-list-row .crm-list-row__identity{grid-column:1;grid-row:1}
    #em-workspace-page .em-list-row .crm-list-row__field,.em-workspace-page .em-list-row .crm-list-row__field{grid-column:1;display:inline-flex;margin-top:6px}
    #em-workspace-page .em-list-row .crm-list-row__actions,.em-workspace-page .em-list-row .crm-list-row__actions{grid-column:2;grid-row:1 / span 2;align-self:center}
    #em-workspace-page .em-quick-grid,.em-workspace-page .em-quick-grid,#em-workspace-page .em-audience-grid,.em-workspace-page .em-audience-grid{grid-template-columns:1fr}
}
</style>
