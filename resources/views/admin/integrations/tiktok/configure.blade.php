@extends('admin.layouts.app')
@section('title', 'Configure TikTok form')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Configure TikTok form',
        'subtitle' => 'Choose how this Instant Form should create CRM leads later',
        'showBreadcrumb' => true,
        'hideFlash' => true,
        'breadcrumbs' => [
            ['label' => 'Integrations', 'url' => route('admin.integrations.hub')],
            ['label' => 'TikTok', 'url' => route('admin.integrations.tiktok.show')],
            ['label' => 'Configure form'],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0 mb-24">
        <div class="card-body p-24">
            <h5 class="mb-4">{{ $mapping->external_form_name }}</h5>
            <p class="text-sm text-secondary-light mb-0">Form reference: {{ $mapping->external_form_id }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.integrations.tiktok.forms.update', $mapping) }}">
        @csrf
        @method('PUT')

        <div class="card radius-12 shadow-2 border-0 mb-24">
            <div class="card-body p-24">
                <h6 class="mb-16">Lead setup</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="lead_source_label">Lead source</label>
                        <input type="text" name="lead_source_label" id="lead_source_label" class="form-control radius-8 @error('lead_source_label') is-invalid @enderror" value="{{ old('lead_source_label', $mapping->lead_source_label) }}" required>
                        @error('lead_source_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="assigned_to">Lead assignment</label>
                        <select name="assigned_to" id="assigned_to" class="form-select radius-8 @error('assigned_to') is-invalid @enderror">
                            <option value="">Unassigned</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" @selected((string) old('assigned_to', $mapping->assigned_to) === (string) $admin->id)>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="priority">Priority</label>
                        <select name="priority" id="priority" class="form-select radius-8" required>
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', $mapping->priority?->value ?? $mapping->priority) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-12">
                            <input type="hidden" name="auto_create_lead" value="0">
                            <input class="form-check-input" type="checkbox" name="auto_create_lead" value="1" id="auto_create_lead" @checked((string) old('auto_create_lead', $mapping->auto_create_lead ? '1' : '0') === '1')>
                            <label class="form-check-label" for="auto_create_lead">Auto-create CRM lead</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-12">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked((string) old('is_active', $mapping->is_active ? '1' : '0') === '1')>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card radius-12 shadow-2 border-0 mb-24">
            <div class="card-body p-24">
                <h6 class="mb-8">Field mapping</h6>
                <p class="text-sm text-secondary-light mb-20">Match each TikTok question to a CRM lead field. Leave as “Do not map” for questions you do not want in a specific CRM field.</p>

                @if(count($fields) === 0)
                    <p class="text-sm text-secondary-light mb-0">TikTok did not return any questions for this Instant Form yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>TikTok field</th>
                                    <th>CRM field</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fields as $field)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $field['label'] }}</div>
                                            <code class="text-xs">{{ $field['id'] }}</code>
                                        </td>
                                        <td>
                                            <select name="field_mapping[{{ $field['id'] }}]" class="form-select radius-8">
                                                <option value="">Do not map</option>
                                                @foreach($crmFields as $crmKey => $crmLabel)
                                                    <option value="{{ $crmKey }}" @selected(old('field_mapping.'.$field['id'], $field['mapped_to']) === $crmKey)>{{ $crmLabel }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex flex-wrap gap-8">
            <button type="submit" class="btn btn-primary-600 radius-8">Save mapping</button>
            <a href="{{ route('admin.integrations.tiktok.show') }}" class="btn btn-outline-neutral-500 radius-8">Cancel</a>
        </div>
    </form>
</div>
@endsection
