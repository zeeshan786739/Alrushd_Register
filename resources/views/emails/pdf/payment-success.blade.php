<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Form Submission View</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            background-color: #FFF;
            color: #000;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }

        h5 {
            font-weight: 600;
            color: #183E77;
            font-size: 20px;
            margin: 0 0 10px 0;
        }

        .section {
            background: #EAF2FF;
            border-radius: 16px;
            padding: 16px;
            margin-top: 15px;
        }

        .card {
            background-color: #fff;
            border-radius: 12px;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #B49A64;
            color: white;
            font-weight: 600;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .header-section {
            background-color: #FFFFFF;
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #183E77;
        }

        .header-section img {
            width: 100px;
            margin-bottom: 10px;
        }

        .header-section h1 {
            color: #183E77;
            font-size: 26px;
            margin: 0;
        }

        .header-section p {
            margin: 3px 0;
            color: #555;
            font-size: 12px;
        }

        .terms-section {
            background-color: #0C2A58;
            color: white;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            margin-top: 25px;
        }

        .terms-section h2 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .signature-section {
            background-color: #fff;
            /* border: 2px solid #183e77;
            border-radius: 10px; */
            padding: 20px;
            margin-top: 25px;
        }

        .signature-section h2 {
            color: #183e77;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .signature-section p {
            font-size: 13px;
            margin: 4px 0;
        }

        .signature-box {
            width: 200px;
            height: 120px;
            border: 1px solid #000;
            border-radius: 16px;
            margin: 10px auto;
            text-align: center;
        }

        .table-no-border td {
            border: none !important;
        }
    </style>
</head>

<body>

    <div class="header-section">
        <img src="https://register.alrushd.co.uk/frontend/assets/img/logo.png" alt="Logo">
        <h1>Al-Rushd Independent School</h1>
        <p>Email: admin@alrushd.co.uk | Phone: +442036330757</p>
        <p>Unit 8, Church Road Studios 62 Church Road London E12 6AF</p>
    </div>

    <div class="container">

        <div class="section">
            <h5>Primary Parent Information</h5>
            <div class="card">
                <table class="table-no-border">
                    <tr>
                        <td><b>Name:</b>{{$submission->title }} {{$submission->fname }} {{$submission->lname }}</td>
                        <td><b>Relationship:</b>{{ $submission->relationship }}</td>
                        <td><b>Email:</b>{{ $submission->email }}</td>
                    </tr>
                    <tr>
                        <td><b>Confirm Email:</b>{{ $submission->confirm_email }}</td>
                        <td><b>Phone:</b>{{ $submission->mobile_number }}</td>
                        <td><b>Home Telephone:</b>{{ $submission->home_telephone }}</td>
                    </tr>
                    <tr>
                        <td><b>Work Number:</b>{{ $submission->work_number }}</td>
                        <td><b>Country:</b>{{ $submission->country }}</td>
                        <td><b>City:</b>{{ $submission->city }}</td>
                    </tr>
                    <tr>
                        <td><b>Post:</b>{{ $submission->postal_code }}</td>
                        <td><b>Address:</b>{{ $submission->address }}</td>
                        <td><b>Apartment:</b>{{ $submission->apartment }}</td>
                    </tr>
                    <tr>
                        <td colspan="3"><b>Province:</b>{{ $submission->province }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <h5>Secondary Parent Information</h5>
            <div class="card">
                <table class="table-no-border">
                    <tr>
                        <td><b>Name:</b> {{ $submission->secondary_title }} {{ $submission->secondary_fname }} {{ $submission->secondary_lname }}</td>
                        <td><b>Relationship:</b>{{ $submission->secondary_relationship }}</td>
                        <td><b>Email:</b>{{ $submission->secondary_email }}</td>
                    </tr>
                    <tr>
                        <td><b>Confirm Email:</b>{{ $submission->secondary_confirm_email }}</td>
                        <td><b>Phone:</b>{{ $submission->secondary_mobile_number }}</td>
                        <td><b>Home Telephone:</b> {{ $submission->secondary_home_telephone }}</td>
                    </tr>
                    <tr>
                        <td><b>Work Number:</b>{{ $submission->secondary_work_number }}</td>
                        <td><b>Country:</b>{{ $submission->secondary_country }}</td>
                        <td><b>City:</b>{{ $submission->secondary_city }}</td>
                    </tr>
                    <tr>
                        <td><b>Post:</b>{{ $submission->secondary_postal_code }}</td>
                        <td><b>Address:</b>{{ $submission->secondary_address }}</td>
                        <td><b>Apartment:</b>{{ $submission->secondary_apartment }}</td>
                    </tr>
                    <tr>
                        <td colspan="3"><b>Province:</b>{{ $submission->secondary_province }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <br><br>

        @if($submission->students->count())
        <div class="section">
            <h5>Student Information</h5>

            @foreach($submission->students as $student)
            <div class="card">
                <table class="table-no-border">
                    <tr>
                        <td><b>Serial:</b>{{ $loop->iteration }}</td>
                        <td><b>Name:</b>{{ $student->fname }} {{ $student->lname }}</td>
                        <td><b>DOB:</b> {{ $student->dob }}</td>
                    </tr>
                    <tr>
                        <td><b>Gender:</b>{{ $student->gender }}</td>
                        <td><b>Nationality:</b>{{ $student->nationality }}</td>
                        <td><b>Start Date:</b> {{ $student->start_date }}</td>
                    </tr>
                    <tr>
                        <td><b>Year:</b>{{ $student->year->name }}</td>
                        <td><b>Package:</b>{{ $student->package->name }}</td>
                        <td><b>Hifdh:</b>{{ $student->hifdh == 1 ? 'Yes' : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            @endforeach
        </div>
        @endif



        @if(!empty($submission->packages) && is_iterable($submission->packages))
        <div class="section">
            <h5>Packages Information</h5>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Package</th>
                            <th>Regular Price</th>
                            <th>Discount Price</th>
                            <th>Discount</th>
                        </tr>
                    </thead>
                    <tbody>

                        @if(!empty($submission->packages) && is_iterable($submission->packages))

                        @foreach($submission->packages as $pkg)
                        @php
                        $student = \App\Models\FormStudent::find($pkg['student_id'] ?? null);
                        @endphp
                        <tr>
                            <td>{{ $student->fname ?? 'Unknown' }} {{ $student->lname ?? '' }}</td>
                            <td>{{ ucfirst($pkg['package']) }}</td>
                            <td>£{{ $pkg['regular_price'] }}</td>
                            <td>£{{ $pkg['discount_price'] }}</td>
                            <td>£{{ $pkg['discount'] }}</td>
                        </tr>

                        @endforeach
                        @else
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 16px;">No packages available</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif


        <br> <br>
        @if($submission->card_holder_name)
        <div class="section">
            <h5>Payment Info</h5>
            <div class="card">
                <table class="table-no-border">
                    <tr>
                        <td><b>Card Holder:</b> {{ $submission->card_holder_name ?? 'N/A' }}</td>
                        <td><b>Email:</b>{{ $submission->payment_email ?? 'N/A' }}</td>
                        <td><b>Country:</b>{{ $submission->payment_country ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><b>Postal Code:</b>{{ $submission->payment_postal_code ?? 'N/A' }}</td>
                        <td><b>Accepted:</b>{{ $submission->payment_accept ?? 'N/A' }}</td>
                        <td><b>Status:</b> <span class="badge badge-success">
                                <span class="badge bg-{{ $submission->status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </span></td>
                    </tr>
                    <tr>
                        <td><b>Total Amount:</b>£{{ number_format($submission->total_amount, 2) }}</td>
                        <td><b>Paid Amount:</b> £{{ number_format($submission->paid_amount, 2) }}</td>
                        <td><b>Transaction ID:</b>{{ $submission->transaction_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><b>Payment Date:</b> {{ $submission->payment_date ?? 'N/A' }}</td>
                        <td><b>Currency:</b>{{ $submission->currency ?? 'N/A' }}</td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        <div class="terms-section">
            <h2>Terms and Conditions of Al Rushd School</h2>
            <p>I have read and understood your admission process and agree with the Terms and Conditions of Al-Rushd Independent School.</p>
        </div>

        <div class="signature-section">

    {!! optional(\App\Models\TermsAndCondition::first())->terms_description !!}

    @php
        $signaturePath = storage_path('app/public/' . $submission->signature); // path
        if(file_exists($signaturePath)){
            $signatureData = base64_encode(file_get_contents($signaturePath));
            $type = pathinfo($signaturePath, PATHINFO_EXTENSION); // jpg/png
        } else {
            $signatureData = null;
            $type = null;
        }
    @endphp

    <table style="width: 100%; margin-top: 20px;">
        <tr style="border: none !important;">
            <!-- Left Column -->
            <td style="width: 50%; vertical-align: top;border: none !important;">
                <p><b>Parent/Guardian Name:</b> {{$submission->title }} {{$submission->fname }} {{$submission->lname }}</p>
                <p><b>Date:</b> {{ $submission->payment_date }}</p>
            </td>

            <!-- Right Column -->
            <td style="width: 50%; text-align: center; vertical-align: top;border: none !important;">
                <div class="signature-box" style="border: 1px solid #ccc; padding: 10px; display: inline-block; max-width: 250px;">
                    @if($signatureData)
                        <img src="data:image/{{ $type }};base64,{{ $signatureData }}" style="width: 100%; height: auto;">
                    @else
                        <p>No Signature Uploaded</p>
                    @endif
                </div>
                <p><b>Signature</b></p>
            </td>
        </tr>
    </table>

</div>




    </div>

</body>

</html>