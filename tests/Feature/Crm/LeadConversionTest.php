<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Services\Crm\LeadConversionService;

class LeadConversionTest extends CrmTestCase
{
    public function test_lead_conversion_creates_customer_and_links_lead(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Convert',
            'last_name' => 'Me',
            'email' => 'convert@example.com',
            'lead_status' => 'qualified',
            'priority' => 'high',
        ]);

        $this->actingAsCrmAdmin();

        $customer = app(LeadConversionService::class)->convertToCustomer($lead);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame($lead->id, $customer->lead_id);

        $lead->refresh();
        $this->assertTrue($lead->is_converted);
        $this->assertSame('won', $lead->lead_status);
        $this->assertSame($customer->id, $lead->customer_id);
    }

    public function test_duplicate_conversion_returns_existing_customer(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Once',
            'email' => 'once@example.com',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin();
        $service = app(LeadConversionService::class);

        $first = $service->convertToCustomer($lead);
        $second = $service->convertToCustomer($lead->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Customer::forOrganization($this->organizationA->id)->count());
    }
}
