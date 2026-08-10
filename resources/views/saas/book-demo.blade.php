@extends('saas.layout')

@php $saasName = \App\Models\PlatformSetting::get('platform_name', config('saas.name')); @endphp

@section('title', 'Book a Demo — ' . $saasName)

@section('page_css')
        .demo-wrap { display: grid; grid-template-columns: 1fr 1.1fr; gap: 64px; align-items: start; padding: 80px 0; }
        @media (max-width: 900px) { .demo-wrap { grid-template-columns: 1fr; } }
        .demo-form { background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 40px; box-shadow: var(--shadow-lg); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 560px) { .form-row { grid-template-columns: 1fr; } }
        .success-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 16px; padding: 40px; text-align: center; }
        .perk { display: flex; gap: 14px; margin-bottom: 22px; }
        .perk .icon-chip { margin: 0; flex-shrink: 0; width: 44px; height: 44px; font-size: 20px; }
@endsection

@section('content')
<div class="container demo-wrap">
    <div>
        <span class="eyebrow">Book a Demo</span>
        <h1 class="display" style="font-size: clamp(32px, 4vw, 46px);">See {{ $saasName }} in action — <span class="grad-text">live, with our team</span></h1>
        <p class="lede" style="margin: 20px 0 36px;">A 30-minute walkthrough tailored to your school. We'll cover your admissions workflow, forms, campaigns and pricing — and answer everything.</p>

        <div class="perk">
            <div class="icon-chip" style="background:#eff6ff;">🗓️</div>
            <div><strong style="font-size:15px;">Personalised walkthrough</strong><p style="font-size:14px; color:var(--muted);">We demo with examples from schools your size.</p></div>
        </div>
        <div class="perk">
            <div class="icon-chip" style="background:#f5f3ff;">📦</div>
            <div><strong style="font-size:15px;">Free data migration advice</strong><p style="font-size:14px; color:var(--muted);">Bring your spreadsheets — we'll show you exactly how they map in.</p></div>
        </div>
        <div class="perk">
            <div class="icon-chip" style="background:#ecfdf5;">💬</div>
            <div><strong style="font-size:15px;">No pressure, no obligation</strong><p style="font-size:14px; color:var(--muted);">You'll leave with a clear picture, whatever you decide.</p></div>
        </div>
    </div>

    <div>
        @if(session('demo_submitted'))
        <div class="success-box">
            <div style="font-size: 52px; margin-bottom: 12px;">🎉</div>
            <h2 class="headline" style="font-size: 26px;">Request received!</h2>
            <p style="color: var(--muted); margin-bottom: 24px;">Thanks for your interest — our team will reach out within one business day to schedule your demo.</p>
            <a href="{{ route('saas.landing') }}" class="btn btn-primary">Back to Home</a>
        </div>
        @else
        <form class="demo-form" method="POST" action="{{ route('saas.demo.store') }}">
            @csrf
            <h2 style="font-size: 22px; font-weight: 800; margin-bottom: 24px;">Tell us about your school</h2>

            @if($errors->any())
            <ul class="error-list">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            @endif

            <div class="form-row">
                <div class="field">
                    <label>Your name *</label>
                    <input type="text" name="name" required value="{{ old('name') }}" placeholder="Jane Smith">
                </div>
                <div class="field">
                    <label>Work email *</label>
                    <input type="email" name="email" required value="{{ old('email') }}" placeholder="jane@school.edu">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+44 …">
                </div>
                <div class="field">
                    <label>Country</label>
                    <input type="text" name="country" value="{{ old('country') }}" placeholder="United Kingdom">
                </div>
            </div>
            <div class="field">
                <label>School / organisation name *</label>
                <input type="text" name="organization_name" required value="{{ old('organization_name') }}" placeholder="Bright Minds Academy">
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Organisation type</label>
                    <select name="organization_type">
                        <option value="">Select…</option>
                        @foreach(['School', 'Online School', 'Madrasah', 'College', 'Tuition Centre', 'Other'] as $type)
                            <option value="{{ $type }}" @selected(old('organization_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Number of students</label>
                    <select name="students_count">
                        <option value="">Select…</option>
                        @foreach(['1–100', '100–500', '500–1,000', '1,000–5,000', '5,000+'] as $bracket)
                            <option value="{{ $bracket }}" @selected(old('students_count') === $bracket)>{{ $bracket }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Anything specific you'd like to see?</label>
                <textarea name="message" rows="3" placeholder="e.g. We run Facebook ads and need better lead follow-up…">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Request My Demo</button>
            <p style="font-size: 12.5px; color: var(--muted); text-align: center; margin-top: 14px;">We'll only use your details to arrange the demo. No spam, ever.</p>
        </form>
        @endif
    </div>
</div>
@endsection
