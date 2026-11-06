<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;
use App\Support\LeadDateFilter;
use App\Support\LeadFormOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadFiltersTest extends CrmTestCase
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

    private function added(Lead $lead, string $date): void
    {
        DB::table('crm_leads')->where('id', $lead->id)->update(['created_at' => $date]);
    }

    private function page(array $query)
    {
        return $this->actingAsCrmAdmin()->get(route('admin.crm.leads.index', ['view' => 'list', ...$query]));
    }

    public function test_date_added_range_includes_both_end_days(): void
    {
        $this->added($this->lead('Before'), '2026-10-31 23:59:00');
        $this->added($this->lead('FirstDay'), '2026-11-01 00:00:00');
        $this->added($this->lead('LastDay'), '2026-11-06 23:59:00');
        $this->added($this->lead('After'), '2026-11-07 00:00:00');

        $this->page(['date_from' => '2026-11-01', 'date_to' => '2026-11-06'])
            ->assertOk()
            ->assertSee('FirstDay')
            ->assertSee('LastDay')
            ->assertDontSee('Before')
            ->assertDontSee('>After', false)
            ->assertSee('1–6 Nov 2026');
    }

    public function test_enquiry_date_uses_the_original_date_and_falls_back_to_date_added(): void
    {
        $this->added($this->lead('ImportedOld', ['source_submitted_at' => '2026-03-10 10:00:00']), '2026-11-05 09:00:00');
        $this->added($this->lead('ManualMarch'), '2026-03-11 09:00:00');
        $this->added($this->lead('ManualNov'), '2026-11-05 09:00:00');

        $this->page(['date_field' => 'enquiry', 'date_from' => '2026-03-01', 'date_to' => '2026-03-31'])
            ->assertSee('ImportedOld')
            ->assertSee('ManualMarch')
            ->assertDontSee('ManualNov');
    }

    public function test_follow_up_date_range(): void
    {
        $this->lead('DueSoon', ['next_follow_up_date' => '2026-12-02']);
        $this->lead('DueLater', ['next_follow_up_date' => '2027-01-02']);

        $this->page(['date_field' => 'follow_up', 'date_from' => '2026-12-01', 'date_to' => '2026-12-31'])
            ->assertSee('DueSoon')
            ->assertDontSee('DueLater');
    }

    public function test_bad_dates_and_fields_are_ignored_and_reversed_ranges_are_put_right(): void
    {
        $this->lead('Visible');

        foreach (['2026-13-45', "2026-01-01' OR 1=1", 'yesterday'] as $bad) {
            $this->page(['date_from' => $bad, 'date_field' => 'password'])->assertOk()->assertSee('Visible');
        }
        $this->page(['date_from' => ['x'], 'date_to' => ['y']])->assertOk();

        [$from, $to] = LeadDateFilter::range(Request::create('/', 'GET', ['date_from' => '2026-11-06', 'date_to' => '2026-11-01']));
        $this->assertSame(['2026-11-01', '2026-11-06'], [$from->toDateString(), $to->toDateString()]);
    }

    public function test_date_range_works_with_other_filters_and_export_and_bulk_action(): void
    {
        $match = $this->lead('InRange', ['year_group' => 'Year 7']);
        $otherYear = $this->lead('OtherYear', ['year_group' => 'Year 8']);
        $otherDate = $this->lead('OtherDate', ['year_group' => 'Year 7']);
        $this->added($match, '2026-11-02 10:00:00');
        $this->added($otherYear, '2026-11-02 10:00:00');
        $this->added($otherDate, '2026-01-02 10:00:00');

        $this->page(['year_group' => 'Year 7', 'date_from' => '2026-11-01', 'date_to' => '2026-11-06'])
            ->assertSee('InRange')
            ->assertDontSee('OtherYear')
            ->assertDontSee('OtherDate');

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.bulk-filter'), [
                'field' => 'admission_intake',
                'value' => 'Sept 27/28',
                'filters' => ['year_group' => 'Year 7', 'date_from' => '2026-11-01', 'date_to' => '2026-11-06'],
            ])
            ->assertOk();

        $this->assertSame('Sept 27/28', $match->fresh()->admission_intake);
        $this->assertNull($otherYear->fresh()->admission_intake);
        $this->assertNull($otherDate->fresh()->admission_intake);
    }

    public function test_not_set_finds_leads_with_no_intake_or_year_group(): void
    {
        $this->lead('Tagged', ['admission_intake' => 'Sept 27/28', 'year_group' => 'Year 7']);
        $this->lead('Untagged');
        $this->lead('BlankText', ['admission_intake' => '', 'year_group' => '']);

        $this->page(['admission_intake' => LeadFormOptions::FILTER_NOT_SET])
            ->assertSee('Untagged')->assertSee('BlankText')->assertDontSee('Tagged<', false);

        $this->page(['year_group' => LeadFormOptions::FILTER_NOT_SET])
            ->assertSee('Untagged')->assertSee('BlankText');
    }

    public function test_follow_up_contact_children_and_converted_filters(): void
    {
        $this->lead('NoPhone', ['email' => 'a@example.test', 'next_follow_up_date' => now()->addDays(3)->toDateString()]);
        $this->lead('HasPhone', ['phone' => '07700900123', 'extra_year_groups' => ['Year 2'], 'year_group' => 'Year 1']);
        $this->lead('Zubair', ['phone' => '07700900124', 'is_converted' => true]);

        $this->page(['contact_details' => 'missing_phone'])->assertSee('NoPhone')->assertDontSee('HasPhone');
        $this->page(['contact_details' => 'has_phone'])->assertSee('HasPhone')->assertDontSee('NoPhone');
        $this->page(['contact_details' => 'missing_email'])->assertSee('HasPhone')->assertDontSee('NoPhone');
        $this->page(['children' => 'multiple'])->assertSee('HasPhone')->assertDontSee('NoPhone');
        $this->page(['converted' => 'yes'])->assertSee('Zubair')->assertDontSee('HasPhone');
        $this->page(['converted' => 'no'])->assertSee('HasPhone')->assertDontSee('Zubair');
        $this->page(['follow_up' => 'upcoming'])->assertSee('NoPhone')->assertDontSee('HasPhone');
        $this->page(['follow_up' => 'none'])->assertSee('HasPhone')->assertDontSee('NoPhone');
    }

    public function test_main_filters_are_outside_the_collapsed_section(): void
    {
        $html = $this->page([])->assertOk()->assertSee('Any date')->assertSee('Overdue')->getContent();

        $collapsedAt = strpos($html, 'crm-lead-finder__more"');
        foreach (['id="lead_status"', 'id="year_group"', 'id="admission_intake"', 'id="assigned_to"'] as $field) {
            $this->assertLessThan($collapsedAt, strpos($html, $field), $field.' should be visible without opening More filters');
        }
    }

    public function test_empty_results_explain_what_to_do_and_keep_the_view(): void
    {
        $this->lead('Someone');

        $this->page(['admission_intake' => 'Sept 27/28'])
            ->assertOk()
            ->assertSee('No leads match these filters')
            ->assertSee('Show leads with no intake')
            ->assertSee('admission_intake='.LeadFormOptions::FILTER_NOT_SET, false)
            ->assertSee('Clear all filters')
            ->assertSee('is-no-results', false);

        $this->page(['date_from' => '2001-01-01', 'date_to' => '2001-01-02'])
            ->assertSee('No leads match these filters')
            ->assertDontSee('Show leads with no intake');

        $this->page([])->assertDontSee('No leads match these filters');
    }

    public function test_filter_form_keeps_the_date_range_when_another_filter_changes(): void
    {
        $this->page(['date_field' => 'enquiry', 'date_from' => '2026-11-01', 'date_to' => '2026-11-06'])
            ->assertSee('<input type="hidden" name="date_from" value="2026-11-01">', false)
            ->assertSee('<input type="hidden" name="date_to" value="2026-11-06">', false)
            ->assertSee('<input type="hidden" name="date_field" value="enquiry">', false);
    }
}
