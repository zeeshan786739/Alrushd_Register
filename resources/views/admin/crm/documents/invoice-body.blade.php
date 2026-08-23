@php
    use App\Support\CrmDocument;
    $doc = $doc ?? CrmDocument::invoiceViewData($invoice, $documentMode ?? 'preview');
    $t = $doc['text'];
@endphp
<div class="crm-doc-i">
    <div class="crm-doc-i-topbar"></div>

    <table class="crm-doc-i-header">
        <tr>
            <td style="width:55%">
                @if($doc['logo_src'])
                    <img src="{{ $doc['logo_src'] }}" class="crm-doc-i-logo" alt="">
                @endif
                @if($doc['display_name'])
                    <div class="crm-doc-i-brand" {!! CrmDocument::textDir($doc['display_name'], $doc["is_pdf"]) !!}>{{ $doc['display_name'] }}</div>
                @endif
                @if(!empty($doc['brand_lines']))
                    <div class="crm-doc-i-brand-meta">
                        @foreach($doc['brand_lines'] as $line)
                            <div {!! CrmDocument::textDir($line, $doc["is_pdf"]) !!}>{{ $line }}</div>
                        @endforeach
                    </div>
                @endif
            </td>
            <td style="width:45%">
                <div class="crm-doc-i-title-wrap">
                    <div class="crm-doc-i-title" {!! CrmDocument::textDir($doc['heading'], $doc["is_pdf"]) !!}>{{ $doc['heading'] }}</div>
                    @if($doc['subtitle'])
                        <div class="crm-doc-i-muted" style="margin-bottom:6px" {!! CrmDocument::textDir($doc['subtitle'], $doc["is_pdf"]) !!}>{{ $doc['subtitle'] }}</div>
                    @endif
                    <div class="crm-doc-i-number">{{ $invoice->invoice_number }}</div>
                    @if($doc['show_status'])
                        <span class="crm-doc-i-stamp {{ $doc['stamp_class'] }}">{{ $doc['stamp_label'] }}</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if($doc['is_overdue_display'] && $doc['show_status'])
        <p class="crm-doc-i-overdue-note">Payment overdue · Due {{ $invoice->due_date?->format('M j, Y') }}</p>
    @endif

    <table class="crm-doc-i-panels">
        <tr>
            <td>
                <div class="crm-doc-i-bill">
                    <div class="crm-doc-i-label">Bill to</div>
                    <div class="crm-doc-i-name" {!! CrmDocument::textDir($invoice->customer?->name, $doc["is_pdf"]) !!}>{{ $t($invoice->customer?->name) }}</div>
                    @if($invoice->customer?->company)
                        <div class="crm-doc-i-muted" {!! CrmDocument::textDir($invoice->customer->company, $doc["is_pdf"]) !!}>{{ $t($invoice->customer->company) }}</div>
                    @endif
                    @if($doc['show_customer_email'] && $invoice->customer?->email)
                        <div class="crm-doc-i-muted">{{ $invoice->customer->email }}</div>
                    @endif
                    @if($doc['show_customer_phone'] && $invoice->customer?->phone)
                        <div class="crm-doc-i-muted">{{ $invoice->customer->phone }}</div>
                    @endif
                </div>
            </td>
            <td>
                <div class="crm-doc-i-details">
                    <div class="crm-doc-i-label">Invoice details</div>
                    <table class="crm-doc-i-meta">
                        <tr><td>Invoice #</td><td>{{ $invoice->invoice_number }}</td></tr>
                        @if($doc['show_issue_date'])
                            <tr><td>Issue date</td><td>{{ $invoice->invoice_date?->format('M j, Y') ?? '—' }}</td></tr>
                        @endif
                        @if($doc['show_due_date'])
                            <tr><td>Due date</td><td>{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</td></tr>
                        @endif
                        @if($doc['show_project'] && $invoice->project)
                            <tr>
                                <td>Project</td>
                                <td {!! CrmDocument::textDir($invoice->project->name, $doc["is_pdf"]) !!}>{{ $t($invoice->project->name) }}</td>
                            </tr>
                        @endif
                        @if($doc['show_source_quotation'] && $invoice->quotation)
                            <tr>
                                <td>Source quote</td>
                                <td>{{ $invoice->quotation->quotation_number }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="crm-doc-i-items">
        <thead>
            <tr>
                <th style="width:48%">Description</th>
                <th class="num" style="width:12%">Qty</th>
                <th class="num" style="width:20%">Unit price</th>
                <th class="num" style="width:20%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                <tr>
                    <td class="crm-doc-i-desc" {!! CrmDocument::textDir($item->description, $doc["is_pdf"]) !!}>{{ $t($item->description) }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ CrmDocument::money($item->unit_price) }}</td>
                    <td class="num">{{ CrmDocument::money($item->total) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No line items.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="crm-doc-i-summary-wrap">
        <table class="crm-doc-i-summary">
            @if($doc['show_subtotal'])
                <tr>
                    <td>Subtotal</td>
                    <td class="num">{{ CrmDocument::money($invoice->subtotal) }}</td>
                </tr>
            @endif
            @if($doc['show_discount'] && (float) $invoice->discount_amount > 0)
                <tr>
                    <td>Discount ({{ CrmDocument::money($invoice->discount_percentage) }}%)</td>
                    <td class="num">-{{ CrmDocument::money($invoice->discount_amount) }}</td>
                </tr>
            @endif
            @if($doc['show_tax'] && ((float) $invoice->tax_amount > 0 || (float) $invoice->tax_percentage > 0))
                <tr>
                    <td>Tax ({{ CrmDocument::money($invoice->tax_percentage) }}%)</td>
                    <td class="num">{{ CrmDocument::money($invoice->tax_amount) }}</td>
                </tr>
            @endif
            @if($doc['show_total'])
                <tr class="total-row">
                    <td>Total</td>
                    <td class="num">{{ CrmDocument::money($invoice->total) }}</td>
                </tr>
            @endif
            @if($doc['show_amount_paid'])
                <tr>
                    <td>Already paid</td>
                    <td class="num">{{ CrmDocument::money($invoice->paid_amount) }}</td>
                </tr>
            @endif
            @if($doc['show_balance_due'])
                <tr class="balance-row {{ $doc['is_paid'] ? 'paid-zero' : '' }}">
                    <td>Balance due</td>
                    <td class="num">{{ CrmDocument::money($invoice->due_amount) }}</td>
                </tr>
            @endif
        </table>
    </div>

    @if($doc['show_payment_history'] && ($invoice->payments ?? null) && $invoice->payments->isNotEmpty())
        <div class="crm-doc-i-payments">
            <div class="crm-doc-i-label">Payments received</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="num">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', (string) $payment->payment_method)) }}</td>
                            <td>{{ $payment->transaction_id ?: '—' }}</td>
                            <td class="num">{{ CrmDocument::money($payment->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($doc['payment_instructions']))
        <div class="crm-doc-i-section">
            <div class="crm-doc-i-label">Payment information</div>
            @foreach($doc['payment_instructions'] as $row)
                <p><strong>{{ $row['label'] }}:</strong> <span {!! CrmDocument::textDir($row['value'], $doc["is_pdf"]) !!}>{{ $row['value'] }}</span></p>
            @endforeach
        </div>
    @endif

    @if($doc['terms_text'])
        <div class="crm-doc-i-section">
            <div class="crm-doc-i-label">Terms</div>
            <p {!! CrmDocument::textDir($doc['terms_text'], $doc["is_pdf"]) !!}>{{ $doc['terms_text'] }}</p>
        </div>
    @endif

    <div class="crm-doc-i-footer">
        @if($doc['footer_text'])
            <div {!! CrmDocument::textDir($doc['footer_text'], $doc["is_pdf"]) !!}>{{ $doc['footer_text'] }}</div>
        @endif
        <div>
            {{ $invoice->invoice_number }}
            @if($doc['is_paid'])
                · Paid in full
            @endif
        </div>
    </div>
</div>
