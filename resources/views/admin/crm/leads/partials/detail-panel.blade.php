@php
    $followUp = \App\Support\LeadFollowUpState::forLead($lead);
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
    $comments = $lead->notes->sortByDesc('created_at')->values();
    $visibleComments = $comments->take(2);
    $hiddenCommentCount = max(0, $comments->count() - $visibleComments->count());
    $activities = $lead->activities->sortByDesc('created_at')->values();
    $visibleActivities = $activities->take(2);
    $hiddenActivityCount = max(0, $activities->count() - $visibleActivities->count());
    $importSummary = \App\Support\LeadImportSummary::forLead($lead);
@endphp
<div class="crm-lead-panel crm-lead-ticket" data-crm-lead-panel data-lead-id="{{ $lead->id }}">
    <header class="crm-lead-ticket__header">
        <div class="crm-lead-ticket__identity">
            <span class="crm-lead-avatar crm-lead-avatar--panel" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials($lead->full_name) }}</span>
            <div class="min-w-0">
                <div class="crm-lead-ticket__ref">Lead #{{ $lead->id }}</div>
                @can('update leads')
                    <h2 class="crm-lead-ticket__title crm-lead-ticket__title--editable"
                        data-crm-panel-title
                        data-crm-lead-rename
                        tabindex="0"
                        title="Click to rename">{{ $lead->full_name }}</h2>
                @else
                    <h2 class="crm-lead-ticket__title" data-crm-panel-title>{{ $lead->full_name }}</h2>
                @endcan
                <div class="crm-lead-ticket__contact">
                    @if($lead->email)
                        <a href="mailto:{{ $lead->email }}" class="crm-lead-ticket__contact-chip" onclick="event.stopPropagation()">
                            <iconify-icon icon="solar:letter-linear"></iconify-icon>{{ $lead->email }}
                        </a>
                    @endif
                    @if($lead->phone)
                        <a href="tel:{{ $lead->phone }}" class="crm-lead-ticket__contact-chip" onclick="event.stopPropagation()">
                            <iconify-icon icon="solar:phone-linear"></iconify-icon>{{ $lead->phone }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="crm-lead-ticket__controls">
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
        <div class="crm-lead-ticket__alert {{ $followUp->attention ? 'is-attention' : '' }}">
            <iconify-icon icon="solar:bell-bing-linear"></iconify-icon>
            <div>
                <strong>{{ $followUp->label }}</strong>
                <span>{{ $followUp->detail }}</span>
            </div>
        </div>
    @endif

    <div class="crm-lead-ticket__layout">
        <div class="crm-lead-ticket__main">
            @include('admin.crm.leads.partials.import-summary', ['lead' => $lead, 'importSummary' => $importSummary])

            <div class="crm-lead-ticket__toolbar">
                @can('update leads')
                    <button type="button" class="crm-lead-ticket__tool" data-crm-panel-edit data-lead-id="{{ $lead->id }}">
                        <iconify-icon icon="solar:pen-linear"></iconify-icon>
                        <span>Edit</span>
                    </button>
                    @if($lead->email)
                        <a href="{{ route('admin.crm.leads.email.form', $lead) }}" class="crm-lead-ticket__tool">
                            <iconify-icon icon="solar:letter-linear"></iconify-icon>
                            <span>Email</span>
                        </a>
                    @endif
                @endcan
                @can('convert leads')
                    @if(!$lead->is_converted)
                        <form method="POST"
                              action="{{ route('admin.crm.leads.convert', $lead) }}"
                              class="crm-lead-ticket__tool-form"
                              data-crm-confirm
                              data-confirm-title="Convert lead to customer?"
                              data-confirm-message="This will create a customer record from this lead and keep the original lead history linked."
                              data-confirm-label="Convert to Customer"
                              data-confirm-tone="success"
                              data-confirm-icon="solar:users-group-rounded-linear">
                            @csrf
                            <button class="crm-lead-ticket__tool crm-lead-ticket__tool--primary" type="submit">
                                <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                                <span>Convert</span>
                            </button>
                        </form>
                    @elseif($lead->customer)
                        <a href="{{ route('admin.crm.customers.show', $lead->customer) }}" class="crm-lead-ticket__tool crm-lead-ticket__tool--success">
                            <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                            <span>View customer</span>
                        </a>
                    @endif
                @endcan
            </div>

            @if($lead->lead_description)
                <section class="crm-lead-ticket__block">
                    <h3 class="crm-lead-ticket__block-title">Description</h3>
                    <div class="crm-lead-ticket__description">{{ $lead->lead_description }}</div>
                </section>
            @endif

            @if($lead->formEntry)
                <section class="crm-lead-ticket__block">
                    <div class="crm-lead-ticket__block-head">
                        <h3 class="crm-lead-ticket__block-title"><iconify-icon icon="solar:inbox-in-linear"></iconify-icon> Form submission</h3>
                        <a href="{{ route('admin.crm.form-entries.show', $lead->formEntry) }}" class="crm-lead-ticket__block-link" onclick="event.stopPropagation()">
                            View full submission
                        </a>
                    </div>
                    <div class="crm-lead-ticket__form-meta">
                        @include('admin.crm.partials.crm-source-badge', ['source' => $lead->source ?: 'form_submission'])
                        @if($lead->formEntry->form)
                            <span class="crm-lead-ticket__form-name">
                                <iconify-icon icon="solar:document-text-linear"></iconify-icon>
                                {{ $lead->formEntry->form->name }}
                            </span>
                        @endif
                        @if($lead->formEntry->submitted_at)
                            <span class="crm-lead-ticket__form-date">
                                Submitted {{ $lead->formEntry->submitted_at->format('M j, Y g:i A') }}
                            </span>
                        @endif
                    </div>
                    @include('admin.crm.partials.form-submission-preview', ['formEntry' => $lead->formEntry, 'limit' => 5])
                </section>
            @endif

            <section class="crm-lead-ticket__block">
                <div class="crm-lead-ticket__block-head">
                    <h3 class="crm-lead-ticket__block-title"><iconify-icon icon="solar:chat-round-dots-linear"></iconify-icon> Comments</h3>
                    <span class="crm-lead-ticket__block-meta">{{ $comments->count() }} total</span>
                </div>

                <div class="crm-lead-ticket__comments" data-crm-comments-list>
                    @if($hiddenCommentCount > 0)
                        <div class="crm-lead-ticket__comments-hidden" data-crm-comments-hidden hidden>
                            @foreach($comments->slice(2)->reverse() as $note)
                                @include('admin.crm.leads.partials.comment-item', ['note' => $note, 'admins' => $admins])
                            @endforeach
                        </div>
                    @endif

                    @forelse($visibleComments->reverse() as $note)
                        @include('admin.crm.leads.partials.comment-item', ['note' => $note, 'admins' => $admins])
                    @empty
                        <p class="crm-lead-ticket__empty">No comments yet. Start the conversation below.</p>
                    @endforelse
                </div>

                @if($hiddenCommentCount > 0)
                    <button type="button" class="crm-lead-ticket__show-more" data-crm-show-more="comments">
                        Show {{ $hiddenCommentCount }} more comment{{ $hiddenCommentCount === 1 ? '' : 's' }}
                    </button>
                @endif

                @can('update leads')
                    @include('admin.crm.leads.partials.comment-editor', ['lead' => $lead, 'admins' => $admins])
                @endcan
            </section>

            <details class="crm-lead-ticket__block crm-lead-ticket__block--collapsible">
                <summary class="crm-lead-ticket__block-head crm-lead-ticket__block-head--toggle">
                    <h3 class="crm-lead-ticket__block-title"><iconify-icon icon="solar:history-linear"></iconify-icon> Activity</h3>
                    <span class="crm-lead-ticket__block-meta">{{ $activities->count() }} event{{ $activities->count() === 1 ? '' : 's' }}</span>
                </summary>
                <div class="crm-lead-ticket__timeline" data-crm-activity-list>
                    @if($hiddenActivityCount > 0)
                        <div class="crm-lead-ticket__timeline-hidden" data-crm-activity-hidden hidden>
                            @foreach($activities->slice(2) as $activity)
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
                            @endforeach
                        </div>
                    @endif

                    @forelse($visibleActivities as $activity)
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
                    @empty
                        <p class="crm-lead-ticket__empty">No activity logged yet.</p>
                    @endforelse
                </div>

                @if($hiddenActivityCount > 0)
                    <button type="button" class="crm-lead-ticket__show-more" data-crm-show-more="activity">
                        Show {{ $hiddenActivityCount }} more activit{{ $hiddenActivityCount === 1 ? 'y' : 'ies' }}
                    </button>
                @endif
            </details>
        </div>

        <aside class="crm-lead-ticket__sidebar">
            <h3 class="crm-lead-ticket__sidebar-title">Details</h3>
            <dl class="crm-lead-ticket__properties">
                <div class="crm-lead-ticket__property">
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
                <div class="crm-lead-ticket__property">
                    <dt>Company</dt>
                    <dd>{{ $lead->company ?? '—' }}</dd>
                </div>
                <div class="crm-lead-ticket__property">
                    <dt>Created</dt>
                    <dd>{{ $lead->created_at->format('M j, Y') }}</dd>
                </div>
                <div class="crm-lead-ticket__property">
                    <dt>Last contacted</dt>
                    <dd>{{ optional($lead->last_contacted_at)->diffForHumans() ?? '—' }}</dd>
                </div>
                <div class="crm-lead-ticket__property">
                    <dt>Follow-up</dt>
                    <dd>{{ $followUp->hasFollowUp() ? $followUp->label : 'None scheduled' }}</dd>
                </div>
                <div class="crm-lead-ticket__property">
                    <dt>Appointment</dt>
                    <dd>{{ optional($lead->appointment_date)->format('M j, Y g:i A') ?? '—' }}</dd>
                </div>
                @if(!$lead->formEntry && $lead->source && $lead->source !== 'manual')
                    <div class="crm-lead-ticket__property">
                        <dt>Source</dt>
                        <dd>@include('admin.crm.partials.crm-source-badge', ['source' => $lead->source, 'label' => $lead->lead_source ?? null])</dd>
                    </div>
                @endif
                @if($importSummary && ($importSummary['sidebar'] ?? []) !== [])
                    <div class="crm-lead-ticket__property crm-lead-ticket__property--stack">
                        <dt>From import</dt>
                        <dd class="crm-lead-ticket__import-chips">
                            @foreach($importSummary['sidebar'] as $item)
                                <span class="crm-field-chip crm-field-chip--{{ $item['tone'] }}" title="{{ $item['label'] }}">
                                    <span class="crm-field-chip__key">{{ $item['label'] }}</span>
                                    <span class="crm-field-chip__val">{{ $item['value'] }}</span>
                                </span>
                            @endforeach
                        </dd>
                    </div>
                @endif
            </dl>
        </aside>
    </div>
</div>
