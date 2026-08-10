@extends('platform.layouts.app')

@section('title', 'Edit ' . $organization->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-24">
    <h6 class="fw-semibold mb-0">Edit School — {{ $organization->name }}</h6>
    <a href="{{ route('platform.schools.show', $organization) }}" class="btn btn-outline-secondary">Back</a>
</div>

@if($errors->any())
<div class="alert alert-danger radius-8">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="card radius-12 border-0 shadow-sm">
    <div class="card-body p-24">
        <form method="POST" action="{{ route('platform.schools.update', $organization) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">School name *</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $organization->name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug *</label>
                <input type="text" name="slug" class="form-control" required value="{{ old('slug', $organization->slug) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $organization->email) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact person</label>
                <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $organization->contact_name) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $organization->phone) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Website</label>
                <input type="text" name="website" class="form-control" value="{{ old('website', $organization->website) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control" value="{{ old('country', $organization->country) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Timezone</label>
                <input type="text" name="timezone" class="form-control" value="{{ old('timezone', $organization->timezone) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Internal notes</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $organization->notes) }}</textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-40">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
