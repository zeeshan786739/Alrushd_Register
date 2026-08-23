@extends('admin.layouts.app')
@section('title', 'Import Leads')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Import Leads',
        'subtitle' => 'Upload a spreadsheet to create CRM leads',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => 'Import'],
        ],
        'actions' => [
            ['label' => 'Import History', 'url' => route('admin.crm.leads.import.index'), 'icon' => 'solar:history-linear', 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <p class="text-secondary-light mb-16">Supports .xlsx, .xls, SpreadsheetML, and .csv. Files are stored privately and are never executed as macros.</p>
            <form method="POST" action="{{ route('admin.crm.leads.import.store') }}" enctype="multipart/form-data">
                @csrf
                <label class="form-label" for="lead-import-file">Spreadsheet file</label>
                <input type="file" name="file" id="lead-import-file" class="form-control radius-8 @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <button type="submit" class="btn btn-primary-600 radius-8 px-20 py-11 mt-16">
                    <iconify-icon icon="solar:upload-linear"></iconify-icon>
                    Upload and continue
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
