<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Website CMS → Navigation menu. The editor works on a tree but stores the flat list the
 * website reads ({label, href, group, description}; a group = the dropdown it sits in).
 * These tests send the flat lists the editor produces and check what is stored and served.
 */
class WebsiteNavMenuTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Menu school', 'slug' => 'menu-school', 'is_active' => true]);
        $cms = WebsiteCms::forOrganization($this->school->id);
        $cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.menu-school.test'])->save();
        $this->token = $cms->rotateApiToken();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);

        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@menu-school.test',
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

    private static function item(string $label, string $href, string $group = '', string $description = ''): array
    {
        return compact('label', 'href', 'group', 'description');
    }

    private function saveMenu(array $items, array $extra = []): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => ['nav_menu' => array_merge(['items' => $items], $extra)]])
            ->assertOk();
    }

    private function draftMenu(): array
    {
        return WebsiteCms::forOrganization($this->school->id)->fresh()->draft['nav_menu'];
    }

    private function menu(): array
    {
        return [
            self::item('About Us', '/about-us', 'About', 'Our story'),
            self::item('Why Al-Rushd?', '/why-al-rushd', 'About'),
            self::item('Fees', '/fees'),
            self::item('Contact', '/contact'),
        ];
    }

    public function test_saving_stores_items_in_the_given_order(): void
    {
        $this->saveMenu($this->menu(), ['cta_text' => 'Apply Now', 'cta_url' => '/apply-now']);

        $menu = $this->draftMenu();
        $this->assertSame($this->menu(), $menu['items']);
        $this->assertSame('Apply Now', $menu['cta_text']);
        $this->assertSame('/apply-now', $menu['cta_url']);
    }

    public function test_reordering_is_kept(): void
    {
        $this->saveMenu($this->menu());

        // Contact moved above Fees; About Us moved below Why Al-Rushd? inside the dropdown.
        $reordered = [
            self::item('Why Al-Rushd?', '/why-al-rushd', 'About'),
            self::item('About Us', '/about-us', 'About', 'Our story'),
            self::item('Contact', '/contact'),
            self::item('Fees', '/fees'),
        ];
        $this->saveMenu($reordered);

        $this->assertSame($reordered, $this->draftMenu()['items']);
    }

    public function test_moving_a_link_into_a_dropdown_is_served_to_the_website(): void
    {
        $this->saveMenu($this->menu());

        // Fees moved into the About dropdown (written right after the dropdown's other links).
        $moved = [
            self::item('About Us', '/about-us', 'About', 'Our story'),
            self::item('Why Al-Rushd?', '/why-al-rushd', 'About'),
            self::item('Fees', '/fees', 'About', 'What it costs'),
            self::item('Contact', '/contact'),
        ];
        $this->saveMenu($moved);
        $this->actingAs($this->admin, 'admin')->postJson(route('admin.website-cms.publish'))->assertOk();

        $served = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/headless/v1/site')
            ->assertOk()
            ->json('settings.nav_menu.items');

        $this->assertSame($moved, $served);
    }

    public function test_removing_items_removes_them_even_down_to_an_empty_menu(): void
    {
        $this->saveMenu($this->menu());

        $this->saveMenu([self::item('Fees', '/fees')]);
        $this->assertSame([self::item('Fees', '/fees')], $this->draftMenu()['items']);

        // Before: list items were merged index-by-index with the defaults, so an empty
        // menu came back with the default "Home" link.
        $this->saveMenu([]);
        $this->assertSame([], $this->draftMenu()['items']);
    }

    public function test_an_empty_dropdown_is_kept_as_a_placeholder_row(): void
    {
        $items = [self::item('', '', 'Admissions'), self::item('Fees', '/fees')];
        $this->saveMenu($items);

        $this->assertSame($items, $this->draftMenu()['items']);
    }

    public function test_items_are_normalised_to_the_shape_the_website_reads(): void
    {
        $this->saveMenu([
            ['label' => '  Fees ', 'href' => ' /fees ', 'group' => '', 'description' => '', 'extra' => 'dropped'],
            ['label' => 'No group key', 'href' => '/x'],
            'not-an-item',
            ['label' => '', 'href' => '', 'group' => '', 'description' => ''],
        ]);

        $this->assertSame([
            self::item('Fees', '/fees'),
            self::item('No group key', '/x'),
        ], $this->draftMenu()['items']);
    }
}
