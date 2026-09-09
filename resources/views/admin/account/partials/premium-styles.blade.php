<style>
#acct-workspace-page{font-family:var(--crm-font);color:var(--crm-text)}
#acct-workspace-page .acct-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
#acct-workspace-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#acct-workspace-page .crm-metrics-strip--compact{padding:10px 18px}
#acct-workspace-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#acct-workspace-page .crm-metrics-strip__item{display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#acct-workspace-page .crm-metrics-strip__label{font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#acct-workspace-page .crm-metrics-strip__item strong{font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums}
#acct-workspace-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#acct-workspace-page .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}
#acct-workspace-page .acct-hero-status__pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700}
#acct-workspace-page .acct-hero-status__pill--ok{background:rgba(22,163,74,.12);color:#15803d}
#acct-workspace-page .acct-hero-status__pill--warn{background:rgba(217,119,6,.12);color:#b45309}
#acct-workspace-page .acct-hero-status__pill--muted{background:var(--crm-surface-sunken);color:var(--crm-text-muted)}
#acct-workspace-page .acct-tabs-workspace{padding:16px 16px 16px}
#acct-workspace-page .acct-tabs-workspace__head{margin-bottom:10px}
#acct-workspace-page .acct-tabs-workspace__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#acct-workspace-page .acct-tabs-workspace__sub{margin:2px 0 0;font-size:12px;color:var(--crm-text-muted)}
#acct-workspace-page .acct-module-nav{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}
#acct-workspace-page .acct-module-nav .crm-source-card{
    display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;
    transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease;
}
#acct-workspace-page .acct-module-nav .crm-source-card:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px);color:var(--crm-brand)}
#acct-workspace-page .acct-module-nav .crm-source-card.is-active{
    border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));
    box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand);
}
#acct-workspace-page .acct-module-nav .crm-source-card__icon{display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken);color:var(--crm-text-muted)}
#acct-workspace-page .acct-module-nav .crm-source-card__icon iconify-icon{font-size:20px;color:inherit;--iconify-color:currentColor}
#acct-workspace-page .acct-module-nav .crm-source-card__label{font-size:12px;font-weight:700;line-height:1.2}
#acct-workspace-page .acct-module-nav .crm-source-card.is-active .crm-source-card__icon{background:var(--crm-brand);color:#fff}
#acct-workspace-page .acct-module-nav .crm-source-card--overview .crm-source-card__icon{background:rgba(15,39,74,.08);color:var(--crm-brand)}
#acct-workspace-page .acct-module-nav .crm-source-card--profile .crm-source-card__icon{background:rgba(124,58,237,.12);color:#7c3aed}
#acct-workspace-page .acct-module-nav .crm-source-card--security .crm-source-card__icon{background:rgba(217,119,6,.12);color:#d97706}
#acct-workspace-page .acct-module-nav .crm-source-card--payments .crm-source-card__icon{background:rgba(22,163,74,.12);color:#16a34a}
#acct-workspace-page .acct-module-nav .crm-source-card--website .crm-source-card__icon{background:rgba(8,145,178,.12);color:#0891b2}
#acct-workspace-page .acct-module-nav .crm-source-card--billing .crm-source-card__icon{background:rgba(197,168,109,.18);color:#9a7b42}

