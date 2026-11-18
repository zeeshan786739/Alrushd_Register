<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Services\WebsiteCmsService;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Website CMS → Announcement & portal: when the bar shows, how older settings keep the live
 * website unchanged, and server-side validation of links, text and dates.
 */
class WebsiteAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Notice school', 'slug' => 'notice-school', 'is_active' => true]);
        $cms = WebsiteCms::forOrganization($this->school->id);
        $cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.notice-school.test'])->save();
        $this->token = $cms->rotateApiToken();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);
        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@notice-school.test',
            'password' => bcrypt('password'),
            'organization_id' => $this->school->id,
        ]);
        $this->admin->assignRole($role);
        PublicOrganizationContext::clear();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        PublicOrganizationContext::clear();
        parent::tearDown();
    }

    private function bar(array $overrides = []): array
    {
        return array_merge(['announcement' => 'Open day on Saturday', 'announcement_enabled' => true], $overrides);
    }

    private function saveWebsite(array $website)
    {
        return $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => ['website' => $website]]);
    }

    /* ── visibility ── */

    public function test_the_bar_shows_only_when_switched_on_and_it_has_text(): void
    {
        $this->assertTrue(WebsiteCmsService::announcementIsActive($this->bar(), '2026-12-01'));
        $this->assertFalse(WebsiteCmsService::announcementIsActive($this->bar(['announcement_enabled' => false]), '2026-12-01'));
        $this->assertFalse(WebsiteCmsService::announcementIsActive($this->bar(['announcement' => '   ']), '2026-12-01'));
    }

    public function test_the_bar_shows_only_inside_its_dates_both_inclusive(): void
    {
        $dated = $this->bar(['announcement_starts_at' => '2026-12-01', 'announcement_ends_at' => '2026-12-15']);

        $this->assertFalse(WebsiteCmsService::announcementIsActive($dated, '2026-11-30'));
        $this->assertTrue(WebsiteCmsService::announcementIsActive($dated, '2026-12-01'));
        $this->assertTrue(WebsiteCmsService::announcementIsActive($dated, '2026-12-15'));
        $this->assertFalse(WebsiteCmsService::announcementIsActive($dated, '2026-12-16'));

        // Either end may be open.
        $this->assertTrue(WebsiteCmsService::announcementIsActive($this->bar(['announcement_starts_at' => '2026-12-01']), '2030-01-01'));
        $this->assertTrue(WebsiteCmsService::announcementIsActive($this->bar(['announcement_ends_at' => '2026-12-15']), '2020-01-01'));
    }

    public function test_settings_from_before_the_switch_keep_the_website_unchanged(): void
    {
        // No announcement_enabled / portal_button_text keys: the bar shows because it has text,
        // and the portal button keeps the label the website always used.
        $legacy = ['announcement' => 'Admissions open', 'announcement_url' => '/apply-now'];
        $this->assertTrue(WebsiteCmsService::announcementIsActive($legacy, '2026-12-01'));
        $this->assertFalse(WebsiteCmsService::announcementIsActive(['announcement' => ''], '2026-12-01'));

        $merged = app(WebsiteCmsService::class)->merge(['website' => $legacy]);
        $this->assertTrue($merged['website']['announcement_enabled']);
        $this->assertSame('Portal Login', $merged['website']['portal_button_text']);
        $this->assertSame('gold', $merged['website']['announcement_style']);
        $this->assertSame('', $merged['website']['announcement_link_text']);
    }

    public function test_the_api_tells_the_website_whether_the_bar_shows_today(): void
    {
        Carbon::setTestNow('2026-12-20 10:00:00');
        $this->saveWebsite($this->bar(['announcement_starts_at' => '2026-12-01', 'announcement_ends_at' => '2026-12-15']))->assertOk();
        $this->actingAs($this->admin, 'admin')->postJson(route('admin.website-cms.publish'))->assertOk();

        $site = fn () => $this->withHeader('Authorization', 'Bearer '.$this->token)->getJson('/api/headless/v1/site')->assertOk();

        $site()->assertJsonPath('settings.website.announcement_active', false)
            ->assertJsonPath('settings.website.announcement_ends_at', '2026-12-15');

        Carbon::setTestNow('2026-12-10 10:00:00');
        $site()->assertJsonPath('settings.website.announcement_active', true);
    }

    /* ── validation ── */

    public function test_valid_settings_are_saved(): void
    {
        $this->saveWebsite($this->bar([
            'announcement_url' => '/apply-now',
            'announcement_link_text' => 'Apply now',
            'announcement_style' => 'navy',
            'announcement_starts_at' => '2026-12-01',
            'announcement_ends_at' => '2026-12-01',
            'portal_url' => 'https://school.notice-school.test/login',
            'portal_button_text' => 'Parent & student login',
        ]))->assertOk();

        $website = WebsiteCms::forOrganization($this->school->id)->fresh()->draft['website'];
        $this->assertSame('navy', $website['announcement_style']);
        $this->assertSame('Apply now', $website['announcement_link_text']);
        $this->assertSame('Parent & student login', $website['portal_button_text']);
    }

    public function test_links_must_be_a_page_path_or_a_full_web_address(): void
    {
        foreach (['apply-now', '//evil.example', 'javascript:alert(1)'] as $bad) {
            $this->saveWebsite($this->bar(['announcement_url' => $bad]))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['website.announcement_url']);
        }
        foreach (['/fees', 'https://example.org/open-day', '#faq'] as $good) {
            $this->saveWebsite($this->bar(['announcement_url' => $good]))->assertOk();
        }
    }

    public function test_portal_address_must_be_a_web_address(): void
    {
        $this->saveWebsite(['portal_url' => 'school.example/login'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['website.portal_url']);

        $this->saveWebsite(['portal_url' => 'https://school.example.org/login'])->assertOk();
    }

    public function test_text_length_style_and_dates_are_checked(): void
    {
        $this->saveWebsite($this->bar(['announcement' => str_repeat('a', 121)]))->assertStatus(422)->assertJsonValidationErrors(['website.announcement']);
        $this->saveWebsite($this->bar(['announcement' => str_repeat('a', 120)]))->assertOk();
        $this->saveWebsite($this->bar(['announcement_style' => 'pink']))->assertStatus(422)->assertJsonValidationErrors(['website.announcement_style']);
        $this->saveWebsite($this->bar(['announcement_starts_at' => '2026-12-15', 'announcement_ends_at' => '2026-12-01']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['website.announcement_ends_at']);
    }

    public function test_an_old_unchanged_value_never_blocks_saving_other_sections(): void
    {
        // A link saved before validation existed, left untouched while another section is saved.
        $cms = WebsiteCms::forOrganization($this->school->id);
        $cms->draft = app(WebsiteCmsService::class)->merge(['website' => ['announcement_url' => 'apply-now']]);
        $cms->save();

        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => [
                'website' => ['announcement_url' => 'apply-now'],
                'branding' => ['tagline' => 'Learning without limits'],
            ]])
            ->assertOk();
    }

    /* ── migration ── */

    public function test_migration_fills_new_settings_without_changing_the_live_site(): void
    {
        DB::table('website_cms')->where('organization_id', $this->school->id)->update([
            'draft' => json_encode(['website' => ['announcement' => 'Admissions open', 'portal_url' => 'https://p.test']]),
            'published' => json_encode(['website' => ['announcement' => '']]),
        ]);

        (require database_path('migrations/2026_11_17_100000_add_announcement_and_portal_settings_to_website_cms.php'))->up();

        $row = DB::table('website_cms')->where('organization_id', $this->school->id)->first();
        $draft = json_decode($row->draft, true)['website'];
        $published = json_decode($row->published, true)['website'];

        $this->assertTrue($draft['announcement_enabled']);
        $this->assertFalse($published['announcement_enabled']);
        $this->assertSame('Portal Login', $draft['portal_button_text']);
        $this->assertSame('gold', $draft['announcement_style']);
        $this->assertSame('', $draft['announcement_link_text']);
        $this->assertSame('https://p.test', $draft['portal_url']);
    }
}
