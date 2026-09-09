@php
    $due = \App\Support\ProjectDueState::forProject($project);
    $canUpdate = auth('admin')->user()?->can('update projects');
    $canDelete = auth('admin')->user()?->can('delete projects');
    $showUrl = route('admin.crm.projects.show', $project);
@endphp
<article class="crm-list-row crm-list-row--status-{{ $project->status }} crm-list-row--priority-{{ $project->priority }}"
         data-crm-record-open data-href="{{ $showUrl }}" tabindex="0" role="button"
         aria-label="Open project {{ $project->name }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list">{{ \App\Support\UserManagementHelper::initials($project->name) }}</span>
        <div class="crm-list-row__identity-copy">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $project->name }}</span>
                <span class="crm-list-row__id">{{ $project->project_code }}</span>
            </div>
            @if($project->customer)
                <div class="crm-list-row__contact-line">
                    <span class="crm-list-row__contact"><iconify-icon icon="solar:user-linear"></iconify-icon>{{ Str::limit($project->customer->name, 28) }}</span>
                </div>
            @endif
        </div>
    </div>
    <div class="crm-list-row__field crm-list-row__field--customer">
        <span class="crm-list-row__contact">{{ Str::limit($project->customer?->name ?? '—', 20) }}</span>
    </div>
    <div class="crm-list-row__field crm-list-row__field--status">
        @if($canUpdate)
            @include('admin.crm.partials.inline-control', ['field' => 'status', 'value' => $project->status, 'recordId' => $project->id, 'options' => $statusInlineOptions, 'ariaLabel' => 'Status for '.$project->name])
        @else
            @include('admin.crm.partials.status-pill', ['status' => $project->status])
        @endif
    </div>
    <div class="crm-list-row__field crm-list-row__field--priority">
        @if($canUpdate)
            @include('admin.crm.partials.inline-control', ['field' => 'priority', 'value' => $project->priority, 'recordId' => $project->id, 'options' => $priorityInlineOptions, 'ariaLabel' => 'Priority for '.$project->name])
        @else
            @include('admin.crm.partials.status-pill', ['status' => $project->priority])
        @endif
    </div>
    <div class="crm-list-row__field crm-list-row__field--progress">
        <div class="crm-list-row__progress">
            <div class="crm-list-row__progress-bar"><span style="width:{{ (int) $project->progress }}%"></span></div>
            <span class="crm-list-row__progress-label">{{ (int) $project->progress }}%</span>
        </div>
    </div>
    <div class="crm-list-row__field crm-list-row__field--due">
        @if($due->applies ?? false)
            <span class="{{ $due->badgeClass }}">{{ $due->label }}</span>
        @else
            <span class="crm-list-row__empty">{{ $project->end_date?->format('M j, Y') ?? '—' }}</span>
        @endif
    </div>
    <div class="crm-list-row__field crm-list-row__field--assignee">
        @if($canUpdate)
            @include('admin.crm.partials.inline-control', ['field' => 'assigned_to', 'value' => $project->assigned_to ?? '', 'recordId' => $project->id, 'options' => $ownerInlineOptions, 'owner' => true, 'ariaLabel' => 'Owner for '.$project->name])
        @else
            <span class="crm-list-row__assignee"><iconify-icon icon="solar:user-linear"></iconify-icon>{{ $project->assignedAdmin?->name ?? 'Unassigned' }}</span>
        @endif
    </div>
    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canUpdate)<a href="{{ route('admin.crm.projects.edit', $project) }}" class="crm-list-action" onclick="event.stopPropagation()"><iconify-icon icon="solar:pen-linear"></iconify-icon></a>@endif
            @if($canDelete)
                <form action="{{ route('admin.crm.projects.destroy', $project) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">@csrf @method('DELETE')
                    <button type="submit" class="crm-list-action is-delete"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
                </form>
            @endif
        </div>
        <button type="button" class="crm-list-row__chevron" data-crm-record-open-trigger onclick="event.stopPropagation()"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></button>
    </div>
</article>
