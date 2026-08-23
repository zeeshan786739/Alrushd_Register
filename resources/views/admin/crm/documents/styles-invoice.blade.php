{{-- Shared visual styles for Invoice documents (PDF + HTML preview). Distinct from quotation. --}}
<style>
    .crm-doc-i {
        font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
        font-size: 11px;
        color: #111827;
        line-height: 1.45;
        width: 100%;
    }
    .crm-doc-i * { box-sizing: border-box; }
    .crm-doc-i-topbar {
        height: 6px;
        background: #0f274a;
        margin: -0.1px 0 16px;
    }
    .crm-doc-i-header { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .crm-doc-i-header td { vertical-align: top; border: 0; padding: 0; }
    .crm-doc-i-logo { max-height: 44px; max-width: 130px; width: auto; height: auto; margin-bottom: 6px; display: block; }
    .crm-doc-i-brand {
        font-size: 15px;
        font-weight: 700;
        color: #0f274a;
        margin: 0 0 4px;
    }
    .crm-doc-i-brand-meta { font-size: 9.5px; color: #1f2937; line-height: 1.5; }
    .crm-doc-i-title-wrap { text-align: right; }
    .crm-doc-i-title {
        font-size: 26px;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #0f274a;
        margin: 0;
        line-height: 1.1;
    }
    .crm-doc-i-number {
        font-size: 12px;
        font-weight: 700;
        color: #111827;
        margin: 6px 0 8px;
    }
    .crm-doc-i-stamp {
        display: inline-block;
        border: 2px solid #15803d;
        color: #15803d;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 4px;
    }
    .crm-doc-i-stamp-partial { border-color: #b45309; color: #b45309; }
    .crm-doc-i-stamp-overdue { border-color: #b91c1c; color: #b91c1c; }
    .crm-doc-i-stamp-sent { border-color: #1d4ed8; color: #1d4ed8; }
    .crm-doc-i-stamp-draft { border-color: #6b7280; color: #6b7280; }
    .crm-doc-i-panels { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .crm-doc-i-panels td { vertical-align: top; border: 0; padding: 0; width: 50%; }
    .crm-doc-i-label {
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #4b5563;
        margin: 0 0 8px;
    }
    .crm-doc-i-bill {
        border-left: 3px solid #0f274a;
        padding: 4px 0 4px 12px;
        margin-right: 12px;
    }
    .crm-doc-i-details {
        background: #f3f4f6;
        border-radius: 4px;
        padding: 12px 14px;
        margin-left: 8px;
    }
    .crm-doc-i-name { font-size: 13px; font-weight: 700; margin: 0 0 4px; color: #111827; }
    .crm-doc-i-muted { font-size: 10px; color: #1f2937; margin: 0 0 2px; }
    .crm-doc-i-meta { width: 100%; border-collapse: collapse; }
    .crm-doc-i-meta td { border: 0; padding: 2px 0; font-size: 10px; color: #111827; }
    .crm-doc-i-meta td:first-child { color: #4b5563; width: 40%; }
    .crm-doc-i-items {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0 10px;
    }
    .crm-doc-i-items thead { display: table-header-group; }
    .crm-doc-i-items th {
        background: #111827;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 8px 10px;
        border: 0;
        text-align: left;
    }
    .crm-doc-i-items th.num, .crm-doc-i-items td.num { text-align: right; white-space: nowrap; }
    .crm-doc-i-items td {
        border-bottom: 1px solid #e5e7eb;
        padding: 9px 10px;
        vertical-align: top;
        font-size: 10.5px;
        color: #111827;
    }
    .crm-doc-i-desc { word-wrap: break-word; overflow-wrap: anywhere; }
    .crm-doc-i-summary-wrap { width: 100%; margin-top: 8px; }
    .crm-doc-i-summary {
        width: 280px;
        margin-left: auto;
        border-collapse: collapse;
    }
    .crm-doc-i-summary td {
        border: 0;
        padding: 5px 0;
        font-size: 10.5px;
        color: #111827;
    }
    .crm-doc-i-summary td.num { text-align: right; color: #111827; }
    .crm-doc-i-summary .total-row td {
        border-top: 1px solid #d1d5db;
        padding-top: 8px;
        font-weight: 700;
        color: #111827;
    }
    .crm-doc-i-summary .balance-row td {
        background: #0f274a;
        color: #ffffff !important;
        font-size: 13px;
        font-weight: 800;
        padding: 10px 12px;
    }
    .crm-doc-i-summary .balance-row td.num { color: #ffffff !important; }
    .crm-doc-i-summary .paid-zero td {
        background: #15803d;
    }
    .crm-doc-i-payments {
        margin-top: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 10px 12px;
    }
    .crm-doc-i-payments table { width: 100%; border-collapse: collapse; }
    .crm-doc-i-payments th, .crm-doc-i-payments td {
        border: 0;
        border-bottom: 1px solid #f3f4f6;
        padding: 5px 4px;
        font-size: 9.5px;
        text-align: left;
        color: #111827;
    }
    .crm-doc-i-payments th { color: #4b5563; font-weight: 700; text-transform: uppercase; font-size: 8.5px; }
    .crm-doc-i-payments td.num { text-align: right; color: #111827; }
    .crm-doc-i-section {
        margin-top: 16px;
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
    }
    .crm-doc-i-section p {
        margin: 0;
        font-size: 10px;
        color: #1f2937;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .crm-doc-i-footer {
        margin-top: 24px;
        padding-top: 8px;
        border-top: 1px solid #e5e7eb;
        font-size: 9px;
        color: #6b7280;
        text-align: center;
    }
    .crm-doc-i-overdue-note {
        margin: 0 0 12px;
        font-size: 10px;
        font-weight: 700;
        color: #b91c1c;
    }
</style>
