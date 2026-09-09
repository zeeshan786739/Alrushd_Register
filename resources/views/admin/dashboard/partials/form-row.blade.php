@php
    $label = $form->displayLabel();
    $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $label) ?: 'FM', 0, 2));
@endphp
<a href="{{ route('admin.form-manager.entries', $form) }}" class="crm-list-row dash-list-row dash-list-row--form {{ $form->is_active ? '' : 'is-inactive' }}" aria-label="Open {{ $label }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list" aria-hidden="true">{{ $initials }}</span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $label }}</span>
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">
                    <iconify-icon icon="solar:document-text-linear" aria-hidden="true"></iconify-icon>
                    {{ $form->slug }}
                </span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__field">
        <span @class(['dash-row-badge', 'is-live' => $form->is_active, 'is-draft' => ! $form->is_active])>
            {{ $form->is_active ? 'Live' : 'Inactive' }}
        </span>
    </div>
    <div class="crm-list-row__field crm-list-row__field--date">
        <span class="crm-list-row__date">{{ number_format($form->entries_count) }} submissions</span>
        <span class="crm-list-row__date-sub">{{ $form->hasPlacement('landing') ? 'On landing page' : 'Not on landing' }}</span>
    </div>
    <div class="crm-list-row__actions">
        <span class="crm-list-row__chevron" aria-hidden="true">
            <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
        </span>
    </div>
</a>
