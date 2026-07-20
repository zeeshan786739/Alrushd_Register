<p>Dear {{ $invoice->customer?->name }},</p>
<p>Please find attached invoice <strong>{{ $invoice->invoice_number }}</strong>.</p>
<p>Total: <strong>{{ number_format($invoice->total, 2) }}</strong></p>
<p>Amount due: <strong>{{ number_format($invoice->due_amount, 2) }}</strong></p>
<p>Due date: {{ $invoice->due_date->format('M j, Y') }}</p>
<p>Thank you,<br>{{ config('app.name') }}</p>
