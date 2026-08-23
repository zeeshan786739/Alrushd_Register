<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\Lead;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\ProviderEvent;
use App\Services\Crm\CrmTransactionalMailService;
use App\Services\EmailMarketing\Delivery\DeliveryResult;
use App\Services\EmailMarketing\Delivery\EmailDeliveryService;
use App\Services\EmailMarketing\Delivery\OutboundEmail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mockery;

class CrmTransactionalMailTest extends CrmTestCase
{
    private function customer(): Customer
    {
        return Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Mail Cust',
            'email' => 'crm-mail-'.uniqid().'@example.com',
            'status' => 'active',
        ]);
    }

    private function draftQuotation(?Customer $customer = null): Quotation
    {
        $customer ??= $this->customer();
        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 100,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'status' => 'draft',
        ]);
        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        return $quotation->fresh(['customer', 'items']);
    }

    private function draftInvoice(?Customer $customer = null): Invoice
    {
        $customer ??= $this->customer();
        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 100,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'paid_amount' => 0,
            'due_amount' => 100,
            'status' => 'draft',
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        return $invoice->fresh(['customer', 'items']);
    }

    public function test_lead_email_uses_delivery_service_and_stores_correlation(): void
    {
        Mail::fake();
        Http::fake();

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Email',
            'last_name' => 'Lead',
            'email' => 'lead-tx@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.email.send', $lead), [
                'subject' => 'Hello there',
                'message' => 'Follow up body',
            ])
            ->assertRedirect(route('admin.crm.leads.show', $lead));

        $message = Message::query()
            ->where('organization_id', $this->organizationA->id)
            ->where('lead_id', $lead->id)
            ->first();

        $this->assertNotNull($message);
        $this->assertNotNull($message->correlation_uuid);
        $this->assertTrue(Str::isUuid($message->correlation_uuid));
        $this->assertSame('sent', $message->delivery_status);
        $this->assertSame('laravel', $message->provider);
        $this->assertSame($this->adminA->id, $message->created_by);
        Http::assertNothingSent();

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('Recent emails')
            ->assertSee('Hello there');
    }

    public function test_quotation_send_uses_delivery_service_with_pdf_and_marks_sent(): void
    {
        Mail::fake();
        /** @var OutboundEmail|null $captured */
        $captured = null;

        $this->mock(EmailDeliveryService::class, function ($mock) use (&$captured) {
            $mock->shouldReceive('send')->once()->andReturnUsing(function (OutboundEmail $email) use (&$captured) {
                $captured = $email;

                return DeliveryResult::accepted('laravel', 'msg-q-1', 'processed');
            });
        });

        $quotation = $this->draftQuotation();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.send', $quotation))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertSame('sent', $quotation->status);
        $this->assertNotNull($quotation->sent_at);

        $this->assertNotNull($captured);
        $this->assertSame($quotation->customer->email, $captured->to[0]);
        $this->assertSame('Quotation '.$quotation->quotation_number, $captured->subject);
        $this->assertCount(1, $captured->attachments);
        $this->assertSame('application/pdf', $captured->attachments[0]['mime']);
        $this->assertArrayHasKey('correlation_uuid', $captured->customArgs);
        $this->assertArrayNotHasKey('organization_id', $captured->customArgs);

        $message = Message::query()->where('quotation_id', $quotation->id)->first();
        $this->assertNotNull($message);
        $this->assertSame($message->correlation_uuid, $captured->customArgs['correlation_uuid']);
        $this->assertSame($quotation->customer_id, $message->customer_id);
    }

    public function test_invoice_send_uses_delivery_service_with_pdf(): void
    {
        Mail::fake();
        /** @var OutboundEmail|null $captured */
        $captured = null;

        $this->mock(EmailDeliveryService::class, function ($mock) use (&$captured) {
            $mock->shouldReceive('send')->once()->andReturnUsing(function (OutboundEmail $email) use (&$captured) {
                $captured = $email;

                return DeliveryResult::accepted('laravel', 'msg-i-1', 'processed');
            });
        });

        $invoice = $this->draftInvoice();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.invoices.send', $invoice))
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('sent', $invoice->status);
        $this->assertNotNull($invoice->sent_at);

        $this->assertNotNull($captured);
        $this->assertCount(1, $captured->attachments);
        $this->assertSame('application/pdf', $captured->attachments[0]['mime']);

        $message = Message::query()->where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($message);
        $this->assertSame($invoice->customer_id, $message->customer_id);
    }

    public function test_quotation_provider_failure_still_marks_sent_per_legacy_semantics(): void
    {
        $this->mock(EmailDeliveryService::class, function ($mock) {
            $mock->shouldReceive('send')->once()->andReturn(DeliveryResult::failed('sendgrid', 'provider down'));
        });

        $quotation = $this->draftQuotation();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.send', $quotation))
            ->assertRedirect()
            ->assertSessionHas('success');

        $quotation->refresh();
        $this->assertSame('sent', $quotation->status);
        $this->assertNotNull($quotation->sent_at);

        $message = Message::query()->where('quotation_id', $quotation->id)->first();
        $this->assertNotNull($message);
        $this->assertSame('failed', $message->delivery_status);
    }

    public function test_invoice_provider_failure_marks_sent_but_records_failed_message(): void
    {
        $this->mock(EmailDeliveryService::class, function ($mock) {
            $mock->shouldReceive('send')->once()->andReturn(DeliveryResult::failed('sendgrid', 'provider down'));
        });

        $invoice = $this->draftInvoice();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.invoices.send', $invoice))
            ->assertRedirect();

        $this->assertSame('sent', $invoice->fresh()->status);
        $message = Message::query()->where('invoice_id', $invoice->id)->first();
        $this->assertSame('failed', $message->delivery_status);
    }

    public function test_mark_as_sent_still_sends_no_email(): void
    {
        Mail::fake();
        $spy = Mockery::spy(EmailDeliveryService::class);
        $this->app->instance(EmailDeliveryService::class, $spy);

        $quotation = $this->draftQuotation();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.mark-sent', $quotation))
            ->assertRedirect();

        $spy->shouldNotHaveReceived('send');
        Mail::assertNothingSent();
        $this->assertSame(0, Message::query()->where('quotation_id', $quotation->id)->count());
        $this->assertSame('sent', $quotation->fresh()->status);
    }

    public function test_delivery_events_visible_on_quotation_show(): void
    {
        $quotation = $this->draftQuotation();
        $quotation->update(['status' => 'sent', 'sent_at' => now()]);

        $uuid = (string) Str::uuid();
        $message = Message::create([
            'organization_id' => $this->organizationA->id,
            'folder' => 'sent',
            'direction' => 'outbound',
            'message_id' => 'crm-'.$uuid,
            'correlation_uuid' => $uuid,
            'to' => $quotation->customer->email,
            'subject' => 'Quotation '.$quotation->quotation_number,
            'delivery_status' => 'sent',
            'provider' => 'sendgrid',
            'provider_status' => 'delivered',
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'sent_at' => now()->subMinutes(20),
            'delivered_at' => now()->subMinutes(15),
            'opened_at' => now()->subMinutes(10),
        ]);

        ProviderEvent::create([
            'organization_id' => $this->organizationA->id,
            'em_message_id' => $message->id,
            'provider' => 'sendgrid',
            'event_type' => 'processed',
            'provider_event_id' => 'e1',
            'correlation_uuid' => $uuid,
            'occurred_at' => now()->subMinutes(20),
        ]);
        ProviderEvent::create([
            'organization_id' => $this->organizationA->id,
            'em_message_id' => $message->id,
            'provider' => 'sendgrid',
            'event_type' => 'delivered',
            'provider_event_id' => 'e2',
            'correlation_uuid' => $uuid,
            'occurred_at' => now()->subMinutes(15),
        ]);
        ProviderEvent::create([
            'organization_id' => $this->organizationA->id,
            'em_message_id' => $message->id,
            'provider' => 'sendgrid',
            'event_type' => 'open',
            'provider_event_id' => 'e3',
            'correlation_uuid' => $uuid,
            'occurred_at' => now()->subMinutes(10),
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('Email Delivery')
            ->assertSee('Delivered')
            ->assertSee('Opened');
    }

    public function test_bounced_status_visible_on_invoice_show(): void
    {
        $invoice = $this->draftInvoice();
        $invoice->update(['status' => 'sent', 'sent_at' => now()]);
        $uuid = (string) Str::uuid();

        Message::create([
            'organization_id' => $this->organizationA->id,
            'folder' => 'sent',
            'direction' => 'outbound',
            'message_id' => 'crm-'.$uuid,
            'correlation_uuid' => $uuid,
            'to' => $invoice->customer->email,
            'subject' => 'Invoice '.$invoice->invoice_number,
            'delivery_status' => 'bounced',
            'provider' => 'sendgrid',
            'provider_status' => 'bounce',
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'sent_at' => now(),
            'bounced_at' => now(),
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Email Delivery')
            ->assertSee('Bounced');
    }

    public function test_email_history_is_tenant_isolated(): void
    {
        $quotation = $this->draftQuotation();
        $uuid = (string) Str::uuid();
        Message::create([
            'organization_id' => $this->organizationA->id,
            'folder' => 'sent',
            'direction' => 'outbound',
            'message_id' => 'crm-'.$uuid,
            'correlation_uuid' => $uuid,
            'to' => 'secret@example.com',
            'subject' => 'Org A secret quote mail',
            'delivery_status' => 'sent',
            'provider_status' => 'delivered',
            'quotation_id' => $quotation->id,
            'sent_at' => now(),
        ]);

        $this->actingAsCrmAdmin($this->adminB)
            ->get(route('admin.crm.quotations.show', $quotation))
            ->assertNotFound();
    }

    public function test_service_stores_opaque_correlation_only(): void
    {
        /** @var OutboundEmail|null $captured */
        $captured = null;
        $this->mock(EmailDeliveryService::class, function ($mock) use (&$captured) {
            $mock->shouldReceive('send')->once()->andReturnUsing(function (OutboundEmail $email) use (&$captured) {
                $captured = $email;

                return DeliveryResult::accepted('laravel', 'x', 'processed');
            });
        });

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Corr',
            'email' => 'corr@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        app(CrmTransactionalMailService::class)->sendLeadEmail($lead, 'Subj', 'Body', $this->adminA->id);

        $this->assertSame(['correlation_uuid'], array_keys($captured->customArgs));
        $this->assertTrue(Str::isUuid($captured->customArgs['correlation_uuid']));
    }
}
