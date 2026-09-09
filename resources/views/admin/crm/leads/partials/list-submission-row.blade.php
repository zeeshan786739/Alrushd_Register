@php
    $contact = $entry->contact ?? \App\Support\FormEntryContact::fromEntry($entry);
@endphp
<article class="crm-list-row crm-submission-row crm-list-row--intake"
         data-crm-submission-open
         data-form-entry-id="{{ $entry->id }}"
         tabindex="0"
         role="button"
         aria-label="Open form submission from {{ $contact['display_name'] }}">
    <span class="crm-list-row__priority-rail crm-list-row__priority-rail--intake" aria-hidden="true"></span>

    <span class="crm-list-row__select crm-list-row__select--spacer" aria-hidden="true"></span>

    <span class="crm-list-row__handle crm-list-row__handle--spacer" aria-hidden="true"></span>

    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--submission" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials($contact['display_name']) }}</span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $contact['display_name'] }}</span>
                <span class="crm-list-row__intake-badge">Pending intake</span>
            </div>
            <div class="crm-list-row__contact-line">
                @if($contact['email'])
                    <span class="crm-list-row__contact" title="{{ $contact['email'] }}">
                        <iconify-icon icon="solar:letter-linear" aria-hidden="true"></iconify-icon>
                        {{ Str::limit($contact['email'], 28) }}
                    </span>
                @endif
                @if($contact['phone'])
                    <span class="crm-list-row__contact" title="{{ $contact['phone'] }}">
                        <iconify-icon icon="solar:phone-linear" aria-hidden="true"></iconify-icon>
                        {{ $contact['phone'] }}
                    </span>
                @endif
            </div>
            <div class="crm-list-row__tags">
                @include('admin.crm.partials.crm-source-badge', ['source' => 'form_submission', 'compact' => true])
                @if($entry->form)
                    <span class="crm-list-row__form-name">{{ Str::limit($entry->form->name, 24) }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="crm-list-row__field crm-list-row__field--status">
        <span class="crm-status-pill crm-status-pill--tone-warning">Intake</span>
    </div>

    <div class="crm-list-row__field crm-list-row__field--priority">
        <span class="crm-list-row__empty">—</span>
    </div>

    <div class="crm-list-row__field crm-list-row__field--assignee">
        <span class="crm-list-row__assignee crm-list-row__assignee--muted">
            <iconify-icon icon="solar:user-linear" aria-hidden="true"></iconify-icon>
            Unassigned
        </span>
    </div>

    <div class="crm-list-row__field crm-list-row__field--followup">
        <span class="crm-list-row__empty">—</span>
    </div>

    <div class="crm-list-row__field crm-list-row__field--date">
        <span class="crm-list-row__date">{{ optional($entry->submitted_at)->diffForHumans(short: true) ?? '—' }}</span>
        <span class="crm-list-row__date-sub">{{ optional($entry->submitted_at)->format('M j, Y') ?? '—' }}</span>
    </div>

    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @can('convert form submissions')
                <form action="{{ route('admin.crm.form-entries.convert-lead', $entry) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">
                    @csrf
                    <button type="submit" class="crm-list-action is-convert" title="Add to pipeline" aria-label="Add to pipeline">
                        <iconify-icon icon="solar:user-hand-up-linear"></iconify-icon>
                    </button>
                </form>
            @endcan
        </div>
        <span class="crm-list-row__chevron" aria-hidden="true">
            <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
        </span>
    </div>
</article>
