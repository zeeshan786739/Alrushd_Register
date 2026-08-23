<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\DocumentSetting;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Support\CrmDocument;

class CrmDocumentSettingsTest extends CrmTestCase
{
    public function test_unconfigured_branding_does_not_auto_show_organization_name(): void
    {
        $this->organizationA->update(['name' => 'Default Organization']);

        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'محمد علي',
            'email' => 'doc-set@example.com',
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
            'status' => 'sent',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Line',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        $html = view('admin.crm.documents.quotation-body', [
            'quotation' => $quotation->load(['customer', 'items', 'project']),
            'documentMode' => 'preview',
            'doc' => CrmDocument::quotationViewData($quotation, 'preview'),
        ])->render();

        $this->assertStringNotContainsString('Default Organization', $html);
        $this->assertStringContainsString('محمد علي', $html);
        $this->assertStringContainsString('Grand total', $html);
    }

    public function test_visibility_toggles_and_tenant_isolation(): void
    {
        DocumentSetting::create([
            'organization_id' => $this->organizationA->id,
            'branding' => array_merge(CrmDocument::defaultBranding(), [
                'display_name' => 'Org A Visible',
                'address' => 'Street A',
                'email' => 'a@example.com',
                'show_display_name' => true,
                'show_address' => false,
                'show_email' => true,
            ]),
            'quotation' => CrmDocument::defaultQuotation(),
            'invoice' => array_merge(CrmDocument::defaultInvoice(), [
                'bank_name' => 'Secret Bank',
                'show_bank_name' => false,
                'account_number' => 'IBAN-123',
                'show_account_number' => true,
            ]),
        ]);

        DocumentSetting::create([
            'organization_id' => $this->organizationB->id,
            'branding' => array_merge(CrmDocument::defaultBranding(), [
                'display_name' => 'Org B Leak',
                'show_display_name' => true,
            ]),
            'quotation' => CrmDocument::defaultQuotation(),
            'invoice' => CrmDocument::defaultInvoice(),
        ]);

        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Cust A',
            'email' => 'custa@example.com',
            'phone' => '111',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 50,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 50,
            'paid_amount' => 0,
            'due_amount' => 50,
            'status' => 'sent',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 50,
            'total' => 50,
        ]);

        $doc = CrmDocument::invoiceViewData($invoice, 'preview');
        $html = view('admin.crm.documents.invoice-body', [
            'invoice' => $invoice->load(['customer', 'items', 'payments', 'project', 'quotation']),
            'documentMode' => 'preview',
            'doc' => $doc,
        ])->render();

        $this->assertStringContainsString('Org A Visible', $html);
        $this->assertStringContainsString('a@example.com', $html);
        $this->assertStringNotContainsString('Street A', $html);
        $this->assertStringNotContainsString('Org B Leak', $html);
        $this->assertStringNotContainsString('Secret Bank', $html);
        $this->assertStringContainsString('IBAN-123', $html);

        $settingsB = CrmDocument::settings($this->organizationB->id);
        $this->assertSame('Org B Leak', $settingsB['branding']['display_name']);
        $this->assertNotSame('Org A Visible', $settingsB['branding']['display_name']);
    }

    public function test_settings_update_is_org_scoped_and_empty_enabled_fields_omit(): void
    {
        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.settings.documents.update'), [
                'active_tab' => 'branding',
                'branding' => [
                    'display_name' => 'Configured Name',
                    'address' => '',
                    'email' => '',
                    'phone' => '',
                    'website' => '',
                    'registration_number' => '',
                    'vat_number' => '',
                    'show_display_name' => '1',
                    'show_address' => '1',
                    'show_email' => '1',
                ],
            ])
            ->assertRedirect();

        $row = DocumentSetting::query()->where('organization_id', $this->organizationA->id)->firstOrFail();
        $this->assertSame('Configured Name', $row->branding['display_name']);
        $this->assertTrue($row->branding['show_address']);

        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Preview Cust',
            'email' => 'prev@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 10,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 10,
            'status' => 'draft',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'X',
            'quantity' => 1,
            'unit_price' => 10,
            'total' => 10,
        ]);

        $html = view('admin.crm.documents.quotation-body', [
            'quotation' => $quotation->load(['customer', 'items', 'project']),
            'documentMode' => 'preview',
            'doc' => CrmDocument::quotationViewData($quotation, 'preview'),
        ])->render();

        $this->assertStringContainsString('Configured Name', $html);
        $this->assertStringNotContainsString('Reg:', $html);

        $this->actingAsCrmAdmin($this->adminB)
            ->get(route('admin.crm.settings.documents.edit'))
            ->assertOk()
            ->assertDontSee('Configured Name');
    }

    public function test_arabic_pdf_pipeline_shapes_without_mutating_source(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'محمد علي',
            'email' => 'ar@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 20,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 20,
            'status' => 'sent',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'خدمة',
            'quantity' => 1,
            'unit_price' => 20,
            'total' => 20,
        ]);

        $html = view('admin.crm.pdf.quotation', [
            'quotation' => $quotation->load(['customer', 'items', 'project']),
            'organization' => null,
            'doc' => CrmDocument::quotationViewData($quotation, 'pdf'),
        ])->render();

        $this->assertStringContainsString('محمد علي', $html);

        $shaped = CrmDocument::prepareHtmlForPdf($html);
        $this->assertNotSame($html, $shaped);
        $this->assertSame('محمد علي', $customer->fresh()->name);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.pdf', $quotation))
            ->assertOk();
    }
}
