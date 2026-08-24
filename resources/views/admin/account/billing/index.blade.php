@extends('admin.layouts.app')
@section('title', 'Billing & Subscription')
@section('content')
@include('admin.account.partials.shell', [
    'activeTab' => 'billing',
    'shellTitle' => 'Billing & Subscription',
    'shellSubtitle' => 'Your school\'s Enrolliq plan — separate from customer payment keys.',
])

<div class="row g-4">
    <div class="col-xxl-5">
        <div class="acct-panel mb-24">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Current plan</h2>
                    <p class="acct-panel__desc">Platform subscription for {{ $organization->name }}.</p>
                </div>
            </div>
            <div class="acct-panel__body">
                @if($subscription)
                    <div class="acct-plan-current">
                        <div class="acct-plan-current__top">
                            <h3>{{ $subscription->plan?->name ?? 'Custom' }}</h3>
                            <span class="badge px-12 py-6 radius-8 {{ $subscription->status?->badgeClass() }}">{{ $subscription->status?->label() }}</span>
                        </div>
                        @if($subscription->plan)
                            <p class="acct-plan-current__price">{{ $subscription->plan->formattedPrice() }} <span>/ {{ $subscription->plan->billing_interval }}</span></p>
                        @endif
                        <ul class="acct-plan-current__meta">
                            @if($subscription->trial_ends_at)
                                <li>Trial ends <strong>{{ $subscription->trial_ends_at->format('d M Y') }}</strong> ({{ $subscription->trial_ends_at->diffForHumans() }})</li>
                            @endif
                            @if($subscription->current_period_end)
                                <li>Renews on <strong>{{ $subscription->current_period_end->format('d M Y') }}</strong></li>
                            @endif
                            <li>School status: <strong>{{ $organization->status?->label() }}</strong></li>
                        </ul>
                    </div>
                @else
                    <div class="em-inbox-empty em-empty-state em-empty-state--compact py-32">
                        <p class="mb-0">No subscription found. Choose a plan below to get started.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="acct-panel">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Billing history</h2>
                </div>
            </div>
            <div class="acct-table-wrap">
                <table class="table mb-0 align-middle acct-table">
                    <thead><tr><th>Plan</th><th>Status</th><th>Started</th></tr></thead>
                    <tbody>
                        @forelse($history as $item)
                        <tr>
                            <td>{{ $item->plan?->name ?? '—' }}</td>
                            <td><span class="badge px-12 py-6 radius-8 {{ $item->status?->badgeClass() }}">{{ $item->status?->label() }}</span></td>
                            <td class="text-secondary-light text-sm">{{ $item->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-24 text-secondary-light">No billing history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xxl-7">
        <div class="acct-panel">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Available plans</h2>
                    <p class="acct-panel__desc">Upgrade or switch your Enrolliq subscription.</p>
                </div>
            </div>
            <div class="acct-panel__body">
                <div class="acct-plan-grid">
                    @foreach($plans as $plan)
                    <div class="acct-plan-card {{ $subscription?->saas_plan_id === $plan->id ? 'is-current' : '' }}">
                        <div class="acct-plan-card__head">
                            <h4>{{ $plan->name }}</h4>
                            @if($subscription?->saas_plan_id === $plan->id)
                                <span class="acct-plan-card__badge">Current</span>
                            @endif
                        </div>
                        <p class="acct-plan-card__price">{{ $plan->formattedPrice() }}<small>/{{ $plan->billing_interval }}</small></p>
                        <ul class="acct-plan-card__features">
                            @foreach(array_slice($plan->features ?? [], 0, 5) as $feature)
                                <li><iconify-icon icon="solar:check-circle-linear"></iconify-icon> {{ $feature }}</li>
                            @endforeach
                        </ul>
                        <div class="acct-plan-card__action">
                            @if($subscription?->saas_plan_id === $plan->id && $subscription?->status?->value === 'active')
                                <button class="btn btn-outline-success radius-8 w-100" disabled>Current plan</button>
                            @else
                                <form method="POST" action="{{ route('admin.billing.checkout') }}">
                                    @csrf
                                    <input type="hidden" name="plan" value="{{ $plan->slug }}">
                                    <button type="submit" class="btn btn-primary-600 radius-8 w-100 fc-btn"
                                        @unless($stripeReady && $plan->isSyncedToStripe()) disabled title="Online payment not available yet — contact support" @endunless>
                                        {{ $subscription?->saas_plan_id === $plan->id ? 'Renew / Pay' : 'Switch to '.$plan->name }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @unless($stripeReady)
                <p class="text-secondary-light text-sm mt-16 mb-0">
                    Online payments are being set up. To change your plan, contact
                    <a href="mailto:{{ \App\Models\PlatformSetting::get('support_email', config('saas.support_email')) }}">{{ \App\Models\PlatformSetting::get('support_email', config('saas.support_email')) }}</a>.
                </p>
                @endunless
            </div>
        </div>
    </div>
</div>
@endsection
