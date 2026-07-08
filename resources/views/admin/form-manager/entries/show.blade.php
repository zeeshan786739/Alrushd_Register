@extends('admin.layouts.app')

@php use Illuminate\Support\Facades\Storage; @endphp

@section('title') Submission #{{ $entry->id }} @endsection

@section('content')
@include('admin.form-manager.partials.styles')

<div class="dashboard-main-body">
    @include('admin.form-manager.partials.header', [
        'title' => 'Submission #'.$entry->id,
        'subtitle' => 'Submitted on '.($entry->submitted_at ?? $entry->created_at)->format('d M Y \a\t H:i'),
        'breadcrumbs' => [
            ['label' => $form->name, 'url' => route('admin.form-manager.entries', $form)],
            ['label' => 'Submission #'.$entry->id],
        ],
        'actions' => [
            ['label' => 'Back to list', 'url' => route('admin.form-manager.entries', $form), 'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11', 'icon' => 'solar:alt-arrow-left-linear'],
        ],
    ])

    <div class="row gy-4">
        <div class="col-lg-4">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <div class="d-flex align-items-center gap-12 mb-20">
                        <span class="w-40-px h-40-px bg-primary-50 text-primary-600 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:shield-check-linear"></iconify-icon>
                        </span>
                        <h6 class="mb-0 fw-semibold">Status</h6>
                    </div>

                    @php
                        $statusClass = match($entry->status) {
                            'approved' => 'bg-success-focus text-success-main',
                            'rejected' => 'bg-danger-focus text-danger-main',
                            default => 'bg-warning-focus text-warning-main',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} px-16 py-8 radius-8 fw-semibold mb-20 d-inline-block">{{ ucfirst($entry->status) }}</span>

                    @if($entry->legacy_source)
                        <div class="fc-form-section mb-20">
                            <span class="fc-badge fc-badge-neutral">
                                <iconify-icon icon="solar:database-linear"></iconify-icon>
                                Legacy: {{ $entry->legacy_source }}
                            </span>
                        </div>
                    @endif

                    <form action="{{ route('admin.form-manager.entries.status', [$form, $entry]) }}" method="POST">
                        @csrf @method('PATCH')
                        <label class="form-label fw-medium text-sm">Update status</label>
                        <select name="status" class="form-select radius-8 mb-16">
                            @foreach(['pending','approved','rejected'] as $st)
                                <option value="{{ $st }}" @selected($entry->status === $st)>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary-600 radius-8 w-100 py-11 fc-btn">
                            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                            <span>Save Status</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card radius-12 shadow-2 border-0">
                <div class="card-body p-0">
                    <div class="px-24 py-16 border-bottom">
                        <h6 class="mb-0 fw-semibold fc-panel-title">
                            <iconify-icon icon="solar:document-text-linear"></iconify-icon>
                            Submission Data
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table bordered-table mb-0">
                            <tbody>
                                @php
                                    $data = is_array($entry->data) ? $entry->data : (json_decode($entry->data, true) ?? []);
                                    $shownKeys = [];
                                @endphp

                                @forelse($form->fields as $field)
                                    @php
                                        $value = $data[$field->key] ?? null;
                                        $shownKeys[] = $field->key;
                                    @endphp
                                    <tr>
                                        <th class="bg-neutral-50 fw-semibold text-secondary-light ps-24" style="width:240px;">{{ $field->label }}</th>
                                        <td class="pe-24">
                                            @if(is_array($value))
                                                <pre class="mb-0 text-sm bg-neutral-50 p-12 radius-8 border-0">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @elseif(is_string($value) && str_starts_with($value, 'form-uploads/'))
                                                <a href="{{ Storage::url($value) }}" target="_blank" class="btn btn-sm btn-outline-primary-600 radius-8">
                                                    <iconify-icon icon="solar:download-linear" class="me-1"></iconify-icon> Download
                                                </a>
                                            @else
                                                <span class="text-sm">{{ $value ?? '—' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                @endforelse

                                @foreach($data as $key => $value)
                                    @if(in_array($key, $shownKeys, true)) @continue @endif
                                    <tr>
                                        <th class="bg-neutral-50 fw-semibold text-secondary-light ps-24">{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                        <td class="pe-24">
                                            @if(is_array($value))
                                                <pre class="mb-0 text-sm bg-neutral-50 p-12 radius-8 border-0">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @else
                                                <span class="text-sm">{{ $value ?? '—' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @if(empty($form->fields) && empty($data))
                                    <tr>
                                        <td colspan="2" class="text-center py-40 text-secondary-light">No submission data available.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
