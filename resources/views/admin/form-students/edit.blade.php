@extends('admin.layouts.app')

@section('title') Update Student Form @endsection

@section('content')

<div class="row gy-4 pt-5">
    <div class="col-lg-12 m-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title text-primary mb-0"><span class="icon">
                        <iconify-icon icon="fa-solid:edit"></iconify-icon>
                    </span> Primary Parent Information</h5>
                <a href="{{ route('admin.form-students.index') }}" class="btn btn-primary btn-sm">← Back</a>
            </div>

            <div class="card-body">
                <form class="row gy-3 needs-validation" novalidate action="{{ route('admin.form-students.update',$data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="col-md-4">
                        <label class="form-label">School</label>
                        <div class="has-validation">
                            <input readonly type="text" name="selected_school" id="selected_school" class="form-control @error('selected_school') is-invalid @enderror" value="{{$data->selected_school}}">
                            @error('selected_school')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Title</label>
                        <div class="has-validation">
                            <select name="title" id="title" class="form-control @error('title') is-invalid @enderror form-select" required>
                                <option value="">-- Select --</option>
                                <option value="Mr" {{ $data->title == 'Mr' ? 'selected' : '' }}>Mr</option>
                                <option value="Mrs" {{ $data->title == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                <option value="Miss" {{ $data->title == 'Miss' ? 'selected' : '' }}>Miss</option>
                                <option value="Ms" {{ $data->title == 'Ms' ? 'selected' : '' }}>Ms</option>
                                <option value="Mx" {{ $data->title == 'Mx' ? 'selected' : '' }}>Mx</option>
                                <option value="Dr" {{ $data->title == 'Dr' ? 'selected' : '' }}>Dr</option>
                                <option value="Prof" {{ $data->title == 'Prof' ? 'selected' : '' }}>Prof</option>
                                <option value="Rev" {{ $data->title == 'Rev' ? 'selected' : '' }}>Rev</option>
                                <option value="Sir" {{ $data->title == 'Sir' ? 'selected' : '' }}>Sir</option>
                                <option value="Dame" {{ $data->title == 'Dame' ? 'selected' : '' }}>Dame</option>
                                <option value="Lady" {{ $data->title == 'Lady' ? 'selected' : '' }}>Lady</option>
                                <option value="Lord" {{ $data->title == 'Lord' ? 'selected' : '' }}>Lord</option>
                            </select>
                            @error('title')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">First Name</label>
                        <div class="has-validation">
                            <input type="text" name="fname" id="fname" class="form-control @error('fname') is-invalid @enderror" value="{{$data->fname}}">
                            @error('fname')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <div class="has-validation">
                            <input type="text" name="lname" id="lname" class="form-control @error('lname') is-invalid @enderror" value="{{$data->lname}}">
                            @error('lname')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">RelationShip</label>
                        <div class="has-validation">
                            <select name="relationship" id="relationship" class="form-control  @error('relationship') is-invalid @enderror form-select" required>
                                @foreach ($relationship as $item)
                                <option value="Father" {{ $data->relationship == $item->name ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>

                            @error('relationship')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <div class="has-validation">
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{$data->email}}">
                            @error('email')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirm Email</label>
                        <div class="has-validation">
                            <input type="email" name="confirm_email" id="confirm_email" class="form-control @error('confirm_email') is-invalid @enderror" value="{{$data->confirm_email}}">
                            @error('confirm_email')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Mobile Number</label>
                        <div class="has-validation">
                            <input type="text" name="mobile_number" id="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" value="{{$data->mobile_number}}">
                            @error('mobile_number')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Home Telephone</label>
                        <div class="has-validation">
                            <input type="text" name="home_telephone" id="home_telephone" class="form-control @error('home_telephone') is-invalid @enderror" value="{{$data->home_telephone}}">
                            @error('home_telephone')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Work Number</label>
                        <div class="has-validation">
                            <input type="text" name="work_number" id="work_number" class="form-control @error('work_number') is-invalid @enderror" value="{{$data->work_number}}">
                            @error('work_number')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Address</label>
                        <div class="has-validation">
                            <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{$data->address}}">
                            @error('address')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apartment</label>
                        <div class="has-validation">
                            <input type="text" name="apartment" id="apartment" class="form-control @error('apartment') is-invalid @enderror" value="{{$data->apartment}}">
                            @error('apartment')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <div class="has-validation">
                            <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{$data->city}}">
                            @error('city')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Province</label>
                        <div class="has-validation">
                            <input type="text" name="province" id="province" class="form-control @error('province') is-invalid @enderror" value="{{$data->province}}">
                            @error('province')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Postal Code</label>
                        <div class="has-validation">
                            <input type="text" name="postal_code" id="postal_code" class="form-control @error('postal_code') is-invalid @enderror" value="{{$data->postal_code}}">
                            @error('postal_code')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Country</label>
                        <div class="has-validation">
                            <select name="country" id="country" class="form-select select2" required>
                                @foreach ($country as $item)
                                <option value="{{$item->name}}" {{ $data->country == $item->name ? 'selected' : '' }}>{{$item->name}}</option>
                                @endforeach
                            </select>

                            @error('country')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="row pt-4 mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Proof of ID </label>
                            <div class="has-validation">
                                <input value="{{$data->file1}}" type="file" name="file1" id="file1" class="form-control @error('file1') is-invalid @enderror">
                                @if($data->file1)
                                <!-- Button to open the image in a new tab -->
                                <a href="{{ Storage::url($data->file1) }}" target="_blank" class="mt-11 text-primary">
                                    Link Here
                                </a>
                                @endif

                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Proof of Address</label>
                            <div class="has-validation">
                                <input value="{{$data->file2}}" type="file" name="file2" id="file2" class="form-control @error('file2') is-invalid @enderror">
                                @if($data->file2)
                                <!-- Button to open the image in a new tab -->
                                <a href="{{ Storage::url($data->file2) }}" target="_blank" class="mt-11 text-primary">
                                     Link Here
                                </a>
                                @endif

                            </div>
                        </div>
                    </div>




                    <div class="col-md-12 text-end py-3">
                        <button class="btn btn-sm btn-success-600" type="submit"><span class="icon">
                                <iconify-icon icon="fa-solid:edit"></iconify-icon>
                            </span> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="row gy-4 pt-5">
    <div class="col-lg-12 m-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title text-primary mb-0"><span class="icon">
                        <iconify-icon icon="fa-solid:edit"></iconify-icon>
                    </span> Secondary Parent Information</h5>
                <a href="{{ route('admin.form-students.index') }}" class="btn btn-primary btn-sm">← Back</a>
            </div>

            <div class="card-body">
                <form class="row gy-3 needs-validation" novalidate action="{{ route('admin.form-students.update',$data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')


                    <div class="col-md-4">
                        <label class="form-label">Title</label>
                        <div class="has-validation">
                            <select name="secondary_title" id="secondary_title" class="form-control @error('secondary_title') is-invalid @enderror form-select" required>
                                <option value="">-- Select --</option>
                                <option value="Mr" {{ $data->secondary_title == 'Mr' ? 'selected' : '' }}>Mr</option>
                                <option value="Mrs" {{ $data->secondary_title == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                <option value="Miss" {{ $data->secondary_title == 'Miss' ? 'selected' : '' }}>Miss</option>
                                <option value="Ms" {{ $data->secondary_title == 'Ms' ? 'selected' : '' }}>Ms</option>
                                <option value="Mx" {{ $data->secondary_title == 'Mx' ? 'selected' : '' }}>Mx</option>
                                <option value="Dr" {{ $data->secondary_title == 'Dr' ? 'selected' : '' }}>Dr</option>
                                <option value="Prof" {{ $data->secondary_title == 'Prof' ? 'selected' : '' }}>Prof</option>
                                <option value="Rev" {{ $data->secondary_title == 'Rev' ? 'selected' : '' }}>Rev</option>
                                <option value="Sir" {{ $data->secondary_title == 'Sir' ? 'selected' : '' }}>Sir</option>
                                <option value="Dame" {{ $data->secondary_title == 'Dame' ? 'selected' : '' }}>Dame</option>
                                <option value="Lady" {{ $data->secondary_title == 'Lady' ? 'selected' : '' }}>Lady</option>
                                <option value="Lord" {{ $data->secondary_title == 'Lord' ? 'selected' : '' }}>Lord</option>
                            </select>
                            @error('secondary_title')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">First Name</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_fname" id="secondary_fname" class="form-control @error('secondary_fname') is-invalid @enderror" value="{{$data->secondary_fname}}">
                            @error('secondary_fname')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_lname" id="secondary_lname" class="form-control @error('secondary_lname') is-invalid @enderror" value="{{$data->secondary_lname}}">
                            @error('secondary_lname')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">RelationShip</label>
                        <div class="has-validation">
                            <select name="secondary_relationship" id="secondary_relationship" class="form-control  @error('secondary_relationship') is-invalid @enderror form-select" required>
                                @foreach ($relationship as $item)
                                <option value="Father" {{ $data->secondary_relationship == $item->name ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>

                            @error('secondary_relationship')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <div class="has-validation">
                            <input type="email" name="secondary_email" id="secondary_email" class="form-control @error('secondary_email') is-invalid @enderror" value="{{$data->secondary_email}}">
                            @error('secondary_email')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirm Email</label>
                        <div class="has-validation">
                            <input type="email" name="secondary_confirm_email" id="secondary_confirm_email" class="form-control @error('secondary_confirm_email') is-invalid @enderror" value="{{$data->secondary_confirm_email}}">
                            @error('secondary_confirm_email')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Mobile Number</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_mobile_number" id="secondary_mobile_number" class="form-control @error('secondary_mobile_number') is-invalid @enderror" value="{{$data->secondary_mobile_number}}">
                            @error('secondary_mobile_number')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Home Telephone</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_home_telephone" id="secondary_home_telephone" class="form-control @error('secondary_home_telephone') is-invalid @enderror" value="{{$data->secondary_home_telephone}}">
                            @error('secondary_home_telephone')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Work Number</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_work_number" id="secondary_work_number" class="form-control @error('secondary_work_number') is-invalid @enderror" value="{{$data->secondary_work_number}}">
                            @error('secondary_work_number')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Address</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_address" id="secondary_address" class="form-control @error('secondary_address') is-invalid @enderror" value="{{$data->secondary_address}}">
                            @error('secondary_address')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apartment</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_apartment" id="secondary_apartment" class="form-control @error('secondary_apartment') is-invalid @enderror" value="{{$data->secondary_apartment}}">
                            @error('secondary_apartment')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_city" id="secondary_city" class="form-control @error('secondary_city') is-invalid @enderror" value="{{$data->secondary_city}}">
                            @error('secondary_city')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Province</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_province" id="secondary_province" class="form-control @error('secondary_province') is-invalid @enderror" value="{{$data->secondary_province}}">
                            @error('secondary_province')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Postal Code</label>
                        <div class="has-validation">
                            <input type="text" name="secondary_postal_code" id="secondary_postal_code" class="form-control @error('secondary_postal_code') is-invalid @enderror" value="{{$data->secondary_postal_code}}">
                            @error('secondary_postal_code')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Country</label>
                        <div class="has-validation">
                            <select name="secondary_country" id="secondary_country" class="form-select select2" required>
                                @foreach ($country as $item)
                                <option value="{{$item->name}}" {{ $data->secondary_country == $item->name ? 'selected' : '' }}>{{$item->name}}</option>
                                @endforeach
                            </select>

                            @error('secondary_country')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="row pt-4 mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Proof of ID </label>
                            <div class="has-validation">
                                <input value="{{$data->file3}}" type="file" name="file3" id="file3" class="form-control @error('file3') is-invalid @enderror">
                                @if($data->file3)
                                <!-- Button to open the image in a new tab -->
                                <a href="{{ Storage::url($data->file3) }}" target="_blank" class="mt-11 text-primary">
                                     Link Here
                                </a>
                                @endif

                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Proof of Address</label>
                            <div class="has-validation">
                                <input value="{{$data->file4}}" type="file" name="file4" id="file4" class="form-control @error('file4') is-invalid @enderror">
                                @if($data->file4)
                                <!-- Button to open the image in a new tab -->
                                <a href="{{ Storage::url($data->file4) }}" target="_blank" class="mt-11 text-primary">
                                     Link Here
                                </a>
                                @endif

                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 text-end py-3">
                        <button class="btn btn-sm btn-success-600" type="submit"><span class="icon">
                                <iconify-icon icon="fa-solid:edit"></iconify-icon>
                            </span> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<div class="row gy-4 pt-5">
    <div class="col-lg-12 m-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title text-primary mb-0"><span class="icon">
                        <iconify-icon icon="fa-solid:edit"></iconify-icon>
                    </span> Additional Information</h5>
                <a href="{{ route('admin.form-students.index') }}" class="btn btn-primary btn-sm">← Back</a>
            </div>

            <div class="card-body">
                <form class="row gy-3 needs-validation" novalidate action="{{ route('admin.form-students.update',$data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')



                    <div class="col-md-12">
                        <label class="form-label">An Education & Health Care plan (EHCP) is a formal document detailing a child's learning difficulties and the help they will be given. Does the child have an Education Health Care Plan?</label>
                        <div>
                            <input type="radio" name="health_care" id="health_care" class="form-check-input" value="1" {{ $data->health_care==1 ? 'checked' : '' }}> <span>Yes</span>
                            <input type="radio" name="health_care" id="health_care" class="form-check-input" value="0" {{ $data->health_care==0 ? 'checked' : '' }}> <span>No</span>

                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Permanent Exclusions : Has this child been permanently excluded (expelled) from their previous school?</label>
                        <div>
                            <input type="radio" name="previus_school" id="previus_school" class="form-check-input" value="1" {{ $data->previus_school==1 ? 'checked' : '' }}> <span>Yes</span>
                            <input type="radio" name="previus_school" id="previus_school" class="form-check-input" value="0" {{ $data->previus_school==0 ? 'checked' : '' }}> <span>No</span>

                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Fair Access Protocol: (Checkboxes option for the list below) - Does the child fall under any of the below listed categories of the Fair Access Protocol?</label>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="9" {{ $data->access_protocol==9 ? 'checked' : '' }}> <span>None</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="0" {{ $data->access_protocol==0 ? 'checked' : '' }}> <span>Children subject to a child in need plan or a child protection plan within the last 12 months</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="1" {{ $data->access_protocol==1 ? 'checked' : '' }}> <span>Children living in a refuge</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="2" {{ $data->access_protocol==2 ? 'checked' : '' }}> <span>Children from the criminal justice system</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="3" {{ $data->access_protocol==3 ? 'checked' : '' }}> <span>Children who are carers</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="4" {{ $data->access_protocol==4 ? 'checked' : '' }}> <span>Children who are homeless</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="5" {{ $data->access_protocol==5 ? 'checked' : '' }}> <span>Children in formal kinship care arrangements</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="6" {{ $data->access_protocol==6 ? 'checked' : '' }}> <span>Children of, or who are, Gypsies, Roma or Travellers</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="7" {{ $data->access_protocol==7 ? 'checked' : '' }}> <span>Children who are refugees or asylum seekers</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="8" {{ $data->access_protocol==8 ? 'checked' : '' }}> <span>Children who have been out of education for four weeks or more</span>
                        </div>
                        <div>
                            <input type="radio" name="access_protocol" id="access_protocol" class="form-check-input" value="10" {{ $data->access_protocol==10 ? 'checked' : '' }}> <span>Other</span>
                        </div>


                    </div>

                    <div class="col-md-6">
                        <label class="form-label">If any of these apply, provide the supporting local authority</label>
                        <div class="has-validation">
                            <input type="text" name="authority" id="authority" class="form-control @error('authority') is-invalid @enderror" value="{{$data->authority}}">
                            @error('authority')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Provide the name of the assigned social worker</label>
                        <div class="has-validation">
                            <input type="text" name="assigned" id="assigned" class="form-control @error('assigned') is-invalid @enderror" value="{{$data->assigned}}">
                            @error('assigned')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Is this child on the special educational needs and disabilities code of practice</label>
                        <div>
                            <input type="radio" name="special_education" id="special_education" class="form-check-input" value="1" {{ $data->special_education==1 ? 'checked' : '' }}> <span>Yes</span>
                            <input type="radio" name="special_education" id="special_education" class="form-check-input" value="0" {{ $data->special_education==0 ? 'checked' : '' }}> <span>No</span>

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Does the child have any long term medical conditions?</label>
                        <div>
                            <input type="radio" name="medical_condition" id="medical_condition" class="form-check-input" value="1" {{ $data->medical_condition==1 ? 'checked' : '' }}> <span>Yes</span>
                            <input type="radio" name="medical_condition" id="medical_condition" class="form-check-input" value="0" {{ $data->medical_condition==0 ? 'checked' : '' }}> <span>No</span>

                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Has the child been directed to an Alternative Provision to improve their behaviour?</label>
                        <div>
                            <input type="radio" name="direct_placement" id="direct_placement" class="form-check-input" value="1" {{ $data->direct_placement==1 ? 'checked' : '' }}> <span>Yes</span>
                            <input type="radio" name="direct_placement" id="direct_placement" class="form-check-input" value="0" {{ $data->direct_placement==0 ? 'checked' : '' }}> <span>No</span>

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Alternative Provision</label>
                        <div class="has-validation">
                            <input type="text" name="placement_detail" id="placement_detail" class="form-control @error('placement_detail') is-invalid @enderror" value="{{$data->placement_detail}}">
                            @error('placement_detail')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Attendance percentage</label>
                        <div>
                            <input type="text" name="percentage" id="percentage" class="form-control @error('percentage') is-invalid @enderror" value="{{$data->percentage}}">
                            @error('percentage')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>




                    <div class="col-md-12 text-end py-3">
                        <button class="btn btn-sm btn-success-600" type="submit"><span class="icon">
                                <iconify-icon icon="fa-solid:edit"></iconify-icon>
                            </span> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="row gy-4 pt-5">
    <div class="col-lg-12 m-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title text-primary mb-0"><span class="icon">
                        <iconify-icon icon="fa-solid:edit"></iconify-icon>
                    </span> Payment Information</h5>
                <a href="{{ route('admin.form-students.index') }}" class="btn btn-primary btn-sm">← Back</a>
            </div>

            <div class="card-body">
                <form class="row gy-3 needs-validation" novalidate action="{{ route('admin.form-students.update',$data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')


                    <div class="col-md-6">
                        <label class="form-label">Signature Accept</label>
                        <div class="has-validation">
                            <input type="text" name="signature_accept" id="signature_accept" class="form-control @error('signature_accept') is-invalid @enderror" value="{{$data->signature_accept}}">
                            @error('signature_accept')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Email</label>
                        <div class="has-validation">
                            <input type="text" name="payment_email" id="payment_email" class="form-control @error('payment_email') is-invalid @enderror" value="{{$data->payment_email}}">
                            @error('payment_email')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Country</label>
                        <div class="has-validation">
                            <select name="payment_country" id="payment_country" class="form-select select2 @error('payment_country') is-invalid @enderror">
                                @foreach ($paymentcountry as $item)
                                <option value="{{$item->name}}" {{ $data->payment_country == $item->name ? 'selected' : '' }}>{{$item->name}}</option>
                                @endforeach
                            </select>
                            @error('payment_country')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Postal Code</label>
                        <div class="has-validation">
                            <input type="text" name="payment_postal_code" id="payment_postal_code" class="form-control @error('payment_postal_code') is-invalid @enderror" value="{{$data->payment_postal_code}}">
                            @error('payment_postal_code')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Accept</label>
                        <div class="has-validation">

                            <div>
                                <input type="radio" name="payment_accept" id="payment_accept" class="form-check-input" value="1" {{ $data->payment_accept==1 ? 'checked' : '' }}> <span>Yes</span>
                                <input type="radio" name="payment_accept" id="payment_accept" class="form-check-input" value="0" {{ $data->payment_accept==0 ? 'checked' : '' }}> <span>No</span>

                            </div>

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Total Amount</label>
                        <div class="has-validation">
                            <input type="text" name="total_amount" id="total_amount" class="form-control @error('total_amount') is-invalid @enderror" value="{{$data->total_amount}}">
                            @error('total_amount')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Paid Amount</label>
                        <div class="has-validation">
                            <input type="text" name="paid_amount" id="paid_amount" class="form-control @error('paid_amount') is-invalid @enderror" value="{{$data->paid_amount}}">
                            @error('paid_amount')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Transaction ID</label>
                        <div class="has-validation">
                            <input type="text" name="transaction_id" id="transaction_id" class="form-control @error('transaction_id') is-invalid @enderror" value="{{$data->transaction_id}}">
                            @error('transaction_id')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Card Holder Name</label>
                        <div class="has-validation">
                            <input type="text" name="card_holder_name" id="card_holder_name" class="form-control @error('card_holder_name') is-invalid @enderror" value="{{$data->card_holder_name}}">
                            @error('card_holder_name')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Currency</label>
                        <div class="has-validation">
                            <input type="text" name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" value="{{$data->currency}}">
                            @error('currency')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Date</label>
                        <div class="has-validation">
                            <input type="date" name="payment_date" id="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ \Carbon\Carbon::parse($data->payment_date)->format('Y-m-d') }}">
                            @error('payment_date')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Signature </label>
                        <div class="has-validation">
                            <input value="{{$data->signature}}" type="file" name="signature" id="signature" class="p-1 form-control @error('signature') is-invalid @enderror">
                            @if($data->signature)
                            <!-- Button to open the image in a new tab -->
                            <a href="{{ Storage::url($data->signature) }}" target="_blank">
                                <img src="{{ Storage::url($data->signature) }}" alt="">
                            </a>
                            @endif

                        </div>
                    </div>


                    <div class="col-md-12 text-end py-3">
                        <button class="btn btn-sm btn-success-600" type="submit"><span class="icon">
                                <iconify-icon icon="fa-solid:edit"></iconify-icon>
                            </span> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<div class="row gy-4 pt-5">
    <div class="col-lg-12 m-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title text-primary mb-0"><span class="icon">
                        <iconify-icon icon="fa-solid:edit"></iconify-icon>
                    </span> Package Information</h5>
                <a href="{{ route('admin.form-students.index') }}" class="btn btn-primary btn-sm">← Back</a>
            </div>

            <div class="card-body">
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
    </div>
</div>





@foreach ($data->students as $student)

<div class="row gy-4 pt-5">
    <div class="col-lg-12 m-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title text-primary mb-0"><span class="icon">
                        <iconify-icon icon="fa-solid:edit"></iconify-icon>
                    </span> Student Update {{$loop->iteration}}</h5>
                <a href="{{ route('admin.form-students.index') }}" class="btn btn-primary btn-sm">← Back</a>
            </div>

            <div class="card-body">
                <form class="row gy-3 needs-validation" novalidate action="{{ route('admin.form-student-single.update',$student->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')


                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <div class="has-validation">
                            <input type="text" name="fname" id="fname" class="form-control @error('fname') is-invalid @enderror" value="{{$student->fname}}">
                            @error('fname')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <div class="has-validation">
                            <input type="text" name="lname" id="lname" class="form-control @error('lname') is-invalid @enderror" value="{{$student->lname}}">
                            @error('lname')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <div class="has-validation">
                            <input type="text" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror" value="{{$student->dob}}">
                            @error('dob')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <div class="has-validation">
                           <select name="gender" id="gender" class="form-select form-control">
                            @foreach ($genders as $gender)
                                <option value="{{$gender->name}}" {{ $student->gender==$gender->name ? 'selected' : '' }}>{{$gender->name}}</option>
                            @endforeach
                           </select>
                            @error('gender')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="col-md-6">
                        <label class="form-label">Nationality</label>
                        <div class="has-validation">
                           <select name="nationality" id="nationality" class="form-select form-control">
                            @foreach ($nationalities as $item)
                                <option value="{{$item->name}}" {{ $student->nationality==$item->name ? 'selected' : '' }}>{{$item->name}}</option>
                            @endforeach
                           </select>
                            @error('nationality')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>


                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <div class="has-validation">
                           <select name="start_date" id="start_date" class="form-select form-control">
                            @foreach ($admissiondate as $item)
                                <option value="{{$item->date}}" {{ $student->date==$item->name ? 'selected' : '' }}>{{$item->date}}</option>
                            @endforeach
                           </select>
                            @error('start_date')
                            <span class="text-danger">{{$message}}</span>
                            @enderror

                        </div>
                    </div>
                    
                
                    <div class="col-md-12 text-end py-3">
                        <button class="btn btn-sm btn-success-600" type="submit"><span class="icon">
                                <iconify-icon icon="fa-solid:edit"></iconify-icon>
                            </span> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    
@endforeach


@endsection