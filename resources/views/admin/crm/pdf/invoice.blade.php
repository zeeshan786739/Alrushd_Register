<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Invoice {{ $invoice->invoice_number }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111}h1{font-size:20px;margin:0 0 8px}.meta{margin-bottom:24px}table{width:100%;border-collapse:collapse;margin-top:16px}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f5f5f5}.totals{margin-top:16px;width:300px;margin-left:auto}.totals td{border:none;padding:4px 8px}.right{text-align:right}.bold{font-weight:bold}</style></head>
<body>
<h1>Invoice {{ $invoice->invoice_number }}</h1>
<div class="meta">
    <div><strong>Customer:</strong> {{ $invoice->customer?->name }}</div>
    <div><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M j, Y') }}</div>
    <div><strong>Due Date:</strong> {{ $invoice->due_date->format('M j, Y') }}</div>
</div>
<table><thead><tr><th>Description</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
<tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price,2) }}</td><td>{{ number_format($item->total,2) }}</td></tr>@endforeach</tbody></table>
<table class="totals"><tr><td>Subtotal</td><td class="right">{{ number_format($invoice->subtotal,2) }}</td></tr>
<tr><td>Tax ({{ $invoice->tax_percentage }}%)</td><td class="right">{{ number_format($invoice->tax_amount,2) }}</td></tr>
<tr><td>Discount ({{ $invoice->discount_percentage }}%)</td><td class="right">-{{ number_format($invoice->discount_amount,2) }}</td></tr>
<tr><td class="bold">Total</td><td class="right bold">{{ number_format($invoice->total,2) }}</td></tr>
<tr><td>Paid</td><td class="right">{{ number_format($invoice->paid_amount,2) }}</td></tr>
<tr><td class="bold">Amount Due</td><td class="right bold">{{ number_format($invoice->due_amount,2) }}</td></tr></table>
@if($invoice->terms)<p><strong>Terms:</strong> {{ $invoice->terms }}</p>@endif
</body></html>
