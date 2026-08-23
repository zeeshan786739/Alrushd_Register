<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\Lead;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Services\Crm\LeadConversionService;

class CustomerProfileTest extends CrmTestCase
{
    public function test_customer_detail_is_tenant_isolated(): void
    {
        $foreign = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Customer',
            'email' => 'foreign-profile@example.com',
            'status' => 'active',
        ]);

        $this->actingAsCrmAdmin($this->adminA)
            ->get(route('admin.crm.customers.show', $foreign))
            ->assertNotFound();
    }

    public function test_customer_show_surfaces_original_lead_and_same_org_relations(): void
    {
        $this->actingAsCrmAdmin();

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Origin',
            'last_name' => 'Lead',
            'email' => 'origin-lead@example.com',
            'lead_status' => 'qualified',
            'priority' => 'medium',
        ]);

        $customer = app(LeadConversionService::class)->convertToCustomer($lead);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Same Org Project',
            'project_code' => 'PRJ-SAME',
            'status' => 'in_progress',
            'priority' => 'medium',
            'progress' => 40,
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 120,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 120,
            'status' => 'accepted',
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
            'paid_amount' => 25,
            'due_amount' => 75,
            'status' => 'partially_paid',
        ]);

        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Other Org Customer',
            'email' => 'other-org@example.com',
            'status' => 'active',
        ]);

        Project::create([
            'organization_id' => $this->organizationB->id,
            'customer_id' => $foreignCustomer->id,
            'name' => 'Foreign Project',
            'project_code' => 'PRJ-FOREIGN',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        Invoice::create([
            'organization_id' => $this->organizationB->id,
            'invoice_number' => 'INV-FOREIGN',
            'customer_id' => $foreignCustomer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'subtotal' => 999,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 999,
            'paid_amount' => 999,
            'due_amount' => 0,
            'status' => 'paid',
        ]);

        $response = $this->actingAsCrmAdmin()
            ->get(route('admin.crm.customers.show', $customer))
            ->assertOk()
            ->assertSee('Origin Lead')
            ->assertSee('Same Org Project')
            ->assertSee($quotation->quotation_number)
            ->assertSee($invoice->invoice_number)
            ->assertDontSee('Foreign Project')
            ->assertDontSee('INV-FOREIGN');

        $commercial = $response->viewData('commercial');
        $this->assertSame(1, $commercial['projects_total']);
        $this->assertSame(1, $commercial['projects_active']);
        $this->assertEquals(120.0, $commercial['quotations_accepted_value']);
        $this->assertEquals(100.0, $commercial['invoiced_amount']);
        $this->assertEquals(25.0, $commercial['paid_amount']);
        $this->assertEquals(75.0, $commercial['outstanding_amount']);
        $this->assertLessThan(999, $commercial['invoiced_amount']);
    }

    public function test_customer_create_and_edit_still_work(): void
    {
        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.customers.store'), [
                'name' => 'Profile Customer',
                'email' => 'profile-customer@example.com',
                'status' => 'active',
                'assigned_to' => $this->adminA->id,
            ])
            ->assertRedirect();

        $customer = Customer::forOrganization($this->organizationA->id)->firstOrFail();

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.customers.update', $customer), [
                'name' => 'Profile Customer Updated',
                'email' => 'profile-customer@example.com',
                'status' => 'prospect',
                'assigned_to' => $this->adminA->id,
            ])
            ->assertRedirect(route('admin.crm.customers.show', $customer));

        $this->assertSame('Profile Customer Updated', $customer->fresh()->name);
        $this->assertSame('prospect', $customer->fresh()->status);
    }

    public function test_customer_delete_guard_still_works(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Guarded',
            'email' => 'guarded-profile@example.com',
            'status' => 'active',
        ]);

        Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Linked',
            'project_code' => 'PRJ-GUARD',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->delete(route('admin.crm.customers.destroy', $customer))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('crm_customers', ['id' => $customer->id]);
    }
}
