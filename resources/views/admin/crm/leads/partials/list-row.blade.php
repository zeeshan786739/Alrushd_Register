@php $followUp = \App\Support\LeadFollowUpState::forLead($lead); @endphp
<article class="crm-list-row crm-lead-row"
         data-crm-list-row
         data-crm-lead-open
         data-lead-id="{{ $lead->id }}"
         data-current-status="{{ $lead->lead_status }}"
         tabindex="0"
         role="button"
         aria-label="Open lead {{ $lead->full_name }}">
    @canany(['update leads', 'assign leads'])
        <label class="crm-list-row__select" onclick="event.stopPropagation()">
            <input type="checkbox"
                   class="crm-list-select"
                   data-crm-lead-select
                   value="{{ $lead->id }}"
                   aria-label="Select {{ $lead->full_name }}">
        </label>
    @else
        <span class="crm-list-row__select crm-list-row__select--spacer" aria-hidden="true"></span>
    @endcanany
    @can('update leads')
        <div class="crm-list-row__handle"
             data-crm-list-drag
             draggable="true"
             title="Drag to change status"
             aria-label="Drag lead to change status">
            <iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon>
        </div>
    @else
        <span class="crm-list-row__handle crm-list-row__handle--spacer" aria-hidden="true"></span>
    @endcan

    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials($lead->full_name) }}</span>
        <div class="min-w-0">
            <div class="crm-list-row__name">{{ $lead->full_name }}</div>
            <div class="crm-list-row__meta">{{ $lead->email ?? $lead->phone ?? 'No contact saved' }}</div>
            @if($lead->source === 'form_submission' || $lead->formEntry)
                <div class="crm-list-row__source">
                    @include('admin.crm.partials.crm-source-badge', ['source' => $lead->source ?: 'form_submission', 'compact' => true])
                    @if($lead->formEntry?->form)
                        <span class="crm-list-row__form-name">{{ Str::limit($lead->formEntry->form->name, 32) }}</span>
                    @endif
                </div>
            @endif
            @if($lead->category)
                <span class="crm-category-badge crm-category-badge--{{ $lead->category->displayTone() }}">
                    <iconify-icon icon="{{ $lead->category->displayIcon() }}"></iconify-icon>
                    {{ $lead->category->name }}
                </span>
            @endif
        </div>
    </div>

    <div class="crm-list-row__field">
        <span class="crm-list-row__label">Status</span>
        @can('update leads')
            @include('admin.crm.partials.inline-control', [
                'field' => 'lead_status',
                'value' => $lead->lead_status,
                'recordId' => $lead->id,
                'idAttr' => 'data-lead-id',
                'options' => $statusInlineOptions,
                'ariaLabel' => 'Status for '.$lead->full_name,
            ])
        @else
            @include('admin.crm.partials.status-pill', ['status'=>$lead->lead_status])
        @endcan
    </div>

    <div class="crm-list-row__field">
        <span class="crm-list-row__label">Priority</span>
        @can('update leads')
            @include('admin.crm.partials.inline-control', [
                'field' => 'priority',
                'value' => $lead->priority,
                'recordId' => $lead->id,
                'idAttr' => 'data-lead-id',
                'options' => $priorityInlineOptions,
                'ariaLabel' => 'Priority for '.$lead->full_name,
            ])
        @else
            @include('admin.crm.partials.status-pill', ['status'=>$lead->priority])
        @endcan
    </div>

    <div class="crm-list-row__field">
        <span class="crm-list-row__label">Assigned</span>
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
            <span class="crm-list-row__value">{{ $lead->assignedAdmin?->name ?? 'Unassigned' }}</span>
        @endcan
    </div>

    <div class="crm-list-row__field">
        <span class="crm-list-row__label">Follow-up</span>
        @if($followUp->hasFollowUp())
            <span class="{{ $followUp->badgeClass }}">
                @if($followUp->attention)<span class="crm-followup-dot" aria-hidden="true"></span>@endif
                {{ $followUp->label }}
            </span>
        @else
            <span class="crm-list-row__value">—</span>
        @endif
    </div>

    <div class="crm-list-row__field crm-list-row__field--date">
        <span class="crm-list-row__label">Created</span>
        <span class="crm-list-row__value">{{ $lead->created_at->format('M j, Y') }}</span>
    </div>

    <div class="crm-list-row__actions">
        @can('update leads')
            <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="crm-list-action is-edit" title="Edit lead" aria-label="Edit lead" onclick="event.stopPropagation()">
                <iconify-icon icon="solar:pen-linear"></iconify-icon>
            </a>
        @endcan
        @can('delete leads')
            <form action="{{ route('admin.crm.leads.destroy', $lead) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">
                @csrf @method('DELETE')
                <button type="submit" class="crm-list-action is-delete" title="Delete lead" aria-label="Delete lead">
                    <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                </button>
            </form>
        @endcan
    </div>
</article>
