<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\DocumentSetting;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Support\CrmDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CrmDocumentLogoTest extends CrmTestCase
{
    public function test_logo_upload_persists_and_survives_settings_save_without_new_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('brand-logo.png', 120, 60);

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.settings.documents.update'), [
                'active_tab' => 'branding',
                'logo' => $file,
                'branding' => [
                    'display_name' => 'Logo Org',
                    'show_logo' => '1',
                    'show_display_name' => '1',
                ],
            ])
            ->assertRedirect();

        $row = DocumentSetting::query()->where('organization_id', $this->organizationA->id)->firstOrFail();
        $this->assertNotNull($row->logo_path);
        $this->assertTrue($row->branding['show_logo']);
        Storage::disk('public')->assertExists($row->logo_path);
        $originalPath = $row->logo_path;

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.settings.documents.update'), [
                'active_tab' => 'branding',
                'branding' => [
                    'display_name' => 'Logo Org',
                    'show_logo' => '1',
                    'show_display_name' => '1',
                ],
            ])
            ->assertRedirect();

        $this->assertSame($originalPath, $row->fresh()->logo_path);
        Storage::disk('public')->assertExists($originalPath);
    }

    public function test_remove_logo_clears_path_and_show_logo_false_hides_src(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('keep.png', 80, 40)
            ->store('crm-documents/'.$this->organizationA->id, 'public');

        DocumentSetting::create([
            'organization_id' => $this->organizationA->id,
            'logo_path' => $path,
            'branding' => array_merge(CrmDocument::defaultBranding(), [
                'show_logo' => true,
                'display_name' => 'A',
            ]),
            'quotation' => CrmDocument::defaultQuotation(),
            'invoice' => CrmDocument::defaultInvoice(),
        ]);

        $this->assertNotNull(CrmDocument::logoSrcForDocument($path, $this->organizationA->id));

        // Foreign org path must not resolve under current org id.
        $this->assertNull(CrmDocument::logoSrcForDocument($path, $this->organizationB->id));

        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Logo Cust',
            'email' => 'logo-cust@example.com',
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

        $withLogo = CrmDocument::quotationViewData($quotation, 'preview');
        $this->assertNotNull($withLogo['logo_src']);
        $this->assertStringStartsWith('data:image/', $withLogo['logo_src']);

        DocumentSetting::query()->where('organization_id', $this->organizationA->id)->update([
            'branding' => array_merge(CrmDocument::defaultBranding(), [
                'show_logo' => false,
                'display_name' => 'A',
            ]),
        ]);

        $hidden = CrmDocument::quotationViewData($quotation->fresh(), 'preview');
        $this->assertNull($hidden['logo_src']);

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.settings.documents.update'), [
                'active_tab' => 'branding',
                'remove_logo' => '1',
                'branding' => [
                    'display_name' => 'A',
                    'show_logo' => '0',
                ],
            ])
            ->assertRedirect();

        $row = DocumentSetting::query()->where('organization_id', $this->organizationA->id)->firstOrFail();
        $this->assertNull($row->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_quotation_and_invoice_preview_and_pdf_receive_logo_data_uri(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('doc.png', 100, 40)
            ->store('crm-documents/'.$this->organizationA->id, 'public');

        DocumentSetting::create([
            'organization_id' => $this->organizationA->id,
            'logo_path' => $path,
            'branding' => array_merge(CrmDocument::defaultBranding(), [
                'show_logo' => true,
            ]),
            'quotation' => CrmDocument::defaultQuotation(),
            'invoice' => CrmDocument::defaultInvoice(),
        ]);

        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Doc Logo Cust',
            'email' => 'doc-logo@example.com',
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
            'description' => 'Service',
            'quantity' => 1,
            'unit_price' => 20,
            'total' => 20,
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 20,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 20,
            'paid_amount' => 0,
            'due_amount' => 20,
            'status' => 'sent',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Service',
            'quantity' => 1,
            'unit_price' => 20,
            'total' => 20,
        ]);

        $qPreview = CrmDocument::quotationViewData($quotation, 'preview');
        $qPdf = CrmDocument::quotationViewData($quotation, 'pdf');
        $iPreview = CrmDocument::invoiceViewData($invoice, 'preview');
        $iPdf = CrmDocument::invoiceViewData($invoice, 'pdf');

        foreach ([$qPreview, $qPdf, $iPreview, $iPdf] as $doc) {
            $this->assertNotNull($doc['logo_src']);
            $this->assertStringStartsWith('data:image/png;base64,', $doc['logo_src']);
        }

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('data:image/png;base64,', false);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('data:image/png;base64,', false);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.settings.documents.edit', ['tab' => 'branding']))
            ->assertOk()
            ->assertSee('Current logo')
            ->assertSee('data:image/png;base64,', false);
    }

    public function test_cross_org_logo_path_is_not_resolved_for_other_tenant(): void
    {
        Storage::fake('public');

        $pathB = UploadedFile::fake()->image('b.png', 50, 50)
            ->store('crm-documents/'.$this->organizationB->id, 'public');

        DocumentSetting::create([
            'organization_id' => $this->organizationB->id,
            'logo_path' => $pathB,
            'branding' => array_merge(CrmDocument::defaultBranding(), ['show_logo' => true]),
            'quotation' => CrmDocument::defaultQuotation(),
            'invoice' => CrmDocument::defaultInvoice(),
        ]);

        $settingsA = CrmDocument::settings($this->organizationA->id);
        $this->assertNull($settingsA['logo_path']);

        // Org A documents must not pick up Org B settings.
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'A',
            'email' => 'a-logo-iso@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 5,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 5,
            'status' => 'draft',
        ]);

        $doc = CrmDocument::quotationViewData($quotation, 'preview');
        $this->assertNull($doc['logo_src']);

        // Even if Org A somehow referenced Org B's storage path, tenant guard blocks it.
        $this->assertNull(CrmDocument::logoSrcForDocument($pathB, $this->organizationA->id));
    }
}
