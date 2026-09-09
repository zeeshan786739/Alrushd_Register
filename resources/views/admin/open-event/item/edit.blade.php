@extends('admin.layouts.app')

@section('title') Update Open Event-Item @endsection

@section('content')
<div class="dashboard-main-body" id="oe-workspace-page">
@include('admin.open-event.partials.shell', [
    'activeTab' => 'items',
    'compact' => true,
    'shellTitle' => 'Update event item',
    'shellSubtitle' => 'Edit session details linked to an open event.',
    'shellActions' => [[
        'label' => 'Back to items',
        'url' => route('admin.open-event-items.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation oe-form-page" novalidate action="{{ route('admin.open-event-items.update', $data->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

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
                                <option value="{{ $item->id }}" @selected($data->open_events_id == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('open_events_id')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Title</label>
                        <input value="{{ $data->title }}" type="text" name="title" id="title" class="form-control radius-8 @error('title') is-invalid @enderror">
                        @error('title')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Text Name</label>
                        <input value="{{ $data->textname }}" type="text" name="textname" id="textname" class="form-control radius-8 @error('textname') is-invalid @enderror">
                        @error('textname')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Time</label>
                        <input value="{{ $data->time }}" type="text" name="time" id="time" class="form-control radius-8 @error('time') is-invalid @enderror">
                        @error('time')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Years</label>
                        <input value="{{ $data->year }}" type="text" name="year" id="year" class="form-control radius-8 @error('year') is-invalid @enderror">
                        @error('year')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Minutes</label>
                        <input value="{{ $data->minutes }}" type="text" name="minutes" id="minutes" class="form-control radius-8 @error('minutes') is-invalid @enderror">
                        @error('minutes')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field oe-form-field--full">
                        <label class="oe-form-field__label">Description</label>
                        <textarea name="description" id="description" class="form-control radius-8 @error('description') is-invalid @enderror" rows="4">{!! $data->description !!}</textarea>
                        @error('description')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Image</label>
                        <input type="file" name="image" id="image" class="form-control radius-8 @error('image') is-invalid @enderror">
                        @error('image')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                        @if($data->image)
                            <img id="previewImage" src="{{ Storage::url($data->image) }}" alt="Preview" class="oe-image-preview">
                        @else
                            <img id="previewImage" src="#" alt="Preview" class="oe-image-preview" style="display:none;">
                        @endif
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
            <a href="{{ route('admin.open-event-items.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
            <button class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn" type="submit">
                <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                Update
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
