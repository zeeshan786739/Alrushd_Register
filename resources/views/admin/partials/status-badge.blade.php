@if(($status ?? '1') == '1' || ($status ?? 1) == 1)
    <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">{{ $activeLabel ?? 'Active' }}</span>
@else
    <span class="bg-danger-focus text-light-main px-24 py-4 rounded-pill fw-medium text-sm">{{ $inactiveLabel ?? 'Deactive' }}</span>
@endif
