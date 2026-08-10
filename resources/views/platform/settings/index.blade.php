@extends('platform.layouts.app')

@section('title', 'Platform Settings')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Platform Settings</h6>
        <span class="text-secondary-light text-sm">Branding and billing configuration for the SaaS platform itself.</span>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger radius-8">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('platform.settings.update') }}">
    @csrf
    @method('PUT')

    <div class="row gy-4">
        <div class="col-lg-6">
            <div class="card radius-12 border-0 shadow-sm h-100">
                <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Branding</h6></div>
                <div class="card-body p-24 row g-3">
                    <div class="col-12">
                        <label class="form-label">Platform name</label>
                        <input type="text" name="platform_name" class="form-control"
                               value="{{ old('platform_name', $settings['platform_name'] ?? config('saas.name')) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Support email</label>
                        <input type="email" name="support_email" class="form-control"
                               value="{{ old('support_email', $settings['support_email'] ?? config('saas.support_email')) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card radius-12 border-0 shadow-sm h-100">
                <div class="card-header bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                    <h6 class="text-lg fw-semibold mb-0">Stripe (Platform Billing)</h6>
                    <span class="badge platform-badge {{ $stripeConfigured ? 'bg-success-focus text-success-main' : 'bg-warning-focus text-warning-main' }}">
                        {{ $stripeConfigured ? 'Configured' : 'Not configured' }}
                    </span>
                </div>
                <div class="card-body p-24 row g-3">
                    <div class="col-12">
                        <label class="form-label">Publishable key</label>
                        <input type="text" name="stripe_key" class="form-control" placeholder="pk_live_…"
                               value="{{ old('stripe_key', $settings['stripe_key'] ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Secret key</label>
                        <input type="password" name="stripe_secret" class="form-control" placeholder="sk_live_…"
                               value="{{ old('stripe_secret', $settings['stripe_secret'] ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Webhook signing secret</label>
                        <input type="password" name="stripe_webhook_secret" class="form-control" placeholder="whsec_…"
                               value="{{ old('stripe_webhook_secret', $settings['stripe_webhook_secret'] ?? '') }}">
                        <span class="text-secondary-light text-xs">Point a Stripe webhook at <code>{{ url('/webhooks/stripe/platform') }}</code> with the subscription + invoice events.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary px-40 py-12">Save Settings</button>
        </div>
    </div>
</form>
@endsection
