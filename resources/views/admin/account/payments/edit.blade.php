@extends('admin.layouts.app')
@section('title', 'Payment Settings')
@section('content')
@include('admin.account.partials.shell', [
    'activeTab' => 'payments',
    'shellTitle' => 'Customer payments',
    'shellSubtitle' => 'Connect your Stripe account to collect fees from forms, admissions, and checkout pages.',
])

<div class="em-panel em-settings-panel">
    <form method="POST" action="{{ route('admin.account.payments.update') }}" class="em-settings-form">
        @csrf
        @method('PUT')

        <div class="em-settings-stack">
            <section class="em-form-block">
                <div class="em-form-block__head">
                    <span class="em-form-block__icon"><iconify-icon icon="solar:wallet-money-linear"></iconify-icon></span>
                    <div>
                        <h3 class="em-form-block__title">Stripe connection</h3>
                        <p class="em-form-block__desc">These keys are for your school’s customer payments — separate from your Enrolliq subscription billing.</p>
                    </div>
                </div>

                <div class="em-settings-alert {{ $stripe->isConfigured() ? 'em-settings-alert--success' : 'em-settings-alert--warning' }} mb-16">
                    <span class="em-settings-alert__icon">
                        <iconify-icon icon="{{ $stripe->isConfigured() ? 'solar:check-circle-linear' : 'solar:info-circle-linear' }}"></iconify-icon>
                    </span>
                    <div class="em-settings-alert__body">
                        <strong>{{ $stripe->isConfigured() ? 'Ready to accept payments' : 'Not configured yet' }}</strong>
                        <p class="mb-0">
                            @if($settings->last_verified_at)
                                Last verified {{ $settings->last_verified_at->diffForHumans() }}.
                            @else
                                Save your keys, then test the connection below.
                            @endif
                        </p>
                    </div>
                </div>

                <label class="em-toggle-row em-toggle-row--featured">
                    <span class="em-toggle-row__text">
                        <strong>Enable online payments</strong>
                        <small>Allow Stripe checkout on forms and public payment pages for this school.</small>
                    </span>
                    <input class="em-toggle-row__input" type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $settings->is_enabled))>
                    <span class="em-toggle-row__switch" aria-hidden="true"></span>
                </label>

                <label class="em-toggle-row">
                    <span class="em-toggle-row__text">
                        <strong>Test mode</strong>
                        <small>Use test keys (pk_test_ / sk_test_) while setting up — no real charges.</small>
                    </span>
                    <input class="em-toggle-row__input" type="checkbox" name="test_mode" value="1" @checked(old('test_mode', $settings->test_mode ?? true))>
                    <span class="em-toggle-row__switch" aria-hidden="true"></span>
                </label>
            </section>

            <section class="em-form-block">
                <div class="em-form-block__head">
                    <span class="em-form-block__icon"><iconify-icon icon="solar:key-linear"></iconify-icon></span>
                    <div>
                        <h3 class="em-form-block__title">API keys</h3>
                        <p class="em-form-block__desc">Find these in your Stripe Dashboard → Developers → API keys.</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label" for="stripe_publishable_key">Publishable key</label>
                        <input id="stripe_publishable_key" name="stripe_publishable_key" class="form-control radius-8" value="{{ old('stripe_publishable_key', $settings->stripe_publishable_key) }}" placeholder="pk_test_… or pk_live_…" autocomplete="off">
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label" for="stripe_secret">Secret key</label>
                        <input id="stripe_secret" type="password" name="stripe_secret" class="form-control radius-8" value="" placeholder="{{ $settings->stripe_secret ? '•••••••• (leave blank to keep)' : 'sk_test_… or sk_live_…' }}" autocomplete="new-password">
                        <div class="form-text">Never share this key. It stays encrypted on the server.</div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label" for="stripe_webhook_secret">Webhook secret (optional)</label>
                        <input id="stripe_webhook_secret" type="password" name="stripe_webhook_secret" class="form-control radius-8" value="" placeholder="{{ $settings->stripe_webhook_secret ? '•••••••• (leave blank to keep)' : 'whsec_…' }}" autocomplete="new-password">
                        <div class="form-text">For automated payment status updates in a future release.</div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label" for="statement_descriptor">Statement descriptor (optional)</label>
                        <input id="statement_descriptor" name="statement_descriptor" maxlength="22" class="form-control radius-8" value="{{ old('statement_descriptor', $settings->statement_descriptor) }}" placeholder="e.g. AL-RUSHD FEES">
                        <div class="form-text">Shown on customer card statements (max 22 characters).</div>
                    </div>
                </div>
            </section>

            <section class="em-form-block">
                <div class="em-form-block__head">
                    <span class="em-form-block__icon"><iconify-icon icon="solar:question-circle-linear"></iconify-icon></span>
                    <div>
                        <h3 class="em-form-block__title">Need help?</h3>
                        <p class="em-form-block__desc mb-0">Use test card <code>4242 4242 4242 4242</code> with any future expiry in test mode.</p>
                    </div>
                </div>
            </section>
        </div>

        <div class="em-settings-footer">
            <p class="em-settings-footer__hint">Keys are scoped to this school only. Platform billing uses separate Stripe credentials.</p>
            <div class="d-flex flex-wrap gap-10">
                @if($settings->stripe_secret)
                <button formaction="{{ route('admin.account.payments.test') }}" formmethod="POST" class="btn btn-outline-neutral-500 radius-8 fc-btn" type="submit">
                    @csrf
                    <iconify-icon icon="solar:plug-circle-linear"></iconify-icon> Test connection
                </button>
                @endif
                <button class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn" type="submit">
                    <iconify-icon icon="solar:diskette-linear"></iconify-icon> Save payment settings
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
