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
    .crm-status-pill{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;text-transform:capitalize}
    .crm-status-pill--draft{background:#f1f5f9;color:#475569}
    .crm-status-pill--sent,.crm-status-pill--contacted{background:#dbeafe;color:#1d4ed8}
    .crm-status-pill--accepted,.crm-status-pill--won,.crm-status-pill--paid,.crm-status-pill--active,.crm-status-pill--approved,.crm-status-pill--completed{background:#dcfce7;color:#15803d}
    .crm-status-pill--rejected,.crm-status-pill--lost,.crm-status-pill--cancelled,.crm-status-pill--inactive{background:#fee2e2;color:#b91c1c}
    .crm-status-pill--partially_paid,.crm-status-pill--in_progress,.crm-status-pill--pending,.crm-status-pill--new,.crm-status-pill--prospect{background:#fef3c7;color:#b45309}
    .crm-status-pill--overdue,.crm-status-pill--urgent,.crm-status-pill--high{background:#ffedd5;color:#c2410c}
</style>
