@php
    $cfg = $typeData['config'];
    $field = $cfg['field'] ?? 'name';
    $inputType = ($cfg['input'] ?? '') === 'date' ? 'date' : 'text';
@endphp
<article class="enroll-catalog-row">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="enroll-catalog-row__value">
        @if($typeData['can_edit'])
        <form method="POST" action="{{ route('admin.enrollment-setup.update', [$typeKey, $item->id]) }}" class="enroll-inline-form">
            @csrf @method('PUT')
            <input type="{{ $inputType }}" name="{{ $field }}" value="{{ \App\Support\EnrollmentCatalog::displayValue($item, $cfg) }}" class="enroll-inline-form__input" required>
            <select name="status" class="enroll-inline-form__status">
                <option value="1" @selected((int)$item->status === 1)>Active</option>
                <option value="0" @selected((int)$item->status === 0)>Hidden</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8">Save</button>
        </form>
        @else
        <span class="fw-semibold">{{ \App\Support\EnrollmentCatalog::displayValue($item, $cfg) }}</span>
        @endif
    </div>
    <div class="enroll-catalog-row__status">
        @include('admin.partials.status-badge', ['status' => $item->status])
    </div>
    <div class="enroll-catalog-row__actions">
        @if($typeData['can_delete'])
        <form method="POST" action="{{ route('admin.enrollment-setup.destroy', [$typeKey, $item->id]) }}" class="d-inline" onsubmit="return confirm('Remove this item? It will disappear from form dropdowns.');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger-600 radius-8">Delete</button>
        </form>
        @endif
    </div>
</article>
