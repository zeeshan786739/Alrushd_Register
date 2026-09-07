@extends('platform.layouts.app')

@section('title', 'Free Trial Requests')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Free Trial Requests</h6>
        <span class="text-secondary-light text-sm">Approve or reject self-serve trial applications before access is granted.</span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success radius-8 mb-24">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger radius-8 mb-24">{{ session('error') }}</div>
@endif

<div class="d-flex flex-wrap gap-2 mb-24">
    <a href="{{ route('platform.trial-requests.index') }}"
       class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
        All ({{ $counts->sum() }})
    </a>
    @foreach($statuses as $status)
    <a href="{{ route('platform.trial-requests.index', ['status' => $status->value]) }}"
       class="btn btn-sm {{ request('status') === $status->value ? 'btn-primary' : 'btn-outline-secondary' }}">
        {{ $status->label() }} ({{ $counts[$status->value] ?? 0 }})
    </a>
    @endforeach
</div>

<div class="card radius-12 border-0 shadow-sm">
    <div class="card-header bg-base py-16 px-24">
        <form method="GET" class="d-flex gap-2" style="max-width: 420px;">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search school, name, email…">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-24">School</th>
                        <th>Admin</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th class="text-end pe-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $item)
                    <tr class="platform-row-link" role="link" tabindex="0"
                        data-href="{{ route('platform.trial-requests.show', $item) }}"
                        style="cursor:pointer">
                        <td class="ps-24">
                            <span class="fw-medium text-primary-600">{{ $item->school_name }}</span>
                            @if($item->country)<div class="text-secondary-light text-sm">{{ $item->country }}</div>@endif
                        </td>
                        <td>
                            <div>{{ $item->admin_name }}</div>
                            <div class="text-secondary-light text-sm">{{ $item->admin_email }}</div>
                        </td>
                        <td class="text-sm">{{ $item->plan?->name ?? '—' }}</td>
                        <td><span class="badge platform-badge {{ $item->status?->badgeClass() }}">{{ $item->status?->label() }}</span></td>
                        <td class="text-sm text-secondary-light">{{ $item->created_at->format('d M Y') }}</td>
                        <td class="text-end pe-24" data-row-ignore>
                            <a href="{{ route('platform.trial-requests.show', $item) }}" class="btn btn-sm btn-outline-primary">Open</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-40 text-secondary-light">No trial requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
    <div class="card-footer bg-base py-16 px-24">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
