<p>Hello {{ $lead->full_name }},</p>
<div>{!! nl2br(e($body)) !!}</div>
<p>Regards,<br>{{ config('app.name') }}</p>
