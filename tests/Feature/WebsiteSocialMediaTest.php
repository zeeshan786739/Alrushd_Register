<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Services\WebsiteCmsService;
use App\Support\PublicOrganizationContext;
use App\Support\SocialProfiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Website CMS → Social media: link checks, when an icon may show, and the typo detector. */
class WebsiteSocialMediaTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Social school', 'slug' => 'social-school', 'is_active' => true]);
        WebsiteCms::forOrganization($this->school->id)
            ->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.social-school.test'])->save();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);
        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@social-school.test',
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

    private function saveSocial(array $social)
    {
        return $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => ['social' => $social]]);
    }

    /* ── domain validation ── */

    public function test_links_must_belong_to_their_network(): void
    {
        $this->assertNull(SocialProfiles::linkProblem('facebook', 'https://www.facebook.com/alrushd/'));
        $this->assertNull(SocialProfiles::linkProblem('facebook', 'https://m.facebook.com/alrushd'));
        $this->assertNull(SocialProfiles::linkProblem('twitter', 'https://x.com/alrushd'));
        $this->assertNull(SocialProfiles::linkProblem('twitter', 'https://twitter.com/alrushd'));
        $this->assertNull(SocialProfiles::linkProblem('youtube', 'https://youtu.be/abc123'));
        $this->assertNull(SocialProfiles::linkProblem('whatsapp', 'https://wa.me/442036330757'));
        $this->assertNull(SocialProfiles::linkProblem('whatsapp', 'https://chat.whatsapp.com/AbC'));
        $this->assertNull(SocialProfiles::linkProblem('threads', 'https://www.threads.net/@alrushd'));
        $this->assertNull(SocialProfiles::linkProblem('instagram', ''));

        $this->assertSame('domain', SocialProfiles::linkProblem('instagram', 'https://www.facebook.com/alrushd'));
        $this->assertSame('domain', SocialProfiles::linkProblem('facebook', 'https://facebook.com.evil.example/alrushd'));
        $this->assertSame('domain', SocialProfiles::linkProblem('linkedin', 'https://notlinkedin.com/x'));
        $this->assertSame('format', SocialProfiles::linkProblem('tiktok', 'tiktok.com/@alrushd'));
        $this->assertSame('format', SocialProfiles::linkProblem('tiktok', 'javascript:alert(1)'));
    }

    public function test_saving_rejects_a_wrong_domain_with_a_clear_message(): void
    {
        $this->saveSocial(['instagram' => 'https://www.facebook.com/alrushd'])
            ->assertStatus(422)
            ->assertJsonPath('errors', ['social.instagram' => ["This doesn't look like an Instagram link."]]);

        $this->saveSocial(['twitter' => 'https://x.com/alrushd'])->assertOk();
    }

    /* ── when an icon shows ── */

    public function test_an_icon_shows_only_when_switched_on_with_a_valid_link(): void
    {
        $this->assertTrue(SocialProfiles::isShown('facebook', 'https://www.facebook.com/alrushd', true));
        $this->assertFalse(SocialProfiles::isShown('facebook', 'https://www.facebook.com/alrushd', false));
        // Switched on but empty: nothing to link to, so no icon.
        $this->assertFalse(SocialProfiles::isShown('facebook', '', true));
        $this->assertFalse(SocialProfiles::isShown('facebook', '   ', true));
        $this->assertFalse(SocialProfiles::isShown('facebook', 'https://www.instagram.com/alrushd', true));
    }

    /* ── typo detector ── */

    public function test_a_username_close_to_the_schools_usual_one_is_flagged(): void
    {
        $warnings = SocialProfiles::typoWarnings([
            'facebook' => 'https://www.facebook.com/alrushd.independentschool/',
            'instagram' => 'https://www.instagram.com/al_rushd_academy/',
            'linkedin' => 'https://www.linkedin.com/company/alrushdschool/',
            'twitter' => 'https://twitter.com/alrsuhad',
            'youtube' => 'https://www.youtube.com/channel/UCY2LpEsW1zJm2mDC8aRLtGg',
            'tiktok' => 'https://www.tiktok.com/@alrushd',
        ]);

        $this->assertSame(['twitter' => ['handle' => 'alrsuhad', 'suggestion' => 'alrushd']], $warnings);
    }

    public function test_consistent_or_unrelated_usernames_are_not_flagged(): void
    {
        $this->assertSame([], SocialProfiles::typoWarnings([
            'facebook' => 'https://facebook.com/alrsuhad',
            'instagram' => 'https://instagram.com/alrsuhad',
            'twitter' => 'https://twitter.com/alrsuhad',
        ]));
        $this->assertSame([], SocialProfiles::typoWarnings([
            'facebook' => 'https://facebook.com/greenfieldschool',
            'tiktok' => 'https://www.tiktok.com/@alrushd',
        ]));
    }

    public function test_edit_distance_counts_a_swapped_pair_as_one_edit(): void
    {
        $this->assertSame(1, SocialProfiles::editDistance('alrsuhd', 'alrushd'));
        $this->assertSame(2, SocialProfiles::editDistance('alrsuhad', 'alrushd'));
        $this->assertSame(0, SocialProfiles::editDistance('alrushd', 'alrushd'));
    }

    /* ── WhatsApp ── */

    public function test_whatsapp_uses_contact_details_by_default(): void
    {
        $merged = app(WebsiteCmsService::class)->merge(['social' => ['facebook' => 'https://facebook.com/x']]);
        $this->assertTrue($merged['social']['whatsapp_use_contact']);

        // While it comes from Contact details, the social WhatsApp field isn't checked.
        $this->saveSocial(['whatsapp_use_contact' => true, 'whatsapp' => 'not a link'])->assertOk();
        $this->saveSocial(['whatsapp_use_contact' => false, 'whatsapp' => 'https://example.com/chat'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['social.whatsapp']);
        $this->saveSocial(['whatsapp_use_contact' => false, 'whatsapp' => 'https://wa.me/447700900123'])->assertOk();
    }
}
