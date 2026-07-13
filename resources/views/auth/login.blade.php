<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — {{ config('app.name') }}</title>
    <link href="{{ asset('frontend/') }}/assets/img/logo.png" rel="icon">
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/lib/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/remixicon.css">
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/alrushad-overrides.css">
    <style>
        :root {
            --crm-brand: #4569e6;
            --crm-brand-hover: #3458d4;
            --crm-surface-sunken: #f4f6fa;
        }
        body.crm-auth {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(135deg, #eef2ff 0%, #f4f6fa 45%, #e8ecf2 100%);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .crm-auth-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border: 1px solid #e8ecf2;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.10);
            padding: clamp(28px, 5vw, 40px);
            animation: crmPageIn 0.45s cubic-bezier(0.4, 0, 0.2, 1) both;
        }
        .crm-auth-logo {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: rgba(69, 105, 230, 0.10);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .crm-auth-logo img { max-width: 36px; max-height: 36px; }
        .crm-auth-title {
            font-size: 1.375rem;
            font-weight: 700;
            text-align: center;
            color: #111827;
            margin: 0;
        }
        .crm-auth-subtitle {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin: 8px 0 28px;
        }
        .crm-auth-field {
            position: relative;
            margin-bottom: 16px;
        }
        .crm-auth-field iconify-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            pointer-events: none;
        }
        .crm-auth-field input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #e8ecf2;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .crm-auth-field input:focus {
            outline: none;
            border-color: var(--crm-brand);
            box-shadow: 0 0 0 3px rgba(69, 105, 230, 0.15);
        }
        .crm-auth-field input.is-invalid { border-color: #ef4444; }
        .crm-auth-error { color: #ef4444; font-size: 13px; margin-top: 6px; }
        .crm-auth-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .crm-auth-remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #64748b;
            cursor: pointer;
        }
        .crm-auth-submit {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            background: var(--crm-brand);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(69, 105, 230, 0.22);
            transition: background 0.2s, transform 0.2s;
        }
        .crm-auth-submit:hover {
            background: var(--crm-brand-hover);
            transform: translateY(-1px);
        }
        @media (max-width: 575px) {
            .crm-auth-actions { flex-direction: column; align-items: stretch; }
            .crm-auth-submit { width: 100%; justify-content: center; }
        }
    </style>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
</head>
<body class="crm-auth">

    <div class="crm-auth-card">
        <div class="crm-auth-logo">
            <img src="{{ asset('frontend/') }}/assets/img/logo.png" alt="{{ config('app.name') }}">
        </div>
        <h1 class="crm-auth-title">{{ config('app.name') }}</h1>
        <p class="crm-auth-subtitle">Sign in to your CRM account</p>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="crm-auth-field">
                <iconify-icon icon="solar:letter-linear"></iconify-icon>
                <input type="email"
                       name="email"
                       placeholder="Email address"
                       value="{{ old('email') }}"
                       class="@error('email') is-invalid @enderror"
                       autofocus
                       required>
            </div>
            @error('email')
                <div class="crm-auth-error">{{ $message }}</div>
            @enderror

            <div class="crm-auth-field">
                <iconify-icon icon="solar:lock-password-linear"></iconify-icon>
                <input type="password"
                       name="password"
                       placeholder="Password"
                       class="@error('password') is-invalid @enderror"
                       required>
            </div>
            @error('password')
                <div class="crm-auth-error">{{ $message }}</div>
            @enderror

            <div class="crm-auth-actions">
                <label class="crm-auth-remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Remember me
                </label>
                <button type="submit" class="crm-auth-submit">
                    <iconify-icon icon="solar:login-2-linear"></iconify-icon>
                    Sign in
                </button>
            </div>
        </form>
    </div>

</body>
</html>
