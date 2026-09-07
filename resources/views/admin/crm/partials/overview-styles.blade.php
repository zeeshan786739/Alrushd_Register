<style>
#crm-overview-page{
    --ov-text:#172B4D;
    --ov-subtle:#42526E;
    --ov-muted:#6B778C;
    --ov-border:#DFE1E6;
    --ov-surface:#FFFFFF;
    --ov-bg:#F4F5F7;
    --ov-brand:#0052CC;
    --ov-brand-subtle:#DEEBFF;
    --ov-radius:3px;
    background:var(--ov-bg);
    color:var(--ov-text);
    padding:16px 20px 28px;
    min-height:calc(100vh - 72px);
    font-size:14px;
    line-height:1.4286;
}

#crm-overview-page .crm-page-header{
    display:flex;align-items:center;padding:0 0 16px;margin-bottom:14px;border-bottom:1px solid var(--ov-border);
}
#crm-overview-page .crm-page-header__title{
    font-size:24px!important;line-height:1.25!important;font-weight:500!important;
    letter-spacing:-.01em;color:var(--ov-text);margin-bottom:4px;
}
#crm-overview-page .crm-page-header__subtitle{font-size:12px;color:var(--ov-muted);margin:0;font-weight:400}
#crm-overview-page .crm-breadcrumb{display:none}

#crm-overview-page .crm-overview-workspace{background:#fff}
#crm-overview-page .crm-overview-summary{
    display:flex;flex-wrap:wrap;align-items:center;gap:6px 14px;
    margin:0;padding:10px 16px;border:0;border-bottom:1px solid var(--ov-border);
    background:#FAFBFC;border-radius:0;
}
#crm-overview-page .crm-overview-summary__item{
    display:inline-flex;align-items:baseline;gap:6px;font-size:12px;color:var(--ov-subtle);
}
#crm-overview-page .crm-overview-summary__label{
    font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--ov-muted);
}
#crm-overview-page .crm-overview-summary__item strong{
    font-size:13px;font-weight:600;color:var(--ov-text);font-variant-numeric:tabular-nums;
}
#crm-overview-page .crm-overview-summary__sep{width:1px;height:14px;background:var(--ov-border)}

#crm-overview-page .crm-overview-layout{
    display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,360px);gap:0;align-items:start;
    padding:14px 16px 16px;
}
#crm-overview-page .crm-overview-main{display:flex;flex-direction:column;gap:12px;min-width:0;padding-right:14px;border-right:1px solid var(--ov-border)}
#crm-overview-page .crm-overview-aside{position:sticky;top:16px;padding-left:14px}

#crm-overview-page .crm-overview-panel{
    border:1px solid var(--ov-border);border-radius:var(--ov-radius);background:var(--ov-surface);overflow:hidden;
    box-shadow:0 1px 0 rgba(9,30,66,.04);
}
#crm-overview-page .crm-overview-panel__head{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding:10px 14px;border-bottom:1px solid var(--ov-border);background:var(--ov-bg);
}
#crm-overview-page .crm-overview-panel__title{
    display:flex;align-items:center;gap:8px;margin:0;font-size:13px;font-weight:600;color:var(--ov-text);
}
#crm-overview-page .crm-overview-panel__title iconify-icon{font-size:16px;color:var(--ov-muted)}
#crm-overview-page .crm-overview-panel__link{
    font-size:12px;font-weight:500;color:var(--ov-brand);text-decoration:none;
}
#crm-overview-page .crm-overview-panel__link:hover{text-decoration:underline}

