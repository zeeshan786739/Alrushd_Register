@extends('admin.layouts.app')

@section('title') Subscribe Form @endsection

@section('content')
@include('admin.partials.wpapi-filter-bar', [
    'action' => route('admin.subscribe-applications'),
    'searchPlaceholder' => 'Search name, email, or date…',
])

<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-10">
            <h6 class="card-title text-primary mb-0">Subscribe List</h6>
            <form action="{{ route('admin.form.import.subscription', 14891) }}" method="GET" class="d-inline mb-0">
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
                        <th scope="col">Name</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Email</th>
                        <th scope="col">Select</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $entry)
                    <tr>
                        <td>{{ $entry->entry_id }}</td>
                        <td>{{ $entry->date_created }}</td>
                        <td>{{ $entry->name_2 }}</td>
                        <td>{{ $entry->phone_1 }}</td>
                        <td>{{ $entry->email_2 }}</td>
                        <td>{{ $entry->select_3 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
