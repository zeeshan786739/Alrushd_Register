@php
    $followUp = \App\Support\LeadFollowUpState::forLead($lead);
    $canUpdate = auth('admin')->user()?->can('update leads');
    $canAssign = auth('admin')->user()?->can('assign leads');
    $canDelete = auth('admin')->user()?->can('delete leads');
@endphp
<article class="crm-board-card crm-board-card--priority-{{ $lead->priority }}"
         data-crm-board-card
         data-crm-lead-open
         data-lead-id="{{ $lead->id }}"
         data-current-status="{{ $lead->lead_status }}"
         tabindex="0"
         role="button"
         aria-label="Open lead {{ $lead->full_name }}"
         draggable="{{ $canUpdate ? 'true' : 'false' }}">
    <span class="crm-board-card__priority-rail" aria-hidden="true"></span>

    <div class="crm-board-card__main">
        <div class="crm-board-card__header">
            <div class="crm-board-card__ref">
                @if($lead->category)
                    <span class="crm-board-card__category-pill crm-category-badge crm-category-badge--{{ $lead->category->displayTone() }}">
                        <iconify-icon icon="{{ $lead->category->displayIcon() }}"></iconify-icon>
                        {{ $lead->category->name }}
                    </span>
                @endif
                <span class="crm-board-card__lead-id">Lead #{{ $lead->id }}</span>
            </div>
            <div class="crm-board-card__flags">
                @if($lead->is_converted)
                    <span class="crm-board-card__flag is-converted" title="Converted"><iconify-icon icon="solar:check-circle-linear"></iconify-icon></span>
                @elseif($followUp->attention)
                    <span class="crm-board-card__flag is-attention" title="{{ $followUp->label }}"><iconify-icon icon="solar:bell-bing-linear"></iconify-icon></span>
                @endif
                @if($canUpdate)
                    <button type="button"
                            class="crm-board-card__quick-action"
                            data-crm-panel-edit
                            data-lead-id="{{ $lead->id }}"
                            title="Edit lead"
                            aria-label="Edit {{ $lead->full_name }}">
                        <iconify-icon icon="solar:pen-linear"></iconify-icon>
                    </button>
                @endif
                @if($canDelete && ! $lead->is_converted)
                    <form action="{{ route('admin.crm.leads.destroy', $lead) }}" method="POST" class="d-inline" data-crm-lead-delete onclick="event.stopPropagation()">
                        @csrf @method('DELETE')
                        <button type="submit" class="crm-board-card__quick-action is-danger" title="Remove from view" aria-label="Remove {{ $lead->full_name }} from view">
                            <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="crm-board-card__identity">
            <span class="crm-lead-avatar crm-lead-avatar--board" aria-hidden="true">
                {{ \App\Support\UserManagementHelper::initials($lead->full_name) }}
            </span>
            <div class="crm-board-card__identity-copy min-w-0">
                <div class="crm-board-card__title">{{ $lead->full_name }}</div>
                @if($lead->company)
                    <div class="crm-board-card__company">{{ $lead->company }}</div>
                @endif
            </div>
        </div>

        <div class="crm-board-card__contacts">
            @if($lead->email)
                <span class="crm-board-card__contact" title="{{ $lead->email }}">
                    <iconify-icon icon="solar:letter-linear" aria-hidden="true"></iconify-icon>
                    <span>{{ Str::limit($lead->email, 26) }}</span>
                </span>
            @endif
            @if($lead->phone)
                <span class="crm-board-card__contact" title="{{ $lead->phone }}">
                    <iconify-icon icon="solar:phone-linear" aria-hidden="true"></iconify-icon>
                    <span>{{ $lead->phone }}</span>
                </span>
            @endif
            @if(!$lead->email && !$lead->phone)
                <span class="crm-board-card__contact crm-board-card__contact--empty">No contact saved</span>
            @endif
        </div>

        <div class="crm-board-card__tags">
            @include('admin.crm.leads.partials.admission-tags', ['lead' => $lead, 'compact' => true, 'limit' => 5])
            @if($lead->source || $lead->formEntry)
                @include('admin.crm.partials.crm-source-badge', ['source' => $lead->source ?: 'form_submission', 'compact' => true])
                @if($lead->formEntry?->form)
                    <span class="crm-board-card__form-name" title="{{ $lead->formEntry->form->name }}">
                        <iconify-icon icon="solar:document-text-linear" aria-hidden="true"></iconify-icon>
                        {{ Str::limit($lead->formEntry->form->name, 22) }}
                    </span>
                @endif
            @endif
            @if($followUp->hasFollowUp())
                <span class="crm-board-card__followup {{ $followUp->attention ? 'is-attention' : '' }}">
                    <iconify-icon icon="solar:calendar-linear" aria-hidden="true"></iconify-icon>
                    {{ $followUp->label }}
                </span>
            @endif
        </div>

        <div class="crm-board-card__record-meta">
            @if($lead->created_at)
                <time datetime="{{ $lead->created_at->toIso8601String() }}" title="{{ $lead->created_at->format('M j, Y g:i A') }}">
                    Added {{ $lead->created_at->format('M j, Y') }}
                </time>
            @endif
            @if(!$followUp->hasFollowUp())
                <span class="crm-board-card__record-note">No follow-up</span>
            @endif
        </div>

        <div class="crm-board-card__footer">
            <div class="crm-board-card__footer-left">
                @if($canUpdate)
                    @include('admin.crm.partials.inline-control', [
                        'field' => 'priority',
                        'value' => $lead->priority,
                        'recordId' => $lead->id,
                        'idAttr' => 'data-lead-id',
                        'options' => $priorityInlineOptions,
                        'ariaLabel' => 'Priority for '.$lead->full_name,
                    ])
                @else
                    @include('admin.crm.partials.status-pill', ['status' => $lead->priority])
                @endif
            </div>
            <div class="crm-board-card__footer-right">
                @if($canAssign)
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
                    <span class="crm-board-card__assignee-static">
                        <span class="crm-lead-avatar crm-lead-avatar--assignee" aria-hidden="true">
                            {{ \App\Support\UserManagementHelper::initials($lead->assignedAdmin?->name ?? 'U') }}
                        </span>
                        <span data-crm-board-assignee-label>{{ $lead->assignedAdmin?->name ?? 'Unassigned' }}</span>
                    </span>
                @endif
            </div>
        </div>
    </div>
</article>
