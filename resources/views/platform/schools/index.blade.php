@extends('platform.layouts.app')

@section('title', 'Schools')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Schools</h6>
        <span class="text-secondary-light text-sm">All organisations using the platform.</span>
    </div>
    <a href="{{ route('platform.schools.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <iconify-icon icon="ic:baseline-plus"></iconify-icon> New School
    </a>
</div>

<div class="card radius-12 border-0 shadow-sm">
    <div class="card-header bg-base py-16 px-24">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Search name, email or slug…">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="plan" class="form-select">
                    <option value="">All plans</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(request('plan') == $plan->id)>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                <a href="{{ route('platform.schools.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-24">School</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Admins</th>
                        <th>Trial ends</th>
                        <th>Created</th>
                        <th class="text-end pe-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schools as $school)
                    <tr>
                        <td class="ps-24">
                            <a href="{{ route('platform.schools.show', $school) }}" class="fw-semibold text-primary-600">{{ $school->name }}</a>
                            <div class="text-secondary-light text-sm">{{ $school->email ?? $school->slug }}</div>
                        </td>
                        <td>{{ $school->currentSubscription?->plan?->name ?? '—' }}</td>
                        <td><span class="badge platform-badge {{ $school->status?->badgeClass() }}">{{ $school->status?->label() }}</span></td>
                        <td>{{ $school->admins_count }}</td>
                        <td class="text-sm text-secondary-light">{{ $school->trial_ends_at?->format('d M Y') ?? '—' }}</td>
                        <td class="text-sm text-secondary-light">{{ $school->created_at->format('d M Y') }}</td>
                        <td class="text-end pe-24">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <iconify-icon icon="solar:eye-linear"></iconify-icon>
                                </a>
                                @if($school->allowsAccess())
                                <form method="POST" action="{{ route('platform.schools.status', $school) }}"
                                      onsubmit="return confirm('Deactivate {{ $school->name }}? Their admins will be locked out.');">
                                    @csrf
                                    <input type="hidden" name="status" value="inactive">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Deactivate">
                                        <iconify-icon icon="solar:lock-linear"></iconify-icon>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('platform.schools.status', $school) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Activate">
                                        <iconify-icon icon="solar:lock-unlocked-linear"></iconify-icon>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-40 text-secondary-light">No schools match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($schools->hasPages())
    <div class="card-footer bg-base py-16 px-24">
        {{ $schools->links() }}
    </div>
    @endif
</div>
@endsection
