@extends('admin.layouts.app')

@section('title') Form Submission View @endsection

@section('content')

@if($data->fname)
<section style="background: #EAF2FF;border-radius: 24px;padding:32px 46px 48px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <p style="font-family: Switzer;font-weight: 600;font-style: Semibold;font-size: 24px;color: #183E77;">Primary Parent Information</p>
            </div>
            <div class="col-12 card p-3">
                <div class="row">
                    <div class="col-lg-5">
                        <p>Name: <b>{{ $data->title }} {{ $data->fname }} {{ $data->lname }}</b></p>
                        <p>Relationship: <b>{{ $data->relationship }}</b></p>
                        <p>Email: <b>{{ $data->email }}</b></p>
                        <p>Confirm Email: <b>{{ $data->confirm_email }}</b></p>
                        <p>Phone: <b>{{ $data->mobile_number }}</b></p>
                    </div>
                    <div class="col-lg-4">
                        <p>Home Telephone: <b>{{ $data->home_telephone }}</b></p>
                        <p>Work Number: <b>{{ $data->work_number }}</b></p>
                        <p>Country: <b>{{ $data->country }}</b></p>
                        <p>City: <b>{{ $data->city }}</b></p>
                        <p>Post: <b>{{ $data->postal_code }}</b></p>
                    </div>
                    <div class="col-lg-3">
                        <p>Address: <b>{{ $data->address }}</b></p>
                        <p>Apartment: <b>{{ $data->apartment }}</b></p>
                        <p>Province: <b>{{ $data->province }}</b></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif


@if($data->secondary_fname)

<section class="mt-3" style="background: #EAF2FF;border-radius: 24px;padding:32px 46px 48px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <p style="font-family: Switzer;font-weight: 600;font-style: Semibold;font-size: 24px;color: #183E77;">Secondary Parent Information</p>
            </div>
            <div class="col-12 card p-3">
                <div class="row">
                    <div class="col-lg-5">
                        <p>Name: <b>{{ $data->secondary_title }} {{ $data->secondary_fname }} {{ $data->secondary_lname }}</b></p>
                        <p>Relationship: <b>{{ $data->secondary_relationship }}</b></p>
                        <p>Email: <b>{{ $data->secondary_email }}</b></p>
                        <p>Confirm Email: <b>{{ $data->secondary_confirm_email }}</b></p>
                        <p>Phone: <b>{{ $data->secondary_mobile_number }}</b></p>
                    </div>
                    <div class="col-lg-4">
                        <p>Home Telephone: <b>{{ $data->secondary_home_telephone }}</b></p>
                        <p>Work Number: <b> {{ $data->secondary_work_number }}</b></p>
                        <p>Country: <b> {{ $data->secondary_country }}</b></p>
                        <p>City: <b>{{ $data->secondary_city }}</b></p>
                        <p>Post: <b>{{ $data->secondary_postal_code }}</b></p>
                    </div>
                    <div class="col-lg-3">
                        <p>Address: <b>{{ $data->secondary_address }}</b></p>
                        <p>Apartment: <b>{{ $data->secondary_apartment }}</b></p>
                        <p>Province: <b>{{ $data->secondary_province }}</b></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if($data->students->count())
<section class="mt-3" style="background: #EAF2FF;border-radius: 24px;padding:32px 46px 48px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <p style="font-family: Switzer;font-weight: 600;font-style: Semibold;font-size: 24px;color: #183E77;">Student Information</p>
            </div>
            @foreach($data->students as $student)
            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-4">
                        <p>Serial: <b>{{ $loop->iteration }}</b></p>
                        <p>Name: <b>{{ $student->fname }} {{ $student->lname }}</b></p>
                        <p>DOB: <b>{{ $student->dob }}</b></p>

                    </div>
                    <div class="col-lg-4">
                        <p>Gender: <b>{{ $student->gender }}</b></p>
                        <p>Nationality: <b> {{ $student->nationality }}</b></p>
                        <p>Start Date: <b> {{ $student->start_date }}</b></p>

                    </div>
                    <div class="col-lg-4">
                        <p>Year: <b>{{ $student->year->name }}</b></p>
                        <p>Package: <b>{{ $student->package->name }}</b></p>
                        <p>Hifdh: <b>{{ $student->hifdh == 1 ? 'Yes' : 'N/A' }}</b></p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(!empty($data->packages) && is_iterable($data->packages))
