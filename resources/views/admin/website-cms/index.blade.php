@extends('admin.layouts.app')

@section('title', 'Website CMS')

@section('css')
<link href="{{ asset('admin/assets/css/website-cms.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-pages.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-pages.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-nav.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-nav.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-footer.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-footer.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-forms.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-forms.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-announce.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-announce.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-contact.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-contact.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-social.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-social.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-branding.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-branding.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-seo.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-seo.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-analytics.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-analytics.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-code.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-code.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-preview.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-preview.css')) }}" rel="stylesheet">
<link href="{{ asset('admin/assets/css/website-cms-assistant.css') }}?v={{ filemtime(public_path('admin/assets/css/website-cms-assistant.css')) }}" rel="stylesheet">
@endsection

@section('content')

<div class="wcm-app" id="websiteCmsApp">
    {{-- Top bar --}}
    <header class="wcm-topbar">
        <div class="wcm-topbar-left">
            <button type="button" class="wcm-sidebar-toggle" id="wcmSidebarToggle" aria-label="Toggle sidebar">
                <iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon>
            </button>
            <div>
                <h1 class="wcm-title">Website CMS</h1>
                <p class="wcm-subtitle">{{ ($headless ?? false) ? 'Manage your connected website without code' : 'Manage your landing page without code' }}</p>
            </div>
        </div>
        <div class="wcm-topbar-right">
            @if($publicWebsiteUrl ?? null)
            <a href="{{ $publicWebsiteUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary-600 btn-sm radius-8 wcm-open-site" title="Open your live website">
                <iconify-icon icon="solar:globus-linear"></iconify-icon>
                Open my website
            </a>
            @endif
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
                @php
                    $navGroups = collect($modules)->groupBy(fn ($m) => $m['group'] ?? 'Other');
                @endphp
                @foreach($navGroups as $groupLabel => $groupMods)
                <div class="wcm-nav-group">
                    <div class="wcm-nav-group-label">{{ $groupLabel }}</div>
                    @foreach($groupMods as $mod)
                    <button type="button" class="wcm-nav-item {{ ($mod['id'] ?? '') === 'dashboard' ? 'is-active' : '' }}" data-module="{{ $mod['id'] }}">
                        <iconify-icon icon="{{ $mod['icon'] }}"></iconify-icon>
                        <span>{{ $mod['label'] }}</span>
                    </button>
                    @endforeach
                </div>
                @endforeach
            </nav>
        </aside>

        {{-- Editor panel --}}
        <main class="wcm-editor">
            <div class="wcm-editor-header">
                <div class="wcm-editor-heading">
                    <h2 class="wcm-module-title" id="wcmModuleTitle">Dashboard</h2>
                    <p class="wcm-module-desc" id="wcmModuleDesc">Quick links and publish overview</p>
                </div>
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
                @if($publicWebsiteUrl ?? null)
                <a href="{{ $publicWebsiteUrl }}" target="_blank" rel="noopener" class="wcm-preview-open" title="Open website in new tab">
                    <iconify-icon icon="solar:square-arrow-right-up-linear"></iconify-icon>
                </a>
                @endif
            </div>
            <div class="wcm-preview-frame-wrap">
                @php
                    $frameUrl = $previewUrl ?? url('/');
                    $frameUrl .= (str_contains($frameUrl, '?') ? '&' : '?').'cms_preview=1';
                @endphp
                <iframe id="wcmPreviewFrame" src="{{ $frameUrl }}" title="Website preview"></iframe>
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
@endsection

@section('script')
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
        hasUnpublished: @json((bool) $hasUnpublished),
        siteHasUnpublished: @json((bool) ($siteHasUnpublished ?? $hasUnpublished)),
        publishedNavMenu: @json($publishedNavMenu ?? null),
        publishedFooterMenu: @json($publishedFooterMenu ?? null),
        publishedWebsiteForms: @json($publishedWebsiteForms ?? null),
        publishedWebsite: @json($publishedWebsite ?? null),
        publishedContact: @json($publishedContact ?? null),
        publishedSocial: @json($publishedSocial ?? null),
        publishedBranding: @json($publishedBranding ?? null),
        publishedSeo: @json($publishedSeo ?? null),
        publishedAnalytics: @json($publishedAnalytics ?? null),
        publishedCustomCode: @json($publishedCustomCode ?? null),
        previewPanel: @json($previewPanel ?? null),
        canEditCode: @json((bool) ($canEditCode ?? false)),
        assistant: @json($assistant ?? null),
        customCodeLastPublished: @json($customCodeLastPublished ?? null),
        seoSuggestedAddress: @json($seoSuggestedAddress ?? null),
        socialNetworks: @json(\App\Support\SocialProfiles::NETWORKS),
        contactTimezones: @json(\App\Support\ContactDetails::TIMEZONES),
        publishedAt: @json($publishedAt?->toIso8601String()),
        publishedAtHuman: @json($publishedAt?->diffForHumans()),
        previewUrl: @json($previewUrl ?? url('/')),
        publicWebsiteUrl: @json($publicWebsiteUrl ?? null),
        csrf: @json(csrf_token()),
        headless: {
            enabled: @json((bool) ($headless ?? false)),
            connection: @json($connection ?? []),
            pages: @json($pages ?? []),
            forms: @json($availableForms ?? []),
            formStatus: @json($websiteFormStatus ?? []),
            routes: {
                connection: @json(route('admin.website-cms.connection')),
                token: @json(route('admin.website-cms.connection.token')),
                test: @json(route('admin.website-cms.connection.test')),
                revalidate: @json(route('admin.website-cms.connection.revalidate')),
                previewUrl: @json(route('admin.website-cms.connection.preview-url')),
                pages: @json(route('admin.website-cms.pages.index')),
                formStatus: @json(route('admin.website-cms.website-forms.status')),
                brandingImage: @json(route('admin.website-cms.branding-image')),
                formEdit: @json(route('admin.form-manager.edit', ['form' => '__FORM__'])),
                formAnswers: @json(\Illuminate\Support\Facades\Route::has('admin.crm.form-entries.form') ? route('admin.crm.form-entries.form', ['form' => '__FORM__']) : route('admin.form-manager.entries', ['form' => '__FORM__'])),
            },
        },
    };
</script>
<script src="{{ asset('admin/assets/js/website-cms-headless.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-headless.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-pages.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-pages.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-nav.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-nav.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-footer.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-footer.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-forms.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-forms.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-announce.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-announce.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-contact.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-contact.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-social.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-social.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-branding.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-branding.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-seo.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-seo.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-analytics.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-analytics.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-code.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-code.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-preview.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-preview.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms-assistant.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms-assistant.js')) }}"></script>
<script src="{{ asset('admin/assets/js/website-cms.js') }}?v={{ filemtime(public_path('admin/assets/js/website-cms.js')) }}"></script>
@endsection
