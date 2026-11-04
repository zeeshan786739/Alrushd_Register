<?php

namespace Tests\Feature\Crm;

use App\Models\Form;
use App\Models\FormEntry;
use App\Models\FormField;

class FormSubmissionsBrowseTest extends CrmTestCase
{
    private Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->form = Form::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Job Applications',
            'slug' => 'job-applications-test',
            'is_active' => true,
        ]);
        FormField::create(['form_id' => $this->form->id, 'key' => 'position_applied_for', 'label' => 'Position Applied For', 'type' => 'select']);

        $this->entry('Mohammad Rasool', 'noor@example.com', '+93 729 285 088', 'Maths Teacher', 'Maths, Physics', 'pending', '2026-07-09 12:22:00');
        $this->entry('aamna Rehman', 'aamna@example.com', '07742786769', 'Arabic Tutor', 'Arabic', 'approved', '2026-06-25 12:52:00');
        $this->entry('Zeinab Safrulla', 'zeinab@example.com', '0770368266', 'Maths Teacher', null, 'rejected', '2026-06-23 08:46:00');
        $this->entry(null, 'noname@example.com', '0700000000', null, null, 'pending', '2026-06-01 08:00:00');
    }

    private function entry(?string $name, string $email, string $phone, ?string $role, ?string $subject, string $status, string $submittedAt): FormEntry
    {
        // Matches real legacy rows: unanswered columns are stored as explicit nulls.
        return FormEntry::create([
            'organization_id' => $this->organizationA->id,
            'form_id' => $this->form->id,
            'legacy_source' => 'job_applications',
            'legacy_record_id' => random_int(1, 999999),
            'data' => [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'position_applied_for' => $role,
                'subject_skill' => $subject,
            ],
            'status' => $status,
            'submitted_at' => $submittedAt,
        ]);
    }

    private function page(array $query = [])
    {
        return $this->actingAsCrmAdmin()->get(route('admin.form-manager.entries', array_merge(['form' => $this->form], $query)));
    }

    /** @return list<string> applicant emails in the order rows appear */
    private function orderOf(array $query): array
    {
        preg_match_all('/[a-z]+@example\.com/', $this->page($query)->assertOk()->getContent(), $matches);

        return array_values(array_unique($matches[0]));
    }

    public function test_rows_show_applicant_and_role_and_hide_internal_table_names(): void
    {
        $this->page()
            ->assertOk()
            ->assertSee('Mohammad Rasool')
            ->assertSee('Maths Teacher')
            ->assertSee('Position Applied For')
            ->assertDontSee('Legacy:')
            ->assertDontSee('form_entries');
    }

    public function test_select_fields_load_the_styled_menu_and_status_options_carry_their_colour(): void
    {
        $this->page()
            ->assertOk()
            ->assertSee('admin/assets/js/crm-select.js', false)
            ->assertSee('admin/assets/css/crm-select.css', false)
            ->assertSee('<option value="approved" data-tone="success"', false)
            ->assertSee('<option value="rejected" data-tone="danger"', false);
    }

    public function test_default_order_is_newest_first(): void
    {
        $this->assertSame(
            ['noor@example.com', 'aamna@example.com', 'zeinab@example.com', 'noname@example.com'],
            $this->orderOf([])
        );
    }

    public function test_sort_oldest_first(): void
    {
        $this->assertSame(
            ['noname@example.com', 'zeinab@example.com', 'aamna@example.com', 'noor@example.com'],
            $this->orderOf(['sort' => 'oldest'])
        );
    }

    public function test_sort_by_name_ignores_case_and_keeps_blank_names_last(): void
    {
        $this->assertSame(
            ['aamna@example.com', 'noor@example.com', 'zeinab@example.com', 'noname@example.com'],
            $this->orderOf(['sort' => 'name_asc'])
        );
        $this->assertSame(
            ['zeinab@example.com', 'noor@example.com', 'aamna@example.com', 'noname@example.com'],
            $this->orderOf(['sort' => 'name_desc'])
        );
    }

    public function test_sort_by_role_then_newest_and_keeps_blank_roles_last(): void
    {
        $this->assertSame(
            ['aamna@example.com', 'noor@example.com', 'zeinab@example.com', 'noname@example.com'],
            $this->orderOf(['sort' => 'role_asc'])
        );
    }

    public function test_sort_by_status(): void
    {
        $order = $this->orderOf(['sort' => 'status']);

        $this->assertSame('zeinab@example.com', end($order));
        $this->assertSame('aamna@example.com', $order[2]);
    }

    public function test_unknown_sort_falls_back_to_newest(): void
    {
        $this->assertSame('noor@example.com', $this->orderOf(['sort' => "x'; DROP TABLE forms;--"])[0]);
    }

    public function test_filter_by_role_ignores_case_and_offers_only_real_answers(): void
    {
        $this->assertSame(['noor@example.com', 'zeinab@example.com'], $this->orderOf(['role' => 'maths teacher']));

        $this->page()->assertSee('value="Arabic Tutor"', false)->assertDontSee('value="null"', false);
    }

    public function test_filter_by_subject_matches_part_of_a_free_text_answer(): void
    {
        $this->assertSame(['noor@example.com'], $this->orderOf(['subject' => 'physics']));
    }

    public function test_search_is_case_insensitive_and_looks_at_answers_not_field_names(): void
    {
        $this->assertSame(['noor@example.com'], $this->orderOf(['search' => 'RASOOL']));
        $this->assertSame([], $this->orderOf(['search' => 'position_applied_for']));
    }

    public function test_search_matches_a_phone_number_typed_without_spaces(): void
    {
        $this->assertSame(['noor@example.com'], $this->orderOf(['search' => '93729285088']));
    }

    public function test_search_treats_percent_as_text_not_a_wildcard(): void
    {
        $this->assertSame([], $this->orderOf(['search' => '%']));
    }

    public function test_status_and_date_filters_combine_with_sort(): void
    {
        $this->assertSame(['noname@example.com', 'noor@example.com'], $this->orderOf(['status' => 'pending', 'sort' => 'oldest']));
        $this->assertSame(['aamna@example.com'], $this->orderOf(['date_from' => '2026-06-24', 'date_to' => '2026-06-30']));
    }

    public function test_status_pills_keep_the_current_sort_and_filters(): void
    {
        $this->page(['sort' => 'name_asc', 'role' => 'Maths Teacher'])
            ->assertSee('sort=name_asc&amp;role=Maths+Teacher&amp;status=pending', false);
    }

    public function test_filters_are_hidden_when_a_form_has_no_such_answers(): void
    {
        $this->page()->assertDontSee('id="fc-entry-year_group"', false);
    }

    public function test_another_organization_cannot_open_the_page(): void
    {
        $this->actingAsCrmAdmin($this->adminB)
            ->get(route('admin.form-manager.entries', $this->form))
            ->assertNotFound();
    }

    public function test_crm_submissions_list_sorts_searches_and_only_lists_own_forms(): void
    {
        Form::create(['organization_id' => $this->organizationB->id, 'name' => 'Other Org Secret Form', 'slug' => 'other-org', 'is_active' => true]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.form-entries.index', ['search' => 'zeinab', 'sort' => 'name_asc']))
            ->assertOk()
            ->assertSee('Zeinab')
            ->assertDontSee('noor@example.com')
            ->assertDontSee('Other Org Secret Form');
    }

    public function test_converting_a_submission_with_a_list_answer_to_a_customer_does_not_crash(): void
    {
        $entry = FormEntry::create([
            'organization_id' => $this->organizationA->id,
            'form_id' => $this->form->id,
            'data' => ['first_name' => 'Amina', 'email' => 'amina@example.com', 'students' => [['name' => 'A']]],
            'status' => 'pending',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.form-entries.convert-customer', $entry))
            ->assertRedirect();

        $this->assertDatabaseHas('crm_customers', ['email' => 'amina@example.com']);
    }

    public function test_malformed_filter_values_do_not_cause_a_server_error(): void
    {
        $this->page(['search' => ['x'], 'role' => ['y'], 'subject' => ['z'], 'sort' => ['name_asc'], 'status' => ['pending'], 'date_from' => ['a']])->assertOk();

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.form-entries.index', ['search' => ['x'], 'sort' => ['y'], 'status' => ['z']]))
            ->assertOk();
    }

    public function test_search_input_cannot_inject_sql(): void
    {
        $attack = "x') OR 1=1 -- ";

        $this->assertSame([], $this->orderOf(['search' => $attack]));
        $this->assertSame([], $this->orderOf(['role' => $attack]));
        $this->assertDatabaseCount('form_entries', 4);
    }

    public function test_applicant_answers_are_escaped_when_displayed(): void
    {
        $this->entry('<script>alert(1)</script>', 'xss@example.com', '0700000001', '<img src=x onerror=alert(2)>', null, 'pending', '2026-08-01 10:00:00');

        $this->page()
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(2)>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_submissions_require_the_matching_permissions(): void
    {
        $entry = FormEntry::query()->where('form_id', $this->form->id)->firstOrFail();
        $limited = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'no-submissions', 'guard_name' => 'admin']);
        $limited->syncPermissions(['view leads']);
        $this->adminA->syncRoles([$limited]);
        $this->actingAsCrmAdmin();

        $this->get(route('admin.form-manager.entries', $this->form))->assertForbidden();
        $this->get(route('admin.form-manager.entries.show', [$this->form, $entry]))->assertForbidden();
        $this->patch(route('admin.form-manager.entries.status', [$this->form, $entry]), ['status' => 'approved'])->assertForbidden();
        $this->delete(route('admin.form-manager.entries.destroy', [$this->form, $entry]))->assertForbidden();
        $this->assertDatabaseHas('form_entries', ['id' => $entry->id, 'status' => $entry->status]);

        $limited->givePermissionTo('view form submissions');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->adminA->refresh();
        $this->get(route('admin.form-manager.entries', $this->form))->assertOk();
    }

    public function test_page_has_one_status_control_and_no_duplicate_metrics(): void
    {
        $this->page()
            ->assertOk()
            ->assertSee('fc-subs-tabs', false)
            ->assertDontSee('id="fc-entry-status"', false)
            ->assertDontSee('class="crm-metrics-strip', false)
            ->assertSee('4 submissions · 2 awaiting review');
    }

    public function test_active_filters_show_as_removable_chips_that_keep_the_other_filters(): void
    {
        $response = $this->page(['role' => 'Maths Teacher', 'search' => 'rasool', 'sort' => 'name_asc'])->assertOk();

        $response->assertSee('fc-subs-chip', false)->assertSee('Clear all');
        $this->assertStringContainsString('search=rasool&amp;sort=name_asc"', $response->getContent());
    }

    public function test_filter_panel_stays_closed_until_a_panel_filter_is_used(): void
    {
        $closed = '/id="fc-subs-panel"\s+hidden/';

        $this->assertMatchesRegularExpression($closed, $this->page()->getContent());
        $this->assertDoesNotMatchRegularExpression($closed, $this->page(['date_from' => '2026-06-01'])->getContent());
    }

    public function test_empty_results_offer_a_way_back(): void
    {
        $this->page(['search' => 'nobody-matches-this'])
            ->assertOk()
            ->assertSee('No submissions match')
            ->assertSee('Show all submissions');
    }

    public function test_status_can_be_changed_from_the_list(): void
    {
        $entry = FormEntry::query()->where('form_id', $this->form->id)->where('status', 'pending')->firstOrFail();

        $this->page()->assertSee('id="fc-status-'.$entry->id.'"', false);

        $this->actingAsCrmAdmin()
            ->from(route('admin.form-manager.entries', ['form' => $this->form, 'sort' => 'oldest']))
            ->patch(route('admin.form-manager.entries.status', [$this->form, $entry]), ['status' => 'approved'])
            ->assertRedirect(route('admin.form-manager.entries', ['form' => $this->form, 'sort' => 'oldest']));

        $this->assertSame('approved', $entry->fresh()->status);
    }

    public function test_controls_are_hidden_from_people_without_permission(): void
    {
        $viewer = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'viewer-only', 'guard_name' => 'admin']);
        $viewer->syncPermissions(['view form submissions']);
        $this->adminA->syncRoles([$viewer]);

        $this->page()
            ->assertOk()
            ->assertDontSee('fc-subs-status-select" data-status', false)
            ->assertDontSee('Delete submission');
    }
}
