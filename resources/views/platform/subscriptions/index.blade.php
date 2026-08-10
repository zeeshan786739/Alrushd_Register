@extends('platform.layouts.app')

@section('title', 'Subscriptions')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Subscriptions</h6>
        <span class="text-secondary-light text-sm">Every subscription across the platform, synced from Stripe.</span>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['trialing', 'active', 'past_due', 'canceled', 'incomplete', 'complimentary'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="card radius-12 border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-24">School</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Period ends</th>
                        <th>Stripe ID</th>
                        <th>Started</th>
                        <th class="text-end pe-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                    <tr>
                        <td class="ps-24">
                            @if($subscription->organization)
                            <a href="{{ route('platform.schools.show', $subscription->organization) }}" class="fw-medium text-primary-600">
                                {{ $subscription->organization->name }}
                            </a>
                            @else — @endif
                        </td>
                        <td>{{ $subscription->plan?->name ?? '—' }}</td>
                        <td><span class="badge platform-badge {{ $subscription->status?->badgeClass() }}">{{ $subscription->status?->label() }}</span></td>
                        <td class="text-sm text-secondary-light">
                            {{ $subscription->current_period_end?->format('d M Y') ?? $subscription->trial_ends_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="text-sm"><code>{{ $subscription->stripe_subscription_id ?? 'manual' }}</code></td>
                        <td class="text-sm text-secondary-light">{{ $subscription->created_at->format('d M Y') }}</td>
                        <td class="text-end pe-24">
                            @if($subscription->status?->isCurrent() && $subscription->status?->value !== 'complimentary')
                            <form method="POST" action="{{ route('platform.subscriptions.cancel', $subscription) }}"
                                  onsubmit="return confirm('Cancel this subscription? The school will lose access at the end of the flow.');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-40 text-secondary-light">No subscriptions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($subscriptions->hasPages())
    <div class="card-footer bg-base py-16 px-24">{{ $subscriptions->links() }}</div>
    @endif
</div>
@endsection
