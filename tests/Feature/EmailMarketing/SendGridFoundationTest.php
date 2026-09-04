<?php

namespace Tests\Feature\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\ProviderEvent;
use App\Services\EmailMarketing\Delivery\EmailDeliveryService;
use App\Services\EmailMarketing\Delivery\OutboundEmail;
use App\Services\EmailMarketing\Delivery\SendGridMailProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendGridFoundationTest extends EmailMarketingTestCase
{
    private function enableMailbox(): MailboxSetting
    {
        return MailboxSetting::create([
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
        ]);
    }

    public function test_compose_still_sends_through_delivery_service_without_real_sendgrid(): void
    {
        Mail::fake();
        $this->enableMailbox();

        $this->actingAsEmAdmin()
            ->post(route('admin.email.send'), [
                'to' => 'customer@example.com',
                'subject' => 'Hello',
                'body_html' => '<p>Hi</p>',
            ])
            ->assertRedirect(route('admin.email.sent'));

        $message = Message::query()->where('organization_id', $this->organizationA->id)->sent()->first();
        $this->assertNotNull($message);
        $this->assertNotNull($message->correlation_uuid);
        $this->assertSame('sent', $message->delivery_status);
        $this->assertSame('laravel', $message->provider);
    }

    public function test_sendgrid_provider_posts_to_api_when_configured(): void
    {
        config([
            'sendgrid.api_key' => 'sg.test-key',
            'sendgrid.api_base' => 'https://api.sendgrid.com',
        ]);

        Http::fake([
            'api.sendgrid.com/v3/mail/send' => Http::response(null, 202, ['X-Message-Id' => 'sg-msg-1']),
        ]);

        $provider = app(SendGridMailProvider::class);
        $result = $provider->send(new OutboundEmail(
            fromEmail: 'from@example.com',
            fromName: 'From',
            to: ['to@example.com'],
            subject: 'Subj',
            html: '<p>Body</p>',
            customArgs: ['correlation_uuid' => (string) Str::uuid()],
        ));

        $this->assertTrue($result->accepted);
        $this->assertSame('sendgrid', $result->provider);
        $this->assertSame('sg-msg-1', $result->providerMessageId);
        $this->assertSame('accepted', $result->providerStatus);
        Http::assertSentCount(1);
    }

    public function test_mailbox_settings_store_tenant_sendgrid_credentials_encrypted(): void
    {
        $apiKey = 'SG.'.str_repeat('a', 66);
        $webhookKey = 'tenant-webhook-public-key';

        $this->actingAsEmAdmin()
            ->put(route('admin.email.mailbox.settings.update'), [
                'is_enabled' => '1',
                'from_name' => 'Org A',
                'from_email' => 'mail@orga.test',
                'sendgrid_api_key' => $apiKey,
                'sendgrid_event_webhook_public_key' => $webhookKey,
            ])
            ->assertSessionHasNoErrors();

        $settings = MailboxSetting::query()
            ->where('organization_id', $this->organizationA->id)
            ->firstOrFail();

        $this->assertSame($apiKey, $settings->sendgrid_api_key);
        $this->assertSame($webhookKey, $settings->sendgrid_event_webhook_public_key);
        $this->assertNotSame($apiKey, $settings->getRawOriginal('sendgrid_api_key'));
        $this->assertNotSame($webhookKey, $settings->getRawOriginal('sendgrid_event_webhook_public_key'));
    }

    public function test_sendgrid_provider_prefers_the_tenant_api_key(): void
    {
        config(['sendgrid.api_key' => 'SG.global-key']);
        Http::fake([
            'api.sendgrid.com/v3/mail/send' => Http::response(null, 202, ['X-Message-Id' => 'tenant-msg']),
        ]);

        $provider = app(SendGridMailProvider::class);
        $provider->send(new OutboundEmail(
            fromEmail: 'from@example.com',
            fromName: 'From',
            to: ['to@example.com'],
            subject: 'Tenant key',
            html: '<p>Body</p>',
        ), 'SG.tenant-key');

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer SG.tenant-key'));
    }

    public function test_event_webhook_rejects_unauthenticated_requests(): void
    {
        $this->postJson('/webhooks/sendgrid/events', [
            ['event' => 'delivered', 'email' => 'a@b.com'],
        ])->assertUnauthorized();
    }

    public function test_event_webhook_is_idempotent_and_updates_message(): void
    {
        $this->enableMailbox();
        $uuid = (string) Str::uuid();

        $message = Message::create([
            'organization_id' => $this->organizationA->id,
            'folder' => 'sent',
            'direction' => 'outbound',
            'message_id' => 'local-'.$uuid,
            'correlation_uuid' => $uuid,
            'to' => 'customer@example.com',
            'subject' => 'Tracked',
            'delivery_status' => 'sent',
            'provider' => 'sendgrid',
            'provider_status' => 'processed',
        ]);

        $payload = [[
            'email' => 'customer@example.com',
            'timestamp' => now()->timestamp,
            'event' => 'delivered',
            'sg_event_id' => 'evt-1',
            'sg_message_id' => 'sg-abc.filter',
            'correlation_uuid' => $uuid,
        ]];

        $this->withHeaders(['X-Test-SendGrid-Event' => '1'])
            ->postJson('/webhooks/sendgrid/events', $payload)
            ->assertOk()
            ->assertJsonPath('processed', 1);

        $this->withHeaders(['X-Test-SendGrid-Event' => '1'])
            ->postJson('/webhooks/sendgrid/events', $payload)
            ->assertOk()
            ->assertJsonPath('processed', 0);

        $message->refresh();
        $this->assertSame('delivered', $message->provider_status);
        $this->assertNotNull($message->delivered_at);
        $this->assertSame(1, ProviderEvent::query()->count());
    }

    public function test_inbound_webhook_requires_auth_and_stores_sanitized_message(): void
    {
        MailboxSetting::create([
            'organization_id' => $this->organizationA->id,
            'is_enabled' => true,
            'from_email' => 'inbox@orga.test',
            'inbound_enabled' => true,
            'inbound_domain' => 'inbound.orga.test',
        ]);

        $this->post('/webhooks/sendgrid/inbound', [
            'to' => 'team@inbound.orga.test',
            'from' => 'Lead Person <lead@example.com>',
            'subject' => 'Hello',
            'text' => 'Plain text',
            'html' => '<p>Hi</p><script>alert(1)</script>',
        ])->assertUnauthorized();

        $this->withHeaders(['X-Test-SendGrid-Inbound' => '1'])
            ->post('/webhooks/sendgrid/inbound', [
                'to' => 'team@inbound.orga.test',
                'from' => 'Lead Person <lead@example.com>',
                'subject' => 'Hello',
                'text' => 'Plain text',
                'html' => '<p>Hi</p><script>alert(1)</script>',
            ])
            ->assertCreated();

        $message = Message::query()->where('organization_id', $this->organizationA->id)->inbox()->first();
        $this->assertNotNull($message);
        $this->assertSame('lead@example.com', $message->from_email);
        $this->assertStringNotContainsString('<script>', (string) $message->body_html);
        $this->assertStringContainsString('Hi', (string) $message->body_html);
    }

    public function test_cross_org_inbound_thread_token_cannot_attach_wrong_org(): void
    {
        $thread = (string) Str::uuid();

        Message::create([
            'organization_id' => $this->organizationB->id,
            'folder' => 'sent',
            'direction' => 'outbound',
            'message_id' => 'local-b-'.$thread,
            'correlation_uuid' => $thread,
            'thread_id' => $thread,
            'to' => 'x@example.com',
            'subject' => 'B only',
            'delivery_status' => 'sent',
        ]);

        MailboxSetting::create([
            'organization_id' => $this->organizationA->id,
            'is_enabled' => true,
            'from_email' => 'a@orga.test',
            'inbound_enabled' => true,
            'inbound_domain' => 'inbound.orga.test',
        ]);

        // Addressed to org A inbound domain with reply+ token belonging to org B.
        $this->withHeaders(['X-Test-SendGrid-Inbound' => '1'])
            ->post('/webhooks/sendgrid/inbound', [
                'to' => 'reply+'.$thread.'@inbound.orga.test',
                'from' => 'outsider@example.com',
                'subject' => 'Attempt',
                'text' => 'Nope',
            ])
            ->assertCreated();

        $stored = Message::query()->inbox()->latest('id')->first();
        $this->assertSame($this->organizationA->id, $stored->organization_id);
        // Parent from org B must not be linked.
        $this->assertNull($stored->parent_id);
    }

    public function test_delivery_service_uses_laravel_bridge_in_testing(): void
    {
        Mail::fake();
        $settings = $this->enableMailbox();
        config(['sendgrid.api_key' => 'sg.should-not-call']);

        $service = app(EmailDeliveryService::class);
        $this->assertSame('laravel', $service->activeProviderName($settings));

        Http::fake();
        $result = $service->send(new OutboundEmail(
            fromEmail: 'noreply@orga.test',
            fromName: 'Org A',
            to: ['to@example.com'],
            subject: 'T',
            html: '<p>x</p>',
        ), $settings);

        $this->assertTrue($result->accepted);
        $this->assertSame('laravel', $result->provider);
        Http::assertNothingSent();
    }
}
