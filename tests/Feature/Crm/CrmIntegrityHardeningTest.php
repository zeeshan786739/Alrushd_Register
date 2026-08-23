<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\Lead;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Services\Crm\LeadConversionService;
use App\Services\Crm\QuotationConversionService;

class CrmIntegrityHardeningTest extends CrmTestCase
{
    public function test_cross_org_assigned_to_is_blocked_on_customer_create(): void
    {
        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.customers.store'), [
                'name' => 'Tenant Safe',
                'email' => 'tenant-safe@example.com',
                'status' => 'active',
                'assigned_to' => $this->adminB->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertSame(0, Customer::forOrganization($this->organizationA->id)->count());
    }

    public function test_cross_org_customer_id_is_blocked_on_project_create(): void
    {
        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign',
            'email' => 'foreign@example.com',
            'status' => 'active',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.projects.store'), [
                'customer_id' => $foreignCustomer->id,
                'name' => 'Should Fail',
                'status' => 'pending',
                'priority' => 'medium',
            ])
            ->assertSessionHasErrors('customer_id');

        $this->assertSame(0, Project::forOrganization($this->organizationA->id)->count());
    }

    public function test_cross_org_project_id_is_blocked_on_quotation_create(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Local Customer',
            'email' => 'local-q@example.com',
            'status' => 'active',
        ]);

        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Customer',
            'email' => 'foreign-q@example.com',
            'status' => 'active',
        ]);

        $foreignProject = Project::create([
            'organization_id' => $this->organizationB->id,
            'customer_id' => $foreignCustomer->id,
            'name' => 'Foreign Project',
            'project_code' => 'PRJ-FOREIGN',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.store'), [
                'customer_id' => $customer->id,
                'project_id' => $foreignProject->id,
                'quotation_date' => now()->toDateString(),
                'status' => 'draft',
                'items' => [
                    ['description' => 'Item', 'quantity' => 1, 'unit_price' => 10],
                ],
            ])
            ->assertSessionHasErrors('project_id');

        $this->assertSame(0, Quotation::forOrganization($this->organizationA->id)->count());
    }

    public function test_cross_org_quotation_id_is_blocked_on_invoice_create(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Local Invoice Customer',
            'email' => 'local-i@example.com',
            'status' => 'active',
        ]);

        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Invoice Customer',
            'email' => 'foreign-i@example.com',
            'status' => 'active',
        ]);

        $foreignQuotation = Quotation::create([
            'organization_id' => $this->organizationB->id,
            'quotation_number' => 'QT-FOREIGN',
            'customer_id' => $foreignCustomer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 10,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 10,
            'status' => 'accepted',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.invoices.store'), [
                'customer_id' => $customer->id,
                'quotation_id' => $foreignQuotation->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'draft',
                'items' => [
                    ['description' => 'Item', 'quantity' => 1, 'unit_price' => 10],
                ],
            ])
            ->assertSessionHasErrors('quotation_id');

        $this->assertSame(0, Invoice::forOrganization($this->organizationA->id)->count());
    }

    public function test_cross_org_task_assignee_is_blocked(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Task Customer',
            'email' => 'task@example.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Task Project',
            'project_code' => 'PRJ-TASK',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.projects.tasks.store', $project), [
                'name' => 'Do work',
                'assigned_to' => $this->adminB->id,
                'status' => 'pending',
                'priority' => 'medium',
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertSame(0, $project->tasks()->count());
    }

    public function test_lead_double_conversion_remains_idempotent(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Once',
            'email' => 'once-harden@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin();
        $service = app(LeadConversionService::class);

        $first = $service->convertToCustomer($lead);
        $second = $service->convertToCustomer($lead->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Customer::forOrganization($this->organizationA->id)->count());
        $this->assertTrue($lead->fresh()->is_converted);
    }

    public function test_customer_delete_blocked_when_invoices_exist(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Protected Customer',
            'email' => 'protected@example.com',
            'status' => 'active',
        ]);

        Invoice::create([
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
            'status' => 'draft',
        ]);

        $this->actingAsCrmAdmin()
            ->delete(route('admin.crm.customers.destroy', $customer))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('crm_customers', ['id' => $customer->id]);
    }

    public function test_accepted_quotation_cannot_be_edited(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Quote Customer',
            'email' => 'quote@example.com',
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
            'description' => 'Locked item',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.quotations.update', $quotation), [
                'customer_id' => $customer->id,
                'quotation_date' => now()->toDateString(),
                'status' => 'draft',
                'items' => [
                    ['description' => 'Changed', 'quantity' => 1, 'unit_price' => 1],
                ],
            ])
            ->assertRedirect(route('admin.crm.quotations.show', $quotation))
            ->assertSessionHas('error');

        $quotation->refresh();
        $this->assertSame('accepted', $quotation->status);
        $this->assertEquals(100.0, (float) $quotation->total);
    }

    public function test_manual_invoice_create_cannot_duplicate_converted_quotation(): void
    {
        $this->actingAsCrmAdmin();

        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Dup Customer',
            'email' => 'dup@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 80,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 80,
            'status' => 'accepted',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Service',
            'quantity' => 1,
            'unit_price' => 80,
            'total' => 80,
        ]);

        $invoice = app(QuotationConversionService::class)->convertToInvoice($quotation);
        $this->assertNotNull($invoice->id);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.invoices.store'), [
                'customer_id' => $customer->id,
                'quotation_id' => $quotation->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'draft',
                'items' => [
                    ['description' => 'Dup', 'quantity' => 1, 'unit_price' => 80],
                ],
            ])
            ->assertSessionHasErrors('quotation_id');

        $this->assertSame(1, Invoice::forOrganization($this->organizationA->id)->count());
    }

    public function test_payment_and_invoice_remain_org_isolated(): void
    {
        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'B Customer',
            'email' => 'bpay@example.com',
            'status' => 'active',
        ]);

        $foreignInvoice = Invoice::create([
            'organization_id' => $this->organizationB->id,
            'invoice_number' => 'INV-B-1',
            'customer_id' => $foreignCustomer->id,
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

        $this->actingAsCrmAdmin($this->adminA)
            ->post(route('admin.crm.invoices.payments.store', $foreignInvoice), [
                'payment_date' => now()->toDateString(),
                'amount' => 5,
                'payment_method' => 'cash',
            ])
            ->assertNotFound();
    }

    public function test_same_org_customer_and_project_crud_still_works(): void
    {
        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.customers.store'), [
                'name' => 'Happy Path',
                'email' => 'happy@example.com',
                'status' => 'active',
                'assigned_to' => $this->adminA->id,
            ])
            ->assertRedirect();

        $customer = Customer::forOrganization($this->organizationA->id)->firstOrFail();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.projects.store'), [
                'customer_id' => $customer->id,
                'name' => 'Happy Project',
                'status' => 'pending',
                'priority' => 'medium',
                'assigned_to' => $this->adminA->id,
            ])
            ->assertRedirect();

        $this->assertSame(1, Project::forOrganization($this->organizationA->id)->count());
    }
}
