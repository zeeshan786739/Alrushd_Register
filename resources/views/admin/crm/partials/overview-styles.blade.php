<style>
/* CRM Overview — focused, readable dashboard */
#crm-overview-page{
    font-family:var(--crm-font);
    color:var(--crm-text);
}

#crm-overview-page .crm-overview-workspace{background:var(--crm-surface)}

/* ── AI assistant strip ── */
#crm-overview-page .crm-overview-ai{
    padding:18px 20px;border-bottom:1px solid var(--crm-border);
    background:linear-gradient(135deg,rgba(15,39,74,.05),rgba(197,168,109,.07));
}
#crm-overview-page .crm-overview-ai__head{display:flex;align-items:flex-start;gap:12px;margin-bottom:14px}
#crm-overview-page .crm-overview-ai__badge{
    width:40px;height:40px;border-radius:var(--crm-radius-md);flex:0 0 40px;
    display:grid;place-items:center;font-size:20px;
    background:linear-gradient(135deg,var(--crm-brand),#1a3a5c);color:#fff;
    box-shadow:0 4px 12px rgba(15,39,74,.2);
}
#crm-overview-page .crm-overview-ai__title{margin:0 0 4px;font-size:13px;font-weight:700;color:var(--crm-text);letter-spacing:-.01em}
#crm-overview-page .crm-overview-ai__brief{
    margin:0;font-size:14px;line-height:1.55;color:var(--crm-text-muted);max-width:62ch;
}
#crm-overview-page .crm-overview-ai__brief--focus{color:var(--crm-text);font-weight:500}

#crm-overview-page .crm-overview-ai__search{position:relative;margin-bottom:12px;max-width:640px}
#crm-overview-page .crm-overview-ai__search-shell{
    display:flex;align-items:center;gap:8px;padding:4px 4px 4px 12px;
    border:1px solid var(--crm-border);border-radius:var(--crm-radius-md);
    background:var(--crm-surface);box-shadow:0 1px 3px rgba(9,30,66,.05);
}
#crm-overview-page .crm-overview-ai__search-shell:focus-within{
    border-color:var(--crm-brand);box-shadow:0 0 0 3px var(--crm-brand-soft);
}
#crm-overview-page .crm-overview-ai__search-shell>iconify-icon{color:var(--crm-brand);font-size:18px;flex-shrink:0}
#crm-overview-page .crm-overview-ai__search-input{
    flex:1;min-width:0;border:0;background:transparent;padding:8px 0;
    font-size:13px;color:var(--crm-text);outline:none;
}
#crm-overview-page .crm-overview-ai__search-input::placeholder{color:var(--crm-text-muted)}
#crm-overview-page .crm-overview-ai__search-go{
    width:36px;height:36px;border:0;border-radius:var(--crm-radius-sm);
    background:var(--crm-brand);color:#fff;display:grid;place-items:center;cursor:pointer;flex-shrink:0;
}
#crm-overview-page .crm-overview-ai__search-go:hover{background:var(--crm-brand-hover)}
#crm-overview-page .crm-overview-ai__search-panel{
    position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:30;
    padding:10px;border:1px solid var(--crm-border);border-radius:var(--crm-radius-md);
    background:var(--crm-surface);box-shadow:0 12px 32px rgba(9,30,66,.12);
}
#crm-overview-page .crm-overview-ai__interpretation{
    display:flex;align-items:center;gap:6px;margin-bottom:8px;padding:6px 8px;
    border-radius:var(--crm-radius-sm);background:var(--crm-brand-soft);
    font-size:12px;font-weight:500;color:var(--crm-brand);
}
#crm-overview-page .crm-overview-ai__suggestions{display:flex;flex-wrap:wrap;gap:6px}
#crm-overview-page .crm-overview-ai__suggestion{
    padding:5px 10px;border:1px solid var(--crm-border);border-radius:999px;
    background:var(--crm-surface-sunken);font-size:11px;font-weight:600;color:var(--crm-text-muted);cursor:pointer;
}
#crm-overview-page .crm-overview-ai__suggestion:hover{border-color:var(--crm-brand);color:var(--crm-brand);background:var(--crm-brand-soft)}

#crm-overview-page .crm-overview-ai__actions{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
#crm-overview-page .crm-overview-ai__action{
    display:inline-flex;align-items:center;gap:6px;padding:6px 12px;
    border-radius:999px;font-size:12px;font-weight:600;text-decoration:none;
    border:1px solid var(--crm-border);background:var(--crm-surface);color:var(--crm-text);
    transition:background .12s ease,border-color .12s ease;
}
#crm-overview-page .crm-overview-ai__action:hover{background:var(--crm-surface-sunken)}
#crm-overview-page .crm-overview-ai__action--brand{border-color:rgba(15,39,74,.2);color:var(--crm-brand)}
#crm-overview-page .crm-overview-ai__action--danger{border-color:#FFBDAD;background:#FFEBE6;color:#BF2600}
#crm-overview-page .crm-overview-ai__action--warning{border-color:#FFE380;background:#FFF7D6;color:#974F0C}
#crm-overview-page .crm-overview-ai__action--amber{border-color:rgba(197,168,109,.35);background:rgba(197,168,109,.1);color:#8a7344}

