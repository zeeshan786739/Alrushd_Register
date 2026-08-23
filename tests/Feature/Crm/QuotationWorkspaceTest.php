<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Services\Crm\QuotationConversionService;
use App\Support\QuotationExpiryState;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class QuotationWorkspaceTest extends CrmTestCase
{
    public function test_quotation_detail_is_tenant_isolated(): void
    {
        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Q',
            'email' => 'foreign-q@example.com',
            'status' => 'active',
        ]);

        $foreign = Quotation::create([
            'organization_id' => $this->organizationB->id,
            'quotation_number' => 'QUO-FOREIGN',
            'customer_id' => $foreignCustomer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 10,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 10,
            'status' => 'draft',
        ]);

        $this->actingAsCrmAdmin($this->adminA)
            ->get(route('admin.crm.quotations.show', $foreign))
            ->assertNotFound();
    }

    public function test_accepted_quotation_cannot_be_edited_and_shows_workspace(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Quote Cust',
            'email' => 'quote-ws@example.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Quote Project',
            'project_code' => 'PRJ-QWS',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(10)->toDateString(),
            'subtotal' => 100,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Service line',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.show', $quotation))
            ->assertOk()
            ->assertSee($quotation->quotation_number)
            ->assertSee($customer->name)
            ->assertSee($project->name)
            ->assertSee('Service line')
            ->assertSee('Convert to Invoice')
            ->assertDontSee(route('admin.crm.quotations.edit', $quotation, false));

        $this->actingAsCrmAdmin()
            ->put(route('admin.crm.quotations.update', $quotation), [
                'customer_id' => $customer->id,
                'project_id' => $project->id,
                'quotation_date' => now()->toDateString(),
                'status' => 'draft',
                'items' => [
                    ['description' => 'Changed', 'quantity' => 1, 'unit_price' => 1],
                ],
            ])
            ->assertRedirect(route('admin.crm.quotations.show', $quotation))
            ->assertSessionHas('error');

        $this->assertSame('accepted', $quotation->fresh()->status);
        $this->assertEquals(100.0, (float) $quotation->fresh()->total);
    }

    public function test_accept_reject_and_idempotent_conversion(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Lifecycle Cust',
            'email' => 'lifecycle@example.com',
            'status' => 'active',
        ]);

        $sent = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 80,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 80,
            'status' => 'sent',
        ]);

        QuotationItem::create([
            'quotation_id' => $sent->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 80,
            'total' => 80,
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.accept', $sent))
            ->assertRedirect();

        $this->assertSame('accepted', $sent->fresh()->status);
        $this->assertNotNull($sent->fresh()->accepted_at);

        $invoice1 = app(QuotationConversionService::class)->convertToInvoice($sent->fresh());
        $invoice2 = app(QuotationConversionService::class)->convertToInvoice($sent->fresh());

        $this->assertSame($invoice1->id, $invoice2->id);
        $this->assertSame(1, Invoice::forOrganization($this->organizationA->id)->where('quotation_id', $sent->id)->count());

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.show', $sent->fresh()))
            ->assertOk()
            ->assertSee($invoice1->invoice_number)
            ->assertSee('View Invoice')
            ->assertDontSee('>Convert to Invoice<', false);

        $rejectable = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 20,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 20,
            'status' => 'sent',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.reject', $rejectable))
            ->assertRedirect();

        $this->assertSame('rejected', $rejectable->fresh()->status);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.show', $rejectable))
            ->assertOk()
            ->assertSee('Quotation rejected')
            ->assertDontSee('Convert to Invoice');
    }

    public function test_accept_requires_convert_permission(): void
    {
        $role = Role::create(['name' => 'quote-updater', 'guard_name' => 'admin']);
        $role->givePermissionTo(
            Permission::findByName('view quotations', 'admin'),
            Permission::findByName('update quotations', 'admin'),
        );

        $this->adminA->syncRoles([$role]);

        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Perm Cust',
            'email' => 'perm-q@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 15,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 15,
            'status' => 'sent',
        ]);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.accept', $quotation))
            ->assertForbidden();

        $this->assertSame('sent', $quotation->fresh()->status);
    }

    public function test_create_preselection_and_project_customer_validation(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Preselect Cust',
            'email' => 'preselect@example.com',
            'status' => 'active',
        ]);

        $otherCustomer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Other Cust',
            'email' => 'other-pre@example.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'organization_id' => $this->organizationA->id,
            'customer_id' => $customer->id,
            'name' => 'Preselect Project',
            'project_code' => 'PRJ-PRE',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.create', [
                'customer_id' => $customer->id,
                'project_id' => $project->id,
            ]))
            ->assertOk()
            ->assertSee('selected', false)
            ->assertSee((string) $customer->id)
            ->assertSee((string) $project->id);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.store'), [
                'customer_id' => $otherCustomer->id,
                'project_id' => $project->id,
                'quotation_date' => now()->toDateString(),
                'status' => 'draft',
                'items' => [
                    ['description' => 'Mismatch', 'quantity' => 1, 'unit_price' => 10],
                ],
            ])
            ->assertSessionHasErrors('project_id');
    }

    public function test_expiry_display_states_do_not_override_accepted(): void
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Expiry Cust',
            'email' => 'expiry@example.com',
            'status' => 'active',
        ]);

        $expiring = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDay()->toDateString(),
            'subtotal' => 10,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 10,
            'status' => 'sent',
        ]);

        $this->assertSame(QuotationExpiryState::EXPIRING_SOON, QuotationExpiryState::forQuotation($expiring)->state);

        $accepted = Quotation::create([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->subDay()->toDateString(),
            'subtotal' => 10,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 10,
            'status' => 'accepted',
        ]);

        $state = QuotationExpiryState::forQuotation($accepted);
        $this->assertFalse($state->applies);
        $this->assertSame(QuotationExpiryState::NONE, $state->state);
    }
}
