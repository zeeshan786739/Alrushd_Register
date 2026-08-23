<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\InvoicePayment;

class InvoiceDeleteIntegrityTest extends CrmTestCase
{
    public function test_invoice_with_payment_cannot_be_deleted_and_history_stays_intact(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Paid History Customer',
            'email' => 'paid-history@example.com',
            'status' => 'active',
            'lifetime_value' => 40,
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 100,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'paid_amount' => 40,
            'due_amount' => 60,
            'status' => 'partially_paid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Service',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        $payment = InvoicePayment::create([
            'organization_id' => $this->organizationA->id,
            'invoice_id' => $invoice->id,
            'payment_date' => now()->toDateString(),
            'amount' => 40,
            'payment_method' => 'cash',
            'received_by' => $this->adminA->id,
        ]);

        $this->actingAsCrmAdmin()
            ->delete(route('admin.crm.invoices.destroy', $invoice))
            ->assertRedirect()
            ->assertSessionHas('error', 'This invoice has recorded payments and cannot be deleted.');

        $this->assertDatabaseHas('crm_invoices', [
            'id' => $invoice->id,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('crm_invoice_payments', [
            'id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount' => 40,
        ]);
        $this->assertEquals(40.0, (float) $customer->fresh()->lifetime_value);
        $this->assertEquals(40.0, (float) $invoice->fresh()->paid_amount);
    }

    public function test_invoice_without_payments_can_still_be_deleted(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Unpaid Delete Customer',
            'email' => 'unpaid-delete@example.com',
            'status' => 'active',
            'lifetime_value' => 0,
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 75,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 75,
            'paid_amount' => 0,
            'due_amount' => 75,
            'status' => 'draft',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Draft line',
            'quantity' => 1,
            'unit_price' => 75,
            'total' => 75,
        ]);

        $this->actingAsCrmAdmin()
            ->delete(route('admin.crm.invoices.destroy', $invoice))
            ->assertRedirect(route('admin.crm.invoices.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('crm_invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('crm_invoice_items', ['invoice_id' => $invoice->id]);
        $this->assertEquals(0.0, (float) $customer->fresh()->lifetime_value);
    }

    public function test_cross_org_invoice_delete_remains_blocked(): void
    {
        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Delete',
            'email' => 'foreign-delete@example.com',
            'status' => 'active',
        ]);

        $foreignInvoice = Invoice::create([
            'organization_id' => $this->organizationB->id,
            'invoice_number' => 'INV-FOREIGN-DEL',
            'customer_id' => $foreignCustomer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 10,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 10,
            'paid_amount' => 0,
            'due_amount' => 10,
            'status' => 'draft',
        ]);

        $this->actingAsCrmAdmin($this->adminA)
            ->delete(route('admin.crm.invoices.destroy', $foreignInvoice))
            ->assertNotFound();

        $this->assertDatabaseHas('crm_invoices', [
            'id' => $foreignInvoice->id,
            'deleted_at' => null,
        ]);
    }
}
