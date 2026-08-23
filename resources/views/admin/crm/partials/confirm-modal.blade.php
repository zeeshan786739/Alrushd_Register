{{-- Persistent CRM confirmation modal (outside PJAX page-modals swap). --}}
<div class="modal fade" id="crmConfirmModal" tabindex="-1" aria-labelledby="crmConfirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered crm-confirm-dialog">
        <div class="modal-content crm-confirm-content border-0 shadow-lg">
            <div class="modal-body p-24 text-center">
                <div class="crm-confirm-icon" data-crm-confirm-icon-wrap aria-hidden="true">
                    <iconify-icon icon="solar:info-circle-linear" data-crm-confirm-icon></iconify-icon>
                </div>
                <h5 class="crm-confirm-title mb-8" id="crmConfirmModalTitle" data-crm-confirm-title>Confirm</h5>
                <p class="crm-confirm-message mb-0" data-crm-confirm-message></p>
                <div class="crm-confirm-note d-none mt-14" data-crm-confirm-note-wrap role="note">
                    <span data-crm-confirm-note></span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-24 pb-24 justify-content-center gap-10">
                <button type="button" class="btn btn-outline-secondary radius-8 px-18 py-10" data-bs-dismiss="modal" data-crm-confirm-cancel>
                    Cancel
                </button>
                <button type="button" class="btn radius-8 px-18 py-10" data-crm-confirm-submit>
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .crm-confirm-dialog{max-width:420px}
    .crm-confirm-content{border-radius:16px;overflow:hidden}
    .crm-confirm-icon{
        width:56px;height:56px;border-radius:999px;margin:0 auto 14px;
        display:flex;align-items:center;justify-content:center;
        background:#eff6ff;color:#1d4ed8;font-size:28px;
    }
    .crm-confirm-icon.is-success{background:#ecfdf5;color:#15803d}
    .crm-confirm-icon.is-info{background:#eff6ff;color:#1d4ed8}
    .crm-confirm-icon.is-warning{background:#fffbeb;color:#b45309}
    .crm-confirm-icon.is-danger{background:#fef2f2;color:#b91c1c}
    .crm-confirm-title{font-size:1.15rem;font-weight:700;color:#0f172a;line-height:1.35}
    .crm-confirm-message{font-size:14px;color:#475569;line-height:1.5}
    .crm-confirm-note{
        font-size:13px;line-height:1.45;color:#334155;
        background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;
        padding:10px 12px;text-align:left;
    }
    .crm-confirm-content .btn.is-loading{opacity:.7;pointer-events:none}
</style>
