<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Form;
use App\Models\FormEntry;
use App\Services\Crm\FinancialCalculator;

class FinancialIntegrityTest extends CrmTestCase
{
    public function test_calculator_rejects_negative_totals_with_high_discount(): void
    {
        $calculator = app(FinancialCalculator::class);
        $result = $calculator->calculate([
            ['quantity' => 1, 'unit_price' => 100],
        ], 0, 150);

        $this->assertEquals(0.0, $result['total']);
    }

    public function test_calculator_handles_multiple_line_items_with_tax(): void
    {
        $calculator = app(FinancialCalculator::class);
        $result = $calculator->calculate([
            ['quantity' => 3, 'unit_price' => 25.50],
            ['quantity' => 2, 'unit_price' => 10],
        ], 20, 0);

        $this->assertSame(96.5, $result['subtotal']);
        $this->assertSame(19.3, $result['tax_amount']);
        $this->assertSame(115.8, $result['total']);
    }

    public function test_quotation_pdf_returns_pdf_response(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'PDF Customer',
            'email' => 'pdf@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 100,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'status' => 'draft',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Line item',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        $response = $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.pdf', $quotation));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_invoice_pdf_returns_pdf_response(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Invoice PDF Customer',
            'email' => 'invoice-pdf@example.com',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 200,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 200,
            'paid_amount' => 0,
            'due_amount' => 200,
            'status' => 'draft',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Invoice line',
            'quantity' => 1,
            'unit_price' => 200,
            'total' => 200,
        ]);

        $response = $this->actingAsCrmAdmin()
            ->get(route('admin.crm.invoices.pdf', $invoice));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }
}
