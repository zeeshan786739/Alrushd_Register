<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormEntry;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Models\WebsitePage;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HeadlessWebsiteTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Headless school', 'slug' => 'headless-school', 'is_active' => true]);
        $cms = WebsiteCms::forOrganization($this->school->id);
        $cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.example-school.test'])->save();
        $this->token = $cms->rotateApiToken();
        PublicOrganizationContext::clear();
    }

    protected function tearDown(): void
    {
        PublicOrganizationContext::clear();
        parent::tearDown();
    }

    private function api(): self
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->token);
    }

    public function test_api_rejects_missing_or_wrong_token(): void
    {
        $this->getJson('/api/headless/v1/site')->assertUnauthorized();
        $this->withHeader('Authorization', 'Bearer wst_wrong_wrong_wrong_wrong')->getJson('/api/headless/v1/site')->assertUnauthorized();
    }

    public function test_site_payload_never_exposes_system_secrets(): void
    {
        $this->api()->getJson('/api/headless/v1/site')
            ->assertOk()
            ->assertJsonPath('organization.slug', 'headless-school')
            ->assertJsonStructure(['settings' => ['branding', 'theme', 'contact', 'nav_menu', 'footer_menu', 'website']])
            ->assertJsonMissingPath('settings.system');
    }

    public function test_unpublished_pages_are_only_visible_as_drafts(): void
    {
        $draft = WebsitePage::normaliseDraft(['title' => 'Summer school', 'path' => '/summer-school', 'blocks' => [
            ['type' => 'rich_text', 'data' => ['body' => 'Hello']],
            ['type' => 'not-a-block', 'data' => []],
        ]], WebsitePage::KIND_CUSTOM);
        $page = WebsitePage::create(['organization_id' => $this->school->id, 'kind' => 'custom', 'draft' => $draft]);

        $this->assertCount(1, $page->draft['blocks'], 'unknown block types are dropped');
        $this->api()->getJson('/api/headless/v1/page?path=/summer-school')->assertNotFound();
        $this->api()->getJson('/api/headless/v1/page?path=/summer-school&draft=1')->assertOk()->assertJsonPath('page.title', 'Summer school');

        $page->publish();
        $this->api()->getJson('/api/headless/v1/page?path=/summer-school')->assertOk()->assertJsonPath('page.blocks.0.type', 'rich_text');
    }

    public function test_pages_of_another_school_are_not_served(): void
    {
        $other = Organization::create(['name' => 'Other', 'slug' => 'other', 'is_active' => true]);
        $draft = WebsitePage::normaliseDraft(['title' => 'Private', 'path' => '/private'], WebsitePage::KIND_CUSTOM);
        WebsitePage::create(['organization_id' => $other->id, 'kind' => 'custom', 'draft' => $draft, 'published' => $draft, 'path' => '/private']);

        $this->api()->getJson('/api/headless/v1/page?path=/private')->assertNotFound();
    }

    public function test_submissions_are_stored_for_the_tokens_school_with_the_visitor_ip(): void
    {
        $form = Form::create(['organization_id' => $this->school->id, 'name' => 'Contact', 'slug' => 'website-contact', 'handler' => 'dynamic', 'is_active' => true]);
        $form->fields()->create(['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'sort_order' => 1]);

        $this->api()->withHeader('X-Visitor-IP', '203.0.113.5')
            ->postJson('/api/headless/v1/forms/website-contact/submit', [])
            ->assertStatus(422);

        $this->api()->withHeader('X-Visitor-IP', '203.0.113.5')
            ->postJson('/api/headless/v1/forms/website-contact/submit', ['email' => 'parent@example.com'])
            ->assertOk();

        $entry = FormEntry::where('form_id', $form->id)->firstOrFail();
        $this->assertSame($this->school->id, $entry->organization_id);
        $this->assertSame('203.0.113.5', $entry->ip_address);
    }

    public function test_old_built_in_site_redirects_only_for_the_connected_school(): void
    {
        $this->get('/w/headless-school/')->assertRedirect('https://www.example-school.test/');

        PublicOrganizationContext::clear();
        Organization::create(['name' => 'Builtin', 'slug' => 'builtin-school', 'is_active' => true]);
        $this->get('/w/builtin-school/')->assertOk();
    }

    public function test_publishing_pings_the_connected_site(): void
    {
        PublicOrganizationContext::set($this->school);
        app(\App\Services\WebsiteCmsService::class)->publish(null);

        Http::assertSent(fn ($request) => $request->url() === 'https://www.example-school.test/api/revalidate'
            && $request->hasHeader('Authorization', 'Bearer '.$this->token));
        $this->assertSame('ok', WebsiteCms::forOrganization($this->school->id)->last_revalidate_status);
    }
}
