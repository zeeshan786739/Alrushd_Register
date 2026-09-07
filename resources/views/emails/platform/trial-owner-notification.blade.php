@extends('emails.platform.layout')

@section('content')
<h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#0f172a;">New free trial request</h1>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    A school has applied for a free trial on {{ $platformName }}. Approve the request in the owner panel to grant access.
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:20px 0;">
    <tr>
        <td style="padding:16px 18px;font-size:14px;color:#475569;line-height:1.8;">
            <strong style="color:#0f172a;">{{ $request->school_name }}</strong><br>
            Admin: {{ $request->admin_name }} &lt;{{ $request->admin_email }}&gt;<br>
            @if($request->phone)
                Phone: {{ $request->phone }}<br>
            @endif
            @if($request->country)
                Country: {{ $request->country }}<br>
            @endif
            @if($request->plan)
                Plan: {{ $request->plan->name }}
                @if(! $request->plan->isFree())
                    — {{ strtoupper($request->plan->currency ?? 'USD') }} {{ number_format((float) $request->plan->price, 2) }}/{{ $request->plan->billing_interval ?? 'month' }}
                @endif
                <br>
            @endif
        </td>
    </tr>
</table>
<a href="{{ $reviewUrl }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 20px;border-radius:8px;">
    Review &amp; approve in panel →
</a>
@endsection
