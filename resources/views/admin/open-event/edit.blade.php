@extends('admin.layouts.app')

@section('title') Update Open Events @endsection

@section('content')
<div class="dashboard-main-body" id="oe-workspace-page">
@include('admin.open-event.partials.shell', [
    'activeTab' => 'events',
    'compact' => true,
    'shellTitle' => 'Update open event',
    'shellSubtitle' => 'Edit event details for your public registration page.',
    'shellActions' => [[
        'label' => 'Back to events',
        'url' => route('admin.open-events.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation oe-form-page" novalidate action="{{ route('admin.open-events.update', $data->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="oe-form-grid">
        <section class="oe-form-card">
            <div class="oe-form-card__head">
                <span class="oe-form-card__icon"><iconify-icon icon="solar:calendar-mark-linear"></iconify-icon></span>
                <div>
                    <h2 class="oe-form-card__title">Event details</h2>
                    <p class="oe-form-card__sub">Basic information shown on the public open events page</p>
                </div>
            </div>
            <div class="oe-form-card__body">
                <div class="oe-form-fields">
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Name</label>
                        <input value="{{ $data->name }}" type="text" name="name" id="name" class="form-control radius-8 @error('name') is-invalid @enderror">
                        @error('name')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field">
                        <label class="oe-form-field__label">Title</label>
                        <input value="{{ $data->title }}" type="text" name="title" id="title" class="form-control radius-8 @error('title') is-invalid @enderror">
                        @error('title')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field oe-form-field--full">
                        <label class="oe-form-field__label">Description</label>
                        <textarea name="description" id="description" class="form-control radius-8 @error('description') is-invalid @enderror" rows="4">{!! $data->description !!}</textarea>
                        @error('description')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="oe-form-field oe-form-field--full">
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
            <a href="{{ route('admin.open-events.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">Cancel</a>
            <button class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn" type="submit">
                <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                Update
            </button>
        </div>
    </div>
</form>
</div>
@endsection
