@extends('admin.layouts.app')
@section('title', 'Import Leads')
@section('content')
@include('admin.crm.partials.styles')
@php
    $categoryReady = \App\Support\LeadCategorySchema::ready();
    $selectedCategoryId = request('category', old('lead_category_id', ''));
@endphp
<style>
    .crm-import-workspace{background:#f7f8fc;padding:18px;border-radius:18px;min-height:calc(100vh - 72px)}
    .crm-import-hero{display:grid;grid-template-columns:minmax(280px,1.15fr) minmax(300px,.85fr);gap:16px;margin-bottom:18px}
    .crm-import-hero__main{padding:24px;border-radius:18px;background:linear-gradient(135deg,#111827,#25105f 52%,#6930df);color:#fff;box-shadow:0 18px 45px rgba(32,23,84,.16);overflow:hidden;position:relative}
    .crm-import-hero__main::after{content:"";position:absolute;right:-90px;top:-120px;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.22),rgba(255,255,255,0) 65%)}
    .crm-import-hero__eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:12px;padding:5px 10px;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(255,255,255,.1);font-size:11px;font-weight:750;letter-spacing:.045em;text-transform:uppercase}
    .crm-import-hero h2{position:relative;margin:0 0 8px;max-width:640px;color:#fff;font-size:28px;line-height:1.08;font-weight:790;letter-spacing:-.045em}
    .crm-import-hero p{position:relative;margin:0;max-width:720px;color:rgba(255,255,255,.78);font-size:14px;line-height:1.6}
    .crm-import-steps{display:grid;gap:8px}
    .crm-import-step{display:flex;gap:10px;padding:12px 14px;border:1px solid #e2e7ef;border-radius:14px;background:#fff;box-shadow:0 4px 14px rgba(31,42,68,.045);transition:.2s ease}
    .crm-import-step span{width:32px;height:32px;flex:0 0 32px;border-radius:10px;display:grid;place-items:center;background:#eef2f7;color:#64748b;font-weight:800;font-size:13px}
    .crm-import-step strong{display:block;margin-bottom:2px;color:#172033;font-size:13px}
    .crm-import-step small{display:block;color:#7c8ba1;font-size:11px;line-height:1.45}
    .crm-import-step.is-active{border-color:#c7d2fe;background:linear-gradient(180deg,#fff,#f5f3ff);box-shadow:0 6px 18px rgba(97,48,204,.08)}
    .crm-import-step.is-active span{background:linear-gradient(135deg,#6130cc,#4f46e5);color:#fff}
    .crm-import-step.is-complete span{background:#dcfce7;color:#15803d}
    .crm-import-panel{border:1px solid #e2e7ef!important;border-radius:18px!important;box-shadow:0 8px 28px rgba(31,42,68,.06)!important;overflow:hidden;margin-bottom:16px}
    .crm-import-panel__head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 22px;border-bottom:1px solid #eef2f7;background:linear-gradient(180deg,#fff,#fafbff)}
    .crm-import-panel__head h3{margin:0;font-size:16px;font-weight:780;color:#111827;letter-spacing:-.02em}
    .crm-import-panel__head p{margin:4px 0 0;font-size:12px;color:#7c8ba1}
    .crm-import-panel__badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;background:#f0edff;color:#6130cc;font-size:11px;font-weight:700}
    .crm-import-panel__body{padding:22px}
    .crm-import-category-grid{margin-top:12px}
    .crm-import-category-card{position:relative}
    .crm-import-category-card .crm-category-choice{width:100%;margin:0}
    .crm-import-category-delete{position:absolute;top:8px;right:8px;z-index:2}
    .crm-import-category-delete__btn{width:28px;height:28px;border:0;border-radius:8px;background:rgba(255,255,255,.92);color:#94a3b8;display:grid;place-items:center;box-shadow:0 2px 8px rgba(15,23,42,.08);cursor:pointer}
    .crm-import-category-delete__btn:hover{color:#dc2626;background:#fff}
    .crm-import-category-empty{display:grid;place-items:center;text-align:center;padding:28px 16px;color:#7c8ba1}
    .crm-import-category-empty iconify-icon{font-size:34px;color:#cbd5e1;margin-bottom:8px}
    .crm-import-category-create{margin-top:18px;border:1px dashed #d8dee9;border-radius:14px;background:#fafbff}
    .crm-import-category-create summary{display:flex;align-items:center;gap:8px;padding:14px 16px;cursor:pointer;font-size:13px;font-weight:700;color:#334155;list-style:none}
    .crm-import-category-create summary::-webkit-details-marker{display:none}
    .crm-import-category-create__body{padding:0 16px 16px}
    .crm-import-upload-wrap{position:relative}
    .crm-import-upload-lock{position:absolute;inset:0;z-index:3;display:grid;place-items:center;text-align:center;padding:24px;background:rgba(255,255,255,.78);backdrop-filter:blur(4px);border-radius:16px}
    .crm-import-upload-lock.is-hidden{display:none}
    .crm-import-upload-lock iconify-icon{font-size:32px;color:#6130cc;margin-bottom:8px}
    .crm-import-upload-lock p{margin:0;max-width:320px;color:#475569;font-size:13px;font-weight:650}
    .crm-import-dropzone{
        position:relative;display:grid;place-items:center;text-align:center;min-height:260px;padding:36px 24px;
        border:2px dashed #cfd8e6;border-radius:18px;background:
            radial-gradient(circle at 50% 0%, rgba(97,48,204,.06), transparent 42%),
            linear-gradient(180deg,#fff,#f8faff);
        cursor:pointer;transition:.22s ease;
    }
    .crm-import-dropzone.is-locked{cursor:not-allowed;opacity:.72}
    .crm-import-dropzone.is-dragover{border-color:#6130cc;background:linear-gradient(180deg,#faf5ff,#f5f3ff);box-shadow:0 0 0 4px rgba(97,48,204,.12);transform:translateY(-1px)}
    .crm-import-dropzone.has-file{min-height:220px;border-style:solid;border-color:#dbeafe;background:linear-gradient(180deg,#fff,#f0f9ff)}
    .crm-import-dropzone__halo{
        width:72px;height:72px;border-radius:22px;display:grid;place-items:center;margin-bottom:16px;
        background:linear-gradient(135deg,#f0edff,#ede9fe);color:#6130cc;font-size:32px;
        box-shadow:0 10px 30px rgba(97,48,204,.15);
    }
    .crm-import-dropzone h4{margin:0 0 8px;font-size:20px;font-weight:780;color:#111827;letter-spacing:-.025em}
    .crm-import-dropzone p{margin:0 auto 16px;max-width:520px;color:#7c8ba1;font-size:13px;line-height:1.55}
    .crm-import-dropzone__cta{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:999px;background:#fff;border:1px solid #dbeafe;color:#1d4ed8;font-size:12px;font-weight:700;box-shadow:0 4px 14px rgba(29,78,216,.08)}
    .crm-import-dropzone__hint{margin-top:14px;font-size:11px;color:#94a3b8}
    .crm-import-file-preview{
        display:flex;align-items:center;gap:14px;width:min(100%,520px);padding:14px 16px;border-radius:14px;
        background:#fff;border:1px solid #dbeafe;box-shadow:0 8px 24px rgba(29,78,216,.08);text-align:left;
    }
    .crm-import-file-preview__icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:#eff6ff;color:#2563eb;font-size:24px;flex:0 0 46px}
    .crm-import-file-preview__body{min-width:0;flex:1}
    .crm-import-file-preview__name{display:block;font-size:14px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .crm-import-file-preview__size{display:block;font-size:11px;color:#64748b;margin-top:2px}
    .crm-import-file-preview__clear{width:34px;height:34px;border:0;border-radius:10px;background:#f8fafc;color:#64748b;display:grid;place-items:center;cursor:pointer}
    .crm-import-file-preview__clear:hover{color:#dc2626;background:#fef2f2}
    .crm-import-actions{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:18px}
    .crm-import-checks{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:14px}
    .crm-import-check{padding:11px;border:1px solid #e5eaf2;border-radius:13px;background:#fff;color:#657389;font-size:11px;font-weight:650}
    .crm-import-check iconify-icon{display:block;margin-bottom:6px;color:#16a34a;font-size:18px}
    .crm-import-category-card--enter{animation:crmImportCategoryIn .28s ease}
    .crm-import-category-card--leave{opacity:0;transform:scale(.96);transition:.18s ease}
    @keyframes crmImportCategoryIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
    .btn.is-busy,[data-crm-category-create-submit].is-busy{opacity:.72;pointer-events:none}
    @media(max-width:991px){.crm-import-hero{grid-template-columns:1fr}.crm-import-checks{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:575px){.crm-import-workspace{padding:12px}.crm-import-hero__main{padding:20px}.crm-import-hero h2{font-size:24px}.crm-import-checks{grid-template-columns:1fr}}
</style>
<div class="dashboard-main-body crm-import-workspace" id="crm-lead-import-page"
     data-category-required="{{ $categoryReady ? '1' : '0' }}"
     data-selected-category="{{ $selectedCategoryId }}"
     data-csrf="{{ csrf_token() }}">
    @include('admin.partials.page-header', [
        'title' => 'Import Leads',
        'subtitle' => 'Choose a category, upload your sheet, then review and import',
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

    <div class="crm-import-hero">
        <div class="crm-import-hero__main">
            <span class="crm-import-hero__eyebrow"><iconify-icon icon="solar:shield-check-linear"></iconify-icon> Simple 4-step import</span>
            <h2>Pick a category, drop your spreadsheet, and bring every lead into CRM.</h2>
            <p>Start with where leads should live (icon &amp; color), then upload your administration sheet or any Excel/CSV file. Columns, comments, checkboxes, and team assignees are preserved.</p>
        </div>
        <div class="crm-import-steps">
            <div class="crm-import-step is-active" data-crm-import-step="1"><span>1</span><div><strong>Category</strong><small>Select an existing category or create a new one with icon and color.</small></div></div>
            <div class="crm-import-step" data-crm-import-step="2"><span>2</span><div><strong>Upload</strong><small>Drag &amp; drop your Excel or CSV file — administration sheets auto-map.</small></div></div>
            <div class="crm-import-step" data-crm-import-step="3"><span>3</span><div><strong>Map columns</strong><small>Confirm mapping; extra columns stay as custom lead information.</small></div></div>
            <div class="crm-import-step" data-crm-import-step="4"><span>4</span><div><strong>Preview &amp; import</strong><small>Review duplicates and warnings, then import clean leads.</small></div></div>
        </div>
    </div>

    @if($categoryReady)
    <div class="card crm-import-panel border-0">
        <div class="crm-import-panel__head">
            <div>
                <h3>Step 1 — Choose lead category</h3>
                <p>Imported leads are added to this category. Empty categories can be deleted.</p>
            </div>
            <span class="crm-import-panel__badge"><iconify-icon icon="solar:pallete-2-linear"></iconify-icon> Icon &amp; color</span>
        </div>
        <div class="crm-import-panel__body">
            @include('admin.crm.leads.import.partials.category-picker', [
                'categories' => $categories,
                'selectedCategoryId' => $selectedCategoryId,
            ])
        </div>
    </div>
    @endif

    <div class="card crm-import-panel border-0" data-crm-import-upload-panel>
        <div class="crm-import-panel__head">
            <div>
                <h3>Step {{ $categoryReady ? '2' : '1' }} — Upload spreadsheet</h3>
                <p>Supports <strong>New Administration Sheet</strong> and other header-based files (.xlsx, .xls, .csv).</p>
            </div>
            <span class="crm-import-panel__badge"><iconify-icon icon="solar:cloud-upload-linear"></iconify-icon> Drag &amp; drop</span>
        </div>
        <div class="crm-import-panel__body">
            <form method="POST" action="{{ route('admin.crm.leads.import.store') }}" enctype="multipart/form-data" data-crm-import-upload-form>
                @csrf
                @if($categoryReady)
                    <input type="hidden" name="lead_category_id" value="{{ $selectedCategoryId }}" data-crm-import-category-hidden>
                @endif

                <div class="crm-import-upload-wrap">
                    @if($categoryReady)
                    <div class="crm-import-upload-lock" data-crm-import-upload-lock>
                        <div>
                            <iconify-icon icon="solar:folder-with-files-linear"></iconify-icon>
                            <p>Select or create a category above to unlock upload</p>
                        </div>
                    </div>
                    @endif

                    <div class="crm-import-dropzone" data-crm-import-dropzone>
                        <input type="file"
                               name="file"
                               class="visually-hidden"
                               accept=".xlsx,.xls,.csv"
                               required
                               data-crm-import-file-input>

                        <div class="crm-import-dropzone__halo"><iconify-icon icon="solar:cloud-upload-linear"></iconify-icon></div>
                        <h4>Drag &amp; drop your lead spreadsheet</h4>
                        <p>Or click to browse. Your file is stored privately and never run as a macro.</p>
                        <span class="crm-import-dropzone__cta">
                            <iconify-icon icon="solar:folder-open-linear"></iconify-icon>
                            Choose file
                        </span>
                        <div class="crm-import-dropzone__hint">.xlsx · .xls · .csv · up to {{ number_format((int) config('lead_import.max_bytes', 10485760) / 1048576, 0) }} MB</div>

                        <div class="crm-import-file-preview d-none mt-18" data-crm-import-file-preview>
                            <span class="crm-import-file-preview__icon"><iconify-icon icon="solar:document-linear" data-crm-import-file-icon></iconify-icon></span>
                            <span class="crm-import-file-preview__body">
                                <span class="crm-import-file-preview__name" data-crm-import-file-name>file.xlsx</span>
                                <span class="crm-import-file-preview__size" data-crm-import-file-size>0 KB</span>
                            </span>
                            <button type="button" class="crm-import-file-preview__clear" data-crm-import-clear-file title="Remove file">
                                <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                            </button>
                        </div>
                    </div>
                </div>

                @error('file')<div class="invalid-feedback d-block mt-12">{{ $message }}</div>@enderror
                @error('lead_category_id')<div class="invalid-feedback d-block mt-12">{{ $message }}</div>@enderror

                <div class="crm-import-actions">
                    <span class="text-sm text-secondary-light">Team names in column A are matched to CRM users automatically.</span>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-12" data-crm-import-submit @disabled($categoryReady && ! $selectedCategoryId)>
                        <iconify-icon icon="solar:upload-linear"></iconify-icon>
                        Upload and continue
                    </button>
                </div>
            </form>

            <div class="crm-import-checks">
                <div class="crm-import-check"><iconify-icon icon="solar:check-circle-linear"></iconify-icon>Duplicate checks before CRM creation</div>
                <div class="crm-import-check"><iconify-icon icon="solar:check-circle-linear"></iconify-icon>Auto-mapping for administration sheets</div>
                <div class="crm-import-check"><iconify-icon icon="solar:check-circle-linear"></iconify-icon>All extra columns kept as custom data</div>
                <div class="crm-import-check"><iconify-icon icon="solar:check-circle-linear"></iconify-icon>Failed-row export for cleanup</div>
            </div>
        </div>
    </div>
    <div class="crm-toast-slot" data-crm-toast-slot aria-live="polite"></div>
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-lead-category.js') }}"></script>
<script src="{{ asset('admin/assets/js/crm-lead-import.js') }}"></script>
@endsection
