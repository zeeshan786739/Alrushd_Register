<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Jobs\Integrations\ProcessMetaLeadJob;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\MetaLeadSubmission;
use Database\Seeders\IntegrationPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MetaLeadWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IntegrationPermissionsSeeder::class);

        config([
            'integrations.meta.webhook_verify_token' => 'test-verify-token',
        ]);
    }

    public function test_webhook_verification_returns_challenge_when_token_matches(): void
    {
        $this->get('/webhooks/meta/leads?'.http_build_query([
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'test-verify-token',
            'hub_challenge' => 'challenge-123',
        ]))
            ->assertOk()
            ->assertSee('challenge-123');
    }

    public function test_webhook_verification_rejects_invalid_token(): void
    {
        $this->get('/webhooks/meta/leads?'.http_build_query([
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'wrong-token',
            'hub_challenge' => 'challenge-123',
        ]))
            ->assertForbidden();
    }

    public function test_leadgen_webhook_queues_processing_job_for_connected_page(): void
    {
        Queue::fake();

        $organization = \App\Models\Organization::create([
            'name' => 'Test School',
            'slug' => 'test-school',
            'is_active' => true,
        ]);

        IntegrationConnection::create([
            'organization_id' => $organization->id,
            'platform' => IntegrationPlatform::Facebook,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => 'page-123',
            'external_account_name' => 'Test Page',
            'access_token' => 'page-token',
        ]);

        $this->postJson('/webhooks/meta/leads', [
            'object' => 'page',
            'entry' => [[
                'id' => 'page-123',
                'changes' => [[
                    'field' => 'leadgen',
                    'value' => [
                        'leadgen_id' => 'lead-999',
                        'page_id' => 'page-123',
                        'form_id' => 'form-456',
                    ],
                ]],
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('meta_lead_submissions', [
            'meta_leadgen_id' => 'lead-999',
            'organization_id' => $organization->id,
        ]);

        $submission = MetaLeadSubmission::where('meta_leadgen_id', 'lead-999')->first();
        Queue::assertPushed(ProcessMetaLeadJob::class, fn ($job) => $job->metaLeadSubmissionId === $submission->id);
    }
}
