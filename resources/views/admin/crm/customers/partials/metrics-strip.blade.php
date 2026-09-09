<div class="crm-metrics-strip crm-metrics-strip--links-only" aria-label="Workspace links">
    <div class="crm-metrics-strip__items">
        <span class="crm-metrics-strip__hint">Customer workspace</span>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <span class="crm-metrics-strip__item">
            <span class="crm-metrics-strip__label">Total</span>
            <strong>{{ number_format($stats['total'] ?? 0) }}</strong>
        </span>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <span class="crm-metrics-strip__item">
            <span class="crm-metrics-strip__label">Active</span>
            <strong>{{ number_format($stats['active'] ?? 0) }}</strong>
        </span>
        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
        <span class="crm-metrics-strip__item">
            <span class="crm-metrics-strip__label">Prospects</span>
            <strong>{{ number_format($stats['prospect'] ?? 0) }}</strong>
        </span>
    </div>
    <div class="crm-metrics-strip__links">
        @can('view leads')
            <a href="{{ route('admin.crm.leads.index') }}" class="crm-metrics-strip__link">Leads</a>
        @endcan
        <a href="{{ route('admin.crm.form-entries.index') }}" class="crm-metrics-strip__link">Forms</a>
        @can('import leads')
            <a href="{{ route('admin.crm.leads.import.create') }}" class="crm-metrics-strip__link">Import</a>
        @endcan
    </div>
</div>
