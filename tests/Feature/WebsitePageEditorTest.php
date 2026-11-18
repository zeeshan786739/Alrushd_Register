<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Models\WebsitePage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Website CMS → Pages → page editor: draft saving of SEO fields, the sharing-image
 * upload rules, the single "Who can find this page?" choice and one-page discard.
 */
class WebsitePageEditorTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Editor school', 'slug' => 'editor-school', 'is_active' => true]);
        $cms = WebsiteCms::forOrganization($this->school->id);
        $cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.example-school.test'])->save();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }

        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@example-school.test',
            'password' => bcrypt('password'),
            'organization_id' => $this->school->id,
        ]);
        // Admin routes require a role (admin.has.role); the role carries the settings permissions.
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);
        $this->admin->assignRole($role);
    }

    private function systemPage(array $draft = []): WebsitePage
    {
        $draft = array_replace_recursive(WebsitePage::blankDraft(), ['title' => 'About Us', 'path' => '/about-us'], $draft);

        return WebsitePage::create([
            'organization_id' => $this->school->id,
            'kind' => WebsitePage::KIND_SYSTEM,
            'path' => $draft['path'],
            'draft' => $draft,
            'published' => $draft,
            'published_at' => now(),
            'sort_order' => 1,
        ]);
    }

    private function update(WebsitePage $page, array $draft)
    {
        return $this->actingAs($this->admin, 'admin')
            ->putJson(route('admin.website-cms.pages.update', $page), ['draft' => $draft]);
    }

    public function test_meta_fields_are_saved_as_a_draft_and_not_published(): void
    {
        $page = $this->systemPage();
        $draft = $page->draft;
        $draft['seo']['title'] = 'About Al-Rushd | Online Islamic School';
        $draft['seo']['description'] = 'Our story, mission and values, and the teachers behind every live lesson.';
        $draft['seo']['og_image'] = 'https://cdn.example-school.test/about.jpg';

        $this->update($page, $draft)
            ->assertOk()
            ->assertJsonPath('page.draft.seo.title', 'About Al-Rushd | Online Islamic School')
            ->assertJsonPath('page.draft.seo.og_image', 'https://cdn.example-school.test/about.jpg')
            ->assertJsonPath('page.has_changes', true);

        $page->refresh();
        $this->assertSame('Our story, mission and values, and the teachers behind every live lesson.', $page->draft['seo']['description']);
        $this->assertSame('', $page->published['seo']['description'], 'Editing must not change the live version.');
    }

    public function test_built_in_page_address_cannot_be_changed(): void
    {
        $page = $this->systemPage();
        $draft = $page->draft;
        $draft['path'] = '/somewhere-else';

        $this->update($page, $draft)->assertOk()->assertJsonPath('page.draft.path', '/about-us');
    }

    public function test_validation_errors_are_keyed_to_the_field(): void
    {
        $this->systemPage();
        $custom = ['title' => 'Summer school', 'path' => '/about-us', 'seo' => [], 'blocks' => []];

        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.pages.store'), ['draft' => $custom])
            ->assertStatus(422)
            ->assertJsonPath('errors.path.0', 'Another page already uses the address /about-us.');

        $custom['path'] = '/summer-school';
        $custom['seo'] = ['og_image' => 'javascript:alert(1)'];
        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.pages.store'), ['draft' => $custom])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['seo.og_image']]);

        $custom['title'] = '';
        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.pages.store'), ['draft' => $custom])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_hidden_from_search_sets_both_flags_on_a_built_in_page(): void
    {
        $page = $this->systemPage();

        $draft = $page->draft;
        $draft['hidden'] = true;
        $draft['seo']['noindex'] = true;
        $this->update($page, $draft)->assertOk()
            ->assertJsonPath('page.draft.hidden', true)
            ->assertJsonPath('page.draft.seo.noindex', true);

        $draft['hidden'] = false;
        $draft['seo']['noindex'] = false;
        $this->update($page, $draft)->assertOk()
            ->assertJsonPath('page.draft.hidden', false)
            ->assertJsonPath('page.draft.seo.noindex', false);
    }

    public function test_a_built_in_page_with_only_one_flag_is_normalised_to_hidden(): void
    {
        $page = $this->systemPage(['seo' => ['noindex' => true]]);

        $this->update($page, $page->draft)->assertOk()
            ->assertJsonPath('page.draft.hidden', true)
            ->assertJsonPath('page.draft.seo.noindex', true);

        $legacy = $this->systemPage(['title' => 'Fees', 'path' => '/fees', 'hidden' => true]);
        $this->update($legacy, $legacy->draft)->assertOk()
            ->assertJsonPath('page.draft.hidden', true)
            ->assertJsonPath('page.draft.seo.noindex', true);
    }

    public function test_custom_page_visibility_keeps_hidden_from_search_reachable(): void
    {
        $base = ['title' => 'Open day', 'path' => '/open-day', 'blocks' => []];

        // "Hidden from search": out of Google and the sitemap, but the page still opens.
        $id = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.pages.store'), ['draft' => $base + ['hidden' => false, 'seo' => ['noindex' => true]]])
            ->assertOk()
            ->assertJsonPath('page.draft.hidden', false)
            ->assertJsonPath('page.draft.seo.noindex', true)
            ->json('page.id');

        // "Switched off": the page is taken down, which also implies noindex.
        $this->update(WebsitePage::findOrFail($id), $base + ['hidden' => true, 'seo' => ['noindex' => false]])
            ->assertOk()
            ->assertJsonPath('page.draft.hidden', true)
            ->assertJsonPath('page.draft.seo.noindex', true);
    }

    public function test_sharing_image_upload_accepts_jpg_and_png_only(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin, 'admin');

        $this->postJson(route('admin.website-cms.upload'), [
            'file' => UploadedFile::fake()->image('share.png', 1200, 630),
            'field' => 'page.seo.og_image',
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson(route('admin.website-cms.upload'), [
            'file' => UploadedFile::fake()->image('share.gif', 1200, 630),
            'field' => 'page.seo.og_image',
        ])->assertStatus(422)->assertJsonPath('errors.file.0', 'Please choose a JPG or PNG image.');

        $this->postJson(route('admin.website-cms.upload'), [
            'file' => UploadedFile::fake()->image('huge.jpg')->size(6000),
            'field' => 'page.seo.og_image',
        ])->assertStatus(422)->assertJsonPath('errors.file.0', 'That image is larger than 5 MB. Please choose a smaller one.');

        // Other CMS images keep the general image rule.
        $this->postJson(route('admin.website-cms.upload'), [
            'file' => UploadedFile::fake()->image('logo.gif'),
            'field' => 'branding.logo',
        ])->assertOk();
    }

    public function test_discarding_a_page_restores_its_live_version(): void
    {
        $page = $this->systemPage();
        $draft = $page->draft;
        $draft['seo']['description'] = 'A draft description nobody published.';
        $this->update($page, $draft)->assertOk();

        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.pages.discard', $page))
            ->assertOk()
            ->assertJsonPath('page.draft.seo.description', '')
            ->assertJsonPath('page.has_changes', false);
    }

    public function test_pages_of_another_school_cannot_be_edited_or_discarded(): void
    {
        $other = Organization::create(['name' => 'Other school', 'slug' => 'other-school', 'is_active' => true]);
        $foreign = WebsitePage::create([
            'organization_id' => $other->id,
            'kind' => WebsitePage::KIND_SYSTEM,
            'path' => '/fees',
            'draft' => array_replace(WebsitePage::blankDraft(), ['title' => 'Fees', 'path' => '/fees']),
            'published' => array_replace(WebsitePage::blankDraft(), ['title' => 'Fees', 'path' => '/fees']),
        ]);

        $this->update($foreign, $foreign->draft)->assertNotFound();
        $this->actingAs($this->admin, 'admin')->postJson(route('admin.website-cms.pages.discard', $foreign))->assertNotFound();
    }
}
