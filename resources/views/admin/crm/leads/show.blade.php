@extends('admin.layouts.app')
@section('title', $lead->full_name)
@section('content')
@include('admin.crm.partials.styles')
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
@endphp
<div class="dashboard-main-body">
    <div class="crm-workspace-header mb-20">
        <div class="crm-workspace-header__top">
            <div class="min-w-0">
                <div class="text-sm text-secondary-light mb-4">
                    <a href="{{ route('admin.crm.leads.index') }}" class="text-secondary-light text-decoration-none">Leads</a>
                    <span class="mx-4">/</span>
                    <span>{{ $lead->full_name }}</span>
                </div>
                <h1 class="crm-workspace-header__title">{{ $lead->full_name }}</h1>
                <div class="crm-workspace-header__contact">
                    @if($lead->email)
                        <span><iconify-icon icon="solar:letter-linear"></iconify-icon> <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></span>
                    @endif
                    @if($lead->phone)
                        <span><iconify-icon icon="solar:phone-linear"></iconify-icon> {{ $lead->phone }}</span>
                    @endif
                    @if($lead->company)
                        <span><iconify-icon icon="solar:buildings-linear"></iconify-icon> {{ $lead->company }}</span>
                    @endif
                    <span><iconify-icon icon="solar:user-linear"></iconify-icon> {{ $lead->assignedAdmin?->name ?? 'Unassigned' }}</span>
                </div>
                <div class="crm-workspace-header__badges">
                    @include('admin.crm.partials.status-pill', ['status'=>$lead->lead_status])
                    @include('admin.crm.partials.status-pill', ['status'=>$lead->priority])
                    @include('admin.crm.partials.lead-source-badge', ['source'=>$lead->source, 'label'=>$lead->lead_source ?? null])
                    @if($lead->is_converted)<span class="crm-status-pill crm-status-pill--accepted">Converted</span>@endif
                    @if($followUp->hasFollowUp())
                        <span class="{{ $followUp->badgeClass }}">
                            @if($followUp->attention)<span class="crm-followup-dot" aria-hidden="true"></span>@endif
                            {{ $followUp->label }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="crm-workspace-header__actions">
                @can('update leads')
                    <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:pen-linear"></iconify-icon> Edit</a>
                    @if($lead->email)
                        <a href="{{ route('admin.crm.leads.email.form', $lead) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:letter-linear"></iconify-icon> Email Lead</a>
                    @endif
                @endcan
                @can('compose emails')
                    @if($lead->email)
                        <a href="{{ route('admin.email.compose', ['to'=>$lead->email,'lead_id'=>$lead->id,'subject'=>'Follow up']) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:pen-new-square-linear"></iconify-icon> Compose</a>
                    @endif
                @endcan
                @can('convert leads')
                    @if(!$lead->is_converted)
                        <form method="POST"
                              action="{{ route('admin.crm.leads.convert', $lead) }}"
                              class="d-inline"
                              data-crm-confirm
                              data-confirm-title="Convert lead to customer?"
                              data-confirm-message="This will create a customer record from this lead and keep the original lead history linked."
                              data-confirm-label="Convert to Customer"
                              data-confirm-tone="success"
                              data-confirm-icon="solar:users-group-rounded-linear">
                            @csrf
                            <button class="btn btn-primary-600 radius-8 px-16 py-10" type="submit">
                                <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon> Convert to Customer
                            </button>
                        </form>
                    @elseif($lead->customer)
                        <a href="{{ route('admin.crm.customers.show', $lead->customer) }}" class="btn btn-outline-success radius-8 px-16 py-10">View Customer</a>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    @if($followUp->hasFollowUp())
        <div class="crm-followup-alert crm-followup-alert--{{ $followUp->state }} {{ $followUp->attention ? 'crm-followup-alert--attention' : '' }} mb-20" role="status">
            <div class="crm-followup-alert__main">
                <div class="crm-followup-alert__icon"><iconify-icon icon="solar:bell-bing-linear"></iconify-icon></div>
                <div>
                    <p class="crm-followup-alert__title mb-0">
                        @if($followUp->state === 'overdue') Missed follow-up
                        @elseif($followUp->state === 'due_now') Follow-up due now
                        @elseif($followUp->state === 'due_soon') Follow-up due soon
                        @else Upcoming follow-up
                        @endif
                    </p>
                    <p class="crm-followup-alert__meta">{{ $followUp->detail }} · {{ $followUp->label }}</p>
                </div>
            </div>
            @can('update leads')
            <div class="crm-followup-alert__actions">
                <a href="#lead-follow-up-form" class="btn btn-sm btn-outline-neutral-500 radius-8">Reschedule</a>
                <form method="POST" action="{{ route('admin.crm.leads.follow-up.complete', $lead) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary-600 radius-8">Mark complete</button>
                </form>
            </div>
            @endcan
        </div>
    @endif

    <div class="row g-3 mb-20">
        <div class="col-lg-7">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:user-id-linear"></iconify-icon> Contact &amp; overview</h6>
                    <div class="row g-3">
                        <div class="col-md-6"><strong class="text-sm text-secondary-light d-block">Email</strong>{{ $lead->email ?? '—' }}</div>
                        <div class="col-md-6"><strong class="text-sm text-secondary-light d-block">Phone</strong>{{ $lead->phone ?? '—' }}</div>
                        <div class="col-md-6"><strong class="text-sm text-secondary-light d-block">Company</strong>{{ $lead->company ?? '—' }}</div>
                        <div class="col-md-6"><strong class="text-sm text-secondary-light d-block">Source</strong>{{ $lead->lead_source ?? $lead->source ?? '—' }}</div>
                        @if($lead->formEntry)
                        <div class="col-md-6"><strong class="text-sm text-secondary-light d-block">Form Submission</strong><a href="{{ route('admin.crm.form-entries.show', $lead->formEntry) }}">View submission #{{ $lead->formEntry->id }}</a></div>
                        @endif
                        <div class="col-md-6"><strong class="text-sm text-secondary-light d-block">Assigned To</strong>{{ $lead->assignedAdmin?->name ?? 'Unassigned' }}</div>
                        <div class="col-md-6"><strong class="text-sm text-secondary-light d-block">Estimated Value</strong>{{ $lead->estimated_value ? number_format($lead->estimated_value, 2) : '—' }}</div>
                        <div class="col-md-6"><strong class="text-sm text-secondary-light d-block">Created</strong>{{ $lead->created_at->format('M j, Y H:i') }}</div>
                        <div class="col-12"><strong class="text-sm text-secondary-light d-block">Description</strong>{{ $lead->lead_description ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:widget-linear"></iconify-icon> Workflow snapshot</h6>
                    <div class="row g-3">
                        <div class="col-6"><strong class="text-sm text-secondary-light d-block">Status</strong>@include('admin.crm.partials.status-pill', ['status'=>$lead->lead_status])</div>
                        <div class="col-6"><strong class="text-sm text-secondary-light d-block">Priority</strong>@include('admin.crm.partials.status-pill', ['status'=>$lead->priority])</div>
                        <div class="col-6"><strong class="text-sm text-secondary-light d-block">Next follow-up</strong><span class="text-sm">{{ $followUp->hasFollowUp() ? $followUp->label : 'None scheduled' }}</span></div>
                        <div class="col-6"><strong class="text-sm text-secondary-light d-block">Appointment</strong><span class="text-sm">{{ optional($lead->appointment_date)->format('M j, Y H:i') ?? '—' }}</span></div>
                        <div class="col-6"><strong class="text-sm text-secondary-light d-block">Last contacted</strong><span class="text-sm">{{ optional($lead->last_contacted_at)->diffForHumans() ?? '—' }}</span></div>
                        <div class="col-6"><strong class="text-sm text-secondary-light d-block">Contacts</strong><span class="text-sm">{{ (int) $lead->contact_count }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($lead->isFromFacebook() && $lead->metaLeadSubmission)
    @php $meta = $lead->metaLeadSubmission; @endphp
    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <div class="d-flex align-items-center gap-8 mb-16">
                <iconify-icon icon="logos:facebook" width="22"></iconify-icon>
                <h6 class="fw-semibold mb-0">Facebook Lead Ads</h6>
            </div>
            <div class="row g-3 text-sm">
                <div class="col-md-6"><strong>Form mapping</strong><br>{{ $meta->formMapping?->internal_label ?? '—' }}</div>
                <div class="col-md-6"><strong>Meta lead ID</strong><br><code>{{ $meta->meta_leadgen_id }}</code></div>
                <div class="col-md-4"><strong>Form ID</strong><br>{{ $meta->meta_form_id ?? '—' }}</div>
                <div class="col-md-4"><strong>Ad ID</strong><br>{{ $meta->meta_ad_id ?? '—' }}</div>
                <div class="col-md-4"><strong>Campaign ID</strong><br>{{ $meta->meta_campaign_id ?? '—' }}</div>
                <div class="col-md-6"><strong>Received</strong><br>{{ $meta->created_at->format('M j, Y H:i') }}</div>
            </div>
        </div>
    </div>
    @endif

    @include('admin.crm.leads.partials.import-attribution')

    @include('admin.crm.partials.email-history-list')

    <div class="row g-3 mb-20">
        <div class="col-lg-7">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:notes-linear"></iconify-icon> Notes</h6>
                    @forelse($lead->notes as $note)
                        <div class="border-bottom pb-12 mb-12">
                            <div class="d-flex justify-content-between"><strong>{{ $note->admin?->name }}</strong><span class="text-sm text-secondary-light">{{ $note->created_at->diffForHumans() }}</span></div>
                            <p class="mb-0 mt-4">{{ $note->note }}</p>
                        </div>
                    @empty
                        <p class="text-secondary-light mb-0">No notes yet.</p>
                    @endforelse
                    @can('update leads')
                    <form method="POST" action="{{ route('admin.crm.leads.notes.store', $lead) }}" class="mt-20">
                        @csrf
                        <textarea name="note" class="form-control radius-8 mb-12" rows="3" placeholder="Add a note..." required></textarea>
                        <button type="submit" class="btn btn-primary-600 radius-8 btn-sm">Add Note</button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            @can('update leads')
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:bolt-linear"></iconify-icon> Quick actions</h6>

                    <div class="crm-quick-group">
                        <div class="crm-quick-group__label">Lead workflow</div>
                        <form method="POST" action="{{ route('admin.crm.leads.status.update', $lead) }}" class="mb-12">@csrf @method('PATCH')
                            <label class="form-label text-sm">Status</label>
                            <select name="lead_status" class="form-select radius-8 mb-8">@foreach(\App\Enums\LeadStatus::cases() as $status)<option value="{{ $status->value }}" @selected($lead->lead_status==$status->value)>{{ $status->label() }}</option>@endforeach</select>
                            <button class="btn btn-outline-primary-600 btn-sm radius-8 w-100">Update Status</button>
                        </form>
                        @can('assign leads')
                        <form method="POST" action="{{ route('admin.crm.leads.assign', $lead) }}">@csrf @method('PATCH')
                            <label class="form-label text-sm">Assign To</label>
                            <select name="assigned_to" class="form-select radius-8 mb-8">@foreach($admins as $admin)<option value="{{ $admin->id }}" @selected($lead->assigned_to==$admin->id)>{{ $admin->name }}</option>@endforeach</select>
                            <button class="btn btn-outline-primary-600 btn-sm radius-8 w-100">Assign</button>
                        </form>
                        @endcan
                    </div>

                    <div class="crm-quick-group" id="lead-follow-up-form">
                        <div class="crm-quick-group__label">Follow-up</div>
                        <form method="POST" action="{{ route('admin.crm.leads.follow-up', $lead) }}">@csrf @method('PATCH')
                            <label class="form-label text-sm" for="next_follow_up_date">Date</label>
                            <input type="date" id="next_follow_up_date" name="next_follow_up_date" class="form-control radius-8 mb-8" value="{{ old('next_follow_up_date', optional($lead->next_follow_up_date)->format('Y-m-d')) }}" required>
                            <label class="form-label text-sm" for="next_follow_up_time">Time</label>
                            <input type="time" id="next_follow_up_time" name="next_follow_up_time" class="form-control radius-8 mb-8" value="{{ old('next_follow_up_time', $lead->next_follow_up_time ? \Illuminate\Support\Str::of($lead->next_follow_up_time)->substr(0,5) : '') }}">
                            <label class="form-label text-sm" for="next_follow_up_type">Type</label>
                            <input type="text" id="next_follow_up_type" name="next_follow_up_type" class="form-control radius-8 mb-8" value="{{ old('next_follow_up_type', $lead->next_follow_up_type) }}" placeholder="Call, email, visit">
                            <button class="btn btn-outline-primary-600 btn-sm radius-8 w-100">{{ $lead->next_follow_up_date ? 'Reschedule Follow-up' : 'Schedule Follow-up' }}</button>
                        </form>
                    </div>

                    <div class="crm-quick-group">
                        <div class="crm-quick-group__label">Appointment</div>
                        <form method="POST" action="{{ route('admin.crm.leads.appointment', $lead) }}">@csrf @method('PATCH')
                            <label class="form-label text-sm" for="appointment_date">Date &amp; time</label>
                            <input type="datetime-local" id="appointment_date" name="appointment_date" class="form-control radius-8 mb-8" value="{{ old('appointment_date', optional($lead->appointment_date)->format('Y-m-d\TH:i')) }}" required>
                            <label class="form-label text-sm" for="appointment_type">Type</label>
                            <select id="appointment_type" name="appointment_type" class="form-select radius-8 mb-8" required>
                                <option value="school_visit" @selected(old('appointment_type', $lead->appointment_type)==='school_visit')>School Visit</option>
                                <option value="online_meeting" @selected(old('appointment_type', $lead->appointment_type)==='online_meeting')>Online Meeting</option>
                                <option value="phone_call" @selected(old('appointment_type', $lead->appointment_type)==='phone_call')>Phone Call</option>
                            </select>
                            <label class="form-label text-sm" for="appointment_notes">Notes</label>
                            <textarea id="appointment_notes" name="appointment_notes" class="form-control radius-8 mb-8" rows="2">{{ old('appointment_notes', $lead->appointment_notes) }}</textarea>
                            <button class="btn btn-outline-primary-600 btn-sm radius-8 w-100">Schedule Appointment</button>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:lock-linear"></iconify-icon> Quick actions</h6>
                    <p class="text-secondary-light mb-0">You do not have permission to update this lead.</p>
                </div>
            </div>
            @endcan
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0 crm-activity-card mb-24">
        <div class="card-body p-24">
            <h6 class="crm-section-title"><iconify-icon icon="solar:history-linear"></iconify-icon> Recent activity</h6>
            <div class="crm-activity-scroll">
                @forelse($lead->activities as $activity)
                    <div class="crm-activity-item">
                        <div class="crm-activity-icon">
                            <iconify-icon icon="{{ $activityIcons[$activity->activity_type] ?? 'solar:info-circle-linear' }}"></iconify-icon>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm fw-semibold text-capitalize">{{ str_replace('_', ' ', $activity->activity_type) }}</div>
                            <div class="text-sm">{{ $activity->description }}</div>
                            <div class="text-xs text-secondary-light mt-4">
                                {{ $activity->admin?->name ?? 'System' }} · {{ $activity->created_at->format('M j, Y g:i A') }} · {{ $activity->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-secondary-light mb-0">No activity logged yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
