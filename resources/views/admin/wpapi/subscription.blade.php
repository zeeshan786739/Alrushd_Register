@extends('admin.layouts.app')

@section('title') Subscribe Form @endsection

@section('content')
<!-- 🔍 Filter Form -->
<form method="GET" action="{{ route('admin.subscribe-applications') }}"
    class="bg-primary mt-3 mb-5 mx-2 p-10 rounded row text-light" style="text-align: left;">
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
        <a href="{{ route('admin.subscribe-applications') }}" class="btn btn-dark">Reset</a>
    </div>


</form>

<div class="container">
    <div class="row">

        <div class="col-md-12">
            <div class="card card-cyan">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-primary mb-0">Subscribe Form</h5>
                    <div class=" text-end ">
                        <form action="{{ route('admin.form.import.subscription',14891) }}" method="GET">
                            <button type="submit" class="btn btn-success">
                                Import
                                <i class="ri-import-line w-auto"></i>
                            </button>
                        </form>

                    </div>
                </div>
                <div class="card-body">



                    <table class="table table-bordered mb-0" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th style="width: 25%;">Date Submitted</th>
                                <th style="width: 15%;">Name</th>
                                <th style="width: 15%;">Phone</th>
                                <th style="width: 20%;">Email</th>
                                <th style="width: 15%;" class="text-center">Select</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @foreach($data as $entry)
                            <tr>
                                <td>{{ $entry->entry_id }}</td>
                                <td>{{ $entry->date_created }}</td>
                                <td>{{ $entry->name_2 }}</td>
                                <td>{{ $entry->phone_1 }}</td>
                                <td>{{ $entry->email_2 }}</td>
                                <td class="text-center">{{ $entry->select_3 }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>


                </div>
            </div>
        </div>
    </div>
</div>

@endsection