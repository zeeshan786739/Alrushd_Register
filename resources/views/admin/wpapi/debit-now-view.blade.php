@extends('admin.layouts.app')

@section('title') Direct Debit Details @endsection

@section('css')
<style>
    th {
        width: 50%;
    }

    td {
        width: 50%;
    }
</style>
@endsection

@section('content')

<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Direct Debit Details</h6>
            <a href="{{ route('admin.direct-debit') }}" class="btn btn-primary btn-sm">← Back</a>
        </div>
        <div class="card-body">
            <div class="row">
                <table class="table table-border">
                    <tr>
                        <th>Nationality</th>
                        @php
                            $address = is_string($data->address_6) ? json_decode($data->address_6) : (object) $data->address_6;
                        @endphp

                        <td>{{ $address->country ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Forename</th>
                        <td>{{ $data->name_2 }}</td>
                    </tr>
                    <tr>
                        <th>Surname</th>
                        <td>{{ $data->name_4 }}</td>
                    </tr>
                    <tr>
                        <th>Street Address</th>
                       @php
                            // JSON decode করা যদি string হয়
                            $address = is_string($data->address_7) ? json_decode($data->address_7) : (object) $data->address_7;
                        @endphp

                        <td>
                            <strong>Street:</strong> {{ $address->street_address ?? 'N/A' }} <br>
                            <strong>Address Line:</strong> {{ $address->address_line ?? 'N/A' }} <br>
                            <strong>City:</strong> {{ $address->city ?? 'N/A' }} <br>
                            <strong>State:</strong> {{ $address->state ?? 'N/A' }} <br>
                            <strong>ZIP:</strong> {{ $address->zip ?? 'N/A' }} <br>
                            <strong>Country:</strong> {{ $address->country ?? 'N/A' }}
                        </td>

                    </tr>

                     
                    <tr>
                        <th>Email</th>
                        <td>{{ $data->email_2 }}</td>
                    </tr>
                    <tr>
                        <th>Confirm Email</th>
                        <td>{{ $data->email_1 }}</td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td>{{ $data->phone_2 }}</td>
                    </tr>
                    <tr>
                        <th>Bank Name</th>
                        <td>{{ $data->name_30 }}</td>
                    </tr>
                    <tr>
                        <th>Account Number</th>
                        <td>{{ $data->name_31 }}</td>
                    </tr>
                    <tr>
                        <th>Sort Code</th>
                        <td>{{ $data->name_32 }}</td>
                    </tr>
                    <tr>
                        <th>Preferred Monthly Direct Debit Date</th>
                        <td>{{ $data->name_33 }}</td>
                    </tr>
                     <tr>
                        <th>Child One</th>
                        <td>{{ $data->name_34 }}</td>
                    </tr>
                    <tr>
                        <th>Year Group</th>
                        <td>{{ $data->select_3 ?? '' }}</td>
                    </tr>

                    <tr>
                        <th>Child 2: Full Name</th>
                        <td>{{ $data->name_35 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Child 2: Year Group</th>
                        <td>{{ $data->select_9 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Child 3: Full Name</th>
                        <td>{{ $data->name_36 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Child 3: Year Group</th>
                        <td>{{ $data->select_10 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Child 4: Full Name</th>
                        <td>{{ $data->name_37 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Child 4: Year Group</th>
                        <td>{{ $data->select_11 ?? '' }}</td>
                    </tr>


                </table>
            </div>
        </div>
    </div>
</div>

@endsection