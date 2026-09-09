<div class="crm-metrics-strip" aria-label="Project workspace metrics">
    <div class="crm-metrics-strip__items">
        <span class="crm-metrics-strip__hint">Project workspace</span>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Total</span><strong>{{ number_format($stats['total'] ?? 0) }}</strong></span>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">In progress</span><strong>{{ number_format($stats['in_progress'] ?? 0) }}</strong></span>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Completed</span><strong>{{ number_format($stats['completed'] ?? 0) }}</strong></span>
    </div>
    <div class="crm-metrics-strip__links">
        @can('view customers')<a href="{{ route('admin.crm.customers.index') }}" class="crm-metrics-strip__link">Customers</a>@endcan
        @can('view quotations')<a href="{{ route('admin.crm.quotations.index') }}" class="crm-metrics-strip__link">Quotations</a>@endcan
        @can('view invoices')<a href="{{ route('admin.crm.invoices.index') }}" class="crm-metrics-strip__link">Invoices</a>@endcan
    </div>
</div>
