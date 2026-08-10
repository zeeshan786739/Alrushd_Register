@extends('platform.layouts.app')

@section('title', 'Plans & Pricing')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Plans &amp; Pricing</h6>
        <span class="text-secondary-light text-sm">These plans power the public pricing table and Stripe billing.</span>
    </div>
    <a href="{{ route('platform.plans.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <iconify-icon icon="ic:baseline-plus"></iconify-icon> New Plan
    </a>
</div>

@unless($stripeConfigured)
<div class="alert alert-warning radius-8 d-flex align-items-center gap-2">
    <iconify-icon icon="solar:danger-triangle-linear"></iconify-icon>
    Stripe is not configured yet — plans work locally, but online payment needs keys in
    <a href="{{ route('platform.settings.index') }}" class="fw-semibold">Settings</a>.
</div>
@endunless

<div class="row gy-4">
    @forelse($plans as $plan)
    <div class="col-xxl-4 col-md-6">
        <div class="card radius-12 border-0 shadow-sm h-100 {{ $plan->is_featured ? 'border border-primary-600' : '' }}">
            <div class="card-body p-24 d-flex flex-column">
                <div class="d-flex align-items-center justify-content-between mb-8">
                    <h6 class="fw-semibold mb-0">{{ $plan->name }}</h6>
                    <div class="d-flex gap-2">
                        @if($plan->is_featured)<span class="badge platform-badge bg-primary-50 text-primary-600">Featured</span>@endif
                        <span class="badge platform-badge {{ $plan->is_active ? 'bg-success-focus text-success-main' : 'bg-neutral-200 text-neutral-600' }}">
                            {{ $plan->is_active ? 'Active' : 'Hidden' }}
                        </span>
                    </div>
                </div>
                <h4 class="fw-bold mb-4">{{ $plan->formattedPrice() }}<small class="text-secondary-light text-sm fw-normal">/{{ $plan->billing_interval }}</small></h4>
                <p class="text-secondary-light text-sm mb-12">{{ $plan->tagline }}</p>
                <ul class="list-unstyled d-flex flex-column gap-1 text-sm mb-16">
                    @foreach(($plan->features ?? []) as $feature)
                    <li class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:check-circle-bold" class="text-success-main"></iconify-icon> {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                <div class="text-secondary-light text-xs mb-16">
                    Trial: {{ $plan->trial_days }} days · Subscribers: {{ $plan->subscriptions_count }}
                    @if($plan->isSyncedToStripe())
                        · <span class="text-success-main">Stripe synced</span>
                    @else
                        · <span class="text-warning-main">Not on Stripe</span>
                    @endif
                </div>
                <div class="mt-auto d-flex flex-wrap gap-2">
                    <a href="{{ route('platform.plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form method="POST" action="{{ route('platform.plans.toggle', $plan) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $plan->is_active ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                    <form method="POST" action="{{ route('platform.plans.sync-stripe', $plan) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-info">Sync to Stripe</button>
                    </form>
                    @if($plan->subscriptions_count === 0)
                    <form method="POST" action="{{ route('platform.plans.destroy', $plan) }}" onsubmit="return confirm('Delete plan {{ $plan->name }}?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card radius-12 border-0 shadow-sm">
            <div class="card-body text-center py-40 text-secondary-light">
                No plans yet — create your first plan to start selling.
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
