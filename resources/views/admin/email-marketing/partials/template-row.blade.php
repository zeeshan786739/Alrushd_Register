@php
    $previewUrl = route('admin.email.templates.preview', $template);
    $editUrl = auth('admin')->user()?->can('update templates') ? route('admin.email.templates.edit', $template) : $previewUrl;
@endphp
<article class="crm-list-row em-template-row {{ $template->is_active ? 'is-active' : 'is-inactive' }}"
         data-em-template-open
         data-href="{{ $editUrl }}"
         tabindex="0"
         role="link"
         aria-label="Open template {{ $template->name }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="em-row-icon" aria-hidden="true"><iconify-icon icon="solar:clipboard-list-linear"></iconify-icon></span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $template->name }}</span>
            </div>
            @if($template->category)
                <div class="crm-list-row__contact-line">
                    <span class="crm-list-row__contact">{{ $template->category }}</span>
                </div>
            @endif
        </div>
    </div>
    <div class="crm-list-row__field min-w-0">
        <span class="em-inbox-row__subject">{{ $template->subject ?: '—' }}</span>
    </div>
    <div class="crm-list-row__field">
        <span class="em-crm-chip">{{ $template->category ?: 'General' }}</span>
    </div>
    <div class="crm-list-row__field">
        @if($template->is_active)
            <span class="em-status-pill em-status-pill--sent">Active</span>
        @else
            <span class="em-status-pill em-status-pill--draft">Inactive</span>
        @endif
    </div>
    <div class="crm-list-row__actions">
        <a href="{{ $previewUrl }}" class="crm-list-row__action-btn" title="Preview" onclick="event.stopPropagation();">
            <iconify-icon icon="solar:eye-linear"></iconify-icon>
        </a>
        @can('update templates')
        <a href="{{ $editUrl }}" class="crm-list-row__action-btn" title="Edit" onclick="event.stopPropagation();">
            <iconify-icon icon="solar:pen-linear"></iconify-icon>
        </a>
        @endcan
        <span class="crm-list-row__chevron" aria-hidden="true"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></span>
    </div>
</article>
