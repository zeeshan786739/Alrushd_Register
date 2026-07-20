<p>Dear {{ $quotation->customer?->name }},</p>
<p>Please find attached quotation <strong>{{ $quotation->quotation_number }}</strong> for your review.</p>
<p>Total amount: <strong>{{ number_format($quotation->total, 2) }}</strong></p>
@if($quotation->valid_until)<p>Valid until: {{ $quotation->valid_until->format('M j, Y') }}</p>@endif
<p>Thank you,<br>{{ config('app.name') }}</p>
