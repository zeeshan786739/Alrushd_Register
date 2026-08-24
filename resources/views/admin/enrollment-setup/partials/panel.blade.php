@php
    $cfg = $typeData['config'];
    $field = $cfg['field'] ?? 'name';
    $inputType = ($cfg['input'] ?? '') === 'date' ? 'date' : 'text';
    $placeholder = $inputType === 'date' ? 'YYYY-MM-DD' : 'Add new '.strtolower($cfg['label']).'…';
@endphp
<section
    class="card radius-12 border-0 shadow-sm enroll-panel enroll-setup__panel {{ $isActive ? 'is-visible' : '' }}"
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

    <div class="enroll-table-wrap">
        <table class="table enroll-table mb-0">
            <thead>
                <tr>
                    <th>{{ $cfg['label'] }}</th>
                    <th>Status</th>
                    @if($typeData['can_edit'] || $typeData['can_delete'])<th class="text-end">Actions</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($typeData['items'] as $item)
                <tr>
                    <td>
                        @if($typeData['can_edit'])
                        <form method="POST" action="{{ route('admin.enrollment-setup.update', [$typeKey, $item->id]) }}" class="enroll-inline-form">
                            @csrf @method('PUT')
                            <input type="{{ $inputType }}" name="{{ $field }}" value="{{ \App\Support\EnrollmentCatalog::displayValue($item, $cfg) }}" class="enroll-inline-form__input" required>
                            <select name="status" class="enroll-inline-form__status">
                                <option value="1" @selected((int)$item->status === 1)>Active</option>
                                <option value="0" @selected((int)$item->status === 0)>Hidden</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                        @else
                        {{ \App\Support\EnrollmentCatalog::displayValue($item, $cfg) }}
                        @endif
                    </td>
                    <td>
                        @include('admin.partials.status-badge', ['status' => $item->status])
                    </td>
                    @if($typeData['can_edit'] || $typeData['can_delete'])
                    <td class="text-end">
                        @if($typeData['can_delete'])
                        <form method="POST" action="{{ route('admin.enrollment-setup.destroy', [$typeKey, $item->id]) }}" class="d-inline" onsubmit="return confirm('Remove this item? It will disappear from form dropdowns.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-32 text-secondary-light">
                        No items yet. Add your first {{ strtolower($cfg['label']) }} above.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
