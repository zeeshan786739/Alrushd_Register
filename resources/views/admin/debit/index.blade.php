@extends('admin.layouts.app')

@section('title') Debit Form @endsection

@section('content')

<!-- 🔍 Filter Form -->
<form method="GET" action="{{ route('admin.debit-forms.index') }}"
    class="bg-primary mt-3 mb-5 mx-2 p-10 rounded row text-light" style="text-align: left;">

    <div class="col-lg-4">
        <label class="fw-bold" for="search">Search <span class="text-sm">SureName Number or Email</span></label>
        <input type="text" name="search" class="form-control search" placeholder="Search SureName Number or Email..."
            value="{{ request('search') }}">
    </div>
    <div class="col-lg-3">
        <label class="fw-bold" for="start_date">Start Date</label>
        <input type="date" name="start_date" class="form-control search" value="{{ request('start_date') }}">
    </div>
    <div class="col-lg-3">
        <label class="fw-bold" for="end_date">End Date</label>
        <input type="date" name="end_date" class="form-control search" value="{{ request('end_date') }}">
    </div>
    <div class="col-lg-2 mt-24">
        <button type="submit" class="btn btn-warning">Filter</button>
        <a href="{{ route('admin.debit-forms.index') }}" class="btn btn-dark">Reset</a>
    </div>

</form>

<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">Debit Lists</h6>

            <!-- ✅ Pagination Section -->
            <div class="d-flex justify-content-end mt-5">
                {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
        <div class="card-body">
            <table class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">
                            <div class="form-check style-check d-flex align-items-center">

                                <label class="form-check-label">
                                    S.L
                                </label>
                            </div>
                        </th>
                        <th scope="col">Forename</th>
                        <th scope="col">Surename</th>
                        <th scope="col">Country</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone Number</th>
                        <th scope="col">Debit Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)

                    <tr>
                        <td>
                            <div class="form-check style-check d-flex align-items-center">

                                <label class="form-check-label">
                                    {{ $loop->iteration }}
                                </label>
                            </div>
                        </td>
                        <td>{{ $item->forename }}</td>
                        <td>{{ $item->surename }}</td>
                        <td>{{ $item->p_country }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->mobile_number }}</td>
                        <td>{{ $item->debit_date }}</td>

                        <td>

                            <a href="{{ route('admin.debit-forms.show', $item->id) }}"
                                class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                            </a>


                            <form id="delete-form-{{ $item->id }}"
                                action="{{ route('admin.debit-forms.destroy', $item->id) }}" method="POST"
                                style="display: none;">
                                @csrf @method('DELETE')
                            </form>
                            <a href="javascript:void(0)" data-id="{{ $item->id }}"
                                class="delete-btn w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                            </a>

                        </td>
                    </tr>

                    @endforeach



                </tbody>
            </table>

        </div>
    </div>
@endsection
