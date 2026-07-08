@if(($status ?? '1') == '1' || ($status ?? 1) == 1)
    <span class="fc-badge fc-badge-success">{{ $activeLabel ?? 'Active' }}</span>
@else
    <span class="fc-badge fc-badge-danger">{{ $inactiveLabel ?? 'Inactive' }}</span>
@endif
