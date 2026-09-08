@php
    use App\Support\LeadCategoryUi;
    $pickerSelectedId = (string) old('lead_category_id', $selectedCategoryId ?? '');
    $createName = old('name', '');
    $createIcon = old('icon', LeadCategoryUi::DEFAULT_ICON);
    $createTone = old('tone', LeadCategoryUi::DEFAULT_TONE);
    if (! in_array($createIcon, LeadCategoryUi::iconIds(), true)) {
        $createIcon = LeadCategoryUi::DEFAULT_ICON;
    }
    if (! in_array($createTone, LeadCategoryUi::toneIds(), true)) {
        $createTone = LeadCategoryUi::DEFAULT_TONE;
    }
@endphp

<div class="crm-import-category-panel" data-crm-import-category-panel>
    <div class="crm-import-category-empty {{ $categories->isNotEmpty() ? 'd-none' : '' }}" data-crm-import-category-empty>
        <iconify-icon icon="solar:folder-with-files-linear"></iconify-icon>
        <p>No categories yet. Create one below to continue.</p>
    </div>

    <div class="{{ $categories->isEmpty() ? 'd-none' : '' }}" data-crm-import-category-toolbar>
        <div class="crm-import-category-toolbar">
            <div class="crm-category-search">
                <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                <input type="search"
                       class="form-control radius-8"
                       placeholder="Search categories…"
                       autocomplete="off"
                       data-crm-category-search>
            </div>
        </div>
    </div>

    <div class="crm-category-choice-grid crm-import-category-grid {{ $categories->isEmpty() ? 'd-none' : '' }}"
         role="radiogroup"
         aria-label="Lead categories"
         data-crm-category-list>
        @foreach($categories as $category)
            @include('admin.crm.leads.import.partials.category-card', [
                'category' => $category,
                'selected' => $pickerSelectedId === (string) $category->id,
            ])
        @endforeach
    </div>
    <p class="text-sm text-secondary-light mt-12 mb-0 d-none" data-crm-category-empty-search>No categories match your search.</p>

    @error('lead_category_id')<div class="invalid-feedback d-block mt-8">{{ $message }}</div>@enderror

    @canany(['import leads', 'create leads', 'update leads'])
    <details class="crm-import-category-create" data-crm-import-category-create-panel {{ $errors->has('name') || $errors->has('icon') || $errors->has('tone') ? 'open' : '' }}>
        <summary>
            <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
            Create new category
        </summary>
        <div class="crm-import-category-create__body">
            <form method="POST"
                  action="{{ route('admin.crm.leads.import.categories.store') }}"
                  data-crm-category-create
                  data-crm-category-create-ajax="1">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="new-category-name">Category name <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               id="new-category-name"
                               class="form-control radius-8 @error('name') is-invalid @enderror"
                               value="{{ $createName }}"
                               required
                               maxlength="100"
                               placeholder="e.g. Admission enquiries"
                               autocomplete="off"
                               data-crm-preview-name>
                        <div class="invalid-feedback d-block d-none" data-crm-category-name-error></div>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="crm-category-preview w-100" data-crm-category-preview data-tone="{{ $createTone }}">
                            <span class="crm-category-preview__icon">
                                <iconify-icon icon="{{ $createIcon }}" data-crm-preview-icon></iconify-icon>
                            </span>
                            <span class="crm-category-preview__body">
                                <span class="crm-category-preview__name" data-crm-preview-name-label>{{ $createName !== '' ? $createName : 'Category name' }}</span>
                                <span class="crm-category-preview__meta">New batch</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-16">
                    <span class="form-label d-block mb-10">Icon</span>
                    <input type="hidden" name="icon" id="new-category-icon" value="{{ $createIcon }}" data-crm-preview-icon-input>
                    <div class="crm-icon-picker" role="listbox" aria-label="Category icons">
                        @foreach(LeadCategoryUi::icons() as $iconId => $meta)
                            <button type="button"
                                    class="crm-icon-picker__tile {{ $createIcon === $iconId ? 'is-selected' : '' }}"
                                    role="option"
                                    aria-selected="{{ $createIcon === $iconId ? 'true' : 'false' }}"
                                    data-icon="{{ $iconId }}"
                                    data-crm-icon-option
                                    title="{{ $meta['label'] }}">
                                <iconify-icon icon="{{ $iconId }}"></iconify-icon>
                                <span class="crm-icon-picker__check" aria-hidden="true"><iconify-icon icon="solar:check-circle-bold"></iconify-icon></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-16">
                    <span class="form-label d-block mb-10">Color</span>
                    <input type="hidden" name="tone" id="new-category-tone" value="{{ $createTone }}" data-crm-preview-tone-input>
                    <div class="crm-color-picker" role="listbox" aria-label="Category colors">
                        @foreach(LeadCategoryUi::colors() as $toneId => $meta)
                            <button type="button"
                                    class="crm-color-picker__swatch {{ $createTone === $toneId ? 'is-selected' : '' }}"
                                    role="option"
                                    aria-selected="{{ $createTone === $toneId ? 'true' : 'false' }}"
                                    data-tone="{{ $toneId }}"
                                    data-crm-color-option
                                    title="{{ $meta['label'] }}">
                                <span class="crm-color-picker__dot" data-tone="{{ $toneId }}"></span>
                                <span class="crm-color-picker__label">{{ $meta['label'] }}</span>
                                <span class="crm-color-picker__check" aria-hidden="true"><iconify-icon icon="solar:check-circle-bold"></iconify-icon></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-outline-primary-600 radius-8 px-20 py-11 mt-16" data-crm-category-create-submit>
                    <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                    Add category
                </button>
            </form>
        </div>
    </details>
    @endcanany
</div>
