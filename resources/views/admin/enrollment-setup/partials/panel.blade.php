@php
    $cfg = $typeData['config'];
    $field = $cfg['field'] ?? 'name';
    $inputType = ($cfg['input'] ?? '') === 'date' ? 'date' : 'text';
    $placeholder = $inputType === 'date' ? 'YYYY-MM-DD' : 'Add new '.strtolower($cfg['label']).'…';
@endphp
<section
    class="enroll-panel enroll-setup__panel {{ $isActive ? 'is-visible' : '' }}"
    data-tab-panel="{{ $typeKey }}"
    id="panel-{{ $typeKey }}"
    role="tabpanel"
    aria-labelledby="tab-{{ $typeKey }}"
    @unless($isActive) hidden @endunless
>
    <div class="enroll-panel__head">
        <div>
            <h2>{{ $cfg['label'] }}</h2>
            <p>Used as options on enrollment forms. Active items appear in dropdowns.</p>
        </div>
        <span class="enroll-panel__count">{{ $typeData['items']->count() }} items</span>
    </div>

    @if($typeData['can_create'])
    <form method="POST" action="{{ route('admin.enrollment-setup.store', $typeKey) }}" class="enroll-add">
        @csrf
        <input type="{{ $inputType }}" name="{{ $field }}" required placeholder="{{ $placeholder }}" class="enroll-add__input">
        <select name="status" class="enroll-add__status">
            <option value="1">Active</option>
            <option value="0">Hidden</option>
        </select>
        <button type="submit" class="btn btn-primary-600 radius-8 px-20">
            <iconify-icon icon="solar:add-circle-linear"></iconify-icon> Add
        </button>
    </form>
    @endif

    <div class="crm-leads-table">
        <div class="crm-leads-table__head" aria-hidden="true">
            <span>{{ $cfg['label'] }}</span><span>Status</span><span></span>
        </div>
        <div class="crm-leads-list">
            @forelse($typeData['items'] as $item)
                @include('admin.enrollment-setup.partials.catalog-row', compact('typeKey', 'typeData', 'item'))
            @empty
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:widget-linear"></iconify-icon>
                    <strong>No items yet</strong>
                    <span>Add your first {{ strtolower($cfg['label']) }} above.</span>
                </div>
            @endforelse
        </div>
    </div>
</section>