<section class="mt-3" style="background: #EAF2FF; border-radius: 16px; padding: 32px; max-width: 100%; margin: 0 auto;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h5 style="font-family: Switzer, sans-serif; font-weight: 600; color: #183E77; margin-bottom: 20px;">
                    Packages Information
                </h5>
            </div>

            <div class="col-12">
                <div class="table-wrapper" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0; background: white; border-radius: 12px; overflow: hidden;">
                        <thead>
                            <tr style="background: #B49A64; color: #fff;">
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600; border-top-left-radius: 12px;">Student Name</th>
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600;">Package</th>
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600;">Regular Price</th>
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600;">Discount Price</th>
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600; border-top-right-radius: 12px;">Discount</th>
                            </tr>
                        </thead>
                        <tbody>

                        @if(!empty($data->packages) && is_iterable($data->packages))

                            @foreach($data->packages as $pkg)
                            @php
                            $student = \App\Models\FormStudent::find($pkg['student_id'] ?? null);
                            @endphp
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 16px;">{{ $student->fname ?? 'Unknown' }} {{ $student->lname ?? '' }}</td>
                                <td style="padding: 12px 16px;">{{ ucfirst($pkg['package']) }}</td>
                                <td style="padding: 12px 16px;">£{{ $pkg['regular_price'] }}</td>
                                <td style="padding: 12px 16px;">£{{ $pkg['discount_price'] }}</td>
                                <td style="padding: 12px 16px;">£{{ $pkg['discount'] }}</td>
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
        </div>
    </div>
</section>
@endif

@if($data->health_care==1)
<section class="mt-3" style="background: #EAF2FF;border-radius: 24px;padding:32px 46px 48px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <p style="font-family: Switzer;font-weight: 600;font-style: Semibold;font-size: 24px;color: #183E77;"> Child Details & Education/Health Info</p>
            </div>

            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">An Education & Health Care Plan (EHCP):</p>
                        <p style="margin: 4px 0 0; color: #666;">A formal document detailing a child’s learning difficulties and the help they will be given. Does the child have an Education Health Care Plan?</p>
                    </div>
                    <div class="col-lg-4">
                        <b>{{ $data->health_care==1 ? 'Yes' : 'No' }}</b>
                    </div>
                </div>
            </div>

            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">Permanent Exclusions:</p>
                        <p style="margin: 4px 0 0; color: #666;">Has this child been permanently excluded (expelled) from their previous school?</p>
                    </div>
                    <div class="col-lg-4">
                        <b>{{ $data->previus_school==1 ? 'Yes' : 'No' }}</b>
                    </div>
                </div>
            </div>

            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">Fair Access Protocol:</p>
                    </div>
                    <div class="col-lg-4">
                        @php
                        $protocols = [
                        0 => 'Children subject to a child in need plan or a child protection plan within the last 12 months',
                        1 => 'Children living in a refuge',
                        2 => 'Children from the criminal justice system',
                        3 => 'Children who are carers',
                        4 => 'Children who are homeless',
                        5 => 'Children in formal kinship care arrangements',
                        6 => 'Children of, or who are, Gypsies, Roma or Travellers',
                        7 => 'Children who are refugees or asylum seekers',
                        8 => 'Children who have been out of education for four weeks or more',
                        9 => 'None',
                        10 => 'Other'
                        ];
                        @endphp
                        <b>{{ $protocols[$data->access_protocol] ?? 'N/A' }}</b>
                    </div>
                </div>
            </div>

            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">Supporting Local Authority:</p>
                    </div>
                    <div class="col-lg-4">
                        <b>{{ $data->authority }}</b>
                    </div>
                </div>
            </div>

            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">Assigned Social Worker:</p>
                    </div>
                    <div class="col-lg-4">
                        <b>{{ $data->assigned }}</b>
                    </div>
                </div>
            </div>

            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">Special Educational Needs & Disabilities:</p>
                    </div>
                    <div class="col-lg-4">
                        <b>{{ $data->special_education==1 ? 'Yes' : 'No' }}</b>
                    </div>
                </div>
            </div>

            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">Medical Conditions:</p>
                    </div>
                    <div class="col-lg-4">
                        <b>{{ $data->medical_condition==1 ? 'Yes' : 'No' }}</b>
                    </div>
                </div>
            </div>


            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">Direct Placements:</p>
                    </div>
                    <div class="col-lg-4">
                        <b>{{ $data->direct_placement==1 ? 'Yes' : 'No' }}</b>
                    </div>
                </div>
            </div>


            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">Attendance in Previous School (%):</p>
                    </div>
                    <div class="col-lg-4">
                        <b>{{ $data->percentage }}</b>
                    </div>
                </div>
            </div>


            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <p style="margin: 0; font-weight: 600;">Consent:</p>
                        <p style="margin: 4px 0 0; color: #666; ">I have read and understood your admission process and agree with the Terms and Conditions of Al-Rushd Independent School.</p>
                    </div>
                    <div class="col-lg-4">
                        <b>{{ $data->accpet==1 ? 'Yes' : 'No' }}</b>
                    </div>
                </div>
            </div>


        </div>
    </div>
