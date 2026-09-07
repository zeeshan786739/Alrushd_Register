@php
    $followUp = \App\Support\LeadFollowUpState::forLead($lead);
    $activityIcons = [
        'created' => 'solar:add-circle-linear',
        'status_changed' => 'solar:refresh-circle-linear',
        'priority_changed' => 'solar:flag-linear',
        'note_added' => 'solar:notes-linear',
        'follow_up_scheduled' => 'solar:calendar-linear',
        'follow_up_completed' => 'solar:check-circle-linear',
        'appointment_scheduled' => 'solar:calendar-mark-linear',
        'assigned' => 'solar:user-linear',
        'converted' => 'solar:users-group-rounded-linear',
        'email_sent' => 'solar:letter-linear',
        'imported' => 'solar:import-linear',
    ];
    $statusInlineOptions = [];
    foreach (\App\Enums\LeadStatus::cases() as $status) {
        $statusInlineOptions[$status->value] = [
            'label' => $status->label(),
            'tone' => \App\Support\CrmStatusTone::for($status->value),
            'icon' => \App\Support\CrmStatusTone::icon($status->value),
        ];
    }
    $priorityInlineOptions = [];
    foreach (\App\Enums\LeadPriority::cases() as $priority) {
        $priorityInlineOptions[$priority->value] = [
            'label' => $priority->label(),
            'tone' => \App\Support\CrmStatusTone::for($priority->value),
            'icon' => \App\Support\CrmStatusTone::icon($priority->value),
        ];
    }
    $assigneeInlineOptions = ['' => ['label' => 'Unassigned', 'tone' => 'neutral', 'icon' => 'solar:user-linear']];
    foreach ($admins as $admin) {
        $assigneeInlineOptions[(string) $admin->id] = [
            'label' => $admin->name,
            'tone' => 'neutral',
            'icon' => 'solar:user-linear',
        ];
    }
