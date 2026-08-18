<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Enums\TikTokLeadSubmissionStatus;
use App\Jobs\Integrations\ProcessTikTokLeadJob;
use App\Models\Admin;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\TikTokLeadSubmission;
use App\Models\Organization;
use App\Services\Integrations\TikTok\TikTokWebhookAuthenticator;
use Database\Seeders\CrmPermissionsSeeder;
use Database\Seeders\IntegrationPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TikTokLeadWebhookTest extends TestCase
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

        $this->organizationA = Organization::create(['name' => 'Org A', 'slug' => 'org-a-leads', 'is_active' => true]);
        $this->organizationB = Organization::create(['name' => 'Org B', 'slug' => 'org-b-leads', 'is_active' => true]);

        $role = Role::where('name', 'super-admin')->where('guard_name', 'admin')->firstOrFail();

        $this->adminA = Admin::create([
            'name' => 'Admin A',
            'email' => 'admin-a-leads@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationA->id,
        ]);
        $this->adminA->assignRole($role);

        $this->adminB = Admin::create([
            'name' => 'Admin B',
            'email' => 'admin-b-leads@test.local',
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
        Queue::fake();
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);

        $response = $this->postSignedWebhook($this->instantFormPayload(), 'not-a-valid-signature', 403);

        $this->assertDatabaseCount('tiktok_lead_submissions', 0);
        $this->assertDatabaseCount('crm_leads', 0);
        Queue::assertNothingPushed();
        $this->assertStringNotContainsString('ada@example.com', $response->getContent());
        $this->assertStringNotContainsString('tiktok-test-secret', $response->getContent());
        Http::assertNothingSent();
    }

    public function test_missing_signature_is_rejected(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);

        $this->call('POST', '/webhooks/tiktok/leads', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($this->instantFormPayload()))->assertForbidden();

        $this->assertDatabaseCount('tiktok_lead_submissions', 0);
        Queue::assertNothingPushed();
        Http::assertNothingSent();
    }

    public function test_valid_payload_is_accepted_and_queued(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);

        $this->postSignedWebhook($this->instantFormPayload())->assertSee('OK');

        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '1111111111',
            'tiktok_lead_id' => 'lead-100',
            'tiktok_page_id' => 'page-year-7',
            'status' => TikTokLeadSubmissionStatus::Pending->value,
        ]);
        $this->assertDatabaseMissing('tiktok_lead_submissions', [
            'organization_id' => $this->organizationB->id,
        ]);
        Queue::assertPushed(ProcessTikTokLeadJob::class, 1);
        Http::assertNothingSent();
    }

    public function test_altered_body_fails_signature(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $payload = $this->instantFormPayload();
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $this->assertIsString($body);
        $signature = $this->officialSignature($body);
        $tampered = str_replace('ada@example.com', 'eve@example.com', $body);

        $this->call('POST', '/webhooks/tiktok/leads', [], [], [], [
            'HTTP_X_OPEN_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $tampered)->assertForbidden();

        $this->assertDatabaseCount('tiktok_lead_submissions', 0);
        Queue::assertNothingPushed();
        Http::assertNothingSent();
    }

    public function test_raw_utf8_hmac_is_rejected_when_body_contains_unicode(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $payload = $this->instantFormPayload();
        $payload['entry'][0]['page_name'] = 'äöå form';
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $this->assertIsString($body);
        $rawHmac = hash_hmac('sha256', $body, 'tiktok-test-secret');

        $this->call('POST', '/webhooks/tiktok/leads', [], [], [], [
            'HTTP_X_OPEN_SIGNATURE' => $rawHmac,
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertForbidden();

        $this->assertDatabaseCount('tiktok_lead_submissions', 0);
        Queue::assertNothingPushed();
        Http::assertNothingSent();
    }

    public function test_unicode_escaped_signature_is_accepted(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $payload = $this->instantFormPayload();
        $payload['entry'][0]['page_name'] = 'äöå form';
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $escaped = (new TikTokWebhookAuthenticator)->unicodeEscape($body);
        $signature = hash_hmac('sha256', $escaped, 'tiktok-test-secret');

        $this->call('POST', '/webhooks/tiktok/leads', [], [], [], [
            'HTTP_X_OPEN_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();

        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'tiktok_lead_id' => 'lead-100',
            'organization_id' => $this->organizationA->id,
        ]);
        Http::assertNothingSent();
    }

    public function test_unknown_advertiser_does_not_create_submission(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111');

        $this->postSignedWebhook($this->instantFormPayload('lead-100', '9999999999'))->assertOk();

        $this->assertDatabaseCount('tiktok_lead_submissions', 0);
        $this->assertDatabaseCount('crm_leads', 0);
        Queue::assertNothingPushed();
    }

    public function test_ambiguous_advertiser_fails_safely(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111', 'token-a');
        $this->createConnectedTikTok($this->organizationB, $this->adminB, '1111111111', 'token-b');

        $this->postSignedWebhook($this->instantFormPayload())->assertOk();

        $this->assertDatabaseCount('tiktok_lead_submissions', 0);
        $this->assertDatabaseCount('crm_leads', 0);
        Queue::assertNothingPushed();
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);

        $this->postSignedWebhook($this->instantFormPayload())->assertOk();
        $this->postSignedWebhook($this->instantFormPayload())->assertOk();

        $this->assertDatabaseCount('tiktok_lead_submissions', 1);
        Queue::assertPushed(ProcessTikTokLeadJob::class, 1);
    }

    public function test_direct_message_entries_are_ignored(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $payload = $this->instantFormPayload();
        $payload['entry'][0]['lead_source'] = 'DIRECT_MESSAGE';

        $this->postSignedWebhook($payload)->assertOk();

        $this->assertDatabaseCount('tiktok_lead_submissions', 0);
        Queue::assertNothingPushed();
    }

    public function test_org_a_webhook_cannot_create_org_b_submission(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111', 'token-a');
        $this->createConnectedTikTok($this->organizationB, $this->adminB, '2222222222', 'token-b');

        $this->postSignedWebhook($this->instantFormPayload('lead-100', '1111111111'))->assertOk();

        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'organization_id' => $this->organizationA->id,
            'advertiser_id' => '1111111111',
        ]);
        $this->assertDatabaseMissing('tiktok_lead_submissions', [
            'organization_id' => $this->organizationB->id,
        ]);
    }

    public function test_processed_duplicate_does_not_dispatch_again(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        TikTokLeadSubmission::query()->create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '1111111111',
            'tiktok_lead_id' => 'lead-100',
            'tiktok_page_id' => 'page-year-7',
            'status' => TikTokLeadSubmissionStatus::Processed,
            'received_at' => now(),
            'processed_at' => now(),
        ]);

        $this->postSignedWebhook($this->instantFormPayload())->assertOk();

        $this->assertDatabaseCount('tiktok_lead_submissions', 1);
        Queue::assertNothingPushed();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postSignedWebhook(array $payload, ?string $signature = null, int $status = 200)
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $this->assertIsString($body);

        return $this->call('POST', '/webhooks/tiktok/leads', [], [], [], [
            'HTTP_X_OPEN_SIGNATURE' => $signature ?? $this->officialSignature($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertStatus($status);
    }

    private function officialSignature(string $body): string
    {
        return hash_hmac('sha256', (new TikTokWebhookAuthenticator)->unicodeEscape($body), 'tiktok-test-secret');
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
            'request_id' => 'req-lead-1',
        ];
    }

    private function createConnectedTikTok(
        Organization $organization,
        Admin $admin,
        string $advertiserId = '1111111111',
        string $token = 'token-a',
    ): IntegrationConnection {
        return IntegrationConnection::create([
            'organization_id' => $organization->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => $advertiserId,
            'external_account_name' => 'School Ads Account',
            'access_token' => $token,
            'connected_by' => $admin->id,
        ]);
    }
}
