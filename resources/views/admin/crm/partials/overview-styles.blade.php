<style>
/* CRM Overview — uses global AL-Rushd tokens from alrushad-overrides.css */
#crm-overview-page{
    font-family:var(--crm-font);
    color:var(--crm-text);
}

#crm-overview-page .crm-overview-workspace{background:var(--crm-surface)}
#crm-overview-page .crm-overview-summary{
    display:flex;flex-wrap:wrap;align-items:center;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));border-radius:0;
}
#crm-overview-page .crm-overview-summary__item{
    display:inline-flex;align-items:baseline;gap:6px;font-size:var(--crm-text-sm);color:var(--crm-text-muted);
}
#crm-overview-page .crm-overview-summary__label{
    font-size:var(--crm-text-xs);font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--crm-text-muted);
}
#crm-overview-page .crm-overview-summary__item strong{
    font-size:15px;font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums;
}
#crm-overview-page .crm-overview-summary__sep{width:1px;height:14px;background:var(--crm-border)}

#crm-overview-page .crm-overview-layout{
    display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,360px);gap:0;align-items:start;
    padding:14px 16px 16px;
}
#crm-overview-page .crm-overview-main{display:flex;flex-direction:column;gap:12px;min-width:0;padding-right:14px;border-right:1px solid var(--crm-border)}
#crm-overview-page .crm-overview-aside{position:sticky;top:16px;padding-left:14px}

#crm-overview-page .crm-overview-panel{
    border:1px solid var(--crm-border);border-radius:var(--crm-radius-lg);background:var(--crm-surface);overflow:hidden;
    box-shadow:var(--crm-shadow-sm);
}
#crm-overview-page .crm-overview-panel__head{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding:16px 18px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface);
}
#crm-overview-page .crm-overview-panel__title{
    display:flex;align-items:center;gap:8px;margin:0;font-size:15px;font-weight:700;color:var(--crm-text);
}
#crm-overview-page .crm-overview-panel__title iconify-icon{font-size:18px;color:var(--crm-brand)}
#crm-overview-page .crm-overview-panel__link{
    font-size:12px;font-weight:500;color:var(--crm-link, var(--crm-brand));text-decoration:none;
}
#crm-overview-page .crm-overview-panel__link:hover{text-decoration:underline}

#crm-overview-page .crm-overview-metrics{
    display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;background:var(--crm-border);
}
#crm-overview-page .crm-overview-metrics--cols-6{grid-template-columns:repeat(3,minmax(0,1fr))}
#crm-overview-page .crm-overview-metric{
    display:flex;flex-direction:column;gap:4px;padding:12px 14px 12px 16px;background:var(--crm-surface);
    text-decoration:none;color:inherit;transition:background .1s ease;min-height:72px;
    position:relative;
}
#crm-overview-page .crm-overview-metric::before{
    content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--crm-border-strong);
}
#crm-overview-page a.crm-overview-metric:hover{background:var(--crm-surface-sunken)}
#crm-overview-page .crm-overview-metric--accent-brand::before{background:var(--crm-brand)}
#crm-overview-page .crm-overview-metric--accent-purple::before{background:#7c3aed}
#crm-overview-page .crm-overview-metric--accent-blue::before{background:#2563eb}
#crm-overview-page .crm-overview-metric--accent-teal::before{background:#0891b2}
#crm-overview-page .crm-overview-metric--accent-green::before{background:#16a34a}
#crm-overview-page .crm-overview-metric--accent-amber::before{background:var(--crm-accent)}
#crm-overview-page .crm-overview-metric--accent-red::before{background:#dc2626}
#crm-overview-page .crm-overview-metric__label{
    font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted);
}
#crm-overview-page .crm-overview-metric__value{
    font-size:22px;line-height:1.15;font-weight:800;color:var(--crm-text);font-variant-numeric:tabular-nums;letter-spacing:-.02em;
}
#crm-overview-page .crm-overview-metric__hint{font-size:11px;color:var(--crm-text-muted)}
#crm-overview-page .crm-overview-metric--accent-brand .crm-overview-metric__value{color:var(--crm-brand)}
#crm-overview-page .crm-overview-metric--accent-purple .crm-overview-metric__value{color:#6d28d9}
#crm-overview-page .crm-overview-metric--accent-blue .crm-overview-metric__value{color:#1d4ed8}
#crm-overview-page .crm-overview-metric--accent-teal .crm-overview-metric__value{color:#0e7490}
#crm-overview-page .crm-overview-metric--accent-green .crm-overview-metric__value{color:#15803d}
#crm-overview-page .crm-overview-metric--accent-amber .crm-overview-metric__value{color:#9a7b42}
#crm-overview-page .crm-overview-metric--accent-red .crm-overview-metric__value{color:#b91c1c}

#crm-overview-page .crm-overview-attention{padding:0}
#crm-overview-page .crm-overview-attention__head{
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    padding:10px 14px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken);
}
#crm-overview-page .crm-overview-attention__head h2{
    display:flex;align-items:center;gap:8px;margin:0;font-size:13px;font-weight:600;color:var(--crm-text);
}
#crm-overview-page .crm-overview-attention__count{
    min-width:20px;height:20px;padding:0 6px;border-radius:999px;
    background:var(--crm-brand);color:#fff;font-size:11px;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;
}
#crm-overview-page .crm-overview-attention__list{padding:8px}
#crm-overview-page .crm-overview-attention__item{
    display:flex;align-items:flex-start;gap:10px;padding:10px;border-radius:var(--crm-radius-md);
    text-decoration:none;color:inherit;transition:background .1s ease;margin-bottom:4px;
}
#crm-overview-page .crm-overview-attention__item:last-child{margin-bottom:0}
#crm-overview-page .crm-overview-attention__item:hover{background:var(--crm-surface-sunken)}
#crm-overview-page .crm-overview-attention__icon{
    width:28px;height:28px;border-radius:var(--crm-radius-md);flex:0 0 28px;
    display:grid;place-items:center;font-size:14px;
}
#crm-overview-page .crm-overview-attention__item--danger .crm-overview-attention__icon{background:#FFEBE6;color:#BF2600}
#crm-overview-page .crm-overview-attention__item--warning .crm-overview-attention__icon{background:#FFF7D6;color:#974F0C}
#crm-overview-page .crm-overview-attention__body{flex:1;min-width:0}
#crm-overview-page .crm-overview-attention__type{
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted);
}
#crm-overview-page .crm-overview-attention__label{
    margin-top:2px;font-size:13px;font-weight:500;color:var(--crm-text);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-overview-page .crm-overview-attention__meta{
    margin-top:2px;font-size:11px;color:var(--crm-text-muted);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-overview-page .crm-overview-attention__chevron{flex:0 0 auto;color:var(--crm-text-muted);margin-top:4px}
#crm-overview-page .crm-overview-attention__empty{
    display:grid;place-items:center;gap:8px;padding:32px 16px;text-align:center;color:var(--crm-text-muted);
}
#crm-overview-page .crm-overview-attention__empty iconify-icon{font-size:28px;color:var(--crm-border-strong)}
#crm-overview-page .crm-overview-attention__empty strong{display:block;font-size:13px;font-weight:500;color:var(--crm-text)}

#crm-overview-page .crm-overview-form-breakdown{
    border-top:1px solid var(--crm-border);background:var(--crm-surface-sunken);
}
#crm-overview-page .crm-overview-form-breakdown__head,
#crm-overview-page .crm-overview-form-breakdown__row{
    display:grid;grid-template-columns:minmax(0,1.6fr) repeat(4,minmax(52px,.5fr));gap:8px;align-items:center;
    padding:10px 16px;font-size:12px;
}
#crm-overview-page .crm-overview-form-breakdown__head{
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--crm-text-muted);
    border-bottom:1px solid var(--crm-border);
}
#crm-overview-page .crm-overview-form-breakdown__row{
    text-decoration:none;color:inherit;border-bottom:1px solid var(--crm-border);transition:background .1s ease;
}
#crm-overview-page .crm-overview-form-breakdown__row:last-child{border-bottom:0}
#crm-overview-page .crm-overview-form-breakdown__row:hover{background:rgba(15,39,74,.04)}
#crm-overview-page .crm-overview-form-breakdown__name{
    display:flex;align-items:center;gap:8px;min-width:0;font-weight:600;color:var(--crm-text);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-overview-page .crm-overview-form-breakdown__name iconify-icon{color:var(--crm-brand);font-size:16px;flex-shrink:0}
#crm-overview-page .crm-overview-form-breakdown__stat{
    font-variant-numeric:tabular-nums;font-weight:700;color:var(--crm-text);text-align:right;
}
#crm-overview-page .crm-overview-form-breakdown__stat.is-attention{color:#b45309}

@media(max-width:1199px){
    #crm-overview-page .crm-overview-layout{grid-template-columns:1fr;padding:12px}
    #crm-overview-page .crm-overview-main{padding-right:0;border-right:0;padding-bottom:12px;border-bottom:1px solid var(--crm-border)}
    #crm-overview-page .crm-overview-aside{position:static;padding-left:0;padding-top:12px}
    #crm-overview-page .crm-overview-metrics--cols-6{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:767px){
    #crm-overview-page{padding:12px}
    #crm-overview-page .crm-overview-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}
    #crm-overview-page .crm-overview-metrics--cols-6{grid-template-columns:1fr}
    #crm-overview-page .crm-overview-nav__link{padding:8px 10px;font-size:11px}
}
@media(max-width:480px){
    #crm-overview-page .crm-overview-metrics{grid-template-columns:1fr}
}
</style>
