@extends('admin.layouts.app')

@section('title') View Application Form @endsection

@section('content')


<div class="col-12 text-end mb-3">
     <a href="{{ route('admin.job-applications-form.index') }}" class="btn btn-primary btn-sm">Back</a>
</div>

<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Personal Details ({{ $data->name }})</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Full Name :</b> {{ $data->name ?? '-' }}</p>
                    <p><b>Email :</b> {{ $data->email ?? '-' }}</p>
                    <p><b>Phone :</b> {{ $data->phone ?? '-' }}</p>
                    <p><b>Date of Birth :</b> {{ $data->dob ?? '-' }}</p>
                    <p><b>Religion :</b> {{ $data->religion ?? '-' }}</p>
                    <p><b>Ethnicity :</b> {{ $data->ethnicity ?? '-' }}</p>
                    <p><b>Marital Status :</b> {{ $data->marital_status ?? '-' }}</p>
                </div>
                <div class="col-lg-6">
                    <p><b>Gender :</b> {{ $data->gender ?? '-' }}</p>
                    <p><b>Nationality :</b> {{ $data->nationality ?? '-' }}</p>
                    <p><b>Current Address :</b> {{ $data->address ?? '-' }}</p>
                    <p><b>City :</b> {{ $data->city ?? '-' }}</p>
                    <p><b>State :</b> {{ $data->state ?? '-' }}</p>
                    <p><b>Postal Code :</b> {{ $data->postal_code ?? '-' }}</p>
                    <p><b>Country of Residence :</b> {{ $data->country_of_residence ?? '-' }}</p>
                </div>
            </div>
        </div>


    </div>
</div>

