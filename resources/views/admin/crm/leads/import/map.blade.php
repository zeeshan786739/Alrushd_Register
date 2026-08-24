@extends('admin.layouts.app')
@section('title', 'Map Import Columns')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Map columns',
        'subtitle' => $import->original_filename,
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => 'Import', 'url' => route('admin.crm.leads.import.create')],
            ['label' => 'Category', 'url' => route('admin.crm.leads.import.category', $import)],
            ['label' => 'Mapping'],
        ],
    ])

    @if($import->category)
        <div class="alert alert-light border radius-8 mb-16">
            Category for this batch: <strong>{{ $import->category->name }}</strong>
            <a href="{{ route('admin.crm.leads.import.category', $import) }}" class="ms-8">Change</a>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.crm.leads.import.map.save', $import) }}">
        @csrf
        <div class="card radius-12 shadow-2 border-0 mb-24">
            <div class="card-body p-24">
                <div class="row g-3">
                    @if(count($parsed['sheets']) > 1)
                    <div class="col-md-4">
                        <label class="form-label" for="selected_sheet">Sheet</label>
                        <select name="selected_sheet" id="selected_sheet" class="form-select radius-8">
                            @foreach($parsed['sheets'] as $sheet)
                                <option value="{{ $sheet['name'] }}" @selected($import->selected_sheet === $sheet['name'])>{{ $sheet['name'] }} ({{ $sheet['row_count'] }} rows)</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="selected_sheet" value="{{ $import->selected_sheet }}">
                    @endif
                    <div class="col-md-4">
                        <label class="form-label" for="header_row">Header row</label>
                        <input type="number" min="1" name="header_row" id="header_row" class="form-control radius-8" value="{{ old('header_row', $import->header_row) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="source_label">Import label</label>
                        <input type="text" name="options[source_label]" id="source_label" class="form-control radius-8" value="{{ old('options.source_label', $import->option('source_label') ?: $import->original_filename) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="default_status">Default status</label>
                        <select name="options[default_status]" id="default_status" class="form-select radius-8">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('options.default_status', $import->option('default_status', 'new')) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="default_priority">Default priority</label>
                        <select name="options[default_priority]" id="default_priority" class="form-select radius-8">
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(old('options.default_priority', $import->option('default_priority', 'medium')) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="default_assigned_to">Default assignee</label>
                        <select name="options[default_assigned_to]" id="default_assigned_to" class="form-select radius-8">
                            <option value="">Unassigned</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" @selected((string) old('options.default_assigned_to', $import->option('default_assigned_to')) === (string) $admin->id)>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="duplicate_behavior">Duplicates</label>
                        <select name="options[duplicate_behavior]" id="duplicate_behavior" class="form-select radius-8">
                            <option value="skip" @selected(old('options.duplicate_behavior', $import->option('duplicate_behavior', 'skip')) === 'skip')>Skip likely duplicates</option>
                            <option value="create" @selected(old('options.duplicate_behavior', $import->option('duplicate_behavior')) === 'create')>Create anyway</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="default_calling_code">Default calling code</label>
                        <input type="text" name="options[default_calling_code]" id="default_calling_code" class="form-control radius-8" placeholder="92" value="{{ old('options.default_calling_code', $import->option('default_calling_code')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="date_format">Date format (optional)</label>
                        <input type="text" name="options[date_format]" id="date_format" class="form-control radius-8" placeholder="d/m/Y" value="{{ old('options.date_format', $import->option('date_format')) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card radius-12 shadow-2 border-0 mb-24">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Source column</th>
                                <th>Sample values</th>
                                <th>Suggested field</th>
                                <th>Confidence</th>
                                <th>Mapping</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($parsed['headers'] as $header)
                            @php
                                $suggestion = $suggestions[$header['key']] ?? ['field' => 'custom', 'confidence' => 'low', 'reason' => ''];
                                $selected = old('mapping.'.$header['key'], $import->mapping[$header['key']] ?? $suggestion['field']);
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $header['label'] }}</td>
                                <td class="text-sm text-secondary-light">{{ implode(' · ', $parsed['sample_values'][$header['key']] ?? []) }}</td>
                                <td>{{ $fieldOptions[$suggestion['field']] ?? $suggestion['field'] }}</td>
                                <td>
                                    <span class="crm-status-pill">{{ strtoupper($suggestion['confidence'] ?? 'low') }}</span>
                                    <div class="text-xs text-secondary-light">{{ $suggestion['reason'] ?? '' }}</div>
                                </td>
                                <td>
                                    <select name="mapping[{{ $header['key'] }}]" class="form-select radius-8">
                                        @foreach($fieldOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($selected === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary-600 radius-8 px-20 py-11">Preview import</button>
    </form>
</div>
@endsection
