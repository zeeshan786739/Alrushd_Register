@php
    use App\Support\LeadCategoryUi;
    $pickerSelectedId = (string) old('lead_category_id', $selectedCategoryId ?? '');
    $createName = old('name', '');
    $createIcon = LeadCategoryUi::DEFAULT_ICON;
    $createTone = LeadCategoryUi::DEFAULT_TONE;
@endphp

<div class="crm-import-category-simple" data-crm-import-category-panel>
    <div class="row g-3 align-items-end">
        <div class="col-md-8 col-lg-9">
            <label class="form-label fw-semibold mb-8" for="crm-import-category-select">Lead category</label>
            <select id="crm-import-category-select"
                    class="form-select form-select-lg radius-8"
                    data-crm-import-category-select
                    @disabled($categories->isEmpty())>
                <option value="" @selected($pickerSelectedId === '')>
                    {{ $categories->isEmpty() ? 'No categories yet — add one first' : 'Select a category…' }}
                </option>
                @foreach($categories as $category)
                    @php
                        $count = (int) ($category->leads_count ?? 0);
                        $countLabel = $count === 1 ? '1 lead' : number_format($count).' leads';
                    @endphp
                    <option value="{{ $category->id }}"
                            data-leads-count="{{ $count }}"
                            @selected($pickerSelectedId === (string) $category->id)>
                        {{ $category->name }} · {{ $countLabel }}
                    </option>
                @endforeach
            </select>
            <div class="form-text mt-6">Imported rows are saved under this category.</div>
            @error('lead_category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        @canany(['import leads', 'create leads', 'update leads'])
        <div class="col-md-4 col-lg-3">
            <button type="button"
                    class="btn btn-outline-primary-600 radius-8 w-100 py-11"
                    data-crm-import-add-category-toggle
                    aria-expanded="false"
                    aria-controls="crm-import-category-add-panel">
                <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                Add category
            </button>
        </div>
        @endcanany
    </div>

    @canany(['import leads', 'create leads', 'update leads'])
    <div id="crm-import-category-add-panel"
         class="crm-import-category-add {{ $categories->isEmpty() || $errors->has('name') ? '' : 'd-none' }}"
         data-crm-import-category-add-panel>
        <form method="POST"
              action="{{ route('admin.crm.leads.import.categories.store') }}"
              class="crm-import-category-add__form"
              data-crm-category-create
              data-crm-category-create-ajax="1">
            @csrf
            <input type="hidden" name="icon" value="{{ $createIcon }}" data-crm-preview-icon-input>
            <input type="hidden" name="tone" value="{{ $createTone }}" data-crm-preview-tone-input>
            <div class="row g-2 align-items-center">
                <div class="col-sm">
                    <label class="visually-hidden" for="new-category-name">New category name</label>
                    <input type="text"
                           name="name"
                           id="new-category-name"
                           class="form-control radius-8 @error('name') is-invalid @enderror"
                           value="{{ $createName }}"
                           required
                           maxlength="100"
                           placeholder="New category name"
                           autocomplete="off"
                           data-crm-preview-name>
                    <div class="invalid-feedback d-block d-none" data-crm-category-name-error></div>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-auto d-flex gap-8">
                    <button type="submit" class="btn btn-primary-600 radius-8 px-20 py-11" data-crm-category-create-submit>
                        Save
                    </button>
                    @if($categories->isNotEmpty())
                    <button type="button" class="btn btn-outline-neutral-500 radius-8 px-16 py-11" data-crm-import-add-category-cancel>
                        Cancel
                    </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
    @endcanany
</div>
