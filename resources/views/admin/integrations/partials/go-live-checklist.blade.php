<div class="card radius-12 shadow-2 border-0 mt-24">
    <div class="card-body p-24">
        <h6 class="mb-16">Staging go-live checklist</h6>
        <ul class="list-unstyled mb-0">
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="{{ ($configOk ?? false) ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}" class="{{ ($configOk ?? false) ? 'text-success-main' : 'text-danger-main' }} mt-2"></iconify-icon>
                <span>Environment variables set (<code>META_APP_ID</code>, <code>META_APP_SECRET</code>, <code>META_WEBHOOK_VERIFY_TOKEN</code>)</span>
            </li>
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="{{ ($connection->isConnected() ?? false) ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}" class="{{ ($connection->isConnected() ?? false) ? 'text-success-main' : 'text-danger-main' }} mt-2"></iconify-icon>
                <span>Facebook Page connected for this school</span>
            </li>
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="{{ ($connection->webhook_subscribed_at ?? false) ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}" class="{{ ($connection->webhook_subscribed_at ?? false) ? 'text-success-main' : 'text-danger-main' }} mt-2"></iconify-icon>
                <span>Webhook subscribed to <code>leadgen</code> in Meta Developer App</span>
            </li>
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="{{ ($connection->formMappings->where('is_active', true)->count() ?? 0) > 0 ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}" class="{{ ($connection->formMappings->where('is_active', true)->count() ?? 0) > 0 ? 'text-success-main' : 'text-danger-main' }} mt-2"></iconify-icon>
                <span>At least one Lead Form mapped (student, teacher, etc.)</span>
            </li>
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="solar:info-circle-linear" class="text-primary-600 mt-2"></iconify-icon>
                <span>OAuth scopes in <code>META_OAUTH_SCOPES</code> must match permissions on your Meta app. Do not request <code>leads_retrieval</code> or <code>pages_manage_metadata</code> until Meta approves them.</span>
            </li>
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="solar:info-circle-linear" class="text-primary-600 mt-2"></iconify-icon>
                <span><code>leads_retrieval</code> is required to fetch lead field data after webhook. Request via Meta <strong>Publish</strong> → App Review after Connect works.</span>
            </li>
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="solar:info-circle-linear" class="text-primary-600 mt-2"></iconify-icon>
                <span>Queue worker running: <code>php artisan queue:work</code></span>
            </li>
            <li class="d-flex align-items-start gap-8">
                <iconify-icon icon="solar:info-circle-linear" class="text-primary-600 mt-2"></iconify-icon>
                <span>Send a test lead from Meta Lead Ads Testing Tool, then verify CRM → Leads</span>
            </li>
        </ul>
        @can('manage integrations')
        <form method="POST" action="{{ route('admin.integrations.facebook.test-connection') }}" class="mt-20">
            @csrf
            <button type="submit" class="btn btn-outline-primary-600 radius-8">Run connection check</button>
        </form>
        @endcan
    </div>
</div>
