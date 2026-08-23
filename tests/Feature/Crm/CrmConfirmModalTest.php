<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use Illuminate\Support\Facades\Mail;

class CrmConfirmModalTest extends CrmTestCase
{
    public function test_mark_as_sent_uses_crm_confirm_modal_not_native_confirm(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Confirm Modal Cust',
            'email' => 'confirm-modal@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 40,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 40,
            'status' => 'draft',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 40,
            'total' => 40,
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('id="crmConfirmModal"', false)
            ->assertSee('data-crm-confirm', false)
            ->assertSee('Mark quotation as sent?', false)
            ->assertSee('No email will be sent from the system.', false)
            ->assertDontSee("return confirm(", false)
            ->assertDontSee('onsubmit="return confirm', false);

        Mail::fake();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.mark-sent', $quotation))
            ->assertRedirect();

        Mail::assertNothingSent();
        $this->assertSame('sent', $quotation->fresh()->status);
    }

    public function test_convert_to_customer_uses_crm_confirm_modal_not_native_confirm(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Modal',
            'last_name' => 'Convert',
            'email' => 'modal-convert-'.uniqid().'@example.com',
            'lead_status' => 'qualified',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('id="crmConfirmModal"', false)
            ->assertSee('data-crm-confirm', false)
            ->assertSee('Convert lead to customer?', false)
            ->assertSee('Convert to Customer')
            ->assertDontSee("return confirm('Convert this lead to a customer?')", false);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.convert', $lead))
            ->assertRedirect();

        $lead->refresh();
        $this->assertTrue((bool) $lead->is_converted);
        $this->assertNotNull($lead->customer_id);
    }
}
