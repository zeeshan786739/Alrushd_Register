<style>
#um-workspace-page{font-family:var(--crm-font);color:var(--crm-text)}
#um-workspace-page .um-workspace{padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}

#um-workspace-page .crm-metrics-strip{
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px;
    margin:0;padding:14px 18px;border:0;border-bottom:1px solid var(--crm-border);border-radius:0;
    background:linear-gradient(135deg,rgba(15,39,74,.04),rgba(197,168,109,.08));
}
#um-workspace-page .crm-metrics-strip__items{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0}
#um-workspace-page .crm-metrics-strip__item{display:inline-flex;align-items:baseline;gap:6px;padding:2px 10px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#um-workspace-page .crm-metrics-strip__label{font-size:var(--crm-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#um-workspace-page .crm-metrics-strip__item strong{font-size:var(--crm-text-sm);font-weight:800;color:var(--crm-brand);font-variant-numeric:tabular-nums}
#um-workspace-page .crm-metrics-strip__sep{width:1px;height:16px;background:var(--crm-border);margin:0 2px}
#um-workspace-page .crm-metrics-strip__hint{font-size:12px;color:var(--crm-text-muted);font-weight:500}

#um-workspace-page .um-tabs-workspace{padding:14px 18px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#um-workspace-page .um-tabs-workspace__head{display:grid;gap:2px;margin-bottom:10px}
#um-workspace-page .um-tabs-workspace__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#um-workspace-page .um-tabs-workspace__sub{margin:0;font-size:12px;color:var(--crm-text-muted)}
#um-workspace-page .um-module-nav{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:8px;margin:0}
#um-workspace-page .um-module-nav .crm-source-card{
    display:grid;justify-items:center;gap:6px;padding:12px 8px;border:2px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);color:var(--crm-text-muted);text-decoration:none;text-align:center;
    transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease;
}
#um-workspace-page .um-module-nav .crm-source-card:hover{border-color:rgba(15,39,74,.22);background:var(--crm-brand-soft);transform:translateY(-1px);color:var(--crm-brand)}
#um-workspace-page .um-module-nav .crm-source-card.is-active{
    border-color:var(--crm-brand);background:linear-gradient(180deg,rgba(15,39,74,.06),rgba(197,168,109,.1));
    box-shadow:0 8px 22px rgba(15,39,74,.1);color:var(--crm-brand);
}
#um-workspace-page .um-module-nav .crm-source-card__icon{
    display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:var(--crm-surface-sunken);color:var(--crm-text-muted);
}
#um-workspace-page .um-module-nav .crm-source-card__icon iconify-icon{font-size:20px;color:inherit;--iconify-color:currentColor}
#um-workspace-page .um-module-nav .crm-source-card__label{font-size:12px;font-weight:700;line-height:1.2}
#um-workspace-page .um-module-nav .crm-source-card__count{font-size:16px;font-weight:800;font-variant-numeric:tabular-nums;color:var(--crm-text);line-height:1}
#um-workspace-page .um-module-nav .crm-source-card.is-active .crm-source-card__count{color:var(--crm-brand)}
#um-workspace-page .um-module-nav .crm-source-card--all .crm-source-card__icon{background:rgba(15,39,74,.08);color:var(--crm-brand)}
#um-workspace-page .um-module-nav .crm-source-card--all.is-active .crm-source-card__icon{background:var(--crm-brand);color:#fff}
#um-workspace-page .um-module-nav .crm-source-card--team .crm-source-card__icon{background:rgba(8,145,178,.12);color:#0e7490}
#um-workspace-page .um-module-nav .crm-source-card--team.is-active .crm-source-card__icon{background:#0891b2;color:#fff}
#um-workspace-page .um-module-nav .crm-source-card--roles .crm-source-card__icon{background:rgba(124,58,237,.12);color:#6d28d9}
#um-workspace-page .um-module-nav .crm-source-card--roles.is-active .crm-source-card__icon{background:#7c3aed;color:#fff}
#um-workspace-page .um-module-nav .crm-source-card--permissions .crm-source-card__icon{background:rgba(197,168,109,.18);color:#9a7b42}
#um-workspace-page .um-module-nav .crm-source-card--permissions.is-active .crm-source-card__icon{background:linear-gradient(135deg,#0f274a,#c5a86d);color:#fff}

