@php
    $mode = $mode ?? 'facebook';
    $platform = $platform ?? 'facebook';
    $tone = match($submission->status->value ?? '') {
        'processed' => 'success',
        'failed' => 'danger',
        'unmapped', 'pending' => 'warning',
        default => $platform,
    };
    $statusLabel = method_exists($submission->status, 'label')
        ? $submission->status->label()
        : ucfirst($submission->status->value ?? 'Unknown');
    $statusBadgeClass = method_exists($submission->status, 'badgeClass')
        ? 'badge radius-8 '.$submission->status->badgeClass()
        : 'int-status-badge int-status-badge--neutral';
@endphp
<article @class(['int-list-row', 'int-list-row--submission', 'int-list-row--'.$mode, 'int-list-row--'.$tone])>
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    @if($mode === 'hub')
        <div class="crm-list-row__identity">
            <div class="crm-list-row__identity-copy min-w-0">
                <span class="crm-list-row__name">{{ $submission->formMapping?->internal_label ?? 'Facebook lead' }}</span>
            </div>
        </div>
        <div class="crm-list-row__field">{{ $submission->formMapping?->external_form_name ?? $submission->formMapping?->internal_label ?? '—' }}</div>
        <div class="crm-list-row__field"><span @class([$statusBadgeClass])>{{ $statusLabel }}</span></div>
        <div class="crm-list-row__field crm-list-row__field--date">
            <span class="crm-list-row__date">{{ $submission->created_at->diffForHumans() }}</span>
            <span class="crm-list-row__date-sub">Received</span>
        </div>
    @elseif($mode === 'tiktok')
        <div class="crm-list-row__identity">
            <div class="crm-list-row__identity-copy min-w-0">
                <span class="crm-list-row__name">{{ $submission->displayName() }}</span>
                @if($submission->error_message && $submission->status->value === 'failed')
                    <span class="crm-list-row__contact text-danger-main">{{ $submission->error_message }}</span>
                @endif
            </div>
        </div>
        <div class="crm-list-row__field">{{ $submission->formDisplayName() }}</div>
        <div class="crm-list-row__field crm-list-row__field--date">
            <span class="crm-list-row__date">{{ $submission->received_at?->timezone(config('app.timezone'))->toDayDateTimeString() ?? '—' }}</span>
            <span class="crm-list-row__date-sub">Received</span>
        </div>
        <div class="crm-list-row__field"><span @class([$statusBadgeClass])>{{ $statusLabel }}</span></div>
        <div class="crm-list-row__actions">
            @if($submission->lead_id)
                @can('view leads')
                    <a href="{{ route('admin.crm.leads.show', $submission->lead_id) }}" class="btn btn-sm btn-outline-primary-600 radius-8">View lead</a>
                @endcan
            @endif
            @can('manage integrations')
                @if($submission->status->canReprocess())
                    <form method="POST" action="{{ route('admin.integrations.tiktok.submissions.reprocess', $submission) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8">Reprocess</button>
                    </form>
                @endif
            @endcan
        </div>
    @else
        <div class="crm-list-row__field"><span @class([$statusBadgeClass])>{{ $statusLabel }}</span></div>
        <div class="crm-list-row__field">{{ $submission->formMapping?->internal_label ?? 'Unmapped' }}</div>
        <div class="crm-list-row__field">
            @if($submission->lead_id)
                @can('view leads')
                    <a href="{{ route('admin.crm.leads.show', $submission->lead_id) }}">View lead</a>
                @else
                    Linked
                @endcan
            @else
                —
            @endif
        </div>
        <div class="crm-list-row__field crm-list-row__field--date">
            <span class="crm-list-row__date">{{ $submission->created_at->diffForHumans() }}</span>
            <span class="crm-list-row__date-sub">Received</span>
        </div>
        <div class="crm-list-row__actions">
            @can('manage integrations')
                @if(in_array($submission->status->value, ['unmapped', 'failed', 'pending']))
                    <form method="POST" action="{{ route('admin.integrations.facebook.submissions.reprocess', $submission) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8">Reprocess</button>
                    </form>
                @endif
            @endcan
        </div>
    @endif
</article>
