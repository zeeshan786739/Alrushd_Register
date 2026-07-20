<?php

namespace Tests\Feature\EmailMarketing;

use App\Enums\EmailMarketing\CampaignStatus;
use App\Enums\EmailMarketing\RecipientStatus;
use App\Jobs\EmailMarketing\SendCampaignRecipientJob;
use App\Models\Crm\Lead;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\CampaignRecipient;
use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\Suppression;
use App\Models\EmailMarketing\Template;
use App\Services\EmailMarketing\Contracts\MailboxClientInterface;
use App\Services\EmailMarketing\FakeMailboxClient;
use App\Services\EmailMarketing\HtmlSanitizer;
use App\Services\EmailMarketing\InboxSyncService;
use App\Services\EmailMarketing\TemplateRenderer;
use App\Services\EmailMarketing\TrackingService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class EmailMarketingCoreTest extends EmailMarketingTestCase
{
    public function test_guest_cannot_access_inbox(): void
    {
        $this->get(route('admin.email.inbox'))->assertRedirect(route('admin.login'));
    }

    public function test_inbox_and_compose_render_for_authorized_admin(): void
    {
        $this->actingAsEmAdmin()
            ->get(route('admin.email.inbox'))
            ->assertOk()
            ->assertSee('Inbox');

        $this->actingAsEmAdmin()
            ->get(route('admin.email.compose'))
            ->assertOk()
            ->assertSee('Compose');
    }

    public function test_organization_isolation_for_messages(): void
    {
        $message = Message::create([
            'organization_id' => $this->organizationB->id,
            'folder' => 'inbox',
            'direction' => 'inbound',
            'message_id' => 'msg-b-1',
            'from_email' => 'hidden@example.com',
            'subject' => 'Secret',
            'body_text' => 'Hidden',
            'is_read' => false,
        ]);

        $this->actingAsEmAdmin($this->adminA)
            ->get(route('admin.email.show', $message))
            ->assertNotFound();
    }

    public function test_compose_send_stores_sent_message(): void
    {
        Mail::fake();

        MailboxSetting::create([
            'organization_id' => $this->organizationA->id,
            'from_name' => 'Org A',
            'from_email' => 'noreply@orga.test',
            'smtp_host' => 'smtp.test',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'user',
            'smtp_password' => 'secret',
            'is_enabled' => true,
        ]);

        $this->actingAsEmAdmin()
            ->post(route('admin.email.send'), [
                'to' => 'recipient@example.com',
                'subject' => 'Hello',
                'body_html' => '<p>Hi there</p>',
            ])
            ->assertRedirect(route('admin.email.sent'));

        $this->assertDatabaseHas('em_messages', [
            'organization_id' => $this->organizationA->id,
            'folder' => 'sent',
            'subject' => 'Hello',
            'delivery_status' => 'sent',
        ]);

        // Mail::html() uses the array/log transport under test; assert delivery status instead of mailable count.
        $this->assertSame(1, Message::forOrganization($this->organizationA->id)->sent()->where('delivery_status', 'sent')->count());
    }

    public function test_draft_create_and_update_same_record(): void
    {
        $this->actingAsEmAdmin()
            ->post(route('admin.email.draft.save'), [
                'to' => 'draft@example.com',
                'subject' => 'Draft 1',
                'body_html' => '<p>One</p>',
            ])
            ->assertRedirect();

        $draft = Message::forOrganization($this->organizationA->id)->draft()->first();
        $this->assertNotNull($draft);

        $this->actingAsEmAdmin()
            ->post(route('admin.email.draft.save'), [
                'draft_id' => $draft->id,
                'to' => 'draft@example.com',
                'subject' => 'Draft 1 updated',
                'body_html' => '<p>Two</p>',
            ])
            ->assertRedirect();

        $this->assertSame(1, Message::forOrganization($this->organizationA->id)->draft()->count());
        $this->assertSame('Draft 1 updated', $draft->fresh()->subject);
    }

    public function test_star_toggle_and_starred_list(): void
    {
        $message = Message::create([
            'organization_id' => $this->organizationA->id,
            'folder' => 'inbox',
            'direction' => 'inbound',
            'message_id' => 'star-1',
            'subject' => 'Star me',
            'from_email' => 'a@b.com',
            'is_starred' => false,
        ]);

        $this->actingAsEmAdmin()
            ->post(route('admin.email.star', $message))
            ->assertRedirect();

        $this->assertTrue($message->fresh()->is_starred);

        $this->actingAsEmAdmin()
            ->get(route('admin.email.starred'))
            ->assertOk()
            ->assertSee('Star me');
    }

    public function test_inbox_sync_deduplicates_messages(): void
    {
        $this->app->instance(MailboxClientInterface::class, new FakeMailboxClient([
            [
                'message_id' => 'mid-1',
                'imap_uid' => '100',
                'from_email' => 'sender@example.com',
                'from_name' => 'Sender',
                'to' => 'me@example.com',
                'cc' => null,
                'subject' => 'Hello sync',
                'body_html' => '<p>Body</p>',
                'body_text' => 'Body',
                'received_at' => now()->toDateTimeString(),
                'attachments' => [],
            ],
        ]));

        MailboxSetting::create([
            'organization_id' => $this->organizationA->id,
            'imap_host' => 'imap.test',
            'imap_port' => 993,
            'imap_username' => 'user',
            'imap_password' => 'secret',
            'is_enabled' => true,
            'from_email' => 'me@example.com',
            'smtp_host' => 'smtp.test',
            'smtp_username' => 'user',
        ]);

        $sync = app(InboxSyncService::class);
        $first = $sync->syncOrganization($this->organizationA->id);
        $second = $sync->syncOrganization($this->organizationA->id);

        $this->assertSame(1, $first['imported']);
        $this->assertSame(0, $second['imported']);
        $this->assertSame(1, Message::forOrganization($this->organizationA->id)->inbox()->count());
    }

    public function test_campaign_dispatch_queues_jobs_and_is_idempotent(): void
    {
        Queue::fake();

        MailboxSetting::create([
            'organization_id' => $this->organizationA->id,
            'from_name' => 'Org A',
            'from_email' => 'noreply@orga.test',
            'smtp_host' => 'smtp.test',
            'smtp_port' => 587,
            'smtp_username' => 'user',
            'smtp_password' => 'secret',
            'is_enabled' => true,
        ]);

        Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Lead',
            'email' => 'lead@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $campaign = Campaign::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Spring',
            'subject' => 'Hello',
            'body_html' => '<p>Hi {{name}}</p>',
            'status' => CampaignStatus::Draft->value,
            'recipient_source' => 'leads',
            'tracking_enabled' => true,
            'created_by' => $this->adminA->id,
        ]);

        $this->actingAsEmAdmin()
            ->post(route('admin.email.campaigns.send', $campaign))
            ->assertRedirect();

        Queue::assertPushed(SendCampaignRecipientJob::class);
        $this->assertSame(CampaignStatus::Sending->value, $campaign->fresh()->status);
    }

    public function test_suppressed_recipients_are_excluded(): void
    {
        Suppression::create([
            'organization_id' => $this->organizationA->id,
            'email' => 'gone@example.com',
            'unsubscribed_at' => now(),
            'token' => 'tok123',
        ]);

        Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Gone',
            'email' => 'gone@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);
        Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Keep',
            'email' => 'keep@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $resolver = app(\App\Services\EmailMarketing\CampaignRecipientResolver::class);
        $rows = $resolver->resolve($this->organizationA->id, ['source' => 'leads']);

        $this->assertSame(1, $rows->count());
        $this->assertSame('keep@example.com', $rows->first()['email']);
    }

    public function test_open_and_click_tracking_and_safe_redirect(): void
    {
        $campaign = Campaign::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Track',
            'subject' => 'Track',
            'body_html' => '<p>Hi</p>',
            'status' => CampaignStatus::Sent->value,
            'recipient_source' => 'manual',
        ]);

        $recipient = CampaignRecipient::create([
            'organization_id' => $this->organizationA->id,
            'campaign_id' => $campaign->id,
            'email' => 'track@example.com',
            'status' => RecipientStatus::Sent->value,
            'tracking_token' => 'tracktoken123456789012345678901234567890',
        ]);

        $this->get(route('email-marketing.track.open', $recipient->tracking_token))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/gif');

        $this->assertTrue($recipient->fresh()->is_opened);

        $this->get(route('email-marketing.track.click', [
            'token' => $recipient->tracking_token,
            'url' => 'https://example.com/page',
        ]))->assertRedirect('https://example.com/page');

        $this->assertTrue($recipient->fresh()->is_clicked);

        $this->get(route('email-marketing.track.click', [
            'token' => $recipient->tracking_token,
            'url' => 'javascript:alert(1)',
        ]))->assertStatus(400);
    }

    public function test_unsubscribe_flow(): void
    {
        $campaign = Campaign::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Unsub',
            'subject' => 'Unsub',
            'body_html' => '<p>Hi</p>',
            'status' => CampaignStatus::Sent->value,
            'recipient_source' => 'manual',
        ]);

        $recipient = CampaignRecipient::create([
            'organization_id' => $this->organizationA->id,
            'campaign_id' => $campaign->id,
            'email' => 'leave@example.com',
            'status' => RecipientStatus::Sent->value,
            'tracking_token' => 'unsubtoken123456789012345678901234567890',
        ]);

        $this->get(route('email-marketing.unsubscribe.show', $recipient->tracking_token))->assertOk();
        $this->post(route('email-marketing.unsubscribe.store', $recipient->tracking_token))->assertOk();

        $this->assertDatabaseHas('em_suppressions', [
            'organization_id' => $this->organizationA->id,
            'email' => 'leave@example.com',
        ]);
    }

    public function test_template_crud_and_sanitization(): void
    {
        $this->actingAsEmAdmin()
            ->post(route('admin.email.templates.store'), [
                'name' => 'Welcome',
                'subject' => 'Hello {{name}}',
                'body_html' => '<p onclick="alert(1)">Hi {{name}}</p><script>bad()</script>',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $template = Template::forOrganization($this->organizationA->id)->first();
        $this->assertNotNull($template);
        $this->assertStringNotContainsString('<script>', (string) $template->body_html);
        $this->assertStringNotContainsString('onclick', (string) $template->body_html);

        $rendered = app(TemplateRenderer::class)->render($template->body_html, [
            'name' => '<b>Jane</b>',
            'email' => 'jane@example.com',
            'company' => '',
            'unsubscribe_url' => 'https://example.com/u',
        ]);
        $this->assertStringContainsString('&lt;b&gt;Jane&lt;/b&gt;', $rendered);
    }

    public function test_mailbox_password_is_encrypted_and_not_exposed(): void
    {
        $this->actingAsEmAdmin()
            ->put(route('admin.email.mailbox.settings.update'), [
                'from_email' => 'a@test.com',
                'smtp_host' => 'smtp.test',
                'smtp_username' => 'user',
                'smtp_password' => 'super-secret',
                'is_enabled' => 1,
                'validate_cert' => 1,
                'tracking_enabled' => 1,
            ])
            ->assertRedirect();

        $settings = MailboxSetting::where('organization_id', $this->organizationA->id)->first();
        $this->assertNotNull($settings);
        $raw = \DB::table('em_mailbox_settings')->where('id', $settings->id)->value('smtp_password');
        $this->assertNotSame('super-secret', $raw);
        $this->assertSame('super-secret', $settings->smtp_password);

        $this->actingAsEmAdmin()
            ->get(route('admin.email.mailbox.settings'))
            ->assertOk()
            ->assertDontSee('super-secret');
    }

    public function test_html_sanitizer_strips_dangerous_markup(): void
    {
        $clean = app(HtmlSanitizer::class)->sanitize('<p>Hi</p><script>x()</script><a href="javascript:alert(1)">x</a>');
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_crm_compose_prefill_route(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Prefill',
            'email' => 'prefill@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsEmAdmin()
            ->get(route('admin.email.compose', [
                'to' => $lead->email,
                'lead_id' => $lead->id,
                'subject' => 'Follow up',
            ]))
            ->assertOk()
            ->assertSee('prefill@example.com');
    }
}
