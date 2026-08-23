@extends('admin.layouts.app')
@section('title', 'Quotations')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body" id="crm-quotations-page">
    @include('admin.partials.page-header', [
        'title' => 'Quotations',
        'subtitle' => 'Prepare, send, and convert commercial quotations',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'CRM'],['label'=>'Quotations']],
        'actions' => auth('admin')->user()?->can('create quotations')
            ? [['label'=>'Create Quotation','url'=>route('admin.crm.quotations.create'),'icon'=>'solar:add-circle-linear','class'=>'btn-primary-600 radius-8 px-20 py-11']]
            : [],
    ])

    <div class="row g-3 mb-24">
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Total','value'=>$stats['total'],'icon'=>'solar:document-linear','tone'=>'navy'])</div>
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Draft','value'=>$stats['draft'],'icon'=>'solar:pen-linear','tone'=>'slate'])</div>
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Sent','value'=>$stats['sent'],'icon'=>'solar:letter-linear','tone'=>'amber'])</div>
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Accepted','value'=>$stats['accepted'],'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
    </div>

    @include('admin.partials.filter-bar', [
        'action' => route('admin.crm.quotations.index'),
        'resetUrl' => route('admin.crm.quotations.index'),
        'fields' => [
            ['name'=>'search','label'=>'Search','placeholder'=>'Quotation number'],
            ['name'=>'status','label'=>'Status','type'=>'select','options'=>['draft'=>'Draft','sent'=>'Sent','accepted'=>'Accepted','rejected'=>'Rejected','expired'=>'Expired']],
            ['name'=>'customer_id','label'=>'Customer','type'=>'select','options'=>$customers->pluck('name','id')->all()],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Quotation</th>
                            <th>Customer</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Issued</th>
                            <th>Validity</th>
                            <th class="text-end pe-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($quotations as $quotation)
                        @php
                            $locked = $quotation->status === 'accepted' || (bool) $quotation->converted_invoice_id;
                            $expiry = \App\Support\QuotationExpiryState::forQuotation($quotation);
                        @endphp
                        <tr class="crm-clickable-row"
                            tabindex="0"
                            data-href="{{ route('admin.crm.quotations.show', $quotation) }}"
                            aria-label="Open quotation {{ $quotation->quotation_number }}">
                            <td>
                                <div class="crm-lead-name">{{ $quotation->quotation_number }}</div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width:180px">{{ $quotation->customer?->name ?? '—' }}</div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width:160px">{{ $quotation->project?->name ?? '—' }}</div>
                            </td>
                            <td>
                                @include('admin.crm.partials.status-pill', [
                                    'status' => $quotation->converted_invoice_id ? 'converted' : $quotation->status,
                                ])
                            </td>
                            <td class="fw-semibold">{{ number_format((float) $quotation->total, 2) }}</td>
                            <td>{{ $quotation->quotation_date?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                @if($expiry->applies)
                                    <span class="{{ $expiry->badgeClass }}">{{ $expiry->label }}</span>
                                @else
                                    <span class="crm-lead-meta">{{ $quotation->valid_until?->format('M j, Y') ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-20" onclick="event.stopPropagation()">
                                @include('admin.partials.table-actions', [
                                    'viewUrl' => route('admin.crm.quotations.show', $quotation),
                                    'editUrl' => auth('admin')->user()?->can('update quotations') && ! $locked
                                        ? route('admin.crm.quotations.edit', $quotation)
                                        : null,
                                    'deleteId' => $quotation->id,
                                    'deleteRoute' => route('admin.crm.quotations.destroy', $quotation),
                                    'canDelete' => auth('admin')->user()?->can('delete quotations'),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-40 text-secondary-light">No quotations found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-24">{{ $quotations->links() }}</div>
</div>
@endsection

@section('script')
<script>
(function () {
    var page = document.getElementById('crm-quotations-page');
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
