<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>AL-Rushd — {{ $invitation ? 'Set up account' : 'Reset password' }}</title>
    <link href="{{ asset('frontend/assets/img/logo.png') }}" rel="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('admin.auth.partials.access-styles')
</head>
<body>
<main class="ar-card">
    <div class="ar-brand">
        <img class="ar-logo" src="{{ asset('frontend/assets/img/logo.png') }}" alt="AL-Rushd">
        <h1 class="ar-title">{{ $invitation ? 'Welcome to the team' : 'Create a new password' }}</h1>
        <p class="ar-sub">{{ $invitation ? 'Secure your account by choosing your password.' : 'Choose a strong password you have not used elsewhere.' }}</p>
    </div>
    <form method="POST" action="{{ route('admin.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="ar-field">
            <label class="ar-label" for="email">Work email</label>
            <div class="ar-input-wrap @error('email') is-invalid @enderror"><span class="ar-icon"><i class="fas fa-envelope"></i></span><input class="ar-input" type="email" id="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username"></div>
            @error('email')<p class="ar-error">{{ $message }}</p>@enderror
        </div>
        <div class="ar-field">
            <label class="ar-label" for="password">New password</label>
            <div class="ar-input-wrap @error('password') is-invalid @enderror"><span class="ar-icon"><i class="fas fa-lock"></i></span><input class="ar-input" type="password" id="password" name="password" required autocomplete="new-password" minlength="8"><button class="ar-eye" type="button" data-toggle="password" aria-label="Show password"><i class="fas fa-eye"></i></button></div>
            @error('password')<p class="ar-error">{{ $message }}</p>@enderror
        </div>
        <div class="ar-field">
            <label class="ar-label" for="password_confirmation">Confirm password</label>
            <div class="ar-input-wrap"><span class="ar-icon"><i class="fas fa-shield-halved"></i></span><input class="ar-input" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" minlength="8"><button class="ar-eye" type="button" data-toggle="password_confirmation" aria-label="Show password confirmation"><i class="fas fa-eye"></i></button></div>
        </div>
        <p class="ar-hint">Use at least 8 characters. A longer, unique password is recommended.</p>
        <button class="ar-btn" type="submit"><i class="fas fa-check"></i>{{ $invitation ? 'Set password and continue' : 'Reset password' }}</button>
    </form>
    <div class="ar-foot">Already set your password? <a class="ar-link" href="{{ route('admin.login', ['email' => $email]) }}">Sign in</a></div>
</main>
<script>
document.querySelectorAll('[data-toggle]').forEach(function(button){button.addEventListener('click',function(){var input=document.getElementById(button.dataset.toggle);var icon=button.querySelector('i');var showing=input.type==='text';input.type=showing?'password':'text';icon.classList.toggle('fa-eye',showing);icon.classList.toggle('fa-eye-slash',!showing);});});
</script>
</body>
</html>
