<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Services\WebsiteCmsService;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Website CMS → Branding: names, logo / browser-icon uploads and the copyright year. */
class WebsiteBrandingTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);
        Storage::fake('public');

        $this->school = Organization::create(['name' => 'Brand school', 'slug' => 'brand-school', 'is_active' => true]);
        WebsiteCms::forOrganization($this->school->id)
            ->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.brand-school.test'])->save();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);
        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@brand-school.test',
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

    private function upload(string $kind, UploadedFile $file)
    {
        return $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.branding-image'), ['kind' => $kind, 'file' => $file]);
    }

    private function saveBranding(array $branding)
    {
        return $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => ['branding' => $branding]]);
    }

    private function svg(string $body): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10">'.$body.'</svg>');
    }

    /* ── browser icon ── */

    public function test_browser_icon_is_cropped_square_and_saved_in_every_size(): void
    {
        $res = $this->upload('favicon', UploadedFile::fake()->image('icon.png', 700, 512))
            ->assertOk()
            ->assertJsonPath('cropped', true);

        foreach (['32', '180', '192', '512'] as $size) {
            $url = $res->json("icons.{$size}");
            $path = ltrim(str_replace(Storage::disk('public')->url(''), '', $url), '/');
            Storage::disk('public')->assertExists($path);
            [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($path));
            $this->assertSame([(int) $size, (int) $size], [$w, $h], "{$size}px icon");
        }
        $this->assertSame($res->json('icons.512'), $res->json('url'));
    }

    public function test_browser_icon_must_be_at_least_512_pixels(): void
    {
        $this->upload('favicon', UploadedFile::fake()->image('small.png', 300, 300))
            ->assertStatus(422)
            ->assertJsonPath('errors.file.0', 'The browser icon must be at least 512 × 512 pixels (this one is 300 × 300).');
    }

    /* ── logo ── */

    public function test_logo_accepts_png_and_plain_svg(): void
    {
        $this->upload('logo', UploadedFile::fake()->image('logo.png', 400, 400))->assertOk()->assertJsonStructure(['url']);
        $this->upload('logo', $this->svg('<circle cx="5" cy="5" r="4" fill="#143256"/>'))->assertOk();
    }

    public function test_svgs_with_code_or_links_and_other_types_are_refused(): void
    {
        foreach (['<script>alert(1)</script>', '<circle onload="alert(1)" r="4"/>', '<a href="javascript:alert(1)"><circle r="4"/></a>', '<image href="https://evil.example/x.png"/>'] as $bad) {
            $this->upload('logo', $this->svg($bad))->assertStatus(422)->assertJsonValidationErrors(['file']);
        }
        $this->upload('logo', UploadedFile::fake()->image('logo.gif', 100, 100))->assertStatus(422)->assertJsonValidationErrors(['file']);
        $this->upload('logo', UploadedFile::fake()->create('logo.png', 3000, 'image/png'))->assertStatus(422);
    }

    /* ── names and texts ── */

    public function test_names_are_required_and_the_short_name_fits_next_to_the_logo(): void
    {
        $this->saveBranding(['company_name' => '', 'short_name' => 'Al-Rushd'])->assertStatus(422)->assertJsonValidationErrors(['branding.company_name']);
        $this->saveBranding(['company_name' => 'Al-Rushd Online School', 'short_name' => ''])->assertStatus(422)->assertJsonValidationErrors(['branding.short_name']);
        $this->saveBranding(['short_name' => str_repeat('x', 31)])->assertStatus(422)->assertJsonValidationErrors(['branding.short_name']);
        $this->saveBranding(['company_name' => 'Al-Rushd Online School', 'short_name' => 'Al-Rushd', 'tagline' => 'A British Education. Rooted in Faith.'])->assertOk();
    }

    /* ── defaults ── */

    public function test_copyright_year_is_automatic_by_default_and_the_website_keeps_its_own_images(): void
    {
        $branding = app(WebsiteCmsService::class)->merge(['branding' => ['company_name' => 'X', 'logo' => 'https://crm.example/logo.png']])['branding'];

        $this->assertTrue($branding['copyright_auto_year']);
        // The built-in landing page's logo is separate from the connected website's.
        $this->assertSame('', $branding['website_logo']);
        $this->assertSame('', $branding['website_favicon']);
        $this->assertSame([], $branding['website_favicon_icons']);
    }

    public function test_removing_the_browser_icon_drops_its_sizes(): void
    {
        $this->saveBranding([
            'website_favicon' => 'https://crm.example/f-512.png',
            'website_favicon_icons' => ['32' => 'https://crm.example/f-32.png', '512' => 'https://crm.example/f-512.png'],
        ])->assertOk();
        $this->saveBranding(['website_favicon' => '', 'website_favicon_icons' => ['32' => 'https://crm.example/f-32.png']])->assertOk();

        $this->assertSame([], WebsiteCms::forOrganization($this->school->id)->fresh()->draft['branding']['website_favicon_icons']);
    }
}
