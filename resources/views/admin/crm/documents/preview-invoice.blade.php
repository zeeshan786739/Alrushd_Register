@php
    use App\Support\CrmDocument;
    $organization = $organization ?? CrmDocument::organizationFor($invoice->organization_id);
@endphp
<div class="crm-doc-preview-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-10 mb-16">
        <h6 class="crm-section-title mb-0"><iconify-icon icon="solar:bill-list-linear"></iconify-icon> Invoice Preview</h6>
        <div class="d-flex flex-wrap gap-8">
            <a href="{{ route('admin.crm.invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-neutral-500 radius-8">
                <iconify-icon icon="solar:download-linear"></iconify-icon> Download PDF
            </a>
            <a href="{{ route('admin.crm.invoices.pdf', [$invoice, 'inline' => 1]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary-600 radius-8">
                <iconify-icon icon="solar:maximize-square-linear"></iconify-icon> Open PDF
            </a>
            <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-crm-print-preview="#crm-invoice-preview-sheet">
                <iconify-icon icon="solar:printer-linear"></iconify-icon> Print
            </button>
        </div>
    </div>
    <div class="crm-doc-preview-canvas">
        <div id="crm-invoice-preview-sheet" class="crm-doc-preview-sheet">
            @include('admin.crm.documents.styles-invoice', ['documentMode' => 'preview'])
            @include('admin.crm.documents.invoice-body', ['documentMode' => 'preview', 'organization' => $organization])
        </div>
    </div>
</div>
