@extends('admin.layouts.app')

@section('title') Job Application Details @endsection

@section('css')
    <style>
        th{
            width: 40%;
        }
        td{
            width: 60%;
        }
    </style>
@endsection

@section('content')

<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Application Details</h6>
            <a href="{{ route('admin.job-applications') }}" class="btn btn-primary btn-sm">← Back</a>
        </div>
        <div class="card-body">
            <div class="row">
                <table class="table table-border">
                    <tr>
                        <th>Candidate Details Job Applied For</th>
                        <td>{{ $data->name }}</td>
                    </tr>
                    <tr>
                        <th>Forename</th>
                        <td>{{ $data->field_a579d1c }}</td>
                    </tr>
                    <tr>
                        <th>Middle Names</th>
                        <td>{{ $data->email }}</td>
                    </tr>
                    <tr>
                        <th>Surname</th>
                        <td>{{ $data->field_c90264c }}</td>
                    </tr>
                    <tr>
                        <th>Preferred Name</th>
                        <td>{{ $data->message }}</td>
                    </tr>
                     <tr>
                        <th>Date of Birth</th>
                        <td>{{ $data->field_fa1fc8d }}</td>
                    </tr>
                     <tr>
                        <th>Gender</th>
                        <td>{{ $data->field_36f8b56 }}</td>
                    </tr>
                    <tr>
                        <th>Marital Status</th>
                        <td>{{ $data->field_facd0f4 }}</td>
                    </tr>
                    <tr>
                        <th>Nationality</th>
                        <td>{{ $data->field_6ccfce5 }}</td>
                    </tr>
                    <tr>
                        <th>Religion</th>
                        <td>{{ $data->field_926b28e }}</td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td>{{ $data->field_36f8b56 }}</td>
                    </tr>
                    <tr>
                        <th>Home Telephone</th>
                        <td>{{ $data->field_47d2c6a }}</td>
                    </tr>
                    <tr>
                        <th>Address / Street Address</th>
                        <td>{{ $data->field_0bc35f1 }}</td>
                    </tr>
                     <tr>
                        <th>Address Line 2</th>
                        <td>{{ $data->field_047fc60 }}</td>
                    </tr>
                     <tr>
                        <th>City</th>
                        <td>{{ $data->field_a068999 }}</td>
                    </tr>
                     <tr>
                        <th>Country / State / Region</th>
                        <td>{{ $data->field_e95ba4d }}</td>
                    </tr>
                     <tr>
                        <th>Zip / Postal Code</th>
                        <td>{{ $data->field_b2dcf7c }}</td>
                    </tr>
                     <tr>
                        <th>Country</th>
                        <td>{{ $data->field_a8c4c14 }}</td>
                    </tr>
                     <tr>
                        <th>Are you allowed to work in the UK?</th>
                        <td>{{ $data->field_dae410a }}</td>
                    </tr>
                     <tr>
                        <th>Do you have a cleared DBS?</th>
                        <td>{{ $data->field_f2a4f37 }}</td>
                    </tr>
                    <tr>
                        <th>Emergency Contact Details Forename</th>
                        <td>{{ $data->field_0064921 }}</td>
                    </tr>
                    <tr>
                        <th>Surname</th>
                        <td>{{ $data->field_28fb8c2 }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $data->email }}</td>
                    </tr>
                    <tr>
                        <th>Submission Date</th>
                        <td>{{ $data->created_at }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
