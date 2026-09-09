@php
    $viewMode = $viewMode ?? 'board';
@endphp
<div class="crm-metrics-strip crm-metrics-strip--links-only" aria-label="Workspace links">
    <div class="crm-metrics-strip__items">
        <span class="crm-metrics-strip__hint">Pipeline workspace</span>
    </div>
    <div class="crm-metrics-strip__links">
        @can('import leads')
            <a href="{{ route('admin.crm.leads.import.create') }}" class="crm-metrics-strip__link">Import</a>
        @endcan
        <a href="{{ route('admin.crm.form-entries.index') }}" class="crm-metrics-strip__link">Forms</a>
        @can('convert leads')
            <a href="{{ route('admin.crm.customers.index') }}" class="crm-metrics-strip__link">Customers</a>
        @endcan
    </div>
</div>