</section>
@endif

@if($data->signature)

<!-- Signature Info Section -->
<section style="background: #EAF2FF; border-radius: 16px; padding: 32px; width: 99%; margin-top: 24px; font-family: 'Switzer', sans-serif;">
    <div class="container-fluid">
        <h5 style="font-weight: 600; color: #183E77; margin-bottom: 20px;">
            Signature Info
        </h5>
        <div style="background: #fff; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                @if($data->signature)
                <img src="{{ Storage::url($data->signature) }}" alt="Signature" style="width:220px;height:220px;border: 1px solid #ddd; border-radius: 4px;">
                @else
                <p class="text-muted">No Signature Available</p>
                @endif
                <p style="margin: 0; font-weight: 500;">Signature Accept: <b>{{ $data->signature_accept ?? 'N/A' }}</b></p>
            </div>
        </div>
    </div>
</section>
@endif


@if($data->card_holder_name)

<section class="mt-3" style="background: #EAF2FF;border-radius: 24px;padding:32px 46px 48px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <p style="font-family: Switzer;font-weight: 600;font-style: Semibold;font-size: 24px;color: #183E77;"> Payment Info</p>
            </div>

            <div class="col-12 card p-3 mb-3">
                <div class="row">
                    <div class="col-lg-4">
                        <p style="margin: 0 0 8px;"><b>Card Holder:</b> {{ $data->card_holder_name ?? 'N/A' }} </p>
                        <p style="margin: 0 0 8px;"><b>Email:</b>{{ $data->payment_email ?? 'N/A' }}</p>
                        <p style="margin: 0 0 8px;"><b>Country:</b> {{ $data->payment_country ?? 'N/A' }}</p>
                        <p style="margin: 0;"><b>Postal Code:</b>{{ $data->payment_postal_code ?? 'N/A' }}</p>
                    </div>
                    <div class="col-lg-3">
                        <p style="margin: 0 0 8px;"><b>Accepted:</b>{{ $data->payment_accept ?? 'N/A' }}</p>
                        <p style="margin: 0 0 8px;"><b>Status:</b>
                            <span class="badge bg-{{ $data->status == 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($data->status) }}
                            </span>
                        </p>
                        <p style="margin: 0 0 8px;"><b>Total Amount:</b>£{{ number_format($data->total_amount, 2) }}</p>
                        <p style="margin: 0 0 8px;"><b>Paid Amount:</b> £{{ number_format($data->paid_amount, 2) }}</p>
                    </div>
                    <div class="col-lg-5">
                        <p style="margin: 0 0 8px;"><b>Transaction ID:</b>{{ $data->transaction_id ?? 'N/A' }}</p>
                        <p style="margin: 0 0 8px;"><b>Payment Date:</b>{{ $data->payment_date ?? 'N/A' }}</p>
                        <p style="margin: 0;"><b>Currency:</b>{{ $data->currency ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endif
<div class="pb-5 mt-3 text-end">
    <a href="{{ route('admin.download.payment.pdf', $data->id) }}" class="btn btn-primary btn-lg">Download</a>
</div>
@endsection