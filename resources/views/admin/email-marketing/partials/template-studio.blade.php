@php
    $studioId = $studioId ?? 'body_html';
    $studioValue = $studioValue ?? old('body_html', $bodyHtml ?? '');
    $showGallery = $showGallery ?? false;
    $templates = $templates ?? collect();
    $selectedTemplateId = old('template_id', $selectedTemplateId ?? '');
@endphp

<div class="em-template-studio" data-template-studio data-editor-id="{{ $studioId }}">
    @if($showGallery)
    <input type="hidden" name="template_id" value="{{ $selectedTemplateId }}" data-template-id-input>
    <div class="em-template-studio__section">
        <div class="em-template-studio__heading">
            <strong>Start from a template</strong>
            <span>Pick a saved design or a quick starter layout.</span>
        </div>
        <div class="em-template-gallery">
            <button type="button" class="em-template-card {{ $selectedTemplateId === '' ? 'is-active' : '' }}" data-template-pick="" data-template-name="Blank email">
                <iconify-icon icon="solar:document-linear"></iconify-icon>
                <span>Blank email</span>
                <small>Start from scratch</small>
            </button>
            @foreach($templates as $template)
            <button type="button"
                    class="em-template-card {{ (string) $selectedTemplateId === (string) $template->id ? 'is-active' : '' }}"
                    data-template-pick="{{ $template->id }}"
                    data-template-name="{{ $template->name }}"
                    data-template-url="{{ route('admin.email.templates.body', $template) }}">
                <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon>
                <span>{{ $template->name }}</span>
                <small>{{ $template->category ?: 'Saved template' }}</small>
            </button>
            @endforeach
        </div>
        <div class="em-template-starters mt-12">
            <span class="em-template-starters__label">Quick starters</span>
            <div class="em-template-starter-btns">
                <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-starter="welcome">Welcome</button>
                <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-starter="openday">Open day</button>
                <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-starter="reminder">Reminder</button>
                <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-starter="newsletter">Newsletter</button>
            </div>
        </div>
    </div>
    @endif

    <div class="em-template-studio__toolbar">
        <div class="em-merge-tags">
            <span class="em-merge-tags__label">Insert tag</span>
            <button type="button" class="em-merge-tag-btn" data-insert-tag="name">@{{name}}</button>
            <button type="button" class="em-merge-tag-btn" data-insert-tag="email">@{{email}}</button>
            <button type="button" class="em-merge-tag-btn" data-insert-tag="company">@{{company}}</button>
            <button type="button" class="em-merge-tag-btn" data-insert-tag="unsubscribe_url">@{{unsubscribe_url}}</button>
        </div>
        <div class="em-template-studio__blocks">
            <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-insert-block="heading">Heading</button>
            <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-insert-block="paragraph">Paragraph</button>
            <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-insert-block="button">Button</button>
            <button type="button" class="btn btn-sm btn-outline-neutral-500 radius-8" data-insert-block="divider">Divider</button>
        </div>
    </div>

    <div class="em-template-studio__workspace">
        <div class="em-template-studio__editor-pane">
            <label class="form-label fw-semibold text-sm" for="{{ $studioId }}">HTML content</label>
            <textarea id="{{ $studioId }}" name="{{ $studioId === 'body_html' ? 'body_html' : $studioId }}" class="form-control radius-8 em-html-editor" rows="18" required data-studio-editor>{{ $studioValue }}</textarea>
        </div>
        <div class="em-template-studio__preview-pane">
            <div class="em-template-studio__preview-head">
                <strong>Live preview</strong>
                <span class="text-secondary-light text-sm">Sample data applied</span>
            </div>
            <div class="em-template-studio__preview-frame" data-studio-preview></div>
        </div>
    </div>
</div>
