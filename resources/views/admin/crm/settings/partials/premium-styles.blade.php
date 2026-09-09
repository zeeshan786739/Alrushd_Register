<style>
#crm-document-settings-page{font-family:var(--crm-font);color:var(--crm-text)}
#crm-document-settings-page .crm-doc-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
#crm-document-settings-page .crm-metrics-strip{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08))}
#crm-document-settings-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#crm-document-settings-page .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}
#crm-document-settings-page .crm-metrics-strip__links{display:flex;align-items:center;gap:12px}
#crm-document-settings-page .crm-metrics-strip__link{font-size:var(--crm-text-sm);font-weight:500;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#crm-document-settings-page .crm-metrics-strip__link:hover{text-decoration:underline;color:var(--crm-brand-hover)}
#crm-document-settings-page .crm-doc-notice{display:flex;align-items:flex-start;gap:10px;margin:0;padding:12px 18px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,rgba(15,39,74,.03),rgba(197,168,109,.05));color:var(--crm-text-muted);font-size:13px;line-height:1.45}
#crm-document-settings-page .crm-doc-notice iconify-icon{flex-shrink:0;margin-top:2px;font-size:18px;color:var(--crm-brand)}
#crm-document-settings-page .crm-doc-tabs{padding:14px 18px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#crm-document-settings-page .crm-doc-tabs__head{display:grid;gap:2px;margin-bottom:10px}
#crm-document-settings-page .crm-doc-tabs__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#crm-document-settings-page .crm-doc-tabs__sub{margin:0;font-size:12px;color:var(--crm-text-muted)}
#crm-document-settings-page .crm-doc-tabs__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
#crm-document-settings-page .crm-doc-tab{display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease}
#crm-document-settings-page .crm-doc-tab:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px);color:var(--crm-brand)}
#crm-document-settings-page .crm-doc-tab.is-active{border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand)}
#crm-document-settings-page .crm-doc-tab__icon{display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken);font-size:18px}
#crm-document-settings-page .crm-doc-tab.is-active .crm-doc-tab__icon{background:var(--crm-brand);color:#fff}
#crm-document-settings-page .crm-doc-tab__label{font-size:12px;font-weight:700;line-height:1.2}
#crm-document-settings-page .crm-doc-panel{padding:18px}
#crm-document-settings-page .crm-doc-section{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#crm-document-settings-page .crm-doc-section + .crm-doc-section{margin-top:14px}
#crm-document-settings-page .crm-doc-section__head{padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#crm-document-settings-page .crm-doc-section__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#crm-document-settings-page .crm-doc-section__sub{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#crm-document-settings-page .crm-doc-section__body{padding:16px}
#crm-document-settings-page .crm-doc-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}
#crm-document-settings-page .crm-doc-grid__col-12{grid-column:span 12}
#crm-document-settings-page .crm-doc-grid__col-6{grid-column:span 6}
#crm-document-settings-page .crm-doc-grid__col-4{grid-column:span 4}
#crm-document-settings-page .crm-doc-field{display:grid;gap:8px;min-width:0}
#crm-document-settings-page .crm-doc-field__head{display:flex;align-items:center;justify-content:space-between;gap:10px}
#crm-document-settings-page .crm-doc-field__label{margin:0;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#crm-document-settings-page .crm-doc-field .form-control,#crm-document-settings-page .crm-doc-field .form-select{height:38px;padding:6px 12px;border:1px solid var(--crm-border);border-radius:10px;font-size:13px;color:var(--crm-text);background:var(--crm-surface);transition:border-color .12s ease,box-shadow .12s ease}
#crm-document-settings-page .crm-doc-field textarea.form-control{height:auto;min-height:84px;resize:vertical}
#crm-document-settings-page .crm-doc-field .form-control:focus{border-color:rgba(197,168,109,.75);box-shadow:0 0 0 3px rgba(197,168,109,.18);outline:none}
#crm-document-settings-page .crm-doc-toggle{display:inline-flex;align-items:center;gap:6px;cursor:pointer;user-select:none}
#crm-document-settings-page .crm-doc-toggle__input{position:absolute;opacity:0;width:0;height:0;pointer-events:none}
#crm-document-settings-page .crm-doc-toggle__track{position:relative;width:34px;height:20px;border-radius:999px;background:#cbd5e1;transition:background .15s ease;flex-shrink:0}
#crm-document-settings-page .crm-doc-toggle__track::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(15,39,74,.2);transition:transform .15s ease}
#crm-document-settings-page .crm-doc-toggle__input:checked + .crm-doc-toggle__track{background:linear-gradient(135deg,var(--crm-brand),rgba(197,168,109,.85))}
#crm-document-settings-page .crm-doc-toggle__input:checked + .crm-doc-toggle__track::after{transform:translateX(14px)}
#crm-document-settings-page .crm-doc-toggle__input:focus-visible + .crm-doc-toggle__track{outline:3px solid rgba(197,168,109,.25);outline-offset:2px}
#crm-document-settings-page .crm-doc-toggle__text{font-size:11px;font-weight:600;color:var(--crm-text-muted);white-space:nowrap}
#crm-document-settings-page .crm-doc-toggle__input:checked ~ .crm-doc-toggle__text{color:var(--crm-brand)}
#crm-document-settings-page .crm-doc-logo-zone{display:grid;gap:10px;padding:14px;border:1px dashed var(--crm-border);border-radius:12px;background:rgba(15,39,74,.015)}
#crm-document-settings-page .crm-doc-logo-zone__preview{display:flex;align-items:center;justify-content:center;min-height:72px;padding:10px;border-radius:10px;background:var(--crm-surface-sunken)}
#crm-document-settings-page .crm-doc-logo-zone__preview img{max-height:64px;max-width:180px;object-fit:contain;display:block}
#crm-document-settings-page .crm-doc-logo-zone__empty{font-size:12px;color:var(--crm-text-muted);text-align:center}
#crm-document-settings-page .crm-doc-logo-zone__file .form-control{height:auto;padding:8px 10px;font-size:12px}
#crm-document-settings-page .crm-doc-check{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:var(--crm-text-muted);cursor:pointer}
#crm-document-settings-page .crm-doc-check .form-check-input{margin:0;width:16px;height:16px;cursor:pointer}
#crm-document-settings-page .crm-doc-vis-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
#crm-document-settings-page .crm-doc-vis-chip{position:relative;display:block;margin:0;cursor:pointer}
#crm-document-settings-page .crm-doc-vis-chip__input{position:absolute;opacity:0;width:0;height:0;pointer-events:none}
#crm-document-settings-page .crm-doc-vis-chip__inner{display:flex;align-items:center;gap:8px;min-height:40px;padding:8px 12px;border:1px solid var(--crm-border);border-radius:10px;background:var(--crm-surface);color:var(--crm-text-muted);font-size:12px;font-weight:600;transition:border-color .12s ease,background .12s ease,color .12s ease}
#crm-document-settings-page .crm-doc-vis-chip__icon{display:grid;place-items:center;width:18px;height:18px;border-radius:6px;border:1px solid var(--crm-border);background:var(--crm-surface-sunken);font-size:12px;flex-shrink:0;color:transparent}
#crm-document-settings-page .crm-doc-vis-chip__input:checked + .crm-doc-vis-chip__inner{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);color:var(--crm-brand)}
#crm-document-settings-page .crm-doc-vis-chip__input:checked + .crm-doc-vis-chip__inner .crm-doc-vis-chip__icon{border-color:var(--crm-brand);background:var(--crm-brand);color:#fff}
#crm-document-settings-page .crm-doc-vis-chip__input:focus-visible + .crm-doc-vis-chip__inner{outline:3px solid rgba(197,168,109,.25);outline-offset:2px}
#crm-document-settings-page .crm-doc-footnote{margin:10px 0 0;font-size:11px;color:var(--crm-text-muted)}
#crm-document-settings-page .crm-doc-divider{height:1px;margin:4px 0;background:var(--crm-border)}
#crm-document-settings-page .crm-doc-savebar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:14px 18px;border-top:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#crm-document-settings-page .crm-doc-savebar__hint{font-size:12px;color:var(--crm-text-muted)}
#crm-document-settings-page .crm-doc-savebar__actions{display:flex;align-items:center;gap:8px}
#crm-document-settings-page .crm-doc-savebar .btn-primary-600{display:inline-flex;align-items:center;gap:8px;min-height:40px;padding:0 22px;border-radius:10px;font-size:13px;font-weight:700;background:linear-gradient(135deg,rgba(15,39,74,.96),rgba(197,168,109,.78));border:0}
#crm-document-settings-page .crm-doc-savebar .btn-primary-600 iconify-icon{font-size:16px}
#crm-document-settings-page .crm-doc-savebar .btn-primary-600:hover{filter:brightness(1.05)}
#crm-document-settings-page .text-danger-600{font-size:12px;margin-top:4px}
@media (max-width:991px){
    #crm-document-settings-page .crm-doc-tabs__grid{grid-template-columns:1fr}
    #crm-document-settings-page .crm-doc-grid__col-6,#crm-document-settings-page .crm-doc-grid__col-4{grid-column:span 12}
    #crm-document-settings-page .crm-doc-vis-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media (max-width:575px){
    #crm-document-settings-page .crm-doc-vis-grid{grid-template-columns:1fr}
    #crm-document-settings-page .crm-doc-savebar{flex-direction:column;align-items:stretch}
}
</style>
