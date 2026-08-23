@php
    use App\Support\CrmDocument;
    $doc = $doc ?? CrmDocument::quotationViewData($quotation, $documentMode ?? 'preview');
    $t = $doc['text'];
@endphp
<div class="crm-doc-q">
    <div class="crm-doc-q-header">
        <table>
            <tr>
                <td style="width:58%">
                    @if($doc['logo_src'])
                        <img src="{{ $doc['logo_src'] }}" class="crm-doc-q-logo" alt="">
                    @endif
                    @if($doc['display_name'])
                        <div class="crm-doc-q-brand-name" {!! CrmDocument::textDir($doc['display_name'], $doc["is_pdf"]) !!}>{{ $doc['display_name'] }}</div>
                    @endif
                    @if(!empty($doc['brand_lines']))
                        <div class="crm-doc-q-brand-meta">
                            @foreach($doc['brand_lines'] as $line)
                                <div {!! CrmDocument::textDir($line, $doc["is_pdf"]) !!}>{{ $line }}</div>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td style="width:42%">
                    <div class="crm-doc-q-title-block">
                        <div class="crm-doc-q-title" {!! CrmDocument::textDir($doc['heading'], $doc["is_pdf"]) !!}>{{ $doc['heading'] }}</div>
                        @if($doc['subtitle'])
                            <div class="crm-doc-q-muted" style="margin-bottom:6px" {!! CrmDocument::textDir($doc['subtitle'], $doc["is_pdf"]) !!}>{{ $doc['subtitle'] }}</div>
                        @endif
                        <div class="crm-doc-q-number">{{ $quotation->quotation_number }}</div>
                        @if($doc['show_status'])
                            <span class="crm-doc-q-status {{ $doc['status_class'] }}">{{ $doc['status_label'] }}</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="crm-doc-q-grid">
        <tr>
            <td>
                <div class="crm-doc-q-card crm-doc-q-card-left">
                    <div class="crm-doc-q-label">Prepared for</div>
                    <div class="crm-doc-q-name" {!! CrmDocument::textDir($quotation->customer?->name, $doc["is_pdf"]) !!}>{{ $t($quotation->customer?->name) }}</div>
                    @if($quotation->customer?->company)
                        <div class="crm-doc-q-muted" {!! CrmDocument::textDir($quotation->customer->company, $doc["is_pdf"]) !!}>{{ $t($quotation->customer->company) }}</div>
                    @endif
                    @if($doc['show_customer_email'] && $quotation->customer?->email)
                        <div class="crm-doc-q-muted">{{ $quotation->customer->email }}</div>
                    @endif
                    @if($doc['show_customer_phone'] && $quotation->customer?->phone)
                        <div class="crm-doc-q-muted">{{ $quotation->customer->phone }}</div>
                    @endif
                </div>
            </td>
            <td>
                <div class="crm-doc-q-card crm-doc-q-card-right">
                    <div class="crm-doc-q-label">Details</div>
                    <table class="crm-doc-q-meta-table">
                        <tr><td>Number</td><td>{{ $quotation->quotation_number }}</td></tr>
                        @if($doc['show_issue_date'])
                            <tr><td>Issue date</td><td>{{ $quotation->quotation_date?->format('M j, Y') ?? '—' }}</td></tr>
                        @endif
                        @if($doc['show_valid_until'] && $quotation->valid_until)
                            <tr><td>Valid until</td><td>{{ $quotation->valid_until->format('M j, Y') }}</td></tr>
                        @endif
                        @if($doc['show_project'] && $quotation->project)
                            <tr>
                                <td>Project</td>
                                <td {!! CrmDocument::textDir($quotation->project->name, $doc["is_pdf"]) !!}>{{ $t($quotation->project->name) }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="crm-doc-q-items">
        <thead>
            <tr>
                <th style="width:48%">Description</th>
                <th class="num" style="width:12%">Qty</th>
                <th class="num" style="width:20%">Unit price</th>
                <th class="num" style="width:20%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotation->items as $item)
                <tr>
                    <td class="crm-doc-q-desc" {!! CrmDocument::textDir($item->description, $doc["is_pdf"]) !!}>{{ $t($item->description) }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ CrmDocument::money($item->unit_price) }}</td>
                    <td class="num">{{ CrmDocument::money($item->total) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No line items.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="crm-doc-q-totals-wrap">
        <table class="crm-doc-q-totals">
            @if($doc['show_subtotal'])
                <tr>
                    <td>Subtotal</td>
                    <td class="num">{{ CrmDocument::money($quotation->subtotal) }}</td>
                </tr>
            @endif
            @if($doc['show_discount'] && (float) $quotation->discount_amount > 0)
                <tr>
                    <td>Discount ({{ CrmDocument::money($quotation->discount_percentage) }}%)</td>
                    <td class="num">-{{ CrmDocument::money($quotation->discount_amount) }}</td>
                </tr>
            @endif
            @if($doc['show_tax'] && ((float) $quotation->tax_amount > 0 || (float) $quotation->tax_percentage > 0))
                <tr>
                    <td>Tax ({{ CrmDocument::money($quotation->tax_percentage) }}%)</td>
                    <td class="num">{{ CrmDocument::money($quotation->tax_amount) }}</td>
                </tr>
            @endif
            <tr class="grand">
                <td>Grand total</td>
                <td class="num">{{ CrmDocument::money($quotation->total) }}</td>
            </tr>
        </table>
    </div>

    @if($doc['terms_text'])
        <div class="crm-doc-q-section">
            <div class="crm-doc-q-label">Terms</div>
            <p {!! CrmDocument::textDir($doc['terms_text'], $doc["is_pdf"]) !!}>{{ $doc['terms_text'] }}</p>
        </div>
    @endif

    @if($doc['notes_text'])
        <div class="crm-doc-q-section">
            <div class="crm-doc-q-label">Notes</div>
            <p {!! CrmDocument::textDir($doc['notes_text'], $doc["is_pdf"]) !!}>{{ $doc['notes_text'] }}</p>
        </div>
    @endif

    <div class="crm-doc-q-footer">
        @if($doc['footer_text'])
            <div {!! CrmDocument::textDir($doc['footer_text'], $doc["is_pdf"]) !!}>{{ $doc['footer_text'] }}</div>
        @endif
        <div>{{ $quotation->quotation_number }}</div>
    </div>
</div>
