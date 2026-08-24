@extends('admin.layouts.app')
@section('title', 'Select Lead Category')
@section('content')
@include('admin.crm.partials.styles')
@php
    use App\Support\LeadCategoryUi;
    $selectedCategoryId = (string) old('lead_category_id', $import->lead_category_id);
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
<div class="dashboard-main-body" id="crm-lead-category-page">
    @include('admin.partials.page-header', [
        'title' => 'Select Lead Category',
        'subtitle' => $import->original_filename,
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => 'Import', 'url' => route('admin.crm.leads.import.create')],
            ['label' => 'Category'],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0 mb-24">
        <div class="card-body p-24">
            <p class="text-secondary-light mb-16">Choose the category for this entire import batch. Column mapping stays dynamic — unknown spreadsheet columns are preserved as additional lead information.</p>

            <form method="POST" action="{{ route('admin.crm.leads.import.category.save', $import) }}" id="crm-category-select-form">
                @csrf
                <label class="form-label mb-12">Lead Category <span class="text-danger">*</span></label>

                @if($categories->isEmpty())
                    <div class="crm-empty-state py-24">
                        <iconify-icon icon="solar:folder-with-files-linear"></iconify-icon>
                        No categories yet. Create one below to continue.
                    </div>
                @else
                    <div class="crm-category-choice-grid" role="radiogroup" aria-label="Lead categories">
                        @foreach($categories as $category)
                            @php
                                $isSelected = $selectedCategoryId === (string) $category->id;
                                $tone = $category->displayTone();
                                $icon = $category->displayIcon();
                            @endphp
                            <label class="crm-category-choice {{ $isSelected ? 'is-selected' : '' }}" data-tone="{{ $tone }}">
                                <input type="radio"
                                       name="lead_category_id"
                                       value="{{ $category->id }}"
                                       class="crm-category-choice__input"
                                       @checked($isSelected)
                                       required>
                                <span class="crm-category-choice__icon">
                                    <iconify-icon icon="{{ $icon }}"></iconify-icon>
                                </span>
                                <span class="crm-category-choice__body">
                                    <span class="crm-category-choice__name">{{ $category->name }}</span>
                                </span>
                                <span class="crm-category-choice__check" aria-hidden="true">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif

                @error('lead_category_id')<div class="invalid-feedback d-block mt-8">{{ $message }}</div>@enderror

                <button type="submit"
                        class="btn btn-primary-600 radius-8 px-20 py-11 mt-20"
                        @disabled($categories->isEmpty())
                        data-crm-submit-lock>
                    Continue to column mapping
                </button>
            </form>
        </div>
    </div>

    @canany(['import leads', 'create leads', 'update leads'])
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-4">Create a category</h6>
            <p class="text-sm text-secondary-light mb-20">Enter a name to create one. Icon and color are optional — defaults are applied automatically.</p>

            <form method="POST"
                  action="{{ route('admin.crm.leads.import.categories.store', $import) }}"
                  id="crm-category-create-form"
                  data-crm-category-create>
                @csrf

                <div class="mb-20">
                    <label class="form-label" for="new-category-name">Category Name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           id="new-category-name"
                           class="form-control radius-8 @error('name') is-invalid @enderror"
                           value="{{ $createName }}"
                           required
                           maxlength="100"
                           placeholder="e.g. Student Recruitment"
                           autocomplete="off"
                           data-crm-preview-name>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-20">
                    <span class="form-label d-block mb-10">Choose Icon</span>
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
                    @error('icon')<div class="text-danger text-sm mt-8">{{ $message }}</div>@enderror
                </div>

                <div class="mb-20">
                    <span class="form-label d-block mb-10">Choose Color</span>
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
                    @error('tone')<div class="text-danger text-sm mt-8">{{ $message }}</div>@enderror
                </div>

                <div class="mb-20">
                    <span class="form-label d-block mb-10">Preview</span>
                    <div class="crm-category-preview" data-crm-category-preview data-tone="{{ $createTone }}">
                        <span class="crm-category-preview__icon">
                            <iconify-icon icon="{{ $createIcon }}" data-crm-preview-icon></iconify-icon>
                        </span>
                        <span class="crm-category-preview__body">
                            <span class="crm-category-preview__name" data-crm-preview-name-label>{{ $createName !== '' ? $createName : 'Category name' }}</span>
                            <span class="crm-category-preview__meta">0 Leads</span>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-600 radius-8 px-20 py-11" data-crm-submit-lock>
                    <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                    Create Category
                </button>
            </form>
        </div>
    </div>
    @endcanany
</div>
@endsection
@section('script')
<script src="{{ asset('admin/assets/js/crm-lead-category.js') }}"></script>
@endsection
