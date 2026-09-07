@extends('emails.platform.layout')

@section('content')
<h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#0f172a;">Update on your free trial request</h1>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    Hi {{ $request->admin_name }}, thank you for your interest in {{ $platformName }}.
    After review, we’re unable to approve the free trial for <strong>{{ $request->school_name }}</strong> at this time.
</p>
@if(!empty($reason))
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;margin:20px 0;">
    <tr>
        <td style="padding:16px 18px;font-size:14px;color:#9a3412;line-height:1.7;">
            <strong>Note from our team</strong><br>
            {{ $reason }}
        </td>
    </tr>
</table>
@endif
<p style="margin:0;font-size:14px;line-height:1.6;color:#64748b;">
    If you’d like to discuss options or book a demo instead, reply to this email or contact
    <a href="mailto:{{ $supportEmail }}" style="color:#2563eb;">{{ $supportEmail }}</a>.
</p>
@endsection
