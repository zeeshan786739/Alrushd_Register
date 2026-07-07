@extends('admin.layouts.app')

@section('title') Apply Now Form @endsection


@section('content')



<!-- 🔍 Filter Form -->
<form method="GET" action="{{ route('admin.apply-now') }}" class="bg-primary mt-3 mb-5 mx-2 p-10 rounded row text-light"
    style="text-align: left;">
    <div class="col-lg-4">
        <label class="fw-bold" for="search">Search Here</label>
        <input type="text" name="search" class="form-control search" placeholder="Search name, email, or date"
            value="{{ request('search') }}">
    </div>

    <div class="col-lg-3">
        <label class="fw-bold" for="start_date">Start Date</label>
        <input type="date" name="start_date" class="form-control search" value="{{ request('start_date') }}">
    </div>

    <div class="col-lg-3">
        <label class="fw-bold" for="end_date">End Date</label>
        <input type="date" name="end_date" class="form-control search" value="{{ request('end_date') }}">
    </div>

    <div class="col-lg-2 mt-24">
        <button type="submit" class="btn btn-warning">Filter</button>
        <a href="{{ route('admin.apply-now') }}" class="btn btn-dark">Reset</a>
    </div>

</form>



<div class="container">
    <div class="row">

        <div class="col-md-12">
            <div class="card card-cyan">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-primary mb-0">Apply Now Entries</h5>

                    <div class=" text-end">
                        <form action="{{ route('admin.form.import',14242) }}" method="GET">
                            <button type="submit" class="btn btn-success">
                                Import
                                <i class="ri-import-line w-auto"></i>
                            </button>
                        </form>

                    </div>
                </div>
                <div class="card-body">



                    <div class="table-responsive" style="overflow-x: auto; width: 100%;">
                        <table class="table table-bordered mb-0 w-100" style="min-width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date Submitted</th>
                                {{-- <th>Forename</th>
                                <th>Middle Names</th>
                                <th>Sure Names</th> --}}
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @foreach($data as $entry)

                            <tr>
                                <td>{{ $entry->entry_id }}</td>
                                <td>{{ $entry->date_created }}</td>
                                {{-- <td>{{ $entry->name_2 ?? '' }}</td>
                                <td>{{ $entry->name_3 ?? '' }}</td>
                                <td>{{ $entry->name_4 ?? '' }}</td> --}}
                                <td>
                                    <p class="mb-0"><b>Forename :</b> {{ $entry->name_2 ?? '' }}</p>
                                    <p class="mb-0"><b>Middle Name :</b> {{ $entry->name_3 ?? '' }}</p>
                                    <p class="mb-0"><b>Sure Name :</b> {{ $entry->name_4 ?? '' }}</p>
                                </td>
                                <td>{{ $entry->phone_2 ?? '' }}</td>
                                <td>{{ $entry->email_1 ?? '' }}</td>
                                <td class="text-center">
                                    @if($entry->status==0)
                                    <span class="badge bg-warning text-white">Pending</span>
                                    @else
                                    <span class="badge bg-success text-white">Approved</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('admin.student-admission-view', $entry->entry_id) }}"
                                        class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script></script>
@endsection