<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;

class QuotationLineItemsUiTest extends CrmTestCase
{
    public function test_create_quotation_accepts_three_line_items(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Line Items Customer',
            'email' => 'line-items@example.com',
            'status' => 'active',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.store'), [
                'customer_id' => $customer->id,
                'quotation_date' => now()->toDateString(),
                'status' => 'draft',
                'tax_percentage' => 0,
                'discount_percentage' => 0,
                'items' => [
                    ['description' => 'Item One', 'quantity' => 1, 'unit_price' => 100],
                    ['description' => 'Item Two', 'quantity' => 2, 'unit_price' => 50],
                    ['description' => 'Item Three', 'quantity' => 3, 'unit_price' => 10],
                ],
            ])
            ->assertRedirect();

        $quotation = Quotation::forOrganization($this->organizationA->id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $this->assertSame(3, $quotation->items()->count());
        $this->assertEquals(230.0, (float) $quotation->total);
        $this->assertEquals(230.0, (float) $quotation->subtotal);
    }

    public function test_edit_quotation_can_add_and_remove_line_items(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Edit Line Items',
            'email' => 'edit-lines@example.com',
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
            'description' => 'Keep',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Remove Me',
            'quantity' => 1,
            'unit_price' => 20,
            'total' => 20,
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.edit', $quotation))
            ->assertOk()
            ->assertSee('data-crm-add-line-item', false)
            ->assertSee('name="items[0][description]"', false)
            ->assertSee('Keep');

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.quotations.update', $quotation), [
                'customer_id' => $customer->id,
                'quotation_date' => now()->toDateString(),
                'status' => 'draft',
                'tax_percentage' => 0,
                'discount_percentage' => 0,
                'items' => [
                    ['description' => 'Keep', 'quantity' => 1, 'unit_price' => 100],
                    ['description' => 'Added', 'quantity' => 2, 'unit_price' => 25],
                ],
            ])
            ->assertRedirect();

        $quotation->refresh();
        $this->assertSame(2, $quotation->items()->count());
        $this->assertEquals(150.0, (float) $quotation->total);
        $this->assertTrue($quotation->items()->where('description', 'Added')->exists());
        $this->assertFalse($quotation->items()->where('description', 'Remove Me')->exists());
    }

    public function test_create_quotation_page_includes_pjax_safe_line_item_controls(): void
    {
        Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'UI Cust',
            'email' => 'ui-cust@example.com',
            'status' => 'active',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.create'))
            ->assertOk()
            ->assertSee('id="add-line-item"', false)
            ->assertSee('type="button"', false)
            ->assertSee('data-crm-add-line-item', false)
            ->assertSee('line-item-row', false)
            ->assertDontSee("document.addEventListener('DOMContentLoaded'");
    }
}
