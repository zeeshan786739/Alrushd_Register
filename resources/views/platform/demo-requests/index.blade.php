@extends('platform.layouts.app')

@section('title', 'Demo Requests')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Demo Requests</h6>
        <span class="text-secondary-light text-sm">Sales pipeline from the public landing page.</span>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-24">
    <a href="{{ route('platform.demo-requests.index') }}"
       class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
        All ({{ $counts->sum() }})
    </a>
    @foreach($statuses as $status)
    <a href="{{ route('platform.demo-requests.index', ['status' => $status->value]) }}"
       class="btn btn-sm {{ request('status') === $status->value ? 'btn-primary' : 'btn-outline-secondary' }}">
        {{ $status->label() }} ({{ $counts[$status->value] ?? 0 }})
    </a>
    @endforeach
</div>

<div class="card radius-12 border-0 shadow-sm">
    <div class="card-header bg-base py-16 px-24">
        <form method="GET" class="d-flex gap-2" style="max-width: 420px;">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, email, organisation…">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-24">Contact</th>
                        <th>Organisation</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Handled by</th>
                        <th>Received</th>
                        <th class="text-end pe-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demoRequests as $demo)
                    <tr>
                        <td class="ps-24">
                            <a href="{{ route('platform.demo-requests.show', $demo) }}" class="fw-medium text-primary-600">{{ $demo->name }}</a>
                            <div class="text-secondary-light text-sm">{{ $demo->email }}@if($demo->phone) · {{ $demo->phone }}@endif</div>
                        </td>
                        <td>{{ $demo->organization_name ?? '—' }}
                            @if($demo->organization_type)<div class="text-secondary-light text-sm">{{ $demo->organization_type }}</div>@endif
                        </td>
                        <td class="text-sm">{{ $demo->students_count ?? '—' }}</td>
                        <td><span class="badge platform-badge {{ $demo->status?->badgeClass() }}">{{ $demo->status?->label() }}</span></td>
                        <td class="text-sm text-secondary-light">{{ $demo->handler?->name ?? '—' }}</td>
                        <td class="text-sm text-secondary-light">{{ $demo->created_at->format('d M Y') }}</td>
                        <td class="text-end pe-24">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('platform.demo-requests.show', $demo) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                @if($demo->status?->value !== 'converted')
                                <a href="{{ route('platform.schools.create', ['demo_request_id' => $demo->id]) }}" class="btn btn-sm btn-outline-success">Convert</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-40 text-secondary-light">No demo requests yet — share your landing page!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($demoRequests->hasPages())
    <div class="card-footer bg-base py-16 px-24">{{ $demoRequests->links() }}</div>
    @endif
</div>
@endsection
