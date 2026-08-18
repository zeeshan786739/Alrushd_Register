<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Enums\TikTokLeadSubmissionStatus;
use App\Jobs\Integrations\ProcessTikTokLeadJob;
use App\Models\Admin;
use App\Models\Crm\Lead;
use App\Models\FormEntry;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\TikTokFormMapping;
use App\Models\Integrations\TikTokLeadSubmission;
use App\Models\Organization;
use App\Services\Integrations\TikTok\TikTokWebhookAuthenticator;
use Database\Seeders\CrmPermissionsSeeder;
use Database\Seeders\IntegrationPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TikTokLeadProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organizationA;

    protected Organization $organizationB;

    protected Admin $adminA;

    protected Admin $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CrmPermissionsSeeder::class);
        $this->seed(IntegrationPermissionsSeeder::class);

        $this->organizationA = Organization::create(['name' => 'Org A', 'slug' => 'org-a-process', 'is_active' => true]);
        $this->organizationB = Organization::create(['name' => 'Org B', 'slug' => 'org-b-process', 'is_active' => true]);

        $role = Role::where('name', 'super-admin')->where('guard_name', 'admin')->firstOrFail();

        $this->adminA = Admin::create([
            'name' => 'Admin A',
            'email' => 'admin-a-process@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationA->id,
        ]);
        $this->adminA->assignRole($role);

        $this->adminB = Admin::create([
            'name' => 'Admin B',
            'email' => 'admin-b-process@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationB->id,
        ]);
        $this->adminB->assignRole($role);

        config([
            'integrations.tiktok.app_id' => 'tiktok-test-app',
            'integrations.tiktok.app_secret' => 'tiktok-test-secret',
            'integrations.tiktok.api_base' => 'https://business-api.tiktok.com/open_api/v1.3',
        ]);

        Http::preventStrayRequests();
    }

    public function test_successful_mapping_creates_one_form_entry_and_crm_lead(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $this->createMapping($connection, ['email' => 'email', 'phone_number' => 'phone']);
        Http::fake();

        $this->postSignedWebhook($this->instantFormPayload())->assertOk();

        $this->assertDatabaseCount('tiktok_lead_submissions', 1);
        $this->assertDatabaseCount('form_entries', 1);
        $this->assertDatabaseCount('crm_leads', 1);

        $submission = TikTokLeadSubmission::query()->firstOrFail();
        $this->assertSame(TikTokLeadSubmissionStatus::Processed, $submission->status);
        $this->assertNotNull($submission->form_entry_id);
        $this->assertNotNull($submission->lead_id);

        $lead = Lead::query()->firstOrFail();
        $this->assertSame($this->organizationA->id, $lead->organization_id);
        $this->assertSame('tiktok_lead_ads', $lead->source);
        $this->assertSame('TikTok — Year 7 Admissions', $lead->lead_source);
        $this->assertSame('Ada', $lead->first_name);
        $this->assertSame('Lovelace', $lead->last_name);
        $this->assertSame('ada@example.com', $lead->email);
        $this->assertSame('07123456789', $lead->phone);
        $this->assertSame($this->adminA->id, $lead->assigned_to);
        $this->assertSame($submission->form_entry_id, $lead->form_entry_id);

        $entry = FormEntry::query()->firstOrFail();
        $this->assertSame('tiktok_lead_ads', $entry->legacy_source);
        $this->assertSame($this->organizationA->id, $entry->organization_id);
        $this->assertSame('dynamic', $entry->form->handler);
        $this->assertSame('tiktok-lead-ads-'.$this->organizationA->id, $entry->form->slug);
        $this->assertSame('Year 7', $entry->data['fields']['Which year group?'] ?? null);

        Http::assertNothingSent();
    }

    public function test_duplicate_job_creates_no_duplicate_form_entry_or_lead(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $this->createMapping($connection);
        Http::fake();

        $this->postSignedWebhook($this->instantFormPayload())->assertOk();
        $submission = TikTokLeadSubmission::query()->firstOrFail();
        (new ProcessTikTokLeadJob($submission->id))->handle(
            app(\App\Services\Integrations\TikTok\TikTokLeadFieldMapper::class),
            app(\App\Services\Integrations\TikTok\TikTokLeadSyncService::class),
        );

        $this->assertDatabaseCount('form_entries', 1);
        $this->assertDatabaseCount('crm_leads', 1);
    }

    public function test_inactive_mapping_does_not_create_crm_lead(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $this->createMapping($connection, ['email' => 'email'], ['is_active' => false]);

        $this->postSignedWebhook($this->instantFormPayload())->assertOk();

        $this->assertDatabaseCount('crm_leads', 0);
        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'status' => TikTokLeadSubmissionStatus::Ignored->value,
        ]);
        $this->assertDatabaseCount('form_entries', 1);
    }

    public function test_auto_create_false_does_not_create_crm_lead(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $this->createMapping($connection, ['email' => 'email'], ['auto_create_lead' => false]);

        $this->postSignedWebhook($this->instantFormPayload())->assertOk();

        $this->assertDatabaseCount('crm_leads', 0);
        $this->assertDatabaseCount('form_entries', 1);
        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'status' => TikTokLeadSubmissionStatus::Processed->value,
            'lead_id' => null,
        ]);
    }

    public function test_unmapped_submission_is_retained(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);

        $this->postSignedWebhook($this->instantFormPayload())->assertOk();

        $this->assertDatabaseCount('crm_leads', 0);
        $this->assertDatabaseCount('form_entries', 1);
        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'status' => TikTokLeadSubmissionStatus::Unmapped->value,
        ]);
    }

    public function test_cross_org_mapping_cannot_be_used(): void
    {
        $connectionA = $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111', 'token-a');
        $connectionB = $this->createConnectedTikTok($this->organizationB, $this->adminB, '2222222222', 'token-b');
        $this->createMapping($connectionB, ['email' => 'email'], [
            'external_form_id' => 'page-year-7',
            'advertiser_id' => '2222222222',
        ]);

        $this->postSignedWebhook($this->instantFormPayload('lead-100', '1111111111'))->assertOk();

        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'organization_id' => $this->organizationA->id,
            'status' => TikTokLeadSubmissionStatus::Unmapped->value,
        ]);
        $this->assertDatabaseCount('crm_leads', 0);
        $this->assertSame($connectionA->id, TikTokLeadSubmission::query()->firstOrFail()->integration_connection_id);
    }

    public function test_processing_does_not_call_lead_get_or_send_lead_id(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $this->createMapping($connection);
        Http::fake();

        $this->postSignedWebhook($this->instantFormPayload())->assertOk();

        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'tiktok_lead_id' => 'lead-100',
            'status' => TikTokLeadSubmissionStatus::Processed->value,
        ]);
        $this->assertDatabaseCount('crm_leads', 1);
        Http::assertNothingSent();
    }

    public function test_retry_can_process_later_successfully(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $this->createMapping($connection);
        $submission = TikTokLeadSubmission::query()->create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '1111111111',
            'tiktok_lead_id' => 'lead-100',
            'tiktok_page_id' => 'page-year-7',
            'status' => TikTokLeadSubmissionStatus::Failed,
            'field_data' => [
                'email' => 'ada@example.com',
                'phone_number' => '07123456789',
                'name' => 'Ada Lovelace',
            ],
            'error_message' => 'TikTok lead could not be processed. Please try again.',
            'received_at' => now(),
        ]);

        Http::fake();
        (new ProcessTikTokLeadJob($submission->id))->handle(
            app(\App\Services\Integrations\TikTok\TikTokLeadFieldMapper::class),
            app(\App\Services\Integrations\TikTok\TikTokLeadSyncService::class),
        );

        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'id' => $submission->id,
            'status' => TikTokLeadSubmissionStatus::Processed->value,
        ]);
        $this->assertDatabaseCount('crm_leads', 1);
        Http::assertNothingSent();
    }

    public function test_secrets_are_absent_from_error_output(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);

        $response = $this->call('POST', '/webhooks/tiktok/leads', [], [], [], [
            'HTTP_X_OPEN_SIGNATURE' => 'bad',
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($this->instantFormPayload()))->assertForbidden();

        $this->assertStringNotContainsString('tiktok-test-secret', $response->getContent());
        $this->assertStringNotContainsString('token-a', $response->getContent());
        $this->assertStringNotContainsString('ada@example.com', $response->getContent());
        $this->assertDatabaseCount('tiktok_lead_submissions', 0);
    }

    public function test_cross_org_reprocessing_is_denied(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $submission = TikTokLeadSubmission::query()->create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '1111111111',
            'tiktok_lead_id' => 'lead-100',
            'tiktok_page_id' => 'page-year-7',
            'status' => TikTokLeadSubmissionStatus::Failed,
            'received_at' => now(),
        ]);

        Http::fake();

        $this->actingAs($this->adminB, 'admin')
            ->post(route('admin.integrations.tiktok.submissions.reprocess', $submission))
            ->assertNotFound();

        $this->assertSame(TikTokLeadSubmissionStatus::Failed, $submission->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_view_only_admin_cannot_reprocess(): void
    {
        $role = Role::create(['name' => 'tiktok-lead-viewer', 'guard_name' => 'admin']);
        $role->givePermissionTo(Permission::findByName('view integrations', 'admin'));
        $viewer = Admin::create([
            'name' => 'Viewer',
            'email' => 'tiktok-lead-viewer@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationA->id,
        ]);
        $viewer->assignRole($role);

        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $submission = TikTokLeadSubmission::query()->create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '1111111111',
            'tiktok_lead_id' => 'lead-100',
            'status' => TikTokLeadSubmissionStatus::Failed,
            'received_at' => now(),
        ]);

        $this->actingAs($viewer, 'admin')
            ->post(route('admin.integrations.tiktok.submissions.reprocess', $submission))
            ->assertForbidden();
    }

    public function test_enable_lead_delivery_stores_subscription_id(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);
        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            $this->assertStringContainsString('/subscription/subscribe/', $request->url());
            $this->assertSame('LEAD', $request['subscribe_entity']);
            $this->assertSame('1111111111', $request['subscription_detail']['advertiser_id']);
            $this->assertSame('token-a', $request['subscription_detail']['access_token']);
            $this->assertSame('INSTANT_FORM', $request['subscription_detail']['lead_source']);

            return Http::response([
                'code' => 0,
                'message' => 'OK',
                'data' => ['subscription_id' => 'sub-123'],
            ], 200);
        });

        $this->actingAs($this->adminA, 'admin')
            ->post(route('admin.integrations.tiktok.register-webhook'))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('success');

        $connection = IntegrationConnection::query()
            ->where('organization_id', $this->organizationA->id)
            ->where('platform', IntegrationPlatform::TikTok)
            ->firstOrFail();

        $this->assertSame('sub-123', $connection->settings['subscription_id'] ?? null);
        $this->assertSame('1111111111', $connection->settings['subscription_advertiser_id'] ?? null);
        $this->assertNotNull($connection->webhook_subscribed_at);

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Subscribed')
            ->assertDontSee('Lead delivery — Setup required');
    }

    public function test_setup_required_when_webhook_is_not_subscribed(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Setup required')
            ->assertSee('Enable lead delivery')
            ->assertSee('/webhooks/tiktok/leads')
            ->assertSee('No TikTok leads received yet');

        Http::assertNothingSent();
    }

    public function test_recent_leads_ui_shows_status_and_reprocess(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        TikTokLeadSubmission::query()->create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '1111111111',
            'tiktok_lead_id' => 'lead-100',
            'tiktok_page_id' => 'page-year-7',
            'status' => TikTokLeadSubmissionStatus::Failed,
            'field_data' => ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
            'error_message' => 'TikTok lead could not be processed. Please try again.',
            'received_at' => now(),
        ]);

        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Ada Lovelace')
            ->assertSee('Failed')
            ->assertSee('Reprocess')
            ->assertSee('Setup required')
            ->assertDontSee('Subscribed')
            ->assertDontSee('token-a')
            ->assertDontSee('tiktok-test-secret');
    }

    public function test_reprocess_queues_job_for_current_org_only(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $this->createMapping($connection);
        $submission = TikTokLeadSubmission::query()->create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '1111111111',
            'tiktok_lead_id' => 'lead-100',
            'tiktok_page_id' => 'page-year-7',
            'status' => TikTokLeadSubmissionStatus::Unmapped,
            'field_data' => ['email' => 'ada@example.com', 'name' => 'Ada Lovelace'],
            'received_at' => now(),
        ]);

        Queue::fake();
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->post(route('admin.integrations.tiktok.submissions.reprocess', $submission))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('success');

        $this->assertSame(TikTokLeadSubmissionStatus::Pending, $submission->fresh()->status);
        Queue::assertPushed(ProcessTikTokLeadJob::class, fn (ProcessTikTokLeadJob $job) => $job->tiktokLeadSubmissionId === $submission->id);
        Http::assertNothingSent();
    }

    public function test_enable_lead_delivery_skips_tiktok_when_current_advertiser_already_subscribed(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111', 'token-a', [
            'subscription_id' => 'sub-existing',
            'subscription_advertiser_id' => '1111111111',
        ], now());
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->post(route('admin.integrations.tiktok.register-webhook'))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('success');

        Http::assertNothingSent();

        $connection = IntegrationConnection::query()
            ->where('organization_id', $this->organizationA->id)
            ->where('platform', IntegrationPlatform::TikTok)
            ->firstOrFail();

        $this->assertSame('sub-existing', $connection->settings['subscription_id'] ?? null);
    }

    public function test_stale_subscription_for_another_advertiser_is_not_active(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111', 'token-a', [
            'subscription_id' => 'sub-old',
            'subscription_advertiser_id' => '9999999999',
        ], now());
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Setup required')
            ->assertSee('Enable lead delivery')
            ->assertDontSee('Subscribed');

        Http::assertNothingSent();
    }

    public function test_stale_subscription_for_another_advertiser_can_be_replaced(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111', 'token-a', [
            'subscription_id' => 'sub-old',
            'subscription_advertiser_id' => '9999999999',
        ], now());

        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            $this->assertStringContainsString('/subscription/subscribe/', $request->url());
            $this->assertSame('1111111111', $request['subscription_detail']['advertiser_id']);

            return Http::response([
                'code' => 0,
                'message' => 'OK',
                'data' => ['subscription_id' => 'sub-new'],
            ], 200);
        });

        $this->actingAs($this->adminA, 'admin')
            ->post(route('admin.integrations.tiktok.register-webhook'))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('success');

        $connection = IntegrationConnection::query()
            ->where('organization_id', $this->organizationA->id)
            ->where('platform', IntegrationPlatform::TikTok)
            ->firstOrFail();

        $this->assertSame('sub-new', $connection->settings['subscription_id'] ?? null);
        $this->assertSame('1111111111', $connection->settings['subscription_advertiser_id'] ?? null);
    }

    public function test_legacy_subscription_without_advertiser_marker_is_not_treated_as_active(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111', 'token-a', [
            'subscription_id' => 'sub-legacy',
        ], now());
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Setup required')
            ->assertDontSee('Subscribed');

        Http::assertNothingSent();
    }

    public function test_lock_contention_returns_friendly_message_without_calling_tiktok(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        Http::fake();

        $lock = Cache::lock('tiktok:lead-subscription:'.$connection->id, 30);
        $this->assertTrue($lock->get());

        try {
            $this->actingAs($this->adminA, 'admin')
                ->post(route('admin.integrations.tiktok.register-webhook'))
                ->assertRedirect(route('admin.integrations.tiktok.show'))
                ->assertSessionHas('error', 'TikTok lead delivery setup is already in progress. Please try again shortly.');

            Http::assertNothingSent();
        } finally {
            $lock->release();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postSignedWebhook(array $payload)
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $this->assertIsString($body);

        return $this->call('POST', '/webhooks/tiktok/leads', [], [], [], [
            'HTTP_X_OPEN_SIGNATURE' => hash_hmac(
                'sha256',
                (new TikTokWebhookAuthenticator)->unicodeEscape($body),
                'tiktok-test-secret'
            ),
            'CONTENT_TYPE' => 'application/json',
        ], $body);
    }

    /**
     * @return array<string, mixed>
     */
    private function instantFormPayload(string $leadId = 'lead-100', string $advertiserId = '1111111111'): array
    {
        return [
            'object' => 1,
            'entry' => [[
                'id' => $leadId,
                'lead_source' => 'INSTANT_FORM',
                'page_id' => 'page-year-7',
                'page_name' => 'Year 7 Admissions',
                'advertiser_id' => $advertiserId,
                'create_time' => 1710000000,
                'changes' => [
                    ['field' => 'email', 'value' => 'ada@example.com'],
                    ['field' => 'phone_number', 'value' => '07123456789'],
                    ['field' => 'name', 'value' => 'Ada Lovelace'],
                    ['field' => 'Which year group?', 'value' => 'Year 7'],
                ],
            ]],
            'time' => 1710000000,
        ];
    }

    /**
     * @param  array<string, string>  $fieldMapping
     * @param  array<string, mixed>  $overrides
     */
    private function createMapping(IntegrationConnection $connection, array $fieldMapping = ['email' => 'email', 'phone_number' => 'phone'], array $overrides = []): TikTokFormMapping
    {
        return TikTokFormMapping::query()->create(array_merge([
            'organization_id' => $connection->organization_id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => $connection->external_account_id,
            'external_form_id' => 'page-year-7',
            'external_form_name' => 'Year 7 Admissions',
            'external_status' => 'PUBLISHED',
            'lead_source_label' => 'TikTok — Year 7 Admissions',
            'assigned_to' => $connection->organization_id === $this->organizationA->id ? $this->adminA->id : $this->adminB->id,
            'priority' => 'high',
            'auto_create_lead' => true,
            'is_active' => true,
            'field_mapping' => $fieldMapping,
            'external_fields' => array_keys($fieldMapping),
        ], $overrides));
    }

    private function createConnectedTikTok(
        Organization $organization,
        Admin $admin,
        string $advertiserId = '1111111111',
        string $token = 'token-a',
        array $settings = [],
        mixed $webhookSubscribedAt = null,
    ): IntegrationConnection {
        return IntegrationConnection::create([
            'organization_id' => $organization->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => $advertiserId,
            'external_account_name' => 'School Ads Account',
            'access_token' => $token,
            'connected_by' => $admin->id,
            'settings' => $settings,
            'webhook_subscribed_at' => $webhookSubscribedAt,
        ]);
    }
}
