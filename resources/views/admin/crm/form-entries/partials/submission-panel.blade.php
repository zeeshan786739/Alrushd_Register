@php
    $contact = $contact ?? \App\Support\FormEntryContact::fromEntry($formEntry);
@endphp
<div class="crm-lead-panel crm-submission-panel" data-crm-submission-panel data-form-entry-id="{{ $formEntry->id }}">
    <header class="crm-lead-panel__header">
        <div class="crm-lead-panel__identity">
            <span class="crm-lead-avatar crm-lead-avatar--panel crm-lead-avatar--submission" aria-hidden="true">
                {{ \App\Support\UserManagementHelper::initials($contact['display_name']) }}
            </span>
            <div class="min-w-0">
                <div class="crm-lead-panel__ref">Form submission #{{ $formEntry->id }}</div>
                <h2 class="crm-lead-panel__title">{{ $contact['display_name'] }}</h2>
                <div class="crm-lead-panel__contact">
                    @if($contact['email'])
                        <a href="mailto:{{ $contact['email'] }}" class="crm-lead-panel__contact-chip" onclick="event.stopPropagation()">
                            <iconify-icon icon="solar:letter-linear"></iconify-icon>{{ $contact['email'] }}
                        </a>
                    @endif
                    @if($contact['phone'])
                        <a href="tel:{{ $contact['phone'] }}" class="crm-lead-panel__contact-chip" onclick="event.stopPropagation()">
                            <iconify-icon icon="solar:phone-linear"></iconify-icon>{{ $contact['phone'] }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="crm-lead-panel__controls">
            @include('admin.crm.partials.crm-source-badge', ['source' => 'form_submission'])
            <span class="crm-status-pill crm-status-pill--tone-warning">Pending intake</span>
            @if($formEntry->form)
                <span class="crm-category-badge crm-category-badge--info">
                    <iconify-icon icon="solar:document-text-linear"></iconify-icon>
                    {{ $formEntry->form->name }}
                </span>
            @endif
        </div>
    </header>

    <div class="crm-lead-panel__alert">
        <iconify-icon icon="solar:inbox-in-linear"></iconify-icon>
        <div>
            <strong>Not in pipeline yet</strong>
            <span>This submission is waiting to be added as a lead. Convert it to assign, follow up, and move through stages.</span>
        </div>
    </div>

    <div class="crm-lead-panel__toolbar">
        @can('convert form submissions')
            <form method="POST"
                  action="{{ route('admin.crm.form-entries.convert-lead', $formEntry) }}"
                  class="crm-lead-panel__tool-form"
                  data-crm-convert-submission>
                @csrf
                <button class="crm-lead-panel__tool crm-lead-panel__tool--primary" type="submit">
                    <iconify-icon icon="solar:user-hand-up-linear"></iconify-icon>
                    <span>Add to pipeline</span>
                </button>
            </form>
        @endcan
        <a href="{{ route('admin.crm.form-entries.show', $formEntry) }}" class="crm-lead-panel__tool" target="_blank" rel="noopener">
            <iconify-icon icon="solar:arrow-right-up-linear"></iconify-icon>
            <span>Full submission</span>
        </a>
    </div>

    <dl class="crm-lead-panel__properties">
        <div class="crm-lead-panel__property">
            <dt>Submitted</dt>
            <dd>{{ optional($formEntry->submitted_at)->format('M j, Y g:i A') ?? '—' }}</dd>
        </div>
        <div class="crm-lead-panel__property">
            <dt>Status</dt>
            <dd>@include('admin.crm.partials.status-pill', ['status' => $formEntry->status])</dd>
        </div>
        @if($contact['company'])
            <div class="crm-lead-panel__property">
                <dt>Company</dt>
                <dd>{{ $contact['company'] }}</dd>
            </div>
        @endif
    </dl>

    <section class="crm-lead-panel__section crm-lead-panel__section--form">
        <div class="crm-lead-panel__section-head">
            <h3><iconify-icon icon="solar:document-text-linear"></iconify-icon> Submission fields</h3>
        </div>
        <div class="crm-lead-panel__section-body">
            @include('admin.crm.partials.form-submission-preview', ['formEntry' => $formEntry, 'limit' => 8])
        </div>
    </section>
</div>
