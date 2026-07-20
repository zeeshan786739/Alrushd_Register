<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Models\Crm\Invoice;
use App\Models\Form;
use App\Models\FormEntry;

class CrmRouteRenderingTest extends CrmTestCase
{
    public function test_crm_index_routes_render_for_authorized_admin(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Route Customer',
            'email' => 'route@example.com',
            'status' => 'active',
        ]);

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Route',
            'email' => 'route-lead@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'project_code' => Project::generateProjectCode($this->organizationA->id),
            'name' => 'Route Project',
            'customer_id' => $customer->id,
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $quotation = Quotation::create([
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
            'status' => 'draft',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
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

        $form = Form::create([
            'name' => 'Route Form',
            'slug' => 'route-form-'.uniqid(),
            'is_active' => true,
        ]);
        $form->update(['organization_id' => $this->organizationA->id]);

        $entry = FormEntry::create([
            'organization_id' => $this->organizationA->id,
            'form_id' => $form->id,
            'entry_id' => 2001,
            'data' => ['first_name' => 'Entry'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $routes = [
            route('admin.crm.leads.index'),
            route('admin.crm.leads.create'),
            route('admin.crm.leads.show', $lead),
            route('admin.crm.leads.edit', $lead),
            route('admin.crm.leads.email.form', $lead),
            route('admin.crm.customers.index'),
            route('admin.crm.customers.create'),
            route('admin.crm.customers.show', $customer),
            route('admin.crm.customers.edit', $customer),
            route('admin.crm.projects.index'),
            route('admin.crm.projects.create'),
            route('admin.crm.projects.show', $project),
            route('admin.crm.projects.edit', $project),
            route('admin.crm.quotations.index'),
            route('admin.crm.quotations.create'),
            route('admin.crm.quotations.show', $quotation),
            route('admin.crm.quotations.edit', $quotation),
            route('admin.crm.invoices.index'),
            route('admin.crm.invoices.create'),
            route('admin.crm.invoices.show', $invoice),
            route('admin.crm.invoices.edit', $invoice),
            route('admin.crm.form-entries.index'),
            route('admin.crm.form-entries.show', $entry),
            route('admin.crm.form-entries.edit', $entry),
            route('admin.crm.form-entries.form', $form),
        ];

        foreach ($routes as $url) {
            $this->actingAsCrmAdmin()->get($url)->assertOk();
        }
    }
}
