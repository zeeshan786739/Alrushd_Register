<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Quotation {{ $quotation->quotation_number }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111}h1{font-size:20px;margin:0 0 8px}.meta{margin-bottom:24px}table{width:100%;border-collapse:collapse;margin-top:16px}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f5f5f5}.totals{margin-top:16px;width:300px;margin-left:auto}.totals td{border:none;padding:4px 8px}.right{text-align:right}.bold{font-weight:bold}</style></head>
<body>
<h1>Quotation {{ $quotation->quotation_number }}</h1>
<div class="meta">
    <div><strong>Customer:</strong> {{ $quotation->customer?->name }}</div>
    <div><strong>Date:</strong> {{ $quotation->quotation_date->format('M j, Y') }}</div>
    @if($quotation->valid_until)<div><strong>Valid Until:</strong> {{ $quotation->valid_until->format('M j, Y') }}</div>@endif
</div>
<table><thead><tr><th>Description</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
<tbody>@foreach($quotation->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price,2) }}</td><td>{{ number_format($item->total,2) }}</td></tr>@endforeach</tbody></table>
<table class="totals"><tr><td>Subtotal</td><td class="right">{{ number_format($quotation->subtotal,2) }}</td></tr>
<tr><td>Tax ({{ $quotation->tax_percentage }}%)</td><td class="right">{{ number_format($quotation->tax_amount,2) }}</td></tr>
<tr><td>Discount ({{ $quotation->discount_percentage }}%)</td><td class="right">-{{ number_format($quotation->discount_amount,2) }}</td></tr>
<tr><td class="bold">Total</td><td class="right bold">{{ number_format($quotation->total,2) }}</td></tr></table>
@if($quotation->terms)<p><strong>Terms:</strong> {{ $quotation->terms }}</p>@endif
</body></html>
