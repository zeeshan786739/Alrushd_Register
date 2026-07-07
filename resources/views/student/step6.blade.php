@extends('student.app')

@section('title','Preview Information')

@section('student')

<section>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-5 m-auto">
                <!-- Progress Header (Original Design) -->
                <div class="progress-container mb-4">
                    <h5 class="mb-0 text-light title">Estimated time remaining: 4 minutes</h5>
                    <div class="progress mt-2">
                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 70%;"></div>
                    </div>
                    <small id="progressText" class="text-light">70%</small>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-lg-6 step-four m-auto">
                <h3 class="text-center" style="color: #AE9A66;font-size:24px;font-weight:500;">Choose a Pricing Package That Suits You Best</h3>
            </div>
        </div>
        @include('errors.validation')
        <form action="{{ route('form.step.post', 6) }}" method="POST" enctype="multipart/form-data" id="myForm">
            @csrf
            <div class="row d-flex justify-content-center">
                <!-- Parent Info -->
                <div class="col-lg-12">
                    <div class="card p-4 mb-3" style="background-color:#0c2a58;border-radius:16px;">
                        <div class="card-bodyr">
                            <h3 class="py-3" style="color:#FFF;font-size: 24px;font-weight: 600;text-align:center;">Parents Information</h3>

                            <div class="row">
                                <div class="col-lg-8  m-auto">
                                    <div class="row">
                                        <div class="col-lg-7">
                                            <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Name: </span>{{ $data['title'] ?? '' }} {{ $data['fname'] ?? '' }} {{ $data['lname'] ?? '' }}</p>
                                            <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Email: </span>{{ $data['email'] ?? '' }}</p>
                                            <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Phone: </span>{{ $data['mobile_number'] ?? '' }}</p>
                                            <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Parent Type: </span>{{ $data['relationship'] ?? '' }}</p>
                                        </div>
                                        <div class="col-lg-5">
                                            <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Country: </span>{{ $data['country'] ?? '' }}</p>
                                            <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">City: </span>{{ $data['city'] ?? '' }}</p>
                                            <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Postal Code: </span>{{ $data['postal_code'] ?? '' }}</p>
                                            <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Address: </span>{{ $data['address'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('parent-update',$data['id']) }}" class="btn text-center" style="background: #AE9A66;padding:15px 24px;border-radius:99px;font-size:16px;font-weight:600;color:#FFF;"><i class="fa fa-edit"></i> Edit</a>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <!-- Parent Infor -->

                <!-- Multiple Student -->
                @if(!empty($data['students']) && is_array($data['students']))
                @foreach($data['students'] as $index => $student)
                <div class="col-lg-6">
                    <div class="card p-4 mb-3" style="background-color:#0c2a58;border-radius:16px;">
                        <div class="card-bodyr">
                            <h3 class="py-3" style="color:#FFF;font-size: 24px;font-weight: 600;">Student {{ ++$index }} Information</h3>

                            <div class="row">
                                <div class="col-lg-6">
                                    <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Name: </span>{{ $student['fname'] ?? '' }} {{ $student['lname'] ?? '' }}</p>
                                    <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Date of Birth: </span>{{ $student['dob'] ?? '' }}</p>
                                    <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Nationality: </span>{{ $student['nationality'] ?? '' }}</p>
                                    <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Start Date: </span>{{ $student['start_date'] ?? '' }}</p>
                                </div>
                                <div class="col-lg-6">
                                    <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Group: </span>{{ $student['group']['name'] ?? '' }}</p>
                                    <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Year: </span>{{ $student['course']['year']['name'] ?? '' }}</p>
                                    <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Time Table: </span>{{ $data['selected_school'] ?? '' }}</p>
                                    <p style="font-size: 20px;font-weight:400;color:#FFF;"><span style="color: #AE9A66;">Package: </span>{{ $student['course']['package']['name'] ?? '' }}</p>
                                </div>



                                @if(!empty($student['core_subject']))
                                <div class="col-lg-12 mt-3">
                                    <div class="form-group">
                                        <label style="font-size:20px;font-weight:500;color:#AE9A66;">Core Subjects</label>
                                        <div>
                                            @foreach(explode(',', $student['core_subject']) as $subject)
                                            <span class="badge mb-2"
                                                style="background-color:#183e77;
                                                                        border-radius:999px;
                                                                        padding:10px 15px;
                                                                        font-size:16px;
                                                                        color:#FFF;">
                                                {{ trim($subject) }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if(!empty($student['islamic_subject']))
                                <div class="col-lg-12 mt-3">
                                    <div class="form-group">
                                        <label style="font-size:20px;font-weight:500;color:#AE9A66;">Free Islamic Subject</label>
                                        <div>
                                            @foreach(explode(',', $student['islamic_subject']) as $subject)
                                            <span class="badge mb-2"
                                                style="background-color:#183e77;
                                                                        border-radius:999px;
                                                                        padding:10px 15px;
                                                                        font-size:16px;
                                                                        color:#FFF;">
                                                {{ trim($subject) }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if(!empty($student['additional_subject']))
                                <div class="col-lg-12 mt-3">
                                    <div class="form-group">
                                        <label style="font-size:20px;font-weight:500;color:#AE9A66;">Additional Subjects</label>
                                        <div>
                                            @foreach(explode(',', $student['additional_subject']) as $subject)
                                            <span class="badge mb-2"
                                                style="background-color:#183e77;
                                                                        border-radius:999px;
                                                                        padding:10px 15px;
                                                                        font-size:16px;
                                                                        color:#FFF;">
                                                {{ trim($subject) }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if(!empty($student['hifdh_subject']))
                                <div class="col-lg-12 mt-3">
                                    <div class="form-group">
                                        <label style="font-size:20px;font-weight:500;color:#AE9A66;">Additional Hifdh/Hifz Curriculum</label>
                                        <div>
                                            @foreach(explode(',', $student['hifdh_subject']) as $subject)
                                            <span class="badge mb-2"
                                                style="background-color:#183e77;
                                                                        border-radius:999px;
                                                                        padding:10px 15px;
                                                                        font-size:16px;
                                                                        color:#FFF;">
                                                {{ trim($subject) }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif


                                @if(!empty($student['language']))
                                <div class="col-lg-12 mt-3">
                                    <div class="form-group">
                                        <label style="font-size:20px;font-weight:500;color:#AE9A66;">Language</label>
                                        <div>
                                            @foreach(explode(',', $student['language']) as $subject)
                                            <span class="badge mb-2"
                                                style="background-color:#183e77;
                                                                        border-radius:999px;
                                                                        padding:10px 15px;
                                                                        font-size:16px;
                                                                        color:#FFF;">
                                                {{ trim($subject) }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif



                            </div>
                            <div class="mt-3">
                                <a href="{{ route('students.update',$student['id']) }}" class="btn text-center" style="background: #AE9A66;padding:15px 24px;border-radius:99px;font-size:16px;font-weight:600;color:#FFF;"><i class="fa fa-edit"></i> Edit</a>
                            </div>


                        </div>
                    </div>
                </div>
                @endforeach
                @endif

                <!-- Multiple Student -->
            </div>
            <div class="row mt-3">
                <div class="col-lg-6 m-auto">
                    <!-- <div class="d-flex justify-content-between mb-5">
                        <a href="#" class="btn custom-btn w-100 me-3" style="border-radius:8px;">View Terms and Condition</a>
                        <input name="signature"
                            type="text"
                            class="form-control"
                            placeholder="Your Signature here"
                            style="background-color:#FFF !important;color:#000 !important;"
                            value="{{ old('signature', $data['signature'] ?? '') }}">
                    </div> -->

                   <div class="d-flex justify-content-between mb-5 flex-wrap">

                        <!-- Button that triggers the modal -->
                        <a href="#" class="btn custom-btn w-100 me-3 mb-3" style="border-radius:8px;" data-bs-toggle="modal" data-bs-target="#termsModal">
                            View Terms and Condition
                        </a>

                        <!-- Modal -->
                        <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        {!! $terms->terms_description !!}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signature Container -->
                        <div class="signature-container w-100">
                            <label for="signature-pad" class="form-label text-light">Your Digital Signature:</label>
                            <div class="border rounded bg-white position-relative" style="height: 220px;">
                                <canvas id="signature-pad" style="width:100%; height:100%; border:1px solid #ddd; border-radius:8px; cursor:crosshair;"></canvas>
                                <button type="button" id="clear-signature" class="btn btn-sm btn-danger position-absolute" style="top:5px; right:5px; z-index:10;">Clear</button>
                            </div>
                            <!-- Hidden input to store signature image -->
                            <input type="hidden" name="signature" id="signature-input" value="{{ old('signature', Storage::url($data['signature']) ?? '') }}">
                        </div>

                    </div>

                    <!-- <div class="mb-5 text-light">
                        <input type="checkbox"> I have read and agree to the Terms & Conditions.
                    </div> -->

                    <div class="col-lg-12 mt-4 mb-4">
                        <label style="color:#FFF;">Consent <span class="text-danger">*</span></label><br>
                        <label class="custom-checks" for="chek" style="color:#FFF;">
                            I have read and understood your admission process and agree with the Terms
                            and Conditions of Al-Rushd Independent&nbsp;School.
                            <input id="chek" type="checkbox" required name="accpet" value="1" {{ (isset($data['accpet']) && $data['accpet']==1) ? 'checked' : '' }} required>
                            <span class="custom-checkmarks"></span>
                        </label>

                   
                    </div>

                    <!-- Terms Checkmark -->
                    <div class="form-check mb-4 ps-0">
                        <!-- Checkbox HTML -->
                        
                        <label class="custom-check" style="color:#FFF;">
                            I have read and agree to the Terms & Conditions.
                            <input type="checkbox"
                                required
                                name="signature_accept"
                                value="yes"
                                {{ old('signature_accept', $data['signature_accept'] ?? '') == 'yes' ? 'checked' : '' }}>
                            <span class="custom-checkmark"></span>
                        </label>
                    </div>


                    <button type="submit" class="btn custom-btn w-100">Continue</button>
                    <div class="text-center mt-4">
                        <a href="{{ route('form.step', 5) }}" class="text-light text-decoration-none"><i class="fa fa-arrow-left"></i> Go Back</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>



@endsection


@section('script')

<!-- SignaturePad JS -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        const signatureInput = document.getElementById('signature-input');
        const signaturePad = new SignaturePad(canvas, {
            minWidth: 1,
            maxWidth: 3,
            penColor: "black",
            velocityFilterWeight: 0.7,
        });

        // Resize canvas to be responsive
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);

            // Reload existing signature if present
            if (signatureInput.value) {
                const img = new Image();
                img.src = signatureInput.value;
                img.onload = () => signaturePad.fromDataURL(signatureInput.value);
            }
        }
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        // Clear signature
        document.getElementById('clear-signature').addEventListener('click', function() {
            signaturePad.clear();
            signatureInput.value = '';
        });

        // Save signature on form submit
        const form = signaturePad.canvas.closest('form');
        if(form){
            form.addEventListener('submit', function(e){
                if(signaturePad.isEmpty()){
                    alert('Please provide a signature!');
                    e.preventDefault();
                } else {
                    signatureInput.value = signaturePad.toDataURL('image/png');
                }
            });
        }
    });
</script>

@endsection

