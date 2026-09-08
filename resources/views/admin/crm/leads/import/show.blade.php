@extends('admin.layouts.app')
@section('title', 'Import '.$import->original_filename)
@section('content')
@include('admin.crm.partials.styles')
@php
    $status = $import->statusEnum();
@endphp
<style>
    .crm-import-undo-banner{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;padding:16px 18px;border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(180deg,#f8fbff,#eff6ff);margin-bottom:18px}
    .crm-import-undo-banner.is-undone{border-color:#fde68a;background:linear-gradient(180deg,#fffbeb,#fef3c7)}
    .crm-import-undo-banner h6{margin:0 0 4px;font-size:14px;font-weight:780;color:#0f172a}
    .crm-import-undo-banner p{margin:0;font-size:12px;color:#64748b;line-height:1.5}
    .crm-import-row-removed{color:#94a3b8;font-style:italic}
</style>
<div class="dashboard-main-body" id="crm-import-history-page" data-csrf="{{ csrf_token() }}">
    @include('admin.partials.page-header', [
        'title' => $import->original_filename,
        'subtitle' => $status->label(),
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => 'Import History', 'url' => route('admin.crm.leads.import.index')],
            ['label' => $import->original_filename],
        ],
        'actions' => [
            ['label' => 'Issue report', 'url' => route('admin.crm.leads.import.failed-rows', $import), 'icon' => 'solar:export-linear', 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'],
        ],
    ])

    @if($import->canUndo())
        <div class="crm-import-undo-banner">
            <div>
                <h6>Wrong import? You can undo this batch any time.</h6>
                <p>All {{ $import->imported_rows }} imported lead(s) will disappear from the CRM board and list. Nothing is permanently deleted — records stay safely in the database.</p>
            </div>
            <button type="button"
                    class="btn btn-outline-danger-600 radius-8 px-20 py-11"
                    data-crm-import-undo
                    data-import-filename="{{ $import->original_filename }}"
                    data-url="{{ route('admin.crm.leads.import.undo', $import) }}">
                <iconify-icon icon="solar:undo-left-round-linear"></iconify-icon>
                Undo this import
            </button>
        </div>
    @elseif($status->value === 'undone')
        <div class="crm-import-undo-banner is-undone">
            <div>
                <h6>Import undone</h6>
                <p>
                    {{ $import->undone_rows }} lead(s) were removed from view
                    @if($import->undone_at)
                        on {{ $import->undone_at->format('M j, Y H:i') }}
                    @endif
                    @if($import->undoneBy)
                        by {{ $import->undoneBy->name }}
                    @endif
                    . Data is kept in the database for safety.
                </p>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-24">
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Imported','value'=>$import->imported_rows,'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Removed (undo)','value'=>$import->undone_rows,'icon'=>'solar:undo-left-round-linear','tone'=>'gold'])</div>
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Duplicates / skipped','value'=>$import->duplicate_rows,'icon'=>'solar:copy-linear','tone'=>'navy'])</div>
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Failed','value'=>$import->failed_rows,'icon'=>'solar:close-circle-linear','tone'=>'amber'])</div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-24">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-12">Options</h6>
            <div class="text-sm">Category: {{ $import->category?->name ?? '—' }} · Duplicate behavior: {{ $import->option('duplicate_behavior') }} · Status: {{ $import->option('default_status') }} · Label: {{ $import->option('source_label') ?: $import->original_filename }}</div>
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Status</th>
                            <th>Lead</th>
                            <th>Warnings / errors</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        @php
                            $leadRecord = $row->leadRecord;
                            $rowStatus = $row->statusEnum();
                        @endphp
                        <tr>
                            <td>{{ $row->row_number }}</td>
                            <td><span class="badge radius-8 {{ $rowStatus->badgeClass() }}">{{ $rowStatus->label() }}</span></td>
                            <td>
                                @if($leadRecord && ! $leadRecord->trashed())
                                    <a href="{{ route('admin.crm.leads.show', $leadRecord) }}">{{ $leadRecord->full_name }}</a>
                                @elseif($leadRecord && $leadRecord->trashed())
                                    <span class="crm-import-row-removed">{{ $leadRecord->full_name }} (removed from view)</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-sm text-secondary-light">{{ implode(' ', array_merge($row->warnings ?? [], $row->errors ?? [])) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-40 text-secondary-light">No rows.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-16">{{ $rows->links() }}</div>
    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-import-history.js') }}"></script>
@endsection
