@extends('student.app')

@section('title','Select Information')

@section('student')

<section>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-5 m-auto">
                <!-- Progress Header (Original Design) -->
                <div class="progress-container mb-4">
                    <h5 class="mb-0 text-light title">Estimated time remaining: 6 minutes</h5>
                    <div class="progress mt-2">
                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 42%;"></div>
                    </div>
                    <small id="progressText" class="text-light">42%</small>
                </div>
            </div>
        </div>

        @include('errors.validation')
        
        <form action="{{ route('form.step.post', 4) }}" method="POST" enctype="multipart/form-data" id="myForm">
            @csrf
            <div class="row d-flex justify-content-center">
                <div class="col-lg-11">
                    <div id="studentsContainer">
                        <div class="card p-4 mb-3 " style="background-color:#0c2a58;border-radius:24px;color:#FFF;">
                            <div class="card-body">
                                <h3 class="text-center mb-5" style="color: #AE9A66;font-size: 24px;font-weight: 600;">Additional Information</h3>
                                <!-- Row -->
                                <div class="row">


                                    <div class="col-md-7">
                                        <label class="form-label d-block">
                                            An Education & Health Care plan (EHCP) is a formal document
                                            detailing a child's learning difficulties and the help they will be
                                            given. Does the child have an Education Health Care Plan?
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex gap-3">
                                            <div>
                                                <input type="radio" class="custom-radio" id="yes" name="health_care" value="1"
                                                    {{ (isset($data['health_care']) && $data['health_care'] == 1) ? 'checked' : '' }} required>
                                                <label for="yes" class="text-light">Yes</label>
                                            </div>
                                            <div>
                                                <input type="radio" class="custom-radio" id="no" name="health_care" value="0"
                                                    {{ (isset($data['health_care']) && $data['health_care'] == 0) ? 'checked' : '' }} required>
                                                <label for="no" class="text-light">No</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label d-block w-492px h-63">
                                            Permanent Exclusions : Has this child been permanently excluded (expelled)
                                            from their previous school?
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex gap-3">
                                            <div>
                                                <input type="radio" class="custom-radio" id="ayes" name="previus_school" value="1"
                                                    {{ (isset($data['previus_school']) && $data['previus_school'] == 1) ? 'checked' : '' }} required>
                                                <label for="ayes" class="text-light">Yes</label>
                                            </div>
                                            <div>
                                                <input type="radio" class="custom-radio" id="ano" name="previus_school" value="0"
                                                    {{ (isset($data['previus_school']) && $data['previus_school'] == 0) ? 'checked' : '' }} required>
                                                <label for="ano" class="text-light">No</label>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="col-md-8 text-light mb-0 mt-3">
                                        <label class="form-label">
                                            Fair Access Protocol: (Checkboxes option for the list below) -
                                            Does the child fall under any of the below listed categories of the Fair Access Protocol?
                                            <span class="text-danger">*</span>
                                        </label>
                                    </p>

                                    <div class="col-md-12 mt-3">
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_10" name="access_protocol" value="9" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 9) ? 'checked' : '' }} required>
                                            <label for="yes_10" class="text-light">None</label>
                                        </div>
                                        <div>
                                            <input type="radio" class="custom-radio" id="yes_1" name="access_protocol" value="0" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 0) ? 'checked' : '' }} required>
                                            <label for="yes_1" class="text-light">Children subject to a child in need plan or a child protection plan within the last 12 months</label>
                                        </div>
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_2" name="access_protocol" value="1" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 1) ? 'checked' : '' }} required>
                                            <label for="yes_2" class="text-light">Children living in a refuge</label>
                                        </div>
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_3" name="access_protocol" value="2" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 2) ? 'checked' : '' }} required>
                                            <label for="yes_3" class="text-light">Children from the criminal justice&nbsp;system</label>
                                        </div>
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_4" name="access_protocol" value="3" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 3) ? 'checked' : '' }} required>
                                            <label for="yes_4" class="text-light">Children who are carers</label>
                                        </div>
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_5" name="access_protocol" value="4" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 4) ? 'checked' : '' }} required>
                                            <label for="yes_5" class="text-light">Children who are homeless</label>
                                        </div>
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_6" name="access_protocol" value="5" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 5) ? 'checked' : '' }} required>
                                            <label for="yes_6" class="text-light">Children in formal kinship care arrangements</label>
                                        </div>
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_7" name="access_protocol" value="6" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 6) ? 'checked' : '' }} required>
                                            <label for="yes_7" class="text-light">Children of, or who are, Gypsies, Roma or Travellers</label>
                                        </div>
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_8" name="access_protocol" value="7" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 7) ? 'checked' : '' }} required>
                                            <label for="yes_8" class="text-light">Children who are refugees or asylum&nbsp;seekers</label>
                                        </div>
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_9" name="access_protocol" value="8" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 8) ? 'checked' : '' }} required>
                                            <label for="yes_9" class="text-light">Children who have been out of education for four weeks&nbsp;or&nbsp;more</label>
                                        </div>
                                        
                                        <div>
                                            <input class="custom-radio" type="radio" id="yes_11" name="access_protocol" value="10" {{ (isset($data['access_protocol']) && $data['access_protocol'] == 10) ? 'checked' : '' }} required>
                                            <label for="yes_11" class="text-light">Other</label>
                                        </div>
                                    </div>

                                    <div class="row mt-4 extra-section d-none">
                                        <div class="col-lg-6">
                                            <div class="form-group mb-4">
                                                <label for="form-label">If any of these apply, provide the supporting local&nbsp;authority</label>
                                                <input type="text" name="authority" class="form-control" placeholder="Local Authority" value="{{ $data['authority'] ?? '' }}">
                                            </div>
                                        </div>


                                        <div class="col-lg-6">
                                            <div class="form-group mb-4">
                                                <label for="form-label">Provide the name of the assigned social&nbsp;worker*</label>

                                                <input type="text" name="assigned" class="form-control" placeholder="Name" value="{{ $data['assigned'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <p><b>Special Educational Needs and Disabilities:</b></p>
                                            <label class="form-label d-block w-492px h-63">
                                                Is this child on the special educational needs and disabilities code&nbsp;of&nbsp;practice
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex gap-3">
                                                <div>
                                                    <input type="radio" class="custom-radio" name="special_education" value="1"
                                                        {{ (isset($data['special_education']) && $data['special_education']==1) ? 'checked' : '' }} required>
                                                    <label for="ehcp_yes" class="text-light">Yes</label>
                                                </div>
                                                <div>
                                                    <input type="radio" class="custom-radio" name="special_education" value="0"
                                                        {{ (isset($data['special_education']) && $data['special_education']==0) ? 'checked' : '' }} required>
                                                    <label for="ehcp_no" class="text-light">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <p><b>Medical Conditions: </b></p>
                                            <label class="form-label d-block w-492px h-63">
                                                Does the child have any long term medical&nbsp;conditions?&nbsp;
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex gap-3">
                                                <div>
                                                    <input type="radio" class="custom-radio" name="medical_condition" value="1"
                                                        {{ (isset($data['medical_condition']) && $data['medical_condition']==1) ? 'checked' : '' }} required>
                                                    <label for="ehcp_yes_1" class="text-light">Yes</label>
                                                </div>
                                                <div>
                                                    <input type="radio" class="custom-radio" name="medical_condition" value="0"
                                                        {{ (isset($data['medical_condition']) && $data['medical_condition']==0) ? 'checked' : '' }} required>
                                                    <label for="ehcp_yes_2" class="text-light">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <p><b>Direct Placements:</b></p>
                                            <label class="form-label d-block w-492px h-63">
                                                Has the child been directed to an Alternative Provision to improve their&nbsp;behaviour?
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex gap-3">
                                                <div>
                                                    <input type="radio" class="custom-radio" name="direct_placement" value="1"
                                                        {{ (isset($data['direct_placement']) && $data['direct_placement']==1) ? 'checked' : '' }} required>
                                                    <label for="yeson" class="text-light">Yes</label>
                                                </div>
                                                <div>
                                                    <input type="radio" class="custom-radio" name="direct_placement" value="0"
                                                        {{ (isset($data['direct_placement']) && $data['direct_placement']==0) ? 'checked' : '' }} required>
                                                    <label for="yesno" class="text-light">No</label>
                                                </div>
                                            </div>

                                            <!-- Hidden Input Box -->
                                            <div class="mt-3 direct-placement-box d-none">
                                                <label for="placement_detail" class="text-light">Alternative Provision</label>
                                                <input type="text" name="placement_detail" id="placement_detail" class="form-control"
                                                    placeholder="Enter details" value="{{ $data['placement_detail'] ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group mb-4">
                                                <p><b>Attendance in previous school:</b></p>
                                                <label for="form-label">Attendance percentage</label>
                                                <input type="text" id="percentageInput" name="percentage" class="form-control" placeholder="Percentage" value="{{ $data['percentage'] ?? '' }}" required>

                                            </div>
                                        </div>
                                    </div>


                                  
                                </div>
                                <!-- End Rrow -->
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row mt-3 mb-5">
                <div class="col-lg-11 m-auto">

                    <button type="submit" class="btn custom-btn w-100">Continue</button>
                    <div class="text-center mt-4">
                        <a href="{{ route('form.step', 3) }}" class="text-light text-decoration-none"><i class="fa fa-arrow-left"></i> Go Back</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>



