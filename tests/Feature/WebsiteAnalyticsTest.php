<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Services\WebsiteCmsService;
use App\Support\AnalyticsSettings;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Website CMS → Analytics: ID formats, and which tools load before / after cookie consent. */
class WebsiteAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $admin;

    private const ALL = [
        'google_analytics' => 'G-ABC123DEF4',
        'google_tag_manager' => 'GTM-ABC1234',
        'facebook_pixel' => '123456789012345',
        'microsoft_clarity' => 'abcd1234ef',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Stats school', 'slug' => 'stats-school', 'is_active' => true]);
        WebsiteCms::forOrganization($this->school->id)
            ->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.stats-school.test'])->save();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);
        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@stats-school.test',
            'password' => bcrypt('password'),
            'organization_id' => $this->school->id,
        ]);
        $this->admin->assignRole($role);
        PublicOrganizationContext::clear();
    }

    protected function tearDown(): void
    {
        PublicOrganizationContext::clear();
        parent::tearDown();
    }

    private function saveAnalytics(array $analytics)
    {
        return $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => ['analytics' => $analytics]]);
    }

    /* ── validation ── */

    public function test_each_tool_accepts_only_its_own_id_format(): void
    {
        foreach (self::ALL as $tool => $id) {
            $this->assertTrue(AnalyticsSettings::isValid($tool, $id), $tool);
            $this->assertTrue(AnalyticsSettings::isValid($tool, ''), "{$tool} empty");
        }

        $this->assertFalse(AnalyticsSettings::isValid('google_analytics', 'UA-12345678-1'));   // old Universal Analytics
        $this->assertFalse(AnalyticsSettings::isValid('google_analytics', 'GTM-ABC1234'));
        $this->assertFalse(AnalyticsSettings::isValid('google_tag_manager', 'G-ABC123DEF4'));
        $this->assertFalse(AnalyticsSettings::isValid('facebook_pixel', '12345'));
        $this->assertFalse(AnalyticsSettings::isValid('facebook_pixel', '12345678901234a'));
        $this->assertFalse(AnalyticsSettings::isValid('microsoft_clarity', 'abc'));
        $this->assertFalse(AnalyticsSettings::isValid('microsoft_clarity', 'abcd-1234'));
    }

    public function test_spaces_and_letter_case_are_tidied(): void
    {
        $this->assertSame('G-ABC123DEF4', AnalyticsSettings::normalise('google_analytics', '  g-abc123def4 '));
        $this->assertSame('GTM-ABC1234', AnalyticsSettings::normalise('google_tag_manager', 'gtm-abc1234'));
        $this->assertSame('abcd1234ef', AnalyticsSettings::normalise('microsoft_clarity', 'ABCD1234EF'));
        $this->assertSame('123456789012345', AnalyticsSettings::normalise('facebook_pixel', '123 456 789 012 345'));

        $this->saveAnalytics(['google_analytics' => ' g-abc123def4 '])->assertOk();
        $this->assertSame('G-ABC123DEF4', WebsiteCms::forOrganization($this->school->id)->fresh()->draft['analytics']['google_analytics']);
    }

    public function test_an_invalid_id_is_refused_with_an_example(): void
    {
        $this->saveAnalytics(['google_analytics' => 'UA-12345678-1'])
            ->assertStatus(422)
            ->assertJsonPath('errors', ['analytics.google_analytics' => ['It should look like G-ABC123DEF4.']]);
        $this->saveAnalytics(['facebook_pixel' => '12345'])->assertStatus(422)->assertJsonValidationErrors(['analytics.facebook_pixel']);
        $this->saveAnalytics(self::ALL)->assertOk();
    }

    /* ── consent ── */

    public function test_asking_before_tracking_is_on_by_default(): void
    {
        $analytics = app(WebsiteCmsService::class)->merge(['analytics' => ['google_analytics' => 'G-ABC123DEF4']])['analytics'];

        $this->assertTrue($analytics['require_consent']);
        $this->assertTrue(AnalyticsSettings::requiresConsent([]));
    }

    public function test_with_consent_on_nothing_loads_until_the_visitor_accepts(): void
    {
        $settings = self::ALL + ['require_consent' => true];
        $all = array_keys(self::ALL);

        $this->assertSame([], AnalyticsSettings::toolsToLoad($settings, null));
        $this->assertSame([], AnalyticsSettings::toolsToLoad($settings, 'rejected'));
        $this->assertSame($all, AnalyticsSettings::toolsToLoad($settings, 'accepted'));
        $this->assertTrue(AnalyticsSettings::showsBanner($settings));
    }

    public function test_with_consent_off_connected_tools_load_straight_away(): void
    {
        $settings = ['google_analytics' => 'G-ABC123DEF4', 'microsoft_clarity' => 'abcd1234ef', 'require_consent' => false];

        $this->assertSame(['google_analytics', 'microsoft_clarity'], AnalyticsSettings::toolsToLoad($settings, null));
        $this->assertFalse(AnalyticsSettings::showsBanner($settings));
    }

    public function test_invalid_or_empty_ids_never_load_and_need_no_banner(): void
    {
        $settings = ['google_analytics' => 'UA-12345678-1', 'facebook_pixel' => '', 'require_consent' => true];

        $this->assertSame([], AnalyticsSettings::connectedTools($settings));
        $this->assertSame([], AnalyticsSettings::toolsToLoad($settings, 'accepted'));
        $this->assertFalse(AnalyticsSettings::showsBanner($settings), 'No tools: nothing to ask about.');
    }

    public function test_the_api_serves_the_consent_setting(): void
    {
        $cms = WebsiteCms::forOrganization($this->school->id);
        $token = $cms->rotateApiToken();
        $this->saveAnalytics(['google_analytics' => 'G-ABC123DEF4', 'require_consent' => true])->assertOk();
        $this->actingAs($this->admin, 'admin')->postJson(route('admin.website-cms.publish'))->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/headless/v1/site')
            ->assertOk()
            ->assertJsonPath('settings.analytics.google_analytics', 'G-ABC123DEF4')
            ->assertJsonPath('settings.analytics.require_consent', true);
    }
}
