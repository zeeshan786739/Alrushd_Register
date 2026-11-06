<?php

namespace Tests\Feature\Crm;

use App\Models\Admin;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadCategory;
use App\Support\LeadFormOptions;
use App\Support\LeadSettings;
use App\Support\LeadStatusCatalog;

class LeadSettingsTest extends CrmTestCase
{
    private function lead(string $name, array $attributes = []): Lead
    {
        return Lead::create(array_merge([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => $name,
            'lead_status' => 'new',
        ], $attributes));
    }

    private function leads(array $query = [])
    {
        return $this->actingAsCrmAdmin()->get(route('admin.crm.leads.index', ['view' => 'list', ...$query]));
    }

    private function addCustomFilter(string $label = 'Campus', array $options = ['North', 'South']): void
    {
        $this->actingAsCrmAdmin()->postJson(route('admin.crm.settings.leads.custom-filters.store'), compact('label', 'options'))->assertOk();
    }

    private function list(string $list): string
    {
        return route('admin.crm.settings.leads.options.store', $list);
    }

    public function test_page_requires_the_permission(): void
    {
        $staff = Admin::create([
            'name' => 'Staff', 'email' => 'staff@test.local', 'password' => bcrypt('password'),
            'organization_id' => $this->organizationA->id,
        ]);

        $this->actingAsCrmAdmin($staff)->get(route('admin.crm.settings.leads.edit'))->assertForbidden();
        $this->actingAsCrmAdmin($staff)->postJson($this->list('contact_method'), ['name' => 'Fax'])->assertForbidden();
        $this->actingAsCrmAdmin()->get(route('admin.crm.settings.leads.edit'))->assertOk()->assertSee('Lead Settings');
    }

    public function test_standard_setup_is_unchanged_until_customized(): void
    {
        $this->actingAsCrmAdmin();
        $this->assertSame(['Sept 26/27', 'Sept 27/28', 'Sept 28/29'], LeadFormOptions::admissionIntakes());
        $this->assertContains('WhatsApp', LeadFormOptions::contactMethods());

        $this->leads()->assertOk()->assertSee('Sept 27/28 admissions')->assertSee('More filters')->assertSee('Lead source');
    }

    public function test_filters_can_be_moved_renamed_and_hidden(): void
    {
        $filters = [
            ['key' => 'contact_method', 'placement' => 'main', 'label' => 'How to reach'],
            ['key' => 'lead_status', 'placement' => 'main', 'label' => null],
            ['key' => 'year_group', 'placement' => 'hidden', 'label' => null],
        ];
        $this->actingAsCrmAdmin()->putJson(route('admin.crm.settings.leads.layout'), compact('filters'))->assertOk();

        $response = $this->leads()->assertOk()->assertSee('How to reach');
        $this->assertStringNotContainsString('id="year_group"', $response->getContent());
        $this->assertLessThan(
            strpos($response->getContent(), 'id="lead_status"'),
            strpos($response->getContent(), 'id="contact_method"')
        );
    }

    public function test_a_hidden_filter_still_in_the_url_is_kept_and_shown_as_a_chip(): void
    {
        $this->actingAsCrmAdmin()->putJson(route('admin.crm.settings.leads.layout'), [
            'filters' => [['key' => 'year_group', 'placement' => 'hidden', 'label' => null]],
        ])->assertOk();

        $this->lead('MatchedLead', ['year_group' => 'Year 7']);
        $this->lead('OtherLead', ['year_group' => 'Year 2']);

        $this->leads(['year_group' => 'Year 7'])
            ->assertSee('MatchedLead')
            ->assertDontSee('OtherLead')
            ->assertSee('name="year_group" value="Year 7"', false)
            ->assertSee('crm-active-filter__value', false);
    }

