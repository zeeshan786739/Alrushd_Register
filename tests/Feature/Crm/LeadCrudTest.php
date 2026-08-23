<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;

class LeadCrudTest extends CrmTestCase
{
    public function test_leads_index_requires_authentication(): void
    {
        $this->get(route('admin.crm.leads.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_and_view_lead(): void
    {
        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane@example.com',
                'lead_status' => 'new',
                'priority' => 'medium',
            ])
            ->assertRedirect();

        $lead = Lead::forOrganization($this->organizationA->id)->first();
        $this->assertNotNull($lead);
        $this->assertSame('Jane', $lead->first_name);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('Jane');
    }

    public function test_lead_assignment_scopes_assignee_to_organization(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Test',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->patch(route('admin.crm.leads.assign', $lead), [
                'assigned_to' => $this->adminB->id,
            ])
            ->assertNotFound();

        $this->actingAsCrmAdmin()
            ->patch(route('admin.crm.leads.assign', $lead), [
                'assigned_to' => $this->adminA->id,
            ])
            ->assertRedirect();

        $lead->refresh();
        $this->assertSame($this->adminA->id, $lead->assigned_to);
    }

    public function test_cross_org_assigned_to_rejected_on_lead_create(): void
    {
        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.store'), [
                'first_name' => 'Cross',
                'email' => 'cross-create@example.com',
                'lead_status' => 'new',
                'priority' => 'medium',
                'assigned_to' => $this->adminB->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertSame(0, Lead::forOrganization($this->organizationA->id)->count());
    }

    public function test_cross_org_assigned_to_rejected_on_lead_update(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Update',
            'email' => 'cross-update@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
            'assigned_to' => $this->adminA->id,
        ]);

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.leads.update', $lead), [
                'first_name' => 'Update',
                'email' => 'cross-update@example.com',
                'lead_status' => 'new',
                'priority' => 'medium',
                'assigned_to' => $this->adminB->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $lead->refresh();
        $this->assertSame($this->adminA->id, $lead->assigned_to);
    }

    public function test_same_org_assignee_works_on_lead_create(): void
    {
        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.store'), [
                'first_name' => 'Assigned',
                'email' => 'same-org-assignee@example.com',
                'lead_status' => 'new',
                'priority' => 'medium',
                'assigned_to' => $this->adminA->id,
            ])
            ->assertRedirect();

        $lead = Lead::forOrganization($this->organizationA->id)->firstOrFail();
        $this->assertSame($this->adminA->id, $lead->assigned_to);
    }
}
