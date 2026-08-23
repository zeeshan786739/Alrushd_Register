@extends('admin.layouts.app')
@section('title', 'Edit Template')
@section('content')
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'templates',
    'shellTitle' => 'Edit template',
    'shellSubtitle' => $template->name,
    'shellActions' => [[
        'label' => 'Back to templates',
        'url' => route('admin.email.templates.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<div class="em-panel p-24">
    <form method="POST" action="{{ route('admin.email.templates.update', $template) }}">
        @csrf
        @method('PUT')
        <div class="row g-3 mb-20">
            <div class="col-md-4">
                <label class="form-label fw-semibold text-sm" for="name">Template name</label>
                <input id="name" name="name" class="form-control radius-8" value="{{ old('name', $template->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold text-sm" for="subject">Default subject</label>
                <input id="subject" name="subject" class="form-control radius-8" value="{{ old('subject', $template->subject) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold text-sm" for="category">Category</label>
                <input id="category" name="category" class="form-control radius-8" value="{{ old('category', $template->category) }}">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $template->is_active))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>
        </div>

        @include('admin.email-marketing.partials.template-studio', [
            'showGallery' => false,
            'bodyHtml' => old('body_html', $template->body_html),
        ])

        <textarea name="body_text" class="d-none" aria-hidden="true">{{ old('body_text', $template->body_text) }}</textarea>

        <div class="em-wizard-actions mt-24" style="position:static;">
            <button class="btn btn-primary-600 radius-8 fc-btn" type="submit">
                <iconify-icon icon="solar:diskette-linear"></iconify-icon> Update template
            </button>
            <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline-neutral-500 radius-8 fc-btn">Cancel</a>
        </div>
    </form>
    <form method="POST" action="{{ route('admin.email.templates.duplicate', $template) }}" class="mt-12">
        @csrf
        <button class="btn btn-outline-neutral-500 radius-8 fc-btn" type="submit">
            <iconify-icon icon="solar:copy-linear"></iconify-icon> Duplicate template
        </button>
    </form>
</div>
@endsection

@section('script')
@include('admin.email-marketing.partials.template-studio-script')
@endsection
