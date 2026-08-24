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
                            <p class="acct-plan-current__price">{{ $subscription->plan->formattedPriceWithInterval() }}</p>
                        @endif
                        <ul class="acct-plan-current__meta">
                            @if($subscription->isFreeAccess())
                                <li><strong>Free plan</strong> — no payment required</li>
                            @endif
                            @if($subscription->trial_ends_at)
                                <li>Trial ends <strong>{{ $subscription->trial_ends_at->format('d M Y') }}</strong> ({{ $subscription->trial_ends_at->diffForHumans() }})</li>
                            @endif
                            @if($subscription->current_period_end)
                                <li>Renews on <strong>{{ $subscription->current_period_end->format('d M Y') }}</strong></li>
                            @endif
                            <li>School status: <strong>{{ $organization->status?->label() }}</strong></li>
                        </ul>

                        @if(!empty($moduleCatalog))
                        <div class="acct-modules mt-20">
                            <h4 class="acct-modules__title">Included in your plan</h4>
                            <div class="acct-modules__grid">
                                @foreach($moduleCatalog as $moduleKey => $definition)
                                @php $included = in_array($moduleKey, $enabledModules, true); @endphp
                                <div class="acct-module {{ $included ? 'is-included' : 'is-locked' }}">
                                    <iconify-icon icon="{{ $included ? 'solar:check-circle-bold' : 'solar:lock-keyhole-linear' }}"></iconify-icon>
                                    <span>{{ $definition['label'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
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
                        <p class="acct-plan-card__price">{{ $plan->formattedPriceWithInterval() }}</p>
                        <ul class="acct-plan-card__features">
                            @foreach(array_slice($plan->features ?? [], 0, 5) as $feature)
                                <li><iconify-icon icon="solar:check-circle-linear"></iconify-icon> {{ $feature }}</li>
                            @endforeach
                        </ul>
                        <div class="acct-plan-card__action">
                            @if($subscription?->saas_plan_id === $plan->id && $subscription?->status?->isCurrent())
                                <button class="btn btn-outline-success radius-8 w-100" disabled>Current plan</button>
                            @else
                                <form method="POST" action="{{ route('admin.billing.checkout') }}">
                                    @csrf
                                    <input type="hidden" name="plan" value="{{ $plan->slug }}">
                                    @php
                                        $needsStripe = (float) $plan->price > 0 && $plan->isSyncedToStripe();
                                        $canSwitch = ! $needsStripe || $stripeReady;
                                    @endphp
                                    <button type="submit" class="btn btn-primary-600 radius-8 w-100 fc-btn"
                                        @unless($canSwitch) disabled title="Online payment not available yet — contact support" @endunless>
                                        @if($subscription?->saas_plan_id === $plan->id)
                                            Renew / Pay
                                        @elseif($subscription && $needsStripe && $stripeReady)
                                            Switch to {{ $plan->name }}
                                        @else
                                            Switch to {{ $plan->name }}
                                        @endif
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @unless($stripeReady)
                <p class="acct-muted text-sm mt-16 mb-0">
                    Paid plans need online billing to be enabled for your account. Free plans switch instantly above.
                    Need help?
                    <a href="mailto:{{ \App\Models\PlatformSetting::get('support_email', config('saas.support_email')) }}">Contact support</a>.
                </p>
                @endunless
                @if($portalUrl ?? null)
                <p class="acct-muted text-sm mt-12 mb-0">
                    <a href="{{ $portalUrl }}" target="_blank" rel="noopener">Manage your payment methods</a>
                </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
