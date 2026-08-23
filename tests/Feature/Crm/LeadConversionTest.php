<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Services\Crm\LeadConversionException;
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

    public function test_conversion_blocked_when_active_customer_email_exists(): void
    {
        Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Existing Customer',
            'email' => 'dup@example.com',
            'status' => 'active',
        ]);

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Other',
            'email' => 'dup@example.com',
            'lead_status' => 'qualified',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin();

        try {
            app(LeadConversionService::class)->convertToCustomer($lead);
            $this->fail('Expected LeadConversionException was not thrown.');
        } catch (LeadConversionException $e) {
            $this->assertStringContainsString('already exists', $e->getMessage());
        }

        $lead->refresh();
        $this->assertFalse((bool) $lead->is_converted);
        $this->assertNull($lead->customer_id);
        $this->assertSame('qualified', $lead->lead_status);
        $this->assertSame(1, Customer::forOrganization($this->organizationA->id)->count());
    }

    public function test_http_conversion_blocked_keeps_lead_unconverted(): void
    {
        Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Existing Customer',
            'email' => 'http-dup@example.com',
            'status' => 'active',
        ]);

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Blocked',
            'email' => 'http-dup@example.com',
            'lead_status' => 'qualified',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.convert', $lead))
            ->assertRedirect()
            ->assertSessionHas('error');

        $lead->refresh();
        $this->assertFalse((bool) $lead->is_converted);
        $this->assertNull($lead->customer_id);
    }

    public function test_conversion_blocked_when_soft_deleted_customer_email_exists(): void
    {
        $deleted = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Deleted Customer',
            'email' => 'soft-dup@example.com',
            'status' => 'active',
        ]);
        $deleted->delete();

        $lead = Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Soft',
            'email' => 'soft-dup@example.com',
            'lead_status' => 'qualified',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin();

        try {
            app(LeadConversionService::class)->convertToCustomer($lead);
            $this->fail('Expected LeadConversionException was not thrown.');
        } catch (LeadConversionException $e) {
            $this->assertStringContainsString('soft-deleted', $e->getMessage());
        }

        $lead->refresh();
        $this->assertFalse((bool) $lead->is_converted);
        $this->assertNull($lead->customer_id);
        $this->assertSoftDeleted('crm_customers', ['id' => $deleted->id]);
        $this->assertSame(0, Customer::forOrganization($this->organizationA->id)->count());
    }
}
