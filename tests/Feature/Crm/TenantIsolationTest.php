<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;
use App\Services\Crm\FinancialCalculator;
use App\Services\Crm\QuotationConversionService;
use App\Models\Crm\Customer;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Models\Crm\Invoice;

class TenantIsolationTest extends CrmTestCase
{
    public function test_admin_cannot_access_other_organization_lead(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationB->id,
            'source' => 'manual',
            'first_name' => 'Hidden',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin($this->adminA)
            ->get(route('admin.crm.leads.show', $lead))
            ->assertNotFound();
    }

    public function test_financial_calculator_ignores_client_totals(): void
    {
        $calculator = app(FinancialCalculator::class);
        $result = $calculator->calculate([
            ['quantity' => 2, 'unit_price' => 50],
            ['quantity' => 1, 'unit_price' => 100],
        ], 10, 5);

        $this->assertSame(200.0, $result['subtotal']);
        $this->assertSame(20.0, $result['tax_amount']);
        $this->assertSame(10.0, $result['discount_amount']);
        $this->assertSame(210.0, $result['total']);
    }

    public function test_accepted_quotation_converts_to_invoice_once(): void
    {
        $this->actingAsCrmAdmin();

        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Customer',
            'email' => 'cust@example.com',
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
            'status' => 'accepted',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        $service = app(QuotationConversionService::class);
        $invoice1 = $service->convertToInvoice($quotation);
        $invoice2 = $service->convertToInvoice($quotation->fresh());

        $this->assertSame($invoice1->id, $invoice2->id);
        $this->assertSame(1, Invoice::forOrganization($this->organizationA->id)->count());
    }
}
