<style>
    .crm-stat-card{position:relative;display:block;height:100%;background:var(--crm-surface,#fff);border:1px solid var(--crm-border,#e5e7eb);border-radius:14px;overflow:hidden;transition:.2s ease;text-decoration:none;color:inherit}
    .crm-stat-card--link{cursor:pointer}
    .crm-stat-card:hover{transform:translateY(-4px);box-shadow:var(--crm-shadow-lg,0 12px 32px rgba(15,39,74,.12));border-color:rgba(197,168,109,.35)}
    .crm-stat-card__glow{position:absolute;inset:0 auto auto 0;width:100%;height:4px}
    .crm-stat-card--navy .crm-stat-card__glow{background:linear-gradient(90deg,#0F274A,#3d5a80)}
    .crm-stat-card--green .crm-stat-card__glow{background:linear-gradient(90deg,#16a34a,#4ade80)}
    .crm-stat-card--gold .crm-stat-card__glow{background:linear-gradient(90deg,#C5A86D,#e8d5b0)}
    .crm-stat-card--amber .crm-stat-card__glow{background:linear-gradient(90deg,#d97706,#fbbf24)}
    .crm-stat-card--purple .crm-stat-card__glow{background:linear-gradient(90deg,#7c3aed,#a78bfa)}
    .crm-stat-card__body{padding:18px 18px 16px}
    .crm-stat-card__top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
    .crm-stat-card__info{display:flex;align-items:flex-start;gap:12px;min-width:0}
    .crm-stat-card__icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
    .crm-stat-card--navy .crm-stat-card__icon{background:rgba(15,39,74,.1);color:#0F274A}
    .crm-stat-card--green .crm-stat-card__icon{background:rgba(22,163,74,.1);color:#16a34a}
    .crm-stat-card--gold .crm-stat-card__icon{background:rgba(197,168,109,.16);color:#9a7b42}
    .crm-stat-card--amber .crm-stat-card__icon{background:rgba(217,119,6,.1);color:#d97706}
    .crm-stat-card--purple .crm-stat-card__icon{background:rgba(124,58,237,.1);color:#7c3aed}
    .crm-stat-card__label{display:block;font-size:12px;font-weight:600;color:var(--crm-text-muted,#64748b);text-transform:uppercase;letter-spacing:.04em}
    .crm-stat-card__value{display:block;font-size:1.5rem;font-weight:700;color:var(--crm-text,#0f172a);line-height:1.2;margin-top:4px}
    .crm-stat-card__meta{display:block;font-size:11px;color:var(--crm-text-muted,#64748b);margin-top:2px}
    .crm-stat-card__footer{display:flex;align-items:center;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid var(--crm-border,#e5e7eb)}
    .crm-stat-badge{font-size:11px;font-weight:600;padding:2px 8px;border-radius:999px}
    .crm-stat-badge--up{background:rgba(22,163,74,.1);color:#16a34a}
    .crm-stat-badge--down{background:rgba(220,38,38,.1);color:#dc2626}
    .crm-view-toggle{display:inline-flex;border:1px solid var(--crm-border,#e5e7eb);border-radius:10px;overflow:hidden}
    .crm-view-toggle button{border:0;background:#fff;padding:8px 12px;color:var(--crm-text-muted,#64748b)}
    .crm-view-toggle button.is-active{background:var(--crm-brand,#0F274A);color:#fff}
    .crm-grid-view .crm-list-only{display:none!important}
    .crm-list-view .crm-grid-only{display:none!important}
    .crm-card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
    .crm-record-card{background:#fff;border:1px solid var(--crm-border,#e5e7eb);border-radius:12px;padding:16px}
    .crm-line-items-table th,.crm-line-items-table td{vertical-align:middle}

    /* Shared tone tokens (pills + inline selects) */
    .crm-status-pill,
    .crm-inline-select{
        --crm-tone-bg:#f8fafc;--crm-tone-border:#cbd5e1;--crm-tone-text:#334155;--crm-tone-ring:rgba(100,116,139,.22);
    }
    .crm-status-pill--tone-neutral,.crm-inline-select[data-tone="neutral"]{
        --crm-tone-bg:#f1f5f9;--crm-tone-border:#cbd5e1;--crm-tone-text:#475569;--crm-tone-ring:rgba(100,116,139,.22);
    }
    .crm-status-pill--tone-success,.crm-inline-select[data-tone="success"]{
        --crm-tone-bg:#ecfdf5;--crm-tone-border:#86efac;--crm-tone-text:#15803d;--crm-tone-ring:rgba(21,128,61,.2);
    }
    .crm-status-pill--tone-info,.crm-inline-select[data-tone="info"]{
        --crm-tone-bg:#eff6ff;--crm-tone-border:#93c5fd;--crm-tone-text:#1d4ed8;--crm-tone-ring:rgba(29,78,216,.2);
    }
    .crm-status-pill--tone-warning,.crm-inline-select[data-tone="warning"]{
        --crm-tone-bg:#fffbeb;--crm-tone-border:#fcd34d;--crm-tone-text:#b45309;--crm-tone-ring:rgba(180,83,9,.2);
    }
    .crm-status-pill--tone-caution,.crm-inline-select[data-tone="caution"]{
        --crm-tone-bg:#fff7ed;--crm-tone-border:#fdba74;--crm-tone-text:#c2410c;--crm-tone-ring:rgba(194,65,12,.2);
    }
    .crm-status-pill--tone-danger,.crm-inline-select[data-tone="danger"]{
        --crm-tone-bg:#fef2f2;--crm-tone-border:#fca5a5;--crm-tone-text:#b91c1c;--crm-tone-ring:rgba(185,28,28,.2);
    }
    .crm-status-pill--tone-indigo,.crm-inline-select[data-tone="indigo"]{
        --crm-tone-bg:#eef2ff;--crm-tone-border:#a5b4fc;--crm-tone-text:#4338ca;--crm-tone-ring:rgba(67,56,202,.2);
    }

    .crm-status-pill{
        display:inline-flex;align-items:center;justify-content:center;
        min-height:28px;padding:4px 12px;border-radius:999px;
        border:1px solid var(--crm-tone-border);background:var(--crm-tone-bg);color:var(--crm-tone-text);
        font-size:12px;font-weight:650;letter-spacing:.01em;line-height:1.2;
        text-transform:capitalize;text-align:center;white-space:nowrap;
        vertical-align:middle;box-shadow:inset 0 1px 0 rgba(255,255,255,.55);
    }
    /* Legacy status modifiers map onto the same tone tokens */
    .crm-status-pill--draft,.crm-status-pill--medium,.crm-status-pill--low{--crm-tone-bg:#f1f5f9;--crm-tone-border:#cbd5e1;--crm-tone-text:#475569}
    .crm-status-pill--sent,.crm-status-pill--contacted,.crm-status-pill--in_progress{--crm-tone-bg:#eff6ff;--crm-tone-border:#93c5fd;--crm-tone-text:#1d4ed8}
    .crm-status-pill--accepted,.crm-status-pill--won,.crm-status-pill--paid,.crm-status-pill--active,.crm-status-pill--approved,.crm-status-pill--completed{--crm-tone-bg:#ecfdf5;--crm-tone-border:#86efac;--crm-tone-text:#15803d}
    .crm-status-pill--rejected,.crm-status-pill--lost,.crm-status-pill--cancelled,.crm-status-pill--inactive{--crm-tone-bg:#fef2f2;--crm-tone-border:#fca5a5;--crm-tone-text:#b91c1c}
    .crm-status-pill--pending,.crm-status-pill--new,.crm-status-pill--prospect,.crm-status-pill--partially_paid,.crm-status-pill--expired{--crm-tone-bg:#fffbeb;--crm-tone-border:#fcd34d;--crm-tone-text:#b45309}
    .crm-status-pill--overdue,.crm-status-pill--urgent,.crm-status-pill--high,.crm-status-pill--on_hold{--crm-tone-bg:#fff7ed;--crm-tone-border:#fdba74;--crm-tone-text:#c2410c}
    .crm-status-pill--qualified,.crm-status-pill--negotiation,.crm-status-pill--proposal_sent,.crm-status-pill--converted{--crm-tone-bg:#eef2ff;--crm-tone-border:#a5b4fc;--crm-tone-text:#4338ca}

    .crm-lead-row,.crm-clickable-row{cursor:pointer;transition:background .15s ease}
    .crm-lead-row:hover,.crm-clickable-row:hover{background:rgba(15,39,74,.035)!important}
    .crm-lead-row:focus-visible,.crm-clickable-row:focus-visible{outline:2px solid rgba(15,39,74,.35);outline-offset:-2px}

    .crm-inline-select{
        appearance:none;-webkit-appearance:none;-moz-appearance:none;
        display:inline-block;vertical-align:middle;
        min-width:124px;max-width:168px;min-height:30px;height:30px;
        padding:3px 28px 3px 12px;margin:0;
        border-radius:999px;border:1px solid var(--crm-tone-border);
        background-color:var(--crm-tone-bg);color:var(--crm-tone-text);
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M3 4.5L6 8l3-3.5'/%3E%3C/svg%3E");
        background-repeat:no-repeat;background-position:right 10px center;background-size:12px;
        font-size:12px;font-weight:650;line-height:1.2;letter-spacing:.01em;
        text-align:center;text-align-last:center;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.55);
        cursor:pointer;transition:border-color .15s ease, box-shadow .15s ease, background-color .15s ease, color .15s ease;
    }
    .crm-inline-select:hover{filter:brightness(.985)}
    .crm-inline-select:focus,.crm-inline-select:focus-visible{
        outline:none;border-color:var(--crm-tone-text);
        box-shadow:0 0 0 3px var(--crm-tone-ring), inset 0 1px 0 rgba(255,255,255,.55);
    }
    .crm-inline-select:disabled{opacity:.65;cursor:wait;filter:none}
    .crm-inline-select--owner{min-width:140px;max-width:190px;font-weight:600;text-align:left;text-align-last:left;padding-left:12px}

    /* Premium custom inline controls (Iconify-capable) */
    .crm-inline-control{position:relative;display:inline-block;vertical-align:middle;max-width:100%}
    .crm-inline-trigger{
        appearance:none;display:inline-flex;align-items:center;gap:6px;
        min-width:124px;max-width:190px;min-height:30px;height:30px;
        padding:3px 10px;margin:0;border-radius:999px;
        border:1px solid var(--crm-tone-border);background:var(--crm-tone-bg);color:var(--crm-tone-text);
        font-size:12px;font-weight:650;line-height:1.2;letter-spacing:.01em;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.55);cursor:pointer;
        transition:border-color .15s ease, box-shadow .15s ease, background-color .15s ease, color .15s ease, filter .15s ease;
    }
    .crm-inline-control[data-tone="neutral"]{--crm-tone-bg:#f1f5f9;--crm-tone-border:#cbd5e1;--crm-tone-text:#475569;--crm-tone-ring:rgba(100,116,139,.22)}
    .crm-inline-control[data-tone="success"]{--crm-tone-bg:#ecfdf5;--crm-tone-border:#86efac;--crm-tone-text:#15803d;--crm-tone-ring:rgba(21,128,61,.2)}
    .crm-inline-control[data-tone="info"]{--crm-tone-bg:#eff6ff;--crm-tone-border:#93c5fd;--crm-tone-text:#1d4ed8;--crm-tone-ring:rgba(29,78,216,.2)}
    .crm-inline-control[data-tone="warning"]{--crm-tone-bg:#fffbeb;--crm-tone-border:#fcd34d;--crm-tone-text:#b45309;--crm-tone-ring:rgba(180,83,9,.2)}
    .crm-inline-control[data-tone="caution"]{--crm-tone-bg:#fff7ed;--crm-tone-border:#fdba74;--crm-tone-text:#c2410c;--crm-tone-ring:rgba(194,65,12,.2)}
    .crm-inline-control[data-tone="danger"]{--crm-tone-bg:#fef2f2;--crm-tone-border:#fca5a5;--crm-tone-text:#b91c1c;--crm-tone-ring:rgba(185,28,28,.2)}
    .crm-inline-control[data-tone="indigo"]{--crm-tone-bg:#eef2ff;--crm-tone-border:#a5b4fc;--crm-tone-text:#4338ca;--crm-tone-ring:rgba(67,56,202,.2)}
    .crm-inline-trigger:hover{filter:brightness(.985)}
    .crm-inline-trigger:focus,.crm-inline-trigger:focus-visible,.crm-inline-control.is-open .crm-inline-trigger{
        outline:none;border-color:var(--crm-tone-text);
        box-shadow:0 0 0 3px var(--crm-tone-ring), inset 0 1px 0 rgba(255,255,255,.55);
    }
    .crm-inline-control.is-busy .crm-inline-trigger{opacity:.65;cursor:wait;pointer-events:none}
    .crm-inline-trigger__icon,.crm-inline-trigger__chevron{font-size:14px;flex-shrink:0}
    .crm-inline-trigger__label{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:110px}
    .crm-inline-control--owner .crm-inline-trigger{min-width:140px;max-width:200px}
    .crm-inline-control--owner .crm-inline-trigger__label{max-width:130px}
    .crm-inline-menu{
        position:absolute;z-index:40;top:calc(100% + 6px);left:0;min-width:100%;width:max-content;max-width:240px;
        background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:6px;
        box-shadow:0 12px 28px rgba(15,39,74,.14);max-height:260px;overflow:auto;
    }
    .crm-inline-option{
        width:100%;display:flex;align-items:center;gap:8px;border:0;background:transparent;
        padding:8px 10px;border-radius:8px;color:#0f172a;font-size:12px;font-weight:600;
        text-align:left;cursor:pointer;
    }
    .crm-inline-option:hover,.crm-inline-option:focus{background:#f8fafc;outline:none}
    .crm-inline-option.is-selected{background:rgba(15,39,74,.06)}
    .crm-inline-option iconify-icon{font-size:15px;color:#64748b;flex-shrink:0}
    .crm-inline-option__label{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .crm-inline-option__check{color:#0F274A!important}

    .crm-lead-segments__head{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:10px}
    .crm-lead-segments__title{margin:0;font-size:13px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#64748b}
    .crm-lead-segments__hint{font-size:12px;color:#94a3b8}
    .crm-lead-segments__track{
        display:flex;gap:10px;overflow-x:auto;padding-bottom:4px;scroll-snap-type:x mandatory;
        -webkit-overflow-scrolling:touch;scrollbar-width:thin;
    }
    .crm-segment-card{
        flex:0 0 auto;scroll-snap-align:start;min-width:148px;max-width:190px;
        display:flex;align-items:center;gap:10px;padding:10px 12px;
        border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;color:inherit;
        transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .crm-segment-card:hover{border-color:rgba(15,39,74,.25);box-shadow:0 6px 16px rgba(15,39,74,.08);color:inherit}
    .crm-segment-card.is-active{border-color:#0F274A;box-shadow:0 0 0 2px rgba(15,39,74,.12);background:#f8fafc}
    .crm-segment-card__icon{
        width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;
        background:#f1f5f9;color:#0F274A;font-size:18px;flex-shrink:0;
    }
    .crm-segment-card[data-tone="success"] .crm-segment-card__icon{background:#ecfdf5;color:#15803d}
    .crm-segment-card[data-tone="info"] .crm-segment-card__icon{background:#eff6ff;color:#1d4ed8}
    .crm-segment-card[data-tone="warning"] .crm-segment-card__icon{background:#fffbeb;color:#b45309}
    .crm-segment-card[data-tone="caution"] .crm-segment-card__icon{background:#fff7ed;color:#c2410c}
    .crm-segment-card[data-tone="danger"] .crm-segment-card__icon{background:#fef2f2;color:#b91c1c}
    .crm-segment-card[data-tone="indigo"] .crm-segment-card__icon{background:#eef2ff;color:#4338ca}
    .crm-segment-card[data-tone="navy"] .crm-segment-card__icon{background:rgba(15,39,74,.1);color:#0F274A}
    .crm-segment-card__body{min-width:0;display:flex;flex-direction:column;gap:2px}
    .crm-segment-card__name{font-size:12px;font-weight:650;color:#334155;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px}
    .crm-segment-card__counts{display:flex;align-items:baseline;gap:6px}
    .crm-segment-card__counts strong{font-size:15px;color:#0f172a}
    .crm-segment-card__new{font-size:11px;font-weight:650;color:#b45309;white-space:nowrap}

    .crm-category-badge{
        display:inline-flex;align-items:center;gap:4px;margin-top:4px;
        max-width:100%;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:650;
        background:#f8fafc;color:#475569;border:1px solid #e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .crm-category-badge iconify-icon{font-size:12px;flex-shrink:0}
    .crm-category-badge--success{background:#ecfdf5;color:#15803d;border-color:#86efac}
    .crm-category-badge--info{background:#eff6ff;color:#1d4ed8;border-color:#93c5fd}
    .crm-category-badge--warning{background:#fffbeb;color:#b45309;border-color:#fcd34d}
    .crm-category-badge--caution{background:#fff7ed;color:#c2410c;border-color:#fdba74}
    .crm-category-badge--danger{background:#fef2f2;color:#b91c1c;border-color:#fca5a5}
    .crm-category-badge--indigo{background:#eef2ff;color:#4338ca;border-color:#a5b4fc}
    .crm-category-badge--neutral{background:#f1f5f9;color:#475569;border-color:#cbd5e1}

    /* Lead category select + create pickers */
    .crm-category-choice-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}
    .crm-category-choice{
        position:relative;display:flex;align-items:center;gap:10px;padding:12px 14px;
        border:1px solid #e5e7eb;border-radius:12px;background:#fff;cursor:pointer;
        transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .crm-category-choice:hover{border-color:rgba(15,39,74,.28);box-shadow:0 6px 16px rgba(15,39,74,.08)}
    .crm-category-choice.is-selected{border-color:#0F274A;background:#f8fafc;box-shadow:0 0 0 2px rgba(15,39,74,.12)}
    .crm-category-choice__input{position:absolute;opacity:0;pointer-events:none}
    .crm-category-choice__icon{
        width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
        background:#f1f5f9;color:#0F274A;font-size:18px;flex-shrink:0;
    }
    .crm-category-choice[data-tone="info"] .crm-category-choice__icon{background:#eff6ff;color:#1d4ed8}
    .crm-category-choice[data-tone="success"] .crm-category-choice__icon{background:#ecfdf5;color:#15803d}
    .crm-category-choice[data-tone="warning"] .crm-category-choice__icon{background:#fffbeb;color:#b45309}
    .crm-category-choice[data-tone="caution"] .crm-category-choice__icon{background:#fff7ed;color:#c2410c}
    .crm-category-choice[data-tone="danger"] .crm-category-choice__icon{background:#fef2f2;color:#b91c1c}
    .crm-category-choice[data-tone="indigo"] .crm-category-choice__icon{background:#eef2ff;color:#4338ca}
    .crm-category-choice__name{font-size:13px;font-weight:650;color:#0f172a;line-height:1.25}
    .crm-category-choice__meta{display:block;font-size:11px;font-weight:600;color:#64748b;margin-top:2px}
    .crm-category-choice__check{margin-left:auto;color:#0F274A;opacity:0;font-size:18px}
    .crm-category-choice.is-selected .crm-category-choice__check{opacity:1}
    .crm-category-search{position:relative;max-width:420px}
    .crm-category-search iconify-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:16px;pointer-events:none}
    .crm-category-search .form-control{padding-left:38px}

    .crm-icon-picker{display:flex;flex-wrap:wrap;gap:8px}
    .crm-icon-picker__tile{
        position:relative;width:46px;height:46px;border-radius:12px;border:1px solid #e2e8f0;
        background:#fff;color:#475569;display:inline-flex;align-items:center;justify-content:center;
        font-size:20px;cursor:pointer;transition:border-color .15s ease, background .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .crm-icon-picker__tile:hover{border-color:#94a3b8;color:#0F274A}
    .crm-icon-picker__tile.is-selected{border-color:#0F274A;background:#f8fafc;color:#0F274A;box-shadow:0 0 0 2px rgba(15,39,74,.12)}
    .crm-icon-picker__check{position:absolute;top:-5px;right:-5px;font-size:14px;color:#0F274A;opacity:0;background:#fff;border-radius:50%}
    .crm-icon-picker__tile.is-selected .crm-icon-picker__check{opacity:1}

    .crm-color-picker{display:flex;flex-wrap:wrap;gap:8px}
    .crm-color-picker__swatch{
        position:relative;display:inline-flex;align-items:center;gap:8px;min-height:40px;padding:8px 12px 8px 10px;
        border:1px solid #e2e8f0;border-radius:999px;background:#fff;cursor:pointer;
        transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .crm-color-picker__swatch:hover{border-color:#94a3b8}
    .crm-color-picker__swatch.is-selected{border-color:#0F274A;box-shadow:0 0 0 2px rgba(15,39,74,.12);background:#f8fafc}
    .crm-color-picker__dot{width:16px;height:16px;border-radius:50%;flex-shrink:0;border:1px solid rgba(15,39,74,.08)}
    .crm-color-picker__dot[data-tone="info"]{background:#3b82f6}
    .crm-color-picker__dot[data-tone="success"]{background:#22c55e}
    .crm-color-picker__dot[data-tone="warning"]{background:#f59e0b}
    .crm-color-picker__dot[data-tone="indigo"]{background:#6366f1}
    .crm-color-picker__dot[data-tone="danger"]{background:#ef4444}
    .crm-color-picker__dot[data-tone="neutral"]{background:#94a3b8}
    .crm-color-picker__label{font-size:12px;font-weight:650;color:#334155}
    .crm-color-picker__check{font-size:14px;color:#0F274A;opacity:0}
    .crm-color-picker__swatch.is-selected .crm-color-picker__check{opacity:1}

    .crm-category-preview{
        display:inline-flex;align-items:center;gap:12px;min-width:200px;max-width:100%;
        padding:12px 14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;
    }
    .crm-category-preview__icon{
        width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;
        background:#f1f5f9;color:#0F274A;font-size:20px;flex-shrink:0;
    }
    .crm-category-preview[data-tone="info"] .crm-category-preview__icon{background:#eff6ff;color:#1d4ed8}
    .crm-category-preview[data-tone="success"] .crm-category-preview__icon{background:#ecfdf5;color:#15803d}
    .crm-category-preview[data-tone="warning"] .crm-category-preview__icon{background:#fffbeb;color:#b45309}
    .crm-category-preview[data-tone="indigo"] .crm-category-preview__icon{background:#eef2ff;color:#4338ca}
    .crm-category-preview[data-tone="danger"] .crm-category-preview__icon{background:#fef2f2;color:#b91c1c}
    .crm-category-preview[data-tone="neutral"] .crm-category-preview__icon{background:#f1f5f9;color:#475569}
    .crm-category-preview__body{display:flex;flex-direction:column;gap:2px;min-width:0}
    .crm-category-preview__name{font-size:14px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px}
    .crm-category-preview__meta{font-size:12px;color:#94a3b8}
    button.is-busy,[data-crm-submit-lock]:disabled{opacity:.7;cursor:wait}

    .crm-lead-name{font-weight:600;color:var(--crm-text,#0f172a)}
    .crm-lead-meta{font-size:12px;color:var(--crm-text-muted,#64748b)}
    .crm-followup-badge{
        display:inline-flex;align-items:center;justify-content:center;gap:6px;
        min-height:28px;padding:4px 12px;border-radius:999px;border:1px solid transparent;
        font-size:12px;font-weight:650;line-height:1.2;max-width:100%;white-space:nowrap;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.5);
    }
    .crm-followup-badge--none{background:#f8fafc;color:#94a3b8;border-color:#e2e8f0}
    .crm-followup-badge--upcoming{background:#eff6ff;color:#1d4ed8;border-color:#93c5fd}
    .crm-followup-badge--due-soon{background:#fffbeb;color:#b45309;border-color:#fcd34d}
    .crm-followup-badge--due-now{background:#fff7ed;color:#c2410c;border-color:#fdba74}
    .crm-followup-badge--overdue{background:#fef2f2;color:#b91c1c;border-color:#fca5a5}
    .crm-followup-dot{width:8px;height:8px;border-radius:50%;background:currentColor;flex-shrink:0}
    .crm-followup-alert{border:1px solid transparent;border-radius:12px;padding:16px 18px;display:flex;gap:14px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap}
    .crm-followup-alert__main{display:flex;gap:12px;align-items:flex-start;min-width:0}
    .crm-followup-alert__icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px}
    .crm-followup-alert__title{font-weight:700;margin:0 0 4px}
    .crm-followup-alert__meta{font-size:13px;margin:0;opacity:.9}
    .crm-followup-alert__actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .crm-followup-alert--upcoming{background:#eff6ff;border-color:#bfdbfe;color:#1e3a8a}
    .crm-followup-alert--upcoming .crm-followup-alert__icon{background:#dbeafe;color:#1d4ed8}
    .crm-followup-alert--due_soon{background:#fffbeb;border-color:#fde68a;color:#92400e}
    .crm-followup-alert--due_soon .crm-followup-alert__icon{background:#fef3c7;color:#b45309}
    .crm-followup-alert--due_now{background:#fff7ed;border-color:#fdba74;color:#9a3412}
    .crm-followup-alert--due_now .crm-followup-alert__icon{background:#ffedd5;color:#c2410c}
    .crm-followup-alert--overdue{background:#fef2f2;border-color:#fecaca;color:#991b1b}
    .crm-followup-alert--overdue .crm-followup-alert__icon{background:#fee2e2;color:#b91c1c}
    .crm-followup-alert--attention{box-shadow:0 0 0 0 rgba(185,28,28,.25)}
    @media (prefers-reduced-motion: no-preference) {
        .crm-followup-alert--attention{animation:crm-followup-pulse 2.4s ease-in-out infinite}
        .crm-followup-badge--due-now .crm-followup-dot,
        .crm-followup-badge--overdue .crm-followup-dot{animation:crm-dot-pulse 1.6s ease-in-out infinite}
    }
    @keyframes crm-followup-pulse {
        0%,100%{box-shadow:0 0 0 0 rgba(185,28,28,.18)}
        50%{box-shadow:0 0 0 6px rgba(185,28,28,0)}
    }
    @keyframes crm-dot-pulse {
        0%,100%{opacity:1;transform:scale(1)}
        50%{opacity:.45;transform:scale(.85)}
    }
    .crm-workspace-header{background:#fff;border:1px solid var(--crm-border,#e5e7eb);border-radius:14px;padding:20px 22px;box-shadow:var(--crm-shadow-sm,0 4px 16px rgba(15,39,74,.06))}
    .crm-workspace-header__top{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:flex-start}
    .crm-workspace-header__title{font-size:1.5rem;font-weight:700;margin:0 0 6px;color:#0f172a}
    .crm-workspace-header__contact{display:flex;flex-wrap:wrap;gap:12px 18px;font-size:13px;color:#64748b}
    .crm-workspace-header__contact a{color:#0F274A;text-decoration:none}
    .crm-workspace-header__badges{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
    .crm-workspace-header__actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end}
    .crm-section-title{display:flex;align-items:center;gap:8px;font-weight:600;margin:0 0 16px}
    .crm-section-title iconify-icon{font-size:18px;color:#0F274A}
    .crm-quick-group{border-top:1px solid #eef2f7;padding-top:14px;margin-top:14px}
    .crm-quick-group:first-child{border-top:0;padding-top:0;margin-top:0}
    .crm-quick-group__label{font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#64748b;margin-bottom:10px}
    .crm-activity-card{max-height:580px;display:flex;flex-direction:column}
    .crm-activity-card .card-body{overflow:hidden;display:flex;flex-direction:column;min-height:0}
    .crm-activity-scroll{overflow-y:auto;padding-right:4px;max-height:500px;scrollbar-width:thin}
    .crm-activity-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #f1f5f9}
    .crm-activity-item:last-child{border-bottom:0}
    .crm-activity-icon{width:34px;height:34px;border-radius:10px;background:#f1f5f9;color:#0F274A;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .crm-commercial-grid .crm-commercial-stat{background:#fff;border:1px solid #eef2f7;border-radius:12px;padding:14px 16px;height:100%}
    .crm-commercial-stat__label{font-size:12px;color:#64748b;margin-bottom:4px}
    .crm-commercial-stat__value{font-size:1.15rem;font-weight:700;color:#0f172a;margin:0}
    .crm-commercial-stat__hint{font-size:12px;color:#94a3b8;margin:4px 0 0}
    .crm-relation-table a{color:#0F274A;text-decoration:none;font-weight:600}
    .crm-relation-table a:hover{text-decoration:underline}
    .crm-empty-state{text-align:center;padding:28px 16px;color:#94a3b8}
    .crm-empty-state iconify-icon{font-size:28px;display:block;margin:0 auto 8px;opacity:.7}
    .crm-contact-row{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;padding:12px 0;border-bottom:1px solid #f1f5f9}
    .crm-contact-row:last-child{border-bottom:0}
    .crm-task-row{padding:14px 0;border-bottom:1px solid #f1f5f9}
    .crm-task-row:last-child{border-bottom:0}
    .crm-task-row.is-complete{opacity:.92}
    .crm-progress-bar{background:#eef2f7;border-radius:999px;overflow:hidden}
    .crm-progress-bar .progress-bar{background:#0F274A}
    .crm-toast-slot{position:fixed;right:20px;bottom:20px;z-index:1080;display:flex;flex-direction:column;gap:8px}
    .crm-toast{background:#0F274A;color:#fff;padding:10px 14px;border-radius:10px;font-size:13px;box-shadow:0 10px 30px rgba(15,39,74,.25);opacity:0;transform:translateY(8px);transition:.2s ease}
    .crm-toast.is-visible{opacity:1;transform:translateY(0)}
    .crm-toast.is-error{background:#b91c1c}
    .crm-saved-filter-chip{display:inline-flex;align-items:stretch;border:1px solid rgba(15,39,74,.18);border-radius:8px;overflow:hidden;background:#fff}
    .crm-saved-filter-chip__link{display:inline-flex;align-items:center;padding:6px 10px;font-size:12px;font-weight:600;color:#0F274A;text-decoration:none}
    .crm-saved-filter-chip__link:hover{background:rgba(15,39,74,.04);color:#0F274A}
    .crm-saved-filter-chip__remove{border:0;border-left:1px solid rgba(15,39,74,.12);background:transparent;color:#64748b;padding:0 8px;display:inline-flex;align-items:center;cursor:pointer}
    .crm-saved-filter-chip__remove:hover{background:#fee2e2;color:#b91c1c}
    .crm-saved-filter-chip__remove:disabled{opacity:.55;cursor:wait}
    .crm-attention-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border:1px solid var(--crm-border,#e5e7eb);border-radius:10px;text-decoration:none;color:inherit;margin-bottom:8px;transition:.15s ease}
    .crm-attention-row:last-child{margin-bottom:0}
    .crm-attention-row:hover{background:rgba(15,39,74,.03);border-color:rgba(15,39,74,.2);color:inherit}
    .crm-attention-row--danger{border-left:3px solid #dc2626}
    .crm-attention-row--warning{border-left:3px solid #d97706}
    .crm-doc-preview-canvas{background:linear-gradient(180deg,#e8eef5 0%,#f1f5f9 100%);border-radius:12px;padding:20px 12px;overflow:auto}
    .crm-doc-preview-sheet{background:#fff;width:min(794px,100%);margin:0 auto;padding:28px 30px;box-shadow:0 10px 30px rgba(15,39,74,.12);border:1px solid rgba(15,39,74,.06);border-radius:4px}
    @media (max-width: 767px) {
        .crm-inline-select{min-width:104px;max-width:140px}
        .crm-inline-select--owner{min-width:120px;max-width:160px}
        .crm-inline-trigger{min-width:104px;max-width:150px}
        .crm-inline-control--owner .crm-inline-trigger{min-width:120px;max-width:170px}
        .crm-segment-card{min-width:136px}
        .crm-workspace-header__actions{width:100%;justify-content:stretch}
        .crm-workspace-header__actions .btn{flex:1 1 auto;justify-content:center}
        .crm-activity-scroll{max-height:360px}
        .crm-doc-preview-canvas{padding:12px 8px}
        .crm-doc-preview-sheet{padding:18px 14px}
    }
</style>
