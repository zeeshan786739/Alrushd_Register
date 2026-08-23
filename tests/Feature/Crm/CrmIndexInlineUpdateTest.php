<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use Illuminate\Support\Facades\Route;

class CrmIndexInlineUpdateTest extends CrmTestCase
{
    public function test_customer_inline_status_and_owner_update(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Inline Cust',
            'email' => 'inline-cust@example.com',
            'status' => 'prospect',
        ]);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.customers.inline', $customer), [
                'field' => 'status',
                'value' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.customers.inline', $customer), [
                'field' => 'assigned_to',
                'value' => $this->adminA->id,
            ])
            ->assertOk();

        $customer->refresh();
        $this->assertSame('active', $customer->status);
        $this->assertSame($this->adminA->id, $customer->assigned_to);
    }

    public function test_customer_cross_org_owner_blocked(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Owner Guard',
            'email' => 'owner-guard@example.com',
            'status' => 'active',
        ]);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.customers.inline', $customer), [
                'field' => 'assigned_to',
                'value' => $this->adminB->id,
            ])
            ->assertNotFound();

        $this->assertNull($customer->fresh()->assigned_to);
    }

    public function test_customer_index_shows_inline_controls(): void
    {
        Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Index Cust',
            'email' => 'index-cust@example.com',
            'status' => 'active',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.customers.index'))
            ->assertOk()
            ->assertSee('data-crm-inline', false)
            ->assertSee('data-field="status"', false)
            ->assertSee('crm-inline.js', false);
    }

    public function test_project_inline_status_priority_owner_without_progress_change(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Proj Cust',
            'email' => 'proj-cust-inline@example.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Inline Project',
            'project_code' => 'PRJ-INL',
            'status' => 'pending',
            'priority' => 'low',
            'progress' => 37,
        ]);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.projects.inline', $project), [
                'field' => 'status',
                'value' => 'in_progress',
            ])
            ->assertOk()
            ->assertJsonPath('progress', 37);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.projects.inline', $project), [
                'field' => 'priority',
                'value' => 'urgent',
            ])
            ->assertOk();

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.projects.inline', $project), [
                'field' => 'assigned_to',
                'value' => $this->adminA->id,
            ])
            ->assertOk();

        $project->refresh();
        $this->assertSame('in_progress', $project->status);
        $this->assertSame('urgent', $project->priority);
        $this->assertSame($this->adminA->id, $project->assigned_to);
        $this->assertSame(37, (int) $project->progress);
    }

    public function test_project_cross_org_owner_blocked(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Proj Guard',
            'email' => 'proj-guard@example.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Guard Project',
            'project_code' => 'PRJ-GRD',
            'status' => 'pending',
            'priority' => 'medium',
            'progress' => 10,
        ]);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.projects.inline', $project), [
                'field' => 'assigned_to',
                'value' => $this->adminB->id,
            ])
            ->assertNotFound();

        $this->assertNull($project->fresh()->assigned_to);
        $this->assertSame(10, (int) $project->fresh()->progress);
    }

    public function test_quotation_and_invoice_have_no_inline_status_routes(): void
    {
        $this->assertFalse(Route::has('admin.crm.quotations.inline'));
        $this->assertFalse(Route::has('admin.crm.invoices.inline'));
    }

    public function test_quotation_index_keeps_status_badge_not_dropdown(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Q Cust',
            'email' => 'q-inline@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 50,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 50,
            'status' => 'sent',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.index'))
            ->assertOk()
            ->assertSee('crm-status-pill--sent', false)
            ->assertDontSee('data-field="status"', false)
            ->assertSee($quotation->quotation_number);
    }

    public function test_invoice_index_keeps_status_badge_and_payment_status_untouched(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'I Cust',
            'email' => 'i-inline@example.com',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
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
            'paid_amount' => 20,
            'due_amount' => 60,
            'status' => 'partially_paid',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.invoices.index'))
            ->assertOk()
            ->assertSee('crm-status-pill--partially_paid', false)
            ->assertDontSee('data-crm-inline', false);

        $this->assertSame('partially_paid', $invoice->fresh()->status);
        $this->assertEquals(20.0, (float) $invoice->fresh()->paid_amount);
    }

    public function test_converted_quotation_shows_converted_badge(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Conv Cust',
            'email' => 'conv-q@example.com',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
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

        Quotation::create([
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
            'status' => 'accepted',
            'converted_invoice_id' => $invoice->id,
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.index'))
            ->assertOk()
            ->assertSee('crm-status-pill--converted', false);
    }
}
