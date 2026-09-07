@extends('emails.platform.layout')

@section('content')
<h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#0f172a;">Thanks — we got your demo request</h1>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    Hi {{ $demo->name }}, thanks for your interest in {{ $platformName }}. We’ve received your request for
    <strong>{{ $demo->organization_name }}</strong> and our team will review it shortly.
</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    You’ll receive another email once your demo access has been approved, with everything you need to sign in.
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:20px 0;">
    <tr>
        <td style="padding:16px 18px;font-size:14px;color:#475569;line-height:1.7;">
            <strong style="color:#0f172a;">Request summary</strong><br>
            School: {{ $demo->organization_name }}<br>
            Email: {{ $demo->email }}<br>
            @if($demo->country)Country: {{ $demo->country }}<br>@endif
            Submitted: {{ $demo->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}
        </td>
    </tr>
</table>
<p style="margin:0;font-size:14px;line-height:1.6;color:#64748b;">
    No action is needed from you right now. We typically respond within one business day.
</p>
@endsection
