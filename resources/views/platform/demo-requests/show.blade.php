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

@if(session('success'))
<div class="alert alert-success radius-8 mb-24">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger radius-8 mb-24">{{ session('error') }}</div>
@endif

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
                    @if($demoRequest->access_granted_at)
                    <div class="col-md-6"><strong>Access granted:</strong> {{ $demoRequest->access_granted_at->format('d M Y H:i') }}</div>
                    @endif
                    @if($demoRequest->message)
                    <div class="col-12"><strong>Message:</strong><br>{{ $demoRequest->message }}</div>
                    @endif
                    @if($demoRequest->convertedOrganization)
                    <div class="col-12">
                        <strong>School workspace:</strong>
                        <a href="{{ route('platform.schools.show', $demoRequest->convertedOrganization) }}" class="text-primary-600">
                            {{ $demoRequest->convertedOrganization->name }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($demoRequest->canGrantAccess())
        <div class="card radius-12 border-0 shadow-sm mb-24">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Approve Demo Access</h6></div>
            <div class="card-body p-24">
                <p class="text-secondary-light text-sm mb-16">
                    Creates a school workspace and emails the applicant a secure link to set their password (then redirects to login).
                </p>
                <form method="POST" action="{{ route('platform.demo-requests.approve', $demoRequest) }}"
                      data-confirm
                      data-confirm-title="Grant demo access?"
                      data-confirm-text="This creates a school workspace and emails a set-password link to {{ $demoRequest->email }}."
                      data-confirm-label="Approve & Email Access"
                      data-confirm-icon="question"
                      data-confirm-tone="success">
                    @csrf
                    <label class="form-label">Plan for demo workspace</label>
                    <select name="saas_plan_id" class="form-select mb-16">
                        <option value="">Default plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-success w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <iconify-icon icon="solar:check-circle-linear"></iconify-icon> Approve &amp; Email Access
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if($demoRequest->status?->value !== 'converted' && $demoRequest->status?->value !== 'approved')
        <a href="{{ route('platform.schools.create', ['demo_request_id' => $demoRequest->id]) }}" class="btn btn-outline-success d-inline-flex align-items-center gap-2">
            <iconify-icon icon="solar:buildings-2-linear"></iconify-icon> Convert manually
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
                      data-confirm
                      data-confirm-title="Delete this demo request?"
                      data-confirm-text="This cannot be undone."
                      data-confirm-label="Yes, delete"
                      data-confirm-icon="warning"
                      data-confirm-tone="danger">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">Delete Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
