<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Al Rushd — Sign In</title>
    <link href="{{ asset('frontend/assets/img/logo.png') }}" rel="icon">
    <link href="{{ asset('frontend/assets/img/logo.png') }}" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --ar-navy: #0F274A;
            --ar-navy-soft: #183a68;
            --ar-gold: #C5A86D;
            --ar-gold-hover: #b8995f;
            --ar-cream: #F7F5F0;
            --ar-text: #0F274A;
            --ar-muted: #6b7280;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background:
                radial-gradient(ellipse 80% 60% at 10% 0%, rgba(197, 168, 109, 0.18), transparent 55%),
                radial-gradient(ellipse 70% 50% at 100% 100%, rgba(15, 39, 74, 0.08), transparent 50%),
                var(--ar-cream);
            color: var(--ar-text);
        }

        .ar-login {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border: 1px solid rgba(15, 39, 74, 0.08);
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(15, 39, 74, 0.1);
            padding: 40px 36px;
        }

        .ar-login-brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .ar-login-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            margin: 0 auto 14px;
            display: block;
        }

        .ar-login-title {
            margin: 0;
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--ar-navy);
            letter-spacing: -0.02em;
        }

        .ar-login-sub {
            margin: 6px 0 0;
            font-size: 14px;
            color: var(--ar-muted);
        }

        .ar-field { margin-bottom: 16px; }

        .ar-field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--ar-navy);
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }

        .ar-input-wrap {
            display: flex;
            align-items: center;
            background: #edf2f7;
            border: 1px solid transparent;
            border-radius: 12px;
            overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .ar-input-wrap:focus-within {
            border-color: var(--ar-gold);
            box-shadow: 0 0 0 3px rgba(197, 168, 109, 0.2);
            background: #fff;
        }

        .ar-input-wrap.is-invalid {
            border-color: #dc2626;
        }

        .ar-input-icon {
            padding: 0 14px;
            color: var(--ar-muted);
            flex-shrink: 0;
        }

        .ar-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 13px 14px 13px 0;
            font-size: 15px;
            color: var(--ar-navy);
            outline: none;
            width: 100%;
            font-family: inherit;
        }

        .ar-input::placeholder { color: #9ca3af; }

        .ar-eye {
            padding: 0 14px;
            color: var(--ar-muted);
            cursor: pointer;
            background: none;
            border: none;
            font-size: 15px;
        }

        .ar-eye:hover { color: var(--ar-navy); }

        .ar-error {
            margin: 6px 0 0;
            font-size: 13px;
            color: #dc2626;
        }

        .ar-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 8px;
        }

        .ar-remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--ar-muted);
            cursor: pointer;
            user-select: none;
        }

        .ar-remember input {
            width: 16px;
            height: 16px;
            accent-color: var(--ar-gold);
        }

        .ar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 12px;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            background: var(--ar-navy);
            color: #fff;
            font-family: inherit;
            transition: background 0.2s, transform 0.15s;
        }

        .ar-btn:hover {
            background: var(--ar-navy-soft);
        }

        .ar-btn:active { transform: translateY(1px); }

        .ar-login-foot {
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid rgba(15, 39, 74, 0.08);
            text-align: center;
            font-size: 12px;
            color: var(--ar-muted);
        }

        .ar-login-foot a {
            color: var(--ar-gold);
            text-decoration: none;
            font-weight: 600;
        }

        .ar-login-foot a:hover { color: var(--ar-gold-hover); }

        @media (max-width: 480px) {
            .ar-login { padding: 32px 22px; }
            .ar-actions { flex-direction: column; align-items: stretch; }
            .ar-btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="ar-login">
        <div class="ar-login-brand">
            <img src="{{ asset('frontend/assets/img/logo.png') }}" alt="Al Rushd" class="ar-login-logo">
            <h1 class="ar-login-title">Al Rushd</h1>
            <p class="ar-login-sub">Sign in to your admin account</p>
        </div>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="ar-field">
                <label class="ar-field-label" for="email">Email</label>
                <div class="ar-input-wrap @error('email') is-invalid @enderror">
                    <span class="ar-input-icon"><i class="fas fa-envelope"></i></span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="ar-input"
                        placeholder="Enter your email"
                        value="{{ old('email') }}"
                        autofocus
                        required
                        autocomplete="username"
                    >
                </div>
                @error('email')
                    <p class="ar-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="ar-field">
                <label class="ar-field-label" for="password">Password</label>
                <div class="ar-input-wrap @error('password') is-invalid @enderror">
                    <span class="ar-input-icon"><i class="fas fa-lock"></i></span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="ar-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="ar-eye" onclick="togglePassword()" aria-label="Show password">
                        <i id="eyeIcon" class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <p class="ar-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="ar-actions">
                <label class="ar-remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Remember me
                </label>
                <button type="submit" class="ar-btn">
                    <i class="fas fa-right-to-bracket"></i>
                    Sign In
                </button>
            </div>
        </form>

        <div class="ar-login-foot">
            <a href="{{ url('/') }}">Back to website</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
