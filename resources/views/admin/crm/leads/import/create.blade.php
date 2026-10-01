@extends('admin.layouts.app')
@section('title', 'Import Leads')
@section('content')
@include('admin.crm.partials.styles')
@php
    $categoryReady = \App\Support\LeadCategorySchema::ready();
    $selectedCategoryId = request('category', old('lead_category_id', ''));
@endphp
<style>
    .crm-import-page{max-width:880px;margin:0 auto}
    .crm-import-card{border:1px solid #e5e7eb;border-radius:16px;background:#fff;box-shadow:0 4px 20px rgba(15,39,74,.06);overflow:hidden}
    .crm-import-card__section{padding:22px 24px;border-bottom:1px solid #f1f5f9}
    .crm-import-card__section:last-child{border-bottom:0}
    .crm-import-card__label{display:flex;align-items:center;gap:8px;margin:0 0 14px;font-size:13px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em}
    .crm-import-card__label span{width:22px;height:22px;border-radius:6px;background:#0F274A;color:#fff;font-size:12px;font-weight:800;display:grid;place-items:center}
    .crm-import-category-add{margin-top:14px;padding:14px 16px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0}
    .crm-import-dropzone{
        position:relative;display:grid;place-items:center;text-align:center;min-height:180px;padding:28px 20px;
        border:2px dashed #cbd5e1;border-radius:14px;background:#fafbff;cursor:pointer;transition:.2s ease;
    }
    .crm-import-dropzone.is-locked{cursor:not-allowed;opacity:.55}
    .crm-import-dropzone.is-dragover{border-color:#0F274A;background:#f0f4ff;box-shadow:0 0 0 3px rgba(15,39,74,.1)}
    .crm-import-dropzone.has-file{min-height:120px;border-style:solid;border-color:#93c5fd;background:#f0f9ff}
    .crm-import-dropzone__icon{font-size:36px;color:#0F274A;margin-bottom:10px}
    .crm-import-dropzone h4{margin:0 0 6px;font-size:16px;font-weight:700;color:#0f172a}
    .crm-import-dropzone p{margin:0 0 12px;font-size:13px;color:#64748b}
    .crm-import-file-preview{
        display:flex;align-items:center;gap:12px;width:min(100%,480px);padding:12px 14px;border-radius:12px;
        background:#fff;border:1px solid #dbeafe;text-align:left;
    }
    .crm-import-file-preview__icon{font-size:28px;color:#2563eb;flex-shrink:0}
    .crm-import-file-preview__name{display:block;font-size:14px;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .crm-import-file-preview__size{font-size:11px;color:#64748b}
    .crm-import-file-preview__clear{border:0;background:transparent;color:#64748b;padding:4px;cursor:pointer}
    .crm-import-file-preview__clear:hover{color:#dc2626}
    .crm-import-footer{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px}
    .btn.is-busy,[data-crm-category-create-submit].is-busy{opacity:.72;pointer-events:none}
</style>
<div class="dashboard-main-body" id="crm-lead-import-page"
     data-category-required="{{ $categoryReady ? '1' : '0' }}"
     data-selected-category="{{ $selectedCategoryId }}"
     data-csrf="{{ csrf_token() }}">
    @include('admin.partials.page-header', [
        'title' => 'Import Leads',
        'subtitle' => 'Choose a category, upload your file, and preview before importing',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => 'Import'],
        ],
        'actions' => [
            ['label' => 'Import history', 'url' => route('admin.crm.leads.import.index'), 'icon' => 'solar:history-linear', 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'],
        ],
    ])

    <div class="crm-import-page">
        <div class="crm-import-card">
            @if($categoryReady)
            <div class="crm-import-card__section">
                <h2 class="crm-import-card__label"><span>1</span> Category</h2>
                @include('admin.crm.leads.import.partials.category-picker', [
                    'categories' => $categories,
                    'selectedCategoryId' => $selectedCategoryId,
                ])
            </div>
            @endif

            <div class="crm-import-card__section" data-crm-import-upload-panel>
                <h2 class="crm-import-card__label"><span>{{ $categoryReady ? '2' : '1' }}</span> Upload file</h2>
                <form method="POST" action="{{ route('admin.crm.leads.import.store') }}" enctype="multipart/form-data" data-crm-import-upload-form>
                    @csrf
                    @if($categoryReady)
                        <input type="hidden" name="lead_category_id" value="{{ $selectedCategoryId }}" data-crm-import-category-hidden>
                    @endif

                    <div class="crm-import-dropzone mb-16" data-crm-import-dropzone>
                        <input type="file"
                               name="file"
                               class="visually-hidden"
                               accept=".xlsx,.xls,.csv"
                               required
                               data-crm-import-file-input>

                        <div data-crm-import-dropzone-empty>
                            <iconify-icon icon="solar:cloud-upload-linear" class="crm-import-dropzone__icon"></iconify-icon>
                            <h4>Drop your spreadsheet here</h4>
                            <p>Excel or CSV · up to {{ number_format((int) config('lead_import.max_bytes', 10485760) / 1048576, 0) }} MB</p>
                            <span class="btn btn-sm btn-outline-primary-600 radius-8">Browse files</span>
                        </div>

                        <div class="crm-import-file-preview d-none" data-crm-import-file-preview>
                            <iconify-icon icon="solar:document-linear" class="crm-import-file-preview__icon" data-crm-import-file-icon></iconify-icon>
                            <span class="flex-grow-1 min-w-0">
                                <span class="crm-import-file-preview__name" data-crm-import-file-name>file.xlsx</span>
                                <span class="crm-import-file-preview__size" data-crm-import-file-size>0 KB</span>
                            </span>
                            <button type="button" class="crm-import-file-preview__clear" data-crm-import-clear-file title="Remove file">
                                <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    @error('file')<div class="invalid-feedback d-block mb-12">{{ $message }}</div>@enderror
                    @error('lead_category_id')<div class="invalid-feedback d-block mb-12">{{ $message }}</div>@enderror

                    <div class="mb-20">
                        <label class="form-label" for="default_assigned_to">Assign all leads to (optional)</label>
                        <select name="default_assigned_to" id="default_assigned_to" class="form-select radius-8">
                            <option value="">From spreadsheet or unassigned</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" @selected((string) old('default_assigned_to') === (string) $admin->id)>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="crm-import-footer">
                        <p class="text-sm text-secondary-light mb-0">Next step: preview rows and confirm import.</p>
                        <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-12" data-crm-import-submit @disabled($categoryReady && ! $selectedCategoryId)>
                            Continue to preview
                            <iconify-icon icon="solar:arrow-right-linear"></iconify-icon>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-lead-import.js') }}"></script>
@endsection
