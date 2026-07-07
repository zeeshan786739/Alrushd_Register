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
                        <label class="form-label">Terms Description</label>
                        <div class="has-validation">
                            <textarea name="terms_description" id="terms_description" class=" summernote  form-control @error('terms_description') is-invalid @enderror" rows="4">{{$data->terms_description}}</textarea>
                            @error('terms_description')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Form Description</label>
                        <div class="has-validation">
                            <textarea name="form_description" id="form_description" class=" summernote  form-control @error('form_description') is-invalid @enderror" rows="4">{{$data->form_description}}</textarea>
                            @error('form_description')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Image One</label>
                        <div class="has-validation">
                            <input type="file" name="image_one" id="image_one" class="form-control @error('image_one') is-invalid @enderror" onchange="previewImage(event, 'image_one_preview')">
                            @error('image_one')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                            <div class="mt-2">
                                <img id="image_one_preview" src="{{ Storage::url($data->image_one) }}" alt="Image One Preview" class="img-thumbnail" style="max-height: 150px; max-width: 150px;">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Image Two</label>
                        <div class="has-validation">
                            <input type="file" name="image_two" id="image_two" class="form-control @error('image_two') is-invalid @enderror" onchange="previewImage(event, 'image_two_preview')">
                            @error('image_two')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                            <div class="mt-2">
                                <img id="image_two_preview" src="{{ Storage::url($data->image_two) }}" alt="Image Two Preview" class="img-thumbnail" style="max-height: 150px; max-width: 150px;">
                            </div>
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