#crm-overview-page .crm-overview-metrics{
    display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;background:var(--ov-border);
}
#crm-overview-page .crm-overview-metrics--cols-6{grid-template-columns:repeat(3,minmax(0,1fr))}
#crm-overview-page .crm-overview-metric{
    display:flex;flex-direction:column;gap:4px;padding:12px 14px 12px 16px;background:var(--ov-surface);
    text-decoration:none;color:inherit;transition:background .1s ease;min-height:72px;
    position:relative;
}
#crm-overview-page .crm-overview-metric::before{
    content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:#C1C7D0;
}
#crm-overview-page a.crm-overview-metric:hover{background:#FAFBFC}
#crm-overview-page .crm-overview-metric--accent-brand::before{background:#0052CC}
#crm-overview-page .crm-overview-metric--accent-purple::before{background:#6554C0}
#crm-overview-page .crm-overview-metric--accent-blue::before{background:#4C9AFF}
#crm-overview-page .crm-overview-metric--accent-teal::before{background:#00B8D9}
#crm-overview-page .crm-overview-metric--accent-green::before{background:#36B37E}
#crm-overview-page .crm-overview-metric--accent-amber::before{background:#FF991F}
#crm-overview-page .crm-overview-metric--accent-red::before{background:#DE350B}
#crm-overview-page .crm-overview-metric__label{
    font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--ov-muted);
}
#crm-overview-page .crm-overview-metric__value{
    font-size:20px;line-height:1.2;font-weight:600;color:var(--ov-text);font-variant-numeric:tabular-nums;
}
#crm-overview-page .crm-overview-metric__hint{font-size:11px;color:var(--ov-muted)}
#crm-overview-page .crm-overview-metric--accent-brand .crm-overview-metric__value{color:var(--ov-brand)}
#crm-overview-page .crm-overview-metric--accent-purple .crm-overview-metric__value{color:#403294}
#crm-overview-page .crm-overview-metric--accent-blue .crm-overview-metric__value{color:#0747A6}
#crm-overview-page .crm-overview-metric--accent-teal .crm-overview-metric__value{color:#008DA6}
#crm-overview-page .crm-overview-metric--accent-green .crm-overview-metric__value{color:#006644}
#crm-overview-page .crm-overview-metric--accent-amber .crm-overview-metric__value{color:#974F0C}
#crm-overview-page .crm-overview-metric--accent-red .crm-overview-metric__value{color:#BF2600}

#crm-overview-page .crm-overview-attention{padding:0}
#crm-overview-page .crm-overview-attention__head{
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    padding:10px 14px;border-bottom:1px solid var(--ov-border);background:var(--ov-bg);
}
#crm-overview-page .crm-overview-attention__head h2{
    display:flex;align-items:center;gap:8px;margin:0;font-size:13px;font-weight:600;color:var(--ov-text);
}
#crm-overview-page .crm-overview-attention__count{
    min-width:20px;height:20px;padding:0 6px;border-radius:var(--ov-radius);
    background:var(--ov-brand);color:#fff;font-size:11px;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;
}
#crm-overview-page .crm-overview-attention__list{padding:8px}
#crm-overview-page .crm-overview-attention__item{
    display:flex;align-items:flex-start;gap:10px;padding:10px;border-radius:var(--ov-radius);
    text-decoration:none;color:inherit;transition:background .1s ease;margin-bottom:4px;
}
#crm-overview-page .crm-overview-attention__item:last-child{margin-bottom:0}
#crm-overview-page .crm-overview-attention__item:hover{background:var(--ov-bg)}
#crm-overview-page .crm-overview-attention__icon{
    width:28px;height:28px;border-radius:var(--ov-radius);flex:0 0 28px;
    display:grid;place-items:center;font-size:14px;
}
#crm-overview-page .crm-overview-attention__item--danger .crm-overview-attention__icon{background:#FFEBE6;color:#BF2600}
#crm-overview-page .crm-overview-attention__item--warning .crm-overview-attention__icon{background:#FFF7D6;color:#974F0C}
#crm-overview-page .crm-overview-attention__body{flex:1;min-width:0}
#crm-overview-page .crm-overview-attention__type{
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--ov-muted);
}
#crm-overview-page .crm-overview-attention__label{
    margin-top:2px;font-size:13px;font-weight:500;color:var(--ov-text);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-overview-page .crm-overview-attention__meta{
    margin-top:2px;font-size:11px;color:var(--ov-muted);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-overview-page .crm-overview-attention__chevron{flex:0 0 auto;color:var(--ov-muted);margin-top:4px}
#crm-overview-page .crm-overview-attention__empty{
    display:grid;place-items:center;gap:8px;padding:32px 16px;text-align:center;color:var(--ov-muted);
}
#crm-overview-page .crm-overview-attention__empty iconify-icon{font-size:28px;color:#C1C7D0}
#crm-overview-page .crm-overview-attention__empty strong{display:block;font-size:13px;font-weight:500;color:var(--ov-text)}

@media(max-width:1199px){
    #crm-overview-page .crm-overview-layout{grid-template-columns:1fr;padding:12px}
    #crm-overview-page .crm-overview-main{padding-right:0;border-right:0;padding-bottom:12px;border-bottom:1px solid var(--ov-border)}
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
