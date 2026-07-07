@extends('student.app')

@section('title','Add Child')

@section('student')
<section>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-5 m-auto">
                <div class="progress-container mb-4">
                    <h5 class="mb-0 text-light title">Estimated time remaining: 8 minutes</h5>
                    <div class="progress mt-2">
                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 28%;"></div>
                    </div>
                    <small id="progressText" class="text-light">28%</small>
                </div>
            </div>
        </div>


        @include('errors.validation')
        <form action="{{ route('form.step.post', 3) }}" method="POST" enctype="multipart/form-data" id="myForm">
            @csrf
            <div class="row d-flex justify-content-center">
                <div class="col-lg-10">
                    <div id="studentsContainer">



                        @forelse($data['students'] as $index => $student)
                        <div class="card p-4 mb-3 student-card position-relative" style="background-color:#0c2a58;border-radius:24px;color:#FFF;">
                            @if($index > 0)
                            <span class="btn btn-danger btn-sm float-end remove-student" style="position:absolute;top:10px;right:10px;">Remove</span>
                            @endif
                            <div class="card-body">
                                <h3 class="text-center mb-5" style="color: #AE9A66;font-size: 24px;font-weight: 600;">
                                    Tell us about your child {{ $index + 1 }}
                                </h3>
                                <div class="row">
                                    {{-- First Name --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>First Name<span class="text-danger">*</span></label>
                                            <input type="text" name="fname[]" class="form-control" value="{{ $student['fname'] ?? '' }}" placeholder="First name here" required>
                                        </div>
                                    </div>
                                    {{-- Last Name --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Last Name<span class="text-danger">*</span></label>
                                            <input type="text" name="lname[]" class="form-control" value="{{ $student['lname'] ?? '' }}" placeholder="Last name here" required>
                                        </div>
                                    </div>
                                    {{-- DOB --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>DOB<span class="text-danger">*</span></label>
                                            <input type="date" name="dob[]" class="form-control" value="{{ $student['dob'] ?? '' }}" required>
                                        </div>
                                    </div>
                                    {{-- Gender --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Gender<span class="text-danger">*</span></label>
                                            <select name="gender[]" class="form-control form-select" required>
                                                <option value="">-- Select --</option>
                                                @foreach ($genders as $item)
                                                <option value="{{ $item->name }}" {{ ($student['gender'] ?? '') == $item->name ? 'selected' : '' }}>{{ $item->name }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    {{-- Nationality --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Nationality<span class="text-danger">*</span></label>
                                            <select name="nationality[]" class="form-control form-select nationality select2" required>
                                                @foreach($nationality as $nation)
                                                <option value="{{ $nation->name }}" {{ ($student['nationality'] ?? '') == $nation->name ? 'selected' : '' }}>{{ $nation->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Desired Start Date --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Desired Start Date<span class="text-danger">*</span></label>
                                            <select name="start_date[]" class="form-control form-select" required>
                                                <option value="">-- Select --</option>
                                                @foreach($admission_date as $date)
                                                <option value="{{ $date->date }}" {{ ($student['start_date'] ?? '') == $date->date ? 'selected' : '' }}>{{ $date->date }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>


                                    {{-- Year --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Year<span class="text-danger">*</span></label>
                                            <select name="year_id[]" class="form-control form-select year-select"
                                                data-selected="{{ $student['year_id'] ?? '' }}" required>
                                                <option value="">-- Select --</option>
                                                @foreach($years as $y)
                                                <option value="{{ $y->id }}" {{ ($student['year_id'] ?? '') == $y->id ? 'selected' : '' }}>
                                                    {{ $y->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Package --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Package<span class="text-danger">*</span></label>
                                            <select name="package_id[]" class="form-control form-select package-select"
                                                data-selected="{{ $student['package_id'] ?? '' }}" required>
                                                <option value="">-- Select --</option>
                                                @if(!empty($student['package_id']))
                                                <option value="{{ $student['package_id'] }}" selected>
                                                    {{ $student['package_name'] ?? 'Selected Package' }}
                                                </option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>


                                    {{-- Subjects Divs --}}
                                    <div class="col-lg-12 coreSubjectsDiv" style="display:none;">
                                        <div class="form-group mb-4">
                                            <label>Core Subjects</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="core_subject[]" class="core-subjects-input" value="{{ $student['core_subject'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12 islamicSubjectsDiv" style="display:none;">
                                        <div class="form-group mb-4">
                                            <label>Islamic Subjects</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="islamic_subject[]" class="islamic-subjects-input" value="{{ $student['islamic_subject'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12 additionalSubjectsDiv" style="display:none;">
                                        <div class="form-group mb-4">
                                            <label>Additional Subjects</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="additional_subject[]" class="additional-subjects-input" value="{{ $student['additional_subject'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12 languageDiv" style="display:none;">
                                        <div class="form-group mb-4">
                                            <label>Languages</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="language[]" class="language-subjects-input" value="{{ $student['language'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12 hifdhDiv" style="display:none;">
                                        <div class="form-group mb-4">
                                            <label>Hifdh Subjects</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="hifdh_subject[]" class="hifdh-subjects-input" value="{{ $student['hifdh_subject'] ?? '' }}">
                                        </div>
                                        <label class="custom-check">
                                            <input type="checkbox" name="hifdh_option[]" {{ ($student['hifdh'] ?? 0) ? 'checked' : '' }}>
                                            <span class="custom-checkmark"></span>
                                            <span class="text-light" id="hifdhText">{{ $student['hifdh_text'] ?? '' }}</span>
                                        </label>
                                    </div>

                                    {{-- Documents --}}
                                    <div class="col-lg-12 mt-3">
                                        <div class="card">
                                            <div class="card-body text-dark">
                                                <h3>Documents<span class="text-danger">*</span></h3>
                                                <ol>
                                                    <li>Proof of ID (Passport, Birth Certificate, National ID)</li>
                                                    <li>Previous Academic Years Report</li>
                                                </ol>
                                                <div class="row">
                                                    <div class="col-lg-6 text-center">
                                                        <label class="form-label d-block">Proof Of ID <span class="text-danger">*</span></label>
                                                        <input type="file" class="d-none parent-file1" name="student_file1[]" id="student_file1_{{ $index ?? 0 }}" {{ empty($student['student_file1']) ? 'required' : '' }}>
                                                        <label class="btn form-control" style="background: #061E42;color:#FFF;" for="student_file1_{{ $index ?? 0 }}">
                                                            Choose File <i class="fas fa-plus"></i>
                                                        </label>
                                                        <div class="fileName mt-2 text-muted">No file chosen yet</div>
                                                        <span>Maximum Size: 5 MB</span>
                                                        @if (!empty($student['student_file1']))
                                                        <span><a target="_blank" href="{{ Storage::url($student['student_file1']) }}">{{ $student['student_file1'] }}</a></span>
                                                        @endif
                                                    </div>

                                                    <div class="col-lg-6 text-center">
                                                        <label class="form-label d-block">Previous Academic Years Report <span class="text-danger">*</span></label>
                                                        <input type="file" class="d-none parent-file2" name="student_file2[]" id="student_file2_{{ $index ?? 0 }}" {{ empty($student['student_file2']) ? 'required' : '' }}>
                                                        <label class="btn form-control" style="background: #061E42;color:#FFF;" for="student_file2_{{ $index ?? 0 }}">
                                                            Choose File <i class="fas fa-plus"></i>
                                                        </label>
                                                        <div class="fileName mt-2 text-muted">No file chosen yet</div>
                                                        <span>Maximum Size: 5 MB</span>
                                                        @if (!empty($student['student_file2']))
                                                        <span><a target="_blank" href="{{ Storage::url($student['student_file2']) }}">{{ $student['student_file2'] }}</a></span>
                                                        @endif
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div> <!-- row -->
                            </div>
                        </div>
                        @empty

                        {{-- If no session data --}}
                        <div class="card p-4 mb-3 student-card position-relative" style="background-color:#0c2a58;border-radius:24px;color:#FFF;">
                            <div class="card-body">
                                <h3 class="text-center mb-5" style="color: #AE9A66;font-size: 24px;font-weight: 600;">Tell us about your first child</h3>
                                <div class="row">
                                    {{-- First Name --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>First Name<span class="text-danger">*</span></label>
                                            <input type="text" name="fname[]" class="form-control" placeholder="First name here" required>
                                        </div>
                                    </div>
                                    {{-- Last Name --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Last Name<span class="text-danger">*</span></label>
                                            <input type="text" name="lname[]" class="form-control" placeholder="Last name here" required>
                                        </div>
                                    </div>
                                    {{-- DOB --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>DOB<span class="text-danger">*</span></label>
                                            <input type="date" name="dob[]" class="form-control" required>
                                        </div>
                                    </div>
                                    {{-- Gender --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Gender<span class="text-danger">*</span></label>
                                            <select name="gender[]" class="form-control form-select" required>
                                                <option value="">-- Select --</option>
                                                @foreach ($genders as $item)
                                                <option value="{{$item->name}}">{{$item->name}}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    {{-- Nationality --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Nationality<span class="text-danger">*</span></label>
                                            <select name="nationality[]" class="form-control form-select nationality select2" required>
                                                @foreach($nationality as $key => $nation)
                                                <option value="{{ $nation->name }}"
                                                    {{ $key == 0 && empty($student['nationality']) ? 'selected' : '' }}
                                                    {{ ($student['nationality'] ?? '') == $nation->name ? 'selected' : '' }}>
                                                    {{ $nation->name }}
                                                </option>
                                                @endforeach
                                            </select>

                                        </div>
                                    </div>
                                    {{-- Desired Start Date --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Desired Start Date<span class="text-danger">*</span></label>
                                            <select name="start_date[]" class="form-control form-select" required>
                                                <option value="">-- Select --</option>
                                                @foreach($admission_date as $date)
                                                <option value="{{ $date->date }}">{{ $date->date }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Year --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Year<span class="text-danger">*</span></label>
                                            <select name="year_id[]" class="form-control form-select year-select" required>
                                                <option value="">-- Select --</option>
                                                @foreach($years as $y)
                                                <option value="{{ $y->id }}" {{ ($student['year_id'] ?? '') == $y->id ? 'selected' : '' }}>
                                                    {{ $y->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Package --}}
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4">
                                            <label>Package<span class="text-danger">*</span></label>
                                            <select name="package_id[]" class="form-control form-select package-select"
                                                data-selected="{{ $student['package_id'] ?? '' }}" required>
                                                <option value="">-- Select --</option>
                                                @if(!empty($student['package_id']))
                                                <option value="{{ $student['package_id'] }}" selected>
                                                    {{ $student['package_name'] ?? 'Selected Package' }}
                                                </option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Subjects Divs --}}
                                    <div class="col-lg-12 coreSubjectsDiv" style="display:none;">
                                        <div class="form-group mb-2">
                                            <label>Core Subjects</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="core_subject[]" class="core-subjects-input">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 islamicSubjectsDiv" style="display:none;">
                                        <div class="form-group mb-2">
                                            <label>Islamic Subjects</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="islamic_subject[]" class="islamic-subjects-input">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 additionalSubjectsDiv" style="display:none;">
                                        <div class="form-group mb-2">
                                            <label>Additional Subjects</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="additional_subject[]" class="additional-subjects-input">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 languageDiv" style="display:none;">
                                        <div class="form-group mb-2">
                                            <label>Languages</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="language[]" class="language-subjects-input">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 hifdhDiv" style="display:none;">
                                        <div class="form-group mb-2">
                                            <label>Hifdh Subjects</label>
                                            <div class="subjectsContainer"></div>
                                            <input type="hidden" name="hifdh_subject[]" class="hifdh-subjects-input">
                                        </div>
                                        <label class="custom-check">
                                            <input type="checkbox" name="hifdh_option[]">
                                            <span class="custom-checkmark"></span>
                                            <span class="text-light" id="hifdhText"></span>
                                        </label>
                                    </div>
                                    {{-- Documents --}}
                                    <div class="col-lg-12 mt-3">
                                        <div class="card">
                                            <div class="card-body text-dark">
                                                <h3>Documents<span class="text-danger">*</span></h3>
                                                <ol>
                                                    <li>Proof of ID (Passport, Birth Certificate, National ID)</li>
                                                    <li>Previous Academic Years Report</li>
                                                </ol>
                                                <div class="row">

                                                    <div class="col-lg-6 text-center">
                                                        <label class="form-label d-block">Proof Of ID <span class="text-danger">*</span></label>
                                                        <input type="file" class="d-none parent-file1" name="student_file1[]" id="student_file1_{{ $index ?? 0 }}" required>
                                                        <label class="btn form-control" style="background: #061E42;color:#FFF;" for="student_file1_{{ $index ?? 0 }}">
                                                            Choose File <i class="fas fa-plus"></i>
                                                        </label>
                                                        <div class="fileName mt-2 text-muted">No file chosen yet</div>
                                                        <span>Maximum Size: 5 MB</span>

                                                    </div>

                                                    <div class="col-lg-6 text-center">
                                                        <label class="form-label d-block">Previous Academic Years Report <span class="text-danger">*</span></label>
                                                        <input type="file" class="d-none parent-file2" name="student_file2[]" id="student_file2_{{ $index ?? 0 }}" required>
                                                        <label class="btn form-control" style="background: #061E42;color:#FFF;" for="student_file2_{{ $index ?? 0 }}">
                                                            Choose File <i class="fas fa-plus"></i>
                                                        </label>
                                                        <div class="fileName mt-2 text-muted">No file chosen yet</div>
                                                        <span>Maximum Size: 5 MB</span>

                                                    </div>





                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div> <!-- row -->
                            </div>
                        </div>
                        @endforelse


                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-10 m-auto">
                    <p id="addMore" class="btn w-100 py-3" style="background: #183E77;color:#FFF;cursor:pointer;">Add More Students <i class="fa fa-plus ms-3"></i></p>
                    <button type="submit" class="btn custom-btn w-100">Continue</button>
                    <div class="text-center mt-4">
                        <a href="{{ route('form.step', 2) }}" class="text-light text-decoration-none"><i class="fa fa-arrow-left"></i> Go Back</a>
                    </div>
                </div>
            </div>

        </form>
    </div>
</section>
@endsection


@section('script')
<script>
$(document).ready(function() {

    // ------------------------
    // INITIAL SELECT2 SETUP
    // ------------------------
    function initSelect2(container) {
        container.find('.nationality, .nation').each(function() {
            if (!$(this).hasClass("select2-hidden-accessible")) {
                $(this).select2({
                    width: '100%'
                });
            }
        });
    }

    // Run select2 on initial page load
    initSelect2($(document));

    // ------------------------
    // AJAX CACHE
    // ------------------------
    const cache = {
        packages: {},
        courseDetails: {}
    };

    // ------------------------
    // INIT CARD FUNCTION
    // ------------------------
    async function initCard(card, fromDB = false) {
        let yearSelect = card.find('.year-select');
        let packageSelect = card.find('.package-select');

        let savedYear = fromDB ? yearSelect.data('selected') : '';
        let savedPackage = fromDB ? packageSelect.data('selected') : '';

        // LOAD PACKAGES
        async function loadPackages(year_id) {
            packageSelect.html('<option value="">-- Select --</option>');
            card.find('.coreSubjectsDiv,.islamicSubjectsDiv,.additionalSubjectsDiv,.languageDiv,.hifdhDiv')
                .hide().find('.subjectsContainer').html('');
            card.find('#hifdhText').text('');

            if (!year_id) return;

            let data = cache.packages[year_id] ? cache.packages[year_id] :
                await $.get(`/get-packages/${year_id}`);

            cache.packages[year_id] = data;

            data.forEach(p => {
                let selected = (p.id == savedPackage) ? 'selected' : '';
                packageSelect.append(`<option value="${p.id}" ${selected}>${p.name}</option>`);
            });

            if (savedPackage) await loadCourseDetails(year_id, savedPackage);
        }

        // LOAD SUBJECTS
        async function loadCourseDetails(year_id, package_id) {
            card.find('.coreSubjectsDiv,.islamicSubjectsDiv,.additionalSubjectsDiv,.languageDiv,.hifdhDiv')
                .hide().find('.subjectsContainer').html('');
            card.find('#hifdhText').text('');

            if (!year_id || !package_id) return;

            let key = `${year_id}-${package_id}`;
            let data = cache.courseDetails[key] ? cache.courseDetails[key] :
                await $.get('/get-course-details', {
                    year_id,
                    package_id
                });

            cache.courseDetails[key] = data;

            let savedCore = fromDB ? (card.find('.core-subjects-input').val() || '').split(',') : [];
            let savedIslamic = fromDB ? (card.find('.islamic-subjects-input').val() || '').split(',') : [];
            let savedAdditional = fromDB ? (card.find('.additional-subjects-input').val() || '').split(',') : [];
            let savedLang = fromDB ? (card.find('.language-subjects-input').val() || '').split(',') : [];
            let savedHifdh = fromDB ? (card.find('.hifdh-subjects-input').val() || '').split(',') : [];

            function renderSubjects(div, subjects, savedArr) {
                if (Array.isArray(subjects) && subjects.length) {
                    subjects.forEach(sub => {
                        let active = savedArr.includes(sub) ? 'active' : '';
                        card.find(`${div} .subjectsContainer`).append(
                            `<span class="badge mb-1 me-2 fs-6 subject-badge ${active}" style="background:#AE9A66;cursor:pointer;" data-value="${sub}">${sub}</span>`
                        );
                    });
                    card.find(div).show();
                }
            }

            renderSubjects('.coreSubjectsDiv', data.core_subject, savedCore);
            renderSubjects('.islamicSubjectsDiv', data.islamic_subject, savedIslamic);
            renderSubjects('.additionalSubjectsDiv', data.additional_subject, savedAdditional);
            renderSubjects('.languageDiv', data.language, savedLang);

            if (data.hifdh == 1) {
                let active = savedHifdh.includes('Hifdh') ? 'active' : '';
                card.find('.hifdhDiv .subjectsContainer').html(
                    `<span class="badge mb-1 me-2 fs-6 subject-badge ${active}" style="background:#AE9A66;cursor:pointer;" data-value="Hifdh">Hifdh</span>`
                );
                card.find('#hifdhText').text(data.hifdh_text || '');
                card.find('.hifdhDiv').show();
            }
        }

        // ------------------------
        // EVENTS
        // ------------------------
        yearSelect.off('change').on('change', function() {
            savedPackage = '';
            loadPackages($(this).val());
        });

        packageSelect.off('change').on('change', function() {
            loadCourseDetails(yearSelect.val(), $(this).val());
        });

        // SUBJECT BADGE SELECTOR
        card.off('click', '.subject-badge').on('click', '.subject-badge', function() {
            $(this).toggleClass('active');

            const mappings = [{
                    div: '.coreSubjectsDiv',
                    input: '.core-subjects-input'
                },
                {
                    div: '.islamicSubjectsDiv',
                    input: '.islamic-subjects-input'
                },
                {
                    div: '.additionalSubjectsDiv',
                    input: '.additional-subjects-input'
                },
                {
                    div: '.languageDiv',
                    input: '.language-subjects-input'
                },
                {
                    div: '.hifdhDiv',
                    input: '.hifdh-subjects-input'
                },
            ];

            mappings.forEach(m => {
                let values = [];
                card.find(`${m.div} .subject-badge.active`).each(function() {
                    values.push($(this).data('value'));
                });
                card.find(m.input).val(values.join(','));
            });
        });

        // FILE PREVIEW
        card.find('input[type="file"]').off('change').on('change', function(e) {
            let fileName = e.target.files.length ? e.target.files[0].name : "No file chosen yet";
            $(this).closest('.col-lg-6').find('.fileName').text(fileName);
        });

        // AUTO-LOAD SAVED DATA
        if (fromDB && savedYear) {
            await loadPackages(savedYear);
        }
    }

    // ------------------------
    // Init all existing cards
    // ------------------------
    $('#studentsContainer .student-card').each(function() {
        initCard($(this), true);
    });

    // ------------------------
    // ADD NEW STUDENT
    // ------------------------
    let studentCount = $('#studentsContainer .student-card').length;

    $('#addMore').click(function() {
        let firstCard = $('#studentsContainer .student-card:first');

        // Destroy Select2 on first card BEFORE cloning
        firstCard.find('.nationality, .nation').each(function() {
            if ($(this).hasClass("select2-hidden-accessible")) {
                $(this).select2('destroy');
            }
        });

        let newCard = firstCard.clone();

        studentCount++;

        // Reset inputs
        newCard.find('input[type="text"],input[type="date"],input[type="hidden"]').val('');
        newCard.find('select').removeAttr('data-selected');
        newCard.find('.subjectsContainer').html('');
        newCard.find('.coreSubjectsDiv,.islamicSubjectsDiv,.additionalSubjectsDiv,.languageDiv,.hifdhDiv').hide();
        newCard.find('.fileName').text('No file chosen yet');
        newCard.find('input[type="checkbox"]').prop('checked', false);
        newCard.find('h3').text('Tell us about your child ' + studentCount);

        // Remove Select2 from cloned selects if still present
        newCard.find('.nationality, .nation').each(function() {
            if ($(this).hasClass("select2-hidden-accessible")) {
                $(this).select2('destroy');
            }
        });

        // Fix file input IDs and make required
        newCard.find('input.parent-file1').attr('id', 'student_file1_' + studentCount).prop('required', true);
        newCard.find('label[for^="student_file1_"]').attr('for', 'student_file1_' + studentCount);

        newCard.find('input.parent-file2').attr('id', 'student_file2_' + studentCount).prop('required', true);
        newCard.find('label[for^="student_file2_"]').attr('for', 'student_file2_' + studentCount);

        // Append remove button
        newCard.append(`<span class="btn btn-danger btn-sm float-end remove-student" style="position:absolute;top:10px;right:10px;">Remove</span>`);

        // Append card to container FIRST
        $('#studentsContainer').append(newCard);

        // Initialize Select2 AFTER appending
        newCard.find('.nationality, .nation').select2({ width: '100%' });

        // Set first option selected properly
        newCard.find('select.nationality, select.nation').each(function() {
            $(this).val($(this).find('option:first').val()).trigger('change');
        });

        // Initialize card logic
        initCard(newCard, false);
    });

    // ------------------------
    // REMOVE CARD
    // ------------------------
    $(document).on('click', '.remove-student', function() {
        $(this).closest('.student-card').remove();
        updateProgress();
    });

    // ------------------------
    // FORM VALIDATION
    // ------------------------
    function validateForm() {
        let isValid = true;

        $('#studentsContainer .student-card').each(function() {
            $(this).find('input, select').each(function() {
                const $field = $(this);
                const type = $field.attr('type');
                const value = $field.val();

                if ($field.is(':hidden')) return true;

                // Text/date fields
                if ((type === 'text' || type === 'date') && (!value || value.trim() === '')) {
                    $field.addClass('is-invalid');
                    isValid = false;
                } 
                // Select fields
                else if ($field.is('select') && (!value || value === '')) {
                    $field.addClass('is-invalid');
                    isValid = false;
                } 
                // File fields
                else if (type === 'file') {
                    if ($field.prop('required') && $field[0].files.length === 0) {
                        $field.addClass('is-invalid');
                        $field.closest('.col-lg-6').find('.fileName').addClass('text-danger');
                        isValid = false;
                    } else {
                        $field.removeClass('is-invalid');
                        $field.closest('.col-lg-6').find('.fileName').removeClass('text-danger');
                    }
                } 
                // Other fields
                else {
                    $field.removeClass('is-invalid');
                }
            });
        });

        return isValid;
    }

    $('#myForm').on('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            alert('Please fill all required fields for each student.');
            $('html, body').animate({
                scrollTop: $('.is-invalid:first').offset().top - 100
            }, 500);
        }
    });

    // Remove invalid highlight on input/select change
    $(document).on('input change', 'input, select', function() {
        $(this).removeClass('is-invalid');
        if ($(this).attr('type') === 'file') {
            $(this).closest('.col-lg-6').find('.fileName').removeClass('text-danger');
        }
        updateProgress();
    });

    // ------------------------
    // PROGRESS BAR
    // ------------------------
    const startProgress = 28;
    const maxIncrement = 14;
    const $progressBar = $("#progressBar");
    const $progressText = $("#progressText");

    function updateProgress() {
        const $fields = $("#studentsContainer input:not([type='hidden']), #studentsContainer select");
        let validCount = 0;
        $fields.each(function() {
            const $f = $(this);
            const type = $f.attr('type');
            if ($f.is(':hidden')) return true;

            if ((type === 'text' || type === 'date') && $f.val().trim() !== '') validCount++;
            else if ($f.is('select') && $f.val() !== '') validCount++;
            else if (type === 'file' && $f[0].files.length > 0) validCount++;
        });

        const increment = maxIncrement / $fields.length;
        let progress = startProgress + (validCount * increment);
        if (progress > startProgress + maxIncrement) progress = startProgress + maxIncrement;

        $progressBar.css('width', progress + '%');
        $progressText.text(Math.round(progress) + '%');
    }

    // Initialize progress
    updateProgress();

});
</script>
@endsection
