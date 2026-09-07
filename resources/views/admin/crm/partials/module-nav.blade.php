@php
    $active = $active ?? null;
    $user = auth('admin')->user();
    $items = array_values(array_filter([
        ['key' => 'overview', 'label' => 'Overview', 'url' => route('admin.crm.overview'), 'icon' => 'solar:chart-square-linear', 'show' => $user?->can('view leads') || $user?->can('view customers') || $user?->can('view projects') || $user?->can('view quotations') || $user?->can('view invoices')],
        ['key' => 'leads', 'label' => 'Leads', 'url' => route('admin.crm.leads.index'), 'icon' => 'solar:user-hand-up-linear', 'show' => $user?->can('view leads')],
        ['key' => 'customers', 'label' => 'Customers', 'url' => route('admin.crm.customers.index'), 'icon' => 'solar:users-group-rounded-linear', 'show' => $user?->can('view customers')],
        ['key' => 'projects', 'label' => 'Projects', 'url' => route('admin.crm.projects.index'), 'icon' => 'solar:folder-linear', 'show' => $user?->can('view projects')],
        ['key' => 'quotations', 'label' => 'Quotations', 'url' => route('admin.crm.quotations.index'), 'icon' => 'solar:document-text-linear', 'show' => $user?->can('view quotations')],
        ['key' => 'invoices', 'label' => 'Invoices', 'url' => route('admin.crm.invoices.index'), 'icon' => 'solar:bill-list-linear', 'show' => $user?->can('view invoices')],
    ], fn ($item) => $item['show']));
@endphp
@if(!empty($items))
    <nav class="crm-module-nav" aria-label="CRM modules">
        @foreach($items as $item)
            <a href="{{ $item['url'] }}"
               @class(['crm-module-nav__link', 'is-active' => ($active ?? null) === $item['key']])
               @if(($active ?? null) === $item['key']) aria-current="page" @endif>
                <iconify-icon icon="{{ $item['icon'] }}" aria-hidden="true"></iconify-icon>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
@endif
