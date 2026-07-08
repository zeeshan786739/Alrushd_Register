@extends('admin.layouts.app')

@section('title') Job Applications Form @endsection

@section('content')


<!-- 🔍 Filter Form -->
<form method="GET" action="{{ route('admin.job-applications') }}"
    class="bg-primary mt-3 mb-5 mx-2 p-10 rounded row text-light" style="text-align: left;">

    <div class="col-lg-4">
        <label class="fw-bold" for="search">Search Here</label>
        <input type="text" name="search" class="form-control search" placeholder="Search name, email, or country"
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
        <a href="{{ route('admin.job-applications') }}" class="btn btn-dark">Reset</a>
    </div>

</form>

<div class="container">
    <div class="row">

        <div class="col-md-12">
            <div class="card card-cyan">
                <div class="card-header">
                    <h5 class="card-title text-primary mb-0">Job Applications Form</h5>

                    <div class=" text-end">
                        @php
                        $id = '83be6e8';
                        @endphp
                        <form action="{{ route('admin.form.import.jobapplication',$id) }}" method="GET">
                            <button type="submit" class="btn btn-success">
                                Import
                                <i class="ri-import-line w-auto"></i>
                            </button>
                        </form>

                    </div>

                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-0" id="jobapplication">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Submission Date</th>
                                <th>Country</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @foreach($data as $submission)

                            <tr>
                                <td>{{ $submission->field_a579d1c }}</td>
                                <td>{{ $submission->email }}</td>
                                <td>{{ $submission->field_e85f923 }}</td>
                                <td>{{ $submission->form_created_at }}</td>
                                <td>{{ $submission->field_a8c4c14 }}</td>
                                <td class="text-center">
                                    @if($submission->status==0)
                                    <span class="badge bg-warning text-white">Pending</span>
                                    @else
                                    <span class="badge bg-success text-white">Approved</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.job.application.view', $submission->entry_id) }}"
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
@endsection
