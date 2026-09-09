@php
    $canEdit = auth()->user()->can('edit meet_speakers');
    $canDelete = auth()->user()->can('delete meet_speakers');
    $isActive = (int) $item->status === 1;
@endphp
<article
    class="oe-list-row oe-list-row--speaker fc-form-row {{ $isActive ? '' : 'is-inactive' }}"
    data-oe-status="{{ $isActive ? 'active' : 'inactive' }}"
    @if($canEdit)
        data-oe-row-open
        data-href="{{ route('admin.meet-speakers.edit', $item->id) }}"
        tabindex="0"
        role="link"
    @endif
    aria-label="Open speaker {{ $item->name }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="oe-row-icon oe-row-icon--speaker" aria-hidden="true">
            @if($item->image)
                <img src="{{ Storage::url($item->image) }}" alt="">
            @else
                <iconify-icon icon="solar:microphone-linear"></iconify-icon>
            @endif
        </span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $item->name }}</span>
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">{{ $item->designation ?: 'Speaker' }}</span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__field">
        <span class="crm-list-row__contact">{{ $item->designation ?: '—' }}</span>
    </div>
    <div class="crm-list-row__field">
        @if($item->image)
            <img src="{{ Storage::url($item->image) }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:8px;border:1px solid var(--crm-border);">
        @else
            <span class="crm-list-row__contact">No image</span>
        @endif
    </div>
    <div class="crm-list-row__field">
        <span @class(['oe-status-pill', 'oe-status-pill--active' => $isActive, 'oe-status-pill--inactive' => ! $isActive])>
            {{ $isActive ? 'Active' : 'Inactive' }}
        </span>
    </div>
    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canEdit)
                <a href="{{ route('admin.meet-speakers.edit', $item->id) }}" class="crm-list-action" onclick="event.stopPropagation()">
                    <iconify-icon icon="solar:pen-linear"></iconify-icon>
                </a>
            @endif
            @if($canDelete)
                <form id="delete-form-{{ $item->id }}" action="{{ route('admin.meet-speakers.destroy', $item->id) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">@csrf @method('DELETE')</form>
                <button type="button" class="crm-list-action is-delete delete-btn" data-id="{{ $item->id }}"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
            @endif
        </div>
        @if($canEdit)
            <a href="{{ route('admin.meet-speakers.edit', $item->id) }}" class="crm-list-row__chevron" onclick="event.stopPropagation()">
                <iconify-icon icon="solar:alt-arrow-right-linear" aria-hidden="true"></iconify-icon>
            </a>
        @endif
    </div>
</article>