    public function test_quick_buttons_can_be_added_and_removed_and_must_point_at_a_real_option(): void
    {
        $this->actingAsCrmAdmin()->putJson(route('admin.crm.settings.leads.layout'), ['quick_filters' => [
            ['id' => null, 'label' => 'Next year', 'param' => 'admission_intake', 'value' => 'Sept 28/29'],
        ]])->assertOk();

        $this->leads()->assertSee('Next year')->assertDontSee('My leads');

        $this->actingAsCrmAdmin()->putJson(route('admin.crm.settings.leads.layout'), ['quick_filters' => [
            ['id' => null, 'label' => 'Bad', 'param' => 'admission_intake', 'value' => 'Never'],
        ]])->assertStatus(422);
        $this->actingAsCrmAdmin()->putJson(route('admin.crm.settings.leads.layout'), ['quick_filters' => [
            ['id' => null, 'label' => 'Bad', 'param' => 'organization_id', 'value' => '1'],
        ]])->assertStatus(422);

        $this->actingAsCrmAdmin()->postJson(route('admin.crm.settings.leads.restore'), ['section' => 'quick'])->assertOk();
        $this->leads()->assertSee('My leads');
    }

    public function test_date_picker_and_source_cards_can_be_turned_off(): void
    {
        $this->actingAsCrmAdmin()->putJson(route('admin.crm.settings.leads.layout'), ['show_sources' => false, 'show_date_picker' => false])->assertOk();

        $this->leads()->assertDontSee('Choose where leads came from')->assertDontSee('Any date');
    }

    public function test_renaming_an_option_updates_leads_buttons_bookmarks_and_imports(): void
    {
        $lead = $this->lead('RenamedLead', ['admission_intake' => 'Sept 27/28']);

        $this->actingAsCrmAdmin()->patchJson($this->list('admission_intake'), ['value' => 'Sept 27/28', 'name' => 'September 2027'])
            ->assertOk()
            ->assertJsonPath('message', 'Renamed, and updated 1 lead');

        $this->assertSame('September 2027', $lead->fresh()->admission_intake);
        $this->assertContains('September 2027', LeadFormOptions::admissionIntakes());
        $this->assertNotContains('Sept 27/28', LeadFormOptions::admissionIntakes());
        $this->assertSame('September 2027', LeadFormOptions::normalizeAdmissionIntake('Sept 2027'));

        $this->leads(['admission_intake' => 'Sept 27/28'])->assertSee('RenamedLead');
        $this->leads()->assertSee('September 2027 admissions');
    }

    public function test_renaming_back_and_forth_leaves_no_loop(): void
    {
        $this->actingAsCrmAdmin()->patchJson($this->list('contact_method'), ['value' => 'SMS', 'name' => 'Text'])->assertOk();
        $this->actingAsCrmAdmin()->patchJson($this->list('contact_method'), ['value' => 'Text', 'name' => 'SMS'])->assertOk();

        $this->assertSame('SMS', LeadFormOptions::normalizeContactMethod('text message'));
    }

    public function test_duplicates_and_blank_names_are_refused(): void
    {
        $this->actingAsCrmAdmin()->postJson($this->list('contact_method'), ['name' => 'whatsapp'])->assertStatus(422);
        $this->actingAsCrmAdmin()->postJson($this->list('contact_method'), ['name' => '   '])->assertStatus(422);
        $this->actingAsCrmAdmin()->patchJson($this->list('contact_method'), ['value' => 'SMS', 'name' => 'Email'])->assertStatus(422);
        $this->actingAsCrmAdmin()->postJson($this->list('admission_intake'), ['name' => str_repeat('x', 31)])->assertStatus(422);
        $this->actingAsCrmAdmin()->postJson($this->list('nonsense'), ['name' => 'x'])->assertNotFound();
    }

    public function test_removing_an_option_can_keep_clear_or_move_its_leads(): void
    {
        $keep = $this->lead('Keep', ['contact_method' => 'SMS']);
        $clear = $this->lead('Clear', ['contact_method' => 'Email']);
        $move = $this->lead('Move', ['contact_method' => 'Other']);

        $this->actingAsCrmAdmin();
        $this->deleteJson($this->list('contact_method'), ['value' => 'SMS', 'move_to' => null])->assertOk();
        $this->deleteJson($this->list('contact_method'), ['value' => 'Email', 'move_to' => '__clear__'])->assertOk();
        $this->deleteJson($this->list('contact_method'), ['value' => 'Other', 'move_to' => 'WhatsApp'])->assertOk();

        $this->assertSame('SMS', $keep->fresh()->contact_method);
        $this->assertNull($clear->fresh()->contact_method);
        $this->assertSame('WhatsApp', $move->fresh()->contact_method);
        $this->assertNotContains('SMS', LeadFormOptions::contactMethods());

        $this->deleteJson($this->list('contact_method'), ['value' => 'Phone call', 'move_to' => 'Nope'])->assertStatus(422);
    }

