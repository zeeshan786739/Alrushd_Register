@extends('admin.layouts.app')

@section('title') Referral Now Form @endsection

@section('content')


<!-- 🔍 Filter Form -->
<form method="GET" action="{{ route('admin.referral-applications') }}"
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
        <a href="{{ route('admin.referral-applications') }}" class="btn btn-dark">Reset</a>
    </div>


</form>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-cyan">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-primary mb-0">Referral Now Form</h5>

                    <div class="text-end">
                        <form action="{{ route('admin.form.import.contact',14889) }}" method="GET">
                            <button type="submit" class="btn btn-success">
                                Import
                                <i class="ri-import-line w-auto"></i>
                            </button>
                        </form>

                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date Submitted</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Country</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @foreach($data as $entry)
                            <tr>
                                <td>{{ $entry->entry_id }}</td>
                                <td>{{ $entry->date_created }}</td>
                                <td>{{ $entry->name_2 }} {{ $entry->name_4 }}</td>
                                <td>{{ $entry->email_2 }}</td>
                                <td>{{ $entry->phone_2 }}</td>
                                <td>
                                    @php
                                    $address = $entry->address_7;

                                    // যদি string হয় → decode
                                    if (is_string($address)) {
                                    $address = json_decode($address, true);
                                    }
                                    @endphp

                                    {{-- এখন array থেকে প্রয়োজনীয় field দেখানো --}}
                                    @if(is_array($address))
                                    <!-- {{ $address['street_address'] ?? '' }}, 
                                        {{ $address['address_line'] ?? '' }}, 
                                        {{ $address['city'] ?? '' }}, 
                                        {{ $address['state'] ?? '' }}, 
                                        {{ $address['zip'] ?? '' }},  -->
                                    {{ $address['country'] ?? '' }}
                                    @else
                                    {{ $address ?? '' }}
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($entry->status==0)
                                    <span class="badge bg-warning text-white">Pending</span>
                                    @else
                                    <span class="badge bg-success text-white">Approved</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.referral-now-view', $entry->entry_id) }}"
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
