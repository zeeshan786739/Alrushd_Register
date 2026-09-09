@php
    $followUp = \App\Support\LeadFollowUpState::forLead($lead);
    $canUpdate = auth('admin')->user()?->can('update leads');
    $canAssign = auth('admin')->user()?->can('assign leads');
    $canDelete = auth('admin')->user()?->can('delete leads');
@endphp
<article class="crm-list-row crm-lead-row crm-list-row--priority-{{ $lead->priority }}"
         data-crm-list-row
         data-crm-lead-open
         data-lead-id="{{ $lead->id }}"
         data-current-status="{{ $lead->lead_status }}"
         tabindex="0"
         role="button"
         aria-label="Open lead {{ $lead->full_name }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>

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

    <span class="crm-list-row__handle" aria-hidden="true" title="Drag to reorder">
        <iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon>
    </span>

    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials($lead->full_name) }}</span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $lead->full_name }}</span>
                <span class="crm-list-row__id">#{{ $lead->id }}</span>
                @if($followUp->attention)
                    <span class="crm-list-row__attention" title="{{ $followUp->label }}">
                        <iconify-icon icon="solar:bell-bing-linear"></iconify-icon>
                    </span>
                @endif
            </div>
            <div class="crm-list-row__contact-line">
                @if($lead->email)
                    <span class="crm-list-row__contact" title="{{ $lead->email }}">
                        <iconify-icon icon="solar:letter-linear" aria-hidden="true"></iconify-icon>
                        {{ Str::limit($lead->email, 28) }}
                    </span>
                @endif
                @if($lead->phone)
                    <span class="crm-list-row__contact" title="{{ $lead->phone }}">
                        <iconify-icon icon="solar:phone-linear" aria-hidden="true"></iconify-icon>
                        {{ $lead->phone }}
                    </span>
                @endif
                @if(! $lead->email && ! $lead->phone)
                    <span class="crm-list-row__contact crm-list-row__contact--muted">No contact saved</span>
                @endif
            </div>
            <div class="crm-list-row__tags">
                @if($lead->source)
                    @include('admin.crm.partials.crm-source-badge', ['source' => $lead->source, 'compact' => true])
                @endif
                @if($lead->formEntry?->form)
                    <span class="crm-list-row__form-name">{{ Str::limit($lead->formEntry->form->name, 24) }}</span>
                @elseif($lead->leadImport?->original_filename)
                    <span class="crm-list-row__form-name">{{ Str::limit($lead->leadImport->original_filename, 24) }}</span>
                @endif
                @if($lead->category)
                    <span class="crm-category-badge crm-category-badge--{{ $lead->category->displayTone() }} crm-category-badge--compact">
                        <iconify-icon icon="{{ $lead->category->displayIcon() }}"></iconify-icon>
                        {{ $lead->category->name }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="crm-list-row__field crm-list-row__field--status">
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

    <div class="crm-list-row__field crm-list-row__field--priority">
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

    <div class="crm-list-row__field crm-list-row__field--assignee">
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
            <span class="crm-list-row__assignee">
                <iconify-icon icon="solar:user-linear" aria-hidden="true"></iconify-icon>
                {{ $lead->assignedAdmin?->name ?? 'Unassigned' }}
            </span>
        @endcan
    </div>

    <div class="crm-list-row__field crm-list-row__field--followup">
        @if($followUp->hasFollowUp())
            <span class="{{ $followUp->badgeClass }}">
                @if($followUp->attention)<span class="crm-followup-dot" aria-hidden="true"></span>@endif
                {{ $followUp->label }}
            </span>
        @else
            <span class="crm-list-row__empty">—</span>
        @endif
    </div>

    <div class="crm-list-row__field crm-list-row__field--date" title="{{ $lead->created_at->format('M j, Y g:i A') }}">
        <span class="crm-list-row__date">{{ $lead->created_at->diffForHumans(short: true) }}</span>
        <span class="crm-list-row__date-sub">{{ $lead->created_at->format('M j, Y') }}</span>
    </div>

    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canUpdate)
                <button type="button"
                        class="crm-list-action"
                        data-crm-panel-edit
                        data-lead-id="{{ $lead->id }}"
                        title="Edit lead"
                        aria-label="Edit {{ $lead->full_name }}"
                        onclick="event.stopPropagation()">
                    <iconify-icon icon="solar:pen-linear"></iconify-icon>
                </button>
            @endif
            @if($canDelete && ! $lead->is_converted)
                <form action="{{ route('admin.crm.leads.destroy', $lead) }}" method="POST" class="d-inline" data-crm-lead-delete onclick="event.stopPropagation()">
                    @csrf @method('DELETE')
                    <button type="submit" class="crm-list-action is-delete" title="Remove from view" aria-label="Remove {{ $lead->full_name }} from view">
                        <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                    </button>
                </form>
            @endif
        </div>
        <button type="button"
                class="crm-list-row__chevron"
                data-crm-lead-open-trigger
                title="View lead details"
                aria-label="View {{ $lead->full_name }}"
                onclick="event.stopPropagation()">
            <iconify-icon icon="solar:alt-arrow-right-linear" aria-hidden="true"></iconify-icon>
        </button>
    </div>
</article>
