<?php

namespace Tests\Feature\Crm;

use App\Mail\Crm\LeadEmail;
use App\Models\Crm\Lead;
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

        Mail::assertSent(LeadEmail::class, function (LeadEmail $mail) use ($lead) {
            return $mail->hasTo('lead@example.com') && $mail->lead->is($lead);
        });

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