#crm-overview-page .crm-overview-ai__signals{display:flex;flex-wrap:wrap;gap:8px}
#crm-overview-page .crm-overview-signal{
    display:inline-flex;align-items:baseline;gap:8px;padding:6px 12px;
    border:1px solid var(--crm-border);border-radius:var(--crm-radius-sm);
    background:var(--crm-surface);text-decoration:none;color:inherit;
}
#crm-overview-page a.crm-overview-signal:hover{border-color:var(--crm-border-strong);background:var(--crm-surface-sunken)}
#crm-overview-page .crm-overview-signal__label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--crm-text-muted)}
#crm-overview-page .crm-overview-signal__value{font-size:16px;font-weight:800;font-variant-numeric:tabular-nums;letter-spacing:-.02em}
#crm-overview-page .crm-overview-signal--brand .crm-overview-signal__value{color:var(--crm-brand)}
#crm-overview-page .crm-overview-signal--purple .crm-overview-signal__value{color:#6d28d9}
#crm-overview-page .crm-overview-signal--gold .crm-overview-signal__value{color:#8a7344}
#crm-overview-page .crm-overview-signal--red .crm-overview-signal__value{color:#b91c1c}

/* ── Layout ── */
#crm-overview-page .crm-overview-layout{
    display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,320px);gap:16px;
    padding:16px 18px 18px;align-items:start;
}
#crm-overview-page .crm-overview-main{display:flex;flex-direction:column;gap:12px;min-width:0}
#crm-overview-page .crm-overview-aside{position:sticky;top:16px}

/* ── Panels ── */
#crm-overview-page .crm-overview-panel{
    border:1px solid var(--crm-border);border-radius:var(--crm-radius-md);
    background:var(--crm-surface);overflow:hidden;
}
#crm-overview-page .crm-overview-panel__head{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding:12px 14px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken);
}
#crm-overview-page .crm-overview-panel__title{
    display:flex;align-items:center;gap:8px;margin:0;font-size:14px;font-weight:700;color:var(--crm-text);
}
#crm-overview-page .crm-overview-panel__title iconify-icon{font-size:17px;color:var(--crm-brand)}
#crm-overview-page .crm-overview-panel__link{
    font-size:12px;font-weight:600;color:var(--crm-link, var(--crm-brand));text-decoration:none;
}
#crm-overview-page .crm-overview-panel__link:hover{text-decoration:underline}

/* ── Metrics (compact) ── */
#crm-overview-page .crm-overview-metrics{
    display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1px;
    background:var(--crm-border);
}
#crm-overview-page .crm-overview-metrics--3{grid-template-columns:repeat(3,minmax(0,1fr))}
#crm-overview-page .crm-overview-metric{
    display:flex;flex-direction:column;gap:4px;padding:12px 14px;background:var(--crm-surface);
    text-decoration:none;color:inherit;min-height:78px;transition:background .1s ease;
}
#crm-overview-page a.crm-overview-metric:hover{background:var(--crm-surface-sunken)}
#crm-overview-page .crm-overview-metric__top{display:flex;align-items:center;gap:6px}
#crm-overview-page .crm-overview-metric__icon{
    width:24px;height:24px;border-radius:6px;flex:0 0 24px;
    display:grid;place-items:center;font-size:13px;
}
#crm-overview-page .crm-overview-metric--accent-brand .crm-overview-metric__icon{background:rgba(15,39,74,.1);color:var(--crm-brand)}
#crm-overview-page .crm-overview-metric--accent-purple .crm-overview-metric__icon{background:rgba(124,58,237,.1);color:#6d28d9}
#crm-overview-page .crm-overview-metric--accent-blue .crm-overview-metric__icon{background:rgba(37,99,235,.1);color:#1d4ed8}
#crm-overview-page .crm-overview-metric--accent-green .crm-overview-metric__icon{background:rgba(22,163,74,.1);color:#15803d}
#crm-overview-page .crm-overview-metric--accent-amber .crm-overview-metric__icon{background:rgba(197,168,109,.15);color:#9a7b42}
#crm-overview-page .crm-overview-metric--accent-red .crm-overview-metric__icon{background:#FFEBE6;color:#BF2600}
#crm-overview-page .crm-overview-metric__label{
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted);
}
#crm-overview-page .crm-overview-metric__value{
    font-size:22px;line-height:1.1;font-weight:800;color:var(--crm-text);
    font-variant-numeric:tabular-nums;letter-spacing:-.02em;
}
#crm-overview-page .crm-overview-metric--accent-brand .crm-overview-metric__value{color:var(--crm-brand)}
#crm-overview-page .crm-overview-metric--accent-purple .crm-overview-metric__value{color:#6d28d9}
#crm-overview-page .crm-overview-metric--accent-blue .crm-overview-metric__value{color:#1d4ed8}
#crm-overview-page .crm-overview-metric--accent-green .crm-overview-metric__value{color:#15803d}
#crm-overview-page .crm-overview-metric--accent-amber .crm-overview-metric__value{color:#9a7b42}
#crm-overview-page .crm-overview-metric--accent-red .crm-overview-metric__value{color:#b91c1c}
#crm-overview-page .crm-overview-metric__hint{font-size:10px;color:var(--crm-text-muted);margin-top:auto}
#crm-overview-page .crm-overview-metric__track{display:none}

