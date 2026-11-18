<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Support\PublicOrganizationContext;
use App\Support\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Website CMS → SEO: the choice → stored-value mapping, address rules and the Quick check. */
class WebsiteSeoTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $admin;

    private WebsiteCms $cms;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Seo school', 'slug' => 'seo-school', 'is_active' => true, 'website' => 'https://seoschool.co.uk']);
        $this->cms = WebsiteCms::forOrganization($this->school->id);
        $this->cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'http://localhost:3000'])->save();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);
        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@seo-school.test',
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

    private function saveSeo(array $seo)
    {
        return $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => ['seo' => $seo]]);
    }

    private function draftSeo(): array
    {
        return WebsiteCms::forOrganization($this->school->id)->fresh()->draft['seo'];
    }

    private function good(array $overrides = []): array
    {
        return array_merge([
            'meta_title' => 'Al-Rushd Online School | British Islamic School',
            'meta_description' => str_repeat('Live online lessons from Year 1 to A-Level. ', 3),
            'og_image' => 'https://cdn.seoschool.co.uk/share.jpg',
            'canonical_url' => 'https://seoschool.co.uk',
            'robots' => 'index, follow',
            'google_verification' => 'AbCdEf0123456789xyz',
        ], $overrides);
    }

    /* ── mapping ── */

    public function test_choices_are_stored_in_the_formats_the_website_reads(): void
    {
        $this->assertSame('index, follow', SeoSettings::robotsFor(true));
        $this->assertSame('noindex, nofollow', SeoSettings::robotsFor(false));
        $this->assertTrue(SeoSettings::isHidden('noindex, nofollow'));
        $this->assertTrue(SeoSettings::isHidden('NOINDEX'));
        $this->assertFalse(SeoSettings::isHidden('index, follow'));

        $this->saveSeo(['twitter_card' => 'summary', 'robots' => 'noindex, nofollow'])->assertOk();
        $this->assertSame('summary', $this->draftSeo()['twitter_card']);
        $this->assertSame('noindex, nofollow', $this->draftSeo()['robots']);

        $this->saveSeo(['twitter_card' => 'huge'])->assertStatus(422)->assertJsonValidationErrors(['seo.twitter_card']);
    }

    public function test_verification_accepts_the_code_or_the_whole_meta_tag(): void
    {
        $tag = '<meta name="google-site-verification" content="Xy_z-1234567890abcdef" />';
        $this->assertSame('Xy_z-1234567890abcdef', SeoSettings::verificationCode($tag));
        $this->assertSame('Xy_z-1234567890abcdef', SeoSettings::verificationCode('  Xy_z-1234567890abcdef '));

        $this->saveSeo(['google_verification' => $tag])->assertOk();
        $this->assertSame('Xy_z-1234567890abcdef', $this->draftSeo()['google_verification']);

        $this->saveSeo(['google_verification' => 'abc'])->assertStatus(422)->assertJsonValidationErrors(['seo.google_verification']);
    }

    /* ── website address ── */

    public function test_website_address_must_be_a_real_https_address(): void
    {
        $this->assertNull(SeoSettings::canonicalProblem('https://www.seoschool.co.uk'));
        $this->assertSame('test', SeoSettings::canonicalProblem('http://localhost:3000'));
        $this->assertSame('test', SeoSettings::canonicalProblem('https://127.0.0.1:8000'));
        $this->assertSame('test', SeoSettings::canonicalProblem('https://192.168.1.20'));
        $this->assertSame('test', SeoSettings::canonicalProblem('https://school.test'));
        $this->assertSame('not_https', SeoSettings::canonicalProblem('http://www.seoschool.co.uk'));
        $this->assertSame('empty', SeoSettings::canonicalProblem(''));
        $this->assertSame('format', SeoSettings::canonicalProblem('seoschool.co.uk'));

        // A test address may sit in a draft (publishing asks first), but it must be a link.
        $this->saveSeo(['canonical_url' => 'http://localhost:3000'])->assertOk();
        $this->saveSeo(['canonical_url' => 'not a link'])->assertStatus(422)->assertJsonValidationErrors(['seo.canonical_url']);
    }

    public function test_the_one_click_fix_suggests_the_schools_real_address(): void
    {
        // The connected website is still on localhost, so the school's own website is offered.
        $this->assertSame('https://seoschool.co.uk', SeoSettings::suggestedAddress($this->cms, $this->school));

        $this->cms->external_url = 'https://www.seoschool.co.uk/';
        $this->assertSame('https://www.seoschool.co.uk', SeoSettings::suggestedAddress($this->cms, $this->school));

        $this->cms->external_url = 'http://localhost:3000';
        $this->school->website = null;
        $this->assertNull(SeoSettings::suggestedAddress($this->cms, $this->school));
    }

    /* ── quick check ── */

    public function test_quick_check_is_all_good_for_a_complete_setup(): void
    {
        $checks = SeoSettings::checks($this->good());

        $this->assertSame(0, SeoSettings::issueCount($checks));
        $this->assertSame(['robots'], array_column($checks, 'key'));
        $this->assertSame('ok', $checks[0]['level']);
    }

    public function test_quick_check_finds_each_problem_with_its_fix(): void
    {
        $checks = collect(SeoSettings::checks($this->good([
            'canonical_url' => 'http://localhost:3000',
            'robots' => 'noindex, nofollow',
            'og_image' => '',
            'meta_title' => str_repeat('T', 61),
            'meta_description' => str_repeat('D', 163),
            'google_verification' => '',
        ]), 'https://seoschool.co.uk'))->keyBy('key');

        $this->assertSame('error', $checks['canonical']['level']);
        $this->assertSame('Your website address is set to a test address', $checks['canonical']['title']);
        $this->assertSame('https://seoschool.co.uk', $checks['canonical']['fix']);
        $this->assertSame('error', $checks['robots']['level']);
        $this->assertSame('Your whole website is hidden from Google', $checks['robots']['title']);
        $this->assertSame('warning', $checks['image']['level']);
        $this->assertSame('Title 61 characters (aim ≤ 60) · description 163 characters (aim 70–160).', $checks['length']['text']);
        $this->assertSame('info', $checks['console']['level']);

        // Search Console is information, not a problem: 4 things to fix.
        $this->assertSame(4, SeoSettings::issueCount($checks->values()->all()));
    }

    public function test_a_website_with_its_own_default_image_is_not_warned(): void
    {
        $checks = collect(SeoSettings::checks($this->good(['og_image' => '']), null, hasDefaultImage: true))->keyBy('key');

        $this->assertSame('info', $checks['image']['level']);
        $this->assertSame(0, SeoSettings::issueCount($checks->values()->all()));
    }

    public function test_short_descriptions_are_flagged_too(): void
    {
        $checks = collect(SeoSettings::checks($this->good(['meta_description' => 'Too short.'])))->keyBy('key');
        $this->assertSame('Description 10 characters (aim 70–160).', $checks['length']['text']);
    }
}
