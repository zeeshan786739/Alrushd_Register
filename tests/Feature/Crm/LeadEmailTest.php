<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;
use App\Models\EmailMarketing\Message;
use Illuminate\Support\Facades\Mail;

class LeadEmailTest extends CrmTestCase
{
    public function test_admin_can_send_lead_email(): void
    {
        Mail::fake();

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Email',
            'last_name' => 'Lead',
            'email' => 'lead@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.email.send', $lead), [
                'subject' => 'Hello there',
                'message' => 'We would like to follow up with you.',
            ])
            ->assertRedirect(route('admin.crm.leads.show', $lead));

        $message = Message::query()->where('lead_id', $lead->id)->first();
        $this->assertNotNull($message);
        $this->assertSame('Hello there', $message->subject);
        $this->assertSame('sent', $message->delivery_status);
        $this->assertNotNull($message->correlation_uuid);

        $lead->refresh();
        $this->assertSame(1, $lead->contact_count);
        $this->assertNotNull($lead->last_contacted_at);
        $this->assertTrue($lead->activities()->where('activity_type', 'email_sent')->exists());
    }

    public function test_lead_email_form_requires_update_permission(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationB->id,
            'source' => 'manual',
            'first_name' => 'Other',
            'email' => 'other@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.email.form', $lead))
            ->assertNotFound();
    }
}
