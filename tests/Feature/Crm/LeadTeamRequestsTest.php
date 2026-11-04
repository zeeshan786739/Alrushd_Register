<?php

namespace Tests\Feature\Crm;

use App\Enums\LeadStatus;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadStatusDefinition;
use App\Models\Crm\SavedFilter;
use Illuminate\Support\Facades\DB;

class LeadTeamRequestsTest extends CrmTestCase
{
    private function lead(array $attributes = []): Lead
    {
        return Lead::create(array_merge([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Test',
            'lead_status' => 'new',
        ], $attributes));
    }

    public function test_lead_is_created_with_year_group_and_contact_method_and_no_priority(): void
    {
        $this->actingAsCrmAdmin()
            ->postJson(route('admin.crm.leads.store'), [
                'first_name' => 'Shafiq',
                'lead_status' => 'new',
                'year_group' => 'Year 7',
                'contact_method' => 'WhatsApp',
                'lead_source' => 'Enquire now',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('crm_leads', [
            'first_name' => 'Shafiq',
            'year_group' => 'Year 7',
            'contact_method' => 'WhatsApp',
            'lead_source' => 'Enquire now',
            'priority' => 'medium',
        ]);
    }

    public function test_year_group_and_contact_method_must_come_from_the_lists(): void
    {
        $this->actingAsCrmAdmin()
            ->postJson(route('admin.crm.leads.store'), [
                'first_name' => 'Shafiq',
                'lead_status' => 'new',
                'year_group' => 'Year 99',
                'contact_method' => 'Carrier pigeon',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['year_group', 'contact_method']);
    }

    public function test_saving_the_edit_panel_returns_json_instead_of_crashing(): void
    {
        $lead = $this->lead();

        $this->actingAsCrmAdmin()
            ->putJson(route('admin.crm.leads.update', $lead), [
                'first_name' => 'Habib',
                'lead_status' => 'pending',
                'year_group' => 'Year 3',
            ])
            ->assertOk()
            ->assertJsonPath('lead.full_name', 'Habib');

        $this->assertSame('Year 3', $lead->fresh()->year_group);
    }

    public function test_a_spreadsheet_value_outside_the_list_survives_an_edit(): void
    {
        $lead = $this->lead(['contact_method' => 'Facebook message', 'year_group' => 'Yr 5']);

        $this->actingAsCrmAdmin()
            ->putJson(route('admin.crm.leads.update', $lead), [
                'first_name' => 'Test',
                'lead_status' => 'new',
                'contact_method' => 'Facebook message',
                'year_group' => 'Yr 5',
            ])
            ->assertOk();

        $this->assertSame('Facebook message', $lead->fresh()->contact_method);
    }

    public function test_editing_a_lead_does_not_erase_its_stored_company(): void
    {
        $lead = $this->lead(['company' => 'Old Co']);

        $this->actingAsCrmAdmin()
            ->putJson(route('admin.crm.leads.update', $lead), ['first_name' => 'Test', 'lead_status' => 'new'])
            ->assertOk();

        $this->assertSame('Old Co', $lead->fresh()->company);
    }

    public function test_create_and_edit_panels_show_the_requested_fields(): void
    {
        $lead = $this->lead();
        $this->actingAsCrmAdmin();

        foreach ([route('admin.crm.leads.create.panel'), route('admin.crm.leads.panel.edit', $lead)] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Year group')
                ->assertSee('Contact method')
                ->assertSee('Lead source')
                ->assertDontSee('Company')
                ->assertDontSee('Priority')
                ->assertDontSee('name="priority"', false)
                ->assertSee('Enrolled')
                ->assertDontSee('Negotiation');
        }
    }

    public function test_leads_page_offers_only_the_five_statuses_and_no_priority(): void
    {
        $this->lead(['first_name' => 'Listed']);
        $response = $this->actingAsCrmAdmin()->get(route('admin.crm.leads.index', ['view' => 'list']));

        $response->assertOk()->assertSee('Listed');
        foreach (['New', 'Pending', 'Enrolled', 'Not Interested', 'Spam'] as $label) {
            $response->assertSee($label);
        }
        foreach (['Negotiation', 'Proposal Sent', 'On Hold', 'Qualified', 'High priority', 'Any priority'] as $label) {
            $response->assertDontSee($label);
        }
    }

    public function test_board_view_renders_a_column_for_each_status(): void
    {
        $this->lead(['first_name' => 'Boarded', 'lead_status' => 'enrolled']);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['view' => 'board']))
            ->assertOk()
            ->assertSee('Boarded')
            ->assertSee('data-status="not_interested"', false);
    }

    public function test_priority_can_no_longer_be_changed_inline_or_in_bulk(): void
    {
        $lead = $this->lead();
        $this->actingAsCrmAdmin();

        $this->patchJson(route('admin.crm.leads.inline', $lead), ['field' => 'priority', 'value' => 'high'])->assertForbidden();
        $this->patchJson(route('admin.crm.leads.bulk'), ['lead_ids' => [$lead->id], 'field' => 'priority', 'value' => 'high'])->assertForbidden();
    }

    public function test_leads_filter_by_year_group_and_contact_method(): void
    {
        $this->lead(['first_name' => 'YearSeven', 'year_group' => 'Year 7', 'contact_method' => 'Email']);
        $this->lead(['first_name' => 'YearNine', 'year_group' => 'Year 9', 'contact_method' => 'Phone call']);
        $this->actingAsCrmAdmin();

        $this->get(route('admin.crm.leads.index', ['view' => 'list', 'year_group' => 'Year 7']))
            ->assertSee('YearSeven')->assertDontSee('YearNine');
        $this->get(route('admin.crm.leads.index', ['view' => 'list', 'contact_method' => 'Phone call']))
            ->assertSee('YearNine')->assertDontSee('YearSeven');
    }

    public function test_search_finds_a_full_name_and_a_phone_number_typed_without_spaces(): void
    {
        $this->lead(['first_name' => 'Mohammad', 'last_name' => 'Rasool', 'phone' => '07878 117694']);
        $this->lead(['first_name' => 'Someone', 'last_name' => 'Else', 'phone' => '0111 222333']);
        $this->actingAsCrmAdmin();

        $this->get(route('admin.crm.leads.index', ['view' => 'list', 'search' => 'Mohammad Rasool']))
            ->assertSee('Rasool')->assertDontSee('Someone');
        $this->get(route('admin.crm.leads.index', ['view' => 'list', 'search' => '07878117694']))
            ->assertSee('Rasool')->assertDontSee('Someone');
    }

    public function test_old_bookmark_for_a_retired_status_shows_its_replacement(): void
    {
        $this->lead(['first_name' => 'WasWon', 'lead_status' => 'enrolled']);
        $this->lead(['first_name' => 'StillNew', 'lead_status' => 'new']);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['view' => 'list', 'lead_status' => 'won']))
            ->assertOk()->assertSee('WasWon')->assertDontSee('StillNew');
    }

    public function test_unknown_sort_column_is_ignored_instead_of_failing(): void
    {
        $this->lead();

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['view' => 'list', 'sort_by' => 'not_a_column', 'sort_order' => 'sideways']))
            ->assertOk();
    }

    public function test_malformed_lead_filters_do_not_cause_a_server_error(): void
    {
        $this->lead();

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['view' => 'list', 'search' => ['x'], 'year_group' => ['y'], 'lead_status' => ['z'], 'source' => ['s']]))
            ->assertOk();
    }

    public function test_the_status_list_cannot_grow_again(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.crm.leads.statuses.store'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.crm.leads.statuses.destroy'));

        $this->lead();
        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['view' => 'list']))
            ->assertOk()
            ->assertDontSee('data-crm-add-status', false)
            ->assertDontSee('data-crm-delete-status', false);
    }

    public function test_overdue_follow_ups_exclude_closed_statuses(): void
    {
        $this->actingAsCrmAdmin();
        $overdue = $this->lead(['lead_status' => 'pending', 'next_follow_up_date' => today()->subDay()]);
        foreach (LeadStatus::closedValues() as $status) {
            $this->lead(['lead_status' => $status, 'next_follow_up_date' => today()->subDay()]);
        }

        $this->assertSame([$overdue->id], Lead::query()->overdueFollowUp()->pluck('id')->all());
    }

    public function test_new_lead_goes_on_top_without_one_update_per_existing_lead(): void
    {
        $first = $this->lead();
        $second = $this->lead();
        $third = $this->lead();

        $this->assertSame([1, 2, 3], [$third->fresh()->list_position, $second->fresh()->list_position, $first->fresh()->list_position]);

        DB::enableQueryLog();
        $this->lead();
        $shifts = collect(DB::getQueryLog())->filter(fn ($query) => str_starts_with($query['query'], 'update') && str_contains($query['query'], 'list_position'));
        DB::disableQueryLog();

        $this->assertCount(1, $shifts);
        $this->assertSame(4, $first->fresh()->list_position);
    }

    public function test_year_group_comes_through_when_a_form_submission_becomes_a_lead(): void
    {
        $form = \App\Models\Form::create(['organization_id' => $this->organizationA->id, 'name' => 'Enquire', 'slug' => 'enquire-test', 'is_active' => true]);
        $entry = \App\Models\FormEntry::create([
            'organization_id' => $this->organizationA->id,
            'form_id' => $form->id,
            'data' => ['full_name' => 'Amina Khan', 'year_group' => 'Year 5'],
            'status' => 'pending',
        ]);

        $lead = app(\App\Services\Crm\FormEntryLeadConverter::class)->convertIfMissing($entry);

        $this->assertSame('Year 5', $lead->year_group);
    }

    public function test_status_migration_merges_retired_statuses_and_keeps_the_originals(): void
    {
        $orgId = $this->organizationA->id;
        $map = [
            'contacted' => 'pending', 'qualified' => 'enrolled', 'proposal_sent' => 'pending',
            'negotiation' => 'pending', 'on_hold' => 'pending', 'won' => 'enrolled', 'lost' => 'not_interested',
        ];
        $leads = [];
        foreach ($map as $old => $new) {
            $leads[$old] = $this->lead(['lead_status' => $old]);
        }
        $trashed = $this->lead(['lead_status' => 'won']);
        $trashed->delete();
        $untouched = $this->lead(['lead_status' => 'new']);

        $customEnrolled = LeadStatusDefinition::create(['organization_id' => $orgId, 'name' => 'Enrolled', 'slug' => 'enrolled', 'tone' => '#16A34A', 'is_active' => true]);
        $customHyphen = LeadStatusDefinition::create(['organization_id' => $orgId, 'name' => 'Not interested', 'slug' => 'not-interested', 'tone' => '#DC2626', 'is_active' => true]);
        $customOther = LeadStatusDefinition::create(['organization_id' => $orgId, 'name' => 'Waiting on docs', 'slug' => 'waiting-on-docs', 'tone' => '#0080FF', 'is_active' => true]);
        $hyphenLead = $this->lead(['lead_status' => 'not-interested']);
        $otherLead = $this->lead(['lead_status' => 'waiting-on-docs']);
        $orphan = $this->lead(['lead_status' => 'status-deleted-long-ago']);

        $filter = SavedFilter::create(['organization_id' => $orgId, 'admin_id' => $this->adminA->id, 'module' => 'leads', 'name' => 'Won high', 'filters' => ['lead_status' => 'won', 'priority' => 'high']]);

        $metadata = $this->organizationA->metadata ?? [];
        $metadata['crm_pipeline_status_order'] = ['won', 'new', 'contacted', 'qualified'];
        $this->organizationA->update(['metadata' => $metadata]);

        (require database_path('migrations/2026_11_04_100100_consolidate_crm_lead_statuses.php'))->consolidate();

        foreach ($map as $old => $new) {
            $this->assertSame($new, $leads[$old]->fresh()->lead_status, $old);
            $this->assertSame($old, $leads[$old]->fresh()->legacy_lead_status, $old);
        }
        $this->assertSame('enrolled', Lead::withTrashed()->find($trashed->id)->lead_status);
        $this->assertSame('new', $untouched->fresh()->lead_status);
        $this->assertNull($untouched->fresh()->legacy_lead_status);

        $this->assertFalse((bool) $customEnrolled->fresh()->is_active);
        $this->assertFalse((bool) $customHyphen->fresh()->is_active);
        $this->assertSame('not_interested', $hyphenLead->fresh()->lead_status);
        $this->assertSame('not-interested', $hyphenLead->fresh()->legacy_lead_status);
        $this->assertFalse((bool) $customOther->fresh()->is_active);
        $this->assertSame('pending', $otherLead->fresh()->lead_status);
        $this->assertSame('waiting-on-docs', $otherLead->fresh()->legacy_lead_status);
        $this->assertSame('pending', $orphan->fresh()->lead_status);

        $this->assertSame(['lead_status' => 'enrolled'], $filter->fresh()->filters);
        $this->assertSame(['enrolled', 'new', 'pending'], $this->organizationA->fresh()->metadata['crm_pipeline_status_order']);
    }
}
