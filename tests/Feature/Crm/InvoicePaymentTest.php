<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;

class InvoicePaymentTest extends CrmTestCase
{
    public function test_partial_payment_updates_invoice_status(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Paying Customer',
            'email' => 'pay@example.com',
            'status' => 'active',
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
            'paid_amount' => 0,
            'due_amount' => 100,
            'status' => 'sent',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Service',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.invoices.payments.store', $invoice), [
                'payment_date' => now()->toDateString(),
                'amount' => 40,
                'payment_method' => 'bank_transfer',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('partially_paid', $invoice->status);
        $this->assertEquals(40.0, (float) $invoice->paid_amount);
        $this->assertEquals(60.0, (float) $invoice->due_amount);
    }

    public function test_payment_cannot_exceed_outstanding_balance(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Overpay Customer',
            'email' => 'over@example.com',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
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

        $this->actingAsCrmAdmin()
            ->from(route('admin.crm.invoices.show', $invoice))
            ->post(route('admin.crm.invoices.payments.store', $invoice), [
                'payment_date' => now()->toDateString(),
                'amount' => 75,
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('admin.crm.invoices.show', $invoice))
            ->assertSessionHasErrors('amount');

        $invoice->refresh();
        $this->assertEquals(0.0, (float) $invoice->paid_amount);
    }
}
