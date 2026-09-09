<div class="crm-metrics-strip" aria-label="Invoice workspace metrics">
    <div class="crm-metrics-strip__items">
        <span class="crm-metrics-strip__hint">Billing workspace</span>
        <span class="crm-metrics-strip__sep"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Invoiced</span><strong>{{ number_format($stats['invoiced'] ?? 0, 2) }}</strong></span>
        <span class="crm-metrics-strip__sep"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Paid</span><strong>{{ number_format($stats['paid_amount'] ?? 0, 2) }}</strong></span>
        <span class="crm-metrics-strip__sep"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Outstanding</span><strong>{{ number_format($stats['outstanding'] ?? 0, 2) }}</strong></span>
        <span class="crm-metrics-strip__sep"></span>
        <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Overdue</span><strong>{{ number_format($stats['overdue'] ?? 0, 2) }}</strong></span>
    </div>
    <div class="crm-metrics-strip__links">
        @can('view quotations')<a href="{{ route('admin.crm.quotations.index') }}" class="crm-metrics-strip__link">Quotations</a>@endcan
        @can('view customers')<a href="{{ route('admin.crm.customers.index') }}" class="crm-metrics-strip__link">Customers</a>@endcan
    </div>
</div>
