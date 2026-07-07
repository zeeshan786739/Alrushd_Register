@extends('admin.layouts.app')

@section('title') Update Terms & Conditions @endsection

@section('content')

<div class="row gy-4 pt-5">
    <div class="col-lg-12 m-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title text-primary mb-0"><span class="icon">
                        <iconify-icon icon="fa-solid:edit"></iconify-icon>
                    </span> Update Terms & Conditions</h5>
            </div>
            <div class="card-body">
                <form class="row gy-3 needs-validation" novalidate action="{{ route('admin.terms.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                     <div class="col-md-12">
                        <label class="form-label">Staff Terms Description</label>
                        <div class="has-validation">
                            <textarea name="staff_terms_condition" id="staff_terms_condition" class=" summernote  form-control @error('staff_terms_condition') is-invalid @enderror" rows="4">{{$data->staff_terms_condition}}</textarea>
                            @error('staff_terms_condition')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                    </div>




                    <div class="col-md-12 text-end">
                        <button class="btn btn-sm btn-success-600" type="submit"><span class="icon">
                                <iconify-icon icon="fa-solid:edit"></iconify-icon>
                            </span> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function previewImage(event, previewId) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById(previewId);
            output.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection