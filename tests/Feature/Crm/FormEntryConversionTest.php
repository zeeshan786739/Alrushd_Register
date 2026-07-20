<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Form;
use App\Models\FormEntry;

class FormEntryConversionTest extends CrmTestCase
{
    public function test_form_entry_converts_to_lead(): void
    {
        $form = Form::create([
            'name' => 'Contact Form',
            'slug' => 'contact-form-'.uniqid(),
            'is_active' => true,
        ]);
        $form->update(['organization_id' => $this->organizationA->id]);

        $entry = FormEntry::create([
            'organization_id' => $this->organizationA->id,
            'form_id' => $form->id,
            'entry_id' => 1001,
            'data' => [
                'first_name' => 'Form',
                'last_name' => 'User',
                'email' => 'formuser@example.com',
                'phone' => '555-0100',
                'company' => 'Acme',
                'message' => 'Interested in services',
            ],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.form-entries.convert-lead', $entry))
            ->assertRedirect();

        $lead = Lead::where('form_entry_id', $entry->id)->first();
        $this->assertNotNull($lead);
        $this->assertSame('form_submission', $lead->source);
        $this->assertSame('Form', $lead->first_name);
    }

    public function test_form_entry_converts_to_customer(): void
    {
        $form = Form::create([
            'name' => 'Enquiry Form',
            'slug' => 'enquiry-form-'.uniqid(),
            'is_active' => true,
        ]);
        $form->update(['organization_id' => $this->organizationA->id]);

        $entry = FormEntry::create([
            'organization_id' => $this->organizationA->id,
            'form_id' => $form->id,
            'entry_id' => 1002,
            'data' => [
                'first_name' => 'Customer',
                'last_name' => 'Candidate',
                'email' => 'candidate@example.com',
            ],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.form-entries.convert-customer', $entry))
            ->assertRedirect();

        $customer = Customer::where('email', 'candidate@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertSame('Customer Candidate', $customer->name);
    }
}
