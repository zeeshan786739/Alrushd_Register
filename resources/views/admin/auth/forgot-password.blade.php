<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>AL-Rushd — Forgot password</title>
    <link href="{{ asset('frontend/assets/img/logo.png') }}" rel="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('admin.auth.partials.access-styles')
</head>
<body>
<main class="ar-card">
    <div class="ar-brand">
        <img class="ar-logo" src="{{ asset('frontend/assets/img/logo.png') }}" alt="AL-Rushd">
        <h1 class="ar-title">Forgot your password?</h1>
        <p class="ar-sub">Enter your admin email and we’ll send you a secure reset link.</p>
    </div>
    @if(session('status'))<div class="ar-status">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('admin.password.email') }}">
        @csrf
        <div class="ar-field">
            <label class="ar-label" for="email">Work email</label>
            <div class="ar-input-wrap @error('email') is-invalid @enderror">
                <span class="ar-icon"><i class="fas fa-envelope"></i></span>
                <input class="ar-input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@school.com">
            </div>
            @error('email')<p class="ar-error">{{ $message }}</p>@enderror
        </div>
        <button class="ar-btn" type="submit"><i class="fas fa-paper-plane"></i>Send reset link</button>
    </form>
    <div class="ar-foot"><a class="ar-link" href="{{ route('admin.login') }}">Back to sign in</a></div>
</main>
</body>
</html>