    public function test_a_lead_can_keep_a_removed_option_when_saved_but_new_leads_cannot_choose_it(): void
    {
        $lead = $this->lead('Older', ['contact_method' => 'SMS']);
        $this->actingAsCrmAdmin()->deleteJson($this->list('contact_method'), ['value' => 'SMS'])->assertOk();

        $this->actingAsCrmAdmin()->putJson(route('admin.crm.leads.update', $lead), [
            'first_name' => 'Older', 'lead_status' => 'new', 'contact_method' => 'SMS',
        ])->assertOk();

        $this->actingAsCrmAdmin()->postJson(route('admin.crm.leads.store'), [
            'first_name' => 'Newer', 'lead_status' => 'new', 'contact_method' => 'SMS',
        ])->assertStatus(422);
    }

    public function test_removing_an_intake_drops_its_quick_button(): void
    {
        $this->actingAsCrmAdmin()->deleteJson($this->list('admission_intake'), ['value' => 'Sept 27/28'])->assertOk();

        $this->leads()->assertDontSee('Sept 27/28 admissions')->assertSee('My leads');
    }

    public function test_values_found_only_on_leads_are_listed_and_can_be_added(): void
    {
        $this->lead('Sheet', ['contact_method' => 'Carrier pigeon']);

        $response = $this->actingAsCrmAdmin()->postJson($this->list('contact_method'), ['name' => 'Fax'])->assertOk();
        $options = collect($response->json('state.lists'))->firstWhere('key', 'contact_method')['options'];
        $this->assertTrue(collect($options)->firstWhere('value', 'Carrier pigeon')['unlisted']);

        $this->actingAsCrmAdmin()->postJson($this->list('contact_method'), ['name' => 'Carrier pigeon'])->assertOk();
        $this->assertContains('Carrier pigeon', LeadFormOptions::contactMethods());
    }

    public function test_options_can_be_reordered(): void
    {
        $this->actingAsCrmAdmin()->putJson(route('admin.crm.settings.leads.options.order', 'admission_intake'), [
            'values' => ['Sept 28/29', 'Sept 26/27', 'Sept 27/28'],
        ])->assertOk();

        $this->assertSame(['Sept 28/29', 'Sept 26/27', 'Sept 27/28'], LeadFormOptions::admissionIntakes());
    }

    public function test_statuses_including_custom_ones_can_be_reordered(): void
    {
        $this->actingAsCrmAdmin();
        $this->postJson($this->list('lead_status'), ['name' => 'Waiting list'])->assertOk();

        $response = $this->putJson(route('admin.crm.settings.leads.options.order', 'lead_status'), [
            'values' => ['new', 'pending', 'waiting_list', 'enrolled', 'not_interested', 'spam'],
        ])->assertOk();

        $this->assertSame(['new', 'pending', 'waiting_list', 'enrolled', 'not_interested', 'spam'], array_keys(LeadStatusCatalog::options()));
        $this->assertSame('waiting_list', collect($response->json('state.lists'))->firstWhere('key', 'lead_status')['options'][2]['value']);
    }

    public function test_built_in_status_can_be_renamed_but_not_removed(): void
    {
        $this->actingAsCrmAdmin();
        $this->patchJson($this->list('lead_status'), ['value' => 'pending', 'name' => 'In progress'])->assertOk();
        $this->assertSame('In progress', LeadStatusCatalog::label('pending'));
        $this->assertArrayHasKey('pending', LeadStatusCatalog::options());
        $this->assertSame('In progress', LeadStatusCatalog::options()['pending']);

        $this->deleteJson($this->list('lead_status'), ['value' => 'pending', 'move_to' => 'new'])->assertStatus(422);
    }

