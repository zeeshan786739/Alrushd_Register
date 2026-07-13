@extends('admin.layouts.app')

@section('title') Form Students @endsection

@section('content')
    @include('admin.partials.page-header', [
        'title' => 'Form Students',
        'subtitle' => 'Track student registration submissions, payments, and enrollment status.',
        'breadcrumbs' => [['label' => 'Admissions'], ['label' => 'Form Students']],
    ])

    @php
        $yearOptions = \App\Models\StudentYear::pluck('name', 'name')->all();
    @endphp

    @include('admin.partials.filter-bar', [
        'action' => route('admin.form-students.index'),
        'resetUrl' => route('admin.form-students.index'),
        'fields' => [
            ['name' => 'search', 'label' => 'Search', 'placeholder' => 'School, name, number or email…'],
            ['name' => 'year_id', 'label' => 'Year', 'type' => 'select', 'placeholder' => 'All years', 'options' => $yearOptions],
            ['name' => 'start_date', 'label' => 'From', 'type' => 'date'],
            ['name' => 'end_date', 'label' => 'To', 'type' => 'date'],
        ],
    ])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 px-24 py-16 border-bottom">
                <h6 class="mb-0 fw-semibold fc-panel-title">
                    <iconify-icon icon="solar:square-academic-cap-linear"></iconify-icon>
                    Registrations
                    <span class="fc-badge fc-badge-neutral ms-8">{{ $data->total() }}</span>
                </h6>
                @if($data->hasPages())
                    <div class="fc-pagination">{{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table bordered-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-24" style="width:60px">#</th>
                            <th>Submitted</th>
                            <th>Parent</th>
                            <th>Student</th>
                            <th style="width:140px">Amount</th>
                            <th style="width:110px">Status</th>
                            <th class="text-end pe-24" style="width:120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        @can('view coursefee')
                        @if($item->fname)
                        <tr class="fc-form-row">
                            <td class="ps-24 fw-semibold text-secondary-light">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="text-secondary-light text-sm fw-medium">{{ $item->created_at->format('Y-m-d') }}</td>
                            <td>
                                <p class="mb-0 text-sm"><span class="text-secondary-light">Name:</span> {{ $item->title }} {{ $item->fname }} {{ $item->lname }}</p>
                                <p class="mb-0 text-sm"><span class="text-secondary-light">Email:</span> {{ $item->email }}</p>
                                <p class="mb-0 text-sm"><span class="text-secondary-light">Phone:</span> {{ $item->mobile_number }}</p>
                            </td>
                            <td>
                                @foreach($item->students as $student)
                                    <p class="mb-0 text-sm"><span class="text-secondary-light">Name:</span> {{ $student->fname }} {{ $student->lname }}</p>
                                    <p class="mb-0 text-sm"><span class="text-secondary-light">Year:</span> {{ $student->year->name ?? '—' }}</p>
                                @endforeach
                            </td>
                            <td>
                                <p class="mb-0 text-sm"><span class="text-secondary-light">Total:</span> {{ $item->total_amount ? '£'.$item->total_amount : 'N/A' }}</p>
                                <p class="mb-0 text-sm"><span class="text-secondary-light">Paid:</span> {{ $item->paid_amount ? '£'.$item->paid_amount : 'N/A' }}</p>
                            </td>
                            <td>
                                @if($item->status == 'paid')
                                    <span class="fc-badge fc-badge-success">Paid</span>
                                @else
                                    <span class="fc-badge fc-badge-primary">In progress</span>
                                @endif
                            </td>
                            <td class="text-end pe-24">
                                @include('admin.partials.table-actions', [
                                    'viewUrl' => auth()->user()->can('view coursefee') ? route('admin.form-students.show', $item->id) : null,
                                    'editUrl' => auth()->user()->can('edit coursefee') ? route('admin.form-students.edit', $item->id) : null,
                                    'deleteId' => auth()->user()->can('delete coursefee') ? $item->id : null,
                                    'deleteRoute' => auth()->user()->can('delete coursefee') ? route('admin.form-students.destroy', $item->id) : null,
                                    'canView' => auth()->user()->can('view coursefee'),
                                    'canEdit' => auth()->user()->can('edit coursefee'),
                                    'canDelete' => auth()->user()->can('delete coursefee'),
                                ])
                            </td>
                        </tr>
                        @endif
                        @endcan
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-48">
                                <div class="um-empty-state border-0">
                                    <iconify-icon icon="solar:inbox-linear" class="d-block mx-auto"></iconify-icon>
                                    <h6 class="fw-semibold mb-8">No registrations found</h6>
                                    <p class="text-secondary-light text-sm mb-0">Try adjusting your filters.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($data->hasPages())
                <div class="fc-pagination px-24 py-16 border-top">{{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
@endsection
