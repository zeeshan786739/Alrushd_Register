<style>
#enrollment-setup-page{font-family:var(--crm-font);color:var(--crm-text)}
#enrollment-setup-page .enroll-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
#enrollment-setup-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#enrollment-setup-page .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}
#enrollment-setup-page .enroll-page-body{padding:16px;display:grid;grid-template-columns:minmax(240px,280px) minmax(0,1fr);gap:16px;align-items:start}
#enrollment-setup-page .enroll-nav-panel{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#enrollment-setup-page .enroll-nav-panel__head{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));font-size:14px;font-weight:700;color:var(--crm-text)}
#enrollment-setup-page .enroll-nav-panel__head iconify-icon{font-size:20px;color:var(--crm-brand)}
#enrollment-setup-page .enroll-nav-group{margin:12px 16px 6px;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--crm-text-muted)}
#enrollment-setup-page .enroll-nav-item{
    display:grid;grid-template-columns:auto 1fr auto;gap:10px;align-items:center;width:100%;padding:10px 16px;
    border:0;border-left:3px solid transparent;background:transparent;color:var(--crm-text-muted);text-decoration:none;
    font-size:13px;font-weight:600;text-align:left;cursor:pointer;transition:background .12s ease,border-color .12s ease,color .12s ease;
}
#enrollment-setup-page .enroll-nav-item iconify-icon{font-size:18px;color:var(--crm-text-muted)}
#enrollment-setup-page .enroll-nav-item em{font-style:normal;font-size:11px;font-weight:700;color:var(--crm-text-muted);background:var(--crm-surface-sunken);padding:2px 8px;border-radius:999px}
#enrollment-setup-page .enroll-nav-item:hover{background:var(--crm-brand-soft);color:var(--crm-brand)}
#enrollment-setup-page .enroll-nav-item.is-active{background:linear-gradient(90deg,rgba(15,39,74,.04),rgba(197,168,109,.08));border-left-color:var(--crm-brand);color:var(--crm-brand)}
#enrollment-setup-page .enroll-nav-item.is-active iconify-icon{color:var(--crm-brand)}
#enrollment-setup-page .enroll-nav-item.is-active em{background:var(--crm-brand-soft);color:var(--crm-brand)}
#enrollment-setup-page .enroll-nav-extra{border-top:1px solid var(--crm-border);padding-bottom:8px;margin-top:8px}
#enrollment-setup-page .enroll-nav-item--link em{background:rgba(8,145,178,.12);color:#0e7490}
#enrollment-setup-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#enrollment-setup-page .crm-metrics-strip__item{display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#enrollment-setup-page .crm-metrics-strip__label{font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#enrollment-setup-page .crm-metrics-strip__item strong{font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums}
#enrollment-setup-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#enrollment-setup-page .enroll-main{min-width:0}
#enrollment-setup-page .enroll-panel{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04);display:none}
#enrollment-setup-page .enroll-panel.is-visible{display:block}
#enrollment-setup-page .enroll-panel__head{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:10px;padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#enrollment-setup-page .enroll-panel__head h2{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#enrollment-setup-page .enroll-panel__head p{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#enrollment-setup-page .enroll-panel__count{font-size:12px;font-weight:700;color:var(--crm-brand);background:var(--crm-brand-soft);padding:4px 10px;border-radius:999px}
#enrollment-setup-page .enroll-add{display:flex;flex-wrap:wrap;gap:10px;padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,rgba(15,39,74,.02),transparent)}
#enrollment-setup-page .enroll-add__input,#enrollment-setup-page .enroll-add__status{height:40px;border:1px solid var(--crm-border);border-radius:10px;font-size:14px;padding:6px 12px;background:var(--crm-surface)}
#enrollment-setup-page .enroll-add__input{flex:1;min-width:180px}
#enrollment-setup-page .enroll-add__status{min-width:120px}
#enrollment-setup-page .crm-leads-table{border:0;border-radius:0;box-shadow:none;background:transparent}
#enrollment-setup-page .crm-leads-table__head,#enrollment-setup-page .enroll-catalog-row{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(100px,.45fr) 96px;gap:10px;align-items:center;padding:0 12px 0 14px}
#enrollment-setup-page .crm-leads-table__head{
    position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;
    color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
    background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border);
}
#enrollment-setup-page .crm-leads-list{display:flex;flex-direction:column;gap:0}
#enrollment-setup-page .enroll-catalog-row{
    position:relative;min-height:0;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);transition:background .12s ease;
}
#enrollment-setup-page .enroll-catalog-row:last-child{border-bottom:0}
#enrollment-setup-page .enroll-catalog-row:hover{background:var(--crm-surface-sunken)}
#enrollment-setup-page .enroll-catalog-row:nth-child(even){background:rgba(9,30,66,.018)}
#enrollment-setup-page .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0;background:linear-gradient(180deg,#1a3a5c,var(--crm-brand))}
#enrollment-setup-page .enroll-inline-form{display:flex;flex-wrap:wrap;align-items:center;gap:8px;width:100%}
#enrollment-setup-page .enroll-inline-form__input,#enrollment-setup-page .enroll-inline-form__status{height:34px;border:1px solid var(--crm-border);border-radius:8px;font-size:13px;padding:4px 10px;background:var(--crm-surface);min-width:120px;flex:1}
#enrollment-setup-page .enroll-inline-form__status{flex:0 0 110px;min-width:110px}
#enrollment-setup-page .enroll-catalog-row__actions{display:flex;justify-content:flex-end;gap:6px}
#enrollment-setup-page .crm-leads-list-empty{display:grid;place-items:center;gap:6px;padding:48px 16px;color:var(--crm-text-muted);text-align:center}
#enrollment-setup-page .crm-leads-list-empty strong{color:var(--crm-text);font-weight:600}
@media(max-width:991px){
    #enrollment-setup-page .enroll-page-body{grid-template-columns:1fr}
}
@media(max-width:768px){
    #enrollment-setup-page .crm-leads-table__head{display:none}
    #enrollment-setup-page .enroll-catalog-row{grid-template-columns:1fr!important;gap:8px;padding:12px}
    #enrollment-setup-page .enroll-catalog-row__actions{justify-content:flex-start}
}
</style>
