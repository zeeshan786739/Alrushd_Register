@extends('saas.layout')

@php $saasName = \App\Models\PlatformSetting::get('platform_name', config('saas.name')); @endphp

@section('page_css')
        /* Hero */
        .hero { position: relative; overflow: hidden; padding: 100px 0 0; background: radial-gradient(1200px 500px at 70% -10%, rgba(124, 58, 237, .10), transparent 60%), radial-gradient(900px 420px at 20% -10%, rgba(37, 99, 235, .12), transparent 60%), var(--bg); text-align: center; }
        .hero .lede { margin: 22px auto 34px; }
        .hero-cta { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; margin-bottom: 22px; }
        .hero-note { font-size: 13.5px; color: var(--muted); margin-bottom: 56px; }
        .hero-shot { position: relative; max-width: 980px; margin: 0 auto; }
        .hero-shot img { border-radius: 18px 18px 0 0; box-shadow: var(--shadow-lg); border: 1px solid var(--line); border-bottom: none; }
        .hero-glow { position: absolute; inset: auto 10% -30px; height: 80px; background: linear-gradient(90deg, rgba(37,99,235,.4), rgba(124,58,237,.4)); filter: blur(60px); z-index: -1; }

        /* Stats strip */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; text-align: center; }
        .stats h3 { font-size: 34px; font-weight: 900; letter-spacing: -.02em; }
        .stats span { font-size: 14px; color: var(--muted); }
        @media (max-width: 800px) { .stats { grid-template-columns: repeat(2, 1fr); } }

        /* Features */
        .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 52px; }
        .features-grid .card h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .features-grid .card p { font-size: 14.5px; color: var(--muted); }
        @media (max-width: 900px) { .features-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 620px) { .features-grid { grid-template-columns: 1fr; } }

        /* Product split */
        .split { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; margin-top: 72px; }
        .split img { border-radius: 16px; box-shadow: var(--shadow-lg); border: 1px solid var(--line); }
        .split ul { list-style: none; margin-top: 20px; }
        .split ul li { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 12px; font-size: 15px; color: var(--ink-soft); }
        .split ul li::before { content: "✓"; color: #16a34a; font-weight: 800; flex-shrink: 0; }
        @media (max-width: 900px) { .split { grid-template-columns: 1fr; gap: 32px; } .split.reverse > div:first-child { order: 2; } }

        /* How it works */
        .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 52px; counter-reset: step; }
        .step-num { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--brand), var(--violet)); color: #fff; font-weight: 800; font-size: 18px; display: flex; align-items: center; justify-content: center; margin-bottom: 18px; }
        @media (max-width: 800px) { .steps { grid-template-columns: 1fr; } }

        /* Pricing */
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 26px; margin-top: 52px; max-width: 1000px; margin-left: auto; margin-right: auto; }
        .price-card { position: relative; display: flex; flex-direction: column; }
        .price-card.featured { border: 2px solid var(--brand); box-shadow: var(--shadow-lg); }
        .price-card .pop { position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, var(--brand), var(--violet)); color: #fff; font-size: 12px; font-weight: 700; padding: 5px 16px; border-radius: 999px; white-space: nowrap; }
        .price { font-size: 44px; font-weight: 900; letter-spacing: -.03em; margin: 14px 0 2px; }
        .price small { font-size: 15px; color: var(--muted); font-weight: 500; }
        .price-card ul { list-style: none; margin: 22px 0; flex-grow: 1; }
        .price-card ul li { display: flex; gap: 10px; margin-bottom: 11px; font-size: 14.5px; color: var(--ink-soft); }
        .price-card ul li::before { content: "✓"; color: #16a34a; font-weight: 800; }

        /* Testimonials */
        .quotes { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 52px; }
        .quote-card p { font-size: 15px; color: var(--ink-soft); margin-bottom: 20px; }
        .quote-who { display: flex; align-items: center; gap: 12px; }
        .quote-avatar { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #fff; font-size: 16px; }
        .quote-who strong { display: block; font-size: 14px; }
        .quote-who span { font-size: 13px; color: var(--muted); }
        .stars { color: #f59e0b; letter-spacing: 2px; margin-bottom: 14px; }
        @media (max-width: 900px) { .quotes { grid-template-columns: 1fr; } }

        /* FAQ */
        .faq { max-width: 760px; margin: 52px auto 0; }
        .faq details { border: 1px solid var(--line); border-radius: 12px; padding: 20px 24px; margin-bottom: 12px; background: #fff; }
        .faq summary { font-weight: 600; font-size: 15.5px; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; }
        .faq summary::after { content: "+"; font-size: 22px; color: var(--brand); font-weight: 400; }
        .faq details[open] summary::after { content: "–"; }
        .faq details p { margin-top: 12px; font-size: 14.5px; color: var(--muted); }

        /* CTA band */
        .cta-band { background: linear-gradient(135deg, #1e3a8a, #6d28d9); border-radius: 24px; padding: 72px 48px; text-align: center; color: #fff; }
        .cta-band h2 { color: #fff; }
        .cta-band p { color: rgba(255,255,255,.85); max-width: 560px; margin: 12px auto 32px; }
        .cta-band .btn-white { background: #fff; color: var(--brand-dark); }
@endsection

@section('content')

{{-- ===== HERO ===== --}}
<header class="hero">
    <div class="container">
        <span class="eyebrow">✦ The Operating System for Modern Schools</span>
        <h1 class="display">Fill every classroom.<br><span class="grad-text">Automate every admission.</span></h1>
        <p class="lede" style="margin-left:auto; margin-right:auto;">
            {{ $saasName }} brings your leads, admissions, forms, email campaigns and payments into
            one beautiful platform — so your team spends less time on paperwork and more time on students.
        </p>
        <div class="hero-cta">
            <a href="{{ route('saas.signup') }}" class="btn btn-primary btn-lg">Start Free Trial →</a>
            <a href="{{ route('saas.demo.create') }}" class="btn btn-ghost btn-lg">Book a Demo</a>
        </div>
        <p class="hero-note">14-day free trial · No credit card required · Cancel anytime</p>
        <div class="hero-shot">
            <div class="hero-glow"></div>
            <img src="{{ asset('frontend/assets/img/saas/saas-hero-dashboard.png') }}" alt="{{ $saasName }} dashboard">
        </div>
    </div>
</header>

{{-- ===== STATS ===== --}}
<section class="section section-soft" style="padding: 64px 0;">
    <div class="container stats">
        <div><h3 class="grad-text">{{ $schoolsCount }}+</h3><span>Schools onboard</span></div>
        <div><h3 class="grad-text">40%</h3><span>More enquiries converted</span></div>
        <div><h3 class="grad-text">12h</h3><span>Saved per week, per admin</span></div>
        <div><h3 class="grad-text">99.9%</h3><span>Uptime, always on</span></div>
    </div>
</section>

{{-- ===== FEATURES ===== --}}
<section class="section" id="features">
    <div class="container" style="text-align:center;">
        <span class="eyebrow">Everything included</span>
        <h2 class="headline">One platform. Every tool your<br>admissions team needs.</h2>
        <p class="lede" style="margin: 0 auto;">Stop stitching together spreadsheets, form tools and mail merge. {{ $saasName }} replaces them all.</p>

        <div class="features-grid" style="text-align:left;">
            <div class="card">
                <div class="icon-chip" style="background:#eff6ff;">📊</div>
                <h3>Admissions CRM</h3>
                <p>Track every enquiry from first click to enrolment. Pipelines, follow-ups, notes, assignments and conversion analytics built in.</p>
            </div>
            <div class="card">
                <div class="icon-chip" style="background:#f5f3ff;">🧩</div>
                <h3>Drag &amp; Drop Form Builder</h3>
                <p>Publish beautiful multi-step admission, enquiry and staff application forms in minutes — no developers required.</p>
            </div>
            <div class="card">
                <div class="icon-chip" style="background:#ecfdf5;">📣</div>
                <h3>Lead Ads Integrations</h3>
                <p>Connect Facebook and TikTok Lead Ads and watch new leads flow straight into your CRM in real time, mapped to your fields.</p>
            </div>
            <div class="card">
                <div class="icon-chip" style="background:#fff7ed;">✉️</div>
                <h3>Email Marketing</h3>
                <p>Send campaigns from your own mailbox, track opens and clicks, and nurture parents with templates that convert.</p>
            </div>
            <div class="card">
                <div class="icon-chip" style="background:#fef2f2;">💳</div>
                <h3>Payments &amp; Invoicing</h3>
                <p>Collect admission fees online with Stripe, issue branded quotations and invoices, and reconcile everything automatically.</p>
            </div>
            <div class="card">
                <div class="icon-chip" style="background:#f0f9ff;">🌐</div>
                <h3>Website CMS</h3>
                <p>A polished public website for your school with a visual editor, versioned publishing and built-in lead capture forms.</p>
            </div>
        </div>
    </div>
</section>

{{-- ===== PRODUCT TOUR ===== --}}
<section class="section section-soft" id="product">
    <div class="container">
        <div style="text-align:center;">
            <span class="eyebrow">See it in action</span>
            <h2 class="headline">Built around how schools actually work</h2>
        </div>

        <div class="split">
            <div>
                <h2 class="headline" style="font-size: 28px;">From ad click to enrolled student — without the spreadsheet chaos</h2>
                <p class="lede" style="font-size: 16px;">Every enquiry lands in one pipeline, whatever the source: your website, a form, a Facebook ad or a phone call your team logs.</p>
                <ul>
                    <li>Kanban pipeline with custom stages and one-click follow-ups</li>
                    <li>Automatic lead capture from Facebook &amp; TikTok Lead Ads</li>
                    <li>Assign leads to staff, schedule appointments, never drop a family</li>
                    <li>Convert to student records with full history preserved</li>
                </ul>
            </div>
            <div>
                <img src="{{ asset('frontend/assets/img/saas/saas-crm-pipeline.png') }}" alt="Leads pipeline">
            </div>
        </div>

        <div class="split reverse">
            <div>
                <img src="{{ asset('frontend/assets/img/saas/saas-form-builder.png') }}" alt="Form builder">
            </div>
            <div>
                <h2 class="headline" style="font-size: 28px;">Admission forms parents actually finish</h2>
                <p class="lede" style="font-size: 16px;">Build multi-step forms with conditional fields, file uploads and payments — then publish them to your site instantly.</p>
                <ul>
                    <li>Drag-and-drop builder with steps, validation and field mapping</li>
                    <li>Submissions flow into the CRM and can auto-create leads</li>
                    <li>Collect application fees inside the form with Stripe</li>
                    <li>Export everything to Excel or PDF in one click</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- ===== HOW IT WORKS ===== --}}
<section class="section">
    <div class="container" style="text-align:center;">
        <span class="eyebrow">Up and running in a day</span>
        <h2 class="headline">How {{ $saasName }} works</h2>
        <div class="steps" style="text-align:left;">
            <div class="card">
                <div class="step-num">1</div>
                <h3 style="font-size:18px; margin-bottom:8px;">Create your school</h3>
                <p style="font-size:14.5px; color:var(--muted);">Sign up in two minutes, or book a demo and we'll onboard your team personally. Your data stays private to your school.</p>
            </div>
            <div class="card">
                <div class="step-num">2</div>
                <h3 style="font-size:18px; margin-bottom:8px;">Plug in your channels</h3>
                <p style="font-size:14.5px; color:var(--muted);">Publish your forms, connect your ad accounts and mailbox. Existing enquiries can be imported for you.</p>
            </div>
            <div class="card">
                <div class="step-num">3</div>
                <h3 style="font-size:18px; margin-bottom:8px;">Watch enrolments grow</h3>
                <p style="font-size:14.5px; color:var(--muted);">Your team works one shared pipeline with automated follow-ups, campaigns and payments — and you see it all on one dashboard.</p>
            </div>
        </div>
    </div>
</section>

{{-- ===== PRICING ===== --}}
<section class="section section-soft" id="pricing">
    <div class="container" style="text-align:center;">
        <span class="eyebrow">Simple pricing</span>
        <h2 class="headline">Plans that grow with your school</h2>
        <p class="lede" style="margin: 0 auto;">Every plan starts with a free trial. No setup fees, no long contracts.</p>

        <div class="pricing-grid" style="text-align:left;">
            @forelse($plans as $plan)
            <div class="card price-card {{ $plan->is_featured ? 'featured' : '' }}">
                @if($plan->is_featured)<span class="pop">Most Popular</span>@endif
                <h3 style="font-size:18px; font-weight:700;">{{ $plan->name }}</h3>
                <p style="font-size:13.5px; color:var(--muted);">{{ $plan->tagline }}</p>
                <div class="price">{{ $plan->formattedPriceWithInterval() }}</div>
                <span style="font-size:13px; color:var(--muted);">{{ $plan->trial_days }}-day free trial</span>
                <ul>
                    @foreach(($plan->features ?? []) as $feature)
                    <li>{{ $feature }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('saas.signup', ['plan' => $plan->slug]) }}"
                   class="btn {{ $plan->is_featured ? 'btn-primary' : 'btn-ghost' }}" style="width:100%;">
                    Start Free Trial
                </a>
            </div>
            @empty
            <p style="grid-column: 1/-1; color: var(--muted);">Pricing is being finalised — <a href="{{ route('saas.demo.create') }}" style="color:var(--brand); font-weight:600;">book a demo</a> and we'll tailor a plan for you.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- ===== TESTIMONIALS ===== --}}
<section class="section">
    <div class="container" style="text-align:center;">
        <span class="eyebrow">Loved by school teams</span>
        <h2 class="headline">Don't take our word for it</h2>
        <div class="quotes" style="text-align:left;">
            <div class="card quote-card">
                <div class="stars">★★★★★</div>
                <p>"We moved our whole admissions process off spreadsheets in a week. Enquiries from our Facebook ads now land in the pipeline before the parent has even closed the app."</p>
                <div class="quote-who">
                    <div class="quote-avatar" style="background:linear-gradient(135deg,#2563eb,#7c3aed);">AH</div>
                    <div><strong>Aisha Hamid</strong><span>Admissions Lead, AL-Rushd Online School</span></div>
                </div>
            </div>
            <div class="card quote-card">
                <div class="stars">★★★★★</div>
                <p>"The form builder alone is worth it. We publish a new admission form every term and collect the application fee inside the form — zero back-and-forth."</p>
                <div class="quote-who">
                    <div class="quote-avatar" style="background:linear-gradient(135deg,#059669,#0ea5e9);">MK</div>
                    <div><strong>Mohammed Khan</strong><span>Registrar, Bright Minds Academy</span></div>
                </div>
            </div>
            <div class="card quote-card">
                <div class="stars">★★★★★</div>
                <p>"Our open rates doubled once we started segmenting parents with the built-in email campaigns. And the team finally works in one place."</p>
                <div class="quote-who">
                    <div class="quote-avatar" style="background:linear-gradient(135deg,#d946ef,#f43f5e);">SB</div>
                    <div><strong>Sarah Bennett</strong><span>Head of Marketing, Crescent Schools Group</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== FAQ ===== --}}
<section class="section section-soft" id="faq">
    <div class="container" style="text-align:center;">
        <span class="eyebrow">Questions?</span>
        <h2 class="headline">Frequently asked questions</h2>
        <div class="faq" style="text-align:left;">
            <details>
                <summary>Is my school's data isolated from other schools?</summary>
                <p>Yes. Every school is a separate organisation on the platform — your leads, forms, campaigns and settings are completely private to your team, enforced at the database level.</p>
            </details>
            <details>
                <summary>Do I need a credit card to start the trial?</summary>
                <p>No. Sign up, explore everything for the full trial period, and only add payment when you're ready to continue. You can also book a demo first and we'll set everything up with you.</p>
            </details>
            <details>
                <summary>Can you migrate our existing enquiries and student records?</summary>
                <p>Yes — CSV and Excel imports are supported, and on Growth and Scale plans our team handles the migration for you as part of onboarding.</p>
            </details>
            <details>
                <summary>How do the Facebook / TikTok integrations work?</summary>
                <p>Connect your ad account once, map the lead form fields to your CRM fields, and every new lead is synced automatically within seconds via webhooks — no CSV downloads ever again.</p>
            </details>
            <details>
                <summary>Can we cancel anytime?</summary>
                <p>Absolutely. Plans are monthly with no lock-in. If you cancel, your data remains exportable and we keep it safe for 90 days.</p>
            </details>
        </div>
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="section" style="padding-top: 24px;">
    <div class="container">
        <div class="cta-band">
            <h2 class="headline">Ready to grow your school?</h2>
            <p>Join the schools already running their admissions on {{ $saasName }}. Set up takes minutes — and our team is with you the whole way.</p>
            <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                <a href="{{ route('saas.signup') }}" class="btn btn-white btn-lg">Start Free Trial</a>
                <a href="{{ route('saas.demo.create') }}" class="btn btn-lg" style="background:rgba(255,255,255,.14); color:#fff; border:1.5px solid rgba(255,255,255,.4);">Book a Demo</a>
            </div>
        </div>
    </div>
</section>

@endsection
