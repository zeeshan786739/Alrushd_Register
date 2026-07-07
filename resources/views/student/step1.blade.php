@extends('student.app')

@section('title','Select School')

@section('student')

<section>
    <div class="container py-5">

        <div class="row justify-content-center align-items-start g-4">
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-lg-10 m-auto">
                        <!-- Progress Header -->
                        <div class="progress-container mb-4">
                            <h5 class="mb-0 text-light title" id="progressTitle">Estimated time remaining: 10 minutes</h5>
                            <div class="progress mt-2">
                                <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <small id="progressText" class="text-light">0%</small>
                        </div>

                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-lg-12 step-one m-auto">
                        <h3 class="text-center">Register at <br> Al-Rushd International School</h3>
                        <p class="text-center text-light" style="font-size: 24px;font-weight:500;">Select your school</p>
                    </div>
                </div>

                @include('errors.validation')
                <form action="{{ route('form.step.post', 1) }}" method="POST" id="schoolForm">
                    @csrf
                    <!-- Hidden input to store selected school -->
                    <input type="hidden" name="selected_school" id="selected_school" value="{{ old('selected_school', $data['selected_school'] ?? '') }}">

                    <div class="row d-flex justify-content-center">
                        @foreach($schools as $item)
                        <div class="col-lg-12">
                            <div class="card p-4 mb-3 schoolbox {{ (old('selected_school', $data['selected_school'] ?? '') == $item->name) ? 'active' : '' }}"
                                data-value="{{ $item->name }}"
                                onclick="selectSchool(this)"
                                style="background-color:#0c2a58;border-radius:24px;color:#FFF;cursor:pointer;">
                                <div class="card-body text-center">
                                    <img src="{{ Storage::url($item->image) }}" width="48" height="24" alt="" class="img-fluid">
                                    <h3 class="pt-3" style="font-size: 28px;font-weight: 600;">{{ $item->name }}</h3>

                                    <label class="custom-check mb-4">
                                        <input type="checkbox" class="school_check">
                                        <span class="custom-checkmark"></span>
                                    </label>


                                    <p class="mb-0" style="color: #AE9A66;font-size:20px;font-weight:400;">Time zones:</p>
                                    <p style="font-size:20px;font-weight:500;line-height: 28px;">{{ $item->timezone }}</p>
                                    <p style="font-weight: 400;font-size: 16px;line-height: 24px;">
                                        {{ $item->description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Error Message -->
                    <div id="schoolError" class="row mt-2 d-none">
                        <div class="col-lg-12 m-auto">
                            <div class="alert alert-danger">Please select a school before continuing.</div>
                        </div>
                    </div>

                    <!-- Submit button -->
                    <div class="row mt-3">
                        <div class="col-lg-12 m-auto">
                            <button type="submit" class="btn custom-btn w-100">Start Registration</button>
                        </div>
                    </div>
                </form>


            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm rounded-4 custom-school-card p-4" style="background-color:#0a234a; color:#fff; border-radius:24px;">
                    <div class="card-body">
                        {!! $terms->form_description !!}

                        <div class="d-flex justify-content-center align-items-center gap-4 p-2 mt-5">
                            @if ($terms->image_one)
                            <div class="text-center">
                                <img src="{{ Storage::url($terms->image_one) }}" width="60" alt="SSL Icon">
                                <p class="small mt-2 mb-0" style="color:#AE9A66; line-height:1.4;">Privacy<br>Guaranteed</p>
                            </div>
                             @endif
                             @if ($terms->image_two)
                            <div class="text-center">
                                <img src="{{ Storage::url($terms->image_two) }}" width="60" alt="Lock Icon">
                                <p class="small mt-2 mb-0" style="color:#AE9A66; line-height:1.4;">100% Secure<br>Information</p>
                            </div>
                               @endif
                        </div>

                    </div>
                    <!-- <div class="card-body">

                        <h5 class="card-title fw-bold mb-2">Lady Evelyn Independent School</h5>

                        <p class="text-warning mb-3 stars" style="font-size:18px;">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </p>

                        <p class="school-description mb-4" style="line-height:1.7; font-size:16px;">
                            <i class="fas fa-check text-primary me-2"></i>
                            Leading Online American and British Independent School.
                            Choose either the American or British curriculum.
                        </p>

                        
                        <h6 class="fw-bold mt-4 mb-2 section-title" style="color:#AE9A66;">American Section:</h6>
                        <ul class="list-unstyled section-list" style="line-height:1.8; font-size:16px; margin-left:22px;">
                            <li><i class="fas fa-check text-primary me-2"></i> Elementary, Middle and High Schools</li>
                            <li><i class="fas fa-check text-danger me-2"></i> <span class="text-danger">Earn credits and obtain a High School Diploma</span></li>
                            <li><i class="fas fa-check text-primary me-2"></i> Accreditation by Cognia</li>
                        </ul>

                        
                        <h6 class="fw-bold mt-4 mb-2 section-title" style="color:#AE9A66;">British Section:</h6>
                        <ul class="list-unstyled section-list" style="line-height:1.8; font-size:16px; margin-left:22px;">
                            <li><i class="fas fa-check text-primary me-2"></i> Primary, Secondary and Sixth form levels</li>
                            <li><i class="fas fa-check text-danger me-2"></i> <span class="text-danger">Key Stages 1, 2, 3, 4 and 5</span></li>
                            <li><i class="fas fa-check text-primary me-2"></i> International GCSE and A Level</li>
                        </ul>

                        
                        <h6 class="fw-bold mt-4 mb-2 section-title" style="color:#AE9A66;">High Standards</h6>
                        <ul class="list-unstyled section-list" style="line-height:1.8; font-size:16px; margin-left:22px;">
                            <li><i class="fas fa-check text-primary me-2"></i> Live lessons conducted online</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Students can re-watch lesson recordings to revise and catch up</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Islamic Ethos and Values</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Access to top colleges, universities, and companies</li>
                        </ul>

                        
                        <h5 class="fw-bold text-light mt-5 mb-3">Admissions Process</h5>
                        <ul class="list-unstyled admission-list" style="line-height:1.8; font-size:16px; margin-left:22px;">
                            <li><i class="fas fa-check text-primary me-2"></i> Fill in the application form with a $325 (American) or £247 (British) non-refundable <span style="color:#AE9A66;">Application Fee</span>.</li>
                            <li><i class="fas fa-check text-primary me-2"></i> We will contact you with subject selection details.</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Your payment method will be charged for the <span style="color:#AE9A66;">Academic Deposit</span> after acceptance.</li>
                            <li><i class="fas fa-check text-primary me-2"></i> We will send parent and student portal details.</li>
                            <li><i class="fas fa-check text-primary me-2"></i> You will receive booklist, timetable, and start date.</li>
                            <li><i class="fas fa-check text-primary me-2"></i> The student starts learning!</li>
                        </ul>

                        
                        <div class="d-flex justify-content-center align-items-center gap-4 p-2 mt-5">
                            <div class="text-center">
                                <img src="{{ asset('frontend/ssl.png') }}" width="60" alt="SSL Icon">
                                <p class="small mt-2 mb-0" style="color:#AE9A66; line-height:1.4;">Privacy<br>Guaranteed</p>
                            </div>
                            <div class="text-center">
                                <img src="{{ asset('frontend/tala.png') }}" width="60" alt="Lock Icon">
                                <p class="small mt-2 mb-0" style="color:#AE9A66; line-height:1.4;">100% Secure<br>Information</p>
                            </div>
                        </div>

                    </div> -->
                </div>
            </div>
        </div>



    </div>
</section>

@endsection


@section('script')
<script>
    const currentStepPercent = 14; // এই step-এর progress শতাংশ

    // ✅ Progress bar update function
    function updateProgress(isSelected) {
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const percent = isSelected ? currentStepPercent : 0;

        progressBar.style.width = percent + '%';
        progressText.innerText = percent + '%';
    }

    // ✅ School select function
    function selectSchool(el) {
        // সব box থেকে active class সরানো এবং checkbox uncheck করা
        document.querySelectorAll('.schoolbox').forEach(box => {
            box.classList.remove('active');
            const checkbox = box.querySelector('.school_check');
            if (checkbox) checkbox.checked = false;
        });

        // ক্লিক করা box-এ active class দেওয়া
        el.classList.add('active');

        // ক্লিক করা box-এর checkbox checked করা
        const selectedCheckbox = el.querySelector('.school_check');
        if (selectedCheckbox) selectedCheckbox.checked = true;

        // hidden input-এ value সেট করা
        document.getElementById('selected_school').value = el.getAttribute('data-value');

        // error message hide করা
        document.getElementById('schoolError').classList.add('d-none');

        // progress আপডেট করা
        updateProgress(true);
    }

    // ✅ Form submit validation
    document.getElementById('schoolForm').addEventListener('submit', function(e) {
        const selectedValue = document.getElementById('selected_school').value;
        if (!selectedValue) {
            e.preventDefault();
            document.getElementById('schoolError').classList.remove('d-none');
        }
    });

    // ✅ পেজ লোড হলে previous selection restore করা
    document.addEventListener("DOMContentLoaded", function() {
        const preSelected = document.getElementById('selected_school').value;

        if (preSelected) {
            // value থাকলে active class restore + checkbox checked + progress 14%
            document.querySelectorAll('.schoolbox').forEach(box => {
                const checkbox = box.querySelector('.school_check');
                if (box.getAttribute('data-value') === preSelected) {
                    box.classList.add('active');
                    if (checkbox) checkbox.checked = true;
                } else {
                    if (checkbox) checkbox.checked = false;
                }
            });
            updateProgress(true);
        } else {
            // value না থাকলে 0%
            updateProgress(false);
        }
    });
</script>
@endsection
