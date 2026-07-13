@extends('admin.layouts.app')

@section('title') Online Madrash Form @endsection

@section('content')
@include('admin.partials.wpapi-filter-bar', [
    'action' => route('admin.online-madrasah'),
    'searchPlaceholder' => 'Search name, email, or date…',
])

<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-10">
            <h6 class="card-title text-primary mb-0">Online Madrasah List</h6>
            <form action="{{ route('admin.form.import', 15647) }}" method="GET" class="d-inline mb-0">
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
                        <th scope="col">ID</th>
                        <th scope="col">Date Submitted</th>
                        <th scope="col">Forename</th>
                        <th scope="col">Middle Names</th>
                        <th scope="col">Sure Names</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Email</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $entry)
                    <tr>
                        <td>{{ $entry->entry_id }}</td>
                        <td>{{ $entry->date_created }}</td>
                        <td>{{ $entry->name_2 ?? '' }}</td>
                        <td>{{ $entry->name_3 ?? '' }}</td>
                        <td>{{ $entry->name_4 ?? '' }}</td>
                        <td>{{ $entry->phone_2 ?? '' }}</td>
                        <td>{{ $entry->email_1 ?? '' }}</td>
                        <td>
                            @include('admin.partials.wp-api-status-badge', ['status' => $entry->status])
                        </td>
                        <td>
                            @include('admin.partials.table-actions', [
                                'viewUrl' => route('admin.student-admission-view', $entry->entry_id),
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