@endphp
<div class="crm-lead-panel" data-crm-lead-panel data-lead-id="{{ $lead->id }}">
    <header class="crm-lead-panel__header">
        <div class="crm-lead-panel__identity">
            <span class="crm-lead-avatar crm-lead-avatar--panel" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials($lead->full_name) }}</span>
            <div class="min-w-0">
                <div class="crm-lead-panel__ref">Lead #{{ $lead->id }}</div>
                <h2 class="crm-lead-panel__title" data-crm-panel-title>{{ $lead->full_name }}</h2>
                <div class="crm-lead-panel__contact">
                    @if($lead->email)
                        <a href="mailto:{{ $lead->email }}" class="crm-lead-panel__contact-chip" onclick="event.stopPropagation()">
                            <iconify-icon icon="solar:letter-linear"></iconify-icon>{{ $lead->email }}
                        </a>
                    @endif
                    @if($lead->phone)
                        <a href="tel:{{ $lead->phone }}" class="crm-lead-panel__contact-chip" onclick="event.stopPropagation()">
                            <iconify-icon icon="solar:phone-linear"></iconify-icon>{{ $lead->phone }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="crm-lead-panel__controls">
            @can('update leads')
                @include('admin.crm.partials.inline-control', [
                    'field' => 'lead_status',
                    'value' => $lead->lead_status,
                    'recordId' => $lead->id,
                    'idAttr' => 'data-lead-id',
                    'options' => $statusInlineOptions,
                    'ariaLabel' => 'Status for '.$lead->full_name,
                ])
                @include('admin.crm.partials.inline-control', [
                    'field' => 'priority',
                    'value' => $lead->priority,
                    'recordId' => $lead->id,
                    'idAttr' => 'data-lead-id',
                    'options' => $priorityInlineOptions,
                    'ariaLabel' => 'Priority for '.$lead->full_name,
                ])
            @else
                @include('admin.crm.partials.status-pill', ['status'=>$lead->lead_status])
                @include('admin.crm.partials.status-pill', ['status'=>$lead->priority])
            @endcan
            @if($lead->category)
                <span class="crm-category-badge crm-category-badge--{{ $lead->category->displayTone() }}">
                    <iconify-icon icon="{{ $lead->category->displayIcon() }}"></iconify-icon>
                    {{ $lead->category->name }}
                </span>
            @endif
            @if($lead->is_converted)
                <span class="crm-status-pill crm-status-pill--tone-success">Converted</span>
            @endif
        </div>
    </header>

    @if($followUp->hasFollowUp())
        <div class="crm-lead-panel__alert {{ $followUp->attention ? 'is-attention' : '' }}">
            <iconify-icon icon="solar:bell-bing-linear"></iconify-icon>
            <div>
                <strong>{{ $followUp->label }}</strong>
                <span>{{ $followUp->detail }}</span>
            </div>
        </div>
    @endif

    <div class="crm-lead-panel__toolbar">
        @can('update leads')
            <button type="button" class="crm-lead-panel__tool" data-crm-panel-edit data-lead-id="{{ $lead->id }}">
                <iconify-icon icon="solar:pen-linear"></iconify-icon>
                <span>Edit</span>
            </button>
            @if($lead->email)
                <a href="{{ route('admin.crm.leads.email.form', $lead) }}" class="crm-lead-panel__tool">
                    <iconify-icon icon="solar:letter-linear"></iconify-icon>
                    <span>Email</span>
                </a>
            @endif
        @endcan
        @can('convert leads')
            @if(!$lead->is_converted)
                <form method="POST"
                      action="{{ route('admin.crm.leads.convert', $lead) }}"
                      class="crm-lead-panel__tool-form"
                      data-crm-confirm
                      data-confirm-title="Convert lead to customer?"
                      data-confirm-message="This will create a customer record from this lead and keep the original lead history linked."
                      data-confirm-label="Convert to Customer"
                      data-confirm-tone="success"
                      data-confirm-icon="solar:users-group-rounded-linear">
                    @csrf
                    <button class="crm-lead-panel__tool crm-lead-panel__tool--primary" type="submit">
                        <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                        <span>Convert</span>
                    </button>
                </form>
            @elseif($lead->customer)
                <a href="{{ route('admin.crm.customers.show', $lead->customer) }}" class="crm-lead-panel__tool crm-lead-panel__tool--success">
                    <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                    <span>View customer</span>
                </a>
            @endif
        @endcan
    </div>

    <dl class="crm-lead-panel__properties">
        <div class="crm-lead-panel__property">
            <dt>Assigned</dt>
            <dd>
                @can('assign leads')
                    @include('admin.crm.partials.inline-control', [
                        'field' => 'assigned_to',
                        'value' => $lead->assigned_to ?? '',
                        'recordId' => $lead->id,
                        'idAttr' => 'data-lead-id',
                        'options' => $assigneeInlineOptions,
                        'owner' => true,
                        'ariaLabel' => 'Assignee for '.$lead->full_name,
                    ])
                @else
                    {{ $lead->assignedAdmin?->name ?? 'Unassigned' }}
                @endcan
            </dd>
        </div>
        <div class="crm-lead-panel__property">
            <dt>Company</dt>
            <dd>{{ $lead->company ?? '—' }}</dd>
        </div>
        <div class="crm-lead-panel__property">
            <dt>Created</dt>
            <dd>{{ $lead->created_at->format('M j, Y') }}</dd>
        </div>
        <div class="crm-lead-panel__property">
            <dt>Last contacted</dt>
            <dd>{{ optional($lead->last_contacted_at)->diffForHumans() ?? '—' }}</dd>
        </div>
        <div class="crm-lead-panel__property">
            <dt>Follow-up</dt>
            <dd>{{ $followUp->hasFollowUp() ? $followUp->label : 'None scheduled' }}</dd>
        </div>
        <div class="crm-lead-panel__property">
            <dt>Appointment</dt>
            <dd>{{ optional($lead->appointment_date)->format('M j, Y g:i A') ?? '—' }}</dd>
        </div>
        @if($lead->formEntry)
            <div class="crm-lead-panel__property crm-lead-panel__property--wide">
                <dt>Form submission</dt>
                <dd><a href="{{ route('admin.crm.form-entries.show', $lead->formEntry) }}">View submission #{{ $lead->formEntry->id }}</a></dd>
            </div>
        @endif
        @if($lead->lead_description)
            <div class="crm-lead-panel__property crm-lead-panel__property--wide">
                <dt>Description</dt>
                <dd>{{ $lead->lead_description }}</dd>
            </div>
        @endif
    </dl>

    <section class="crm-lead-panel__section">
        <div class="crm-lead-panel__section-head">
            <h3><iconify-icon icon="solar:notes-linear"></iconify-icon> Notes</h3>
            <span class="crm-lead-panel__section-meta">{{ $lead->notes->count() }} total</span>
        </div>
        <div class="crm-lead-panel__section-body">
            @forelse($lead->notes->take(4) as $note)
                <article class="crm-lead-panel__note">
                    <div class="crm-lead-panel__note-head">
                        <strong>{{ $note->admin?->name ?? 'Team member' }}</strong>
                        <time>{{ $note->created_at->diffForHumans() }}</time>
                    </div>
                    <p>{{ $note->note }}</p>
                </article>
            @empty
                <p class="crm-lead-panel__empty">No notes yet. Add context for your team below.</p>
            @endforelse
            @can('update leads')
                <form method="POST" action="{{ route('admin.crm.leads.notes.store', $lead) }}" class="crm-lead-panel__note-form">
                    @csrf
                    <textarea name="note" class="form-control" rows="3" placeholder="Add a note…" required></textarea>
                    <button type="submit" class="crm-lead-panel__note-submit">Add note</button>
                </form>
            @endcan
        </div>
    </section>

    <section class="crm-lead-panel__section">
        <div class="crm-lead-panel__section-head">
            <h3><iconify-icon icon="solar:history-linear"></iconify-icon> Activity</h3>
        </div>
        <div class="crm-lead-panel__timeline">
            @forelse($lead->activities->take(10) as $activity)
                <article class="crm-lead-panel__timeline-item">
                    <span class="crm-lead-panel__timeline-icon">
                        <iconify-icon icon="{{ $activityIcons[$activity->activity_type] ?? 'solar:info-circle-linear' }}"></iconify-icon>
                    </span>
                    <div class="crm-lead-panel__timeline-body">
                        <div class="crm-lead-panel__timeline-title">{{ str_replace('_', ' ', $activity->activity_type) }}</div>
                        @if($activity->description)
                            <p>{{ $activity->description }}</p>
                        @endif
                        <small>{{ $activity->admin?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}</small>
                    </div>
                </article>
            @empty
                <p class="crm-lead-panel__empty">No activity logged yet.</p>
            @endforelse
        </div>
    </section>
</div>
