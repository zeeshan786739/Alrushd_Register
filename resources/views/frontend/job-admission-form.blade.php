@extends('layouts.app')

@section('title','Al-rushd Online School - Job Application')

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
<a href="{{ route('job-applications') }}" class="logo d-flex align-items-center m-auto" style="background: #f6f9fc;padding-top:10px;padding-bottom:10px;">
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


                    <h2 class="form-heading mb-0">Job Application Form</h2>
                    <div id="success-message" class="mt-3"></div>
                </div>
            </div>

            <div class="row mt-5">

                <form id="staffApplicationForm" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="col-lg-8 m-auto">

                        <div class="card mb-3 step1" style="background: #0C2A58;border-radius: 24px;padding:22px;">
                            <div class="card-body p-0">
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Personal Details</h5>
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><b>Full Name</b></label>
                                        <input type="text" class="form-control" name="name" id="name" placeholder="Full Name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="text" class="form-control" name="email" id="email" placeholder="Email Address" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone Number" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" name="dob" id="dob" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gender</label>
                                        <select class="form-select" name="gender" id="gender" required>
                                            <option value="">-- Select Gender --</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Marital Status</label>
                                        <select class="form-select" name="marital_status" id="marital_status" required>
                                            <option value="">-- Select Marital --</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="UnMarried">UnMarried</option>
                                            <option value="Others">Others</option>
                                        </select>
                                        
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
                                        <label class="form-label">Street Address</label>
                                        <input type="text" class="form-control" name="address" id="address" placeholder="Current Address" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" class="form-control" name="city" id="city" placeholder="City" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">State/Province</label>
                                        <input type="text" class="form-control" name="state" id="state" placeholder="State/Province" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Zip/Postal Code</label>
                                        <input type="text" class="form-control" name="postal_code" id="postal_code" placeholder="Zip/Postal Code" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Country of Residence</label>
                                        <select class="form-select select2" name="country_of_residence" id="country_of_residence" required>
                                            <option value="">-- Select Country --</option>
                                            @foreach ($country as $item)
                                            <option value="{{$item->name}}">{{$item->name}}</option>
                                            @endforeach
                                        </select>
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
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Position Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-light">Position Applied For</label>
                                        <select class="form-select select2" name="position_applied_for" id="position_applied_for" required>
                                            <option value="">-- Select Position --</option>
                                            <option value="Primary School Teacher">Primary School Teacher</option>
                                            <option value="Secondary School Teacher">Secondary School Teacher</option>
                                            <option value="English Teacher">English Teacher</option>
                                            <option value="Mathematics Teacher">Mathematics Teacher</option>
                                            <option value="Science Teacher (General)">Science Teacher (General)</option>
                                            <option value="Biology Teacher">Biology Teacher</option>
                                            <option value="Chemistry Teacher">Chemistry Teacher</option>
                                            <option value="Physics Teacher">Physics Teacher</option>
                                            <option value="Computer Science / ICT Teacher">Computer Science / ICT Teacher</option>
                                            <option value="History Teacher">History Teacher</option>
                                            <option value="Geography Teacher">Geography Teacher</option>
                                            <option value="Business Studies Teacher">Business Studies Teacher</option>
                                            <option value="Economics Teacher">Economics Teacher</option>
                                            <option value="Sociology Teacher">Sociology Teacher</option>
                                            <option value="Psychology Teacher">Psychology Teacher</option>
                                            <option value="Accounting Teacher">Accounting Teacher</option>
                                            <option value="Art & Design Teacher">Art & Design Teacher</option>
                                            <option value="Physical Education (PE) Teacher">Physical Education (PE) Teacher</option>
                                            <option value="Arabic Language Teacher">Arabic Language Teacher</option>
                                            <option value="Islamic Studies Teacher">Islamic Studies Teacher</option>
                                            <option value="Qur’an & Tajweed Teacher">Qur’an & Tajweed Teacher</option>
                                            <option value="Hifdh Teacher">Hifdh Teacher</option>
                                            <option value="Special Educational Needs (SEN) Teacher">Special Educational Needs (SEN) Teacher</option>
                                            <option value="Teaching Assistant">Teaching Assistant</option>
                                            <option value="Exam Invigilator">Exam Invigilator</option>
                                            <option value="Homework Reviewer / Marker">Homework Reviewer / Marker</option>
                                            <option value="A-Level English Teacher">A-Level English Teacher</option>
                                            <option value="A-Level Mathematics Teacher">A-Level Mathematics Teacher</option>
                                            <option value="A-Level Biology Teache">A-Level Biology Teacher</option>
                                            <option value="A-Level Chemistry Teacher">A-Level Chemistry Teacher</option>
                                            <option value="A-Level Physics Teacher">A-Level Physics Teacher</option>
                                            <option value="A-Level Business Studies Teacher">A-Level Business Studies Teacher</option>
                                            <option value="A-Level Economics Teacher">A-Level Economics Teacher</option>
                                            <option value="A-Level Psychology Teacher">A-Level Psychology Teacher</option>
                                            <option value="A-Level Sociology Teacher">A-Level Sociology Teacher</option>
                                            <option value="Madrasah Teacher (Evening / Weekend)">Madrasah Teacher (Evening / Weekend)</option>
                                            <option value="Madrasah Coordinator">Madrasah Coordinator</option>
                                            <option value="Islamic Curriculum Lead">Islamic Curriculum Lead</option>
                                            <option value="Qur’an Examiner">Qur’an Examiner</option>
                                            <option value="Hifdh Supervisor">Hifdh Supervisor</option>
                                            <option value="Principal">Principal</option>
                                            <option value="Vice Principal / Deputy Head Teacher">Vice Principal / Deputy Head Teacher</option>
                                            <option value="Assistant Principal">Assistant Principal</option>
                                            <option value="Head of Primary">Head of Primary</option>
                                            <option value="Head of Secondary">Head of Secondary</option>
                                            <option value="Head of Sixth Form">Head of Sixth Form</option>
                                            <option value="Head of Madrasah">Head of Madrasah</option>
                                            <option value="Head of Department (Specify Subject)">Head of Department (Specify Subject)</option>
                                            <option value="Senior Leadership Team (SLT) Member">Senior Leadership Team (SLT) Member</option>
                                            <option value="School Administrator">School Administrator</option>
                                            <option value="Admissions Officer">Admissions Officer</option>
                                            <option value="HR / Recruitment Officer">HR / Recruitment Officer</option>
                                            <option value="Finance Officer / Accountant">Finance Officer / Accountant</option>
                                            <option value="Payroll & Compliance Officer">Payroll & Compliance Officer</option>
                                            <option value="Data & Exams Officer">Data & Exams Officer</option>
                                            <option value="Receptionist / Enrolment Assistant">Receptionist / Enrolment Assistant</option>
                                            <option value="Office Assistant">Office Assistant</option>
                                            <option value="IT Support Officer">IT Support Officer</option>
                                            <option value="Systems Administrator">Systems Administrator</option>
                                            <option value="LMS / Portal Manager">LMS / Portal Manager</option>
                                            <option value="Web Developer">Web Developer</option>
                                            <option value="Digital Marketing Officer">Digital Marketing Officer</option>
                                            <option value="Graphic Designer">Graphic Designer</option>
                                            <option value="Video Editor / Media Production Officer">Video Editor / Media Production Officer</option>
                                            <option value="Marketing & Communications Manager">Marketing & Communications Manager</option>
                                            <option value="Social Media Manager">Social Media Manager</option>
                                            <option value="Content Creator">Content Creator</option>
                                            <option value="Outreach & Partnerships Officer">Outreach & Partnerships Officer</option>
                                            <option value="Student Success Officer">Student Success Officer</option>
                                            <option value="Student Support Coordinator">Student Support Coordinator</option>
                                            <option value="Pastoral Care Officer">Pastoral Care Officer</option>
                                            <option value="Behaviour Support Officer">Behaviour Support Officer</option>
                                            <option value="Learning Mentor">Learning Mentor</option>
                                            <option value="School Counsellor">School Counsellor</option>
                                            <option value="Site Manager">Site Manager</option>
                                            <option value="Caretaker / Maintenance Staff">Caretaker / Maintenance Staff</option>
                                            <option value="Cleaner">Cleaner</option>
                                            <option value="Security Officer">Security Officer</option>
                                            <option value="Community Engagement Officer">Community Engagement Officer</option>
                                            <option value="Events Coordinator">Events Coordinator</option>
                                            <option value="Alumni Relations Officer">Alumni Relations Officer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-light">Department / School Section</label>
                                        <select class="form-select" name="department" id="department" required>
                                            <option value="">-- Select Department --</option>
                                            <option value="Primary">Primary</option>
                                            <option value="Secondary">Secondary</option>
                                            <option value="Sixth Form">Sixth Form</option>
                                            <option value="Admin">Admin</option>
                                            <option value="HR">HR</option>
                                            <option value="Finance">Finance</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Type of Employment</label>
                                        <select class="form-select" name="type_of_employment" id="type_of_employment" required>
                                            <option value="">-- Select --</option>
                                            <option value="Full-Time">Full-Time</option>
                                            <option value="Part-Time">Part-Time</option>
                                            <option value="Temporary / Contract">Temporary / Contract</option>
                                        </select>

                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-light">Available Start Date</label>
                                        <input type="date" class="form-control" name="start_date" id="start_date" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-light">Preferred Working Hours</label>
                                        <select class="form-select" name="preferred_working_hours" id="preferred_working_hours" required>
                                            <option value="">-- Select --</option>
                                            <option value="Weekdays">Weekdays</option>
                                            <option value="Weekends">Weekends</option>
                                            <option value="Evening Only">Evening Only</option>
                                            <option value="Flexible">Flexible</option>
                                        </select>

                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-light">Expected Salary (optional)</label>
                                        <input type="text" class="form-control" name="expected_salary" id="expected_salary"
                                            placeholder="e.g. £25,000 per annum">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-light">How did you hear about this job?</label>
                                        <select class="form-select" name="about_this_job" id="about_this_job" required>
                                            <option value="">-- Select Option --</option>
                                            <option value="Website">Website</option>
                                            <option value="Referral">Referral</option>
                                            <option value="Social Media">Social Media</option>
                                            <option value="Job Board">Job Board</option>
                                            <option value="Other">Other</option>
                                        </select>
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
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Education & Qualifications</h5>
                                

                                <div class="education-wrapper">
                                    <label class="form-label mb-4"><b>Education Details</b></label>

                                    <div id="education-container">
                                        <div class="border education-row g-3 mb-4 p-3 position-relative rounded-2 row">
                                            <div class="col-md-6 mb-3">
                                                <input type="text" class="form-control" name="qualification[]" placeholder="Qualification" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <input type="text" class="form-control" name="subject[]" placeholder="Subject / Field of Study" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <input type="text" class="form-control" name="institution[]" placeholder="Institution Name" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <input type="date" class="form-control" name="graduation_year[]" required>
                                            </div>
                                        
                                        </div>
                                    </div>

                                   <div class="text-end">
                                        <button type="button" id="add-education" class="btn btn-primary btn-sm">Add More</button>
                                   </div>
                                </div>


                                 <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teaching Qualification</label>
                                        <input type="text" class="form-control" name="teaching_qualification" id="teaching_qualification" placeholder="(e.g., QTS, PGCE, etc.)" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Other Relevant Certificates</label>
                                        <input type="text" class="form-control" name="relevant_certificates" id="relevant_certificates" placeholder="(TESOL, Child Protection, etc.)" required>
                                    </div>
                                    {{-- <div class="col-md-6 mb-3">
                                        <label class="form-label">Upload Certificates</label>
                                        <input type="file" class="form-control" name="upload_certificates" id="upload_certificates" required>
                                    </div> --}}

                                   <div class="col-md-6 mb-3">
                                        <label class="form-label">Upload Certificates <span class="text-warning">(optional)</span></label>
                                        <div id="certificate-wrapper">
                                            <div class="input-group mb-2 certificate-row flex-nowrap">
                                                <input type="file" class="form-control" name="upload_certificates[]" 
                                                    style="border-top-right-radius: 0px !important; border-bottom-right-radius: 0px !important;">
                                                <button type="button" class="btn add-more" style="background-color:#AE9A66; color:white;">+</button>
                                            </div>
                                        </div>
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
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Work Experience</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current / Most Recent Employer</label>
                                        <input type="text" class="form-control" name="current" id="current" placeholder="Current / Most Recent Employer" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Position Held</label>
                                        <input type="text" class="form-control" name="position_held" id="position_held" placeholder="Position Held" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" id="start_date" placeholder="Start Date" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date" id="end_date" placeholder="Start Date" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Responsibilities Summary</label>
                                        <textarea class="form-control" name="responsibilities" id="responsibilities" rows="5" cols="5" placeholder="Write a brief summary of your key responsibilities..." required></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reason for Leaving</label>
                                        <input type="text" placeholder="Reason for Leaving" class="form-control" name="reason_for_leaving" id="reason_for_leaving" required>
                                    </div>
                                </div>

                                <div class="previous-employment-wrapper mb-3">
                                    
                                    <div id="employment-container">
                                       
                                    </div>

                                    <button type="button" id="add-employment" class="btn btn-primary btn-sm">Add Previous Employment</button>
                                </div>


                                <div class="row g-3">
                                    {{-- <div class="col-md-6 mb-3">
                                        <label class="form-label">Previous Employment</label>
                                        <input type="text" class="form-control" name="previous_employment" id="previous_employment" placeholder="(repeatable field group if needed)" required>
                                    </div> --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Upload CV</label>
                                        <input type="file" class="form-control" name="upload_cv" id="upload_cv" required>
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
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Skills & Competencies</h5>
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Subjects / Areas you can teach</label>
                                        <input type="text" class="form-control" name="subject_skill" placeholder="Subjects / Areas you can teach" id="subject_skill" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Languages Spoken</label>
                                        <input type="text" placeholder="Languages Spoken" class="form-control" name="languages_spoken" id="languages_spoken" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Level of Computer Literacy</label>
                                        <select class="form-select" name="level_of_computer" id="level_of_computer" required>
                                            <option value="">-- Select Option --</option>
                                            <option value="Basic">Basic</option>
                                            <option value="Intermediate">Intermediate</option>
                                            <option value="Advanced">Advanced</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Microsoft Teams / Zoom Experience</label>
                                        <select class="form-select" name="microsoft_teams" id="microsoft_teams" required>
                                            <option value="">-- Select Option --</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Online Teaching Experience</label>
                                        <select class="form-select" name="online_teaching_experience" id="online_teaching_experience" required>
                                            <option value="">-- Select Option --</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Years of Teaching Experience</label>
                                        <input type="text" class="form-control" name="years_of_teaching_xperience" id="years_of_teaching_xperience" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Other Relevant Skills</label>
                                        <textarea class="form-control" name="other_relevant_skills" id="other_relevant_skills" rows="3" placeholder="Mention any other relevant skills..." required></textarea>
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
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">References</h5>
                                
                                <div class="references-wrapper mb-3">
                                
                                <div id="references-container">
                                    <div class="row g-3 reference-row position-relative border p-3 mb-5 rounded">
                                        <div class="col-md-6 mb-3">
                                            <input type="text" class="form-control" name="refer_name[]" placeholder="Reference Name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="email" class="form-control" name="refer_email[]" placeholder="Reference Email" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="text" class="form-control" name="refer_phone[]" placeholder="Reference Phone" required>
                                        </div>

                                    </div>
                                </div>

                                <button type="button" id="add-reference" class="btn btn-primary btn-sm">Add More Reference</button>
                            </div>



                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Relationship to Applicant </label>
                                        <input type="text" class="form-control" name="relationship_to_applicant" id="relationship_to_applicant" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4 row">
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue back mt-0" id="step5">Back</span>
                                    </div>
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue" id="step7">Next</span>
                                    </div>
                                </div>


                            </div>
                        </div>


                        <div class="card mb-3 step7" style="background: #0C2A58;border-radius: 24px;padding:22px;">
                            <div class="card-body p-0">
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Additional Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Right to Work in the UK? </label>
                                        <select class="form-select" name="right_to_work" id="right_to_work" required>
                                            <option value="">-- Select Option --</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                            <option value="Freelancer">Freelancer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">National Insurance Number (optional)</label>
                                        <input type="text" placeholder="National Insurance Number" class="form-control" name="insurance_number" id="insurance_number" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Do you have a current DBS Certificate?</label>
                                        <select class="form-select" name="dbs_certificate" id="dbs_certificate" required>
                                            <option value="">-- Select Option --</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">DBS Issue Date</label>
                                        <input type="date" class="form-control" name="dbs_issue_date" id="dbs_issue_date" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Upload DBS Certificate <span class="text-warning">(optional)</span></label>
                                        <input type="file" class="form-control" name="upload_dbs_certificate" id="upload_dbs_certificate">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Willing to undergo background checks?</label>
                                        <select class="form-select" name="background_check" id="background_check" required>
                                            <option value="">-- Select Option --</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    {{-- <div class="col-md-6 mb-3">
                                        <label class="form-label">Notice Period (text)</label>
                                        <input type="text" placeholder="Notice Period" class="form-control" name="notice_period" id="notice_period" required>
                                    </div> --}}
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Any medical conditions or special requirements?</label>
                                        <textarea style="height: 100px !important;" class="form-control" name="special_requirement" id="special_requirement" rows="4" cols="4" placeholder="Please mention any medical conditions or special requirements if applicable" required></textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Personal Statement</label>
                                        <textarea style="height: 100px !important;" class="form-control" name="personal_statement" id="personal_statement" rows="4" cols="4" placeholder="Why do you want to work with Al-Rushd?" required></textarea>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-end mt-4 row">
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue back mt-0" id="step6">Back</span>
                                    </div>
                                    <div class="col-lg-2 col-6">
                                        <span class="btn btn-continue" id="step8">Next</span>
                                    </div>
                                </div>


                            </div>
                        </div>



                        <div class="card mb-3 step8" style="background: #0C2A58;border-radius: 24px;padding:22px;">
                            <div class="card-body p-0">
                                <h5 class="form-section-title fw-bold mb-4" style="font-size: 24px;">Declaration</h5>
                                <div class="row g-3">
                                    <div class="align-items-center col-md-12 d-flex">
                                        <input class="form-check-input" type="checkbox" name="true_and_complete" id="true_and_complete" value="1" required>
                                        <label class="form-check-label text-light ms-2" for="true_and_complete">
                                            I confirm that the information provided is true and complete.
                                        </label>
                                    </div>
                                    <div class="align-items-center col-md-12 d-flex">
                                        <input class="form-check-input" type="checkbox" name="recruitment_purposes" id="recruitment_purposes" value="1" required>
                                        <label class="form-check-label text-light ms-2" for="recruitment_purposes">
                                            I consent to my data being stored and processed by Al-Rushd for recruitment purposes.
                                        </label>

                                    </div>

                                     <div class="d-flex justify-content-end mt-4 row">
                                        <div class="col-lg-2 col-6">
                                            <span class="btn btn-continue back mt-0" id="step7">Back</span>
                                        </div>
                                        <div class="col-lg-2 col-6">
                                            <button type="submit" id="submitBtn" class="btn btn-primary px-4" style="background-color: #A39161; border-color: #A39161; font-size: 18px;">
                                                <span class="btn-text">Submit</span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                            </button>

                                        </div>

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
<script>
$(document).ready(function() {

    /*** ===== ADD/REMOVE REFERENCES ===== ***/
    $('#add-reference').click(function() {
        let row = `<div class="row g-3 reference-row position-relative border p-3 mb-5 rounded">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="refer_name[]" placeholder="Reference Name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="email" class="form-control" name="refer_email[]" placeholder="Reference Email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="refer_phone[]" placeholder="Reference Phone" required>
                        </div>
                        <div class="col-md-12 text-end">
                             <button type="button" class="btn btn-danger btn-sm remove-reference"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>`;
        $('#references-container').append(row);
    });

    $(document).on('click', '.remove-reference', function() {
        $(this).closest('.reference-row').remove();
        updateProgress();
    });

    /*** ===== ADD/REMOVE EMPLOYMENT ===== ***/
    $('#add-employment').click(function() {
        let row = `<div class="border employment-row g-3 mb-4 p-3 rounded-2 row">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="current[]" placeholder="Previous Employer" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="position_held[]" placeholder="Position Held" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="date" class="form-control" name="start_date[]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="date" class="form-control" name="end_date[]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <textarea class="form-control" name="responsibilities[]" rows="5" placeholder="Responsibilities Summary" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="reason_for_leaving[]" placeholder="Reason for Leaving" required>
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="button" class="btn btn-danger btn-sm remove-employment"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>`;
        $('#employment-container').append(row);
    });

    $(document).on('click', '.remove-employment', function() {
        $(this).closest('.employment-row').remove();
        updateProgress();
    });

    /*** ===== ADD/REMOVE EDUCATION ===== ***/
    $('#add-education').click(function() {
        let row = `<div class="border education-row g-3 mb-4 p-3 rounded-2 row">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="qualification[]" placeholder="Qualification" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="subject[]" placeholder="Subject / Field of Study" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="institution[]" placeholder="Institution Name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="date" class="form-control" name="graduation_year[]" required>
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="button" class="btn btn-danger btn-sm remove-education"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>`;
        $('#education-container').append(row);
    });

    $(document).on('click', '.remove-education', function() {
        $(this).closest('.education-row').remove();
        updateProgress();
    });

    /*** ===== ADD/REMOVE CERTIFICATES ===== ***/
    $(document).on("click", ".add-more", function() {
        let newField = `
            <div class="input-group mb-2 certificate-row flex-nowrap">
                <input type="file" class="form-control" name="upload_certificates[]" style="border-top-right-radius:0; border-bottom-right-radius:0;">
                <button type="button" class="btn remove-certificate" style="background-color:#dc3545; color:white;">-</button>
            </div>`;
        $("#certificate-wrapper").append(newField);
    });

    $(document).on("click", ".remove-certificate", function() {
        $(this).closest(".certificate-row").remove();
        updateProgress();
    });

    /*** ===== STEP FORM NAVIGATION ===== ***/
    $(".step2, .step3, .step4, .step5, .step6, .step7, .step8").hide();

    function goToStep(current, next) {
        if ($("#staffApplicationForm").valid()) {
            $(current).hide();
            $(next).fadeIn();
        }
    }

    $("#step2").click(() => goToStep(".step1", ".step2"));
    $("#step3").click(() => goToStep(".step2", ".step3"));
    $("#step4").click(() => goToStep(".step3", ".step4"));
    $("#step5").click(() => goToStep(".step4", ".step5"));
    $("#step6").click(() => goToStep(".step5", ".step6"));
    $("#step7").click(() => goToStep(".step6", ".step7"));
    $("#step8").click(() => goToStep(".step7", ".step8"));

    $(".back#step1").click(() => { $(".step2").hide(); $(".step1").fadeIn(); });
    $(".back#step2").click(() => { $(".step3").hide(); $(".step2").fadeIn(); });
    $(".back#step3").click(() => { $(".step4").hide(); $(".step3").fadeIn(); });
    $(".back#step4").click(() => { $(".step5").hide(); $(".step4").fadeIn(); });
    $(".back#step5").click(() => { $(".step6").hide(); $(".step5").fadeIn(); });
    $(".back#step6").click(() => { $(".step7").hide(); $(".step6").fadeIn(); });
    $(".back#step7").click(() => { $(".step8").hide(); $(".step7").fadeIn(); });

    /*** ===== FORM VALIDATION ===== ***/
    $("#staffApplicationForm").validate({
        rules: {
            name: { required: true },
            dob: { required: true, date: true },
        },
        errorClass: 'text-warning error_message',
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        }
    });

    /*** ===== PROGRESS BAR =====***/
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

    /*** ===== DOB VALIDATION =====***/
    let today = new Date().toISOString().split('T')[0];
    $('#dob').attr('max', today);

    $('#dob').on('change', function() {
        let selected = $(this).val();
        if (selected >= today) {
            alert("You cannot select today's or future date.");
            $(this).val('');
        }
        updateProgress();
    });

    /*** ===== AJAX FORM SUBMIT =====***/
    $("#staffApplicationForm").submit(function(e) {
        e.preventDefault();
        if (!$(this).valid()) return;

        let formData = new FormData(this);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        let submitBtn = $("#submitBtn");
        submitBtn.prop("disabled", true);
        submitBtn.find(".btn-text").addClass("d-none");
        submitBtn.find(".spinner-border").removeClass("d-none");

        $.ajax({
            url: '/job-applications-form', // Controller route
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if(response.success){
                    // alert(response.message);
                    // Clear form only on success
                    $("#staffApplicationForm")[0].reset();
                    $(".reference-row, .employment-row, .education-row, .certificate-row").remove();
                    updateProgress();
                    window.location.href = "{{ route('job-applications.success') }}";
                } else {
                    alert("Something went wrong! Please try again.");
                }
            },
            error: function(xhr){
                let errMsg = "Validation error!";
                if(xhr.responseJSON && xhr.responseJSON.errors){
                    errMsg = Object.values(xhr.responseJSON.errors).flat().join("\n");
                }
                alert(errMsg);
            },
            complete: function(){
                submitBtn.prop("disabled", false);
                submitBtn.find(".btn-text").removeClass("d-none");
                submitBtn.find(".spinner-border").addClass("d-none");
            }
        });
    });

});
</script>
@endsection
