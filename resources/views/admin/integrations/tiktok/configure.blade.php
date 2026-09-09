@extends('admin.layouts.app')
@section('title', 'Configure TikTok form')
@section('content')
<div class="dashboard-main-body" id="integrations-workspace-page">
@include('admin.integrations.partials.shell', [
    'activeTab' => 'tiktok',
    'compact' => true,
    'shellTitle' => 'Configure TikTok form',
    'shellSubtitle' => 'Choose how this Instant Form should create CRM leads later',
    'shellBreadcrumbs' => [
        ['label' => 'Integrations', 'url' => route('admin.integrations.hub')],
        ['label' => 'TikTok', 'url' => route('admin.integrations.tiktok.show')],
        ['label' => 'Configure form'],
    ],
    'hideFlash' => true,
    'shellActions' => [[
        'label' => 'Back to TikTok',
        'url' => route('admin.integrations.tiktok.show'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<div class="int-page-body">
    <div class="int-panel mb-16">
        <div class="int-panel__body">
            <h2 class="int-panel__title mb-4">{{ $mapping->external_form_name }}</h2>
            <p class="int-panel__sub mb-0">Form reference: {{ $mapping->external_form_id }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.integrations.tiktok.forms.update', $mapping) }}" class="um-form-page">
        @csrf
        @method('PUT')

        <div class="int-panel mb-16">
            <div class="int-panel__head">
                <div>
                    <h3 class="int-panel__title">Lead setup</h3>
                    <p class="int-panel__sub">Defaults applied when this form creates CRM leads</p>
                </div>
            </div>
            <div class="int-panel__body">
                <div class="int-form-grid">
                    <div class="int-form-field">
                        <label for="lead_source_label">Lead source</label>
                        <input type="text" name="lead_source_label" id="lead_source_label" class="form-control radius-8 @error('lead_source_label') is-invalid @enderror" value="{{ old('lead_source_label', $mapping->lead_source_label) }}" required>
                        @error('lead_source_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="int-form-field">
                        <label for="assigned_to">Lead assignment</label>
                        <select name="assigned_to" id="assigned_to" class="form-select radius-8 @error('assigned_to') is-invalid @enderror">
                            <option value="">Unassigned</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" @selected((string) old('assigned_to', $mapping->assigned_to) === (string) $admin->id)>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="int-form-field">
                        <label for="priority">Priority</label>
                        <select name="priority" id="priority" class="form-select radius-8" required>
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', $mapping->priority?->value ?? $mapping->priority) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="int-form-field d-flex align-items-end gap-16">
                        <div class="form-check mb-12">
                            <input type="hidden" name="auto_create_lead" value="0">
                            <input class="form-check-input" type="checkbox" name="auto_create_lead" value="1" id="auto_create_lead" @checked((string) old('auto_create_lead', $mapping->auto_create_lead ? '1' : '0') === '1')>
                            <label class="form-check-label" for="auto_create_lead">Auto-create CRM lead</label>
                        </div>
                        <div class="form-check mb-12">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked((string) old('is_active', $mapping->is_active ? '1' : '0') === '1')>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="int-panel mb-16">
            <div class="int-panel__head">
                <div>
                    <h3 class="int-panel__title">Field mapping</h3>
                    <p class="int-panel__sub">Match each TikTok question to a CRM lead field. Leave as “Do not map” for questions you do not want in a specific CRM field.</p>
                </div>
            </div>
            <div class="int-panel__body int-panel__body--flush">
                @if(count($fields) === 0)
                    <div class="crm-leads-list-empty">
                        <iconify-icon icon="solar:document-linear"></iconify-icon>
                        <strong>No TikTok fields yet</strong>
                        <span>TikTok did not return any questions for this Instant Form yet.</span>
                    </div>
                @else
                    <div class="crm-leads-table">
                        <div class="crm-leads-table__head crm-leads-table__head--fields" aria-hidden="true">
                            <span>TikTok field</span><span>CRM field</span>
                        </div>
                        <div class="crm-leads-list">
                            @foreach($fields as $field)
                                <article class="int-list-row int-list-row--field int-list-row--tiktok">
                                    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
                                    <div class="crm-list-row__identity">
                                        <div class="crm-list-row__identity-copy min-w-0">
                                            <span class="crm-list-row__name">{{ $field['label'] }}</span>
                                            <span class="crm-list-row__contact"><code class="text-xs">{{ $field['id'] }}</code></span>
                                        </div>
                                    </div>
                                    <div class="crm-list-row__field">
                                        <select name="field_mapping[{{ $field['id'] }}]" class="form-select radius-8">
                                            <option value="">Do not map</option>
                                            @foreach($crmFields as $crmKey => $crmLabel)
                                                <option value="{{ $crmKey }}" @selected(old('field_mapping.'.$field['id'], $field['mapped_to']) === $crmKey)>{{ $crmLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="int-form-save-bar">
            <a href="{{ route('admin.integrations.tiktok.show') }}" class="btn btn-outline-neutral-500 radius-8">Cancel</a>
            <button type="submit" class="btn btn-primary-600 radius-8">Save mapping</button>
        </div>
    </form>
</div>
</div>
@endsection
