@extends('emails.platform.layout')

@section('content')
<h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#0f172a;">Your free trial is ready</h1>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    Hi {{ $request->admin_name }}, your free trial for <strong>{{ $organization->name }}</strong> on {{ $platformName }} has been approved.
</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    Click the button below to set your password (and confirm it). After that you’ll be redirected to the login page.
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin:20px 0;">
    <tr>
        <td style="padding:16px 18px;font-size:14px;color:#166534;line-height:1.8;">
            <strong style="color:#14532d;">Your account</strong><br>
            Email: {{ $request->admin_email }}<br>
            @if($organization->trial_ends_at)
                Trial ends: {{ $organization->trial_ends_at->timezone(config('app.timezone'))->format('d M Y') }}<br>
            @endif
            After setting your password, sign in at
            <a href="{{ $loginUrl }}" style="color:#15803d;">{{ $loginUrl }}</a>
        </td>
    </tr>
</table>
<a href="{{ $setPasswordUrl }}" style="display:inline-block;background:#16a34a;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 20px;border-radius:8px;">
    Set your password →
</a>
<p style="margin:20px 0 0;font-size:14px;line-height:1.6;color:#64748b;">
    This link expires for security. If it stops working, use “Forgot password?” on the login page or reply to this email.
</p>
@endsection
