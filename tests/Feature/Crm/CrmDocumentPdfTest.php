<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\InvoicePayment;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Support\CrmDocument;

class CrmDocumentPdfTest extends CrmTestCase
{
    public function test_quotation_pdf_and_preview_preserve_content_and_unicode(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'محمد علي — Café Résumé',
            'email' => 'unicode-q@example.com',
            'company' => 'شركة النور',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'مشروع تجريبي',
            'project_code' => 'PRJ-UNI-Q',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'subtotal' => 20000,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 20000,
            'status' => 'sent',
            'terms' => 'Payment within 14 days.',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'خدمة استشارية Unicode',
            'quantity' => 1,
            'unit_price' => 20000,
            'total' => 20000,
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('Quotation Preview')
            ->assertSee('محمد علي', false)
            ->assertSee('20,000.00')
            ->assertSee($quotation->quotation_number)
            ->assertSee('مشروع تجريبي', false);

        $pdf = $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.pdf', $quotation))
            ->assertOk();

        $this->assertStringContainsString('application/pdf', (string) $pdf->headers->get('content-type'));
        $this->assertGreaterThan(500, strlen($pdf->getContent()));

        $html = view('admin.crm.pdf.quotation', [
            'quotation' => $quotation->load(['customer', 'items', 'project']),
            'organization' => CrmDocument::organizationFor($this->organizationA->id),
        ])->render();

        $this->assertStringContainsString('محمد علي', $html);
        $this->assertStringContainsString('20,000.00', $html);
        $this->assertStringContainsString('charset=utf-8', $html);
        $this->assertTrue(CrmDocument::containsArabic('شركة النور'));
    }

    public function test_invoice_pdf_shows_paid_and_balance_and_blocks_cross_org(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Invoice Uni عميل',
            'email' => 'unicode-i@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 500,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 500,
            'status' => 'accepted',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 500,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 500,
            'paid_amount' => 200,
            'due_amount' => 300,
            'status' => 'partially_paid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Partial line',
            'quantity' => 1,
            'unit_price' => 500,
            'total' => 500,
        ]);

        InvoicePayment::create([
            'organization_id' => $this->organizationA->id,
            'invoice_id' => $invoice->id,
            'payment_date' => now()->toDateString(),
            'amount' => 200,
            'payment_method' => 'cash',
            'received_by' => $this->adminA->id,
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Invoice Preview')
            ->assertSee('Balance due', false)
            ->assertSee('300.00')
            ->assertSee('200.00')
            ->assertSee($quotation->quotation_number)
            ->assertSee('Invoice Uni', false);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.invoices.pdf', $invoice))
            ->assertOk();

        $html = view('admin.crm.pdf.invoice', [
            'invoice' => $invoice->load(['customer', 'items', 'project', 'quotation', 'payments']),
            'organization' => CrmDocument::organizationFor($this->organizationA->id),
        ])->render();

        $this->assertStringContainsString('Balance due', $html);
        $this->assertStringContainsString('300.00', $html);
        $this->assertStringContainsString($quotation->quotation_number, $html);
        $this->assertStringContainsString('عميل', $html);

        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Doc',
            'email' => 'foreign-doc@example.com',
            'status' => 'active',
        ]);

        $foreignInvoice = Invoice::create([
            'organization_id' => $this->organizationB->id,
            'invoice_number' => 'INV-FOREIGN-DOC',
            'customer_id' => $foreignCustomer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'subtotal' => 10,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 10,
            'paid_amount' => 0,
            'due_amount' => 10,
            'status' => 'sent',
        ]);

        $this->actingAsCrmAdmin($this->adminA)
            ->get(route('admin.crm.invoices.pdf', $foreignInvoice))
            ->assertNotFound();

        $this->actingAsCrmAdmin($this->adminA)
            ->get(route('admin.crm.quotations.pdf', Quotation::create([
                'organization_id' => $this->organizationB->id,
                'quotation_number' => 'QUO-FOREIGN-DOC',
                'customer_id' => $foreignCustomer->id,
                'quotation_date' => now()->toDateString(),
                'subtotal' => 10,
                'tax_percentage' => 0,
                'tax_amount' => 0,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'total' => 10,
                'status' => 'draft',
            ])))
            ->assertNotFound();
    }

    public function test_paid_invoice_document_shows_zero_balance(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Paid Doc Customer',
            'email' => 'paid-doc@example.com',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'subtotal' => 80,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 80,
            'paid_amount' => 80,
            'due_amount' => 0,
            'status' => 'paid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Done',
            'quantity' => 1,
            'unit_price' => 80,
            'total' => 80,
        ]);

        $html = view('admin.crm.documents.invoice-body', [
            'invoice' => $invoice->load(['customer', 'items', 'payments']),
            'documentMode' => 'preview',
            'organization' => CrmDocument::organizationFor($this->organizationA->id),
        ])->render();

        $this->assertStringContainsString('Paid', $html);
        $this->assertStringContainsString('Balance due', $html);
        $this->assertStringContainsString('0.00', $html);
        $this->assertStringNotContainsString('Payment overdue', $html);
    }
}
