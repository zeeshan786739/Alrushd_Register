@php
    $data = is_array($entry->data) ? $entry->data : (json_decode($entry->data, true) ?? []);
    $previewParts = [];
    foreach ($previewKeys as $k) {
        if (!empty($data[$k]) && is_string($data[$k])) {
            $previewParts[] = $data[$k];
        }
    }
    if (empty($previewParts)) {
        foreach (array_slice($data, 0, 3) as $key => $val) {
            if (is_string($val) && strlen($val) < 80) {
                $previewParts[] = ucfirst(str_replace('_', ' ', $key)).': '.$val;
            }
        }
    }
    $previewText = implode(' · ', array_slice($previewParts, 0, 3)) ?: '—';
    $statusClass = match($entry->status) {
        'approved' => 'bg-success-focus text-success-main',
        'rejected' => 'bg-danger-focus text-danger-main',
        default => 'bg-warning-focus text-warning-main',
    };
    $submittedAt = $entry->submitted_at ?? $entry->created_at;
@endphp
<article class="fc-submission-row fc-list-row fc-list-row--entry"
         onclick="window.location='{{ route('admin.form-manager.entries.show', [$form, $entry]) }}'">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="fc-list-row__field fw-semibold text-primary-600">#{{ $entry->id }}</div>
    <div class="fc-list-row__field fc-list-row__field--date">
        <span class="fc-list-row__date">{{ $submittedAt->format('d M Y') }}</span>
        <span class="fc-list-row__date-sub">{{ $submittedAt->format('H:i') }}</span>
    </div>
    <div class="fc-list-row__field">
        <span class="badge {{ $statusClass }} px-12 py-6 radius-8 text-xs fw-semibold">{{ ucfirst($entry->status) }}</span>
    </div>
    <div class="fc-list-row__field">
        <p class="fc-preview-text mb-0" title="{{ $previewText }}">{{ $previewText }}</p>
        @if($entry->legacy_source)
            <span class="fc-badge fc-badge-neutral mt-4">Legacy: {{ $entry->legacy_source }}</span>
        @endif
    </div>
    <div class="fc-list-row__actions" onclick="event.stopPropagation()">
        <div class="fc-table-actions">
            <a href="{{ route('admin.form-manager.entries.show', [$form, $entry]) }}"
               class="fc-action-icon view"
               title="View submission"
               aria-label="View submission">
                <iconify-icon icon="solar:eye-linear"></iconify-icon>
            </a>
            <form method="POST"
                  action="{{ route('admin.form-manager.entries.destroy', [$form, $entry]) }}"
                  class="d-inline"
                  onsubmit="return confirm('Delete this submission?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="fc-action-icon delete"
                        title="Delete submission"
                        aria-label="Delete submission">
                    <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                </button>
            </form>
        </div>
    </div>
</article>
