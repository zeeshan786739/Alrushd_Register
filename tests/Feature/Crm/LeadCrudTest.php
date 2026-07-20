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
}
