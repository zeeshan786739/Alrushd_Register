@extends('platform.layouts.app')

@section('title', 'Demo Request — ' . $demoRequest->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-24">
    <div class="d-flex align-items-center gap-3">
        <h6 class="fw-semibold mb-0">Demo Request — {{ $demoRequest->name }}</h6>
        <span class="badge platform-badge {{ $demoRequest->status?->badgeClass() }}">{{ $demoRequest->status?->label() }}</span>
    </div>
    <a href="{{ route('platform.demo-requests.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="row gy-4">
    <div class="col-lg-7">
        <div class="card radius-12 border-0 shadow-sm mb-24">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Request Details</h6></div>
            <div class="card-body p-24">
                <div class="row g-3 text-sm">
                    <div class="col-md-6"><strong>Name:</strong> {{ $demoRequest->name }}</div>
                    <div class="col-md-6"><strong>Email:</strong> <a href="mailto:{{ $demoRequest->email }}">{{ $demoRequest->email }}</a></div>
                    <div class="col-md-6"><strong>Phone:</strong> {{ $demoRequest->phone ?? '—' }}</div>
                    <div class="col-md-6"><strong>Organisation:</strong> {{ $demoRequest->organization_name ?? '—' }}</div>
                    <div class="col-md-6"><strong>Type:</strong> {{ $demoRequest->organization_type ?? '—' }}</div>
                    <div class="col-md-6"><strong>Country:</strong> {{ $demoRequest->country ?? '—' }}</div>
                    <div class="col-md-6"><strong>Students:</strong> {{ $demoRequest->students_count ?? '—' }}</div>
                    <div class="col-md-6"><strong>Received:</strong> {{ $demoRequest->created_at->format('d M Y H:i') }}</div>
                    @if($demoRequest->message)
                    <div class="col-12"><strong>Message:</strong><br>{{ $demoRequest->message }}</div>
                    @endif
                    @if($demoRequest->convertedOrganization)
                    <div class="col-12">
                        <strong>Converted to:</strong>
                        <a href="{{ route('platform.schools.show', $demoRequest->convertedOrganization) }}" class="text-primary-600">
                            {{ $demoRequest->convertedOrganization->name }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($demoRequest->status?->value !== 'converted')
        <a href="{{ route('platform.schools.create', ['demo_request_id' => $demoRequest->id]) }}" class="btn btn-success d-inline-flex align-items-center gap-2">
            <iconify-icon icon="solar:buildings-2-linear"></iconify-icon> Convert to School
        </a>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card radius-12 border-0 shadow-sm">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Manage</h6></div>
            <div class="card-body p-24">
                <form method="POST" action="{{ route('platform.demo-requests.update', $demoRequest) }}">
                    @csrf
                    @method('PUT')
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select mb-12">
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected($demoRequest->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <label class="form-label">Internal notes</label>
                    <textarea name="internal_notes" rows="5" class="form-control mb-12" placeholder="Call notes, follow-ups…">{{ old('internal_notes', $demoRequest->internal_notes) }}</textarea>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
                <form method="POST" action="{{ route('platform.demo-requests.destroy', $demoRequest) }}" class="mt-12"
                      onsubmit="return confirm('Delete this demo request?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">Delete Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
