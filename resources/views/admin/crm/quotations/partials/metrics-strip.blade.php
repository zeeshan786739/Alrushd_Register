<div class="crm-metrics-strip" aria-label="Quotation workspace metrics">
    <div class="crm-metrics-strip__items">
        <span class="crm-metrics-strip__hint">Quotation workspace</span>
        <span class="crm-metrics-strip__sep"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Total</span><strong>{{ number_format($stats['total'] ?? 0) }}</strong></span>
        <span class="crm-metrics-strip__sep"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Draft</span><strong>{{ number_format($stats['draft'] ?? 0) }}</strong></span>
        <span class="crm-metrics-strip__sep"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Sent</span><strong>{{ number_format($stats['sent'] ?? 0) }}</strong></span>
        <span class="crm-metrics-strip__sep"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Accepted</span><strong>{{ number_format($stats['accepted'] ?? 0) }}</strong></span>
    </div>
    <div class="crm-metrics-strip__links">
        @can('view projects')<a href="{{ route('admin.crm.projects.index') }}" class="crm-metrics-strip__link">Projects</a>@endcan
        @can('view invoices')<a href="{{ route('admin.crm.invoices.index') }}" class="crm-metrics-strip__link">Invoices</a>@endcan
    </div>
</div>
