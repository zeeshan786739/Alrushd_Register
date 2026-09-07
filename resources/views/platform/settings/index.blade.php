@extends('platform.layouts.app')

@section('title', 'Platform Settings')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Platform Settings</h6>
        <span class="text-secondary-light text-sm">Branding, email delivery, and billing for the SaaS platform itself.</span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success radius-8 mb-24">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger radius-8 mb-24">{{ session('error') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger radius-8 mb-24">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

@if($sendGridReady)
<div class="alert alert-info radius-8 mb-24">
    <strong>Email delivery:</strong> Demo/trial emails use SendGrid first, then fall back to Laravel SMTP
    (<code>{{ config('mail.mailers.smtp.host') }}:{{ config('mail.mailers.smtp.port') }}</code>, from <code>{{ $mailFrom }}</code>).
    If messages are not arriving, paste a <em>valid</em> SendGrid API key below and use <em>Send test email</em>.
</div>
@else
<div class="alert alert-warning radius-8 mb-24">
    <strong>SendGrid not set.</strong> Add an API key below, or ensure Laravel SMTP in <code>.env</code> can deliver mail
    (current host <code>{{ config('mail.mailers.smtp.host') }}:{{ config('mail.mailers.smtp.port') }}</code>).
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
                        <input type="password" name="stripe_secret" class="form-control" placeholder="{{ !empty($settings['stripe_secret']) ? '•••••••• (leave blank to keep)' : 'sk_live_…' }}"
                               value="">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Webhook signing secret</label>
                        <input type="password" name="stripe_webhook_secret" class="form-control" placeholder="{{ !empty($settings['stripe_webhook_secret']) ? '•••••••• (leave blank to keep)' : 'whsec_…' }}"
                               value="">
                        <span class="text-secondary-light text-xs">Point a Stripe webhook at <code>{{ url('/webhooks/stripe/platform') }}</code> with the subscription + invoice events.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card radius-12 border-0 shadow-sm">
                <div class="card-header bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                    <h6 class="text-lg fw-semibold mb-0">Transactional Email (SendGrid)</h6>
                    <span class="badge platform-badge {{ $mailConfigured ? 'bg-success-focus text-success-main' : 'bg-warning-focus text-warning-main' }}">
                        {{ $mailConfigured ? 'Ready · '.$mailProvider : 'Not configured' }}
                    </span>
                </div>
                <div class="card-body p-24">
                    <p class="text-secondary-light text-sm mb-20">
                        Used for demo and free-trial confirmation emails, owner notifications, and access-granted messages.
                        If SendGrid fails, mail automatically falls back to Laravel SMTP.
                    </p>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">SendGrid API key</label>
                            <input type="password" name="sendgrid_api_key" class="form-control"
                                   placeholder="{{ !empty($settings['sendgrid_api_key']) || config('sendgrid.api_key') ? '•••••••• (leave blank to keep / use env)' : 'SG.…' }}"
                                   value="">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Owner notification email</label>
                            <input type="email" name="notify_email" class="form-control"
                                   placeholder="{{ config('saas.support_email') }}"
                                   value="{{ old('notify_email', $settings['notify_email'] ?? '') }}">
                            <span class="text-secondary-light text-xs">Where new demo / trial requests are sent.</span>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">From email</label>
                            <input type="email" name="mail_from_email" class="form-control"
                                   placeholder="noreply@yourdomain.com"
                                   value="{{ old('mail_from_email', $settings['mail_from_email'] ?? '') }}">
                            <span class="text-secondary-light text-xs">Must be a verified sender in SendGrid (or allowed by your SMTP).</span>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">From name</label>
                            <input type="text" name="mail_from_name" class="form-control"
                                   placeholder="{{ config('saas.name') }}"
                                   value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary px-40 py-12">Save Settings</button>
        </div>
    </div>
</form>

<div class="card radius-12 border-0 shadow-sm mt-24">
    <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Send test email</h6></div>
    <div class="card-body p-24">
        <p class="text-secondary-light text-sm mb-16">
            Verify delivery after updating your SendGrid key or SMTP. Current from address: <code>{{ $mailFrom }}</code>
        </p>
        <form method="POST" action="{{ route('platform.settings.test-mail') }}" class="row g-3 align-items-end"
              data-confirm
              data-confirm-title="Send test email?"
              data-confirm-text="A test message will be sent using the current mail settings."
              data-confirm-label="Send test"
              data-confirm-icon="info"
              data-confirm-tone="primary">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Recipient</label>
                <input type="email" name="test_email" class="form-control" required
                       value="{{ old('test_email', auth('admin')->user()?->email) }}"
                       placeholder="you@example.com">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-outline-primary px-24">Send test email</button>
            </div>
        </form>
    </div>
</div>
@endsection
