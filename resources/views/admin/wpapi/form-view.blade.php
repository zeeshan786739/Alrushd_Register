@extends('admin.layouts.app')

@section('title') Entry #{{ $submission['entry_id'] }} @endsection


@section('content')


<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Apply Details (Entry #{{ $submission['entry_id'] }})</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium"><a href="{{ route('admin.apply-now') }}" class="btn btn-dark btn-sm">Back</a></li>
    </ul>
</div>


<div class="container">
    
    @foreach([1,2,3] as $i)
        <div class="card mb-5 shadow-sm">
            <div class="card-header bg-primary text-white">
                Student {{ $i }}
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>First Name</th>
                            <td>{{ $submission["name_" . ($i==1 ? "2" : ($i==2 ? "21" : "31"))] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Middle Name</th>
                            <td>{{ $submission["name_" . ($i==1 ? "3" : ($i==2 ? "20" : "33"))] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Surname</th>
                            <td>{{ $submission["name_" . ($i==1 ? "4" : ($i==2 ? "22" : "32"))] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Year</th>
                            <td>{{ $submission["select_" . ($i==1 ? "1" : ($i==2 ? "5" : "9"))] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Date of Birth</th>
                            <td>{{ $submission["date_" . $i] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                            <td>{{ $submission["radio_" . ($i==1 ? "1" : ($i==2 ? "3" : "13"))] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Country</th>
                            <td>
                                @php
                                    $address = json_decode($submission["address_1"], true);
                                @endphp

                                @if($address)
                                    <div class="address-box border p-3 rounded bg-light">
                                        <h6 class="text-primary mb-2">Address Details:</h6>
                                        <p class="mb-1"><strong>Street:</strong> {{ $address['street_address'] ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>Building / Flat:</strong> {{ $address['address_line'] ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>City:</strong> {{ $address['city'] ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>State:</strong> {{ $address['state'] ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>ZIP Code:</strong> {{ $address['zip'] ?? 'N/A' }}</p>
                                        <p class="mb-0"><strong>Country:</strong> {{ $address['country'] ?? 'N/A' }}</p>
                                    </div>
                                @endif

                            </td>
                        </tr>
                        <tr>
                            <th>Proof of ID</th>
                            <td>
                               
                               @if(!empty($submission["upload_1"]))
                                    <a href="{{ asset($submission['upload_1']) }}" target="_blank" class="text-primary">View File</a>
                                @else
                                    N/A
                                @endif

                            </td>
                        </tr>
                        <tr>
                            <th>Previous Academic Years Progress Report</th>
                            <td>
                                @php
                                    $uploadKey = "upload_" . ($i==1 ? "5" : ($i==2 ? "11" : "13"));
                                    $files = $submission[$uploadKey]['file']['file_url'] ?? [];
                                    if(!is_array($files)) $files = [$files];
                                @endphp
                                @foreach($files as $url)
                                    @if($url)
                                        <a href="{{ $url }}" target="_blank" class="text-primary">{{ basename($url) }}</a><br>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    {{-- GUARDIAN --}}
    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-success text-white">1st Guardian Details</div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Title</th>
                        <td>{{ $submission['select_2'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $submission['name_24'] ?? '' }} {{ $submission['name_25'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Relationship to Students(s)</th>
                        <td>{{ $submission['select_3'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>
                            @if(isset($submission['address_1']) && is_array($submission['address_1']))
                                {{ implode(', ', $submission['address_1']) }}
                            @else
                                {{ $submission['address_1'] ?? '' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $submission['email_1'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Confirm Email</th>
                        <td>{{ $submission['email_1'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td>{{ $submission['phone_2'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Home Telephone</th>
                        <td>{{ $submission['phone_1'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Work Number</th>
                        <td>{{ $submission['name_15'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Proof of ID</th>
                        <td>
                            @if(isset($submission['upload_16']['file']))
                                @php
                                    $file = $submission['upload_16']['file'];
                                @endphp
                                <a href="{{ $file['file_url'] ?? '#' }}" target="_blank" class="text-primary">{{ $file['file_name'] ?? 'View File' }}</a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Proof of Address</th>
                        <td>
                            @if(isset($submission['upload_17']['file']))
                                @php
                                    $file = $submission['upload_17']['file'];
                                @endphp
                                <a href="{{ $file['file_url'] ?? '#' }}" target="_blank" class="text-primary">{{ $file['file_name'] ?? 'View File' }}</a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($submission['radio_5'] == 'Yes')
    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-success text-white">2nd Guardian Details</div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Title</th>
                        <td>{{ $submission['select_7'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $submission['name_28'] ?? '' }} {{ $submission['name_29'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Relationship to Students(s)</th>
                        <td>{{ $submission['select_3'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>
                            @if(isset($submission['address_5']) && is_array($submission['address_5']))
                                {{ implode(', ', $submission['address_5']) }}
                            @else
                                {{ $submission['address_5'] ?? '' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $submission['email_5'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Confirm Email</th>
                        <td>{{ $submission['email_6'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td>{{ $submission['phone_3'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Home Telephone</th>
                        <td>{{ $submission['phone_5'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Work Number</th>
                        <td>{{ $submission['phone_4'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Proof of ID</th>
                        <td>
                            @if(isset($submission['upload_20']['file']))
                                @php
                                    $file = $submission['upload_20']['file'];
                                @endphp
                                <a href="{{ $file['file_url'] ?? '#' }}" target="_blank" class="text-primary">{{ $file['file_name'] ?? 'View File' }}</a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Proof of Address</th>
                        <td>
                            @if(isset($submission['upload_21']['file']))
                                @php
                                    $file = $submission['upload_21']['file'];
                                @endphp
                                <a href="{{ $file['file_url'] ?? '#' }}" target="_blank" class="text-primary">{{ $file['file_name'] ?? 'View File' }}</a>
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

        <p>An Education & Health Care plan (EHCP) is a formal document detailing a child's learning difficulties and the help they will be given. Does the child have an Education Health Care Plan? <span class="badge bg-primary">{{ $submission['radio_7'] ?? '' }}</span></p>

        <p>Permanent Exclusions : Has this child been permanently excluded (expelled) from their previous school? <span class="badge bg-primary">{{ $submission['radio_8'] ?? '' }}</span></p>

        <p>Fair Access Protocol: (Checkboxes option for the list below)- Does the child fall in any under the below listed categories of the Fair Access Protocol: <span class="badge bg-primary">{{ $submission['radio_9'] ?? '' }}</span></p>

        <p>Special Educational Needs and Disabilities: Is this child on the special educational needs and disabilities code of practice? <span class="badge bg-primary">{{ $submission['radio_10'] ?? '' }}</span></p>

        <p>Medical Conditions: Does the child have any long term medical conditions? <span class="badge bg-primary">{{ $submission['radio_11'] ?? '' }}</span></p>

        <p>Direct Placements: Has the child been directed to an Alternative Provision to improve their behaviour? <span class="badge bg-primary">{{ $submission['radio_12'] ?? '' }}</span></p>

        <p>If any of these apply, provide the supporting local authority <span class="badge bg-primary">{{ $submission['text_1'] ?? '' }}</span></p>

        <p>Provide the name of the assigned social worker <span class="badge bg-primary">{{ $submission['text_2'] ?? '' }}</span></p>

        <p>Attendance in previous school: Attendance percentage <span class="badge bg-primary">{{ $submission['text_3'] ?? '' }}</span></p>

        <p>Consent <span class="badge bg-primary">{{ $submission['consent_1'] ?? '' }}</span></p>

        </div>
    </div>


</div>
@endsection