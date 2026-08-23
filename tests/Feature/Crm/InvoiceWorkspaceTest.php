<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Support\InvoiceDueState;

class InvoiceWorkspaceTest extends CrmTestCase
{
    public function test_invoice_detail_is_tenant_isolated(): void
    {
        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Inv',
            'email' => 'foreign-inv@example.com',
            'status' => 'active',
        ]);

        $foreign = Invoice::create([
            'organization_id' => $this->organizationB->id,
            'invoice_number' => 'INV-FOREIGN',
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
            'status' => 'sent',
        ]);

        $this->actingAsCrmAdmin($this->adminA)
            ->get(route('admin.crm.invoices.show', $foreign))
            ->assertNotFound();

        $this->actingAsCrmAdmin($this->adminA)
            ->post(route('admin.crm.invoices.payments.store', $foreign), [
                'payment_date' => now()->toDateString(),
                'amount' => 5,
                'payment_method' => 'cash',
            ])
            ->assertNotFound();
    }

    public function test_payment_flow_and_overpayment_guard(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Pay Cust',
            'email' => 'pay-ws@example.com',
            'status' => 'active',
            'lifetime_value' => 0,
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
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
                'amount' => 0,
                'payment_method' => 'cash',
            ])
            ->assertSessionHasErrors('amount');

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.invoices.payments.store', $invoice), [
                'payment_date' => now()->toDateString(),
                'amount' => -5,
                'payment_method' => 'cash',
            ])
            ->assertSessionHasErrors('amount');

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.invoices.payments.store', $invoice), [
                'payment_date' => now()->toDateString(),
                'amount' => 40,
                'payment_method' => 'bank_transfer',
                'transaction_id' => 'TX-40',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('partially_paid', $invoice->status);
        $this->assertEquals(40.0, (float) $invoice->paid_amount);
        $this->assertEquals(60.0, (float) $invoice->due_amount);
        $this->assertEquals(40.0, (float) $customer->fresh()->lifetime_value);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.invoices.payments.store', $invoice), [
                'payment_date' => now()->toDateString(),
                'amount' => 61,
                'payment_method' => 'cash',
            ])
            ->assertSessionHasErrors('amount');

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.invoices.payments.store', $invoice), [
                'payment_date' => now()->toDateString(),
                'amount' => 60,
                'payment_method' => 'cash',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertEquals(0.0, (float) $invoice->due_amount);
        $this->assertEquals(100.0, (float) $customer->fresh()->lifetime_value);
    }

    public function test_due_state_and_paid_not_overdue(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Due Cust',
            'email' => 'due-ws@example.com',
            'status' => 'active',
        ]);

        $overdue = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
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

        $this->assertSame(InvoiceDueState::OVERDUE, InvoiceDueState::forInvoice($overdue)->state);

        $paid = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'subtotal' => 50,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 50,
            'paid_amount' => 50,
            'due_amount' => 0,
            'status' => 'paid',
        ]);

        $state = InvoiceDueState::forInvoice($paid);
        $this->assertSame(InvoiceDueState::PAID, $state->state);
        $this->assertNotSame(InvoiceDueState::OVERDUE, $state->state);
    }

    public function test_paid_invoice_edit_locked_and_total_cannot_go_below_paid(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Lock Cust',
            'email' => 'lock-ws@example.com',
            'status' => 'active',
        ]);

        $paid = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
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
            'invoice_id' => $paid->id,
            'description' => 'Done',
            'quantity' => 1,
            'unit_price' => 80,
            'total' => 80,
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.invoices.edit', $paid))
            ->assertRedirect(route('admin.crm.invoices.show', $paid));

        $partial = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'subtotal' => 100,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'paid_amount' => 60,
            'due_amount' => 40,
            'status' => 'partially_paid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $partial->id,
            'description' => 'Partial',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.invoices.update', $partial), [
                'customer_id' => $customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(5)->toDateString(),
                'status' => 'partially_paid',
                'items' => [
                    ['description' => 'Too small', 'quantity' => 1, 'unit_price' => 20],
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertEquals(100.0, (float) $partial->fresh()->total);
    }

    public function test_quotation_linked_invoice_is_shown(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Link Cust',
            'email' => 'link-ws@example.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Link Project',
            'project_code' => 'PRJ-LINK',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 70,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 70,
            'status' => 'accepted',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'quotation_id' => $quotation->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 70,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 70,
            'paid_amount' => 0,
            'due_amount' => 70,
            'status' => 'draft',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Created from Quotation')
            ->assertSee($quotation->quotation_number)
            ->assertSee($customer->name)
            ->assertSee($project->name);
    }
}
