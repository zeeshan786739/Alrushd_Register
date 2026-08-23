<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;
use App\Models\Crm\SavedFilter;
use Carbon\Carbon;

class LeadStatsAndSavedFiltersTest extends CrmTestCase
{
    public function test_tiktok_seven_day_stat_counts_webhook_source_only(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-21 12:00:00'));

        $tiktokRecent = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'tiktok_lead_ads',
            'first_name' => 'TikTok Real',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);
        $tiktokRecent->forceFill(['created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)])->save();

        $imported = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'file_import',
            'advertising_platform' => 'tiktok',
            'first_name' => 'Imported TikTok',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);
        $imported->forceFill(['created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)])->save();

        $tiktokOld = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'tiktok_lead_ads',
            'first_name' => 'Old TikTok',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);
        $tiktokOld->forceFill(['created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)])->save();

        $otherOrg = Lead::create([
            'organization_id' => $this->organizationB->id,
            'source' => 'tiktok_lead_ads',
            'first_name' => 'Other Org TikTok',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);
        $otherOrg->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();

        $facebook = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'facebook_lead_ads',
            'first_name' => 'Facebook Real',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);
        $facebook->forceFill(['created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)])->save();

        $response = $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index'))
            ->assertOk();

        $stats = $response->viewData('stats');
        $this->assertSame(1, $stats['tiktok_this_week']);
        $this->assertSame(1, $stats['facebook_this_week']);
        $response->assertSee('TikTok (7d)', false);

        Carbon::setTestNow();
    }

    public function test_saved_filter_can_be_removed_by_owner_only(): void
    {
        $own = SavedFilter::create([
            'organization_id' => $this->organizationA->id,
            'admin_id' => $this->adminA->id,
            'module' => 'leads',
            'name' => 'Mine',
            'filters' => ['lead_status' => 'new'],
        ]);

        $otherAdmin = SavedFilter::create([
            'organization_id' => $this->organizationA->id,
            'admin_id' => $this->adminB->id,
            'module' => 'leads',
            'name' => 'Theirs',
            'filters' => ['priority' => 'high'],
        ]);

        $otherOrg = SavedFilter::create([
            'organization_id' => $this->organizationB->id,
            'admin_id' => $this->adminB->id,
            'module' => 'leads',
            'name' => 'Org B',
            'filters' => ['source' => 'manual'],
        ]);

        $this->actingAsCrmAdmin()
            ->deleteJson(route('admin.crm.leads.filters.destroy', $own))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseMissing('crm_saved_filters', ['id' => $own->id]);

        $this->actingAsCrmAdmin()
            ->deleteJson(route('admin.crm.leads.filters.destroy', $otherAdmin))
            ->assertNotFound();

        $this->actingAsCrmAdmin()
            ->deleteJson(route('admin.crm.leads.filters.destroy', $otherOrg))
            ->assertNotFound();

        $this->assertDatabaseHas('crm_saved_filters', ['id' => $otherAdmin->id]);
        $this->assertDatabaseHas('crm_saved_filters', ['id' => $otherOrg->id]);
    }

    public function test_clear_all_removes_only_current_admin_lead_filters(): void
    {
        SavedFilter::create([
            'organization_id' => $this->organizationA->id,
            'admin_id' => $this->adminA->id,
            'module' => 'leads',
            'name' => 'One',
            'filters' => ['lead_status' => 'new'],
        ]);
        SavedFilter::create([
            'organization_id' => $this->organizationA->id,
            'admin_id' => $this->adminA->id,
            'module' => 'leads',
            'name' => 'Two',
            'filters' => ['priority' => 'high'],
        ]);
        $other = SavedFilter::create([
            'organization_id' => $this->organizationA->id,
            'admin_id' => $this->adminB->id,
            'module' => 'leads',
            'name' => 'Keep',
            'filters' => ['priority' => 'low'],
        ]);

        $this->actingAsCrmAdmin()
            ->deleteJson(route('admin.crm.leads.filters.clear'))
            ->assertOk();

        $this->assertSame(0, SavedFilter::query()
            ->where('organization_id', $this->organizationA->id)
            ->where('admin_id', $this->adminA->id)
            ->where('module', 'leads')
            ->count());

        $this->assertDatabaseHas('crm_saved_filters', ['id' => $other->id]);
    }

    public function test_index_hides_saved_filters_section_when_empty(): void
    {
        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index'))
            ->assertOk()
            ->assertDontSee('Saved filters:', false);
    }
}
