@php
    $canView = auth()->user()->can('view open_event_form');
    $canDelete = auth()->user()->can('delete open_event_form');
    $viewUrl = route('admin.open-event-form.show', $entry->id);
@endphp
<article
    class="oe-list-row oe-list-row--submission fc-form-row"
    @if($canView)
        data-oe-row-open
        data-href="{{ $viewUrl }}"
        tabindex="0"
        role="link"
    @endif
    aria-label="View submission entry {{ $entry->entry_id }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="oe-row-icon" aria-hidden="true"><iconify-icon icon="solar:clipboard-list-linear"></iconify-icon></span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">#Entry-{{ $entry->entry_id }}</span>
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">{{ trim(($entry->fname ?? '').' '.($entry->lname ?? '')) ?: 'Registration submission' }}</span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__field crm-list-row__field--date">
        <span class="crm-list-row__date">{{ $entry->submission_date ?: '—' }}</span>
        <span class="crm-list-row__date-sub">Submitted</span>
    </div>
    <div class="crm-list-row__field">
        <span class="crm-list-row__contact">{{ $entry->fname ?? '—' }}</span>
    </div>
    <div class="crm-list-row__field">
        <span class="crm-list-row__contact">{{ $entry->lname ?? '—' }}</span>
    </div>
    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canView)
                <a href="{{ $viewUrl }}" class="crm-list-action" onclick="event.stopPropagation()">
                    <iconify-icon icon="solar:eye-linear"></iconify-icon>
                </a>
            @endif
            @if($canDelete)
                <form id="delete-form-{{ $entry->id }}" action="{{ route('admin.open-event-form.destroy', $entry->id) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">@csrf @method('DELETE')</form>
                <button type="button" class="crm-list-action is-delete delete-btn" data-id="{{ $entry->id }}"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
            @endif
        </div>
        @if($canView)
            <a href="{{ $viewUrl }}" class="crm-list-row__chevron" onclick="event.stopPropagation()">
                <iconify-icon icon="solar:alt-arrow-right-linear" aria-hidden="true"></iconify-icon>
            </a>
        @endif
    </div>
</article>