#um-workspace-page .um-filter-workspace{padding:14px 18px 12px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface),rgba(15,39,74,.02))}
#um-workspace-page .um-filter-workspace--standalone{margin:0 16px 16px;border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04);border-bottom:1px solid var(--crm-border)}
#um-workspace-page .um-filter-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(160px,.55fr);gap:10px;align-items:end}
#um-workspace-page .um-ai-search{display:grid;gap:8px}
#um-workspace-page .um-ai-search__head{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px}
#um-workspace-page .um-ai-search__badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;background:linear-gradient(135deg,rgba(15,39,74,.94),rgba(197,168,109,.82));color:#fff;font-size:11px;font-weight:700}
#um-workspace-page .um-ai-search__shell{display:flex;align-items:center;gap:10px;padding:5px 5px 5px 10px;min-height:46px;border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);box-shadow:0 4px 16px rgba(15,39,74,.04)}
#um-workspace-page .um-ai-search__shell:focus-within{border-color:rgba(197,168,109,.55);box-shadow:0 0 0 3px rgba(197,168,109,.14)}
#um-workspace-page .um-ai-search__icon{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:var(--crm-surface-sunken);color:var(--crm-brand);flex-shrink:0}
#um-workspace-page .um-ai-search__input{flex:1;border:0;background:transparent;min-width:0;padding:8px 0;font-size:14px;color:var(--crm-text);outline:none}
#um-workspace-page .um-ai-search__input::placeholder{color:var(--crm-text-muted)}
#um-workspace-page .um-filter-field label{display:block;margin-bottom:4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--crm-text-muted)}
#um-workspace-page .um-filter-field .form-select{height:38px;padding:6px 12px;border:1px solid var(--crm-border);border-radius:10px;font-size:13px;background:var(--crm-surface)}

#um-workspace-page .crm-leads-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:10px 16px;border-bottom:1px solid var(--crm-border);background:var(--crm-surface-sunken)}
#um-workspace-page .crm-leads-toolbar__meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:var(--crm-text-muted);font-size:var(--crm-text-sm)}
#um-workspace-page .crm-leads-toolbar__meta strong{color:var(--crm-text);font-weight:600}
#um-workspace-page .crm-leads-toolbar__link{font-size:12px;font-weight:600;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#um-workspace-page .crm-leads-toolbar__link:hover{text-decoration:underline}

