@extends('layouts.app')

@section('title','Al-rushd Online School - Staff Application')

@section('css')
<style>
    @media (min-width:768px) {
        .progress {
            max-width: 45%;
        }
    }
    input[type="file"] {
        text-transform: capitalize;
    }
    
</style>
@endsection

@section('content')
<a href="{{ route('staff-admission') }}" class="logo d-flex align-items-center m-auto"
    style="background: #f6f9fc;padding-top:10px;padding-bottom:10px;">
    <img src="{{ asset('frontend/') }}/assets/img/logo.png" alt="" width="70" style="margin:auto;">
</a>
<section class="section">
    <div class="container-fluid">
        <div class="row justify-content-center">
            @if ($errors->any())
            <div class="alert alert-danger mt-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Title -->
            <div class="row pt-3">
                <div class="col-lg-8 m-auto">

                    <!-- Progress Header (Original Design) -->
                    <div class="progress-container mb-4">
                        <h5 id="formTitle" class="mb-0 text-light">Estimated time remaining: 4 minutes</h5>
                        <div class="progress mt-2">
                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <small id="progressText" class="text-light">0%</small>
                    </div>


                    <h2 class="form-heading mb-0">Staff Application Form</h2>
                    <div id="success-message" class="mt-3"></div>
                </div>
            </div>



            <div class="row mt-5">

                <form id="staffApplicationForm" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="type" value="staff">

                    <div class="col-lg-8 m-auto">

                        <div class="card mb-3 step1" style="background: #0C2A58;border-radius: 24px;padding:22px;">
                            <div class="card-body p-0">
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Candidate Details
                                </h5>
                                <div class="row g-3">

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Job Applied For</label>
                                        <select class="form-select select2" name="job_applied_for" id="job_applied_for"
                                            required>
                                            <option value="" selected disabled>-- Select Job Applied For --</option>
                                            <option value="Primary School Teacher">Primary School Teacher</option>
                                            <option value="Secondary School Teacher">Secondary School Teacher</option>
                                            <option value="English Teacher">English Teacher</option>
                                            <option value="Mathematics Teacher">Mathematics Teacher</option>
                                            <option value="Science Teacher (General)">Science Teacher (General)</option>
                                            <option value="Biology Teacher">Biology Teacher</option>
                                            <option value="Chemistry Teacher">Chemistry Teacher</option>
                                            <option value="Physics Teacher">Physics Teacher</option>
                                            <option value="Computer Science / ICT Teacher">Computer Science / ICT
                                                Teacher</option>
                                            <option value="History Teacher">History Teacher</option>
                                            <option value="Geography Teacher">Geography Teacher</option>
                                            <option value="Business Studies Teacher">Business Studies Teacher</option>
                                            <option value="Economics Teacher">Economics Teacher</option>
                                            <option value="Sociology Teacher">Sociology Teacher</option>
                                            <option value="Psychology Teacher">Psychology Teacher</option>
                                            <option value="Accounting Teacher">Accounting Teacher</option>
                                            <option value="Art & Design Teacher">Art & Design Teacher</option>
                                            <option value="Physical Education (PE) Teacher">Physical Education (PE)
                                                Teacher</option>
                                            <option value="Arabic Language Teacher">Arabic Language Teacher</option>
                                            <option value="Islamic Studies Teacher">Islamic Studies Teacher</option>
                                            <option value="Qur’an & Tajweed Teacher">Qur’an & Tajweed Teacher</option>
                                            <option value="Hifdh Teacher">Hifdh Teacher</option>
                                            <option value="Special Educational Needs (SEN) Teacher">Special Educational
                                                Needs (SEN) Teacher</option>
                                            <option value="Teaching Assistant">Teaching Assistant</option>
                                            <option value="Exam Invigilator">Exam Invigilator</option>
                                            <option value="Homework Reviewer / Marker">Homework Reviewer / Marker
                                            </option>
                                            <option value="A-Level English Teacher">A-Level English Teacher</option>
                                            <option value="A-Level Mathematics Teacher">A-Level Mathematics Teacher
                                            </option>
                                            <option value="A-Level Biology Teache">A-Level Biology Teacher</option>
                                            <option value="A-Level Chemistry Teacher">A-Level Chemistry Teacher</option>
                                            <option value="A-Level Physics Teacher">A-Level Physics Teacher</option>
                                            <option value="A-Level Business Studies Teacher">A-Level Business Studies
                                                Teacher</option>
                                            <option value="A-Level Economics Teacher">A-Level Economics Teacher</option>
                                            <option value="A-Level Psychology Teacher">A-Level Psychology Teacher
                                            </option>
                                            <option value="A-Level Sociology Teacher">A-Level Sociology Teacher</option>
                                            <option value="Madrasah Teacher (Evening / Weekend)">Madrasah Teacher
                                                (Evening / Weekend)</option>
                                            <option value="Madrasah Coordinator">Madrasah Coordinator</option>
                                            <option value="Islamic Curriculum Lead">Islamic Curriculum Lead</option>
                                            <option value="Qur’an Examiner">Qur’an Examiner</option>
                                            <option value="Hifdh Supervisor">Hifdh Supervisor</option>
                                            <option value="Principal">Principal</option>
                                            <option value="Vice Principal / Deputy Head Teacher">Vice Principal / Deputy
                                                Head Teacher</option>
                                            <option value="Assistant Principal">Assistant Principal</option>
                                            <option value="Head of Primary">Head of Primary</option>
                                            <option value="Head of Secondary">Head of Secondary</option>
                                            <option value="Head of Sixth Form">Head of Sixth Form</option>
                                            <option value="Head of Madrasah">Head of Madrasah</option>
                                            <option value="Head of Department (Specify Subject)">Head of Department
                                                (Specify Subject)</option>
                                            <option value="Senior Leadership Team (SLT) Member">Senior Leadership Team
                                                (SLT) Member</option>
                                            <option value="School Administrator">School Administrator</option>
                                            <option value="Admissions Officer">Admissions Officer</option>
                                            <option value="HR / Recruitment Officer">HR / Recruitment Officer</option>
                                            <option value="Finance Officer / Accountant">Finance Officer / Accountant
                                            </option>
                                            <option value="Payroll & Compliance Officer">Payroll & Compliance Officer
                                            </option>
                                            <option value="Data & Exams Officer">Data & Exams Officer</option>
                                            <option value="Receptionist / Enrolment Assistant">Receptionist / Enrolment
                                                Assistant</option>
                                            <option value="Office Assistant">Office Assistant</option>
                                            <option value="IT Support Officer">IT Support Officer</option>
                                            <option value="Systems Administrator">Systems Administrator</option>
                                            <option value="LMS / Portal Manager">LMS / Portal Manager</option>
                                            <option value="Web Developer">Web Developer</option>
                                            <option value="Digital Marketing Officer">Digital Marketing Officer</option>
                                            <option value="Graphic Designer">Graphic Designer</option>
                                            <option value="Video Editor / Media Production Officer">Video Editor / Media
                                                Production Officer</option>
                                            <option value="Marketing & Communications Manager">Marketing &
                                                Communications Manager</option>
                                            <option value="Social Media Manager">Social Media Manager</option>
                                            <option value="Content Creator">Content Creator</option>
                                            <option value="Outreach & Partnerships Officer">Outreach & Partnerships
                                                Officer</option>
                                            <option value="Student Success Officer">Student Success Officer</option>
                                            <option value="Student Support Coordinator">Student Support Coordinator
                                            </option>
                                            <option value="Pastoral Care Officer">Pastoral Care Officer</option>
                                            <option value="Behaviour Support Officer">Behaviour Support Officer</option>
                                            <option value="Learning Mentor">Learning Mentor</option>
                                            <option value="School Counsellor">School Counsellor</option>
                                            <option value="Site Manager">Site Manager</option>
                                            <option value="Caretaker / Maintenance Staff">Caretaker / Maintenance Staff
                                            </option>
                                            <option value="Cleaner">Cleaner</option>
                                            <option value="Security Officer">Security Officer</option>
                                            <option value="Community Engagement Officer">Community Engagement Officer
                                            </option>
                                            <option value="Events Coordinator">Events Coordinator</option>
                                            <option value="Alumni Relations Officer">Alumni Relations Officer</option>
                                        </select>
                                    </div>


                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Forename</label>
                                        <input type="text" class="form-control" name="forename" id="forename"
                                            placeholder="Forename" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Middle Names</label>
                                        <input type="text" class="form-control" name="middle_names" id="middle_names"
                                            placeholder="Middle Names">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Surname</label>
                                        <input type="text" class="form-control" name="surname" id="surname"
                                            placeholder="Surname" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Preferred Name</label>
                                        <input type="text" class="form-control" name="preferred_name"
                                            id="preferred_name" placeholder="Preferred Name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" name="date_of_birth" id="date_of_birth">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gender</label><br>
                                        <input type="radio" name="gender" value="Male"> <span
                                            class="text-light">Male</span><br>
                                        <input type="radio" name="gender" value="Female"> <span
                                            class="text-light">Female</span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Marital Status</label><br>
                                        <input type="radio" name="marital_status" value="Single"> <span
                                            class="text-light">Single</span><br>
                                        <input type="radio" name="marital_status" value="Married"> <span
                                            class="text-light">Married</span><br>
                                        <input type="radio" name="marital_status" value="Others"> <span
                                            class="text-light">Others</span>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ethnicity</label>
                                        <select name="ethnicity" id="ethnicity" class="form-select select2" required>
                                            <option value="" selected disabled>-- Select Ethnicity --</option>
                                            <option value="Asian">Asian</option>
                                            <option value="Black / African / Caribbean / Black British">Black / African / Caribbean / Black British</option>
                                            <option value="White">White</option>
                                            <option value="Mixed / Multiple Ethnic Groups">Mixed / Multiple Ethnic Groups</option>
                                            <option value="Arab">Arab</option>
                                            <option value="Hispanic / Latino">Hispanic / Latino</option>
                                            <option value="Native American / Alaska Native">Native American / Alaska Native</option>
                                            <option value="Pacific Islander">Pacific Islander</option>
                                            <option value="Other">Other</option>
                                        </select>

                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Religion</label>
                                        <input type="text" class="form-control" name="religion" id="religion"
                                            placeholder="Religion">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nationality</label>
                                        <select name="nationality" id="nationality" class="form-select select2" required>
                                            <option value="" selected disabled>-- Select Nationality --</option>
                                            @foreach ($country as $item)
                                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Mobile Number</label>
                                        <input type="text" class="form-control" name="mobile_number" id="mobile_number"
                                            placeholder="Mobile Number" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Home Telephone</label>
                                        <input type="text" class="form-control" name="home_telephone"
                                            id="home_telephone" placeholder="Home Telephone">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email"
                                            id="email" placeholder="Email" required>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4 row">
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue" id="step2">Next</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3 step2" style="background: #0C2A58;border-radius: 24px;padding:22px;">
                            <div class="card-body p-0">
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Address</h5>
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Street Address</label>
                                        <input type="text" class="form-control" name="street_address"
                                            id="street_address" placeholder="Street Address">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Address Line 2</label>
                                        <input type="text" class="form-control" name="address_line_2"
                                            id="address_line_2" placeholder="Address Line 2">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" class="form-control" name="city" id="city"
                                            placeholder="City">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">County / State / Region</label>
                                        <input type="text" class="form-control" name="county_state_region"
                                            id="county_state_region" placeholder="County / State / Region">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ZIP / Postal Code</label>
                                        <input type="text" class="form-control" name="zip_postal_code"
                                            id="zip_postal_code" placeholder="ZIP / Postal Code">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Country</label>

                                        <select class="form-select select2" name="country" id="country" required>
                                            <option value="" selected disabled>-- Select Country --</option>
                                            @foreach ($country as $item)
                                            <option value="{{$item->name}}">{{$item->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Are you allowed to work in the UK?</label><br>
                                        <input type="radio" name="uk_work" value="Yes"> <span
                                            class="text-light">Yes</span><br>
                                        <input type="radio" name="uk_work" value="No"> <span
                                            class="text-light">No</span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Do you have a cleared DBS?</label><br>
                                        <input type="radio" name="dbs" value="Yes"> <span
                                            class="text-light">Yes</span><br>
                                        <input type="radio" name="dbs" value="No"> <span class="text-light">No</span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4 row">
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue back mt-0" id="step1">Back</span>
                                    </div>
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue" id="step3">Next</span>
                                    </div>
                                </div>


                            </div>
                        </div>


                        <div class="card mb-3 step3" style="background: #0C2A58;border-radius: 24px;padding:22px;">
                            <div class="card-body p-0">
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Profile Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Profile Photo</label>
                                        <input type="file" class="form-control" name="profile_photo" id="profile_photo">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Proof of ID</label>
                                        <input type="file" class="form-control" name="prof_of_id" id="prof_of_id">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Proof of Address</label>
                                        <input type="file" class="form-control" name="prof_of_address"
                                            id="prof_of_address">
                                    </div>
                                    {{-- <div class="col-md-6 mb-3">
                                        <label class="form-label">Certificates</label>
                                        <input type="file" class="form-control" name="certificated" id="certificated">
                                    </div> --}}

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label d-block">Certificates</label>

                                        <div id="certificates-wrapper">
                                            <div class="certificate-item row g-2 mb-2">
                                                <div class="col-md-6">
                                                    <input type="text" name="certificate_title[]" class="form-control" placeholder="Certificate Title" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="file" name="certificate_file[]" class="form-control" required>
                                                </div>
                                                
                                            </div>
                                        </div>

                                        <button type="button" id="add-certificate" class="btn btn-sm btn-primary mt-2">
                                            + Add More
                                        </button>
                                    </div>

                                   

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">DBS</label>
                                        <input type="file" class="form-control" name="dbs_one" id="dbs_one">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CV</label>
                                        <input type="file" class="form-control" name="cv" id="cv">
                                    </div>


                                </div>

                                <div class="d-flex justify-content-end mt-4 row">
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue back mt-0" id="step2">Back</span>
                                    </div>
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue" id="step4">Next</span>
                                    </div>
                                </div>


                            </div>
                        </div>


                        <div class="card mb-3 step4" style="background: #0C2A58;border-radius: 24px;padding:22px;">
                            <div class="card-body p-0">
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Bank Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-12 mb-4">
                                        <label class="form-label">Bank Type</label>
                                        <select name="bank_type" class="form-select" id="bank_type">
                                            <option value="international">International</option>
                                            <option value="uk">UK</option>
                                        </select>
                                    </div>
                                    <div id="international">
                                        <div class="row">
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Country Name</label>
                                                <input type="text" class="form-control"
                                                    name="international_country_name" id="international_country_name">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Account Name</label>
                                                <input type="text" class="form-control"
                                                    name="international_account_name" id="international_account_name">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Bank Name</label>
                                                <input type="text" class="form-control" name="international_bank_name"
                                                    id="international_bank_name">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Account Number</label>
                                                <input type="text" class="form-control"
                                                    name="international_account_number"
                                                    id="international_account_number">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Swift Code</label>
                                                <input type="text" class="form-control" name="swift_code"
                                                    id="swift_code">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Branch</label>
                                                <input type="text" class="form-control" name="branch" id="branch">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Branch Code</label>
                                                <input type="text" class="form-control" name="branch_code"
                                                    id="branch_code">
                                            </div>
                                        </div>
                                    </div>
                                    <div id="uk">
                                        <div class="row">
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Account Name</label>
                                                <input type="text" class="form-control" name="uk_account_name"
                                                    id="uk_account_name">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Bank Name</label>
                                                <input type="text" class="form-control" name="uk_bank_name"
                                                    id="uk_bank_name">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Account Number</label>
                                                <input type="text" class="form-control" name="uk_account_number"
                                                    id="uk_account_number">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-3">
                                                <label class="form-label">Sort Code</label>
                                                <input type="text" class="form-control" name="sort_code" id="sort_code">
                                            </div>
                                        </div>
                                    </div>




                                </div>

                                <div class="d-flex justify-content-end mt-4 row">
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue back mt-0" id="step3">Back</span>
                                    </div>
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue" id="step5">Next</span>
                                    </div>
                                </div>


                            </div>
                        </div>


                         <div class="card mb-3 step5" style="background: #0C2A58;border-radius: 24px;padding:22px;">
                            <div class="card-body p-0">
                                
                                 <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Emergency Contact
                                    Details</h5>
                                <div class="row g-3">

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Forename<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="emergency_forename"
                                            id="emergency_forename" placeholder="Forename" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Surname<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="emergency_surname"
                                            id="emergency_surname" placeholder="Surname" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="contact_email" id="email"
                                            placeholder="" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="contact_phone" id="phone"
                                            placeholder="" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Address<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="contact_address" id="address"
                                            placeholder="" required>
                                    </div>
                                    

                                   

                                </div>

                                <div class="d-flex justify-content-end mt-4 row">
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue back mt-0" id="step4">Back</span>
                                    </div>
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue" id="step6">Next</span>
                                    </div>
                                </div>


                            </div>
                        </div>



                        <div class="card mb-3 step6" style="background: #0C2A58;border-radius: 24px;padding:22px;">
                            <div class="card-body p-0">
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Terms & Conditions</h5>
                                <div class="row g-3">

                                    
                                    <div class="col-md-12 mb-3">
                                       <div>
                                            {!! $terms->staff_terms_condition !!}
                                       </div>
                                    </div>

                                    

                                    <div class="col-md-6 mb-3">
                                        <label for="signature-pad" class="form-label">Your Digital Signature:</label>
                                        <div class="border rounded bg-white position-relative" style="height: 220px;">
                                            <canvas id="signature-pad"
                                                style="width:100%; height:100%; border:1px solid #ddd; border-radius:8px; cursor:crosshair;"></canvas>
                                            <button type="button" id="clear-signature"
                                                class="btn btn-sm btn-danger position-absolute"
                                                style="top:5px; right:5px; z-index:10;">Clear</button>
                                        </div>
                                        <input type="hidden" name="signature" id="signature-input">
                                    </div>


                                    <div class="col-md-12 mb-3">
                                        <div class="align-items-center col-md-12 d-flex">
                                            <input class="form-check-input" type="checkbox" name="terms" id="terms" value="1" required>
                                            <label class="form-check-label text-light ms-2" for="terms">
                                                I confirm that the information provided is true and complete.
                                            </label>
                                        </div>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-end mt-4 row">
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue back mt-0" id="step5">Back</span>
                                    </div>
                                    <div class="col-lg-2 col-6">
                                        <button type="submit" id="submitBtn" class="btn btn-primary px-4"
                                            style="background-color: #A39161; border-color: #A39161; font-size: 18px;">
                                            <span class="btn-text">Submit</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                                aria-hidden="true"></span>
                                        </button>

                                    </div>

                                </div>

                            </div>
                        </div>




                    </div>

                </form>
            </div>

        </div>
    </div>
</section>



@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

 <script>
$(document).ready(function() {
    // Add new certificate row
    $("#add-certificate").on("click", function() {
        let newRow = `
            <div class="certificate-item row g-2 mb-2">
                <div class="col-md-6">
                    <input type="text" name="certificate_title[]" class="form-control" placeholder="Certificate Title">
                </div>
                <div class="col-md-5">
                    <input type="file" name="certificate_file[]" class="form-control">
                </div>
                <div class="col-md-1 d-flex align-items-center">
                    <button type="button" class="btn btn-danger remove-certificate">×</button>
                </div>
            </div>
        `;
        $("#certificates-wrapper").append(newRow);
    });

    // Remove a certificate row
    $(document).on("click", ".remove-certificate", function() {
        $(this).closest(".certificate-item").remove();
    });
});
</script>

<script>
    $(document).ready(function() {


        /*** ===== SIGNATURE PAD ===== ***/
        const canvas = document.getElementById("signature-pad");
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)', // transparent background
            penColor: 'black'
        });

        // Resize canvas to fit container
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear(); // otherwise old signature might get stretched
        }
        resizeCanvas();
        window.addEventListener("resize", resizeCanvas);

        // Clear button
        $("#clear-signature").click(function() {
            signaturePad.clear();
            $("#signature-input").val(''); // reset hidden input
        });

        // Update hidden input on end of drawing
        canvas.addEventListener("mouseup", function() {
            if (!signaturePad.isEmpty()) {
                $("#signature-input").val(signaturePad.toDataURL("image/png"));
            }
        });
        canvas.addEventListener("touchend", function() {
            if (!signaturePad.isEmpty()) {
                $("#signature-input").val(signaturePad.toDataURL("image/png"));
            }
        });


    /*** ===== DATE OF BIRTH VALIDATION ===== ***/
    let today = new Date();
    let yyyy = today.getFullYear();
    let mm = String(today.getMonth() + 1).padStart(2, '0');
    let dd = String(today.getDate()).padStart(2, '0');
    let todayStr = `${yyyy}-${mm}-${dd}`;
    $("#date_of_birth").attr("max", todayStr); // ✅ future date disable

    $("#date_of_birth").on("change", function() {
        const selected = $(this).val();
        if (selected > todayStr) {
            alert("You cannot select today's or a future date for Date of Birth!");
            $(this).val('');
        }
    });
    

    /*** ===== STEP NAVIGATION ===== ***/
    $(".step2, .step3, .step4, .step5, .step6").hide();

    // function goToStep(current, next) {
    //     if ($("#staffApplicationForm").valid()) {
    //         $(current).hide();
    //         $(next).fadeIn();
    //         updateProgress();
    //     }
    // }
    

    // Next buttons
    $("#step2").click(() => goToStep(".step1", ".step2"));
    $("#step3").click(() => goToStep(".step2", ".step3"));
    $("#step4").click(() => goToStep(".step3", ".step4"));
    $("#step5").click(() => goToStep(".step4", ".step5"));
    $("#step6").click(() => goToStep(".step5", ".step6"));

    // Back buttons
    $(".back#step1").click(() => { $(".step2").hide(); $(".step1").fadeIn(); });
    $(".back#step2").click(() => { $(".step3").hide(); $(".step2").fadeIn(); });
    $(".back#step3").click(() => { $(".step4").hide(); $(".step3").fadeIn(); });
    $(".back#step4").click(() => { $(".step5").hide(); $(".step4").fadeIn(); });
    $(".back#step5").click(() => { $(".step6").hide(); $(".step5").fadeIn(); });

    /*** ===== JQUERY VALIDATION ===== ***/
    // $("#staffApplicationForm").validate({
    //     rules: {
    //         surname: { required: true },
    //     },
    //     messages: {
    //         surname: "Please enter your surname*",
    //     },
    //     errorClass: 'text-warning error_message',
    //     errorPlacement: function(error, element) {
    //         error.insertAfter(element);
    //     }
    // });

    /*** ===== JQUERY VALIDATION ===== ***/
    $("#staffApplicationForm").validate({
        ignore: [], // ensures hidden but visible-step fields are still validated
        errorClass: 'text-warning error_message',
        validClass: 'valid',
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        highlight: function(element) {
            $(element).addClass('text-warning error_message');
        },
        unhighlight: function(element) {
            $(element).removeClass('text-warning error_message');
        }
    });

    /*** ===== STEP NAVIGATION (with auto-scroll to first error) ===== ***/
    function goToStep(current, next) {
        let currentStep = $(current);
        let nextStep = $(next);

        // Validate only visible fields in current step
        let stepIsValid = true;
        currentStep.find("input, select, textarea").each(function() {
            if ($(this).is(":visible") && !$(this).valid()) {
                stepIsValid = false;
            }
        });

        if (stepIsValid) {
            // ✅ Move to next step
            currentStep.hide();
            nextStep.fadeIn();
            updateProgress();
            
        } else {
            // ❌ Scroll to the first invalid field
            let firstError = currentStep.find(".text-warning, .error_message, input.error, select.error, textarea.error").first();
            if (firstError.length) {
                $("html, body").animate({
                    scrollTop: firstError.offset().top - 120
                }, 600);
                firstError.focus();
            }
        }
    }


    /*** ===== BANK TYPE TOGGLE ===== ***/
    function toggleBankType() {
        let type = $("#bank_type").val();
        if (type === "international") {
            $("#international").show();
            $("#uk").hide();
        } else if (type === "uk") {
            $("#international").hide();
            $("#uk").show();
        } else {
            $("#international").hide();
            $("#uk").hide();
        }
    }
    toggleBankType();
    $("#bank_type").on("change", toggleBankType);

    /*** ===== PROGRESS BAR ===== ***/
    function updateProgress() {
        const countedGroups = {};
        let totalFields = 0;
        let filledFields = 0;

        $("#staffApplicationForm input, #staffApplicationForm select, #staffApplicationForm textarea").each(function() {
            const $field = $(this);
            if ($field.is("[type=hidden]")) return;

            const type = $field.attr("type");
            const name = $field.attr("name");

            if ((type === "checkbox" || type === "radio") && name) {
                if (!countedGroups[name]) {
                    countedGroups[name] = true;
                    totalFields++;
                    if ($(`input[name='${name}']:checked`).length > 0) filledFields++;
                }
            } else if (type === "file") {
                totalFields++;
                if ($field[0].files.length > 0) filledFields++;
            } else {
                totalFields++;
                if ($field.val() && $field.val().trim() !== "") filledFields++;
            }
        });

        const progress = totalFields ? Math.round((filledFields / totalFields) * 100) : 0;
        $(".progress-bar").css("width", progress + "%");
        $(".progress-container small").text(progress + "%");
    }
    $(document).on("input change", "#staffApplicationForm input, #staffApplicationForm select, #staffApplicationForm textarea", updateProgress);
    updateProgress();

    
    /*** ===== FINAL FORM SUBMISSION =====***/
    $("#staffApplicationForm").submit(function(e) {
        e.preventDefault();

       
        if (!$("#staffApplicationForm").valid()) return;

        if (signaturePad.isEmpty()) {
            alert("Please provide your digital signature before submitting!");
            return;
        }

        $("#signature-input").val(signaturePad.toDataURL("image/png"));

        var formData = new FormData(this);
        // Append CSRF token explicitly
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        var submitBtn = $("#submitBtn");

        submitBtn.prop("disabled", true);
        submitBtn.find(".btn-text").addClass("d-none");
        submitBtn.find(".spinner-border").removeClass("d-none");

        $.ajax({
            url: '/staff-applications-form',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            // headers: {
            //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            // },
            success: function(response) {
                window.location.href = "{{ route('staff-applications.success') }}";
            },
            error: function(xhr) {
                alert("Something went wrong while submitting!");
                console.error(xhr.responseText);
            },
            complete: function() {
                submitBtn.prop("disabled", false);
                submitBtn.find(".btn-text").removeClass("d-none");
                submitBtn.find(".spinner-border").addClass("d-none");
            }
        });
    });

});

</script>

@endsection