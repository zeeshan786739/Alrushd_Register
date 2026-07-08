    @include('admin.partials.page-header', [
        'title' => $title,
        'subtitle' => $subtitle ?? null,
        'breadcrumbs' => $breadcrumbs ?? [],
        'actions' => [[
            'label' => 'Back to list',
            'url' => $indexRoute,
            'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
            'icon' => 'solar:arrow-left-linear',
        ]],
    ])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-24">
            <form class="row gy-20 needs-validation" novalidate action="{{ $formAction }}" method="POST" @if(!empty($enctype)) enctype="{{ $enctype }}" @endif>
                @csrf
                @if(!empty($formMethod) && $formMethod !== 'POST')
                    @method($formMethod)
                @endif

                <div class="col-12">
                    <label class="form-label fw-medium" for="name">Name</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control radius-8 @error('name') is-invalid @enderror"
                           value="{{ old('name', optional($item)->name) }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium" for="status">Status</label>
                    <select name="status" id="status" class="form-select radius-8 @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', optional($item)->status ?? '1') == '1' || old('status', optional($item)->status ?? 1) == 1)>Active</option>
                        <option value="0" @selected(old('status', optional($item)->status ?? '1') == '0' || old('status', optional($item)->status ?? 1) == 0)>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex flex-wrap justify-content-end gap-10 pt-8">
                    <a href="{{ $indexRoute }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">
                        <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                        <span>Cancel</span>
                    </a>
                    <button class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn" type="submit">
                        <iconify-icon icon="{{ $submitIcon ?? 'solar:diskette-linear' }}"></iconify-icon>
                        <span>{{ $submitLabel ?? 'Save' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
