<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;
use App\Models\Crm\SavedFilter;

class LeadWorkflowTest extends CrmTestCase
{
    public function test_admin_can_save_lead_filter(): void
    {
        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.filters.save'), [
                'name' => 'New High Priority',
                'filters' => [
                    'lead_status' => 'new',
                    'priority' => 'high',
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('crm_saved_filters', [
            'admin_id' => $this->adminA->id,
            'module' => 'leads',
            'name' => 'New High Priority',
        ]);
    }

    public function test_admin_can_schedule_follow_up_and_appointment(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Workflow',
            'email' => 'workflow@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->patch(route('admin.crm.leads.follow-up', $lead), [
                'next_follow_up_date' => now()->addDay()->toDateString(),
                'next_follow_up_time' => '10:00',
                'next_follow_up_type' => 'phone',
            ])
            ->assertRedirect();

        $this->actingAsCrmAdmin()
            ->patch(route('admin.crm.leads.appointment', $lead), [
                'appointment_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'appointment_type' => 'online_meeting',
                'appointment_notes' => 'Discuss packages',
            ])
            ->assertRedirect();

        $lead->refresh();
        $this->assertNotNull($lead->next_follow_up_date);
        $this->assertNotNull($lead->appointment_date);
        $this->assertSame('online_meeting', $lead->appointment_type);
    }

    public function test_lead_index_shows_saved_filters_and_controls(): void
    {
        SavedFilter::create([
            'organization_id' => $this->organizationA->id,
            'admin_id' => $this->adminA->id,
            'module' => 'leads',
            'name' => 'My Filter',
            'filters' => ['lead_status' => 'new'],
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index'))
            ->assertOk()
            ->assertSee('Save Filter')
            ->assertSee('My Filter')
            ->assertSee('Add Lead');
    }
}
