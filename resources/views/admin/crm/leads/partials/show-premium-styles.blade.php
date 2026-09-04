<style>
    .crm-lead-detail {
        --lead-ink: #10233f;
        --lead-muted: #66758c;
        --lead-line: #dfe7f1;
        --lead-soft: #f5f8fc;
        --lead-accent: #2563eb;
        --lead-shadow: 0 10px 30px rgba(26, 49, 82, .07);
        color: var(--lead-ink);
    }

    .crm-lead-detail .crm-workspace-header {
        border: 1px solid #dce6f2;
        border-left: 4px solid var(--lead-accent);
        border-radius: 16px;
        padding: 24px 26px;
        box-shadow: var(--lead-shadow);
        background: linear-gradient(115deg, #fff 0%, #fff 72%, #f2f7ff 100%);
    }

    .crm-lead-detail .crm-workspace-header__title {
        color: #0b1f3a;
        font-size: clamp(1.45rem, 2vw, 1.85rem);
        letter-spacing: -.025em;
    }

    .crm-lead-detail .crm-workspace-header__contact {
        margin-top: 8px;
        color: #53657d;
    }

    .crm-lead-detail .crm-workspace-header__contact span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .crm-lead-detail .crm-workspace-header__actions .btn {
        min-height: 42px;
        border-radius: 10px !important;
        font-weight: 650;
    }

    .crm-lead-detail .crm-followup-alert {
        border-radius: 14px;
        box-shadow: 0 6px 20px rgba(37, 99, 235, .06);
    }

    .crm-lead-detail .card {
        border: 1px solid var(--lead-line) !important;
        border-radius: 16px !important;
        box-shadow: var(--lead-shadow) !important;
        background: #fff;
    }

    .crm-lead-detail .card-body { padding: 24px !important; }

    .crm-lead-detail .crm-section-title,
    .crm-lead-detail .crm-detail-section__title {
        color: #122844;
        font-size: 15px;
        font-weight: 750;
        letter-spacing: -.01em;
    }

    .crm-lead-detail .crm-section-title {
        padding-bottom: 13px;
        border-bottom: 1px solid #e8eef6;
    }

    .crm-lead-detail .crm-section-title iconify-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px;
        border-radius: 9px;
        background: #eaf2ff;
        color: #1d5fd1;
    }

    .crm-lead-detail .crm-detail-facts strong,
    .crm-lead-detail .card .text-secondary-light.d-block {
        margin-bottom: 4px;
        color: #77859a !important;
        font-size: 11px !important;
        font-weight: 700;
        letter-spacing: .045em;
        text-transform: uppercase;
    }

    .crm-lead-detail .crm-detail-secondary-card { overflow: hidden; }
    .crm-lead-detail .crm-detail-secondary-card > .card-body { padding: 0 !important; }
    .crm-lead-detail .crm-detail-section { padding: 22px 24px; }
    .crm-lead-detail .crm-detail-section + .crm-detail-section { border-top: 1px solid var(--lead-line); }
    .crm-lead-detail .crm-detail-section__title { margin: 0 0 17px; }
    .crm-lead-detail .crm-detail-section details { border-top: 1px dashed #d6e0ec; padding-top: 14px; }

    .crm-lead-detail .crm-lead-command-grid > [class*="col-"] { align-self: flex-start; }
    .crm-lead-detail .crm-lead-command-grid__actions { order: 1; }
    .crm-lead-detail .crm-lead-command-grid__notes { order: 2; }
    .crm-lead-detail .crm-lead-command-grid__activity { order: 3; }
    .crm-lead-detail .crm-notes-card,
    .crm-lead-detail .crm-quick-actions-card { height: auto !important; }

    .crm-lead-detail .crm-notes-card .border-bottom {
        margin: 0 !important;
        padding: 14px 2px !important;
        border-color: #e9eef5 !important;
    }

    .crm-lead-detail .crm-notes-card .border-bottom:first-of-type { padding-top: 0 !important; }
    .crm-lead-detail .crm-notes-card textarea {
        min-height: 92px;
        resize: vertical;
        border-color: #ccd8e7;
        background: #f9fbfe;
    }
    .crm-lead-detail .crm-notes-card textarea:focus { background: #fff; }

    .crm-lead-detail .crm-quick-actions-card {
        border-top: 3px solid #193b68 !important;
        background: linear-gradient(135deg, #fff 0%, #f7faff 100%);
    }
    .crm-lead-detail .crm-quick-actions-card > .card-body {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
    .crm-lead-detail .crm-quick-actions-card .crm-section-title {
        grid-column: 1 / -1;
        margin-bottom: 0;
    }
    .crm-lead-detail .crm-quick-group {
        min-width: 0;
        margin-top: 0;
        padding: 18px;
        border: 1px solid #e0e8f2 !important;
        border-radius: 13px;
        background: rgba(255, 255, 255, .94);
    }
    .crm-lead-detail .crm-quick-group__label { color: #526781; }
    .crm-lead-detail .crm-quick-actions-card .form-control,
    .crm-lead-detail .crm-quick-actions-card .form-select {
        min-height: 42px;
        border-color: #d5dfeb;
        background-color: #fbfcfe;
    }
    .crm-lead-detail .crm-quick-actions-card .btn { min-height: 40px; font-weight: 650; }

    .crm-lead-detail .crm-activity-card {
        width: 100%;
        border-top: 3px solid #93a7bf !important;
    }

    @media (min-width: 992px) {
        .crm-lead-detail .crm-activity-card { height: 100%; max-height: 560px; }
    }

    @media (max-width: 991.98px) {
        .crm-lead-detail .crm-quick-actions-card > .card-body { grid-template-columns: 1fr; }
    }

    @media (max-width: 767.98px) {
        .crm-lead-detail .crm-workspace-header { padding: 20px; }
        .crm-lead-detail .card-body,
        .crm-lead-detail .crm-detail-section { padding: 19px !important; }
    }
</style>
