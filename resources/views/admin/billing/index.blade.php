@extends('admin.layouts.app')

@section('title', 'Billing & Subscription')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Billing &amp; Subscription</h6>
        <span class="text-secondary-light text-sm">Your school's plan on {{ \App\Models\PlatformSetting::get('platform_name', config('saas.name')) }}.</span>
    </div>
</div>

<div class="row gy-4">
    <div class="col-lg-5">
        <div class="card radius-12 border-0 shadow-sm mb-24">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Current Plan</h6></div>
            <div class="card-body p-24">
                @if($subscription)
                    <div class="d-flex align-items-center justify-content-between mb-8">
                        <h5 class="fw-bold mb-0">{{ $subscription->plan?->name ?? 'Custom' }}</h5>
                        <span class="badge px-12 py-6 radius-8 {{ $subscription->status?->badgeClass() }}">{{ $subscription->status?->label() }}</span>
                    </div>
                    @if($subscription->plan)
                        <p class="text-secondary-light mb-12">{{ $subscription->plan->formattedPrice() }} / {{ $subscription->plan->billing_interval }}</p>
                    @endif
                    <ul class="list-unstyled text-sm text-secondary-light d-flex flex-column gap-1 mb-0">
                        @if($subscription->trial_ends_at)
                            <li>Trial ends <strong>{{ $subscription->trial_ends_at->format('d M Y') }}</strong> ({{ $subscription->trial_ends_at->diffForHumans() }})</li>
                        @endif
                        @if($subscription->current_period_end)
                            <li>Renews on <strong>{{ $subscription->current_period_end->format('d M Y') }}</strong></li>
                        @endif
                        <li>School status: <strong>{{ $organization->status?->label() }}</strong></li>
                    </ul>
                @else
                    <p class="text-secondary-light mb-0">No subscription found. Choose a plan to get started.</p>
                @endif
            </div>
        </div>

        <div class="card radius-12 border-0 shadow-sm">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">History</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead><tr><th class="ps-24">Plan</th><th>Status</th><th>Started</th></tr></thead>
                        <tbody>
                            @forelse($history as $item)
                            <tr>
                                <td class="ps-24">{{ $item->plan?->name ?? '—' }}</td>
                                <td><span class="badge px-12 py-6 radius-8 {{ $item->status?->badgeClass() }}">{{ $item->status?->label() }}</span></td>
                                <td class="text-secondary-light text-sm">{{ $item->created_at->format('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-24 text-secondary-light">No billing history.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card radius-12 border-0 shadow-sm">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Available Plans</h6></div>
            <div class="card-body p-24">
                <div class="row g-3">
                    @foreach($plans as $plan)
                    <div class="col-md-4">
                        <div class="border radius-12 p-16 h-100 d-flex flex-column {{ $subscription?->saas_plan_id === $plan->id ? 'border-primary-600 bg-primary-50' : '' }}">
                            <h6 class="fw-semibold mb-4">{{ $plan->name }}</h6>
                            <h5 class="fw-bold mb-8">{{ $plan->formattedPrice() }}<small class="text-secondary-light text-sm fw-normal">/{{ $plan->billing_interval }}</small></h5>
                            <ul class="list-unstyled text-sm text-secondary-light d-flex flex-column gap-1 mb-12">
                                @foreach(array_slice($plan->features ?? [], 0, 4) as $feature)
                                <li>• {{ $feature }}</li>
                                @endforeach
                            </ul>
                            <div class="mt-auto">
                                @if($subscription?->saas_plan_id === $plan->id && $subscription?->status?->value === 'active')
                                    <button class="btn btn-sm btn-outline-success w-100" disabled>Current Plan</button>
                                @else
                                    <form method="POST" action="{{ route('admin.billing.checkout') }}">
                                        @csrf
                                        <input type="hidden" name="plan" value="{{ $plan->slug }}">
                                        <button type="submit" class="btn btn-sm btn-primary w-100"
                                            @unless($stripeReady && $plan->isSyncedToStripe()) disabled title="Online payment not available yet — contact support" @endunless>
                                            {{ $subscription?->saas_plan_id === $plan->id ? 'Renew / Pay' : 'Switch to ' . $plan->name }}
                                        </button>
                                    </form>
                                @endif
                            </div>
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