<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Position Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Position Applied For :</b> {{ $data->position_applied_for ?? '-' }}</p>
                    <p><b>Department / School Section :</b> {{ $data->department ?? '-' }}</p>
                    <p><b>Type of Employment :</b> {{ $data->type_of_employment ?? '-' }}</p>
                    <p><b>Available Start Date :</b> {{ $data->start_date ?? '-' }}</p>
                </div>
                <div class="col-lg-6">
                    <p><b>Preferred Working Hours :</b> {{ $data->preferred_working_hours ?? '-' }}</p>
                    <p><b>Expected Salary :</b> {{ $data->expected_salary ?? '-' }}</p>
                    <p><b>How did you hear about this job? :</b> {{ $data->about_this_job ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Education & Qualifications</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @if(!empty($data->education))
                @foreach($data->education as $edu)
                <div class="col-lg-6 border p-3">

                    <p><b>Qualification :</b> {{ $edu['qualification'] ?? '-' }}</p>
                    <p><b>Subject / Field of Study :</b> {{ $edu['subject'] ?? '-' }}</p>
                    <p><b>Institution Name :</b> {{ $edu['institution'] ?? '-' }}</p>
                    <p><b>Graduation Year :</b> {{ $edu['graduation_year'] ?? '-' }}</p>

                </div>
                @endforeach
                @else
                    <p style="color: #888;">No education records found.</p>
                @endif
                <div class="col-lg-12 mt-5">
                    <p><b>Teaching Qualification :</b> {{ $data->teaching_qualification ?? '-' }}</p>
                    <p><b>Other Relevant Certificates :</b> {{ $data->relevant_certificates ?? '-' }}</p>

                    <p style="margin: 10px 0;">
                        <b>Upload Certificates :</b>
                        @if(!empty($data->upload_certificates))
                            @foreach($data->upload_certificates as $index => $cert)
                                <a href="{{ Storage::url($cert) }}" target="_blank" class="btn btn-sm btn-primary mb-1">
                                    Certificate {{ $index + 1 }}
                                </a>
                            @endforeach
                        @else
                            <span style="color: #888;">Not Found</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Work Experience</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @if(!empty($data->previous_employment))
                @foreach($data->previous_employment as $employment)
                <div class="col-lg-6 border p-3">
                  
                    <p><b>Current / Most Recent Employer :</b> {{ $employment['current'] ?? '-' }}</p>
                    <p><b>Position Held :</b> {{ $employment['position_held'] ?? '-' }}</p>
                    <p><b>Start Date – End Date :</b> {{ $employment['employee_start_date'] ?? '-' }} – {{ $employment['employee_end_date'] ?? '-' }}</p>
                    <p><b>Responsibilities Summary :</b> {{ $employment['responsibilities'] ?? '-' }}</p>

                </div>
                @endforeach
                @else
                    <p style="color: #888;">No work experience found.</p>
                @endif
                <div class="col-lg-6 mt-5">
                    @if(!empty($data->previous_employment))
                        @foreach($data->previous_employment as $employment)
                            <p><b>Reason for Leaving :</b> {{ $employment['reason_for_leaving'] ?? '-' }}</p>
                            <hr>
                        @endforeach
                    @endif

                    <p style="margin: 10px 0;">
                        <b>Upload CV :</b>
                        @if($data->upload_cv)
                            <a href="{{ Storage::url($data->upload_cv) }}" target="_blank" class="btn btn-sm btn-primary">
                                View
                            </a>
                        @else
                            <span style="color: #888;">Not Found</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Skills & Competencies</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Subjects / Areas you can teach :</b> {{ $data->subject_skill ?? '-' }}</p>
                    <p><b>Languages Spoken :</b> {{ $data->languages_spoken ?? '-' }}</p>
                    <p><b>Level of Computer Literacy :</b> {{ $data->level_of_computer ?? '-' }}</p>
                    <p><b>Microsoft Teams / Zoom Experience :</b> {{ $data->microsoft_teams ?? '-' }}</p>
                </div>
                <div class="col-lg-6">
                    <p><b>Online Teaching Experience :</b> {{ $data->online_teaching_experience ?? '-' }}</p>
                    <p><b>Years of Teaching Experience :</b> {{ $data->years_of_teaching_xperience ?? '-' }}</p>
                    <p><b>Other Relevant Skills :</b> {{ $data->other_relevant_skills ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">References</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $references = $data->references ?? [];
                @endphp

                @foreach($references as $index => $ref)
                    <div class="col-lg-6 mb-3 border p-3">
                        <p><b>Reference {{ $index + 1 }} Name :</b> {{ $ref['name'] ?? '-' }}</p>
                        <p><b>Reference {{ $index + 1 }} Email :</b> {{ $ref['email'] ?? '-' }}</p>
                        <p><b>Reference {{ $index + 1 }} Phone :</b> {{ $ref['phone'] ?? '-' }}</p>
                        
                    </div>
                @endforeach
                <div class="col-lg-12">
                    <p><b>Relationship to Applicant :</b> {{ $data->relationship_to_applicant ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Additional Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>Right to Work in the UK? :</b> {{ $data->right_to_work ?? '-' }}</p>
                    <p><b>National Insurance Number :</b> {{ $data->insurance_number ?? '-' }}</p>
                    <p><b>Do you have a current DBS Certificate? :</b> {{ $data->dbs_certificate ?? '-' }}</p>
                    <p><b>DBS Issue Date :</b> {{ $data->dbs_issue_date ?? '-' }}</p>

                    <p style="margin: 10px 0;">
                        <b>Upload DBS Certificate :</b>
                        @if(!empty($data->upload_dbs_certificate))
                            <a href="{{ Storage::url($data->upload_dbs_certificate) }}" target="_blank" class="btn btn-sm btn-primary">
                                View
                            </a>
                        @else
                            <span style="color: #888;">Not Found</span>
                        @endif
                    </p>
                </div>

                <div class="col-lg-6">
                    <p><b>Willing to undergo background checks? :</b> {{ $data->background_check ?? '-' }}</p>
                    <p><b>Notice Period :</b> {{ $data->notice_period ?? '-' }}</p>
                    <p><b>Any medical conditions or special requirements? :</b> {{ $data->special_requirement ?? '-' }}</p>
                    <p><b>Personal Statement :</b> {{ $data->personal_statement ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-12 mb-3">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Declaration</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p><b>True and Complete:</b> {{ $data->true_and_complete ? 'Yes' : 'No' }}</p>
                    <p><b>Used for Recruitment Purposes:</b> {{ $data->recruitment_purposes ? 'Yes' : 'No' }}</p>
                </div>
                <div class="col-lg-6">
                    <p><b>Date of Submission:</b> {{ $data->date_of_submission ?? '-' }}</p>
                    <p><b>Status:</b>
                        @if($data->status == 1)
                            Seen
                        @elseif($data->status == 2)
                            Rejected
                        @else
                            Pending
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
