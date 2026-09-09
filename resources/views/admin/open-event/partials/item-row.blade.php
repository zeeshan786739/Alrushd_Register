@php
    $canEdit = auth()->user()->can('edit event_item');
    $canDelete = auth()->user()->can('delete event_item');
    $isActive = (int) $item->status === 1;
@endphp
<article
    class="oe-list-row oe-list-row--item fc-form-row {{ $isActive ? '' : 'is-inactive' }}"
    data-oe-status="{{ $isActive ? 'active' : 'inactive' }}"
    @if($canEdit)
        data-oe-row-open
        data-href="{{ route('admin.open-event-items.edit', $item->id) }}"
        tabindex="0"
        role="link"
    @endif
    aria-label="Open event item {{ $item->title }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="oe-row-icon" aria-hidden="true"><iconify-icon icon="solar:checklist-linear"></iconify-icon></span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $item->title }}</span>
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">{{ $item->openevent?->name ?? 'Unlinked event' }}</span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__field">
        <span class="crm-list-row__contact">{{ $item->openevent?->name ?? '—' }}</span>
    </div>
    <div class="crm-list-row__field">
        <span class="crm-list-row__date">{{ $item->year ?: '—' }}</span>
        <span class="crm-list-row__date-sub">Years</span>
    </div>
    <div class="crm-list-row__field">
        <span class="crm-list-row__date">{{ $item->minutes ?: '—' }}</span>
        <span class="crm-list-row__date-sub">Minutes</span>
    </div>
    <div class="crm-list-row__field">
        <span @class(['oe-status-pill', 'oe-status-pill--active' => $isActive, 'oe-status-pill--inactive' => ! $isActive])>
            {{ $isActive ? 'Active' : 'Inactive' }}
        </span>
    </div>
    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canEdit)
                <a href="{{ route('admin.open-event-items.edit', $item->id) }}" class="crm-list-action" onclick="event.stopPropagation()">
                    <iconify-icon icon="solar:pen-linear"></iconify-icon>
                </a>
            @endif
            @if($canDelete)
                <form id="delete-form-{{ $item->id }}" action="{{ route('admin.open-event-items.destroy', $item->id) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">@csrf @method('DELETE')</form>
                <button type="button" class="crm-list-action is-delete delete-btn" data-id="{{ $item->id }}"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
            @endif
        </div>
        @if($canEdit)
            <a href="{{ route('admin.open-event-items.edit', $item->id) }}" class="crm-list-row__chevron" onclick="event.stopPropagation()">
                <iconify-icon icon="solar:alt-arrow-right-linear" aria-hidden="true"></iconify-icon>
            </a>
        @endif
    </div>
</article>
