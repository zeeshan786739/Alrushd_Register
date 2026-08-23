{{-- Shared visual styles for Quotation documents (PDF + HTML preview). --}}
<style>
    .crm-doc-q {
        font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
        font-size: 11px;
        color: #0f172a;
        line-height: 1.45;
        width: 100%;
    }
    .crm-doc-q * { box-sizing: border-box; }
    .crm-doc-q-header {
        width: 100%;
        border-bottom: 2px solid #0f274a;
        padding-bottom: 14px;
        margin-bottom: 18px;
    }
    .crm-doc-q-header table { width: 100%; border-collapse: collapse; }
    .crm-doc-q-header td { vertical-align: top; border: 0; padding: 0; }
    .crm-doc-q-brand-name {
        font-size: 16px;
        font-weight: 700;
        color: #0f274a;
        margin: 0 0 4px;
        letter-spacing: 0.02em;
    }
    .crm-doc-q-brand-meta { font-size: 9.5px; color: #1e293b; line-height: 1.5; }
    .crm-doc-q-logo { max-height: 48px; max-width: 140px; width: auto; height: auto; margin-bottom: 8px; display: block; }
    .crm-doc-q-title-block { text-align: right; }
    .crm-doc-q-title {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.14em;
        color: #0f274a;
        margin: 0 0 4px;
        text-transform: uppercase;
    }
    .crm-doc-q-number {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 6px;
    }
    .crm-doc-q-status {
        display: inline-block;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 3px 8px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #475569;
    }
    .crm-doc-q-status-sent { background: #dbeafe; color: #1d4ed8; }
    .crm-doc-q-status-accepted { background: #dcfce7; color: #15803d; }
    .crm-doc-q-status-rejected { background: #fee2e2; color: #b91c1c; }
    .crm-doc-q-status-draft { background: #f1f5f9; color: #64748b; }
    .crm-doc-q-grid { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .crm-doc-q-grid td { vertical-align: top; border: 0; padding: 0; width: 50%; }
    .crm-doc-q-label {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #475569;
        margin: 0 0 6px;
    }
    .crm-doc-q-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px 14px;
        min-height: 72px;
    }
    .crm-doc-q-card-right { margin-left: 10px; }
    .crm-doc-q-card-left { margin-right: 10px; }
    .crm-doc-q-name { font-size: 13px; font-weight: 700; color: #0f172a; margin: 0 0 4px; }
    .crm-doc-q-muted { font-size: 10px; color: #1e293b; margin: 0 0 2px; }
    .crm-doc-q-meta-table { width: 100%; border-collapse: collapse; }
    .crm-doc-q-meta-table td { border: 0; padding: 2px 0; font-size: 10px; color: #0f172a; }
    .crm-doc-q-meta-table td:first-child { color: #475569; width: 42%; }
    .crm-doc-q-items {
        width: 100%;
        border-collapse: collapse;
        margin: 6px 0 12px;
    }
    .crm-doc-q-items thead { display: table-header-group; }
    .crm-doc-q-items th {
        background: #0f274a;
        color: #ffffff;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 8px 10px;
        border: 0;
        text-align: left;
    }
    .crm-doc-q-items th.num, .crm-doc-q-items td.num { text-align: right; white-space: nowrap; }
    .crm-doc-q-items td {
        border-bottom: 1px solid #e2e8f0;
        padding: 9px 10px;
        vertical-align: top;
        font-size: 10.5px;
        color: #0f172a;
    }
    .crm-doc-q-items tr:nth-child(even) td { background: #f8fafc; }
    .crm-doc-q-desc { word-wrap: break-word; overflow-wrap: anywhere; }
    .crm-doc-q-totals-wrap { width: 100%; margin-top: 8px; }
    .crm-doc-q-totals {
        width: 260px;
        margin-left: auto;
        border-collapse: collapse;
    }
    .crm-doc-q-totals td {
        border: 0;
        padding: 5px 0;
        font-size: 10.5px;
        color: #0f172a;
    }
    .crm-doc-q-totals td.num { text-align: right; color: #0f172a; }
    .crm-doc-q-totals .grand td {
        border-top: 2px solid #0f274a;
        padding-top: 10px;
        font-size: 13px;
        font-weight: 700;
        color: #0f274a;
    }
    .crm-doc-q-section {
        margin-top: 18px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
    }
    .crm-doc-q-section p {
        margin: 0;
        font-size: 10px;
        color: #1e293b;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .crm-doc-q-footer {
        margin-top: 28px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        font-size: 9px;
        color: #64748b;
        text-align: center;
    }
</style>
