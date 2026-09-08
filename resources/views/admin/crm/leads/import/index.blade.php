@extends('admin.layouts.app')
@section('title', 'Import History')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body" id="crm-import-history-page" data-csrf="{{ csrf_token() }}">
    @include('admin.partials.page-header', [
        'title' => 'Import History',
        'subtitle' => 'Review past imports and undo wrong batches safely',
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

    <div class="alert alert-light border radius-8 mb-20">
        <strong>Safe delete rule:</strong> Removing a lead or undoing an import only hides records from the CRM — nothing is permanently deleted from the database.
    </div>

    @if(($categories ?? collect())->isNotEmpty())
        @include('admin.partials.filter-bar', [
            'action' => route('admin.crm.leads.import.index'),
            'resetUrl' => route('admin.crm.leads.import.index'),
            'fields' => [
                ['name'=>'lead_category_id','label'=>'Category','type'=>'select','options'=>['uncategorized'=>'Uncategorized'] + $categories->pluck('name','id')->all()],
            ],
        ])
    @endif

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Category</th>
                            <th>Uploaded by</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Imported</th>
                            <th>Active leads</th>
                            <th>Undone</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($imports as $batch)
                        @php $batchStatus = $batch->statusEnum(); @endphp
                        <tr data-crm-import-row data-import-id="{{ $batch->id }}">
                            <td>{{ $batch->original_filename }}</td>
                            <td>{{ $batch->category?->name ?? '—' }}</td>
                            <td>{{ $batch->uploader?->name ?? '—' }}</td>
                            <td>{{ $batch->created_at->format('M j, Y H:i') }}</td>
                            <td><span class="badge radius-8 {{ $batchStatus->badgeClass() }}">{{ $batchStatus->label() }}</span></td>
                            <td>{{ $batch->imported_rows }}</td>
                            <td>{{ $batch->leads_count }}</td>
                            <td>{{ $batch->undone_rows ?: '—' }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.crm.leads.import.show', $batch) }}" class="btn btn-sm btn-outline-primary-600 radius-8">View</a>
                                @if($batch->canUndo())
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger-600 radius-8"
                                            data-crm-import-undo
                                            data-url="{{ route('admin.crm.leads.import.undo', $batch) }}"
                                            data-import-filename="{{ $batch->original_filename }}">
                                        Undo
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-40 text-secondary-light">No imports yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-16">{{ $imports->links() }}</div>
    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-import-history.js') }}"></script>
@endsection
