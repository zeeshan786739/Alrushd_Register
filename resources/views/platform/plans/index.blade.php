@extends('platform.layouts.app')

@section('title', 'Plans & Pricing')

@section('content')
<div class="plan-hub">
    <div class="plan-hub__hero">
        <div>
            <span class="plan-hub__eyebrow">SaaS Control Panel</span>
            <h1 class="plan-hub__title">Plans &amp; Pricing</h1>
            <p class="plan-hub__desc">Control what each package includes, set limits, billing cycle, and which plan new schools see first.</p>
        </div>
        <a href="{{ route('platform.plans.create') }}" class="btn btn-primary plan-hub__cta">
            <iconify-icon icon="solar:add-circle-linear"></iconify-icon> New Plan
        </a>
    </div>

    @unless($stripeConfigured)
    <div class="plan-hub__alert">
        <iconify-icon icon="solar:danger-triangle-linear"></iconify-icon>
        <span>Stripe is not configured — plans work locally. Add keys in <a href="{{ route('platform.settings.index') }}">Settings</a> to charge online.</span>
    </div>
    @endunless

    @if($defaultPlan)
    <div class="plan-hub__default">
        <iconify-icon icon="solar:star-circle-linear"></iconify-icon>
        <span>Default signup plan: <strong>{{ $defaultPlan->name }}</strong> ({{ $defaultPlan->formattedPriceWithInterval() }})</span>
    </div>
    @endif

    <div class="plan-hub__grid">
        @forelse($plans as $plan)
        <article class="plan-card {{ $plan->is_featured ? 'is-featured' : '' }} {{ $plan->is_default ? 'is-default' : '' }}">
            <div class="plan-card__head">
                <div>
                    <h2 class="plan-card__name">{{ $plan->name }}</h2>
                    <p class="plan-card__tagline">{{ $plan->tagline ?: 'No tagline yet' }}</p>
                </div>
                <div class="plan-card__badges">
                    @if($plan->is_default)<span class="plan-badge plan-badge--default">Default</span>@endif
                    @if($plan->is_featured)<span class="plan-badge plan-badge--featured">Featured</span>@endif
                    <span class="plan-badge {{ $plan->is_active ? 'plan-badge--active' : 'plan-badge--hidden' }}">{{ $plan->is_active ? 'Active' : 'Hidden' }}</span>
                </div>
            </div>

            <div class="plan-card__price">
                <span class="plan-card__amount">{{ $plan->formattedPrice() }}</span>
                <span class="plan-card__interval">
                    @if($plan->isFree()) free @elseif($plan->isLifetime()) one-time @else / {{ $plan->billingInterval()->shortLabel() }} @endif
                </span>
            </div>

            @if($plan->enabledModuleCount() > 0)
            <div class="plan-card__modules">
                <span class="plan-module-chip">{{ $plan->enabledModuleCount() }} modules enabled</span>
                @if($plan->isFree())<span class="plan-module-chip">No payment required</span>@endif
            </div>
            @endif

            <ul class="plan-card__features">
                @forelse(array_slice($plan->features ?? [], 0, 6) as $feature)
                <li><iconify-icon icon="solar:check-circle-bold"></iconify-icon>{{ $feature }}</li>
                @empty
                <li class="plan-card__empty">No features listed yet</li>
                @endforelse
                @if(count($plan->features ?? []) > 6)
                <li class="plan-card__more">+{{ count($plan->features) - 6 }} more</li>
                @endif
            </ul>

            @if($plan->limitsSummary())
            <div class="plan-card__limits">
                @foreach($plan->limitsSummary() as $limitLine)
                <span class="plan-limit-chip">{{ $limitLine }}</span>
                @endforeach
            </div>
            @endif

            <div class="plan-card__meta">
                <span>Trial {{ $plan->trial_days }}d</span>
                <span>{{ $plan->subscriptions_count }} subscribers</span>
                <span class="{{ $plan->isSyncedToStripe() ? 'text-success-main' : 'text-warning-main' }}">
                    {{ $plan->isSyncedToStripe() ? 'Stripe synced' : 'Not on Stripe' }}
                </span>
            </div>

            <div class="plan-card__actions">
                <a href="{{ route('platform.plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                @unless($plan->is_default)
                <form method="POST" action="{{ route('platform.plans.set-default', $plan) }}">@csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Set default</button>
                </form>
                @endunless
                <form method="POST" action="{{ route('platform.plans.toggle', $plan) }}">@csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $plan->is_active ? 'Hide' : 'Activate' }}</button>
                </form>
                <form method="POST" action="{{ route('platform.plans.sync-stripe', $plan) }}">@csrf
                    <button type="submit" class="btn btn-sm btn-outline-info">Sync Stripe</button>
                </form>
                @if($plan->subscriptions_count === 0 && ! $plan->is_default)
                <form method="POST" action="{{ route('platform.plans.destroy', $plan) }}" onsubmit="return confirm('Delete {{ $plan->name }}?');">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
                @endif
            </div>
        </article>
        @empty
        <div class="plan-card plan-card--empty">
            <iconify-icon icon="solar:card-linear"></iconify-icon>
            <p>No plans yet. Create your first package to power signup and billing.</p>
            <a href="{{ route('platform.plans.create') }}" class="btn btn-primary btn-sm">Create plan</a>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('admin/assets/css/platform-plans.css') }}">
@endsection