@endsection


@section('script')

<script>
$(document).ready(function(){
    $('#percentageInput').on('input', function() {
        let val = $(this).val().replace('%',''); // remove old %
        if(val) $(this).val(val + '%');
    });

    // On form submit, remove % before sending to server
    $('form').on('submit', function() {
        let val = $('#percentageInput').val().replace('%','');
        $('#percentageInput').val(val);
    });
});
</script>
<!-- jQuery Script -->
<script>
$(document).ready(function() {
    function togglePlacementBox() {
        const selectedValue = $('input[name="direct_placement"]:checked').val();
        if (selectedValue === '1') {
            $('.direct-placement-box').removeClass('d-none'); // Show when Yes
        } else {
            $('.direct-placement-box').addClass('d-none'); // Hide when No
        }
    }

    // Run on load (handles edit mode)
    togglePlacementBox();

    // Run on change
    $('input[name="direct_placement"]').on('change', togglePlacementBox);
});
</script>
<!-- jQuery Script -->
<script>
$(document).ready(function() {
    function toggleExtraSection() {
        const selectedValue = $('input[name="access_protocol"]:checked').val();
        if (selectedValue === '9') {
            $('.extra-section').addClass('d-none'); // Hide when "None" selected
        } else {
            $('.extra-section').removeClass('d-none'); // Show otherwise
        }
    }

    // Run on load (to handle edit mode)
    toggleExtraSection();

    // Run on change
    $('input[name="access_protocol"]').on('change', toggleExtraSection);
});
</script>

