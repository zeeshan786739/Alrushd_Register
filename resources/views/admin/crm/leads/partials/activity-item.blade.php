@php
    $activityIcons = [
        'created' => 'solar:add-circle-linear',
        'status_changed' => 'solar:refresh-circle-linear',
        'priority_changed' => 'solar:flag-linear',
        'note_added' => 'solar:chat-round-dots-linear',
        'follow_up_scheduled' => 'solar:calendar-linear',
        'follow_up_completed' => 'solar:check-circle-linear',
        'appointment_scheduled' => 'solar:calendar-mark-linear',
        'assigned' => 'solar:user-linear',
        'converted' => 'solar:users-group-rounded-linear',
        'email_sent' => 'solar:letter-linear',
        'imported' => 'solar:import-linear',
        'lead_renamed' => 'solar:pen-linear',
        'archived' => 'solar:archive-linear',
    ];
@endphp
<article class="crm-lead-ticket__timeline-item">
    <span class="crm-lead-ticket__timeline-icon">
        <iconify-icon icon="{{ $activityIcons[$activity->activity_type] ?? 'solar:info-circle-linear' }}"></iconify-icon>
    </span>
    <div class="crm-lead-ticket__timeline-body">
        <div class="crm-lead-ticket__timeline-title">{{ str_replace('_', ' ', $activity->activity_type) }}</div>
        @if($activity->description)
            <p>{{ $activity->description }}</p>
        @endif
        <small>{{ $activity->admin?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}</small>
    </div>
</article>
