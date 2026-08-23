<?php

namespace Tests\Feature\EmailMarketing;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\Lead;
use App\Models\Crm\Quotation;
use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\MessageAttachment;
use App\Services\EmailMarketing\Delivery\DeliveryResult;
use App\Services\EmailMarketing\Delivery\EmailDeliveryService;
use App\Services\EmailMarketing\Delivery\OutboundEmail;
use App\Services\EmailMarketing\HtmlSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SendGridInboundInboxTest extends EmailMarketingTestCase
{
    private function enableInboundMailbox(): MailboxSetting
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
            'inbound_enabled' => true,
            'inbound_domain' => 'inbound.orga.test',
        ]);
    }

    public function test_valid_inbound_accepted_and_invalid_auth_rejected(): void
    {
        $this->enableInboundMailbox();

        $this->post('/webhooks/sendgrid/inbound', [
            'to' => 'team@inbound.orga.test',
            'from' => 'Person <a@example.com>',
            'subject' => 'Hi',
            'text' => 'Hello',
        ])->assertUnauthorized();

        Http::fake();
        $this->withHeaders(['X-Test-SendGrid-Inbound' => '1'])
            ->post('/webhooks/sendgrid/inbound', [
                'to' => 'team@inbound.orga.test',
                'from' => 'Person <a@example.com>',
                'cc' => 'cc@example.com',
                'subject' => 'Hi',
                'text' => 'Hello',
                'html' => '<p>Hello</p><script>alert(1)</script><iframe src="x"></iframe>',
                'headers' => "Message-ID: <msg-1@example.com>\nDate: Fri, 21 Aug 2026 12:00:00 +0000\n",
            ])
            ->assertCreated();

        $message = Message::query()->where('organization_id', $this->organizationA->id)->inbox()->first();
        $this->assertNotNull($message);
        $this->assertSame('a@example.com', $message->from_email);
        $this->assertSame('cc@example.com', $message->cc);
        $this->assertSame('msg-1@example.com', $message->message_id);
        $this->assertStringNotContainsString('<script>', (string) $message->body_html);
        $this->assertStringNotContainsString('<iframe', (string) $message->body_html);
        Http::assertNothingSent();
    }

    public function test_unknown_inbound_is_acknowledged_but_not_stored(): void
    {
        $this->withHeaders(['X-Test-SendGrid-Inbound' => '1'])
            ->post('/webhooks/sendgrid/inbound', [
                'to' => 'nobody@unknown-domain.test',
                'from' => 'x@example.com',
                'subject' => 'Orphan',
                'text' => 'Hi',
            ])
            ->assertStatus(202)
            ->assertJsonPath('stored', false);

        $this->assertSame(0, Message::query()->inbox()->count());
    }

    public function test_thread_correlation_preserves_crm_relations(): void
    {
        $this->enableInboundMailbox();
        $thread = (string) Str::uuid();

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Threaded',
            'email' => 'threaded@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Cust',
            'email' => 'cust@example.com',
            'status' => 'active',
        ]);
        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 10,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 10,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 10,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 10,
            'paid_amount' => 0,
            'due_amount' => 10,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $parent = Message::create([
            'organization_id' => $this->organizationA->id,
            'folder' => 'sent',
            'direction' => 'outbound',
            'message_id' => 'local-'.$thread,
            'correlation_uuid' => $thread,
            'thread_id' => $thread,
            'to' => 'threaded@example.com',
            'subject' => 'Quote mail',
            'delivery_status' => 'sent',
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'invoice_id' => $invoice->id,
            'sent_at' => now(),
        ]);

        $this->withHeaders(['X-Test-SendGrid-Inbound' => '1'])
            ->post('/webhooks/sendgrid/inbound', [
                'to' => 'reply+'.$thread.'@inbound.orga.test',
                'from' => 'Threaded <threaded@example.com>',
                'subject' => 'Re: Quote mail',
                'text' => 'Thanks',
            ])
            ->assertCreated();

        $inbound = Message::query()->inbox()->latest('id')->first();
        $this->assertSame($thread, $inbound->thread_id);
        $this->assertSame($parent->id, $inbound->parent_id);
        $this->assertSame($lead->id, $inbound->lead_id);
        $this->assertSame($customer->id, $inbound->customer_id);
        $this->assertSame($quotation->id, $inbound->quotation_id);
        $this->assertSame($invoice->id, $inbound->invoice_id);
    }

    public function test_cross_org_thread_token_cannot_link_parent(): void
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
            'lead_id' => null,
        ]);

        $this->enableInboundMailbox();

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
        $this->assertNull($stored->parent_id);
        $this->assertNull($stored->lead_id);
    }

    public function test_html_sanitizer_blocks_dangerous_markup(): void
    {
        $sanitizer = app(HtmlSanitizer::class);
        $clean = $sanitizer->sanitize(
            '<p onclick="evil()">Hi</p><a href="javascript:alert(1)">x</a><img src="javascript:alert(1)">'
            .'<script>bad()</script><iframe src="https://evil.test"></iframe>'
        );

        $this->assertStringContainsString('<p>Hi</p>', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('javascript:', strtolower($clean));
        $this->assertStringNotContainsString('<script', strtolower($clean));
        $this->assertStringNotContainsString('<iframe', strtolower($clean));
    }

    public function test_inbound_attachments_are_private_and_cross_org_blocked(): void
    {
        Storage::fake('local');
        $this->enableInboundMailbox();

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $this->withHeaders(['X-Test-SendGrid-Inbound' => '1'])
            ->post('/webhooks/sendgrid/inbound', [
                'to' => 'team@inbound.orga.test',
                'from' => 'a@example.com',
                'subject' => 'With file',
                'text' => 'See attached',
                'attachment1' => $file,
            ])
            ->assertCreated();

        $message = Message::query()->inbox()->latest('id')->first();
        $attachment = $message->attachments()->first();
        $this->assertNotNull($attachment);
        $this->assertStringStartsWith('email-attachments/'.$this->organizationA->id.'/', $attachment->path);
        $this->assertFalse(str_contains($attachment->path, 'doc.pdf'));

        $this->actingAsEmAdmin($this->adminA)
            ->get(route('admin.email.attachments.download', [$message, $attachment]))
            ->assertOk();

        $this->actingAsEmAdmin($this->adminB)
            ->get(route('admin.email.attachments.download', [$message, $attachment]))
            ->assertNotFound();
    }

    public function test_reply_stays_same_thread_via_delivery_service(): void
    {
        Mail::fake();
        Http::fake();
        $this->enableInboundMailbox();

        $thread = (string) Str::uuid();
        $inbound = Message::create([
            'organization_id' => $this->organizationA->id,
            'folder' => 'inbox',
            'direction' => 'inbound',
            'message_id' => 'inbound-'.$thread,
            'correlation_uuid' => (string) Str::uuid(),
            'thread_id' => $thread,
            'from_email' => 'customer@example.com',
            'from_name' => 'Customer',
            'to' => 'team@inbound.orga.test',
            'subject' => 'Question',
            'body_html' => '<p>Help</p>',
            'body_text' => 'Help',
            'is_read' => true,
            'received_at' => now(),
        ]);

        /** @var OutboundEmail|null $captured */
        $captured = null;
        $this->mock(EmailDeliveryService::class, function ($mock) use (&$captured) {
            $mock->shouldReceive('send')->once()->andReturnUsing(function (OutboundEmail $email) use (&$captured) {
                $captured = $email;

                return DeliveryResult::accepted('laravel', 'reply-1', 'processed');
            });
        });

        $this->actingAsEmAdmin()
            ->post(route('admin.email.reply', $inbound), [
                'body_html' => '<p>Our reply</p>',
                'subject' => 'Re: Question',
            ])
            ->assertRedirect(route('admin.email.sent'));

        $sent = Message::query()->sent()->latest('id')->first();
        $this->assertSame($thread, $sent->thread_id);
        $this->assertSame($inbound->id, $sent->parent_id);
        $this->assertSame('customer@example.com', $captured->to[0]);
        Http::assertNothingSent();
    }

    public function test_read_and_star_remain_local(): void
    {
        $this->enableInboundMailbox();
        $message = Message::create([
            'organization_id' => $this->organizationA->id,
            'folder' => 'inbox',
            'direction' => 'inbound',
            'message_id' => 'inbound-'.Str::uuid(),
            'from_email' => 'a@example.com',
            'to' => 'team@inbound.orga.test',
            'subject' => 'Star me',
            'body_text' => 'Hi',
            'is_read' => false,
            'is_starred' => false,
            'received_at' => now(),
        ]);

        Http::fake();

        $this->actingAsEmAdmin()
            ->post(route('admin.email.star', $message))
            ->assertRedirect();
        $this->assertTrue($message->fresh()->is_starred);

        $this->actingAsEmAdmin()
            ->get(route('admin.email.show', $message))
            ->assertOk()
            ->assertSee('Star me');

        $this->assertTrue($message->fresh()->is_read);

        $this->actingAsEmAdmin()
            ->post(route('admin.email.unread', $message))
            ->assertRedirect();
        $this->assertFalse($message->fresh()->is_read);

        Http::assertNothingSent();
    }

    public function test_inbox_list_shows_preview_and_attachment_indicator(): void
    {
        Storage::fake('local');
        $message = Message::create([
            'organization_id' => $this->organizationA->id,
            'folder' => 'inbox',
            'direction' => 'inbound',
            'message_id' => 'inbound-'.Str::uuid(),
            'from_email' => 'sender@example.com',
            'from_name' => 'Sender Name',
            'to' => 'team@orga.test',
            'subject' => 'List subject',
            'body_text' => 'Short preview body for inbox list',
            'is_read' => false,
            'received_at' => now(),
        ]);

        MessageAttachment::create([
            'organization_id' => $this->organizationA->id,
            'message_id' => $message->id,
            'original_name' => 'file.pdf',
            'stored_name' => Str::uuid().'.pdf',
            'disk' => 'local',
            'path' => 'email-attachments/'.$this->organizationA->id.'/x.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1200,
        ]);

        $this->actingAsEmAdmin()
            ->get(route('admin.email.inbox'))
            ->assertOk()
            ->assertSee('Sender Name')
            ->assertSee('List subject')
            ->assertSee('Short preview body')
            ->assertSee('solar:paperclip-linear', false);
    }
}
