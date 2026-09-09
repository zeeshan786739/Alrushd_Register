@extends('admin.layouts.app')

@section('title') Add Open Event-Item @endsection

@section('content')
<div class="dashboard-main-body" id="oe-workspace-page">
@include('admin.open-event.partials.shell', [
    'activeTab' => 'items',
    'compact' => true,
    'shellTitle' => 'Add event item',
    'shellSubtitle' => 'Create a session or registration slot linked to an open event.',
    'shellActions' => [[
        'label' => 'Back to items',
        'url' => route('admin.open-event-items.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation oe-form-page" novalidate action="{{ route('admin.open-event-items.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('POST')

    <div class="oe-form-grid">
        <section class="oe-form-card">
            <div class="oe-form-card__head">
                <span class="oe-form-card__icon"><iconify-icon icon="solar:checklist-linear"></iconify-icon></span>
                <div>
                    <h2 class="oe-form-card__title">Event item details</h2>
                    <p class="oe-form-card__sub">Session information families can register for</p>
                </div>
            </div>
            <div class="oe-form-card__body">
                <div class="oe-form-fields">
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Open Event</label>
                        <select name="open_events_id" id="open_events_id" class="form-control form-select radius-8 @error('open_events_id') is-invalid @enderror">
                            @foreach($event as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('open_events_id')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Title</label>
                        <input type="text" name="title" id="title" class="form-control radius-8 @error('title') is-invalid @enderror" value="{{ old('title') }}">
                        @error('title')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Text Name</label>
                        <input type="text" name="textname" id="textname" class="form-control radius-8 @error('textname') is-invalid @enderror" value="{{ old('textname') }}">
                        @error('textname')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Time</label>
                        <input type="text" name="time" id="time" class="form-control radius-8 @error('time') is-invalid @enderror" value="{{ old('time') }}">
                        @error('time')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Years</label>
                        <input type="text" name="year" id="year" class="form-control radius-8 @error('year') is-invalid @enderror" value="{{ old('year') }}">
                        @error('year')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Minutes</label>
                        <input type="text" name="minutes" id="minutes" class="form-control radius-8 @error('minutes') is-invalid @enderror" value="{{ old('minutes') }}">
                        @error('minutes')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field oe-form-field--full">
                        <label class="oe-form-field__label">Description</label>
                        <textarea name="description" id="description" class="form-control radius-8 @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                        @error('description')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Image</label>
                        <input type="file" name="image" id="image" class="form-control radius-8 @error('image') is-invalid @enderror">
                        @error('image')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                        <img id="previewImage" src="#" alt="Preview" class="oe-image-preview" style="display:none;">
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Status</label>
                        <select name="status" id="status" class="form-control form-select radius-8 @error('status') is-invalid @enderror">
                            <option value="1">Active</option>
                            <option value="0">Deactive</option>
                        </select>
                        @error('status')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </section>

        <div class="oe-form-save-bar">
            <a href="{{ route('admin.open-event-items.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
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
            } else {
                $('#previewImage').hide();
            }
        });
    });
</script>
@endsection
