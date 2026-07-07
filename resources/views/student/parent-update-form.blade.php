@extends('student.app')
@section('title','Edit Parent"s Information')
@section('student')

<section>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-5 m-auto">
                <!-- Progress Header -->
                <div class="progress-container mb-4">
                    <h5 class="mb-0 text-light title">Estimated time remaining: 05 minutes</h5>
                    <div class="progress mt-2">
                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 30%;"></div>
                    </div>
                    <small id="progressText" class="text-light">30%</small>
                </div>
            </div>
        </div>

        <form action="{{ route('parents-update',$data['id']) }}" method="POST" enctype="multipart/form-data" id="myForm">
            @csrf
            @method('PUT')

            <div class="row d-flex justify-content-center">
                <div class="col-lg-10">

                    {{-- ===================== PRIMARY GUARDIAN ===================== --}}
                    <div class="card p-4 mb-3" style="background-color:#0c2a58;border-radius:24px;color:#FFF;">
                        <div class="card-body">
                            <h3 class="text-center mb-5" style="color: #AE9A66;font-size: 24px;font-weight: 600;">
                                Primary Parent / Guardian Information
                            </h3>

                            <div class="row">
                                {{-- Title --}}
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label for="title">Title</label>
                                        <select name="title" id="title" class="form-control form-select" required>
                                            <option value="">-- Select --</option>
                                            @foreach(['Mr','Mrs','Miss','Ms','Mx','Dr','Prof','Rev','Sir','Dame','Lady','Lord'] as $title)
                                            <option value="{{ $title }}" {{ old('title', $data['title'] ?? '') == $title ? 'selected' : '' }}>
                                                {{ $title }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- First Name --}}
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label for="fname">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="fname" class="form-control"
                                            value="{{ old('fname', $data['fname'] ?? '') }}" required>
                                        @error('fname') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Last Name --}}
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label for="lname">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="lname" class="form-control"
                                            value="{{ old('lname', $data['lname'] ?? '') }}" required>
                                        @error('lname') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Relationship --}}
                                <div class="col-lg-12">
                                    <div class="form-group mb-4">
                                        <label for="relationship">Relationship to Student(s) <span class="text-danger">*</span></label>
                                        <select name="relationship" id="relationship" class="form-control form-select" required>
                                            <option value="">-- Select --</option>
                                            @foreach($relation_ships as $rel)
                                            <option value="{{ $rel->name }}" {{ old('relationship', $data['relationship'] ?? '') == $rel->name ? 'selected' : '' }}>
                                                {{ $rel->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('relationship') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Contact --}}
                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email', $data['email'] ?? '') }}" required>
                                        @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Confirm Email <span class="text-danger">*</span></label>
                                        <input type="email" name="confirm_email" class="form-control"
                                            value="{{ old('confirm_email', $data['confirm_email'] ?? '') }}" required>
                                        @error('confirm_email') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Phone --}}
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label>Mobile Number <span class="text-danger">*</span></label>
                                        <input type="tel" id="mobile_number" name="mobile_number" class="form-control"
                                            value="{{ old('mobile_number', $data['mobile_number'] ?? '') }}" required>
                                        <span id="mobile_error" class="text-danger"></span>
                                        @error('mobile_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label>Home Telephone <span class="text-danger">*</span></label>
                                        <input type="tel" id="home_telephone" name="home_telephone" class="form-control"
                                            value="{{ old('home_telephone', $data['home_telephone'] ?? '') }}" required>
                                        <span id="home_telephone" class="text-danger"></span>
                                        @error('home_telephone') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label>Work Number <span class="text-danger">*</span></label>
                                        <input type="tel" id="work_number" name="work_number" class="form-control"
                                            value="{{ old('work_number', $data['work_number'] ?? '') }}" required>
                                        <span id="work_number" class="text-danger"></span>
                                        @error('work_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Address --}}
                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Street Address <span class="text-danger">*</span></label>
                                        <input type="text" name="address" class="form-control"
                                            value="{{ old('address', $data['address'] ?? '') }}" required>
                                        @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Apartment/Suite <span class="text-danger">*</span></label>
                                        <input type="text" name="apartment" class="form-control"
                                            value="{{ old('apartment', $data['apartment'] ?? '') }}" required>
                                        @error('apartment') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control"
                                            value="{{ old('city', $data['city'] ?? '') }}" required>
                                        @error('city') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>State/Province</label>
                                        <input type="text" name="province" class="form-control"
                                            value="{{ old('province', $data['province'] ?? '') }}">
                                        @error('province') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Zip/Postal Code <span class="text-danger">*</span></label>
                                        <input type="text" name="postal_code" class="form-control"
                                            value="{{ old('postal_code', $data['postal_code'] ?? '') }}" required>
                                        @error('postal_code') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Country <span class="text-danger">*</span></label>
                                        <!-- <select name="country" id="country" class="form-select country" required></select> -->
                                        <select name="country" id="country" class="form-select select2" required>
                                            @foreach ($allcountry as $item)
                                                <option value="{{ $item->name }}" 
                                                    {{ (old('country', $data['country']) == $item->name) ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('country') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- File Upload --}}
                                <div class="col-lg-12 mt-3">
                                    <div class="card">
                                        <div class="card-body text-dark">
                                            <h3>Documents <span class="text-danger">*</span></h3>
                                            <ol>
                                                <li>Proof of ID (Passport, Driving Licence, NID)</li>
                                                <li>Proof of Address</li>
                                            </ol>

                                            <div class="row">
                                                <div class="col-lg-6 text-center">
                                                    <label class="form-label d-block">Proof of ID <span class="text-danger">*</span></label>
                                                    <input type="file" name="file1" id="file1" class="d-none">
                                                    <label for="file1" class="btn form-control" style="background:#061E42;color:#FFF;">
                                                        Choose File
                                                    </label>
                                                    <div id="file1Name" class="mt-2 text-muted">
                                                        {{ old('file1') ?? 'No file chosen yet' }}
                                                    </div>
                                                    <span>Maximum Size: 5 MB</span>
                                                    @error('file1') <span class="text-danger">{{ $message }}</span> @enderror

                                                    @if ($data['file1'])
                                                        <span><a target="_blank" href="{{ Storage::url($data['file1']) }}">{{ $data['file1'] }}</a></span>
                                                    @endif

                                                </div>

                                                <div class="col-lg-6 text-center">
                                                    <label class="form-label d-block">Proof of Address <span class="text-danger">*</span></label>
                                                    <input type="file" name="file2" id="file2" class="d-none">
                                                    <label for="file2" class="btn form-control" style="background:#061E42;color:#FFF;">
                                                        Choose File
                                                    </label>
                                                    <div id="file2Name" class="mt-2 text-muted">
                                                        {{ old('file2') ?? 'No file chosen yet' }}
                                                    </div>
                                                     <span>Maximum Size: 5 MB</span>
                                                    @error('file2') <span class="text-danger">{{ $message }}</span> @enderror
                                                     @if ($data['file2'])
                                                        <span><a target="_blank" href="{{ Storage::url($data['file2']) }}">{{ $data['file2'] }}</a></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>{{-- row end --}}
                        </div>
                    </div>

                    {{-- ===================== SECONDARY GUARDIAN ===================== --}}
                    <div class="card p-4 mb-3" style="background-color:#0c2a58;border-radius:24px;color:#FFF;">
                        <div class="card-body">
                            <h3 class="text-center mb-5" style="color: #AE9A66;font-size: 24px;font-weight: 600;">
                                Secondary Parent / Guardian
                            </h3>

                            <div class="row">
                                {{-- Title --}}
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label>Title</label>
                                        <select name="secondary_title" class="form-control form-select" required>
                                            <option value="">-- Select --</option>
                                            @foreach(['Mr','Mrs','Miss','Ms','Mx','Dr','Prof','Rev','Sir','Dame','Lady','Lord'] as $title)
                                            <option value="{{ $title }}" {{ old('secondary_title', $data['secondary_title'] ?? '') == $title ? 'selected' : '' }}>
                                                {{ $title }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('secondary_title') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- First Name --}}
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label>First Name<span class="text-danger">*</span></label>
                                        <input type="text" name="secondary_fname" class="form-control"
                                            value="{{ old('secondary_fname', $data['secondary_fname'] ?? '') }}" required>
                                        @error('secondary_fname') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Last Name --}}
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label>Last Name<span class="text-danger">*</span></label>
                                        <input type="text" name="secondary_lname" class="form-control"
                                            value="{{ old('secondary_lname', $data['secondary_lname'] ?? '') }}" required>
                                        @error('secondary_lname') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Relationship --}}
                                <div class="col-lg-12">
                                    <div class="form-group mb-4">
                                        <label>Relationship to Student(s)<span class="text-danger">*</span></label>
                                        <select name="secondary_relationship" class="form-control form-select" required>
                                            <option value="">-- Select --</option>
                                            @foreach($relation_ships as $rel)
                                            <option value="{{ $rel->name }}" {{ old('secondary_relationship', $data['secondary_relationship'] ?? '') == $rel->name ? 'selected' : '' }}>
                                                {{ $rel->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('secondary_relationship') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Contact --}}
                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Email Address<span class="text-danger">*</span></label>
                                        <input type="email" name="secondary_email" class="form-control"
                                            value="{{ old('secondary_email', $data['secondary_email'] ?? '') }}" required>
                                        @error('secondary_email') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Confirm Email<span class="text-danger">*</span></label>
                                        <input type="email" name="secondary_confirm_email" class="form-control"
                                            value="{{ old('secondary_confirm_email', $data['secondary_confirm_email'] ?? '') }}" required>
                                        @error('secondary_confirm_email') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Phone --}}
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label>Mobile Number<span class="text-danger">*</span></label>
                                        <input type="tel" id="secondary_mobile_number" name="secondary_mobile_number" class="form-control"
                                            value="{{ old('secondary_mobile_number', $data['secondary_mobile_number'] ?? '') }}" required>
                                        <span id="secondary_mobile_number" class="text-danger"></span>
                                        @error('secondary_mobile_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label>Home Telephone<span class="text-danger">*</span></label>
                                        <input type="tel" id="secondary_home_telephone" name="secondary_home_telephone" class="form-control"
                                            value="{{ old('secondary_home_telephone', $data['secondary_home_telephone'] ?? '') }}" required>
                                        @error('secondary_home_telephone') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group mb-4">
                                        <label>Work Number<span class="text-danger">*</span></label>
                                        <input type="tel" id="secondary_work_number" name="secondary_work_number" class="form-control"
                                            value="{{ old('secondary_work_number', $data['secondary_work_number'] ?? '') }}" required>
                                        @error('secondary_work_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Address --}}
                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Street Address<span class="text-danger">*</span></label>
                                        <input type="text" name="secondary_address" class="form-control"
                                            value="{{ old('secondary_address', $data['secondary_address'] ?? '') }}" required>
                                        @error('secondary_address') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Apartment/Suite<span class="text-danger">*</span></label>
                                        <input type="text" name="secondary_apartment" class="form-control"
                                            value="{{ old('secondary_apartment', $data['secondary_apartment'] ?? '') }}" required>
                                        @error('secondary_apartment') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>City<span class="text-danger">*</span></label>
                                        <input type="text" name="secondary_city" class="form-control"
                                            value="{{ old('secondary_city', $data['secondary_city'] ?? '') }}" required>
                                        @error('secondary_city') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>State/Province</label>
                                        <input type="text" name="secondary_province" class="form-control"
                                            value="{{ old('secondary_province', $data['secondary_province'] ?? '') }}">
                                        @error('secondary_province') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Zip/Postal Code<span class="text-danger">*</span></label>
                                        <input type="text" name="secondary_postal_code" class="form-control"
                                            value="{{ old('secondary_postal_code', $data['secondary_postal_code'] ?? '') }}" required>
                                        @error('secondary_postal_code') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>


                                <div class="col-lg-6">
                                    <div class="form-group mb-4">
                                        <label>Secondary Country<span class="text-danger">*</span></label>
                                        <!-- <select name="secondary_country" id="secondary_country" class="form-select country" required></select> -->
                                        <select name="secondary_country" id="secondary_country" class="form-select select2" required>
                                           @foreach ($allcountry as $item)
                                                <option value="{{ $item->name }}" 
                                                    {{ (old('secondary_country', $data['secondary_country']) == $item->name) ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('secondary_country') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- File Upload --}}
                                <div class="col-lg-12 mt-3">
                                    <div class="card">
                                        <div class="card-body text-dark">
                                            <h3>Documents<span class="text-danger">*</span></h3>
                                            <ol>
                                                <li>Proof of ID (Passport, Driving Licence, NID)</li>
                                                <li>Proof of Address</li>
                                            </ol>

                                            <div class="row">
                                                <div class="col-lg-6 text-center">
                                                    <label class="form-label d-block">Proof of ID <span class="text-danger">*</span></label>
                                                    <input type="file" name="file3" id="file3" class="d-none">
                                                    <label for="file3" class="btn form-control" style="background:#061E42;color:#FFF;">
                                                        Choose File
                                                    </label>
                                                    <div id="file3Name" class="mt-2 text-muted">
                                                        {{ old('file3') ?? 'No file chosen yet' }}
                                                    </div>
                                                     <span>Maximum Size: 5 MB</span>
                                                    @error('file3') <span class="text-danger">{{ $message }}</span> @enderror
                                                     @if ($data['file3'])
                                                        <span><a target="_blank" href="{{ Storage::url($data['file3']) }}">{{ $data['file3'] }}</a></span>
                                                    @endif
                                                </div>

                                                <div class="col-lg-6 text-center">
                                                    <label class="form-label d-block">Proof of Address <span class="text-danger">*</span></label>
                                                    <input type="file" name="file4" id="file4" class="d-none">
                                                    <label for="file4" class="btn form-control" style="background:#061E42;color:#FFF;">
                                                        Choose File
                                                    </label>
                                                    <div id="file4Name" class="mt-2 text-muted">
                                                        {{ old('file4') ?? 'No file chosen yet' }}
                                                    </div>
                                                     <span>Maximum Size: 5 MB</span>
                                                    @error('file4') <span class="text-danger">{{ $message }}</span> @enderror
                                                     @if ($data['file4'])
                                                        <span><a target="_blank" href="{{ Storage::url($data['file4']) }}">{{ $data['file4'] }}</a></span>
                                                    @endif
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- ===================== END SECONDARY GUARDIAN ===================== --}}

                </div>
            </div>

            {{-- Submit + Back --}}
            <div class="row mt-3">
                <div class="col-lg-10 m-auto">
                    <button type="submit" class="btn custom-btn w-100">Update</button>
                    <div class="text-center mt-4">
                        <a href="{{ route('form.step', 6) }}" class="text-light text-decoration-none">
                            <i class="fa fa-arrow-left"></i> Go Back
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

