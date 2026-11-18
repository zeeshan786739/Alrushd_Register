<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AiAssistantChange;
use App\Models\AiAssistantRequest;
use App\Models\AiAssistantSetting;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Models\WebsitePage;
use App\Services\AiAssistant\AiAssistantConfig;
use App\Services\AiAssistant\AiProvider;
use App\Services\AiAssistant\AiProviderException;
use App\Services\AiAssistant\AiReply;
use App\Services\AiAssistant\CmsAssistant;
use App\Services\AiAssistant\CmsAssistantTools;
use App\Services\HeadlessWebsiteService;
use App\Services\WebsiteCmsService;
use App\Support\PublicOrganizationContext;
use App\Support\WebsiteCmsValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** A scripted model: returns the queued replies in order and remembers what it was sent. */
class ScriptedAiProvider implements AiProvider
{
    public array $calls = [];

    public function __construct(public array $replies) {}

    public static function tool(string $name, array $args, string $id = 'call_1'): AiReply
    {
        $call = ['id' => $id, 'type' => 'function', 'function' => ['name' => $name, 'arguments' => json_encode($args)]];

        return new AiReply(null, [['id' => $id, 'name' => $name, 'arguments' => $args]], 120, 20, ['role' => 'assistant', 'content' => null, 'tool_calls' => [$call]]);
    }

    public static function text(string $text): AiReply
    {
        return new AiReply($text, [], 150, 30, ['role' => 'assistant', 'content' => $text]);
    }

    public function chat(array $messages, array $tools): AiReply
    {
        $this->calls[] = ['messages' => $messages, 'tools' => $tools];
        $next = array_shift($this->replies);
        if ($next instanceof \Throwable) {
            throw $next;
        }

        return $next ?? self::text('Done.');
    }
}

