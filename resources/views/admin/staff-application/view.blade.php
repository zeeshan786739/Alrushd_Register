@extends('admin.layouts.app')

@section('title') View Application Form @endsection

@section('content')



<div class="col-12 text-end mb-3">
    <a href="{{ route('admin.staff-applications-form.index') }}" class="btn btn-primary btn-sm">Back</a>
</div>

<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Candidate Details ({{ $data->job_applied_for }})</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Job Applied For :</b>{{$data->job_applied_for}}</p>
                    <p><b>Forename :</b>{{$data->forename}}</p>
                    <p><b>Middle Names :</b>{{$data->middle_names}}</p>
                    <p><b>Surname :</b>{{$data->surname}}</p>
                    <p><b>Preferred Name :</b>{{$data->preferred_name}}</p>
                    <p><b>Date of Birth :</b>{{$data->date_of_birth}}</p>
                    <p><b>Gender :</b>{{$data->gender}}</p>


                </div>
                <div class="col-lg-6">
                    <p><b>Marital Status :</b>{{$data->marital_status}}</p>
                    <p><b>Nationality :</b>{{$data->nationality}}</p>
                    <p><b>Ethnicity :</b>{{$data->ethnicity}}</p>
                    <p><b>Religion :</b>{{$data->religion}}</p>
                    <p><b>Mobile Number :</b>{{$data->mobile_number}}</p>
                    <p><b>Home Telephone :</b>{{$data->home_telephone}}</p>
                    <p><b>Email :</b>{{$data->email}}</p>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Address</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Street Address :</b>{{$data->street_address}}</p>
                    <p><b>Address Line 2 :</b>{{$data->address_line_2}}</p>
                    <p><b>City :</b>{{$data->city}}</p>
                    <p><b>County / State / Region :</b>{{$data->county_state_region}}</p>
                </div>
                <div class="col-lg-6">
                    <p><b>ZIP / Postal Code :</b>{{$data->zip_postal_code}}</p>
                    <p><b>Country :</b>{{$data->country}}</p>
                    <p><b>Are you allowed to work in the UK? :</b>{{$data->uk_work}}</p>
                    <p><b>Do you have a cleared DBS? :</b>{{$data->dbs}}</p>

                </div>
            </div>
        </div>

    </div>
</div>



<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Profile Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Profile Photo :</b><a href="{{ Storage::url($data->profile_photo)}}" target="_blank"
                            class="btn btn-primary btn-sm">Profile Photo</a></p>
                    <p><b>Proof of ID :</b><a href="{{ Storage::url($data->prof_of_id)}}" target="_blank"
                            class="btn btn-primary btn-sm">Proof of ID</a></p>
                    <p><b>Proof of Addres :</b><a href="{{ Storage::url($data->prof_of_address)}}" target="_blank"
                            class="btn btn-primary btn-sm">Proof of Address</a></p>

                </div>
                <div class="col-lg-6">
                    <p><b>Certificates :</b>
                        @php
                        $certificated = json_decode($data->certificated, true);
                        @endphp

                        @if(!empty($certificated))
                        @foreach($certificated as $index => $cert)
                        <a href="{{ asset($cert['file']) }}" target="_blank" class="btn btn-primary btn-sm mb-1">
                            {{ $cert['title'] ?? 'Certificate ' . ($index + 1) }}
                        </a>
                        @endforeach
                        @else
                        <span class="text-muted">No certificates uploaded.</span>
                        @endif
                    </p>
                    <p><b>DBS :</b><a href="{{ Storage::url($data->dbs_one)}}" target="_blank"
                            class="btn btn-primary btn-sm">DBS</a></p>
                    <p><b>CV :</b><a href="{{ Storage::url($data->cv)}}" target="_blank"
                            class="btn btn-primary btn-sm">CV</a></p>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="col-12 mb-3">
    <div class="card basic-data-table shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Bank Information</h6>
        </div>

        <div class="card-body">
            @if($data->bank_type == 'international')
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Bank Type :</b> {{ ucfirst($data->bank_type) }}</p>
                    <p><b>Account Name :</b> {{ $data->international_account_name ?? 'N/A' }}</p>
                    <p><b>Country Name :</b> {{ $data->international_country_name ?? 'N/A' }}</p>
                    <p><b>Bank Name :</b> {{ $data->international_bank_name ?? 'N/A' }}</p>
                    <p><b>Account Number :</b> {{ $data->international_account_number ?? 'N/A' }}</p>
                </div>
                <div class="col-lg-6">
                    <p><b>Swift Code :</b> {{ $data->swift_code ?? 'N/A' }}</p>
                    <p><b>Branch :</b> {{ $data->branch ?? 'N/A' }}</p>
                    <p><b>Branch Code :</b> {{ $data->branch_code ?? 'N/A' }}</p>
                </div>
            </div>
            @else
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Bank Type :</b> {{ ucfirst($data->bank_type) }}</p>
                    <p><b>Account Name :</b> {{ $data->uk_account_name ?? 'N/A' }}</p>
                    <p><b>Bank Name :</b> {{ $data->uk_bank_name ?? 'N/A' }}</p>
                    <p><b>Account Number :</b> {{ $data->uk_account_number ?? 'N/A' }}</p>
                </div>
                <div class="col-lg-6">
                    <p><b>Sort Code :</b> {{ $data->sort_code ?? 'N/A' }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>


<div class="col-12 mb-3">
    <div class="card basic-data-table shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Emergency Contact Details</h6>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Forename :</b> {{ ucfirst($data->emergency_forename) }}</p>
                    <p><b>Surname :</b> {{ $data->emergency_surname ?? 'N/A' }}</p>
                    <p><b>Email :</b> {{ $data->contact_email ?? 'N/A' }}</p>
                </div>
                <div class="col-lg-6">
                    <p><b>Phone :</b> {{ $data->contact_phone ?? 'N/A' }}</p>
                    <p><b>Address :</b> {{ $data->contact_address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 mb-3">
    <div class="card basic-data-table shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Terms & Conditions</h6>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Signature :</b> <img src="{{ Storage::url($data->signature) }}"></p>
                    <p><b>Terms :</b> {{ $data->terms==1 ? "Yes" : "No" }}</p>

                    <a href="javascript:void(0);" class="btn btn-sm btn-success status-btn" data-id="{{ $data->id }}"
                        data-status="1">Seen</a>

                    <a href="javascript:void(0);" class="btn btn-sm btn-danger status-btn" data-id="{{ $data->id }}"
                        data-status="2">Rejected</a>

                    <a href="javascript:void(0);" class="btn btn-sm btn-primary status-btn" data-id="{{ $data->id }}"
                        data-status="0">Pending</a>
                </div>

            </div>
        </div>
    </div>
</div>






@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.status-btn').click(function() {
            var id = $(this).data('id');
            var status = $(this).data('status');

            $.ajax({
                url: "{{ route('admin.staff-applications-form.status.update') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload(); // Or update status text dynamically
                    }
                },
                error: function() {
                    alert('Something went wrong.');
                }
            });
        });
    });
</script>

@endsection