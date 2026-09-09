@extends('admin.layouts.app')

@section('title') Update Meet Speaker @endsection

@section('content')
<div class="dashboard-main-body" id="oe-workspace-page">
@include('admin.open-event.partials.shell', [
    'activeTab' => 'speakers',
    'compact' => true,
    'shellTitle' => 'Update speaker',
    'shellSubtitle' => 'Edit meet-the-speaker profile details.',
    'shellActions' => [[
        'label' => 'Back to speakers',
        'url' => route('admin.meet-speakers.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation oe-form-page" novalidate action="{{ route('admin.meet-speakers.update', $data->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="oe-form-grid">
        <section class="oe-form-card">
            <div class="oe-form-card__head">
                <span class="oe-form-card__icon"><iconify-icon icon="solar:microphone-linear"></iconify-icon></span>
                <div>
                    <h2 class="oe-form-card__title">Speaker profile</h2>
                    <p class="oe-form-card__sub">Displayed during open event registration</p>
                </div>
            </div>
            <div class="oe-form-card__body">
                <div class="oe-form-fields">
                    <div class="oe-form-field oe-form-field--full">
                        <label class="oe-form-field__label">Name</label>
                        <input value="{{ $data->name }}" type="text" name="name" id="name" class="form-control radius-8 @error('name') is-invalid @enderror">
                        @error('name')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field oe-form-field--full">
                        <label class="oe-form-field__label">Designation</label>
                        <input value="{{ $data->designation }}" type="text" name="designation" id="designation" class="form-control radius-8 @error('designation') is-invalid @enderror">
                        @error('designation')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Image</label>
                        <input type="file" name="image" id="image" class="form-control radius-8 @error('image') is-invalid @enderror">
                        @error('image')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                        <img id="previewImage" src="{{ Storage::url($data->image) }}" alt="Preview" class="oe-image-preview">
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Status</label>
                        <select name="status" id="status" class="form-control form-select radius-8 @error('status') is-invalid @enderror">
                            <option value="1" @selected($data->status == 1)>Active</option>
                            <option value="0" @selected($data->status == 0)>Deactive</option>
                        </select>
                        @error('status')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </section>

        <div class="oe-form-save-bar">
            <a href="{{ route('admin.meet-speakers.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
            <button class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn" type="submit">
                <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                Save
            </button>
        </div>
    </div>
</form>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        $('#image').on('change', function (e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (event) {
                    $('#previewImage').attr('src', event.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endsection