    public function test_custom_status_works_end_to_end_and_needs_a_new_home_for_its_leads(): void
    {
        $this->actingAsCrmAdmin();
        $this->postJson($this->list('lead_status'), ['name' => 'Waiting list', 'tone' => 'warning'])->assertOk();
        $this->assertSame('Waiting list', LeadStatusCatalog::options()['waiting_list']);

        $lead = $this->lead('Waiter', ['lead_status' => 'waiting_list']);
        $this->leads(['lead_status' => 'waiting_list'])->assertOk()->assertSee('Waiter');

        $this->deleteJson($this->list('lead_status'), ['value' => 'waiting_list'])->assertStatus(422);
        $this->deleteJson($this->list('lead_status'), ['value' => 'waiting_list', 'move_to' => 'pending'])->assertOk();

        $this->assertSame('pending', $lead->fresh()->lead_status);
        $this->assertArrayNotHasKey('waiting_list', LeadStatusCatalog::options());
    }

    public function test_a_new_status_never_takes_over_a_renamed_one(): void
    {
        $this->actingAsCrmAdmin();
        $this->postJson($this->list('lead_status'), ['name' => 'Waiting'])->assertOk();
        $this->patchJson($this->list('lead_status'), ['value' => 'waiting', 'name' => 'Paused'])->assertOk();
        $this->postJson($this->list('lead_status'), ['name' => 'Waiting'])->assertOk();

        $this->assertSame('Paused', LeadStatusCatalog::options()['waiting']);
        $this->assertSame('Waiting', LeadStatusCatalog::options()['waiting_2']);
    }

    public function test_category_can_be_added_recolored_and_removed_with_its_leads_moved(): void
    {
        $this->actingAsCrmAdmin();
        $this->postJson($this->list('lead_category_id'), ['name' => 'Teachers', 'tone' => 'info'])->assertOk();
        $this->postJson($this->list('lead_category_id'), ['name' => 'Parents', 'tone' => 'info'])->assertOk();
        $teachers = LeadCategory::where('name', 'Teachers')->firstOrFail();
        $parents = LeadCategory::where('name', 'Parents')->firstOrFail();

        $this->patchJson($this->list('lead_category_id'), ['value' => (string) $teachers->id, 'tone' => 'success'])->assertOk();
        $this->assertSame('success', $teachers->fresh()->tone);

        $lead = $this->lead('Teach', ['lead_category_id' => $teachers->id]);
        $this->deleteJson($this->list('lead_category_id'), ['value' => (string) $teachers->id, 'move_to' => (string) $parents->id])->assertOk();

        $this->assertSame($parents->id, $lead->fresh()->lead_category_id);
        $this->assertNull(LeadCategory::find($teachers->id));
    }

    public function test_custom_filter_shows_up_on_forms_and_filters_leads(): void
    {
        $this->addCustomFilter();

        $this->actingAsCrmAdmin()->postJson(route('admin.crm.leads.store'), [
            'first_name' => 'NorthLead', 'lead_status' => 'new', 'custom_fields' => ['campus' => 'North'],
        ])->assertSuccessful();
        $this->lead('NoCampus');
        $this->lead('SouthLead', ['custom_fields' => ['campus' => 'South']]);

        $this->assertSame(['campus' => 'North'], Lead::where('first_name', 'NorthLead')->first()->custom_fields);

        $this->leads(['cf_campus' => 'North'])->assertSee('NorthLead')->assertDontSee('SouthLead')->assertDontSee('NoCampus');
        $this->leads(['cf_campus' => LeadFormOptions::FILTER_NOT_SET])->assertSee('NoCampus')->assertDontSee('NorthLead');
        $this->leads()->assertSee('name="cf_campus"', false);

        $this->actingAsCrmAdmin()->get(route('admin.crm.leads.create.panel'))->assertOk()->assertSee('custom_fields[campus]', false);

        $this->actingAsCrmAdmin()->postJson(route('admin.crm.leads.store'), [
            'first_name' => 'Bad', 'lead_status' => 'new', 'custom_fields' => ['campus' => 'Moon'],
        ])->assertStatus(422);
    }

    public function test_custom_filter_options_rename_with_their_leads_and_removal_keeps_lead_data(): void
    {
        $this->addCustomFilter();
        $lead = $this->lead('Camper', ['custom_fields' => ['campus' => 'North', 'other' => 'kept']]);

        $this->actingAsCrmAdmin()->patchJson($this->list('cf_campus'), ['value' => 'North', 'name' => 'Northside'])
            ->assertOk()->assertJsonPath('message', 'Renamed, and updated 1 lead');
        $this->assertSame(['campus' => 'Northside', 'other' => 'kept'], $lead->fresh()->custom_fields);

        $this->actingAsCrmAdmin()->deleteJson(route('admin.crm.settings.leads.custom-filters.destroy', 'campus'))->assertOk();
        $this->leads()->assertDontSee('name="cf_campus"', false);
        $this->assertSame('Northside', $lead->fresh()->custom_fields['campus']);
    }

