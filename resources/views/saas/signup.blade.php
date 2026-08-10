@extends('saas.layout')

@php $saasName = \App\Models\PlatformSetting::get('platform_name', config('saas.name')); @endphp

@section('title', 'Start Your Free Trial — ' . $saasName)

@section('page_css')
        .signup-wrap { display: grid; grid-template-columns: 1fr 1.15fr; gap: 64px; align-items: start; padding: 80px 0; }
        @media (max-width: 900px) { .signup-wrap { grid-template-columns: 1fr; } }
        .signup-form { background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 40px; box-shadow: var(--shadow-lg); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 560px) { .form-row { grid-template-columns: 1fr; } }
        .plan-pick { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
        .plan-option { display: flex; align-items: center; justify-content: space-between; border: 1.5px solid var(--line); border-radius: 12px; padding: 14px 18px; cursor: pointer; transition: all .15s; }
        .plan-option:has(input:checked) { border-color: var(--brand); background: #eff6ff; box-shadow: 0 0 0 3px rgba(37,99,235,.10); }
        .plan-option input { accent-color: var(--brand); }
        .trust li { display: flex; gap: 10px; margin-bottom: 14px; font-size: 15px; color: var(--ink-soft); }
        .trust li::before { content: "✓"; color: #16a34a; font-weight: 800; }
@endsection

@section('content')
<div class="container signup-wrap">
    <div>
        <span class="eyebrow">Free Trial</span>
        <h1 class="display" style="font-size: clamp(32px, 4vw, 46px);">Your school's new home is <span class="grad-text">two minutes away</span></h1>
        <p class="lede" style="margin: 20px 0 32px;">Create your school workspace, invite your team, and start capturing leads today.</p>
        <ul class="trust" style="list-style:none;">
            <li>Full access to every feature during the trial</li>
            <li>No credit card required to start</li>
            <li>Your data is private to your school — always</li>
            <li>Friendly humans on support if you get stuck</li>
        </ul>
        <img src="{{ asset('frontend/assets/img/saas/saas-hero-dashboard.png') }}" alt="{{ $saasName }} dashboard"
             style="border-radius: 14px; box-shadow: var(--shadow-lg); border: 1px solid var(--line); margin-top: 16px;">
    </div>

    <form class="signup-form" method="POST" action="{{ route('saas.signup.store') }}">
        @csrf
        <h2 style="font-size: 22px; font-weight: 800; margin-bottom: 24px;">Create your school</h2>

        @if($errors->any())
        <ul class="error-list">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        @endif

        <div class="field">
            <label>Choose your plan *</label>
            <div class="plan-pick">
                @foreach($plans as $plan)
                <label class="plan-option">
                    <span style="display:flex; align-items:center; gap:12px;">
                        <input type="radio" name="plan" value="{{ $plan->slug }}"
                               @checked(old('plan', $selectedPlan?->slug) === $plan->slug) required>
                        <span>
                            <strong style="font-size:14.5px;">{{ $plan->name }}</strong>
                            <span style="display:block; font-size:12.5px; color:var(--muted);">{{ $plan->tagline }}</span>
                        </span>
                    </span>
                    <strong style="font-size:15px;">{{ $plan->formattedPrice() }}<small style="color:var(--muted); font-weight:500;">/{{ $plan->billing_interval }}</small></strong>
                </label>
                @endforeach
            </div>
        </div>

        <div class="field">
            <label>School / organisation name *</label>
            <input type="text" name="school_name" required value="{{ old('school_name') }}" placeholder="Bright Minds Academy">
        </div>
        <div class="form-row">
            <div class="field">
                <label>Country</label>
                <input type="text" name="country" value="{{ old('country') }}" placeholder="United Kingdom">
            </div>
            <div class="field">
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+44 …">
            </div>
        </div>

        <hr style="border: none; border-top: 1px solid var(--line); margin: 8px 0 20px;">

        <div class="form-row">
            <div class="field">
                <label>Your name *</label>
                <input type="text" name="admin_name" required value="{{ old('admin_name') }}" placeholder="Jane Smith">
            </div>
            <div class="field">
                <label>Work email *</label>
                <input type="email" name="admin_email" required value="{{ old('admin_email') }}" placeholder="jane@school.edu">
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label>Password *</label>
                <input type="password" name="admin_password" required minlength="8" placeholder="Min 8 characters">
            </div>
            <div class="field">
                <label>Confirm password *</label>
                <input type="password" name="admin_password_confirmation" required minlength="8" placeholder="Repeat password">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Create My School →</button>
        <p style="font-size: 12.5px; color: var(--muted); text-align: center; margin-top: 14px;">
            By signing up you agree to fair use of the platform. Prefer a guided setup?
            <a href="{{ route('saas.demo.create') }}" style="color: var(--brand); font-weight: 600;">Book a demo</a>.
        </p>
    </form>
</div>
@endsection
