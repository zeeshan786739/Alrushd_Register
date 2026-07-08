@extends('admin.layouts.app')

@section('title') Entry #{{ $data->entry_id }} @endsection


@section('content')


<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Apply Details (Entry #{{ $data->entry_id }})</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium"><a href="{{ route('admin.apply-now') }}" class="btn btn-dark btn-sm">Back</a></li>
    </ul>
</div>


<div class="container">


    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-primary text-white">
            Student One
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Forename</th>
                        <td>{{ $data->name_2 }}</td>
                    </tr>
                    <tr>
                        <th>Middle Names</th>
                        <td>{{ $data->name_3 }}</td>
                    </tr>
                    <tr>
                        <th>Surname</th>
                        <td>{{ $data->name_4 }}</td>
                    </tr>
                    <tr>
                        <th>Year Group </th>
                        <td>{{ $data->select_1}}</td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td>{{$data->date_1 }}</td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td>{{$data->radio_1}}</td>
                    </tr>
                    <tr>
                        <th>Nationality</th>
                        <td>
                            @php
                            $address3 = is_string($data->address_3) ? json_decode($data->address_3) : (object) $data->address_3;
                            @endphp

                            {{ $address3->country ?? '' }}
                        </td>

                    </tr>


                    <tr>
                        <th>Proof of ID</th>
                        <td>
                            @php
                            $uploads = is_string($data->upload_1) ? json_decode($data->upload_1, true) : $data->upload_1;

                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0]; // nested array handling
                            }

                            $file = (is_array($uploads) && count($uploads) > 0) ? $uploads[0] : null;
                            @endphp

                            @if($file)
                            <a href="{{ $file }}" target="_blank" class="btn btn-primary btn-sm">View File</a>
                            @else
                            No file uploaded
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Previous Academic Years Progress Report</th>
                        <td>
                            @php
                            // JSON decode করা যদি string হয়
                            $uploads = is_string($data->upload_5) ? json_decode($data->upload_5, true) : $data->upload_5;

                            // Nested array handling
                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0];
                            }
                            @endphp

                            @if(!empty($uploads) && is_array($uploads))
                            @foreach($uploads as $file)
                            <a href="{{ $file }}" target="_blank" class="btn btn-primary btn-sm mb-1">View File</a><br>
                            @endforeach
                            @else
                            No file uploaded
                            @endif
                        </td>

                    </tr>


                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-primary text-white">
            Student Two
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Forename</th>
                        <td>{{ $data->name_21 }}</td>
                    </tr>
                    <tr>
                        <th>Middle Names</th>
                        <td>{{ $data->name_22 }}</td>
                    </tr>
                    <tr>
                        <th>Surname</th>
                        <td>{{ $data->name_20 }}</td>
                    </tr>
                    <tr>
                        <th>Year Group </th>
                        <td>{{ $data->select_5}}</td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td>{{$data->date_2 }}</td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td>{{$data->radio_3}}</td>
                    </tr>
                    <tr>
                        <th>Nationality</th>
                        <td>
                            @php
                            $address2 = is_string($data->address_2) ? json_decode($data->address_2) : (object) $data->address_2;
                            @endphp

                            {{ $address2->country ?? '' }}
                        </td>

                    </tr>


                    <tr>
                        <th>Proof of ID</th>
                        <td>
                            @php
                            $uploads = is_string($data->upload_8) ? json_decode($data->upload_8, true) : $data->upload_8;

                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0]; // nested array handling
                            }

                            $file = (is_array($uploads) && count($uploads) > 0) ? $uploads[0] : null;
                            @endphp

                            @if($file)
                            <a href="{{ $file }}" target="_blank" class="btn btn-primary btn-sm">View File</a>
                            @else
                            No file uploaded
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Previous Academic Years Progress Report</th>
                        <td>
                            @php
                            // JSON decode করা যদি string হয়
                            $uploads = is_string($data->upload_11) ? json_decode($data->upload_11, true) : $data->upload_11;

                            // Nested array handling
                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0];
                            }
                            @endphp

                            @if(!empty($uploads) && is_array($uploads))
                            @foreach($uploads as $file)
                            <a href="{{ $file }}" target="_blank" class="btn btn-primary btn-sm mb-1">View File</a><br>
                            @endforeach
                            @else
                            No file uploaded
                            @endif
                        </td>

                    </tr>


                </tbody>
            </table>
        </div>
    </div>


    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-primary text-white">
            Student Three
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Forename</th>
                        <td>{{ $data->name_31 }}</td>
                    </tr>
                    <tr>
                        <th>Middle Names</th>
                        <td>{{ $data->name_32 }}</td>
                    </tr>
                    <tr>
                        <th>Surname</th>
                        <td>{{ $data->name_33 }}</td>
                    </tr>
                    <tr>
                        <th>Year Group </th>
                        <td>{{ $data->select_9}}</td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td>{{$data->date_3 }}</td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td>{{$data->radio_13}}</td>
                    </tr>
                    <tr>
                        <th>Nationality</th>
                        <td>
                            @php
                            $address4 = is_string($data->address_4) ? json_decode($data->address_4) : (object) $data->address_4;
                            @endphp

                            {{ $address4->country ?? '' }}
                        </td>

                    </tr>


                    <tr>
                        <th>Proof of ID</th>
                        <td>
                            @php
                            $uploads = is_string($data->upload_12) ? json_decode($data->upload_12, true) : $data->upload_12;

                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0]; // nested array handling
                            }

                            $file = (is_array($uploads) && count($uploads) > 0) ? $uploads[0] : null;
                            @endphp

                            @if($file)
                            <a href="{{ $file }}" target="_blank" class="btn btn-primary btn-sm">View File</a>
                            @else
                            No file uploaded
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Previous Academic Years Progress Report</th>
                        <td>
                            @php
                            // JSON decode করা যদি string হয়
                            $uploads = is_string($data->upload_13) ? json_decode($data->upload_13, true) : $data->upload_13;

                            // Nested array handling
                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0];
                            }
                            @endphp

                            @if(!empty($uploads) && is_array($uploads))
                            @foreach($uploads as $file)
                            <a href="{{ $file }}" target="_blank" class="btn btn-primary btn-sm mb-1">View File</a><br>
                            @endforeach
                            @else
                            No file uploaded
                            @endif
                        </td>

                    </tr>


                </tbody>
            </table>
        </div>
    </div>

    {{-- GUARDIAN --}}
    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-success text-white">1st Guardian Details</div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Title</th>
                        <td>{{ $data->select_2 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $data->name_24 ?? '' }} {{ $data->name_25 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Relationship to Students(s)</th>
                        <td>{{ $data->select_3 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>
                            @if(isset($data->address_1) && is_array($data->address_1))
                            {{ implode(', ', $data->address_1) }}
                            @else
                            {{ $data->address_1 ?? '' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $data->email_1 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Confirm Email</th>
                        <td>{{ $data->email_1 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td>{{ $data->phone_2 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Home Telephone</th>
                        <td>{{ $data->phone_1 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Work Number</th>
                        <td>{{ $data->name_15 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Proof of ID</th>
                        <td>
                            @php
                            // যদি JSON string হয় তাহলে decode করা
                            $uploads = is_string($data->upload_16) ? json_decode($data->upload_16, true) : $data->upload_16;

                            // Nested array handling (যদি array এর মধ্যে array থাকে)
                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0];
                            }
                            @endphp

                            @if(!empty($uploads) && is_array($uploads))
                            @foreach($uploads as $file)
                            <a href="{{ $file['file_url'] ?? $file }}" target="_blank" class="text-primary d-block mb-1">
                                {{ $file['file_name'] ?? basename($file) }}
                            </a>
                            @endforeach
                            @else
                            N/A
                            @endif
                        </td>

                    </tr>

                    <tr>
                        <th>Proof of Address</th>
                        <td>
                            @php
                            // যদি JSON string হয় তাহলে decode করা
                            $uploads = is_string($data->upload_17) ? json_decode($data->upload_17, true) : $data->upload_17;

                            // Nested array handling (যদি array এর মধ্যে array থাকে)
                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0];
                            }
                            @endphp

                            @if(!empty($uploads) && is_array($uploads))
                            @foreach($uploads as $file)
                            <a href="{{ $file['file_url'] ?? $file }}" target="_blank" class="text-primary d-block mb-1">
                                {{ $file['file_name'] ?? basename($file) }}
                            </a>
                            @endforeach
                            @else
                            N/A
                            @endif
                        </td>

                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    @if($data->radio_5 == 'Yes')
    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-success text-white">2nd Guardian Details</div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Title</th>
                        <td>{{ $data->select_7 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $data->name_28 ?? '' }} {{ $data->name_29 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Relationship to Students(s)</th>
                        <td>{{ $data->select_3 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>
                            @if(isset($data->address_5) && is_array($data->address_5))
                            {{ implode(', ', $data->address_5) }}
                            @else
                            {{ $data->address_5 ?? '' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $data->email_5 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Confirm Email</th>
                        <td>{{ $data->email_6 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td>{{ $data->phone_3 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Home Telephone</th>
                        <td>{{ $data->phone_5 ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Work Number</th>
                        <td>{{ $data->phone_4 ?? '' }}</td>
                    </tr>

                    <tr>
                        <th>Proof of ID</th>
                        <td>
                            @php
                            // যদি JSON string হয় তাহলে decode করা
                            $uploads = is_string($data->upload_20) ? json_decode($data->upload_20, true) : $data->upload_20;

                            // Nested array handling (যদি array এর মধ্যে array থাকে)
                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0];
                            }
                            @endphp

                            @if(!empty($uploads) && is_array($uploads))
                            @foreach($uploads as $file)
                            <a href="{{ $file['file_url'] ?? $file }}" target="_blank" class="text-primary d-block mb-1">
                                {{ $file['file_name'] ?? basename($file) }}
                            </a>
                            @endforeach
                            @else
                            N/A
                            @endif
                        </td>

                    </tr>

                    <tr>
                        <th>Proof of Address</th>
                        <td>
                            @php
                            // JSON decode করা যদি string হয়
                            $uploads = is_string($data->upload_21) ? json_decode($data->upload_21, true) : $data->upload_21;

                            // Nested array handling (যদি array এর মধ্যে array থাকে)
                            if (is_array($uploads) && isset($uploads[0]) && is_array($uploads[0])) {
                            $uploads = $uploads[0];
                            }
                            @endphp

                            @if(!empty($uploads) && is_array($uploads))
                            @foreach($uploads as $file)
                            <a href="{{ $file['file_url'] ?? $file }}" target="_blank" class="text-primary d-block mb-1">
                                {{ $file['file_name'] ?? basename($file) }}
                            </a>
                            @endforeach
                            @else
                            N/A
                            @endif
                        </td>

                    </tr>


                </tbody>


            </table>
        </div>
    </div>
    @endif

    {{-- OTHER DETAILS --}}
    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-info text-white">Additional Information</div>
        <div class="card-body">

            <p>An Education & Health Care plan (EHCP) is a formal document detailing a child's learning difficulties and the help they will be given. Does the child have an Education Health Care Plan? <span class="badge bg-primary">{{ $data->radio_7 ?? '' }}</span></p>

            <p>Permanent Exclusions : Has this child been permanently excluded (expelled) from their previous school? <span class="badge bg-primary">{{ $data->radio_8 ?? '' }}</span></p>

            <p>Fair Access Protocol: (Checkboxes option for the list below)- Does the child fall in any under the below listed categories of the Fair Access Protocol: <span class="badge bg-primary">{{ $data->radio_9 ?? '' }}</span></p>

            <p>Special Educational Needs and Disabilities: Is this child on the special educational needs and disabilities code of practice? <span class="badge bg-primary">{{ $data->radio_10 ?? '' }}</span></p>

            <p>Medical Conditions: Does the child have any long term medical conditions? <span class="badge bg-primary">{{ $data->radio_11 ?? '' }}</span></p>

            <p>Direct Placements: Has the child been directed to an Alternative Provision to improve their behaviour? <span class="badge bg-primary">{{ $data->radio_12 ?? '' }}</span></p>

            <p>If any of these apply, provide the supporting local authority <span class="badge bg-primary">{{ $data->text_1 ?? '' }}</span></p>

            <p>Provide the name of the assigned social worker <span class="badge bg-primary">{{ $data->text_2 ?? '' }}</span></p>

            <p>Attendance in previous school: Attendance percentage <span class="badge bg-primary">{{ $data->text_3 ?? '' }}</span></p>

            <p>Consent <span class="badge bg-primary">{{ $data->consent_1 ?? '' }}</span></p>

        </div>
    </div>


@endsection
