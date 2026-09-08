@php
    $tone = $category->displayTone();
    $icon = $category->displayIcon();
    $leadCount = (int) ($category->leads_count ?? 0);
@endphp
<div class="crm-import-category-card {{ ! empty($selected) ? 'is-selected' : '' }}"
     data-crm-category-item
     data-crm-import-category-card
     data-category-id="{{ $category->id }}"
     data-name="{{ strtolower($category->name) }}"
     data-tone="{{ $tone }}">
    <label class="crm-category-choice {{ ! empty($selected) ? 'is-selected' : '' }}" data-tone="{{ $tone }}">
        <input type="radio"
               name="lead_category_id"
               value="{{ $category->id }}"
               class="crm-category-choice__input"
               data-crm-import-category-input
               @checked(! empty($selected))>
        <span class="crm-category-choice__icon">
            <iconify-icon icon="{{ $icon }}"></iconify-icon>
        </span>
        <span class="crm-category-choice__body">
            <span class="crm-category-choice__name">{{ $category->name }}</span>
            <span class="crm-category-choice__meta">{{ $leadCount }} {{ $leadCount === 1 ? 'lead' : 'leads' }}</span>
        </span>
        <span class="crm-category-choice__check" aria-hidden="true">
            <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
        </span>
    </label>
    @if($leadCount === 0)
        <form method="POST"
              action="{{ route('admin.crm.leads.import.categories.destroy', $category) }}"
              class="crm-import-category-delete"
              data-crm-category-delete-form
              data-crm-category-delete-ajax="1">
            @csrf
            @method('DELETE')
            <button type="submit" class="crm-import-category-delete__btn" title="Delete empty category">
                <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
            </button>
        </form>
    @endif
</div>
