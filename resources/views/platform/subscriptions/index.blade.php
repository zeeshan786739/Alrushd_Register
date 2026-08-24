@extends('platform.layouts.app')

@section('title', 'Subscriptions')

@section('content')
<div class="plan-hub">
    <div class="plan-hub__hero">
        <div>
            <span class="plan-hub__eyebrow">SaaS Control Panel</span>
            <h1 class="plan-hub__title">Subscriptions</h1>
            <p class="plan-hub__desc">Every school subscription — free, trial, complimentary, or Stripe-managed.</p>
        </div>
        <form method="POST" action="{{ route('platform.subscriptions.normalize') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary plan-hub__cta" onclick="return confirm('Normalize free Starter subscriptions to complimentary status?');">
                <iconify-icon icon="solar:refresh-linear"></iconify-icon> Fix free plans
            </button>
        </form>
    </div>

    <div class="sub-stats">
        <div class="sub-stat"><strong>{{ $stats['current'] }}</strong><span>Active subs</span></div>
        <div class="sub-stat"><strong>{{ $stats['complimentary'] }}</strong><span>Free / complimentary</span></div>
        <div class="sub-stat"><strong>{{ $stats['trialing'] }}</strong><span>Trialing</span></div>
        <div class="sub-stat"><strong>{{ $stats['paid'] }}</strong><span>On paid plans</span></div>
    </div>

    <form method="GET" class="sub-filters">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search schools…" class="sub-filters__search">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['complimentary', 'trialing', 'active', 'past_due', 'canceled', 'incomplete'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <select name="plan" class="form-select" onchange="this.form.submit()">
            <option value="">All plans</option>
            @foreach($plans as $planOption)
                <option value="{{ $planOption->id }}" @selected((string) request('plan') === (string) $planOption->id)>{{ $planOption->name }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search', 'status', 'plan']))
        <a href="{{ route('platform.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        @endif
    </form>

    <div class="card radius-12 border-0 shadow-sm sub-table-card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle sub-table">
                <thead>
                    <tr>
                        <th class="ps-24">School</th>
                        <th>Plan</th>
                        <th>Modules</th>
                        <th>Status</th>
                        <th>Billing</th>
                        <th>Period / trial</th>
                        <th>Started</th>
                        <th class="text-end pe-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                    @php $plan = $subscription->plan; @endphp
                    <tr>
                        <td class="ps-24">
                            @if($subscription->organization)
                            <a href="{{ route('platform.schools.show', $subscription->organization) }}" class="fw-semibold text-primary-600 d-block">
                                {{ $subscription->organization->name }}
                            </a>
                            <span class="text-secondary-light text-xs">/{{ $subscription->organization->slug }}</span>
                            @else — @endif
                        </td>
                        <td>
                            <span class="fw-medium">{{ $plan?->name ?? '—' }}</span>
                            @if($plan)
                            <span class="d-block text-secondary-light text-xs">{{ $plan->formattedPriceWithInterval() }}</span>
                            @endif
                        </td>
                        <td>
                            @if($plan && $plan->enabledModuleCount() > 0)
                            <span class="plan-module-chip">{{ $plan->enabledModuleCount() }} enabled</span>
                            @else
                            <span class="text-secondary-light text-sm">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge platform-badge {{ $subscription->status?->badgeClass() }}">{{ $subscription->status?->label() }}</span>
                        </td>
                        <td>
                            <span class="sub-billing sub-billing--{{ strtolower($subscription->billingSourceLabel()) }}">{{ $subscription->billingSourceLabel() }}</span>
                            @if($subscription->isStripeManaged())
                            <code class="d-block text-xs mt-4">{{ Str::limit($subscription->stripe_subscription_id, 18) }}</code>
                            @endif
                        </td>
                        <td class="text-sm text-secondary-light">
                            @if($subscription->trial_ends_at)
                                Trial {{ $subscription->trial_ends_at->format('d M Y') }}
                            @elseif($subscription->current_period_end)
                                Renews {{ $subscription->current_period_end->format('d M Y') }}
                            @elseif($subscription->isFreeAccess())
                                No end date
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-sm text-secondary-light">{{ $subscription->created_at->format('d M Y') }}</td>
                        <td class="text-end pe-24">
                            <div class="sub-actions">
                                @if($subscription->organization)
                                <a href="{{ route('platform.schools.show', $subscription->organization) }}#subscription" class="btn btn-sm btn-outline-primary">Manage</a>
                                @endif
                                @if($subscription->status?->isCurrent() && ! $subscription->isFreeAccess())
                                <form method="POST" action="{{ route('platform.subscriptions.cancel', $subscription) }}"
                                      onsubmit="return confirm('Cancel this subscription?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-40 text-secondary-light">No subscriptions match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
        <div class="card-footer bg-base py-16 px-24">{{ $subscriptions->links() }}</div>
        @endif
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('admin/assets/css/platform-plans.css') }}">
@endsection
