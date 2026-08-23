<?php

namespace Tests\Feature\EmailMarketing;

use App\Enums\EmailMarketing\CampaignStatus;
use App\Enums\EmailMarketing\RecipientStatus;
use App\Jobs\EmailMarketing\SendCampaignRecipientJob;
use App\Models\Crm\Lead;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\CampaignRecipient;
use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\Suppression;
use App\Services\Crm\CrmTransactionalMailService;
use App\Services\EmailMarketing\CampaignPreflightService;
use App\Services\EmailMarketing\Delivery\DeliveryResult;
use App\Services\EmailMarketing\Delivery\EmailDeliveryService;
use App\Services\EmailMarketing\Delivery\OutboundEmail;
use App\Services\EmailMarketing\Delivery\SendGridMailProvider;
use App\Services\EmailMarketing\SuppressionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class SendGridCampaignPhaseDTest extends EmailMarketingTestCase
{
    private function mailbox(array $extra = []): MailboxSetting
    {
        return MailboxSetting::create(array_merge([
            'organization_id' => $this->organizationA->id,
            'is_enabled' => true,
            'from_name' => 'Org A',
            'from_email' => 'noreply@orga.test',
            'smtp_host' => 'smtp.example.test',
            'smtp_port' => 587,
            'smtp_username' => 'user',
            'smtp_password' => 'secret',
            'tracking_enabled' => true,
            'open_tracking' => true,
            'click_tracking' => true,
            'sendgrid_asm_group_id' => 4242,
        ], $extra));
    }

    private function campaign(array $extra = []): Campaign
    {
        return Campaign::create(array_merge([
            'organization_id' => $this->organizationA->id,
            'name' => 'Phase D Campaign',
            'subject' => 'Hello {{name}}',
            'body_html' => '<p>Hi {{name}}</p><p><a href="{{unsubscribe_url}}">Unsub</a></p>',
            'status' => CampaignStatus::Draft->value,
            'recipient_source' => 'manual',
            'recipient_filters' => [
                'manual_emails' => "one@example.com\none@example.com\nbad-email\ntwo@example.com",
            ],
            'tracking_enabled' => true,
            'created_by' => $this->adminA->id,
        ], $extra));
    }

    public function test_preflight_dedupes_and_excludes_suppressions(): void
    {
        $this->mailbox();
        Suppression::create([
            'organization_id' => $this->organizationA->id,
            'email' => 'two@example.com',
            'reason' => 'sendgrid_unsubscribe',
            'unsubscribed_at' => now(),
        ]);
        Suppression::create([
            'organization_id' => $this->organizationA->id,
            'email' => 'bounce@example.com',
            'reason' => 'sendgrid_bounce',
        ]);

        $summary = app(CampaignPreflightService::class)->summarize($this->organizationA->id, [
            'source' => 'manual',
            'manual_emails' => "one@example.com\none@example.com\nbad\ntwo@example.com\nbounce@example.com",
        ]);

        $this->assertSame(5, $summary['selected']);
        $this->assertSame(4, $summary['valid']);
        $this->assertSame(1, $summary['invalid']);
        $this->assertSame(1, $summary['duplicates']);
        $this->assertSame(1, $summary['unsubscribed']);
        $this->assertSame(1, $summary['suppressed']);
        $this->assertSame(1, $summary['eligible']);
        $this->assertSame('one@example.com', $summary['eligible_rows']->first()['email']);
    }

    public function test_campaign_uses_delivery_service_with_marketing_category_and_asm(): void
    {
        Mail::fake();
        Http::fake();
        $this->mailbox();
        $campaign = $this->campaign([
            'recipient_filters' => ['manual_emails' => 'eligible@example.com'],
        ]);

        /** @var OutboundEmail|null $captured */
        $captured = null;
        $this->mock(EmailDeliveryService::class, function ($mock) use (&$captured) {
            $mock->shouldReceive('send')->once()->andReturnUsing(function (OutboundEmail $email) use (&$captured) {
                $captured = $email;

                return DeliveryResult::accepted('laravel', 'msg-1', 'processed');
            });
        });

        $this->actingAsEmAdmin()
            ->post(route('admin.email.campaigns.send', $campaign))
            ->assertRedirect();

        $recipient = CampaignRecipient::query()->where('campaign_id', $campaign->id)->first();
        $this->assertNotNull($recipient);
        (new SendCampaignRecipientJob($recipient->id))->handle(
            app(\App\Services\EmailMarketing\MailConfigResolver::class),
            app(\App\Services\EmailMarketing\TemplateRenderer::class),
            app(\App\Services\EmailMarketing\HtmlSanitizer::class),
            app(EmailDeliveryService::class),
            app(SuppressionService::class),
        );

        $this->assertSame('marketing', $captured->category);
        $this->assertSame(4242, $captured->asmGroupId);
        $this->assertArrayHasKey('correlation_uuid', $captured->customArgs);
        Http::assertNothingSent();
        $this->assertSame(RecipientStatus::Sent->value, $recipient->fresh()->status);
    }

    public function test_sendgrid_provider_includes_asm_group_when_set(): void
    {
        config([
            'sendgrid.api_key' => 'sg.test-key',
            'sendgrid.api_base' => 'https://api.sendgrid.com',
        ]);
        Http::fake([
            'api.sendgrid.com/v3/mail/send' => Http::response(null, 202, ['X-Message-Id' => 'sg-1']),
        ]);

        $result = app(SendGridMailProvider::class)->send(new OutboundEmail(
            fromEmail: 'from@example.com',
            fromName: 'From',
            to: ['to@example.com'],
            subject: 'Subj',
            html: '<p>Hi</p>',
            category: 'marketing',
            asmGroupId: 99,
        ));

        $this->assertTrue($result->accepted);
        Http::assertSent(function ($request) {
            $data = $request->data();

            return ($data['asm']['group_id'] ?? null) === 99
                && in_array('marketing', $data['categories'] ?? [], true);
        });
    }

    public function test_job_rechecks_suppression_and_skips_without_provider_call(): void
    {
        $this->mailbox();
        $campaign = $this->campaign([
            'status' => CampaignStatus::Sending->value,
            'recipient_filters' => ['manual_emails' => 'later@example.com'],
        ]);
        $recipient = CampaignRecipient::create([
            'organization_id' => $this->organizationA->id,
            'campaign_id' => $campaign->id,
            'email' => 'later@example.com',
            'status' => RecipientStatus::Queued->value,
            'tracking_token' => Str::random(40),
        ]);

        Suppression::create([
            'organization_id' => $this->organizationA->id,
            'email' => 'later@example.com',
            'reason' => 'sendgrid_spamreport',
        ]);

        $this->mock(EmailDeliveryService::class, function ($mock) {
            $mock->shouldReceive('send')->never();
        });

        (new SendCampaignRecipientJob($recipient->id))->handle(
            app(\App\Services\EmailMarketing\MailConfigResolver::class),
            app(\App\Services\EmailMarketing\TemplateRenderer::class),
            app(\App\Services\EmailMarketing\HtmlSanitizer::class),
            app(EmailDeliveryService::class),
            app(SuppressionService::class),
        );

        $this->assertSame(RecipientStatus::Skipped->value, $recipient->fresh()->status);
        $this->assertStringContainsString('Suppressed', (string) $recipient->fresh()->error_message);
    }

    public function test_marketing_unsubscribe_does_not_block_transactional_email(): void
    {
        Mail::fake();
        $this->mailbox();
        Suppression::create([
            'organization_id' => $this->organizationA->id,
            'email' => 'lead-tx@example.com',
            'reason' => 'sendgrid_unsubscribe',
            'unsubscribed_at' => now(),
        ]);

        $this->assertTrue(app(SuppressionService::class)->isMarketingBlocked($this->organizationA->id, 'lead-tx@example.com'));
        $this->assertFalse(app(SuppressionService::class)->isTransactionalBlocked($this->organizationA->id, 'lead-tx@example.com'));

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Tx',
            'email' => 'lead-tx@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->mock(EmailDeliveryService::class, function ($mock) {
            $mock->shouldReceive('send')->once()->andReturn(DeliveryResult::accepted('laravel', 't1', 'processed'));
        });

        $result = app(CrmTransactionalMailService::class)->sendLeadEmail($lead, 'Hello', 'Body', $this->adminA->id);
        $this->assertTrue($result->accepted);
    }

    public function test_provider_events_update_campaign_metrics_idempotently(): void
    {
        $this->mailbox();
        $uuid = (string) Str::uuid();
        $campaign = $this->campaign(['status' => CampaignStatus::Sending->value]);
        $recipient = CampaignRecipient::create([
            'organization_id' => $this->organizationA->id,
            'campaign_id' => $campaign->id,
            'email' => 'metrics@example.com',
            'status' => RecipientStatus::Sent->value,
            'correlation_uuid' => $uuid,
            'tracking_token' => Str::random(40),
            'sent_at' => now(),
            'provider_status' => 'processed',
        ]);

        $payload = [
            ['event' => 'delivered', 'email' => 'metrics@example.com', 'timestamp' => now()->timestamp, 'sg_event_id' => 'd1', 'correlation_uuid' => $uuid],
            ['event' => 'open', 'email' => 'metrics@example.com', 'timestamp' => now()->timestamp, 'sg_event_id' => 'o1', 'correlation_uuid' => $uuid],
            ['event' => 'click', 'email' => 'metrics@example.com', 'timestamp' => now()->timestamp, 'sg_event_id' => 'c1', 'correlation_uuid' => $uuid, 'url' => 'https://example.com/path?secret=1'],
        ];

        $this->withHeaders(['X-Test-SendGrid-Event' => '1'])
            ->postJson('/webhooks/sendgrid/events', $payload)
            ->assertOk()
            ->assertJsonPath('processed', 3);

        // Retry should not double-count.
        $this->withHeaders(['X-Test-SendGrid-Event' => '1'])
            ->postJson('/webhooks/sendgrid/events', $payload)
            ->assertOk()
            ->assertJsonPath('processed', 0);

        $recipient->refresh();
        $campaign->refresh();
        $this->assertNotNull($recipient->delivered_at);
        $this->assertTrue($recipient->is_opened);
        $this->assertTrue($recipient->is_clicked);
        $this->assertSame(1, $campaign->opened_count);
        $this->assertSame(1, $campaign->clicked_count);

        $this->actingAsEmAdmin()
            ->get(route('admin.email.campaigns.show', $campaign))
            ->assertOk()
            ->assertSee('Delivered')
            ->assertSee('Opened')
            ->assertSee('Clicked');
    }

    public function test_unsubscribe_and_group_resubscribe_are_org_scoped(): void
    {
        $this->mailbox();
        $uuid = (string) Str::uuid();
        CampaignRecipient::create([
            'organization_id' => $this->organizationA->id,
            'campaign_id' => $this->campaign(['status' => CampaignStatus::Sent->value])->id,
            'email' => 'unsub@example.com',
            'status' => RecipientStatus::Sent->value,
            'correlation_uuid' => $uuid,
            'tracking_token' => Str::random(40),
            'sent_at' => now(),
        ]);

        $this->withHeaders(['X-Test-SendGrid-Event' => '1'])
            ->postJson('/webhooks/sendgrid/events', [[
                'event' => 'group_unsubscribe',
                'email' => 'unsub@example.com',
                'timestamp' => now()->timestamp,
                'sg_event_id' => 'u1',
                'correlation_uuid' => $uuid,
                'asm_group_id' => 4242,
            ]])
            ->assertOk();

        $row = Suppression::query()
            ->where('organization_id', $this->organizationA->id)
            ->where('email', 'unsub@example.com')
            ->first();
        $this->assertNotNull($row->unsubscribed_at);
        $this->assertTrue(app(SuppressionService::class)->isMarketingBlocked($this->organizationA->id, 'unsub@example.com'));
        $this->assertFalse(app(SuppressionService::class)->isMarketingBlocked($this->organizationB->id, 'unsub@example.com'));

        $this->withHeaders(['X-Test-SendGrid-Event' => '1'])
            ->postJson('/webhooks/sendgrid/events', [[
                'event' => 'group_resubscribe',
                'email' => 'unsub@example.com',
                'timestamp' => now()->addMinute()->timestamp,
                'sg_event_id' => 'r1',
                'correlation_uuid' => $uuid,
                'asm_group_id' => 4242,
            ]])
            ->assertOk();

        $this->assertFalse(app(SuppressionService::class)->isMarketingBlocked($this->organizationA->id, 'unsub@example.com'));
    }

    public function test_bounce_event_blocks_future_marketing_sends(): void
    {
        $this->mailbox();
        $uuid = (string) Str::uuid();
        CampaignRecipient::create([
            'organization_id' => $this->organizationA->id,
            'campaign_id' => $this->campaign(['status' => CampaignStatus::Sent->value])->id,
            'email' => 'hard@example.com',
            'status' => RecipientStatus::Sent->value,
            'correlation_uuid' => $uuid,
            'tracking_token' => Str::random(40),
            'sent_at' => now(),
        ]);

        $this->withHeaders(['X-Test-SendGrid-Event' => '1'])
            ->postJson('/webhooks/sendgrid/events', [[
                'event' => 'bounce',
                'email' => 'hard@example.com',
                'timestamp' => now()->timestamp,
                'sg_event_id' => 'b1',
                'correlation_uuid' => $uuid,
                'reason' => '550 user unknown',
            ]])
            ->assertOk();

        $this->assertTrue(app(SuppressionService::class)->isMarketingBlocked($this->organizationA->id, 'hard@example.com'));
        $this->assertSame('bounce', CampaignRecipient::query()->where('correlation_uuid', $uuid)->value('provider_status'));
    }

    public function test_campaign_analytics_are_tenant_isolated(): void
    {
        $foreign = Campaign::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign',
            'subject' => 'Secret',
            'body_html' => '<p>x</p>',
            'status' => CampaignStatus::Sent->value,
            'recipient_source' => 'manual',
            'recipient_count' => 1,
            'sent_count' => 1,
            'opened_count' => 1,
        ]);

        $this->actingAsEmAdmin($this->adminA)
            ->get(route('admin.email.campaigns.show', $foreign))
            ->assertNotFound();
    }

    public function test_dispatch_still_queues_jobs_not_inline_http_send(): void
    {
        Queue::fake();
        $this->mailbox();
        $campaign = $this->campaign([
            'recipient_filters' => ['manual_emails' => 'queue@example.com'],
        ]);

        $this->actingAsEmAdmin()
            ->post(route('admin.email.campaigns.send', $campaign))
            ->assertRedirect();

        Queue::assertPushed(SendCampaignRecipientJob::class);
    }
}
