@extends('admin.layouts.app')
@section('title', 'Import Leads')
@section('content')
@include('admin.crm.partials.styles')
<style>
    .crm-import-workspace{background:#f7f8fc;padding:18px;border-radius:18px;min-height:calc(100vh - 72px)}
    .crm-import-hero{display:grid;grid-template-columns:minmax(280px,1.2fr) minmax(320px,.8fr);gap:16px;margin-bottom:18px}
    .crm-import-hero__main{padding:24px;border-radius:18px;background:linear-gradient(135deg,#111827,#25105f 52%,#6930df);color:#fff;box-shadow:0 18px 45px rgba(32,23,84,.16);overflow:hidden;position:relative}
    .crm-import-hero__main::after{content:"";position:absolute;right:-90px;top:-120px;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.22),rgba(255,255,255,0) 65%)}
    .crm-import-hero__eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:12px;padding:5px 10px;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(255,255,255,.1);font-size:11px;font-weight:750;letter-spacing:.045em;text-transform:uppercase}
    .crm-import-hero h2{position:relative;margin:0 0 8px;max-width:640px;color:#fff;font-size:30px;line-height:1.08;font-weight:790;letter-spacing:-.045em}
    .crm-import-hero p{position:relative;margin:0;max-width:720px;color:rgba(255,255,255,.78);font-size:14px;line-height:1.6}
    .crm-import-steps{display:grid;gap:10px}
    .crm-import-step{display:flex;gap:10px;padding:14px;border:1px solid #e2e7ef;border-radius:15px;background:#fff;box-shadow:0 4px 14px rgba(31,42,68,.045)}
    .crm-import-step span{width:34px;height:34px;flex:0 0 34px;border-radius:11px;display:grid;place-items:center;background:#f0edff;color:#6130cc;font-weight:800}
    .crm-import-step strong{display:block;margin-bottom:2px;color:#172033;font-size:13px}
    .crm-import-step small{display:block;color:#7c8ba1;font-size:11px;line-height:1.45}
    .crm-import-uploader{border:1px solid #e2e7ef!important;border-radius:18px!important;box-shadow:0 8px 28px rgba(31,42,68,.07)!important;overflow:hidden}
    .crm-import-drop{display:grid;place-items:center;text-align:center;padding:34px 22px;border:1px dashed #cfd8e6;border-radius:16px;background:linear-gradient(180deg,#fff,#fafbff)}
    .crm-import-drop__icon{width:58px;height:58px;border-radius:18px;display:grid;place-items:center;margin-bottom:14px;background:#f0edff;color:#6130cc;font-size:27px}
    .crm-import-drop h3{margin:0 0 6px;font-size:19px;font-weight:780;letter-spacing:-.025em;color:#111827}
    .crm-import-drop p{max-width:560px;margin:0 auto 18px;color:#7c8ba1;font-size:13px;line-height:1.55}
    .crm-import-drop .form-control{max-width:520px;height:46px;border-radius:12px!important;border-color:#dfe5ee;background:#fff}
    .crm-import-checks{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:14px}
    .crm-import-check{padding:11px;border:1px solid #e5eaf2;border-radius:13px;background:#fff;color:#657389;font-size:11px;font-weight:650}
    .crm-import-check iconify-icon{display:block;margin-bottom:6px;color:#16a34a;font-size:18px}
    @media(max-width:991px){.crm-import-hero{grid-template-columns:1fr}.crm-import-checks{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:575px){.crm-import-workspace{padding:12px}.crm-import-hero__main{padding:20px}.crm-import-hero h2{font-size:24px}.crm-import-checks{grid-template-columns:1fr}}
</style>
<div class="dashboard-main-body crm-import-workspace">
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

    <div class="crm-import-hero">
        <div class="crm-import-hero__main">
            <span class="crm-import-hero__eyebrow"><iconify-icon icon="solar:shield-check-linear"></iconify-icon> Enterprise lead intake</span>
            <h2>Bring spreadsheet leads into a mapped, reviewed CRM pipeline.</h2>
            <p>Upload safely, map columns to CRM fields, assign a category, preview the result, then import only clean records into the Leads board.</p>
        </div>
        <div class="crm-import-steps">
            <div class="crm-import-step"><span>1</span><div><strong>Upload</strong><small>Excel or CSV files are stored privately and never executed as macros.</small></div></div>
            <div class="crm-import-step"><span>2</span><div><strong>Map &amp; Categorize</strong><small>Match spreadsheet columns to CRM fields and group them into lead segments.</small></div></div>
            <div class="crm-import-step"><span>3</span><div><strong>Preview &amp; Import</strong><small>Validate duplicates, review failed rows, and continue with clean leads.</small></div></div>
        </div>
    </div>

    <div class="card crm-import-uploader radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <form method="POST" action="{{ route('admin.crm.leads.import.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="crm-import-drop">
                    <div class="crm-import-drop__icon"><iconify-icon icon="solar:upload-linear"></iconify-icon></div>
                    <h3>Upload your lead spreadsheet</h3>
                    <p>Use a clean header row with names, phones, emails, campaign fields, source data, priority, status, and follow-up details where available.</p>
                    <input type="file" name="file" id="lead-import-file" class="form-control radius-8 @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                    @error('file')<div class="invalid-feedback d-block text-start mt-8">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-12 mt-18">
                        <iconify-icon icon="solar:upload-linear"></iconify-icon>
                        Upload and continue
                    </button>
                </div>
            </form>
            <div class="crm-import-checks">
                <div class="crm-import-check"><iconify-icon icon="solar:check-circle-linear"></iconify-icon>Duplicate checks before CRM creation</div>
                <div class="crm-import-check"><iconify-icon icon="solar:check-circle-linear"></iconify-icon>Field mapping for different sheet formats</div>
                <div class="crm-import-check"><iconify-icon icon="solar:check-circle-linear"></iconify-icon>Category assignment for lead segments</div>
                <div class="crm-import-check"><iconify-icon icon="solar:check-circle-linear"></iconify-icon>Failed-row export for cleanup</div>
            </div>
        </div>
    </div>
</div>
@endsection
