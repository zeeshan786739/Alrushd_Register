<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\Project;
use App\Models\Crm\ProjectTask;
use App\Models\Crm\Quotation;
use App\Support\ProjectDueState;

class ProjectWorkspaceTest extends CrmTestCase
{
    public function test_project_detail_is_tenant_isolated(): void
    {
        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign',
            'email' => 'foreign-project@example.com',
            'status' => 'active',
        ]);

        $foreignProject = Project::create([
            'organization_id' => $this->organizationB->id,
            'customer_id' => $foreignCustomer->id,
            'name' => 'Foreign Project',
            'project_code' => 'PRJ-FOR',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin($this->adminA)
            ->get(route('admin.crm.projects.show', $foreignProject))
            ->assertNotFound();
    }

    public function test_cross_org_task_assignee_is_blocked_and_same_org_works(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Task Cust',
            'email' => 'task-cust@example.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Task Project',
            'project_code' => 'PRJ-TASK2',
            'status' => 'in_progress',
            'priority' => 'medium',
            'progress' => 0,
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.projects.tasks.store', $project), [
                'name' => 'Bad assignee',
                'assigned_to' => $this->adminB->id,
                'status' => 'pending',
                'priority' => 'medium',
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.projects.tasks.store', $project), [
                'name' => 'Good task',
                'assigned_to' => $this->adminA->id,
                'status' => 'pending',
                'priority' => 'medium',
            ])
            ->assertRedirect();

        $task = $project->tasks()->firstOrFail();
        $this->assertSame($this->adminA->id, $task->assigned_to);
        $this->assertSame(0, (int) $project->fresh()->progress);

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.projects.tasks.update', [$project, $task->id]), [
                'name' => 'Good task',
                'assigned_to' => $this->adminA->id,
                'status' => 'completed',
                'priority' => 'medium',
            ])
            ->assertRedirect();

        $this->assertSame(100, (int) $project->fresh()->progress);

        $this->actingAsCrmAdmin()
            ->delete(route('admin.crm.projects.tasks.destroy', [$project, $task->id]))
            ->assertRedirect();

        $this->assertSame(0, $project->tasks()->count());
    }

    public function test_project_show_surfaces_same_org_quotes_and_invoices_only(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Show Cust',
            'email' => 'show-cust@example.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Workspace Project',
            'project_code' => 'PRJ-WS',
            'status' => 'in_progress',
            'priority' => 'high',
            'end_date' => now()->subDay()->toDateString(),
            'progress' => 0,
        ]);

        $quote = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 50,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 50,
            'status' => 'sent',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 40,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 40,
            'paid_amount' => 10,
            'due_amount' => 30,
            'status' => 'partially_paid',
        ]);

        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'B Cust',
            'email' => 'b-cust@example.com',
            'status' => 'active',
        ]);

        $foreignProject = Project::create([
            'organization_id' => $this->organizationB->id,
            'customer_id' => $foreignCustomer->id,
            'name' => 'Other Project',
            'project_code' => 'PRJ-B',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        Invoice::create([
            'organization_id' => $this->organizationB->id,
            'invoice_number' => 'INV-B-HIDDEN',
            'customer_id' => $foreignCustomer->id,
            'project_id' => $foreignProject->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'subtotal' => 900,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 900,
            'paid_amount' => 0,
            'due_amount' => 900,
            'status' => 'sent',
        ]);

        $due = ProjectDueState::forProject($project);
        $this->assertSame(ProjectDueState::OVERDUE, $due->state);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.projects.show', $project))
            ->assertOk()
            ->assertSee('Workspace Project')
            ->assertSee($customer->name)
            ->assertSee($quote->quotation_number)
            ->assertSee($invoice->invoice_number)
            ->assertDontSee('INV-B-HIDDEN')
            ->assertSee('Create Quotation')
            ->assertSee('customer_id='.$customer->id)
            ->assertSee('project_id='.$project->id);
    }

    public function test_project_create_still_works_with_same_org_customer(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Create Cust',
            'email' => 'create-cust@example.com',
            'status' => 'active',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.projects.store'), [
                'customer_id' => $customer->id,
                'name' => 'Created Project',
                'status' => 'pending',
                'priority' => 'medium',
                'assigned_to' => $this->adminA->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('crm_projects', [
            'name' => 'Created Project',
            'customer_id' => $customer->id,
            'organization_id' => $this->organizationA->id,
        ]);
    }
}