#um-workspace-page .crm-list-shell{padding:0 16px 16px}
#um-workspace-page .crm-leads-table{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#um-workspace-page .crm-leads-table__head,#um-workspace-page .um-list-row{
    display:grid;grid-template-columns:minmax(240px,2fr) minmax(120px,.75fr) minmax(110px,.65fr) 96px;
    gap:10px;align-items:center;padding:0 12px 0 14px;text-decoration:none;color:inherit;
}
#um-workspace-page .crm-leads-table__head--roles,#um-workspace-page .um-list-row.um-list-row--role{grid-template-columns:minmax(220px,1.6fr) minmax(88px,.5fr) minmax(88px,.5fr) minmax(120px,.7fr) 96px}
#um-workspace-page .crm-leads-table__head--team,#um-workspace-page .um-list-row--team{grid-template-columns:minmax(240px,2fr) minmax(120px,.75fr) minmax(110px,.65fr) 96px}
#um-workspace-page .crm-leads-table__head--overview,#um-workspace-page .um-list-row--overview{grid-template-columns:minmax(240px,2fr) minmax(120px,.75fr) 76px}
#um-workspace-page .um-list-row--team .crm-list-row__identity{grid-column:1;grid-row:1}
#um-workspace-page .um-list-row--team .crm-list-row__field:not(.crm-list-row__field--date){grid-column:2;grid-row:1}
#um-workspace-page .um-list-row--team .crm-list-row__field--date{grid-column:3;grid-row:1}
#um-workspace-page .um-list-row--team .crm-list-row__actions{grid-column:4;grid-row:1}
#um-workspace-page .um-list-row--overview .crm-list-row__identity{grid-column:1;grid-row:1}
#um-workspace-page .um-list-row--overview .crm-list-row__field{grid-column:2;grid-row:1}
#um-workspace-page .um-list-row--overview .crm-list-row__actions{grid-column:3;grid-row:1}
#um-workspace-page .um-list-row--role .crm-list-row__identity{grid-column:1;grid-row:1}
#um-workspace-page .um-list-row--role .crm-list-row__field:nth-child(3){grid-column:2;grid-row:1}
#um-workspace-page .um-list-row--role .crm-list-row__field:nth-child(4){grid-column:3;grid-row:1}
#um-workspace-page .um-list-row--role .crm-list-row__field--date{grid-column:4;grid-row:1}
#um-workspace-page .um-list-row--role .crm-list-row__actions{grid-column:5;grid-row:1}
#um-workspace-page .crm-list-row__chevron{text-decoration:none}
#um-workspace-page .crm-leads-table__head{
    position:sticky;top:0;z-index:2;min-height:38px;padding-top:8px;padding-bottom:8px;
    color:var(--crm-text-muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
    background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));border-bottom:1px solid var(--crm-border);
}
#um-workspace-page .crm-leads-list{display:flex;flex-direction:column;gap:0}
#um-workspace-page .crm-leads-list > .um-list-row,#um-workspace-page .crm-leads-list > a.um-list-row,#um-workspace-page .crm-leads-list > article.um-list-row{
    display:grid!important;width:100%;margin:0!important;box-sizing:border-box;text-decoration:none;color:inherit;line-height:normal;
}
#um-workspace-page .um-list-row{position:relative;min-height:0;padding-top:10px;padding-bottom:10px;border-bottom:1px solid var(--crm-border);cursor:pointer;transition:background .12s ease;align-items:center}
#um-workspace-page .crm-leads-list .um-list-row:hover,#um-workspace-page .crm-leads-list a.um-list-row:hover{background:var(--crm-surface-sunken)!important}
#um-workspace-page .um-list-row:last-child{border-bottom:0}
#um-workspace-page .um-list-row:hover{background:var(--crm-surface-sunken)}
#um-workspace-page .um-list-row:nth-child(even){background:rgba(9,30,66,.018)}
#um-workspace-page .um-list-row:nth-child(even):hover{background:var(--crm-surface-sunken)}
#um-workspace-page .crm-list-row__priority-rail{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0;background:linear-gradient(180deg,#1a3a5c,var(--crm-brand))}
#um-workspace-page .um-list-row--role .crm-list-row__priority-rail{background:linear-gradient(180deg,#7c3aed,#a78bfa)}
#um-workspace-page .um-list-row--protected .crm-list-row__priority-rail{background:linear-gradient(180deg,#fb923c,#ea580c)}
#um-workspace-page .crm-list-row__identity{display:flex;align-items:flex-start;gap:10px;min-width:0}
#um-workspace-page .um-user-avatar{width:36px;height:36px;border-radius:999px;display:grid;place-items:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
#um-workspace-page .crm-list-row__identity-copy{display:grid;gap:4px;min-width:0}
#um-workspace-page .crm-list-row__name-row{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-width:0}
#um-workspace-page .crm-list-row__name{font-size:13px;font-weight:700;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
#um-workspace-page .crm-list-row__contact-line{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px;min-width:0}
#um-workspace-page .crm-list-row__contact{display:inline-flex;align-items:center;gap:4px;min-width:0;color:var(--crm-text-muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#um-workspace-page .crm-list-row__field{display:flex;align-items:center;flex-wrap:wrap;gap:6px;min-width:0}
#um-workspace-page .crm-list-row__field--date{display:flex;flex-direction:column;align-items:flex-start;gap:1px}
#um-workspace-page .crm-list-row__date{font-size:12px;font-weight:600;color:var(--crm-text)}
#um-workspace-page .crm-list-row__date-sub{font-size:10px;color:var(--crm-text-muted)}
#um-workspace-page .crm-list-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:4px}
#um-workspace-page .crm-list-row__action-group{display:flex;align-items:center;gap:2px;opacity:0;transition:opacity .12s ease}
#um-workspace-page .um-list-row:hover .crm-list-row__action-group,#um-workspace-page .um-list-row:focus-within .crm-list-row__action-group{opacity:1}
#um-workspace-page .crm-list-row__chevron{display:grid;place-items:center;width:24px;height:24px;padding:0;border:0;border-radius:8px;background:transparent;color:var(--crm-text-muted);font-size:16px;cursor:pointer}
#um-workspace-page .um-list-row:hover .crm-list-row__chevron{color:var(--crm-brand)}
#um-workspace-page .um-role-badge{display:inline-flex;align-items:center;min-height:24px;padding:2px 10px;border-radius:999px;background:var(--crm-brand-soft);color:var(--crm-brand);font-size:11px;font-weight:700}
#um-workspace-page .um-you-badge{display:inline-flex;align-items:center;padding:1px 7px;border-radius:999px;background:rgba(22,163,74,.12);color:#15803d;font-size:10px;font-weight:700;text-transform:uppercase}
#um-workspace-page .um-system-badge{display:inline-flex;align-items:center;padding:1px 7px;border-radius:999px;background:#FFF7D6;color:#974F0C;font-size:10px;font-weight:700;text-transform:uppercase}
#um-workspace-page .um-muted-pill{font-size:11px;color:var(--crm-text-muted);font-weight:600}
#um-workspace-page .crm-leads-list-empty{display:grid;place-items:center;gap:6px;padding:48px 16px;color:var(--crm-text-muted);text-align:center}
#um-workspace-page .crm-leads-list-empty strong{color:var(--crm-text);font-weight:600}
#um-workspace-page .crm-list-action{width:28px;height:28px;border:0;border-radius:8px;display:grid;place-items:center;background:transparent;color:var(--crm-text-muted);text-decoration:none}
#um-workspace-page .crm-list-action:hover{background:var(--crm-surface-sunken);color:var(--crm-text)}
#um-workspace-page .crm-list-action.is-delete:hover{background:#FFEBE6;color:#BF2600}