<script>
$(document).ready(function() {
    const startProgress = 42;   // শুরুতে 42%
    const maxIncrement = 14;    // 42% থেকে 56% পর্যন্ত
    const $progressBar = $("#progressBar");
    const $progressText = $("#progressText");

    // সব input/select field ধরা (file এবং hidden বাদ)
    const $fields = $("input:not([type='hidden']):not([type='file']), select");

    // প্রতিটি input এর increment
    const totalFields = $fields.length;
    const increment = maxIncrement / totalFields;

    function calculateProgress() {
        let filled = 0;

        $fields.each(function() {
            const type = $(this).attr("type");

            if ((type === "checkbox" || type === "radio") && $(this).is(":checked")) {
                filled++;
            } else if (type === "text" || $(this).is("select")) {
                if ($(this).val()?.trim() !== "") filled++;
            }
        });

        let progress = startProgress + (filled * increment);
        if (progress > startProgress + maxIncrement) progress = startProgress + maxIncrement;

        $progressBar.css("width", progress + "%");
        $progressText.text(progress.toFixed(0) + "%");
    }

    // page load এ আগের value অনুযায়ী progress set
    calculateProgress();

    // যখন user input/select করে
    $fields.on("input change", function() {
        calculateProgress();
    });
});
</script>


@endsection