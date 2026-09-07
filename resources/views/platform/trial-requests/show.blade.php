@extends('platform.layouts.app')

@section('title', 'Trial Request — ' . $trialRequest->school_name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-24">
    <div class="d-flex align-items-center gap-3">
        <h6 class="fw-semibold mb-0">Trial Request — {{ $trialRequest->school_name }}</h6>
        <span class="badge platform-badge {{ $trialRequest->status?->badgeClass() }}">{{ $trialRequest->status?->label() }}</span>
    </div>
    <a href="{{ route('platform.trial-requests.index') }}" class="btn btn-outline-secondary">Back</a>
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
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Application Details</h6></div>
            <div class="card-body p-24">
                <div class="row g-3 text-sm">
                    <div class="col-md-6"><strong>School:</strong> {{ $trialRequest->school_name }}</div>
                    <div class="col-md-6"><strong>Plan:</strong> {{ $trialRequest->plan?->name ?? '—' }}</div>
                    <div class="col-md-6"><strong>Admin name:</strong> {{ $trialRequest->admin_name }}</div>
                    <div class="col-md-6"><strong>Admin email:</strong> <a href="mailto:{{ $trialRequest->admin_email }}">{{ $trialRequest->admin_email }}</a></div>
                    <div class="col-md-6"><strong>Phone:</strong> {{ $trialRequest->phone ?? '—' }}</div>
                    <div class="col-md-6"><strong>Country:</strong> {{ $trialRequest->country ?? '—' }}</div>
                    <div class="col-md-6"><strong>Received:</strong> {{ $trialRequest->created_at->format('d M Y H:i') }}</div>
                    <div class="col-md-6"><strong>Handled by:</strong> {{ $trialRequest->handler?->name ?? '—' }}</div>
                    @if($trialRequest->organization)
                    <div class="col-12">
                        <strong>Provisioned school:</strong>
                        <a href="{{ route('platform.schools.show', $trialRequest->organization) }}" class="text-primary-600">
                            {{ $trialRequest->organization->name }}
                        </a>
                    </div>
                    @endif
                    @if($trialRequest->rejection_reason)
                    <div class="col-12"><strong>Rejection reason:</strong><br>{{ $trialRequest->rejection_reason }}</div>
                    @endif
                </div>
            </div>
        </div>

        @if($trialRequest->isPending())
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('platform.trial-requests.approve', $trialRequest) }}"
                  data-confirm
                  data-confirm-title="Approve free trial?"
                  data-confirm-text="This creates the school workspace and emails a set-password link to {{ $trialRequest->admin_email }}."
                  data-confirm-label="Approve & Grant Access"
                  data-confirm-icon="question"
                  data-confirm-tone="success">
                @csrf
                <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                    <iconify-icon icon="solar:check-circle-linear"></iconify-icon> Approve &amp; Grant Access
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card radius-12 border-0 shadow-sm mb-24">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Internal notes</h6></div>
            <div class="card-body p-24">
                <form method="POST" action="{{ route('platform.trial-requests.update', $trialRequest) }}">
                    @csrf
                    @method('PUT')
                    <textarea name="internal_notes" rows="5" class="form-control mb-12" placeholder="Review notes…">{{ old('internal_notes', $trialRequest->internal_notes) }}</textarea>
                    <button type="submit" class="btn btn-primary w-100">Save notes</button>
                </form>
            </div>
        </div>

        @if($trialRequest->isPending())
        <div class="card radius-12 border-0 shadow-sm mb-24">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Reject</h6></div>
            <div class="card-body p-24">
                <form method="POST" action="{{ route('platform.trial-requests.reject', $trialRequest) }}"
                      data-confirm
                      data-confirm-title="Reject this trial request?"
                      data-confirm-text="The applicant will be notified by email."
                      data-confirm-label="Yes, reject"
                      data-confirm-icon="warning"
                      data-confirm-tone="danger">
                    @csrf
                    <label class="form-label">Reason (optional, included in email)</label>
                    <textarea name="rejection_reason" rows="3" class="form-control mb-12" placeholder="e.g. Please book a demo first…">{{ old('rejection_reason') }}</textarea>
                    <button type="submit" class="btn btn-outline-danger w-100">Reject request</button>
                </form>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('platform.trial-requests.destroy', $trialRequest) }}"
              data-confirm
              data-confirm-title="Delete this trial request?"
              data-confirm-text="This permanently removes the application."
              data-confirm-label="Yes, delete"
              data-confirm-icon="warning"
              data-confirm-tone="danger">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-secondary w-100">Delete Request</button>
        </form>
    </div>
</div>
@endsection
