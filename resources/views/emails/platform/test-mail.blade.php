@extends('emails.platform.layout')

@section('content')
<h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#0f172a;">Mail delivery works</h1>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
    This is a test message from the {{ $platformName }} owner panel.
    If you received it, transactional email is configured correctly.
</p>
<p style="margin:0;font-size:14px;line-height:1.6;color:#64748b;">
    Sent to: {{ $recipient }}
</p>
@endsection