#um-workspace-page .um-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,340px);gap:0;align-items:start}
#um-workspace-page .um-layout__main{min-width:0;border-right:1px solid var(--crm-border)}
#um-workspace-page .um-layout__aside{padding:16px;display:grid;gap:14px;background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#um-workspace-page .um-side-panel{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#um-workspace-page .um-side-panel__head{padding:12px 14px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#um-workspace-page .um-side-panel__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#um-workspace-page .um-side-panel__sub{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#um-workspace-page .um-side-panel__link{display:inline-block;margin-top:8px;font-size:12px;font-weight:600;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#um-workspace-page .um-role-mini{padding:12px 14px;border-bottom:1px solid var(--crm-border)}
#um-workspace-page .um-role-mini:last-child{border-bottom:0}
#um-workspace-page .um-role-mini__head{display:flex;align-items:center;gap:10px;margin-bottom:8px}
#um-workspace-page .um-role-mini__head strong{font-size:13px;color:var(--crm-text)}
#um-workspace-page .um-role-mini__stats{display:flex;gap:12px;font-size:11px;color:var(--crm-text-muted);margin-bottom:8px}
#um-workspace-page .um-role-mini__link{font-size:12px;font-weight:600;color:var(--crm-link,var(--crm-brand));text-decoration:none}
#um-workspace-page .um-tip-card{display:flex;gap:10px;padding:14px;border:1px dashed var(--crm-border);border-radius:12px;background:rgba(15,39,74,.015);color:var(--crm-text-muted);font-size:12px;line-height:1.45}
#um-workspace-page .um-tip-card iconify-icon{font-size:20px;color:var(--crm-brand);flex-shrink:0;margin-top:2px}
#um-workspace-page .um-tip-card strong{display:block;color:var(--crm-text);margin-bottom:4px}

