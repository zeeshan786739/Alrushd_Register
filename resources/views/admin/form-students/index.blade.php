@extends('admin.layouts.app')

@section('title') Form Students @endsection

@section('content')


<!-- 🔍 Filter Form -->
<form method="GET" action="{{ route('admin.form-students.index') }}" class="bg-primary mt-3 mb-5 mx-2 p-10 rounded row text-light" style="text-align: left;">

    <div class="col-lg-3">
        <label class="fw-bold" for="search">Search Here</label>
        <input type="text" name="search" class="form-control search"
            placeholder="Search School,Name, Number or Email..."
            value="{{ request('search') }}">
    </div>
    <div class="col-lg-3">
        <label class="fw-bold" for="start_date">Year</label>
        <select name="year_id" id="year_id" class="form-select select2 search_select">
            <option value="">Select Year</option>
            @foreach (\App\Models\StudentYear::all() as $item)
            <option value="{{ $item->name }}" {{ request('year_id') == $item->name ? 'selected' : '' }}>{{ $item->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3">
        <label class="fw-bold" for="start_date">Start Date</label>
    <input type="date" name="start_date" class="form-control search" value="{{ request('start_date') }}">
    </div>
    <div class="col-lg-3">
            <label class="fw-bold" for="end_date">End Date</label>
    <input type="date" name="end_date" class="form-control search" value="{{ request('end_date') }}">
    </div>
    <div class="col-lg-2 mt-24 text-end ms-auto">
        <button type="submit" class="btn btn-warning">Filter</button>
    <a href="{{ route('admin.form-students.index') }}" class="btn btn-dark">Reset</a>
    </div>

</form>


<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0 text-lg">Form Students Lists</h6>
             <!-- ✅ Pagination Section -->
            <div class="d-flex justify-content-end mt-5">
                {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto; width: 100%;">
                <table class="table table-bordered mb-0 w-100" style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th scope="col">
                                <div class="form-check style-check d-flex align-items-center">

                                    <label class="form-check-label">
                                        S.L
                                    </label>
                                </div>
                            </th>

                            <th>
                               Date Of Submission
                            </th>
                            {{-- <th>
                                School
                            </th> --}}
                            {{-- <th>Name</th>

                            <th>Email</th>

                            <th>Phone</th> --}}
                            <th>Parent(Info)</th>
                            <th>Student(Info)</th>
                            <th style="width: 13%">
                                Amount
                            </th>
                            {{-- <th>Total Amount</th>
                            <th>Paid Amount</th> --}}

                            <th>Status</th>

                            <th style="width: 12%">
                                Action
                            </th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        @can('view coursefee')
                        @if($item->fname)
                        <tr>
                            <td>
                                <div class="form-check style-check d-flex align-items-center">

                                    <label class="form-check-label">
                                        {{ $loop->iteration }}
                                    </label>
                                </div>
                            </td>

                            <td>
                                <b>{{ $item->created_at->format('Y-m-d') }}</b>
                            </td>

                            {{-- <td>
                                {{  $item->selected_school }}
                            </td> --}}

                            <td>
                                <p class="mb-0"><b>Name :</b> {{ $item->title }} {{ $item->fname }} {{ $item->lname }}</p>
                                <p class="mb-0"><b>Email :</b> {{ $item->email}}</p>
                                <p class="mb-0"><b>Phone :</b> {{ $item->mobile_number}}</p>
                            </td>
                            
                            <td>
                                @foreach ($item->students as $student)
                                <p class="mb-0"><b>Name :</b> {{ $student->fname }} {{ $student->lname }}</p>
                                <p class="mb-0"><b>Year :</b> {{ $student->year->name}}</p>
                                @endforeach
                            </td>

                            {{-- <td>
                                {{ $item->title }} {{ $item->fname }} {{ $item->lname }}
                            </td>

                            <td>
                                {{ $item->email}}
                            </td>

                            <td>
                                {{ $item->mobile_number}}
                            </td> --}}
                            <td>
                                <p class="mb-0"><b>Total :</b> @if($item->total_amount)
                                £{{ $item->total_amount}}
                                @else
                                <span>N/A</span>
                                @endif</p>
                                <p class="mb-0"><b>Paid :</b> @if($item->paid_amount)
                                £{{ $item->paid_amount}}
                                @else
                                <span>N/A</span>
                                @endif</p>
                            </td>

                            {{-- <td>
                                @if($item->total_amount)
                                £{{ $item->total_amount}}
                                @else
                                <span>N/A</span>
                                @endif
                            </td>

                            <td>
                                @if($item->paid_amount)
                                £{{ $item->paid_amount}}
                                @else
                                <span>N/A</span>
                                @endif
                            </td> --}}

                            <td>
                                @if($item->status=='paid')
                                <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Paid</span>
                                @else
                                <span class="bg-danger-focus text-light-main px-24 py-4 rounded-pill fw-medium text-sm">Progress</span>
                                @endif
                            </td>

                            <td>
                                 @can('view coursefee')
                                <a href="{{ route('admin.form-students.show', $item->id) }}"
                                    class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                </a>
                                @endcan
                                @can('edit coursefee')
                                <a href="{{ route('admin.form-students.edit', $item->id) }}"
                                    class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>
                                @endcan
                                @can('delete coursefee')
                                <form id="delete-form-{{ $item->id }}" action="{{ route('admin.form-students.destroy', $item->id) }}" method="POST" style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                                <a href="javascript:void(0)" data-id="{{ $item->id }}"
                                    class="delete-btn w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endif
                        @endcan
                        @endforeach



                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
@section('script')
<script>
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete-form-' + id).submit();
            }
        });
    });
</script>
@endsection
@endsection