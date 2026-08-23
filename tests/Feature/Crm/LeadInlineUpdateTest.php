<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LeadInlineUpdateTest extends CrmTestCase
{
    private function makeLead(array $overrides = []): Lead
    {
        return Lead::create(array_merge([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Inline',
            'email' => 'inline-'.uniqid().'@example.test',
            'lead_status' => 'new',
            'priority' => 'medium',
        ], $overrides));
    }

    public function test_inline_status_update_requires_authorization(): void
    {
        $lead = $this->makeLead();
        $role = Role::create(['name' => 'viewer-only', 'guard_name' => 'admin']);
        $role->givePermissionTo(Permission::findByName('view leads', 'admin'));
        $this->adminA->syncRoles([$role]);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.inline', $lead), [
                'field' => 'lead_status',
                'value' => 'contacted',
            ])
            ->assertForbidden();
    }

    public function test_inline_status_and_priority_update(): void
    {
        $lead = $this->makeLead();

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.inline', $lead), [
                'field' => 'lead_status',
                'value' => 'contacted',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.inline', $lead), [
                'field' => 'priority',
                'value' => 'high',
            ])
            ->assertOk();

        $lead->refresh();
        $this->assertSame('contacted', $lead->lead_status);
        $this->assertSame('high', $lead->priority);
        $this->assertSame('manual', $lead->source);
    }

    public function test_inline_assignee_same_org_only(): void
    {
        $lead = $this->makeLead();

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.inline', $lead), [
                'field' => 'assigned_to',
                'value' => $this->adminB->id,
            ])
            ->assertNotFound();

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.inline', $lead), [
                'field' => 'assigned_to',
                'value' => $this->adminA->id,
            ])
            ->assertOk();

        $this->assertSame($this->adminA->id, $lead->fresh()->assigned_to);
    }

    public function test_lead_row_detail_route_still_works(): void
    {
        $lead = $this->makeLead(['first_name' => 'RowClick']);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('RowClick')
            ->assertSee('Quick actions')
            ->assertSee('Recent activity');
    }

    public function test_complete_follow_up_clears_schedule(): void
    {
        $lead = $this->makeLead([
            'next_follow_up_date' => now()->subHour()->toDateString(),
            'next_follow_up_time' => now()->subHour()->format('H:i:s'),
            'next_follow_up_type' => 'call',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.follow-up.complete', $lead))
            ->assertRedirect();

        $lead->refresh();
        $this->assertNull($lead->next_follow_up_date);
        $this->assertTrue($lead->activities()->where('activity_type', 'follow_up_completed')->exists());
    }

    public function test_leads_index_includes_inline_controls(): void
    {
        $this->makeLead();

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index'))
            ->assertOk()
            ->assertSee('data-crm-inline', false)
            ->assertSee('crm-lead-row', false);
    }
}