@endsection

@section('script')
<script>
    // File preview
    ['file1', 'file2', 'file3', 'file4'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', function(e) {
            let fileName = e.target.files.length ? e.target.files[0].name : "No file chosen yet";
            document.getElementById(id + 'Name').textContent = fileName;
        });
    });
</script>



<!-- Phone Number Validation -->
<script>
$(document).ready(function () {
    // সব phone field এর লিস্ট
    const phoneFields = [
        { selector: "#mobile_number", dbValue: "{{ $data['mobile_number'] ?? '' }}" },
        { selector: "#secondary_mobile_number", dbValue: "{{ $data['secondary_mobile_number'] ?? '' }}" },
        { selector: "#home_telephone", dbValue: "{{ $data['home_telephone'] ?? '' }}" },
        { selector: "#work_number", dbValue: "{{ $data['work_number'] ?? '' }}" },
        { selector: "#secondary_home_telephone", dbValue: "{{ $data['secondary_home_telephone'] ?? '' }}" },
        { selector: "#secondary_work_number", dbValue: "{{ $data['secondary_work_number'] ?? '' }}" },
    ];

    const itiObjects = {};

    phoneFields.forEach(function (field) {
        const input = document.querySelector(field.selector);
        if (!input) return;

        // container styling
        $(input).wrap("<div class='position-relative'></div>");
        $(input).after("<small class='validation-msg position-absolute mt-1' style='font-size:13px;'></small>");
        const msg = $(input).siblings(".validation-msg");

        // initialize intl-tel-input
        const iti = window.intlTelInput(input, {
            separateDialCode: true,
            preferredCountries: ["gb", "bd", "us"],
            initialCountry: "gb", // 🔹 Default UK
            utilsScript:
                "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });

        itiObjects[field.selector] = iti;

        // 🔹 যদি database থেকে number আসে, সেট করো এবং country auto detect হোক
        if (field.dbValue) {
            iti.setNumber(field.dbValue); // e.g. +8801712345678
        } else {
            // 🔹 Database এ কিছু না থাকলে default UK রাখো
            iti.setCountry("gb");
        }

        // instant validation function
        function validateNumber() {
            const val = $(input).val().trim();
            if (val === "") {
                msg.text("").removeClass("text-danger text-success");
                return;
            }

            if (!iti.isValidNumber()) {
                msg.text("❌ Invalid phone number").removeClass("text-success").addClass("text-danger");
            } else {
                msg.text("✅ Valid phone number").removeClass("text-danger").addClass("text-success");
            }
        }

        // live validation (on typing or country change)
        $(input).on("input blur change countrychange", function () {
            validateNumber();
        });

        // page load এও validation check (যদি DB value থাকে)
        validateNumber();
    });

    // form submit এ সব number check
    $("form").on("submit", function (e) {
        let hasError = false;

        phoneFields.forEach(function (field) {
            const input = document.querySelector(field.selector);
            if (!input) return;

            const iti = itiObjects[field.selector];
            const msg = $(input).siblings(".validation-msg");
            const val = $(input).val().trim();

            if (val === "") return; // empty হলে skip

            if (!iti.isValidNumber()) {
                msg.text("❌ Invalid phone number").removeClass("text-success").addClass("text-danger");
                input.focus();
                hasError = true;
            } else {
                msg.text("✅ Valid phone number").removeClass("text-danger").addClass("text-success");
                const fullNumber = iti.getNumber();
                $(input).val(fullNumber); // DB তে যাবে full international format (+880...)
            }
        });

        if (hasError) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<script>
  $(document).ready(function() {
    // Initialize Select2 on the select element with id "country"
    $('.select2').select2();
  });
</script>
<!-- 
<script>
    $(document).ready(function() {
        // DB / Session থেকে আসা value
        const selectedCountry = @json(session('country') ?? $data['country'] ?? '');
        const selectedSecondary = @json(session('secondary_country') ?? $data['secondary_country'] ?? '');


        function initCountrySelect(selector, selectedValue) {
            // Country plugin initialize
            $(selector).countrySelector({
                valueType: 'full'
            });

            // Polling দিয়ে wait করি plugin option load complete হওয়ার জন্য
            const interval = setInterval(() => {
                const optionsCount = $(selector).find('option').length;

                if (optionsCount > 0) {
                    // Options load complete, clear interval
                    clearInterval(interval);

                    // Select2 initialize
                    $(selector).select2({
                        placeholder: "Select a country",
                        allowClear: true,
                        width: '100%'
                    });

                    // DB value select করা
                    if (selectedValue) {
                        $(selector).find('option').each(function() {
                            const optionText = $(this).text().trim().toLowerCase();
                            const dbValue = selectedValue.trim().toLowerCase();

                            if (optionText === dbValue) {
                                $(this).prop('selected', true);
                                $(selector).trigger('change'); // Select2 refresh
                                return false; // break
                            }
                        });
                    }
                }
            }, 100); // 100ms interval
        }

        // দুইটা select box initialize
        initCountrySelect('#country', selectedCountry);
        initCountrySelect('#secondary_country', selectedSecondary);

        // Form submit এর সময় শুধু full name save করবো
        $('form').on('submit', function() {
            const primaryText = $('#country option:selected').text().trim();
            const secondaryText = $('#secondary_country option:selected').text().trim();

            $('#country').html(`<option selected value="${primaryText}">${primaryText}</option>`);
            $('#secondary_country').html(`<option selected value="${secondaryText}">${secondaryText}</option>`);
        });
    });
</script> -->


@endsection