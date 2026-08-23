@extends('admin.layouts.app')
@section('title', 'Invoices')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body" id="crm-invoices-page">
    @include('admin.partials.page-header', [
        'title' => 'Invoices',
        'subtitle' => 'Track billing, collections, and outstanding balances',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'CRM'],['label'=>'Invoices']],
        'actions' => auth('admin')->user()?->can('create invoices')
            ? [['label'=>'Create Invoice','url'=>route('admin.crm.invoices.create'),'icon'=>'solar:add-circle-linear','class'=>'btn-primary-600 radius-8 px-20 py-11']]
            : [],
    ])

    <div class="row g-3 mb-24">
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Total Invoiced','value'=>number_format($stats['invoiced'],2),'icon'=>'solar:bill-list-linear','tone'=>'navy'])</div>
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Paid','value'=>number_format($stats['paid_amount'],2),'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Outstanding','value'=>number_format($stats['outstanding'],2),'icon'=>'solar:wallet-linear','tone'=>'amber'])</div>
        <div class="col-6 col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Overdue','value'=>number_format($stats['overdue'],2),'icon'=>'solar:danger-triangle-linear','tone'=>'amber'])</div>
    </div>

    @include('admin.partials.filter-bar', [
        'action' => route('admin.crm.invoices.index'),
        'resetUrl' => route('admin.crm.invoices.index'),
        'fields' => [
            ['name'=>'search','label'=>'Search','placeholder'=>'Invoice number'],
            ['name'=>'status','label'=>'Status','type'=>'select','options'=>['draft'=>'Draft','sent'=>'Sent','partially_paid'=>'Partially Paid','paid'=>'Paid','overdue'=>'Overdue','cancelled'=>'Cancelled']],
            ['name'=>'customer_id','label'=>'Customer','type'=>'select','options'=>$customers->pluck('name','id')->all()],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Outstanding</th>
                            <th>Issued</th>
                            <th>Due</th>
                            <th class="text-end pe-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($invoices as $invoice)
                        @php $due = \App\Support\InvoiceDueState::forInvoice($invoice); @endphp
                        <tr class="crm-clickable-row"
                            tabindex="0"
                            data-href="{{ route('admin.crm.invoices.show', $invoice) }}"
                            aria-label="Open invoice {{ $invoice->invoice_number }}">
                            <td>
                                <div class="crm-lead-name">{{ $invoice->invoice_number }}</div>
                            </td>
                            <td><div class="text-truncate" style="max-width:160px">{{ $invoice->customer?->name ?? '—' }}</div></td>
                            <td><div class="text-truncate" style="max-width:140px">{{ $invoice->project?->name ?? '—' }}</div></td>
                            <td>@include('admin.crm.partials.status-pill', ['status'=>$invoice->status])</td>
                            <td class="fw-semibold">{{ number_format((float) $invoice->total, 2) }}</td>
                            <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format((float) $invoice->due_amount, 2) }}</td>
                            <td>{{ $invoice->invoice_date?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                @if($due->applies)
                                    <span class="{{ $due->badgeClass }}">{{ $due->label }}</span>
                                @else
                                    <span class="crm-lead-meta">{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-20" onclick="event.stopPropagation()">
                                @include('admin.partials.table-actions', [
                                    'viewUrl' => route('admin.crm.invoices.show', $invoice),
                                    'editUrl' => auth('admin')->user()?->can('update invoices') && $invoice->status !== 'paid'
                                        ? route('admin.crm.invoices.edit', $invoice)
                                        : null,
                                    'deleteId' => $invoice->id,
                                    'deleteRoute' => route('admin.crm.invoices.destroy', $invoice),
                                    'canDelete' => auth('admin')->user()?->can('delete invoices'),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center py-40 text-secondary-light">No invoices found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-24">{{ $invoices->links() }}</div>
</div>
@endsection

@section('script')
<script>
(function () {
    var page = document.getElementById('crm-invoices-page');
    if (!page) return;
    function isInteractive(target) {
        return !!target.closest('a, button, input, select, textarea, label, form, .fc-table-actions');
    }
    page.addEventListener('click', function (event) {
        var row = event.target.closest('tr.crm-clickable-row[data-href]');
        if (!row || !page.contains(row) || isInteractive(event.target)) return;
        window.location.href = row.getAttribute('data-href');
    });
    page.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        var row = event.target.closest('tr.crm-clickable-row[data-href]');
        if (!row || event.target !== row) return;
        event.preventDefault();
        window.location.href = row.getAttribute('data-href');
    });
})();
</script>
@endsection