#um-workspace-page .um-info-banner{display:flex;gap:10px;margin:16px;padding:12px 14px;border:1px solid var(--crm-border);border-radius:12px;background:linear-gradient(135deg,rgba(15,39,74,.03),rgba(197,168,109,.05));color:var(--crm-text-muted);font-size:13px}
#um-workspace-page .um-info-banner iconify-icon{flex-shrink:0;font-size:18px;color:var(--crm-brand)}
#um-workspace-page .um-info-banner strong{display:block;color:var(--crm-text);margin-bottom:2px}
#um-workspace-page .um-info-banner p{margin:0;font-size:12px}
#um-workspace-page .um-panel--flat{border:0;box-shadow:none;border-radius:0;background:transparent;padding:0 16px 16px}

#um-workspace-page .crm-leads-table--permissions{background:var(--crm-surface-sunken)}
#um-workspace-page .um-perm-catalog-shell{padding:16px}
#um-workspace-page .um-perm-catalog{display:flex;flex-direction:column;gap:14px;padding:0}
#um-workspace-page .um-perm-group{
    border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);
    overflow:hidden;box-shadow:0 4px 14px rgba(15,39,74,.04);
}
#um-workspace-page .um-perm-group__head{
    padding:14px 16px;background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface));
    border-bottom:1px solid var(--crm-border);
}
#um-workspace-page .um-perm-group__title{font-size:14px;font-weight:700;color:var(--crm-text)}
#um-workspace-page .um-perm-group__count{
    min-width:28px;height:28px;padding:0 8px;border-radius:999px;font-size:12px;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;
    background:var(--crm-brand-soft);color:var(--crm-brand);border:0;
}
#um-workspace-page .um-perm-chip-grid{
    display:grid;grid-template-columns:repeat(auto-fill,minmax(min(240px,100%),1fr));
    gap:12px;padding:16px;
}
#um-workspace-page .um-perm-chip{
    padding:11px 14px;border-radius:12px;border:1px solid var(--crm-border);
    background:var(--crm-surface);font-size:13px;line-height:1.35;
    transition:border-color .12s ease,box-shadow .12s ease;
}
#um-workspace-page .um-perm-chip:hover{border-color:rgba(15,39,74,.18);box-shadow:0 4px 12px rgba(15,39,74,.06)}
#um-workspace-page .um-info-banner{margin:16px 16px 0}

