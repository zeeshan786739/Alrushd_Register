<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Services\WebsiteCmsService;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Website CMS → Footer. The editor shows two heading inputs and column cards, but stores what
 * the website reads: heading "first|second" and flat links { group (column), label, href }.
 */
class WebsiteFooterTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Footer school', 'slug' => 'footer-school', 'is_active' => true]);
        $cms = WebsiteCms::forOrganization($this->school->id);
        $cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.footer-school.test'])->save();
        $this->token = $cms->rotateApiToken();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);

        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@footer-school.test',
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

    private function saveFooter(array $footer): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => ['footer_menu' => $footer]])
            ->assertOk();
    }

    private function draftFooter(): array
    {
        return WebsiteCms::forOrganization($this->school->id)->fresh()->draft['footer_menu'];
    }

    private static function link(string $group, string $label, string $href): array
    {
        return compact('group', 'label', 'href');
    }

    public function test_cms_page_loads_the_footer_editor_with_the_published_footer(): void
    {
        $this->saveFooter(['heading' => 'Live|Footer', 'items' => [self::link('School', 'About Us', '/about-us')]]);
        $this->actingAs($this->admin, 'admin')->postJson(route('admin.website-cms.publish'))->assertOk();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('website-cms-footer.js', false)
            ->assertSee('website-cms-nav.js', false)
            ->assertSee('website-cms-pages.js', false)
            ->assertSee('publishedFooterMenu', false)
            ->assertSee('Live|Footer', false);
    }

    public function test_two_heading_lines_are_joined_with_a_bar(): void
    {
        $this->saveFooter(['heading' => ' Give Your Child the | Best of Both Worlds ']);
        $this->assertSame('Give Your Child the|Best of Both Worlds', $this->draftFooter()['heading']);
    }

    public function test_heading_without_a_second_line_has_no_bar(): void
    {
        $this->saveFooter(['heading' => 'Learn with us|   ']);
        $this->assertSame('Learn with us', $this->draftFooter()['heading']);

        $this->saveFooter(['heading' => 'Learn with us']);
        $this->assertSame('Learn with us', $this->draftFooter()['heading']);
    }

    public function test_only_the_first_bar_splits_the_heading(): void
    {
        // The website splits on "|" and shows only two lines, so a stray bar must not create a third.
        $this->assertSame('One|Two Three', WebsiteCmsService::normaliseFooterHeading('One|Two|Three'));
    }

    public function test_column_grouping_and_order_are_kept_and_served(): void
    {
        $items = [
            self::link('Learning', 'How We Teach', '/how-we-teach'),
            self::link('Learning', 'Blog', '/blog'),
            self::link('School', 'Why Al-Rushd?', '/why-al-rushd'),
            self::link('School', 'About Us', '/about-us'),
            self::link('Admissions', 'Fees', '/fees'),
        ];
        $this->saveFooter(['heading' => 'A|B', 'description' => 'Intro.', 'items' => $items]);
        $this->assertSame($items, $this->draftFooter()['items']);

        $this->actingAs($this->admin, 'admin')->postJson(route('admin.website-cms.publish'))->assertOk();
        $served = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/headless/v1/site')
            ->assertOk()
            ->json('settings.footer_menu');

        $this->assertSame($items, $served['items']);
        $this->assertSame('A|B', $served['heading']);
        $this->assertSame('Intro.', $served['description']);
    }

    public function test_renaming_a_column_renames_all_its_links(): void
    {
        $this->saveFooter(['items' => [self::link('School', 'About Us', '/about-us'), self::link('School', 'Fees', '/fees')]]);

        // The editor writes every link of a renamed column with the new heading.
        $renamed = [self::link('Our school', 'About Us', '/about-us'), self::link('Our school', 'Fees', '/fees')];
        $this->saveFooter(['items' => $renamed]);

        $this->assertSame($renamed, $this->draftFooter()['items']);
    }

    public function test_empty_column_is_kept_and_removing_all_links_stays_empty(): void
    {
        $withEmpty = [self::link('School', 'About Us', '/about-us'), self::link('Policies', '', '')];
        $this->saveFooter(['items' => $withEmpty]);
        $this->assertSame($withEmpty, $this->draftFooter()['items']);

        $this->saveFooter(['items' => []]);
        $this->assertSame([], $this->draftFooter()['items']);
    }

    public function test_links_are_normalised_to_the_shape_the_website_reads(): void
    {
        $this->saveFooter(['items' => [
            ['group' => ' School ', 'label' => ' About Us ', 'href' => ' /about-us ', 'extra' => 'dropped'],
            'not-a-link',
            ['group' => '', 'label' => '', 'href' => ''],
        ]]);

        $this->assertSame([self::link('School', 'About Us', '/about-us')], $this->draftFooter()['items']);
    }
}
