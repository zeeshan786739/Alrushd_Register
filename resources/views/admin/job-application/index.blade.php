@extends('admin.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    @include('admin.partials.page-header', [
        'title' => $pageTitle,
        'subtitle' => 'Review and manage job application submissions from the public website.',
        'breadcrumbs' => [['label' => 'Form Submissions'], ['label' => 'Job Applications']],
    ])

    @include('admin.partials.filter-bar', [
        'action' => route('admin.job-applications-form.index'),
        'resetUrl' => route('admin.job-applications-form.index'),
        'fields' => [
            ['name' => 'search', 'label' => 'Search', 'placeholder' => 'Name, email, phone or country…'],
            ['name' => 'start_date', 'label' => 'From', 'type' => 'date'],
            ['name' => 'end_date', 'label' => 'To', 'type' => 'date'],
        ],
    ])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 px-24 py-16 border-bottom">
                <h6 class="mb-0 fw-semibold fc-panel-title">
                    <iconify-icon icon="solar:document-text-linear"></iconify-icon>
                    Submissions
                    <span class="fc-badge fc-badge-neutral ms-8">{{ $data->total() }}</span>
                </h6>
                @if($data->hasPages())
                    <div class="fc-pagination">{{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>

            @if($data->isEmpty())
                <div class="um-empty-state">
                    <iconify-icon icon="solar:inbox-linear" class="d-block mx-auto"></iconify-icon>
                    <h6 class="fw-semibold mb-8">No submissions found</h6>
                    <p class="text-secondary-light text-sm mb-0">Try adjusting your search or date filters.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table bordered-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-24" style="width:60px">#</th>
                                <th>Submitted</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Country</th>
                                <th style="width:110px">Status</th>
                                <th class="text-end pe-24" style="width:100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                            <tr class="fc-form-row">
                                <td class="ps-24 fw-semibold text-secondary-light">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                <td class="text-secondary-light text-sm">{{ $item->date_of_submission }}</td>
                                <td><span class="fw-medium">{{ $item->name }}</span></td>
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->phone }}</td>
                                <td>{{ $item->country_of_residence }}</td>
                                <td>
                                    @if($item->status == 1)
                                        <span class="fc-badge fc-badge-success">Approved</span>
                                    @else
                                        <span class="fc-badge fc-badge-primary">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end pe-24">
                                    @include('admin.partials.table-actions', [
                                        'viewUrl' => route('admin.job-applications-form.view', $item->id),
                                        'deleteId' => $item->id,
                                        'deleteRoute' => route('admin.job-applications-form.destroy', $item->id),
                                        'canEdit' => false,
                                    ])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($data->hasPages())
                    <div class="fc-pagination px-24 py-16 border-top">{{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                @endif
            @endif
        </div>
    </div>
@endsection