#um-workspace-page .um-form-page{padding-bottom:16px}
#um-workspace-page .um-form-grid{display:grid;grid-template-columns:minmax(280px,.85fr) minmax(0,1.35fr);gap:16px;padding:0 16px 16px;align-items:start}
#um-workspace-page .um-form-grid--role{grid-template-columns:minmax(260px,.75fr) minmax(0,1.45fr)}
#um-workspace-page .um-form-card{border:1px solid var(--crm-border);border-radius:14px;background:var(--crm-surface);overflow:hidden;box-shadow:0 8px 24px rgba(15,39,74,.04)}
#um-workspace-page .um-form-card--sticky{position:sticky;top:12px}
#um-workspace-page .um-form-card__head{display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-bottom:1px solid var(--crm-border);background:linear-gradient(180deg,var(--crm-surface-sunken),var(--crm-surface))}
#um-workspace-page .um-form-card__icon{display:grid;place-items:center;width:36px;height:36px;border-radius:12px;background:rgba(15,39,74,.08);color:var(--crm-brand);flex-shrink:0}
#um-workspace-page .um-form-card__icon iconify-icon{font-size:20px}
#um-workspace-page .um-form-card__title{margin:0;font-size:14px;font-weight:700;color:var(--crm-text)}
#um-workspace-page .um-form-card__sub{margin:4px 0 0;font-size:12px;color:var(--crm-text-muted)}
#um-workspace-page .um-form-card__body{padding:16px 18px}
#um-workspace-page .um-form-card__body--flush{padding:0}
#um-workspace-page .um-form-card__error{margin:0 16px 12px}
#um-workspace-page .um-form-fields{display:grid;gap:14px}
#um-workspace-page .um-form-field__label{display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:var(--crm-text)}
#um-workspace-page .um-form-field .form-control{height:42px;border:1px solid var(--crm-border);border-radius:10px;font-size:14px}
#um-workspace-page .um-form-field .form-control:focus{border-color:rgba(197,168,109,.75);box-shadow:0 0 0 3px rgba(197,168,109,.18)}
#um-workspace-page .um-form-note{display:flex;gap:10px;margin-top:14px;padding:12px 14px;border:1px solid rgba(8,145,178,.2);border-radius:12px;background:rgba(8,145,178,.06);color:var(--crm-text-muted);font-size:12px;line-height:1.45}
#um-workspace-page .um-form-note iconify-icon{flex-shrink:0;font-size:18px;color:#0891b2;margin-top:1px}
#um-workspace-page .um-form-save-bar{
    display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:10px;
    margin:16px 16px 0;padding:12px 16px;border:1px solid var(--crm-border);border-radius:14px;
    background:var(--crm-surface);box-shadow:0 8px 24px rgba(15,39,74,.04);
}
#um-workspace-page .um-form-save-bar .fc-btn{margin:0}
#um-workspace-page .um-role-mini__stats--card{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;padding:12px;border:1px solid var(--crm-border);border-radius:12px;background:var(--crm-surface-sunken)}
#um-workspace-page .um-role-mini__stats--card div{display:grid;gap:2px}
#um-workspace-page .um-role-mini__stats--card strong{font-size:18px;font-weight:800;color:var(--crm-text);line-height:1}
#um-workspace-page .um-role-mini__stats--card span{font-size:11px;color:var(--crm-text-muted)}
#um-workspace-page .um-form-list-shell{padding:0;border:0;border-radius:0;box-shadow:none;background:transparent}
#um-workspace-page .um-form-list-shell .crm-leads-table{border:0;border-radius:0;box-shadow:none}
#um-workspace-page .crm-leads-table__head--picker{grid-template-columns:minmax(220px,1.6fr) minmax(120px,.7fr) 40px}
#um-workspace-page .um-role-picker-row{
    position:relative;display:grid;grid-template-columns:minmax(220px,1.6fr) minmax(120px,.7fr) 40px;
    gap:10px;align-items:center;margin:0;padding:8px 12px 8px 14px;border-bottom:1px solid var(--crm-border);
    cursor:pointer;transition:background .12s ease;
}
#um-workspace-page .um-role-picker-row:last-child{border-bottom:0}
#um-workspace-page .um-role-picker-row:hover{background:var(--crm-surface-sunken)}
#um-workspace-page .um-role-picker-row:has(.um-role-picker-row__check:checked){background:linear-gradient(90deg,rgba(15,39,74,.04),rgba(197,168,109,.08))}
#um-workspace-page .um-role-picker-row:has(.um-role-picker-row__check:checked) .crm-list-row__priority-rail{background:linear-gradient(180deg,#16a34a,#15803d)}
#um-workspace-page .um-role-picker-row__check{position:absolute;opacity:0;pointer-events:none;width:0;height:0}
#um-workspace-page .um-role-picker-row__identity{display:flex;align-items:center;gap:10px;min-width:0}
#um-workspace-page .um-role-picker-row__avatar{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0}
#um-workspace-page .um-role-picker-row__body{display:grid;gap:2px;min-width:0}
#um-workspace-page .um-role-picker-row__body strong{font-size:13px;color:var(--crm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#um-workspace-page .um-role-picker-row__body span{font-size:11px;color:var(--crm-text-muted)}
#um-workspace-page .um-role-picker-row__meta{font-size:12px;font-weight:600;color:var(--crm-text-muted)}
#um-workspace-page .um-role-picker-row__mark{display:grid;place-items:center;width:28px;height:28px;color:var(--crm-border);font-size:20px;transition:color .12s ease}
#um-workspace-page .um-role-picker-row:has(.um-role-picker-row__check:checked) .um-role-picker-row__mark{color:#16a34a}

