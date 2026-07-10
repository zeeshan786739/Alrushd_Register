@extends('admin.layouts.app')

@section('title') Job Applications Form @endsection

@section('content')
@include('admin.partials.wpapi-filter-bar', [
    'action' => route('admin.job-applications'),
    'searchPlaceholder' => 'Search name, email, or country…',
])

<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-10">
            <h6 class="card-title text-primary mb-0">Job Applications List</h6>
            <form action="{{ route('admin.form.import.jobapplication', '83be6e8') }}" method="GET" class="d-inline mb-0">
                <button type="submit" class="btn btn-success btn-sm fc-btn">
                    <iconify-icon icon="solar:import-linear"></iconify-icon>
                    <span>Import</span>
                </button>
            </form>
        </div>
        <div class="card-body">
            <table class="table bordered-table mb-0" id="dataTable" data-page-length="10">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Submission Date</th>
                        <th scope="col">Country</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $submission)
                    <tr>
                        <td>{{ $submission->field_a579d1c }}</td>
                        <td>{{ $submission->email }}</td>
                        <td>{{ $submission->field_e85f923 }}</td>
                        <td>{{ $submission->form_created_at }}</td>
                        <td>{{ $submission->field_a8c4c14 }}</td>
                        <td>
                            @include('admin.partials.wp-api-status-badge', ['status' => $submission->status])
                        </td>
                        <td>
                            @include('admin.partials.table-actions', [
                                'viewUrl' => route('admin.job.application.view', $submission->entry_id),
                                'canEdit' => false,
                                'canDelete' => false,
                            ])
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