#acct-workspace-page .acct-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,340px);gap:16px;padding:0 16px 16px;align-items:start}
#acct-workspace-page .acct-layout__main,#acct-workspace-page .acct-layout__side{display:grid;gap:16px;min-width:0}
#acct-workspace-page .acct-panel{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#acct-workspace-page .acct-panel__head{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:10px;padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#acct-workspace-page .acct-panel__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#acct-workspace-page .acct-panel__desc{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#acct-workspace-page .acct-list-shell{padding:0}
#acct-workspace-page .acct-list-shell .crm-leads-table{border:0;border-radius:0;box-shadow:none;background:transparent}
#acct-workspace-page .crm-leads-table__head--quick,#acct-workspace-page .acct-quick-row{display:grid;grid-template-columns:minmax(240px,2fr) minmax(120px,.75fr) 40px;gap:10px;align-items:center;padding:0 12px 0 14px;text-decoration:none;color:inherit}
#acct-workspace-page .crm-leads-table__head--usage,#acct-workspace-page .acct-usage-row{display:grid;grid-template-columns:minmax(280px,1fr) 40px;gap:10px;align-items:center;padding:0 12px 0 14px}
#acct-workspace-page .crm-leads-table__head{position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border)}
#acct-workspace-page .crm-leads-list{display:flex;flex-direction:column}
#acct-workspace-page .acct-quick-row,#acct-workspace-page .acct-usage-row{position:relative;min-height:0;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);cursor:pointer;transition:background .12s ease}
#acct-workspace-page .acct-quick-row:last-child,#acct-workspace-page .acct-usage-row:last-child{border-bottom:0}
#acct-workspace-page .acct-quick-row:hover,#acct-workspace-page .acct-usage-row:hover{background:var(--crm-surface-sunken)}
#acct-workspace-page .acct-quick-row:nth-child(even){background:rgba(9,30,66,.018)}
#acct-workspace-page .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0}
#acct-workspace-page .acct-quick-row--payments .crm-list-row__priority-rail{background:linear-gradient(180deg,#4ade80,#16a34a)}
#acct-workspace-page .acct-quick-row--billing .crm-list-row__priority-rail{background:linear-gradient(180deg,#0f274a,#c5a86d)}
#acct-workspace-page .acct-quick-row--website .crm-list-row__priority-rail{background:linear-gradient(180deg,#22d3ee,#0891b2)}
#acct-workspace-page .acct-quick-row--profile .crm-list-row__priority-rail{background:linear-gradient(180deg,#a78bfa,#7c3aed)}
#acct-workspace-page .acct-quick-row--security .crm-list-row__priority-rail{background:linear-gradient(180deg,#fb923c,#ea580c)}
#acct-workspace-page .acct-usage-row .crm-list-row__priority-rail{background:linear-gradient(180deg,rgba(15,39,74,.5),rgba(197,168,109,.7))}
#acct-workspace-page .acct-row-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;font-size:17px;flex-shrink:0}
#acct-workspace-page .acct-row-icon--payments{background:rgba(22,163,74,.12);color:#16a34a}
#acct-workspace-page .acct-row-icon--billing{background:rgba(15,39,74,.1);color:var(--crm-brand)}
#acct-workspace-page .acct-row-icon--website{background:rgba(8,145,178,.12);color:#0891b2}
#acct-workspace-page .acct-row-icon--profile{background:rgba(124,58,237,.12);color:#7c3aed}
#acct-workspace-page .acct-row-icon--security{background:rgba(217,119,6,.12);color:#d97706}
#acct-workspace-page .acct-row-icon--usage{background:rgba(15,39,74,.08);color:var(--crm-brand)}
#acct-workspace-page .crm-list-row__identity{display:flex;align-items:flex-start;gap:10px;min-width:0}
#acct-workspace-page .crm-list-row__identity-copy{display:grid;gap:4px;min-width:0}
#acct-workspace-page .crm-list-row__name{font-size:13px;font-weight:700;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
#acct-workspace-page .crm-list-row__contact{font-size:11px;color:var(--crm-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#acct-workspace-page .crm-list-row__field{display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:6px;min-width:0}
#acct-workspace-page .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end}
#acct-workspace-page .crm-list-row__chevron{display:grid;place-items:center;width:24px;height:24px;color:var(--crm-text-muted);font-size:16px}
#acct-workspace-page .acct-quick-row:hover .crm-list-row__chevron{color:var(--crm-brand)}
#acct-workspace-page .acct-status-pill{display:inline-flex;align-items:center;min-height:24px;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:700}
#acct-workspace-page .acct-status-pill--ok{background:rgba(22,163,74,.12);color:#15803d}
#acct-workspace-page .acct-status-pill--warn{background:rgba(217,119,6,.12);color:#b45309}
#acct-workspace-page .acct-status-pill--muted{background:var(--crm-brand-soft);color:var(--crm-brand)}

#acct-workspace-page .acct-profile-card{padding:20px 18px;text-align:center;border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04)}
#acct-workspace-page .acct-profile-card__avatar{width:72px;height:72px;border-radius:16px;margin:0 auto 12px;display:grid;place-items:center;background:linear-gradient(135deg,var(--crm-brand-soft),rgba(197,168,109,.15));color:var(--crm-brand);font-size:22px;font-weight:800}
#acct-workspace-page .acct-profile-card h3{margin:0 0 4px;font-size:15px;font-weight:700;color:var(--crm-text)}
#acct-workspace-page .acct-profile-card p{margin:0 0 14px;font-size:12px;color:var(--crm-text-muted)}
#acct-workspace-page .acct-profile-card__actions{display:grid;gap:8px}
#acct-workspace-page .acct-setup-banner{display:flex;flex-direction:column;align-items:flex-start;gap:12px;padding:16px;border:1px solid rgba(217,119,6,.25);border-radius:14px;background:linear-gradient(135deg,rgba(217,119,6,.08),rgba(245,158,11,.04))}
#acct-workspace-page .acct-setup-banner iconify-icon{font-size:24px;color:#b45309}
#acct-workspace-page .acct-setup-banner strong{display:block;font-size:14px;color:var(--crm-text)}
#acct-workspace-page .acct-setup-banner p{margin:0;font-size:12px;color:var(--crm-text-muted)}

#acct-workspace-page .acct-settings-workspace{display:grid;gap:16px;padding:0 16px 16px}
#acct-workspace-page .acct-settings-card{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#acct-workspace-page .acct-settings-card__head{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:10px;padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#acct-workspace-page .acct-settings-card__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#acct-workspace-page .acct-settings-card__sub{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#acct-workspace-page .acct-settings-card__body{padding:16px 18px}
#acct-workspace-page .acct-settings-stack{display:grid;gap:12px}
#acct-workspace-page .em-settings-footer{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;padding:12px 16px;border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04)}
#acct-workspace-page .em-settings-footer__hint{margin:0;font-size:12px;color:var(--crm-text-muted)}

@media(max-width:1200px){
    #acct-workspace-page .acct-module-nav{grid-template-columns:repeat(3,minmax(0,1fr))}
    #acct-workspace-page .acct-layout{grid-template-columns:1fr}
}
@media(max-width:768px){
    #acct-workspace-page .acct-module-nav{grid-template-columns:repeat(2,minmax(0,1fr))}
    #acct-workspace-page .crm-leads-table__head{display:none}
    #acct-workspace-page .acct-quick-row{grid-template-columns:1fr 24px!important;gap:8px;padding:12px}
    #acct-workspace-page .acct-quick-row .crm-list-row__field{display:none}
    #acct-workspace-page .acct-usage-row{grid-template-columns:1fr;padding:12px}
}
</style>
