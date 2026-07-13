@extends('admin.layouts.app')

@section('title', $form->name.' — Submissions')

@section('content')
@include('admin.form-manager.partials.styles')

@php
    $currentStatus = request('status', '');
    $previewKeys = ['full_name', 'name', 'first_name', 'email', 'phone', 'mobile', 'job_title', 'company'];
@endphp

<div class="dashboard-main-body">
    @include('admin.form-manager.partials.header', [
        'title' => $form->name,
        'subtitle' => ($entryStats['total'] ?? $entries->total()).' total submissions stored in form_entries',
        'breadcrumbs' => [
            ['label' => 'Form Center', 'url' => route('admin.form-manager.index')],
            ['label' => $form->name, 'url' => route('admin.form-manager.edit', $form)],
            ['label' => 'Submissions'],
        ],
        'actions' => [
            ['label' => 'Customize Form', 'url' => route('admin.form-manager.edit', $form), 'class' => 'btn-outline-primary-600 radius-8 px-20 py-11', 'icon' => 'solar:pen-linear'],
            ['label' => 'Back', 'url' => route('admin.form-manager.index'), 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11'],
        ],
    ])

    {{-- Status stat pills (clickable filters) --}}
    <div class="row g-3 mb-24">
        @php
            $pills = [
                ['key' => '', 'label' => 'All', 'count' => $entryStats['total'], 'icon' => 'solar:documents-linear', 'bg' => 'bg-primary-50 text-primary-600'],
                ['key' => 'pending', 'label' => 'Pending', 'count' => $entryStats['pending'], 'icon' => 'solar:clock-circle-linear', 'bg' => 'bg-warning-50 text-warning-600'],
                ['key' => 'approved', 'label' => 'Approved', 'count' => $entryStats['approved'], 'icon' => 'solar:check-circle-linear', 'bg' => 'bg-success-50 text-success-600'],
                ['key' => 'rejected', 'label' => 'Rejected', 'count' => $entryStats['rejected'], 'icon' => 'solar:close-circle-linear', 'bg' => 'bg-danger-50 text-danger-600'],
            ];
        @endphp
        @foreach($pills as $pill)
            @php
                $pillQuery = array_filter([
                    'search' => request('search'),
                    'date_from' => request('date_from'),
                    'date_to' => request('date_to'),
                    'status' => $pill['key'] ?: null,
                ]);
                $isActive = $currentStatus === $pill['key'];
            @endphp
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('admin.form-manager.entries', $form) }}?{{ http_build_query($pillQuery) }}"
                   class="fc-stat-pill {{ $isActive ? 'active-filter' : '' }}">
                    <span class="fc-stat-pill-icon {{ $pill['bg'] }}">
                        <iconify-icon icon="{{ $pill['icon'] }}"></iconify-icon>
                    </span>
                    <span>
                        <span class="d-block fw-bold text-lg lh-1 mb-4">{{ number_format($pill['count']) }}</span>
                        <span class="text-secondary-light text-sm">{{ $pill['label'] }}</span>
                    </span>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card radius-12 shadow-2 border-0 mb-24">
        <div class="card-body p-24">
            <div class="d-flex align-items-center gap-8 mb-20">
                <h6 class="mb-0 fw-semibold fc-panel-title">
                    <iconify-icon icon="solar:filter-linear"></iconify-icon>
                    Filter Submissions
                </h6>
            </div>
            <form method="GET" action="{{ route('admin.form-manager.entries', $form) }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-sm fw-medium">Search</label>
                    <div class="input-group">
                        <span class="input-group-text radius-start-8 bg-neutral-100 border-end-0">
                            <iconify-icon icon="solar:magnifer-linear" class="text-secondary-light"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control radius-end-8 border-start-0" value="{{ request('search') }}" placeholder="Name, email, phone…">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-sm fw-medium">Start Date</label>
                    <input type="date" name="date_from" class="form-control radius-8" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-sm fw-medium">End Date</label>
                    <input type="date" name="date_to" class="form-control radius-8" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-sm fw-medium">Status</label>
                    <select name="status" class="form-select radius-8">
                        <option value="">All</option>
                        @foreach(['pending','approved','rejected'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <label class="form-label text-sm fw-medium d-none d-md-block">&nbsp;</label>
                    <div class="fc-filter-actions">
                        <button type="submit" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn">
                            <iconify-icon icon="solar:filter-linear"></iconify-icon>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('admin.form-manager.entries', $form) }}"
                           class="btn btn-outline-neutral-500 radius-8 fc-btn fc-btn-icon"
                           title="Reset filters"
                           aria-label="Reset filters">
                            <iconify-icon icon="solar:refresh-linear"></iconify-icon>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Submissions table --}}
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 px-24 py-16 border-bottom">
                <h6 class="mb-0 fw-semibold fc-panel-title">
                    <iconify-icon icon="solar:inbox-linear"></iconify-icon>
                    Submissions
                </h6>
            </div>

            @if($entries->isEmpty())
                <div class="text-center py-60 px-24">
                    <div class="w-72-px h-72-px bg-neutral-100 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-16">
                        <iconify-icon icon="solar:inbox-linear" class="text-3xl text-secondary-light"></iconify-icon>
                    </div>
                    <h6 class="fw-semibold mb-8">No submissions found</h6>
                    <p class="text-secondary-light text-sm mb-0">Try adjusting your filters or check back later.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-24" style="width:60px">#</th>
                                <th style="width:160px">Submitted</th>
                                <th style="width:110px">Status</th>
                                <th>Preview</th>
                                <th class="text-end pe-24" style="width:96px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                                @php
                                    $data = is_array($entry->data) ? $entry->data : (json_decode($entry->data, true) ?? []);
                                    $previewParts = [];
                                    foreach ($previewKeys as $k) {
                                        if (!empty($data[$k]) && is_string($data[$k])) {
                                            $previewParts[] = $data[$k];
                                        }
                                    }
                                    if (empty($previewParts)) {
                                        foreach (array_slice($data, 0, 3) as $key => $val) {
                                            if (is_string($val) && strlen($val) < 80) {
                                                $previewParts[] = ucfirst(str_replace('_', ' ', $key)).': '.$val;
                                            }
                                        }
                                    }
                                    $previewText = implode(' · ', array_slice($previewParts, 0, 3)) ?: '—';
                                    $statusClass = match($entry->status) {
                                        'approved' => 'bg-success-focus text-success-main',
                                        'rejected' => 'bg-danger-focus text-danger-main',
                                        default => 'bg-warning-focus text-warning-main',
                                    };
                                @endphp
                                <tr class="fc-submission-row" onclick="window.location='{{ route('admin.form-manager.entries.show', [$form, $entry]) }}'">
                                    <td class="ps-24 fw-semibold text-primary-600">#{{ $entry->id }}</td>
                                    @php $submittedAt = $entry->submitted_at ?? $entry->created_at; @endphp
                                    <td>
                                        <span class="d-block fw-medium text-sm">{{ $submittedAt->format('d M Y') }}</span>
                                        <span class="text-secondary-light text-xs">{{ $submittedAt->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClass }} px-12 py-6 radius-8 text-xs fw-semibold">{{ ucfirst($entry->status) }}</span>
                                    </td>
                                    <td>
                                        <p class="fc-preview-text mb-0 text-truncate" style="max-width:420px" title="{{ $previewText }}">{{ $previewText }}</p>
                                        @if($entry->legacy_source)
                                            <span class="fc-badge fc-badge-neutral mt-4">Legacy: {{ $entry->legacy_source }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-24" onclick="event.stopPropagation()">
                                        <div class="fc-table-actions">
                                            <a href="{{ route('admin.form-manager.entries.show', [$form, $entry]) }}"
                                               class="fc-action-icon view"
                                               title="View submission"
                                               aria-label="View submission">
                                                <iconify-icon icon="solar:eye-linear"></iconify-icon>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('admin.form-manager.entries.destroy', [$form, $entry]) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Delete this submission?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="fc-action-icon delete"
                                                        title="Delete submission"
                                                        aria-label="Delete submission">
                                                    <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($entries->hasPages())
                    <div class="px-24 py-16 border-top fc-pagination">
                        <span class="fc-pagination-info">
                            Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ $entries->total() }} results
                        </span>
                        {{ $entries->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @elseif($entries->total() > 0)
                    <div class="px-24 py-16 border-top">
                        <span class="fc-pagination-info">{{ $entries->total() }} submission{{ $entries->total() === 1 ? '' : 's' }}</span>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
