@php
    $contact = $entry->contact ?? \App\Support\FormEntryContact::fromEntry($entry);
@endphp
<article class="crm-board-card crm-board-card--submission"
         data-crm-submission-open
         data-form-entry-id="{{ $entry->id }}"
         tabindex="0"
         role="button"
         aria-label="Open form submission from {{ $contact['display_name'] }}">
    <span class="crm-board-card__priority-rail crm-board-card__priority-rail--submission" aria-hidden="true"></span>

    <div class="crm-board-card__main">
        <div class="crm-board-card__header">
            <div class="crm-board-card__ref">
                <span class="crm-board-card__submission-badge">
                    <iconify-icon icon="solar:inbox-in-linear"></iconify-icon>
                    Form intake
                </span>
            </div>
            <div class="crm-board-card__flags">
                <span class="crm-board-card__flag is-pending" title="Pending conversion">
                    <iconify-icon icon="solar:clock-circle-linear"></iconify-icon>
                </span>
            </div>
        </div>

        <div class="crm-board-card__body">
            <div class="crm-board-card__title">{{ $contact['display_name'] }}</div>
            <div class="crm-board-card__meta text-truncate" title="{{ $contact['email'] ?? $contact['phone'] }}">
                {{ $contact['email'] ?? $contact['phone'] ?? 'No contact saved' }}
            </div>
            @if($entry->form)
                <div class="crm-board-card__source-row">
                    <span class="crm-board-card__form-name" title="{{ $entry->form->name }}">
                        <iconify-icon icon="solar:document-text-linear" aria-hidden="true"></iconify-icon>
                        {{ Str::limit($entry->form->name, 28) }}
                    </span>
                </div>
            @endif
            @if($contact['preview'])
                <div class="crm-board-card__submission-preview">{{ Str::limit($contact['preview'], 72) }}</div>
            @endif
        </div>

        <div class="crm-board-card__footer">
            <div class="crm-board-card__footer-left">
                <span class="crm-board-card__submitted-at">
                    <iconify-icon icon="solar:calendar-linear"></iconify-icon>
                    {{ optional($entry->submitted_at)->diffForHumans() ?? 'Recently' }}
                </span>
            </div>
            <div class="crm-board-card__footer-right">
                @can('convert form submissions')
                    <form method="POST"
                          action="{{ route('admin.crm.form-entries.convert-lead', $entry) }}"
                          class="crm-board-card__convert-form"
                          data-crm-convert-submission
                          onclick="event.stopPropagation()">
                        @csrf
                        <button type="submit" class="crm-board-card__convert-btn">Add to pipeline</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</article>
