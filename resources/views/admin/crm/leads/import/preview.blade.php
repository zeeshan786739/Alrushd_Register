@extends('admin.layouts.app')
@section('title', 'Preview Import')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Preview import',
        'subtitle' => $import->original_filename,
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => 'Mapping', 'url' => route('admin.crm.leads.import.map', $import)],
            ['label' => 'Preview'],
        ],
    ])

    <div class="row g-3 mb-24">
        <div class="col-sm-6 col-xl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Total','value'=>$import->total_rows,'icon'=>'solar:document-linear','tone'=>'navy'])</div>
        <div class="col-sm-6 col-xl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Ready','value'=>$import->ready_rows,'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
        <div class="col-sm-6 col-xl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Warnings','value'=>$import->warning_rows,'icon'=>'solar:danger-triangle-linear','tone'=>'amber'])</div>
        <div class="col-sm-6 col-xl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Duplicates','value'=>$import->duplicate_rows,'icon'=>'solar:copy-linear','tone'=>'gold'])</div>
        <div class="col-sm-6 col-xl-2">@include('admin.partials.dashboard-stat-card', ['label'=>'Invalid','value'=>$import->failed_rows,'icon'=>'solar:close-circle-linear','tone'=>'navy'])</div>
    </div>

    @if($previousImportCount > 0)
        <div class="alert alert-warning radius-8">This file hash was imported previously ({{ $previousImportCount }} completed batch{{ $previousImportCount > 1 ? 'es' : '' }}).</div>
    @endif

    @if($unmapped)
        <div class="alert alert-info radius-8">Unmapped columns will be stored as additional lead information: {{ implode(', ', $unmapped) }}</div>
    @endif

    <div class="card radius-12 shadow-2 border-0 mb-24">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Status</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        @php $fields = $row->normalized_data['fields'] ?? []; @endphp
                        <tr>
                            <td>{{ $row->row_number }}</td>
                            <td><span class="{{ $row->statusEnum()->badgeClass() }} px-8 py-4 radius-8 text-sm">{{ $row->statusEnum()->label() }}</span></td>
                            <td>{{ $fields['first_name'] ?? '—' }} {{ $fields['last_name'] ?? '' }}</td>
                            <td>{{ $fields['email'] ?? '—' }}</td>
                            <td>{{ $fields['phone'] ?? '—' }}</td>
                            <td class="text-sm text-secondary-light">
                                {{ implode(' ', $row->warnings ?? []) }}
                                {{ implode(' ', $row->errors ?? []) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-40 text-secondary-light">No data rows found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.crm.leads.import.confirm', $import) }}">
        @csrf
        <div class="form-check mb-16">
            <input class="form-check-input" type="checkbox" name="confirm" id="confirm" value="1" required>
            <label class="form-check-label" for="confirm">I confirm these rows should be imported into the current organization.</label>
        </div>
        @error('confirm')<div class="text-danger text-sm mb-12">{{ $message }}</div>@enderror
        <a href="{{ route('admin.crm.leads.import.map', $import) }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11">Back to mapping</a>
        <button type="submit" class="btn btn-primary-600 radius-8 px-20 py-11">Confirm import</button>
    </form>
</div>
@endsection
