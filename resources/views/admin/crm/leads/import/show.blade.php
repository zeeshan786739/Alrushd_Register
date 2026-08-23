@extends('admin.layouts.app')
@section('title', 'Import '.$import->original_filename)
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => $import->original_filename,
        'subtitle' => $import->statusEnum()->label(),
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

    <div class="row g-3 mb-24">
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Imported','value'=>$import->imported_rows,'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Duplicates / skipped','value'=>$import->duplicate_rows,'icon'=>'solar:copy-linear','tone'=>'gold'])</div>
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Failed','value'=>$import->failed_rows,'icon'=>'solar:close-circle-linear','tone'=>'navy'])</div>
        <div class="col-sm-6 col-xl-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Total rows','value'=>$import->total_rows,'icon'=>'solar:document-linear','tone'=>'amber'])</div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-24">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-12">Options</h6>
            <div class="text-sm">Duplicate behavior: {{ $import->option('duplicate_behavior') }} · Status: {{ $import->option('default_status') }} · Label: {{ $import->option('source_label') ?: $import->original_filename }}</div>
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
                        <tr>
                            <td>{{ $row->row_number }}</td>
                            <td>{{ $row->statusEnum()->label() }}</td>
                            <td>
                                @if($row->lead)
                                    <a href="{{ route('admin.crm.leads.show', $row->lead) }}">{{ $row->lead->full_name }}</a>
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
</div>
@endsection