/** Website CMS → AI assistant: tools go through the CMS's own validation and draft save; nothing publishes. */
class WebsiteAiAssistantTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private WebsiteCms $cms;

    private Admin $editor;

    private Admin $developer;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'AI school', 'slug' => 'ai-school', 'is_active' => true]);
        $this->cms = WebsiteCms::forOrganization($this->school->id);
        $this->cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.ai-school.test'])->save();
        $this->cms->rotateApiToken();

        foreach (['view setting', 'edit setting', 'edit website code', 'manage ai assistant'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $editor = Role::findOrCreate('website-editor', 'admin');
        $editor->givePermissionTo(['view setting', 'edit setting']);
        $developer = Role::findOrCreate('website-developer', 'admin');
        $developer->givePermissionTo(['view setting', 'edit setting', 'edit website code', 'manage ai assistant']);

        $this->editor = $this->makeAdmin('editor@ai-school.test', $editor);
        $this->developer = $this->makeAdmin('dev@ai-school.test', $developer);

        AiAssistantSetting::create(['organization_id' => $this->school->id, 'enabled' => true, 'api_key' => 'sk-test-abcd1234', 'limit_type' => 'tokens']);
        PublicOrganizationContext::clear();
    }

    protected function tearDown(): void
    {
        PublicOrganizationContext::clear();
        parent::tearDown();
    }

    private function makeAdmin(string $email, ?Role $role): Admin
    {
        $admin = Admin::create(['name' => 'A', 'email' => $email, 'password' => bcrypt('password'), 'organization_id' => $this->school->id]);
        if ($role) {
            $admin->assignRole($role);
        }

        return $admin;
    }

    private function tools(Admin $admin): CmsAssistantTools
    {
        $this->actingAs($admin, 'admin');

        return new CmsAssistantTools(app(WebsiteCmsService::class), app(WebsiteCmsValidator::class), app(HeadlessWebsiteService::class), $admin);
    }

    private function draft(): array
    {
        return app(WebsiteCmsService::class)->getDraft();
    }

    private function fakeModel(array $replies): ScriptedAiProvider
    {
        $fake = new ScriptedAiProvider($replies);
        $this->app->instance(AiProvider::class, $fake);

        return $fake;
    }

    /* ── tools: validation ── */

    public function test_invalid_values_are_rejected_without_saving(): void
    {
        $tools = $this->tools($this->editor);
        $before = $this->draft();

        // The CMS's own rules (WebsiteCmsValidator) …
        $this->assertSame('Please enter a full email address, like name@school.co.uk', $tools->updateSection('contact', ['email' => 'not-an-email'])['errors']['email']);
        $this->assertArrayHasKey('short_name', $tools->updateSection('branding', ['short_name' => str_repeat('x', 31)])['errors']);
        $this->assertArrayHasKey('twitter', $tools->updateSection('social', ['twitter' => 'https://example.com/school'])['errors']);
        // … plus the map's type checks, and nothing outside the map.
        $this->assertArrayHasKey('accent', $tools->updateSection('theme', ['accent' => 'gold'])['errors']);
        $this->assertArrayHasKey('logo', $tools->updateSection('branding', ['logo' => 'https://evil.test/x.png'])['errors']);
        $this->assertArrayHasKey('custom_css', $this->tools($this->developer)->updateSection('custom_code', ['custom_css' => 'body{}'])['errors']);
        $this->assertArrayHasKey('error', $tools->updateSection('system', ['stripe_secret' => 'x']));

        $this->assertSame($before, $this->draft());
        $this->assertSame(0, AiAssistantChange::count());
    }

    public function test_a_valid_change_is_saved_as_a_draft_with_old_and_new_values(): void
    {
        $tools = $this->tools($this->editor);
        $tools->recordAs('11111111-1111-1111-1111-111111111111');

        $result = $tools->updateSection('footer_menu', ['heading' => 'Learning|without limits']);

        $this->assertSame(['heading' => ['', 'Learning|without limits']], $result['changed']);
        $this->assertSame('Learning|without limits', $this->draft()['footer_menu']['heading']);
        $this->assertEmpty(WebsiteCms::find($this->cms->id)->published, 'nothing is published');
        $this->assertNull(WebsiteCms::find($this->cms->id)->published_at);
        $this->assertDatabaseHas('ai_assistant_changes', ['change_set' => '11111111-1111-1111-1111-111111111111', 'target' => 'section', 'target_key' => 'footer_menu', 'field' => 'heading']);
    }

    public function test_menu_items_are_added_and_removed_through_the_same_save(): void
    {
        $tools = $this->tools($this->editor);
        $tools->updateSection('nav_menu', ['cta_text' => 'Apply today']);
        app(WebsiteCmsService::class)->saveDraft(array_replace_recursive($this->draft(), ['nav_menu' => ['items' => [
            ['label' => 'Home', 'href' => '/', 'group' => '', 'description' => ''],
            ['label' => 'Our story', 'href' => '/about', 'group' => 'About', 'description' => ''],
        ]]]));

        $this->assertSame('Added “Fees” under About', $tools->addMenuItem(['menu' => 'header', 'label' => 'Fees', 'href' => '/fees', 'group' => 'About'])['done']);
        $this->assertSame(['Home', 'Our story', 'Fees'], array_column($this->draft()['nav_menu']['items'], 'label'));
        $this->assertSame('About', $this->draft()['nav_menu']['items'][2]['group']);

        $this->assertStringContainsString('No link called', $tools->removeMenuItem(['menu' => 'header', 'label' => 'Prices'])['error']);
        $tools->removeMenuItem(['menu' => 'header', 'label' => 'fees']);
        $this->assertSame(['Home', 'Our story'], array_column($this->draft()['nav_menu']['items'], 'label'));
    }

    public function test_pages_change_through_the_page_rules(): void
    {
        $tools = $this->tools($this->editor);
        $page = WebsitePage::create(['organization_id' => $this->school->id, 'kind' => WebsitePage::KIND_CUSTOM, 'draft' => WebsitePage::normaliseDraft(['title' => 'Fees', 'path' => '/fees'], WebsitePage::KIND_CUSTOM), 'sort_order' => 1]);

        $this->assertArrayHasKey('og_image', $tools->updatePage($page->id, ['og_image' => 'not a link'])['errors']);
        $this->assertArrayHasKey('title', $tools->updatePage($page->id, ['title' => '  '])['errors']);
        $this->assertArrayHasKey('path', $tools->updatePage($page->id, ['path' => '/admin'])['errors'], 'the address is not editable here');

        $this->assertSame(['seo_title' => ['', 'School fees 2027']], $tools->updatePage($page->id, ['seo_title' => 'School fees 2027'])['changed']);
        $this->assertSame('School fees 2027', $page->fresh()->draft['seo']['title']);
    }

    /* ── permissions ── */

    public function test_code_and_analytics_need_the_edit_website_code_permission(): void
    {
        $tools = $this->tools($this->editor);
        $this->assertSame(['error' => 'not allowed'], $tools->updateSection('analytics', ['google_analytics' => 'G-ABC123DEF4']));
        $this->assertSame(['error' => 'not allowed'], $tools->updateSection('custom_code', ['footer_code_enabled' => false]));
        $this->assertSame('', $this->draft()['analytics']['google_analytics']);

        $dev = $this->tools($this->developer);
        $this->assertArrayHasKey('changed', $dev->updateSection('analytics', ['google_analytics' => 'G-ABC123DEF4']));
        $this->assertArrayHasKey('google_analytics', $dev->updateSection('analytics', ['google_analytics' => 'UA-1'])['errors']);
    }

    public function test_people_who_cannot_edit_the_website_cannot_use_the_assistant(): void
    {
        $viewer = $this->makeAdmin('viewer@ai-school.test', tap(Role::findOrCreate('viewer', 'admin'))->givePermissionTo('view setting'));
        $this->assertSame(['error' => 'not allowed'], $this->tools($viewer)->updateSection('footer_menu', ['heading' => 'x']));

        $this->actingAs($viewer, 'admin')->postJson(route('admin.website-cms.ai-assistant.chat'), ['message' => 'hi'])->assertForbidden();
        $this->actingAs($this->editor, 'admin')->postJson(route('admin.website-cms.ai-assistant.settings.save'), ['enabled' => false, 'limit_type' => 'tokens'])->assertForbidden();
    }

    /* ── undo ── */

    public function test_undo_restores_the_old_values_through_the_same_save(): void
    {
        $this->fakeModel([
            ScriptedAiProvider::tool('update_section', ['section' => 'social', 'changes' => ['enabled.twitter' => false]]),
            ScriptedAiProvider::text('I hid the Twitter icon.'),
        ]);
        $this->assertTrue($this->draft()['social']['enabled']['twitter']);

        $changeSet = $this->actingAs($this->editor, 'admin')
            ->postJson(route('admin.website-cms.ai-assistant.chat'), ['message' => 'hide the twitter icon', 'section' => 'social'])
            ->assertOk()->assertJsonPath('data.social.enabled.twitter', false)->json('change_set');
        $this->assertFalse($this->draft()['social']['enabled']['twitter']);

        $this->postJson(route('admin.website-cms.ai-assistant.undo'), ['change_set' => $changeSet])
            ->assertOk()->assertJsonPath('data.social.enabled.twitter', true);
        $this->assertTrue($this->draft()['social']['enabled']['twitter']);
        $this->assertNotNull(AiAssistantChange::where('change_set', $changeSet)->first()->undone_at);

        $this->postJson(route('admin.website-cms.ai-assistant.undo'), ['change_set' => $changeSet])->assertStatus(422);
    }

    /* ── no publishing ── */

    public function test_there_is_no_publish_tool_and_nothing_is_published(): void
    {
        $names = array_map(fn ($t) => $t['function']['name'], CmsAssistantTools::definitions());
        $this->assertEmpty(array_filter($names, fn ($n) => str_contains($n, 'publish')));
        $this->assertSame(['error' => 'Unknown tool.'], $this->tools($this->editor)->execute('publish', []));
        $this->assertFalse(collect(app('router')->getRoutes())->contains(fn ($r) => str_contains($r->uri(), 'ai-assistant') && str_contains($r->uri(), 'publish')));
    }

    /* ── history ── */

    public function test_history_keeps_six_messages_plus_a_one_line_summary_and_no_tool_results(): void
    {
        $history = [];
        foreach (range(1, 5) as $i) {
            $history[] = ['role' => 'user', 'content' => "request {$i}"];
            $history[] = ['role' => 'tool', 'content' => '{"big":"result"}'];
            $history[] = ['role' => 'assistant', 'content' => "answer {$i}"];
        }

        [$summary, $recent] = CmsAssistant::trimHistory($history);

        $this->assertCount(6, $recent);
        $this->assertSame(['request 3', 'answer 3', 'request 4', 'answer 4', 'request 5', 'answer 5'], array_column($recent, 'content'));
        $this->assertSame('Earlier in this chat the user asked: “request 1”; “request 2”.', $summary);
        $this->assertSame([null, [['role' => 'user', 'content' => 'hi']]], CmsAssistant::trimHistory([['role' => 'user', 'content' => 'hi']]));
    }

    /* ── settings ── */

    public function test_settings_fall_back_to_env_and_the_key_stays_encrypted_and_private(): void
    {
        config(['services.openai' => ['key' => 'sk-from-env-9999', 'model' => 'gpt-4.1-nano', 'base_url' => 'https://llm.example.test/v1/']]);
        AiAssistantSetting::where('organization_id', $this->school->id)->update(['api_key' => null, 'model' => null, 'base_url' => null]);

        $config = AiAssistantConfig::forOrganization($this->school->id);
        $this->assertSame(['sk-from-env-9999', 'gpt-4.1-nano', 'https://llm.example.test/v1', true], [$config->apiKey, $config->model, $config->baseUrl, $config->keyFromEnv]);

        $this->actingAs($this->developer, 'admin')->postJson(route('admin.website-cms.ai-assistant.settings.save'), [
            'enabled' => true, 'api_key' => 'sk-saved-key-5678', 'model' => 'gpt-4o-mini', 'limit_type' => 'usd', 'monthly_limit' => 5,
        ])->assertOk()->assertJsonPath('settings.key_last4', '5678')->assertJsonMissing(['sk-saved-key-5678']);

        $config = AiAssistantConfig::forOrganization($this->school->id);
        $this->assertSame(['sk-saved-key-5678', 'gpt-4o-mini', 'https://llm.example.test/v1', false], [$config->apiKey, $config->model, $config->baseUrl, $config->keyFromEnv]);
        $raw = DB::table('ai_assistant_settings')->where('organization_id', $this->school->id)->value('api_key');
        $this->assertStringNotContainsString('sk-saved-key-5678', $raw);

        // An empty key field keeps the saved key.
        $this->postJson(route('admin.website-cms.ai-assistant.settings.save'), ['enabled' => true, 'api_key' => '', 'limit_type' => 'tokens'])->assertOk();
        $this->assertSame('sk-saved-key-5678', AiAssistantConfig::forOrganization($this->school->id)->apiKey);
        $this->get(route('admin.settings.index'))->assertOk()->assertDontSee('sk-saved-key-5678')->assertSee('website-cms-assistant.js', false);
    }

    /* ── end to end ── */

    public function test_change_the_footer_heading_end_to_end(): void
    {
        $model = $this->fakeModel([
            ScriptedAiProvider::tool('get_section', ['section' => 'footer_menu']),
            ScriptedAiProvider::tool('update_section', ['section' => 'footer_menu', 'changes' => ['heading' => 'Learning without limits']], 'call_2'),
            ScriptedAiProvider::text('I changed the footer heading to “Learning without limits”. Press Publish to put it live.'),
        ]);

        $response = $this->actingAs($this->editor, 'admin')
            ->postJson(route('admin.website-cms.ai-assistant.chat'), ['message' => 'Change the footer heading to Learning without limits', 'section' => 'footer_menu'])
            ->assertOk()
            ->assertJsonPath('changes.0.section', 'footer_menu')
            ->assertJsonPath('changes.0.field', 'heading')
            ->assertJsonPath('changes.0.new', 'Learning without limits')
            ->assertJsonPath('data.footer_menu.heading', 'Learning without limits');
        $this->assertStringContainsString('Publish', $response->json('reply'));

        $this->assertSame('Learning without limits', $this->draft()['footer_menu']['heading']);
        $this->assertEmpty(WebsiteCms::find($this->cms->id)->published);
        $this->assertNull(WebsiteCms::find($this->cms->id)->published_at);

        // The same system prompt and tools come first on every round (prompt caching); the tool result went back.
        $this->assertCount(3, $model->calls);
        foreach ($model->calls as $call) {
            $this->assertSame(['role' => 'system', 'content' => CmsAssistant::SYSTEM_PROMPT], $call['messages'][0]);
            $this->assertSame(json_encode(CmsAssistantTools::definitions()), json_encode($call['tools']));
        }
        $this->assertSame("[Open section: Footer]\nChange the footer heading to Learning without limits", $model->calls[0]['messages'][1]['content']);
        $this->assertSame('tool', end($model->calls[1]['messages'])['role']);
        $this->assertLessThan(150, str_word_count(CmsAssistant::SYSTEM_PROMPT));

        $log = AiAssistantRequest::sole();
        $this->assertSame([$this->editor->id, 'footer_menu', 'ok', 390, 70, 3], [$log->admin_id, $log->section, $log->status, $log->tokens_in, $log->tokens_out, $log->rounds]);
        $this->assertGreaterThan(0, $log->cost_usd);
        $this->assertSame($log->id, AiAssistantChange::sole()->request_id);
    }

    public function test_a_failing_ai_service_changes_nothing_and_says_so(): void
    {
        $this->fakeModel([new AiProviderException('timeout')]);
        $before = $this->draft();

        $this->actingAs($this->editor, 'admin')->postJson(route('admin.website-cms.ai-assistant.chat'), ['message' => 'hide twitter'])
            ->assertStatus(502)->assertJsonPath('message', 'The AI service isn’t responding — your site wasn’t changed. Try again.');
        $this->assertSame($before, $this->draft());
        $this->assertSame('error', AiAssistantRequest::sole()->status);
    }

    public function test_the_model_stops_after_five_tool_rounds(): void
    {
        $model = $this->fakeModel(array_fill(0, 8, ScriptedAiProvider::tool('list_sections', [])));

        $this->actingAs($this->editor, 'admin')->postJson(route('admin.website-cms.ai-assistant.chat'), ['message' => 'loop'])->assertOk();
        $this->assertCount(CmsAssistant::MAX_ROUNDS, $model->calls);
    }

    /* ── limits ── */

    public function test_rate_limit_and_monthly_limit(): void
    {
        $this->fakeModel([]);
        RateLimiter::clear('ai-assistant:'.$this->editor->id);
        foreach (range(1, 30) as $_) {
            RateLimiter::hit('ai-assistant:'.$this->editor->id, 3600);
        }
        $this->actingAs($this->editor, 'admin')->postJson(route('admin.website-cms.ai-assistant.chat'), ['message' => 'hi'])->assertStatus(429);
        RateLimiter::clear('ai-assistant:'.$this->editor->id);

        AiAssistantSetting::where('organization_id', $this->school->id)->update(['monthly_limit' => 1000, 'limit_type' => 'tokens']);
        AiAssistantRequest::create(['organization_id' => $this->school->id, 'admin_id' => $this->editor->id, 'model' => 'gpt-4o-mini', 'tokens_in' => 900, 'tokens_out' => 100, 'status' => 'ok']);

        $this->postJson(route('admin.website-cms.ai-assistant.chat'), ['message' => 'hi'])
            ->assertStatus(423)->assertJsonPath('blocked', true);
    }
}
