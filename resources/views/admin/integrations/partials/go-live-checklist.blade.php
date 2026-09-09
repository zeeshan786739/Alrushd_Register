<div class="int-panel mt-16">
    <div class="int-panel__head">
        <div>
            <h3 class="int-panel__title">Setup progress</h3>
            <p class="int-panel__sub">Complete these steps so Facebook leads flow into your CRM automatically.</p>
        </div>
    </div>
    <div class="int-panel__body">
        <ul class="int-checklist">
            <li @class(['is-done' => ($connection->isConnected() ?? false), 'is-pending' => !($connection->isConnected() ?? false)])>
                <iconify-icon icon="{{ ($connection->isConnected() ?? false) ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}"></iconify-icon>
                <span>Facebook Page connected for this school</span>
            </li>
            <li @class(['is-done' => (($connection->webhook_subscribed_at ?? false) || ($connection->last_webhook_at ?? false)), 'is-pending' => !(($connection->webhook_subscribed_at ?? false) || ($connection->last_webhook_at ?? false))])>
                <iconify-icon icon="{{ (($connection->webhook_subscribed_at ?? false) || ($connection->last_webhook_at ?? false)) ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}"></iconify-icon>
                <span>Lead delivery active — new form submissions sync to Enrolliq</span>
            </li>
            <li @class(['is-done' => ($connection->formMappings->where('is_active', true)->count() ?? 0) > 0, 'is-pending' => ($connection->formMappings->where('is_active', true)->count() ?? 0) === 0])>
                <iconify-icon icon="{{ ($connection->formMappings->where('is_active', true)->count() ?? 0) > 0 ? 'solar:check-circle-bold' : 'solar:close-circle-linear' }}"></iconify-icon>
                <span>At least one Lead Form mapped to your pipeline</span>
            </li>
            <li @class(['is-done' => ($connection->last_webhook_at ?? false), 'is-info' => !($connection->last_webhook_at ?? false)])>
                <iconify-icon icon="{{ ($connection->last_webhook_at ?? false) ? 'solar:check-circle-bold' : 'solar:info-circle-linear' }}"></iconify-icon>
                <span>Submit a test lead from Facebook, then check CRM → Leads</span>
            </li>
        </ul>
        @can('manage integrations')
        <form method="POST" action="{{ route('admin.integrations.facebook.test-connection') }}" class="mt-16">
            @csrf
            <button type="submit" class="btn btn-outline-primary-600 radius-8">Test connection</button>
        </form>
        @endcan
    </div>
</div>
