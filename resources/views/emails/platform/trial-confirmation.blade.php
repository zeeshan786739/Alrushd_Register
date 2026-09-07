@extends('emails.platform.layout')

@section('content')
<h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#0f172a;">We received your free trial request</h1>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    Hi {{ $request->admin_name }}, thanks for choosing {{ $platformName }}. We’ve received your application for
    <strong>{{ $request->school_name }}</strong>
    @if($request->plan)
        on the <strong>{{ $request->plan->name }}</strong> plan
    @endif.
</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    Our team will review your request. Once approved, you’ll get another email with a link to set your password and sign in.
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:20px 0;">
    <tr>
        <td style="padding:16px 18px;font-size:14px;color:#475569;line-height:1.7;">
            <strong style="color:#0f172a;">Application summary</strong><br>
            School: {{ $request->school_name }}<br>
            Admin email: {{ $request->admin_email }}<br>
            @if($request->plan)
                Plan: {{ $request->plan->name }}<br>
            @endif
            Submitted: {{ $request->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}
        </td>
    </tr>
</table>
<p style="margin:0;font-size:14px;line-height:1.6;color:#64748b;">
    No action is needed from you right now. We’ll email you as soon as access is granted.
</p>
@endsection
