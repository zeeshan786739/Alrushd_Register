@php
    $contact = $entry->contact ?? \App\Support\FormEntryContact::fromEntry($entry);
@endphp
<article class="crm-list-row crm-submission-row"
         data-crm-submission-open
         data-form-entry-id="{{ $entry->id }}"
         tabindex="0"
         role="button"
         aria-label="Open form submission from {{ $contact['display_name'] }}">
    <span class="crm-list-row__select crm-list-row__select--spacer" aria-hidden="true"></span>
    <span class="crm-list-row__handle crm-list-row__handle--spacer" aria-hidden="true"></span>

    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--submission" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials($contact['display_name']) }}</span>
        <div class="min-w-0">
            <div class="crm-list-row__name">{{ $contact['display_name'] }}</div>
            <div class="crm-list-row__meta">{{ $contact['email'] ?? $contact['phone'] ?? 'No contact saved' }}</div>
            <div class="crm-list-row__source">
                @include('admin.crm.partials.crm-source-badge', ['source' => 'form_submission', 'compact' => true])
                @if($entry->form)
                    <span class="crm-list-row__form-name">{{ Str::limit($entry->form->name, 32) }}</span>
                @endif
                <span class="crm-list-row__intake-badge">Pending intake</span>
            </div>
        </div>
    </div>

    <div class="crm-list-row__field">
        <span class="crm-list-row__label">Status</span>
        <span class="crm-status-pill crm-status-pill--tone-warning">Intake</span>
    </div>

    <div class="crm-list-row__field">
        <span class="crm-list-row__label">Priority</span>
        <span class="crm-list-row__value">—</span>
    </div>

    <div class="crm-list-row__field">
        <span class="crm-list-row__label">Assigned</span>
        <span class="crm-list-row__value">Unassigned</span>
    </div>

    <div class="crm-list-row__field">
        <span class="crm-list-row__label">Follow-up</span>
        <span class="crm-list-row__value">—</span>
    </div>

    <div class="crm-list-row__field crm-list-row__field--date">
        <span class="crm-list-row__label">Submitted</span>
        <span class="crm-list-row__value">{{ optional($entry->submitted_at)->format('M j, Y') ?? '—' }}</span>
    </div>

    <div class="crm-list-row__actions">
        @can('convert form submissions')
            <form action="{{ route('admin.crm.form-entries.convert-lead', $entry) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">
                @csrf
                <button type="submit" class="crm-list-action is-convert" title="Add to pipeline" aria-label="Add to pipeline">
                    <iconify-icon icon="solar:user-hand-up-linear"></iconify-icon>
                </button>
            </form>
        @endcan
    </div>
</article>
