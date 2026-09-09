@php
    $canEdit = auth()->user()->can('edit open_event');
    $canDelete = auth()->user()->can('delete open_event');
    $isActive = (int) $item->status === 1;
@endphp
<article
    class="oe-list-row oe-list-row--event fc-form-row {{ $isActive ? '' : 'is-inactive' }}"
    data-oe-status="{{ $isActive ? 'active' : 'inactive' }}"
    @if($canEdit)
        data-oe-row-open
        data-href="{{ route('admin.open-events.edit', $item->id) }}"
        tabindex="0"
        role="link"
    @endif
    aria-label="Open event {{ $item->name }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="oe-row-icon" aria-hidden="true"><iconify-icon icon="solar:calendar-mark-linear"></iconify-icon></span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $item->name }}</span>
            </div>
            @if($item->title)
                <div class="crm-list-row__contact-line">
                    <span class="crm-list-row__contact">{{ Str::limit($item->title, 48) }}</span>
                </div>
            @endif
        </div>
    </div>
    <div class="crm-list-row__field">
        <span class="crm-list-row__contact">{{ Str::limit(strip_tags($item->description ?? ''), 42) ?: '—' }}</span>
    </div>
    <div class="crm-list-row__field">
        <span @class(['oe-status-pill', 'oe-status-pill--active' => $isActive, 'oe-status-pill--inactive' => ! $isActive])>
            {{ $isActive ? 'Active' : 'Inactive' }}
        </span>
    </div>
    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canEdit)
                <a href="{{ route('admin.open-events.edit', $item->id) }}" class="crm-list-action" onclick="event.stopPropagation()">
                    <iconify-icon icon="solar:pen-linear"></iconify-icon>
                </a>
            @endif
            @if($canDelete)
                <form id="delete-form-{{ $item->id }}" action="{{ route('admin.open-events.destroy', $item->id) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">@csrf @method('DELETE')</form>
                <button type="button" class="crm-list-action is-delete delete-btn" data-id="{{ $item->id }}"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
            @endif
        </div>
        @if($canEdit)
            <a href="{{ route('admin.open-events.edit', $item->id) }}" class="crm-list-row__chevron" onclick="event.stopPropagation()">
                <iconify-icon icon="solar:alt-arrow-right-linear" aria-hidden="true"></iconify-icon>
            </a>
        @endif
    </div>
</article>
