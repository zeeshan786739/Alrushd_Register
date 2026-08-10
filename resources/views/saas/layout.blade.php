<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $saasName = \App\Models\PlatformSetting::get('platform_name', config('saas.name')); @endphp
    <title>@yield('title', $saasName . ' — The All-in-One CRM for Schools')</title>
    <meta name="description" content="@yield('meta_description', $saasName . ' helps schools capture leads, automate admissions, run email campaigns and get paid — all from one beautiful platform.')">
    <meta property="og:title" content="@yield('title', $saasName . ' — The All-in-One CRM for Schools')">
    <meta property="og:image" content="{{ asset('frontend/assets/img/saas/saas-hero-dashboard.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('frontend/assets/img/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --ink-soft: #475569;
            --muted: #64748b;
            --brand: #2563eb;
            --brand-dark: #1d4ed8;
            --violet: #7c3aed;
            --bg: #ffffff;
            --bg-soft: #f8fafc;
            --line: #e2e8f0;
            --radius: 16px;
            --shadow-lg: 0 24px 60px -12px rgba(15, 23, 42, .18);
            --shadow-sm: 0 4px 16px rgba(15, 23, 42, .06);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; color: var(--ink); background: var(--bg); line-height: 1.6; -webkit-font-smoothing: antialiased; }
        img { max-width: 100%; display: block; }
        a { text-decoration: none; color: inherit; }
        .container { max-width: 1160px; margin: 0 auto; padding: 0 24px; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; font-size: 15px; padding: 13px 26px; border-radius: 12px; border: none; cursor: pointer; transition: all .2s ease; }
        .btn-primary { background: linear-gradient(135deg, var(--brand), var(--violet)); color: #fff; box-shadow: 0 8px 24px rgba(37, 99, 235, .35); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(37, 99, 235, .45); }
        .btn-ghost { background: #fff; color: var(--ink); border: 1.5px solid var(--line); }
        .btn-ghost:hover { border-color: var(--brand); color: var(--brand); }
        .btn-lg { padding: 16px 34px; font-size: 16px; }

        /* Navbar */
        .nav-wrap { position: sticky; top: 0; z-index: 100; backdrop-filter: blur(14px); background: rgba(255,255,255,.85); border-bottom: 1px solid var(--line); }
        .nav { display: flex; align-items: center; justify-content: space-between; height: 72px; }
        .nav-logo { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 21px; letter-spacing: -.02em; }
        .nav-logo .mark { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, var(--brand), var(--violet)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; font-weight: 900; }
        .nav-links { display: flex; align-items: center; gap: 28px; font-size: 14.5px; font-weight: 500; color: var(--ink-soft); }
        .nav-links a:hover { color: var(--brand); }
        .nav-cta { display: flex; align-items: center; gap: 12px; }
        .nav-cta .login { font-size: 14.5px; font-weight: 600; color: var(--ink-soft); }
        .nav-cta .btn { padding: 10px 20px; font-size: 14px; }
        @media (max-width: 900px) { .nav-links { display: none; } }

        /* Sections */
        .section { padding: 96px 0; }
        .section-soft { background: var(--bg-soft); }
        .eyebrow { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--brand); background: #eff6ff; padding: 6px 14px; border-radius: 999px; margin-bottom: 18px; }
        h1.display { font-size: clamp(38px, 5.4vw, 62px); line-height: 1.08; font-weight: 900; letter-spacing: -.03em; }
        h2.headline { font-size: clamp(28px, 3.6vw, 42px); line-height: 1.15; font-weight: 800; letter-spacing: -.02em; margin-bottom: 16px; }
        .lede { font-size: 18px; color: var(--muted); max-width: 640px; }
        .grad-text { background: linear-gradient(90deg, var(--brand), var(--violet)); -webkit-background-clip: text; background-clip: text; color: transparent; }

        /* Cards */
        .card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 30px; transition: all .25s ease; }
        .card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: transparent; }
        .icon-chip { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 25px; margin-bottom: 18px; }

        /* Forms */
        .field label { display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 6px; color: var(--ink); }
        .field input, .field select, .field textarea { width: 100%; padding: 12px 14px; border: 1.5px solid var(--line); border-radius: 10px; font-family: inherit; font-size: 14.5px; color: var(--ink); background: #fff; transition: border .15s; }
        .field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .field { margin-bottom: 16px; }
        .error-list { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 10px; padding: 14px 18px 14px 32px; margin-bottom: 20px; font-size: 14px; }

        /* Footer */
        footer { background: #0b1120; color: #94a3b8; padding: 64px 0 32px; }
        footer h4 { color: #fff; font-size: 14px; margin-bottom: 14px; }
        footer a { display: block; font-size: 14px; margin-bottom: 8px; color: #94a3b8; }
        footer a:hover { color: #fff; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 44px; }
        .footer-bottom { border-top: 1px solid #1e293b; padding-top: 24px; font-size: 13px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
        @media (max-width: 800px) { .footer-grid { grid-template-columns: 1fr 1fr; } }

        @yield('page_css')
    </style>
</head>
<body>

<div class="nav-wrap">
    <div class="container nav">
        <a href="{{ route('saas.landing') }}" class="nav-logo">
            <span class="mark">{{ strtoupper(substr($saasName, 0, 1)) }}</span> {{ $saasName }}
        </a>
        <nav class="nav-links">
            <a href="{{ route('saas.landing') }}#features">Features</a>
            <a href="{{ route('saas.landing') }}#product">Product</a>
            <a href="{{ route('saas.landing') }}#pricing">Pricing</a>
            <a href="{{ route('saas.landing') }}#faq">FAQ</a>
        </nav>
        <div class="nav-cta">
            <a href="{{ route('admin.login') }}" class="login">Log in</a>
            <a href="{{ route('saas.demo.create') }}" class="btn btn-ghost" style="padding: 10px 20px; font-size: 14px;">Book a Demo</a>
            <a href="{{ route('saas.signup') }}" class="btn btn-primary">Start Free Trial</a>
        </div>
    </div>
</div>

@yield('content')

<footer>
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="nav-logo" style="color:#fff; margin-bottom: 14px;">
                    <span class="mark">{{ strtoupper(substr($saasName, 0, 1)) }}</span> {{ $saasName }}
                </div>
                <p style="font-size: 14px; max-width: 300px;">The all-in-one CRM and admissions platform built for schools and education organisations.</p>
            </div>
            <div>
                <h4>Product</h4>
                <a href="{{ route('saas.landing') }}#features">Features</a>
                <a href="{{ route('saas.landing') }}#pricing">Pricing</a>
                <a href="{{ route('saas.signup') }}">Start Free Trial</a>
            </div>
            <div>
                <h4>Company</h4>
                <a href="{{ route('saas.demo.create') }}">Book a Demo</a>
                <a href="mailto:{{ \App\Models\PlatformSetting::get('support_email', config('saas.support_email')) }}">Contact</a>
            </div>
            <div>
                <h4>For Schools</h4>
                <a href="{{ route('admin.login') }}">School Login</a>
                <a href="{{ route('saas.landing') }}#faq">FAQ</a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© {{ date('Y') }} {{ $saasName }}. All rights reserved.</span>
            <span>Made for educators, by educators.</span>
        </div>
    </div>
</footer>

@yield('page_js')
</body>
</html>
