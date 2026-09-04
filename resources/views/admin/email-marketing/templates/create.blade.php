@extends('admin.layouts.app')
@section('title', 'Create Template')
@section('content')
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'templates',
    'shellTitle' => 'Create template',
    'shellSubtitle' => 'Design reusable emails with live preview, merge tags, and content blocks.',
    'shellActions' => [[
        'label' => 'Back to templates',
        'url' => route('admin.email.templates.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<div class="em-panel p-24">
    <form method="POST" action="{{ route('admin.email.templates.store') }}">
        @csrf
        <div class="row g-3 mb-20">
            <div class="col-md-4">
                <label class="form-label fw-semibold text-sm" for="name">Template name</label>
                <input id="name" name="name" class="form-control radius-8" value="{{ old('name') }}" required placeholder="Open day invite">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold text-sm" for="subject">Default subject</label>
                <input id="subject" name="subject" class="form-control radius-8" value="{{ old('subject') }}" placeholder="Join us for our open day">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold text-sm" for="category">Category</label>
                <input id="category" name="category" class="form-control radius-8" value="{{ old('category') }}" placeholder="Admissions, Newsletter…">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', true))>
                    <label class="form-check-label" for="is_active">Active — available when creating campaigns</label>
                </div>
            </div>
        </div>

        @include('admin.email-marketing.partials.template-studio', [
            'showGallery' => true,
            'templates' => $templates,
            'bodyHtml' => old('body_html'),
        ])

        <textarea name="body_text" class="d-none" aria-hidden="true">{{ old('body_text') }}</textarea>

        <div class="em-wizard-actions mt-24" style="position:static;">
            <button class="btn btn-primary-600 radius-8 fc-btn" type="submit">
                <iconify-icon icon="solar:diskette-linear"></iconify-icon> Save template
            </button>
            <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline-neutral-500 radius-8 fc-btn">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('script')
@include('admin.email-marketing.partials.template-studio-script')
@endsection
