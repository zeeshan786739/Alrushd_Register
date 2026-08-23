<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoicePayment;
use App\Models\Crm\Lead;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;

class CrmLifecycleWorkflowTest extends CrmTestCase
{
    public function test_full_crm_lifecycle_preserves_org_and_links(): void
    {
        $this->actingAsCrmAdmin();

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Flow',
            'last_name' => 'Customer',
            'email' => 'flow-e2e@example.com',
            'lead_status' => 'qualified',
            'priority' => 'high',
        ]);

        $this->post(route('admin.crm.leads.convert', $lead))
            ->assertRedirect();

        $lead->refresh();
        $this->assertTrue((bool) $lead->is_converted);
        $this->assertNotNull($lead->customer_id);

        $customer = Customer::forOrganization($this->organizationA->id)->findOrFail($lead->customer_id);
        $this->assertSame($this->organizationA->id, $customer->organization_id);
        $this->assertSame($lead->id, $customer->lead_id);

        $this->post(route('admin.crm.projects.store'), [
            'customer_id' => $customer->id,
            'name' => 'Flow Project',
            'status' => 'in_progress',
            'priority' => 'medium',
            'end_date' => now()->addDays(14)->toDateString(),
        ])->assertRedirect();

        $project = Project::forOrganization($this->organizationA->id)->where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame($this->organizationA->id, $project->organization_id);

        $this->get(route('admin.crm.quotations.create', [
            'customer_id' => $customer->id,
            'project_id' => $project->id,
        ]))
            ->assertOk()
            ->assertSee('selected', false)
            ->assertSee((string) $customer->id)
            ->assertSee((string) $project->id);

        $this->post(route('admin.crm.quotations.store'), [
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(10)->toDateString(),
            'status' => 'sent',
            'tax_percentage' => 0,
            'discount_percentage' => 0,
            'items' => [
                ['description' => 'Lifecycle service', 'quantity' => 1, 'unit_price' => 250],
            ],
        ])->assertRedirect();

        $quotation = Quotation::forOrganization($this->organizationA->id)
            ->where('customer_id', $customer->id)
            ->where('project_id', $project->id)
            ->firstOrFail();
        $this->assertSame($this->organizationA->id, $quotation->organization_id);
        $this->assertEquals(250.0, (float) $quotation->total);

        $this->post(route('admin.crm.quotations.accept', $quotation))->assertRedirect();
        $quotation->refresh();
        $this->assertSame('accepted', $quotation->status);

        $this->post(route('admin.crm.quotations.convert', $quotation))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertNotNull($quotation->converted_invoice_id);

        $invoice = Invoice::forOrganization($this->organizationA->id)->findOrFail($quotation->converted_invoice_id);
        $this->assertSame($this->organizationA->id, $invoice->organization_id);
        $this->assertSame($customer->id, $invoice->customer_id);
        $this->assertSame($project->id, $invoice->project_id);
        $this->assertSame($quotation->id, $invoice->quotation_id);
        $this->assertEquals(250.0, (float) $invoice->total);

        $this->post(route('admin.crm.invoices.payments.store', $invoice), [
            'payment_date' => now()->toDateString(),
            'amount' => 100,
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'FLOW-100',
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame('partially_paid', $invoice->status);
        $this->assertEquals(100.0, (float) $invoice->paid_amount);
        $this->assertEquals(150.0, (float) $invoice->due_amount);
        $this->assertEquals(100.0, (float) $customer->fresh()->lifetime_value);

        $this->assertSame(1, InvoicePayment::query()->where('invoice_id', $invoice->id)->count());
        $this->assertSame(0, Invoice::forOrganization($this->organizationB->id)->count());
    }

    public function test_overview_totals_and_attention_are_current_org_only(): void
    {
        $localLead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Local',
            'email' => 'local-overview@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
            'next_follow_up_date' => now()->subDay()->toDateString(),
        ]);

        $foreignLead = Lead::create([
            'organization_id' => $this->organizationB->id,
            'source' => 'manual',
            'first_name' => 'ForeignAttention',
            'email' => 'foreign-overview@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
            'next_follow_up_date' => now()->subDay()->toDateString(),
        ]);

        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Overview Cust',
            'email' => 'foreign-ov-cust@example.com',
            'status' => 'active',
        ]);

        Invoice::create([
            'organization_id' => $this->organizationB->id,
            'invoice_number' => 'INV-FOREIGN-OV',
            'customer_id' => $foreignCustomer->id,
            'invoice_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'subtotal' => 999,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 999,
            'paid_amount' => 0,
            'due_amount' => 999,
            'status' => 'sent',
        ]);

        $response = $this->actingAsCrmAdmin()
            ->get(route('admin.crm.overview'))
            ->assertOk();

        $response->assertSee('Needs Attention');
        $response->assertSee($localLead->first_name);
        $response->assertDontSee('ForeignAttention');
        $response->assertDontSee('INV-FOREIGN-OV');
    }

    public function test_create_forms_preselect_same_org_context(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Preselect Cust',
            'email' => 'preselect@example.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Preselect Project',
            'project_code' => 'PRJ-PRE',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Pre',
            'email' => 'foreign-pre@example.com',
            'status' => 'active',
        ]);

        $foreignProject = Project::create([
            'organization_id' => $this->organizationB->id,
            'customer_id' => $foreignCustomer->id,
            'name' => 'Should Not Appear',
            'project_code' => 'PRJ-FPRE',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.projects.create', ['customer_id' => $customer->id]))
            ->assertOk()
            ->assertSee('selected', false)
            ->assertSee((string) $customer->id);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.create', [
                'customer_id' => $customer->id,
                'project_id' => $project->id,
            ]))
            ->assertOk()
            ->assertSee($project->name)
            ->assertDontSee($foreignProject->name);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.invoices.create', [
                'customer_id' => $customer->id,
                'project_id' => $project->id,
            ]))
            ->assertOk()
            ->assertSee($project->name)
            ->assertDontSee($foreignProject->name);
    }
}
