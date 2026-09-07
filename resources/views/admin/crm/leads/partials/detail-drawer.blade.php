<div class="modal fade crm-lead-modal" id="crmLeadDetailModal" tabindex="-1" aria-labelledby="crmLeadDetailModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered modal-xl crm-lead-modal__dialog">
        <div class="modal-content crm-lead-modal__content">
            <div class="modal-header crm-lead-modal__header">
                <div class="crm-lead-modal__heading min-w-0">
                    <div class="crm-lead-modal__eyebrow" id="crmLeadDetailModalLabel">Lead preview</div>
                    <div class="crm-lead-modal__subtitle" data-crm-panel-subtitle>Select a lead to inspect details</div>
                </div>
                <div class="crm-lead-modal__actions">
                    <button type="button" class="crm-lead-modal__action" data-crm-modal-maximize aria-label="Maximize" title="Maximize">
                        <iconify-icon icon="solar:maximize-square-linear" data-crm-maximize-icon></iconify-icon>
                    </button>
                    <button type="button" class="crm-lead-modal__action" data-bs-dismiss="modal" aria-label="Close">
                        <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                    </button>
                </div>
            </div>
            <div class="modal-body crm-lead-modal__body" data-crm-lead-panel-host>
                <div class="crm-lead-panel-loading">
                    <div class="crm-lead-panel-loading__spinner"></div>
                    <span>Loading lead details…</span>
                </div>
            </div>
        </div>
    </div>
</div>