/* ── Attention sidebar ── */
#crm-overview-page .crm-overview-attention{
    border:1px solid var(--crm-border);border-radius:var(--crm-radius-md);
    background:var(--crm-surface);overflow:hidden;
}
#crm-overview-page .crm-overview-attention__head{
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    padding:12px 14px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken);
}
#crm-overview-page .crm-overview-attention__head h2{
    display:flex;align-items:center;gap:6px;margin:0;font-size:13px;font-weight:700;color:var(--crm-text);
}
#crm-overview-page .crm-overview-attention__head h2 iconify-icon{color:var(--crm-brand);font-size:16px}
#crm-overview-page .crm-overview-attention__count{
    min-width:22px;height:22px;padding:0 7px;border-radius:999px;
    background:var(--crm-brand);color:#fff;font-size:11px;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;
}
#crm-overview-page .crm-overview-attention__list{
    max-height:min(480px,60vh);overflow-y:auto;padding:6px;
}
#crm-overview-page .crm-overview-attention__item{
    display:flex;align-items:flex-start;gap:10px;padding:10px;border-radius:var(--crm-radius-sm);
    text-decoration:none;color:inherit;margin-bottom:4px;border:1px solid transparent;
}
#crm-overview-page .crm-overview-attention__item:last-child{margin-bottom:0}
#crm-overview-page .crm-overview-attention__item:hover{background:var(--crm-surface-sunken);border-color:var(--crm-border)}
#crm-overview-page .crm-overview-attention__icon{
    width:28px;height:28px;border-radius:var(--crm-radius-sm);flex:0 0 28px;
    display:grid;place-items:center;font-size:14px;
}
#crm-overview-page .crm-overview-attention__item--danger .crm-overview-attention__icon{background:#FFEBE6;color:#BF2600}
#crm-overview-page .crm-overview-attention__item--warning .crm-overview-attention__icon{background:#FFF7D6;color:#974F0C}
#crm-overview-page .crm-overview-attention__body{flex:1;min-width:0}
#crm-overview-page .crm-overview-attention__label{
    display:block;font-size:13px;font-weight:600;color:var(--crm-text);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-overview-page .crm-overview-attention__meta{
    display:block;margin-top:2px;font-size:11px;color:var(--crm-text-muted);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-overview-page .crm-overview-attention__empty{
    display:grid;place-items:center;gap:6px;padding:28px 16px;text-align:center;color:var(--crm-text-muted);
}
#crm-overview-page .crm-overview-attention__empty iconify-icon{font-size:32px;color:#16a34a}
#crm-overview-page .crm-overview-attention__empty strong{font-size:14px;color:var(--crm-text)}

/* ── Form breakdown (compact) ── */
#crm-overview-page .crm-overview-form-breakdown{border-top:1px solid var(--crm-border)}
#crm-overview-page .crm-overview-form-breakdown__row--compact{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding:10px 14px;text-decoration:none;color:inherit;border-bottom:1px solid var(--crm-surface-sunken);
    font-size:12px;
}
#crm-overview-page .crm-overview-form-breakdown__row--compact:last-child{border-bottom:0}
#crm-overview-page .crm-overview-form-breakdown__row--compact:hover{background:var(--crm-surface-sunken)}
#crm-overview-page .crm-overview-form-breakdown__name{
    min-width:0;font-weight:600;color:var(--crm-text);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#crm-overview-page .crm-overview-form-breakdown__stat{font-weight:700;flex-shrink:0}
#crm-overview-page .crm-overview-form-breakdown__stat.is-attention{color:#b45309}

@media(max-width:1024px){
    #crm-overview-page .crm-overview-layout{grid-template-columns:1fr}
    #crm-overview-page .crm-overview-aside{position:static}
}
@media(max-width:640px){
    #crm-overview-page .crm-overview-metrics,
    #crm-overview-page .crm-overview-metrics--3{grid-template-columns:1fr}
    #crm-overview-page .crm-overview-ai{padding:14px}
}
</style>