    public function test_editing_a_lead_keeps_custom_values_it_was_not_sent(): void
    {
        $this->addCustomFilter();
        $lead = $this->lead('Stay', ['custom_fields' => ['campus' => 'South', 'old_key' => 'x']]);

        $this->actingAsCrmAdmin()->putJson(route('admin.crm.leads.update', $lead), [
            'first_name' => 'Stay', 'lead_status' => 'new', 'custom_fields' => ['campus' => ''],
        ])->assertOk();
        $this->assertSame(['old_key' => 'x'], $lead->fresh()->custom_fields);

        $this->actingAsCrmAdmin()->putJson(route('admin.crm.leads.update', $lead), ['first_name' => 'Stay', 'lead_status' => 'new'])->assertOk();
        $this->assertSame(['old_key' => 'x'], $lead->fresh()->custom_fields);
    }

    public function test_custom_filter_name_cannot_clash_with_another_filter(): void
    {
        $this->actingAsCrmAdmin()->postJson(route('admin.crm.settings.leads.custom-filters.store'), ['label' => 'status', 'options' => ['A']])->assertStatus(422);
        $this->actingAsCrmAdmin()->postJson(route('admin.crm.settings.leads.custom-filters.store'), ['label' => 'Campus', 'options' => []])->assertStatus(422);
    }

    public function test_bulk_update_respects_a_custom_filter(): void
    {
        $this->addCustomFilter();
        $north = $this->lead('N', ['custom_fields' => ['campus' => 'North']]);
        $south = $this->lead('S', ['custom_fields' => ['campus' => 'South']]);

        $this->actingAsCrmAdmin()->patchJson(route('admin.crm.leads.bulk-filter'), [
            'filters' => ['cf_campus' => 'North'],
            'field' => 'lead_status',
            'value' => 'pending',
        ])->assertOk();

        $this->assertSame('pending', $north->fresh()->lead_status);
        $this->assertSame('new', $south->fresh()->lead_status);
    }

    public function test_settings_belong_to_one_organization(): void
    {
        $this->actingAsCrmAdmin()->postJson($this->list('contact_method'), ['name' => 'Fax'])->assertOk();
        $this->addCustomFilter();
        $outside = Lead::create(['organization_id' => $this->organizationB->id, 'source' => 'manual', 'first_name' => 'B', 'lead_status' => 'new', 'contact_method' => 'SMS']);

        $this->actingAsCrmAdmin()->patchJson($this->list('contact_method'), ['value' => 'SMS', 'name' => 'Texting'])->assertOk();
        $this->assertSame('SMS', $outside->fresh()->contact_method);

        $this->actingAs($this->adminB, 'admin');
        app()->forgetInstance('crm.lead-settings.'.$this->organizationB->id);
        $this->assertNotContains('Fax', LeadFormOptions::contactMethods());
        $this->assertSame([], LeadSettings::customFilters());
        $this->assertContains('SMS', LeadFormOptions::contactMethods());
    }

    public function test_saving_settings_does_not_disturb_other_organization_metadata(): void
    {
        $this->organizationA->update(['metadata' => ['crm_pipeline_status_order' => ['pending', 'new']]]);

        $this->actingAsCrmAdmin()->postJson($this->list('contact_method'), ['name' => 'Fax'])->assertOk();

        $metadata = $this->organizationA->fresh()->metadata;
        $this->assertSame(['pending', 'new'], $metadata['crm_pipeline_status_order']);
        $this->assertContains('Fax', $metadata['crm_lead_settings']['contact_methods']);
    }

    public function test_labels_are_escaped_on_the_settings_page(): void
    {
        $this->actingAsCrmAdmin()->postJson($this->list('contact_method'), ['name' => '<script>x</script>'])->assertOk();

        $this->assertStringNotContainsString('<script>x</script>', $this->actingAsCrmAdmin()->get(route('admin.crm.settings.leads.edit'))->getContent());
    }
}
