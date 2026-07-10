@extends('admin.layouts.app')

@section('title') Referral Now Form @endsection

@section('content')
@include('admin.partials.wpapi-filter-bar', [
    'action' => route('admin.referral-applications'),
    'searchPlaceholder' => 'Search name, email, or date…',
])

<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-10">
            <h6 class="card-title text-primary mb-0">Referral List</h6>
            <form action="{{ route('admin.form.import.contact', 14889) }}" method="GET" class="d-inline mb-0">
                <button type="submit" class="btn btn-success btn-sm fc-btn">
                    <iconify-icon icon="solar:import-linear"></iconify-icon>
                    <span>Import</span>
                </button>
            </form>
        </div>
        <div class="card-body">
            <table class="table bordered-table mb-0" id="dataTable" data-page-length="10">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Date Submitted</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Country</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $entry)
                    <tr>
                        <td>{{ $entry->entry_id }}</td>
                        <td>{{ $entry->date_created }}</td>
                        <td>{{ $entry->name_2 }} {{ $entry->name_4 }}</td>
                        <td>{{ $entry->email_2 }}</td>
                        <td>{{ $entry->phone_2 }}</td>
                        <td>
                            @php
                                $address = $entry->address_7;
                                if (is_string($address)) {
                                    $address = json_decode($address, true);
                                }
                            @endphp
                            @if(is_array($address))
                                {{ $address['country'] ?? '' }}
                            @else
                                {{ $address ?? '' }}
                            @endif
                        </td>
                        <td>
                            @include('admin.partials.wp-api-status-badge', ['status' => $entry->status])
                        </td>
                        <td>
                            @include('admin.partials.table-actions', [
                                'viewUrl' => route('admin.referral-now-view', $entry->entry_id),
                                'canEdit' => false,
                                'canDelete' => false,
                            ])
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
