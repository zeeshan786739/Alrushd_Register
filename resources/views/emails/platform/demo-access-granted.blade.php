@extends('emails.platform.layout')

@section('content')
<h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#0f172a;">Your demo access is ready</h1>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    Hi {{ $demo->name }}, great news — your demo workspace for
    <strong>{{ $organization->name }}</strong> on {{ $platformName }} is ready.
</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    Click the button below to choose your password, then you’ll be taken to the login page.
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin:20px 0;">
    <tr>
        <td style="padding:16px 18px;font-size:14px;color:#166534;line-height:1.8;">
            <strong style="color:#14532d;">Your account</strong><br>
            Email: {{ $demo->email }}<br>
            After setting your password, sign in at
            <a href="{{ $loginUrl }}" style="color:#15803d;">{{ $loginUrl }}</a>
        </td>
    </tr>
</table>
<a href="{{ $setPasswordUrl }}" style="display:inline-block;background:#16a34a;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 20px;border-radius:8px;">
    Set your password →
</a>
<p style="margin:20px 0 0;font-size:14px;line-height:1.6;color:#64748b;">
    This link expires for security. If it stops working, reply to this email and we’ll send a fresh one.
</p>
@endsection
