@php
    $isLead = ($rowType ?? '') === 'lead';
    $label = $isLead
        ? (trim(($row->first_name ?? '').' '.($row->last_name ?? '')) ?: ($row->email ?: 'Lead #'.$row->id))
        : ($row->form?->displayLabel() ?? 'Form submission');
    $initials = \App\Support\UserManagementHelper::initials($label);
    $meta = $isLead
        ? ($row->created_at?->diffForHumans(short: true).' · '.ucfirst(str_replace('_', ' ', (string) $row->lead_status)))
        : ($row->submitted_at?->diffForHumans(short: true) ?? 'Recently');
    $typeLabel = $isLead ? 'New lead' : 'Submission';
    $icon = $isLead ? 'solar:user-hand-up-linear' : 'solar:document-text-linear';
    $url = $isLead
        ? route('admin.crm.leads.show', $row)
        : ($row->form ? route('admin.form-manager.entries.show', [$row->form, $row]) : route('admin.form-manager.index'));
    $statusBadge = $isLead ? null : ($row->status ? ucfirst($row->status) : null);
@endphp
<a href="{{ $url }}" class="crm-list-row dash-list-row dash-list-row--{{ $isLead ? 'lead' : 'activity' }}" aria-label="Open {{ $label }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list" aria-hidden="true">{{ $initials }}</span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $label }}</span>
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">
                    <iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon>
                    {{ $typeLabel }}
                </span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__field">
        @if($statusBadge)
            <span class="dash-row-badge is-neutral">{{ $statusBadge }}</span>
        @elseif($isLead)
            <span class="dash-row-badge is-live">{{ ucfirst(str_replace('_', ' ', (string) $row->lead_status)) }}</span>
        @else
            <span class="dash-row-badge is-neutral">Form</span>
        @endif
    </div>
    <div class="crm-list-row__field crm-list-row__field--date">
        <span class="crm-list-row__date">{{ $meta }}</span>
    </div>
    <div class="crm-list-row__actions">
        <span class="crm-list-row__chevron" aria-hidden="true">
            <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
        </span>
    </div>
</a>
