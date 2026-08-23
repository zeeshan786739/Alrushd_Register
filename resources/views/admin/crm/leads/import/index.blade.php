@extends('admin.layouts.app')
@section('title', 'Import History')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Import History',
        'subtitle' => 'Previous lead imports for this organization',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => 'Import History'],
        ],
        'actions' => [
            ['label' => 'Import Leads', 'url' => route('admin.crm.leads.import.create'), 'icon' => 'solar:import-linear', 'class' => 'btn-primary-600 radius-8 px-20 py-11'],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Uploaded by</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Imported</th>
                            <th>Warnings</th>
                            <th>Duplicates</th>
                            <th>Failed</th>
                            <th>Leads</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($imports as $batch)
                        <tr>
                            <td>{{ $batch->original_filename }}</td>
                            <td>{{ $batch->uploader?->name ?? '—' }}</td>
                            <td>{{ $batch->created_at->format('M j, Y H:i') }}</td>
                            <td>{{ $batch->statusEnum()->label() }}</td>
                            <td>{{ $batch->total_rows }}</td>
                            <td>{{ $batch->imported_rows }}</td>
                            <td>{{ $batch->warning_rows }}</td>
                            <td>{{ $batch->duplicate_rows }}</td>
                            <td>{{ $batch->failed_rows }}</td>
                            <td>{{ $batch->leads_count }}</td>
                            <td><a href="{{ route('admin.crm.leads.import.show', $batch) }}" class="btn btn-sm btn-outline-primary-600 radius-8">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center py-40 text-secondary-light">No imports yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-16">{{ $imports->links() }}</div>
</div>
@endsection
