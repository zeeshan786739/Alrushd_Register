<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Services\HeadlessWebsiteService;
use App\Support\PreviewToken;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Website CMS live preview: the admin-bound preview token and the origin the CMS trusts. */
class WebsitePreviewTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private WebsiteCms $cms;

    private Admin $admin;

    private string $apiToken;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Preview school', 'slug' => 'preview-school', 'is_active' => true]);
        $this->cms = WebsiteCms::forOrganization($this->school->id);
        $this->cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.preview-school.test/'])->save();
        $this->apiToken = $this->cms->rotateApiToken();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);
        $this->admin = $this->makeAdmin('editor@preview-school.test', $this->school, $role);
        PublicOrganizationContext::clear();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        PublicOrganizationContext::clear();
        parent::tearDown();
    }

    private function makeAdmin(string $email, Organization $org, ?Role $role): Admin
    {
        $admin = Admin::create(['name' => 'A', 'email' => $email, 'password' => bcrypt('password'), 'organization_id' => $org->id]);
        if ($role) {
            $admin->assignRole($role);
        }

        return $admin;
    }

    private function verifyViaApi(string $token)
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->apiToken)
            ->getJson('/api/headless/v1/preview/verify?token='.urlencode($token));
    }

    /* ── token ── */

    public function test_a_fresh_token_opens_its_page(): void
    {
        $token = PreviewToken::issue($this->cms, $this->admin, '/fees');

        $this->assertSame(['ok' => true, 'path' => '/fees', 'admin_id' => $this->admin->id], PreviewToken::verify($token, $this->cms));
        $this->verifyViaApi($token)->assertOk()->assertJsonPath('ok', true)->assertJsonPath('path', '/fees');
    }

    public function test_an_expired_token_is_refused(): void
    {
        $token = PreviewToken::issue($this->cms, $this->admin, '/');

        Carbon::setTestNow(now()->addMinutes(PreviewToken::TTL_MINUTES + 1));
        $this->assertSame(['ok' => false, 'reason' => 'expired'], PreviewToken::verify($token, $this->cms));
        $this->verifyViaApi($token)->assertStatus(401)->assertJsonPath('reason', 'expired');
    }

    public function test_a_token_for_the_wrong_user_is_refused(): void
    {
        // An admin of another school.
        $other = Organization::create(['name' => 'Other', 'slug' => 'other-school', 'is_active' => true]);
        $outsider = $this->makeAdmin('x@other-school.test', $other, Role::findByName('website-editor', 'admin'));
        $this->assertSame('admin', PreviewToken::verify(PreviewToken::issue($this->cms, $outsider, '/'), $this->cms)['reason']);

        // An admin here without permission to view website settings.
        $noAccess = $this->makeAdmin('nope@preview-school.test', $this->school, null);
        $this->assertSame('admin', PreviewToken::verify(PreviewToken::issue($this->cms, $noAccess, '/'), $this->cms)['reason']);

        // An admin who has since been removed.
        $gone = $this->makeAdmin('gone@preview-school.test', $this->school, Role::findByName('website-editor', 'admin'));
        $token = PreviewToken::issue($this->cms, $gone, '/');
        $gone->delete();
        $this->assertSame('admin', PreviewToken::verify($token, $this->cms)['reason']);
    }

    public function test_a_token_for_another_schools_website_is_refused(): void
    {
        $other = Organization::create(['name' => 'Other', 'slug' => 'other-school', 'is_active' => true]);
        $otherCms = WebsiteCms::forOrganization($other->id);

        $token = PreviewToken::issue($this->cms, $this->admin, '/');
        $this->assertSame('organization', PreviewToken::verify($token, $otherCms)['reason']);
    }

    public function test_tampered_or_malformed_tokens_are_refused(): void
    {
        $token = PreviewToken::issue($this->cms, $this->admin, '/');
        [$payload, $signature] = explode('.', $token);
        $forged = rtrim(strtr(base64_encode(json_encode(['o' => $this->school->id, 'a' => $this->admin->id, 'p' => '/', 'e' => now()->addYear()->timestamp])), '+/', '-_'), '=');

        $this->assertSame('signature', PreviewToken::verify($forged.'.'.$signature, $this->cms)['reason']);
        $this->assertSame('malformed', PreviewToken::verify('nonsense', $this->cms)['reason']);
        $this->assertSame('malformed', PreviewToken::verify('', $this->cms)['reason']);
        $this->verifyViaApi('nonsense')->assertStatus(401);
        // Without the website's API token the check isn't reachable at all.
        $this->flushHeaders()->getJson('/api/headless/v1/preview/verify?token='.urlencode($token))->assertUnauthorized();
    }

    public function test_only_same_site_paths_are_opened(): void
    {
        // A protocol-relative link becomes a path on the school's own site, never another site.
        $this->assertSame('/evil.example/x', PreviewToken::cleanPath('//evil.example/x'));
        $this->assertSame('/', PreviewToken::cleanPath('https://evil.example'));
        $this->assertSame('/fees', PreviewToken::cleanPath('fees'));
        $this->assertSame('/blog-news/my-post', PreviewToken::cleanPath('/blog-news/my-post'));
    }

    public function test_the_cms_gets_draft_and_live_links_for_the_signed_in_admin(): void
    {
        $draft = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.website-cms.connection.preview-url', ['path' => '/fees']))
            ->assertOk()
            ->assertJsonPath('origin', 'https://www.preview-school.test')
            ->json('url');
        $this->assertStringStartsWith('https://www.preview-school.test/api/preview?cms_preview=', $draft);
        parse_str((string) parse_url($draft, PHP_URL_QUERY), $query);
        $this->assertSame(['ok' => true, 'path' => '/fees', 'admin_id' => $this->admin->id], PreviewToken::verify($query['cms_preview'], $this->cms));

        $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.website-cms.connection.preview-url', ['path' => '/fees', 'mode' => 'live']))
            ->assertOk()
            ->assertJsonPath('url', 'https://www.preview-school.test/api/preview/exit?path=%2Ffees&frame=1');
    }

    /* ── origins ── */

    public function test_the_trusted_origin_is_exactly_what_browsers_report(): void
    {
        $this->assertSame('https://www.preview-school.test', HeadlessWebsiteService::origin('https://www.preview-school.test/'));
        $this->assertSame('https://school.test', HeadlessWebsiteService::origin('HTTPS://School.TEST:443/path?x=1'));
        $this->assertSame('http://localhost:3000', HeadlessWebsiteService::origin('http://localhost:3000'));
        $this->assertSame('http://localhost', HeadlessWebsiteService::origin('http://localhost:80'));
        $this->assertNull(HeadlessWebsiteService::origin('javascript:alert(1)'));
        $this->assertNull(HeadlessWebsiteService::origin(''));
    }

    /* ── connection test, both directions ── */

    /** Replace setUp's catch-all fake with this website answer, then run "Test connection". */
    private function testConnectionWith($websiteAnswer)
    {
        app()->forgetInstance(\Illuminate\Http\Client\Factory::class);
        Http::clearResolvedInstances();
        Http::fake(['www.preview-school.test/*' => $websiteAnswer]);

        return $this->actingAs($this->admin, 'admin')->postJson(route('admin.website-cms.connection.test'))->assertOk();
    }

    /** The CMS page's own origin in tests (APP_URL). */
    private function crmOrigin(): string
    {
        return HeadlessWebsiteService::origin(url('/'));
    }

    private function report(array $overrides = []): array
    {
        return array_replace_recursive([
            'ok' => true, 'app' => 'alrushd-website',
            'crm' => ['url' => $this->crmOrigin().'/api/headless/v1', 'status' => 'ok', 'organization' => 'preview-school'],
            'frame_origins' => [$this->crmOrigin()], 'cms_origin_allowed' => true,
        ], $overrides);
    }

    public function test_connection_ok_both_ways(): void
    {
        $this->testConnectionWith(Http::response($this->report()))
            ->assertJsonPath('success', true)->assertJsonPath('code', 'ok');
        Http::assertSent(fn ($r) => $r->url() === 'https://www.preview-school.test/api/revalidate' && $r->header('X-CMS-Origin') === [$this->crmOrigin()]);
    }

    public function test_another_app_on_the_websites_port_is_named(): void
    {
        $this->testConnectionWith(Http::response('<html>Not found</html>', 404))
            ->assertJsonPath('success', false)->assertJsonPath('code', 'wrong_site');
    }

    public function test_a_website_pointing_at_the_wrong_crm_address_is_told_the_right_one(): void
    {
        $res = $this->testConnectionWith(Http::response($this->report(['crm' => ['url' => 'http://127.0.0.1:8010/api/headless/v1', 'status' => 'unreachable']])))
            ->assertJsonPath('success', false)->assertJsonPath('code', 'crm_unreachable');
        $this->assertStringContainsString('CRM_API_URL='.$this->crmOrigin().'/api/headless/v1', $res->json('message'));
    }

    public function test_a_busy_single_worker_crm_is_not_reported_as_a_wrong_address(): void
    {
        // The website points at this very CRM but couldn't call back during the test.
        $this->testConnectionWith(Http::response($this->report(['crm' => ['status' => 'unreachable']])))
            ->assertJsonPath('success', true);
    }

    public function test_a_website_that_refuses_the_cms_frame_says_which_origin_to_add(): void
    {
        $res = $this->testConnectionWith(Http::response($this->report(['cms_origin_allowed' => false])))
            ->assertJsonPath('success', false)->assertJsonPath('code', 'frame_origin');
        $this->assertStringContainsString('CRM_ORIGIN', $res->json('message'));
        $this->assertStringContainsString($this->crmOrigin(), $res->json('message'));
    }

    public function test_older_websites_still_pass_the_test(): void
    {
        $this->testConnectionWith(Http::response(['ok' => true]))->assertJsonPath('success', true);
        $this->testConnectionWith(Http::response(['error' => 'Invalid token.'], 401))->assertJsonPath('code', 'token');
    }

    public function test_the_cms_page_hands_the_panel_only_the_connected_origin(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('website-cms-preview.js', false)
            ->assertSee('"origin":"https:\/\/www.preview-school.test"', false);
    }
}
