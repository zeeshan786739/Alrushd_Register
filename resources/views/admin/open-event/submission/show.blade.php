@extends('admin.layouts.app')

@section('title') Open Event (Entry #{{ $data->entry_id }}) @endsection

@section('content')
<div class="dashboard-main-body" id="oe-workspace-page">
@include('admin.open-event.partials.shell', [
    'activeTab' => 'submissions',
    'compact' => true,
    'shellTitle' => 'Submission #Entry-'.$data->entry_id,
    'shellSubtitle' => 'Registration details submitted through the open event form.',
    'shellActions' => [[
        'label' => 'Back to submissions',
        'url' => route('admin.open-event-form.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<div class="oe-detail-grid">
    <section class="oe-detail-card">
        <div class="oe-detail-card__head">
            <h2>Parent / Guardian Details</h2>
            <p>Contact information for the registering parent or guardian</p>
        </div>
        <table class="oe-detail-table">
            <tbody>
                <tr>
                    <th>First Name</th>
                    <td>{{ $data->fname ?? '' }}</td>
                </tr>
                <tr>
                    <th>Last Name</th>
                    <td>{{ $data->lname ?? '' }}</td>
                </tr>
                <tr>
                    <th>Email Address</th>
                    <td>{{ $data->email ?? '' }}</td>
                </tr>
                <tr>
                    <th>Mobile Number</th>
                    <td>{{ $data->mobile_number ?? '' }}</td>
                </tr>
                <tr>
                    <th>Country of Residence</th>
                    <td>{{ $data->country }}</td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="oe-detail-card">
        <div class="oe-detail-card__head">
            <h2>Student Details</h2>
            <p>Student information and preferences from the registration form</p>
        </div>
        <table class="oe-detail-table">
            <tbody>
                <tr>
                    <th>Student First Name</th>
                    <td>{{ $data->sfname ?? '' }}</td>
                </tr>
                <tr>
                    <th>Student Last Name</th>
                    <td>{{ $data->slname ?? '' }}</td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td>{{ $data->dob ?? '' }}</td>
                </tr>
                <tr>
                    <th>Preferred Start Date</th>
                    <td>{{ $data->start_date ?? '' }}</td>
                </tr>
                <tr>
                    <th>Times</th>
                    <td>
                        @if(is_array($data->time))
                            {{ implode(', ', $data->time) }}
                        @else
                            {{ $data->time }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Questions</th>
                    <td>{{ $data->questions ?? '' }}</td>
                </tr>
                <tr>
                    <th>Terms</th>
                    <td>{{ $data->terms ?? '' }}</td>
                </tr>
            </tbody>
        </table>
    </section>
</div>
</div>
@endsection
