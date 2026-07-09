@extends('admin.layouts.app')

@section('title', 'Website CMS')

@section('content')
<link href="{{ asset('admin/assets/css/website-cms.css') }}" rel="stylesheet">

<div class="wcm-app" id="websiteCmsApp">
    {{-- Top bar --}}
    <header class="wcm-topbar">
        <div class="wcm-topbar-left">
            <button type="button" class="wcm-sidebar-toggle" id="wcmSidebarToggle" aria-label="Toggle sidebar">
                <iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon>
            </button>
            <div>
                <h1 class="wcm-title">Website CMS</h1>
                <p class="wcm-subtitle">Manage your landing page without code</p>
            </div>
        </div>
        <div class="wcm-topbar-right">
            <span class="wcm-status" id="wcmStatus">
                <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                <span id="wcmStatusText">All changes saved</span>
            </span>
            @if($hasUnpublished)
            <span class="wcm-badge wcm-badge--warn">Unpublished changes</span>
            @endif
            @if($publishedAt)
            <span class="wcm-meta">Published {{ $publishedAt->diffForHumans() }}</span>
            @endif
        </div>
    </header>

    <div class="wcm-layout">
        {{-- Sidebar modules --}}
        <aside class="wcm-sidebar" id="wcmSidebar">
            <nav class="wcm-nav">
                @foreach($modules as $mod)
                <button type="button" class="wcm-nav-item {{ $loop->first ? 'is-active' : '' }}" data-module="{{ $mod['id'] }}">
                    <iconify-icon icon="{{ $mod['icon'] }}"></iconify-icon>
                    <span>{{ $mod['label'] }}</span>
                </button>
                @endforeach
            </nav>
        </aside>

        {{-- Editor panel --}}
        <main class="wcm-editor">
            <div class="wcm-editor-header">
                <h2 class="wcm-module-title" id="wcmModuleTitle">Dashboard</h2>
                <div class="wcm-editor-actions">
                    <button type="button" class="btn btn-outline-neutral-500 btn-sm radius-8" id="wcmResetSection">
                        <iconify-icon icon="solar:restart-linear"></iconify-icon> Reset Section
                    </button>
                </div>
            </div>
            <div class="wcm-editor-body" id="wcmEditorBody">
                {{-- Filled by JS --}}
            </div>
        </main>

        {{-- Live preview --}}
        <aside class="wcm-preview">
            <div class="wcm-preview-header">
                <span class="wcm-preview-label">Live Preview</span>
                <div class="wcm-preview-devices">
                    <button type="button" class="wcm-device is-active" data-width="100%" title="Desktop"><iconify-icon icon="solar:monitor-linear"></iconify-icon></button>
                    <button type="button" class="wcm-device" data-width="768px" title="Tablet"><iconify-icon icon="solar:tablet-linear"></iconify-icon></button>
                    <button type="button" class="wcm-device" data-width="375px" title="Mobile"><iconify-icon icon="solar:smartphone-linear"></iconify-icon></button>
                </div>
                <button type="button" class="wcm-preview-refresh" id="wcmRefreshPreview" title="Refresh preview">
                    <iconify-icon icon="solar:refresh-linear"></iconify-icon>
                </button>
            </div>
            <div class="wcm-preview-frame-wrap">
                <iframe id="wcmPreviewFrame" src="{{ url('/?cms_preview=1') }}" title="Website preview"></iframe>
            </div>
        </aside>
    </div>

    {{-- Sticky save bar --}}
    <footer class="wcm-savebar">
        <div class="wcm-savebar-inner">
            <span class="wcm-savebar-hint">Changes are saved as draft until you publish</span>
            <div class="wcm-savebar-actions">
                <button type="button" class="btn btn-outline-neutral-500 radius-8 px-20 py-10" id="wcmDiscard">
                    <iconify-icon icon="solar:close-circle-linear"></iconify-icon> Discard
                </button>
                <button type="button" class="btn btn-outline-primary-600 radius-8 px-20 py-10" id="wcmSaveDraft">
                    <iconify-icon icon="solar:diskette-linear"></iconify-icon> Save Draft
                </button>
                <button type="button" class="btn btn-primary-600 radius-8 px-24 py-10" id="wcmPublish">
                    <iconify-icon icon="solar:upload-linear"></iconify-icon> Publish
                </button>
            </div>
        </div>
    </footer>
</div>

<script>
    window.__WCM = {
        data: @json($cmsData),
        routes: {
            draft: @json(route('admin.website-cms.draft')),
            publish: @json(route('admin.website-cms.publish')),
            discard: @json(route('admin.website-cms.discard')),
            reset: @json(route('admin.website-cms.reset-section')),
            preview: @json(route('admin.website-cms.preview')),
            upload: @json(route('admin.website-cms.upload')),
            restore: @json(route('admin.website-cms.restore-version')),
            formCenter: @json(route('admin.form-manager.index')),
        },
        modules: @json($modules),
        versionHistory: @json($versionHistory),
        csrf: @json(csrf_token()),
    };
</script>
<script src="{{ asset('admin/assets/js/website-cms.js') }}"></script>
@endsection
