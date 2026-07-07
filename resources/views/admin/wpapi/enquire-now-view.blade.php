@extends('admin.layouts.app')

@section('title') Enquire Now Details @endsection

@section('css')
<style>
    th {
        width: 50%;
    }

    td {
        width: 50%;
    }
</style>
@endsection

@section('content')

<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Enquire Now Details</h6>
            <a href="{{ route('admin.enquire-now') }}" class="btn btn-primary btn-sm">← Back</a>
        </div>
        <div class="card-body">
            <div class="row">
                <table class="table table-border">
                    <tr>
                        <th>First Name</th>
                        <td>{{ $data->name_2 }}</td>
                    </tr>
                    <tr>
                        <th>Last Name</th>
                        <td>{{ $data->name_4 }}</td>
                    </tr>
                    <tr>
                        <th>Email Address</th>
                        <td>{{ $data->email_2 }}</td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td>{{ $data->phone_2 }}</td>
                    </tr>
                    <tr>
                        <th>Country of Residence</th>
                       @php
                            $address = is_string($data->address_7) ? json_decode($data->address_7) : (object) $data->address_7;
                        @endphp

                        <td>{{ $address->country ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Student First Name</th>
                        <td>{{ $data->name_38 }}</td>
                    </tr>
                    <tr>
                        <th>Student Last Name </th>
                        <td>{{ $data->name_39 }}</td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        @php
                            // যদি string হয় → decode, যদি array/object হয়ে আসে → object cast
                            $dateData = is_string($data->date_1) ? json_decode($data->date_1) : (object) $data->date_1;

                            $formattedDate = '';

                            if ($dateData && isset($dateData->year, $dateData->month, $dateData->day)) {
                                // Make sure month/day are 2-digit
                                $year = $dateData->year;
                                $month = str_pad($dateData->month, 2, '0', STR_PAD_LEFT);
                                $day = str_pad($dateData->day, 2, '0', STR_PAD_LEFT);

                                $formattedDate = date('d-m-Y', strtotime("$year-$month-$day"));
                            }
                        @endphp

                        <td>{{ $formattedDate }}</td>



                    </tr>
                    <tr>
                        <th>Preferred Start Date</th>
                       @php
                            $date2 = is_string($data->date_2) ? json_decode($data->date_2, true) : $data->date_2;

                            if (is_array($date2) && isset($date2['year'], $date2['month'], $date2['day'])) {
                                $formattedDate2 = date('d-m-Y', strtotime($date2['year'] . '-' . $date2['month'] . '-' . $date2['day']));
                            } else {
                                $formattedDate2 = '';
                            }
                        @endphp

                        <td>{{ $formattedDate2 }}</td>

                    </tr>

                     <tr>
                        <th>Country of Residence</th>
                      @php
                        $address8 = is_string($data->address_8) ? json_decode($data->address_8) : (object) $data->address_8;
                      @endphp

                        <td>{{ $address8->country ?? '' }}</td>

                    </tr>

                    <tr>
                        <th>What can we help you with?</th>
                        <td>{{ $data->name_38 }}</td>
                    </tr>

                     <tr>
                        <th>I am interested in learning more about?</th>
                        <td>{{ $data->checkbox_1 }}</td>
                    </tr>

                    <tr>
                        <th>Please let us know if you have any questions or how our admissions team can help you. Feel free to share more about your child and family:</th>
                        <td>{{ $data->textarea_1 }}</td>
                    </tr>

                     <tr>
                        <th>Please keep me updated on news, events and offers from Al-Rushd</th>
                        <td>{{ $data->radio_2 }}</td>
                    </tr>

                </table>
            </div>
        </div>
    </div>
</div>

@endsection