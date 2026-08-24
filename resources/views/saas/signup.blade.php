@extends('saas.layout')

@php $saasName = \App\Models\PlatformSetting::get('platform_name', config('saas.name')); @endphp

@section('title', 'Start Your Free Trial — ' . $saasName)

@section('page_css')
        .signup-wrap { display: grid; grid-template-columns: 1fr 1.15fr; gap: 64px; align-items: start; padding: 80px 0; }
        @media (max-width: 900px) { .signup-wrap { grid-template-columns: 1fr; padding: 48px 0; } }
        .signup-form { background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 40px; box-shadow: var(--shadow-lg); }
        .signup-form > h2 { font-size: 22px; font-weight: 800; margin-bottom: 24px; color: var(--ink); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 560px) { .form-row { grid-template-columns: 1fr; } .signup-form { padding: 28px 22px; } }

        .plan-pick { display: flex; flex-direction: column; gap: 12px; margin-bottom: 4px; }
        .plan-card { display: block; cursor: pointer; position: relative; }
        .plan-card__input {
            position: absolute;
            opacity: 0;
            width: 1px;
            height: 1px;
            pointer-events: none;
        }
        .plan-card__surface {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 14px;
            border: 2px solid var(--line);
            border-radius: 14px;
            padding: 16px 18px;
            background: #fff;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }
        .plan-card:hover .plan-card__surface {
            border-color: #cbd5e1;
            box-shadow: var(--shadow-sm);
        }
        .plan-card__input:checked + .plan-card__surface {
            border-color: var(--brand);
            background: linear-gradient(180deg, #f8fbff 0%, #eff6ff 100%);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }
        .plan-card__input:focus-visible + .plan-card__surface {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .25);
        }
        .plan-card__radio {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #94a3b8;
            background: #fff;
            flex-shrink: 0;
            position: relative;
        }
        .plan-card__input:checked + .plan-card__surface .plan-card__radio {
            border-color: var(--brand);
            background: var(--brand);
        }
        .plan-card__input:checked + .plan-card__surface .plan-card__radio::after {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: 50%;
            background: #fff;
        }
        .plan-card__info { min-width: 0; }
        .plan-card__name {
            display: block;
            font-size: 15px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.3;
        }
        .plan-card__tagline {
            display: block;
            margin-top: 3px;
            font-size: 13px;
            font-weight: 500;
            color: var(--ink-soft);
            line-height: 1.45;
        }
        .plan-card__price {
            text-align: right;
            white-space: nowrap;
        }
        .plan-card__amount {
            display: block;
            font-size: 17px;
            font-weight: 800;
            color: var(--ink);
            line-height: 1.2;
        }
        .plan-card__interval {
            display: block;
            margin-top: 2px;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-soft);
            text-transform: lowercase;
        }
        .plan-card__badge {
            display: inline-block;
            margin-top: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--brand);
            background: #dbeafe;
            padding: 3px 8px;
            border-radius: 999px;
        }

        .trust li { display: flex; gap: 10px; margin-bottom: 14px; font-size: 15px; color: var(--ink-soft); }
        .trust li::before { content: "✓"; color: #16a34a; font-weight: 800; flex-shrink: 0; }
        .signup-footnote { font-size: 13px; color: var(--ink-soft); text-align: center; margin-top: 14px; line-height: 1.5; }
        .signup-footnote a { color: var(--brand); font-weight: 600; }
@endsection

@section('content')
<div class="container signup-wrap">
    <div>
        <span class="eyebrow">Free Trial</span>
        <h1 class="display" style="font-size: clamp(32px, 4vw, 46px);">Your school's new home is <span class="grad-text">two minutes away</span></h1>
        <p class="lede" style="margin: 20px 0 32px;">Create your school workspace, invite your team, and start capturing leads today.</p>
        <ul class="trust" style="list-style:none; padding:0;">
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
        <h2>Create your school</h2>

        @if($errors->any())
        <ul class="error-list">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        @endif

        <div class="field">
            <label>Choose your plan *</label>
            <div class="plan-pick">
                @forelse($plans as $plan)
                <label class="plan-card">
                    <input type="radio" class="plan-card__input" name="plan" value="{{ $plan->slug }}"
                           @checked(old('plan', $selectedPlan?->slug) === $plan->slug) required>
                    <span class="plan-card__surface">
                        <span class="plan-card__radio" aria-hidden="true"></span>
                        <span class="plan-card__info">
                            <span class="plan-card__name">{{ $plan->name }}</span>
                            @if(filled($plan->tagline))
                            <span class="plan-card__tagline">{{ $plan->tagline }}</span>
                            @endif
                            @if($plan->is_default)
                            <span class="plan-card__badge">Recommended</span>
                            @elseif($plan->is_featured)
                            <span class="plan-card__badge">Most popular</span>
                            @endif
                        </span>
                        <span class="plan-card__price">
                            <span class="plan-card__amount">{{ $plan->formattedPrice() }}</span>
                            <span class="plan-card__interval">{{ $plan->isLifetime() ? 'one-time' : '/ '.$plan->billingInterval()->shortLabel() }}</span>
                        </span>
                    </span>
                </label>
                @empty
                <p style="font-size:14px; color:var(--ink-soft); padding:12px 0;">Plans are being configured — <a href="{{ route('saas.demo.create') }}" style="color:var(--brand); font-weight:600;">book a demo</a> to get started.</p>
                @endforelse
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
        <p class="signup-footnote">
            By signing up you agree to fair use of the platform. Prefer a guided setup?
            <a href="{{ route('saas.demo.create') }}">Book a demo</a>.
        </p>
    </form>
</div>
@endsection
