@php
    $statusTone = match($form->external_status) {
        'PUBLISHED' => 'success',
        'EDITED' => 'warning',
        default => 'tiktok',
    };
    $mappingTone = match($form->mappingStatus()) {
        'configured' => 'success',
        'disabled' => 'tiktok',
        default => 'warning',
    };
@endphp
<article @class(['int-list-row', 'int-list-row--tiktok-form', 'int-list-row--'.$statusTone])>
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <div class="crm-list-row__identity-copy min-w-0">
            <span class="crm-list-row__name">{{ $form->external_form_name }}</span>
            <span class="crm-list-row__contact"><code class="text-xs">{{ $form->external_form_id }}</code></span>
        </div>
    </div>
    <div class="crm-list-row__field">
        @if($form->external_status === 'PUBLISHED')
            <span class="int-status-badge int-status-badge--success">Ready</span>
        @elseif($form->external_status === 'EDITED')
            <span class="int-status-badge int-status-badge--warning">Draft</span>
        @elseif(filled($form->external_status))
            <span class="int-status-badge int-status-badge--neutral">{{ $form->external_status }}</span>
        @else
            <span class="int-status-badge int-status-badge--neutral">Unknown</span>
        @endif
    </div>
    <div class="crm-list-row__field">
        @if($form->mappingStatus() === 'configured')
            <span class="int-status-badge int-status-badge--success">Configured</span>
        @elseif($form->mappingStatus() === 'disabled')
            <span class="int-status-badge int-status-badge--neutral">Disabled</span>
        @else
            <span class="int-status-badge int-status-badge--warning">Needs setup</span>
        @endif
    </div>
    <div class="crm-list-row__field">{{ $form->assignedAdmin?->name ?? 'Unassigned' }}</div>
    <div class="crm-list-row__field">{{ $form->lead_source_label ?: '—' }}</div>
    <div class="crm-list-row__actions">
        @can('manage integrations')
            <a href="{{ route('admin.integrations.tiktok.forms.configure', $form) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Configure</a>
        @endcan
    </div>
</article>
