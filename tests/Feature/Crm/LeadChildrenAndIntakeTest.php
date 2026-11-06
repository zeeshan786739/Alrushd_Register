<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;
use App\Models\Form;
use App\Models\FormEntry;
use App\Support\FormEntryContact;
use App\Support\LeadFormOptions;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;

class LeadChildrenAndIntakeTest extends CrmTestCase
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

    private function payload(array $attributes = []): array
    {
        return array_merge(['first_name' => 'Parent', 'lead_status' => 'new'], $attributes);
    }

    public function test_a_lead_can_be_created_with_four_children_including_twins(): void
    {
        $this->actingAsCrmAdmin()
            ->postJson(route('admin.crm.leads.store'), $this->payload([
                'year_group' => 'Year 3',
                'extra_year_groups' => ['', 'Year 3', 'Year 7', 'Reception'],
                'admission_intake' => 'Sept 27/28',
            ]))
            ->assertCreated();

        $lead = Lead::where('first_name', 'Parent')->firstOrFail();
        $this->assertSame('Year 3', $lead->year_group);
        $this->assertSame(['Year 3', 'Year 7', 'Reception'], $lead->extra_year_groups);
        $this->assertSame(['Year 3', 'Year 3', 'Year 7', 'Reception'], $lead->childYearGroups());
        $this->assertSame('Sept 27/28', $lead->admission_intake);
    }

    public function test_a_fifth_child_is_rejected(): void
    {
        $this->actingAsCrmAdmin()
            ->postJson(route('admin.crm.leads.store'), $this->payload([
                'year_group' => 'Year 1',
                'extra_year_groups' => ['Year 2', 'Year 3', 'Year 4', 'Year 5'],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('extra_year_groups');
    }

    public function test_year_groups_and_intakes_must_come_from_the_lists(): void
    {
        $this->actingAsCrmAdmin()
            ->postJson(route('admin.crm.leads.store'), $this->payload([
                'extra_year_groups' => ['Year 3', '<script>'],
                'admission_intake' => 'Whenever',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['extra_year_groups.0', 'admission_intake']);
    }

    public function test_when_the_first_child_is_blank_the_next_child_moves_up(): void
    {
        $this->actingAsCrmAdmin()
            ->postJson(route('admin.crm.leads.store'), $this->payload([
                'year_group' => '',
                'extra_year_groups' => ['', 'Year 8'],
            ]))
            ->assertCreated();

        $lead = Lead::where('first_name', 'Parent')->firstOrFail();
        $this->assertSame('Year 8', $lead->year_group);
        $this->assertNull($lead->extra_year_groups);
    }

    public function test_removing_every_extra_child_is_saved_but_a_save_without_the_list_keeps_them(): void
    {
        $lead = $this->lead(['year_group' => 'Year 1', 'extra_year_groups' => ['Year 2']]);

        $this->actingAsCrmAdmin()
            ->putJson(route('admin.crm.leads.update', $lead), $this->payload(['year_group' => 'Year 1']))
            ->assertOk();
        $this->assertSame(['Year 2'], $lead->fresh()->extra_year_groups);

        $this->actingAsCrmAdmin()
            ->putJson(route('admin.crm.leads.update', $lead), $this->payload(['year_group' => 'Year 1', 'extra_year_groups' => ['']]))
            ->assertOk();
        $this->assertNull($lead->fresh()->extra_year_groups);
    }

    public function test_a_year_group_outside_the_list_survives_an_edit(): void
    {
        $lead = $this->lead(['year_group' => 'Year 1', 'extra_year_groups' => ['Old Year Name'], 'admission_intake' => 'Sept 24/25']);

        $this->actingAsCrmAdmin()
            ->putJson(route('admin.crm.leads.update', $lead), $this->payload([
                'year_group' => 'Year 1',
                'extra_year_groups' => ['Old Year Name'],
                'admission_intake' => 'Sept 24/25',
            ]))
            ->assertOk();

        $this->assertSame(['Old Year Name'], $lead->fresh()->extra_year_groups);
    }

    public function test_year_group_filter_finds_a_lead_by_any_of_its_children(): void
    {
        $this->lead(['first_name' => 'Second child match', 'year_group' => 'Year 1', 'extra_year_groups' => ['Year 9']]);
        $this->lead(['first_name' => 'First child match', 'year_group' => 'Year 9']);
        $this->lead(['first_name' => 'No match', 'year_group' => 'Year 2', 'extra_year_groups' => ['Year 4']]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['view' => 'list', 'year_group' => 'Year 9']))
            ->assertOk()
            ->assertSee('Second child match')
            ->assertSee('First child match')
            ->assertDontSee('No match');
    }

    public function test_the_sept_27_28_filter_is_offered_and_shows_only_those_leads(): void
    {
        $this->lead(['first_name' => 'Joining 2027', 'admission_intake' => 'Sept 27/28']);
        $this->lead(['first_name' => 'Joining 2026', 'admission_intake' => 'Sept 26/27']);
        $this->lead(['first_name' => 'Waheed Untagged']);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['view' => 'list']))
            ->assertOk()
            ->assertSee('Sept 27/28 admissions')
            ->assertSee('admission_intake=Sept', false);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['view' => 'list', 'admission_intake' => 'Sept 27/28']))
            ->assertOk()
            ->assertSee('Joining 2027')
            ->assertDontSee('Joining 2026')
            ->assertDontSee('Waheed Untagged');
    }

    public function test_intake_can_be_set_and_cleared_in_bulk(): void
    {
        $lead = $this->lead();

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.bulk'), ['lead_ids' => [$lead->id], 'field' => 'admission_intake', 'value' => 'Sept 27/28'])
            ->assertOk();
        $this->assertSame('Sept 27/28', $lead->fresh()->admission_intake);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.bulk'), ['lead_ids' => [$lead->id], 'field' => 'admission_intake', 'value' => 'Nonsense'])
            ->assertUnprocessable();

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.bulk'), ['lead_ids' => [$lead->id], 'field' => 'admission_intake', 'value' => null])
            ->assertOk();
        $this->assertNull($lead->fresh()->admission_intake);
    }

    public function test_intake_can_be_set_on_all_leads_matching_a_filter(): void
    {
        $match = $this->lead(['year_group' => 'Year 7']);
        $other = $this->lead(['year_group' => 'Year 8']);

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.bulk-filter'), [
                'field' => 'admission_intake',
                'value' => 'Sept 27/28',
                'filters' => ['year_group' => 'Year 7'],
            ])
            ->assertOk();

        $this->assertSame('Sept 27/28', $match->fresh()->admission_intake);
        $this->assertNull($other->fresh()->admission_intake);
    }

    public function test_create_and_edit_panels_offer_children_and_intake(): void
    {
        $lead = $this->lead(['year_group' => 'Year 1', 'extra_year_groups' => ['Year 2'], 'admission_intake' => 'Sept 27/28']);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.create.panel'))
            ->assertOk()
            ->assertSee('Add another child')
            ->assertSee('name="extra_year_groups[]"', false)
            ->assertSee('name="admission_intake"', false);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.panel.edit', $lead))
            ->assertOk()
            ->assertSee('Child 2 year group')
            ->assertSee('<option value="Year 2" selected', false);
    }

    /** @return array<int, array{0: ?string, 1: ?string}> */
    public static function intakeTexts(): array
    {
        return [
            ['Sept 27/28', 'Sept 27/28'],
            ['Sept 2027', 'Sept 27/28'],
            ['September 2027', 'Sept 27/28'],
            ['sep 27', 'Sept 27/28'],
            ['2027/28', 'Sept 27/28'],
            ['2027-2028', 'Sept 27/28'],
            ['27-28', 'Sept 27/28'],
            ['2027-09-01', 'Sept 27/28'],
            ['01/09/2027', 'Sept 27/28'],
            ['1 September 2026', 'Sept 26/27'],
            ['10/11/2026', null],
            ['2027-01-15', null],
            ['Interested', null],
            ['January 2027', null],
            ['', null],
            [null, null],
        ];
    }

    #[DataProvider('intakeTexts')]
    public function test_intake_is_read_only_from_clear_september_text(?string $text, ?string $expected): void
    {
        $this->assertSame($expected, LeadFormOptions::normalizeAdmissionIntake($text));
    }

    private function entry(array $data): FormEntry
    {
        $form = Form::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Student application',
            'slug' => 'student-application-'.uniqid(),
            'is_active' => true,
        ]);

        return FormEntry::create([
            'organization_id' => $this->organizationA->id,
            'form_id' => $form->id,
            'legacy_source' => 'test',
            'legacy_record_id' => random_int(1, 999999),
            'data' => $data,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    public function test_a_form_listing_several_students_fills_in_each_child_and_the_intake(): void
    {
        $contact = FormEntryContact::fromEntry($this->entry([
            'fname' => 'Parent',
            'students' => [
                ['fname' => 'A', 'year_group' => 'Year 3', 'start_date' => 'September 2027'],
                ['fname' => 'B', 'year_group' => 'Year 5', 'start_date' => 'September 2027'],
            ],
        ]));

        $this->assertSame('Year 3', $contact['year_group']);
        $this->assertSame(['Year 5'], $contact['extra_year_groups']);
        $this->assertSame('Sept 27/28', $contact['admission_intake']);
    }

    public function test_a_job_application_start_date_is_never_treated_as_an_admission(): void
    {
        $contact = FormEntryContact::fromEntry($this->entry(['name' => 'Teacher', 'start_date' => '2027-09-01']));

        $this->assertNull($contact['admission_intake']);
        $this->assertNull($contact['extra_year_groups']);
    }

    public function test_migration_fills_existing_leads_from_imports_and_form_submissions(): void
    {
        $imported = $this->lead(['custom_data' => ['Current status' => 'Sept 2027']]);
        $unclear = $this->lead(['custom_data' => ['Current status' => 'Interested']]);
        $entry = $this->entry(['students' => [
            ['year_group' => 'Year 2', 'start_date' => '2027-09-01'],
            ['year_group' => 'Year 6', 'start_date' => '2027-09-01'],
        ]]);
        $fromForm = Lead::where('form_entry_id', $entry->id)->first() ?? $this->lead(['form_entry_id' => $entry->id]);
        DB::table('crm_leads')->where('id', $fromForm->id)->update(['year_group' => null, 'extra_year_groups' => null, 'admission_intake' => null]);

        $migration = require database_path('migrations/2026_11_06_100000_add_admission_intake_and_extra_year_groups_to_crm_leads.php');
        $migration->down();
        $migration->up();

        $this->assertSame('Sept 27/28', $imported->fresh()->admission_intake);
        $this->assertNull($unclear->fresh()->admission_intake);
        $this->assertSame('Year 2', $fromForm->fresh()->year_group);
        $this->assertSame(['Year 6'], $fromForm->fresh()->extra_year_groups);
        $this->assertSame('Sept 27/28', $fromForm->fresh()->admission_intake);
    }

    public function test_tags_show_each_year_group_once_with_a_count_for_twins(): void
    {
        $lead = $this->lead(['year_group' => 'Year 3', 'extra_year_groups' => ['Year 3', 'Year 7'], 'admission_intake' => 'Sept 27/28']);

        $labels = array_column(\App\Support\LeadAdmissionTags::forLead($lead), 'label');

        $this->assertSame(['Year 3 ×2', 'Year 7', 'Sept 27/28'], $labels);
    }
}