#um-workspace-page .um-perm-picker--premium .um-perm-picker__filter{padding:0;border-bottom:1px solid var(--crm-border)}
#um-workspace-page .um-perm-picker--premium .um-perm-picker__filter .um-filter-workspace{padding:14px 16px 12px;margin:0;border:0}
#um-workspace-page .um-perm-picker__meta{display:inline-flex;align-items:baseline;gap:4px;font-size:12px;color:var(--crm-text-muted)}
#um-workspace-page .um-perm-picker__meta strong{font-size:13px;font-weight:800;color:var(--crm-brand)}
#um-workspace-page .um-perm-picker__catalog{padding:16px;background:var(--crm-surface-sunken)}
#um-workspace-page .um-perm-picker--premium .um-perm-catalog{gap:14px}
#um-workspace-page .um-perm-group__actions{display:inline-flex;align-items:center;gap:10px}
#um-workspace-page .um-perm-group__toggle{border:0;background:transparent;color:var(--crm-link,var(--crm-brand));font-size:12px;font-weight:700;cursor:pointer;padding:0}
#um-workspace-page .um-perm-chip-grid--picker{padding:16px}
#um-workspace-page .um-perm-chip--picker{
    position:relative;display:flex;align-items:center;flex-wrap:wrap;gap:8px;cursor:pointer;margin:0;
}
#um-workspace-page .um-perm-chip__check{position:absolute;opacity:0;pointer-events:none;width:0;height:0}
#um-workspace-page .um-perm-chip__label{flex:1;min-width:0;font-size:13px;color:var(--crm-text)}
#um-workspace-page .um-perm-chip__tick{display:grid;place-items:center;width:22px;height:22px;color:var(--crm-border);font-size:18px;transition:color .12s ease}
#um-workspace-page .um-perm-chip--picker:has(.um-perm-chip__check:checked){border-color:var(--crm-brand);background:var(--crm-brand-soft);box-shadow:0 0 0 1px rgba(15,39,74,.06)}
#um-workspace-page .um-perm-chip--picker:has(.um-perm-chip__check:checked) .um-perm-chip__tick{color:#16a34a}

@media(max-width:1100px){
    #um-workspace-page .um-form-grid,#um-workspace-page .um-form-grid--role{grid-template-columns:1fr}
    #um-workspace-page .um-form-card--sticky{position:static}
    #um-workspace-page .um-layout{grid-template-columns:1fr}
    #um-workspace-page .um-layout__main{border-right:0;border-bottom:1px solid var(--crm-border)}
    #um-workspace-page .um-filter-grid{grid-template-columns:1fr}
}
@media(max-width:768px){
    #um-workspace-page .um-form-save-bar{margin:12px 0 0;padding:12px;border-radius:12px}
    #um-workspace-page .crm-leads-table__head--picker{display:none}
    #um-workspace-page .um-role-picker-row{grid-template-columns:1fr 28px;gap:8px;padding:12px}
    #um-workspace-page .um-role-picker-row__meta{display:none}
    #um-workspace-page .um-perm-catalog-shell{padding:12px}
    #um-workspace-page .um-perm-chip-grid{gap:10px;padding:12px}
    #um-workspace-page .crm-leads-table__head{display:none}
    #um-workspace-page .um-list-row{grid-template-columns:1fr 24px!important;gap:8px;padding:12px}
    #um-workspace-page .um-list-row .crm-list-row__identity{grid-column:1}
    #um-workspace-page .um-list-row .crm-list-row__field{display:none}
    #um-workspace-page .um-list-row .crm-list-row__actions{grid-column:2;align-self:center}
    #um-workspace-page .um-list-row .crm-list-row__action-group{opacity:1}
    #um-workspace-page .um-module-nav{grid-template-columns:repeat(2,minmax(0,1fr))}
}
</style>
