<div class="fc-table-actions">
    @if(!empty($viewUrl) && ($canView ?? true))
        <a href="{{ $viewUrl }}" class="fc-action-icon view" title="{{ $viewTitle ?? 'View' }}" aria-label="{{ $viewTitle ?? 'View' }}">
            <iconify-icon icon="solar:eye-linear"></iconify-icon>
        </a>
    @endif
    @if(!empty($editUrl) && ($canEdit ?? true))
        <a href="{{ $editUrl }}" class="fc-action-icon edit" title="{{ $editTitle ?? 'Edit' }}" aria-label="{{ $editTitle ?? 'Edit' }}">
            <iconify-icon icon="solar:pen-linear"></iconify-icon>
        </a>
    @endif
    @if(!empty($deleteId) && !empty($deleteRoute) && ($canDelete ?? true))
        <form id="delete-form-{{ $deleteId }}" action="{{ $deleteRoute }}" method="POST" class="d-inline">
            @csrf @method('DELETE')
        </form>
        <button type="button"
                class="fc-action-icon delete delete-btn"
                data-id="{{ $deleteId }}"
                title="{{ $deleteTitle ?? 'Delete' }}"
                aria-label="{{ $deleteTitle ?? 'Delete' }}">
            <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
        </button>
    @endif
</div>
