<div class="card radius-12 shadow-2 border-0 mt-24">
    <div class="card-body p-24">
        <h6 class="mb-8">Setup progress</h6>
        <p class="text-sm text-secondary-light mb-16">Complete these steps so Facebook leads flow into your CRM automatically.</p>
        <ul class="list-unstyled mb-0">
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="{{ ($connection->isConnected() ?? false) ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}" class="{{ ($connection->isConnected() ?? false) ? 'text-success-main' : 'text-danger-main' }} mt-2"></iconify-icon>
                <span>Facebook Page connected for this school</span>
            </li>
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="{{ (($connection->webhook_subscribed_at ?? false) || ($connection->last_webhook_at ?? false)) ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}" class="{{ (($connection->webhook_subscribed_at ?? false) || ($connection->last_webhook_at ?? false)) ? 'text-success-main' : 'text-danger-main' }} mt-2"></iconify-icon>
                <span>Lead delivery active — new form submissions sync to Enrolliq</span>
            </li>
            <li class="d-flex align-items-start gap-8 mb-12">
                <iconify-icon icon="{{ ($connection->formMappings->where('is_active', true)->count() ?? 0) > 0 ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}" class="{{ ($connection->formMappings->where('is_active', true)->count() ?? 0) > 0 ? 'text-success-main' : 'text-danger-main' }} mt-2"></iconify-icon>
                <span>At least one Lead Form mapped to your pipeline</span>
            </li>
            <li class="d-flex align-items-start gap-8">
                <iconify-icon icon="{{ ($connection->last_webhook_at ?? false) ? 'solar:check-circle-bold' : 'solar:info-circle-linear' }}" class="{{ ($connection->last_webhook_at ?? false) ? 'text-success-main' : 'text-primary-600' }} mt-2"></iconify-icon>
                <span>Submit a test lead from Facebook, then check CRM → Leads</span>
            </li>
        </ul>
        @can('manage integrations')
        <form method="POST" action="{{ route('admin.integrations.facebook.test-connection') }}" class="mt-20">
            @csrf
            <button type="submit" class="btn btn-outline-primary-600 radius-8">Test connection</button>
        </form>
        @endcan
    </div>
</div>
