<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\PlatformActivityLog;
use App\Models\WebsiteCms;
use App\Services\WebsiteCmsService;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Website CMS → Custom code: per-block on/off output, the code permission, limits and the log. */
class WebsiteCustomCodeTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $developer;

    private Admin $editor;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Code school', 'slug' => 'code-school', 'is_active' => true]);
        $cms = WebsiteCms::forOrganization($this->school->id);
        $cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.code-school.test'])->save();
        $this->token = $cms->rotateApiToken();

        foreach (['view setting', 'edit setting', 'edit website code'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $developerRole = Role::findOrCreate('website-developer', 'admin');
        $developerRole->givePermissionTo(['view setting', 'edit setting', 'edit website code']);
        $editorRole = Role::findOrCreate('website-editor', 'admin');
        $editorRole->givePermissionTo(['view setting', 'edit setting']);

        $this->developer = $this->admin('dev@code-school.test', $developerRole);
        $this->editor = $this->admin('editor@code-school.test', $editorRole);
        PublicOrganizationContext::clear();
    }

    protected function tearDown(): void
    {
        PublicOrganizationContext::clear();
        parent::tearDown();
    }

    private function admin(string $email, Role $role): Admin
    {
        $admin = Admin::create(['name' => ucfirst(strtok($email, '@')), 'email' => $email, 'password' => bcrypt('password'), 'organization_id' => $this->school->id]);
        $admin->assignRole($role);

        return $admin;
    }

    private function saveCode(Admin $as, array $code)
    {
        return $this->actingAs($as, 'admin')->postJson(route('admin.website-cms.draft'), ['data' => ['custom_code' => $code]]);
    }

    private function publishedSite(): array
    {
        $this->actingAs($this->developer, 'admin')->postJson(route('admin.website-cms.publish'))->assertOk();

        return $this->withHeader('Authorization', 'Bearer '.$this->token)->getJson('/api/headless/v1/site')->assertOk()->json('settings.custom_code');
    }

    /* ── on / off output ── */

    public function test_blocks_are_on_by_default(): void
    {
        $code = app(WebsiteCmsService::class)->merge(['custom_code' => ['custom_css' => 'body{}']])['custom_code'];

        $this->assertTrue($code['custom_css_enabled']);
        $this->assertTrue($code['head_code_enabled']);
        $this->assertTrue($code['footer_code_enabled']);
    }

    public function test_switched_on_blocks_are_sent_to_the_website(): void
    {
        $this->saveCode($this->developer, ['custom_css' => '.hero h1{letter-spacing:-.01em}', 'footer_scripts' => '<div id="chat"></div>'])->assertOk();

        $code = $this->publishedSite();
        $this->assertSame('.hero h1{letter-spacing:-.01em}', $code['custom_css']);
        $this->assertSame('<div id="chat"></div>', $code['footer_scripts']);
    }

    public function test_switched_off_blocks_are_kept_but_not_sent_to_the_website(): void
    {
        $this->saveCode($this->developer, [
            'custom_css' => '.hero h1{color:red}', 'custom_css_enabled' => false,
            'head_scripts' => '<meta name="x" content="y">', 'head_code_enabled' => true,
            'footer_scripts' => '<script>chat()</script>', 'footer_code_enabled' => false,
        ])->assertOk();

        $code = $this->publishedSite();
        $this->assertSame('', $code['custom_css']);
        $this->assertSame('<meta name="x" content="y">', $code['head_scripts']);
        $this->assertSame('', $code['footer_scripts']);
        $this->assertFalse($code['custom_css_enabled']);

        // Still in the CMS, ready to switch back on.
        $draft = WebsiteCms::forOrganization($this->school->id)->fresh()->draft['custom_code'];
        $this->assertSame('.hero h1{color:red}', $draft['custom_css']);
        $this->assertSame('<script>chat()</script>', $draft['footer_scripts']);
    }

    /* ── permission ── */

    public function test_only_people_with_the_code_permission_can_change_custom_code(): void
    {
        $this->saveCode($this->editor, ['footer_scripts' => '<script>track()</script>'])
            ->assertForbidden()
            ->assertJsonPath('message', 'Only people allowed to edit website code can change Custom code.');
        $this->saveCode($this->editor, ['custom_css_enabled' => false])->assertForbidden();

        $this->saveCode($this->developer, ['footer_scripts' => '<script>chat()</script>'])->assertOk();
    }

    public function test_people_without_it_can_still_save_other_sections(): void
    {
        $this->saveCode($this->developer, ['custom_css' => 'body{margin:0}'])->assertOk();

        // The editor's save carries the unchanged custom code along with their own change.
        $this->actingAs($this->editor, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => [
                'custom_code' => ['custom_css' => 'body{margin:0}', 'custom_css_enabled' => true],
                'branding' => ['tagline' => 'Learning without limits'],
            ]])
            ->assertOk();
    }

    public function test_migration_gives_the_permission_to_roles_that_edit_settings_only(): void
    {
        $settings = Role::findOrCreate('settings-manager', 'admin');
        $settings->givePermissionTo('edit setting');
        $viewer = Role::findOrCreate('viewer', 'admin');
        $viewer->givePermissionTo('view setting');

        (require database_path('migrations/2026_11_17_120000_add_edit_website_code_permission.php'))->up();

        $this->assertTrue($settings->fresh()->hasPermissionTo('edit website code'));
        $this->assertFalse($viewer->fresh()->hasPermissionTo('edit website code'));
    }

    public function test_the_screen_is_read_only_without_the_permission(): void
    {
        $this->actingAs($this->editor, 'admin')->get(route('admin.settings.index'))->assertOk()->assertSee('canEditCode: false', false);
        $this->actingAs($this->developer, 'admin')->get(route('admin.settings.index'))->assertOk()->assertSee('canEditCode: true', false);
    }

    /* ── limits ── */

    public function test_blocks_are_limited_to_50_kb_and_css_cannot_close_its_style_tag(): void
    {
        $this->saveCode($this->developer, ['footer_scripts' => str_repeat('a', 51201)])
            ->assertStatus(422)->assertJsonValidationErrors(['custom_code.footer_scripts']);
        $this->saveCode($this->developer, ['custom_css' => 'body{}</style><script>x()</script>'])
            ->assertStatus(422)->assertJsonValidationErrors(['custom_code.custom_css']);
        $this->saveCode($this->developer, ['custom_css' => str_repeat('a', 51200)])->assertOk();
    }

    /* ── log ── */

    public function test_publishing_changed_code_records_who_and_what(): void
    {
        $this->saveCode($this->developer, ['footer_scripts' => '<div id="chat"></div>'])->assertOk();
        $this->actingAs($this->developer, 'admin')->postJson(route('admin.website-cms.publish'))->assertOk();

        $log = PlatformActivityLog::where('action', 'website.custom_code.published')->sole();
        $this->assertSame($this->developer->id, $log->admin_id);
        $this->assertSame($this->school->id, $log->organization_id);
        $this->assertSame(['before' => '', 'after' => '<div id="chat"></div>'], $log->metadata['changes']['footer_scripts']);

        // Publishing again without code changes adds nothing.
        $this->actingAs($this->developer, 'admin')->postJson(route('admin.website-cms.publish'))->assertOk();
        $this->assertSame(1, PlatformActivityLog::where('action', 'website.custom_code.published')->count());
    }
}
