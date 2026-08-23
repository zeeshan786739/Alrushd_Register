<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\Quotation;
use App\Models\Crm\QuotationItem;
use App\Models\EmailMarketing\Message;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class QuotationMarkSentTest extends CrmTestCase
{
    private function makeDraftQuotation(array $overrides = []): Quotation
    {
        $customer = Customer::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Mark Sent Cust',
            'email' => 'mark-sent-'.uniqid().'@example.com',
            'status' => 'active',
        ]);

        $quotation = Quotation::create(array_merge([
            'organization_id' => $this->organizationA->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationA->id),
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 150,
            'tax_percentage' => 10,
            'tax_amount' => 15,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 165,
            'status' => 'draft',
            'notes' => 'Keep notes intact',
            'terms' => 'Net 14',
        ], $overrides));

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Consulting',
            'quantity' => 1,
            'unit_price' => 150,
            'total' => 150,
        ]);

        return $quotation->fresh(['items', 'customer']);
    }

    public function test_draft_can_be_marked_sent_without_email(): void
    {
        Mail::fake();

        $quotation = $this->makeDraftQuotation();
        $total = (float) $quotation->total;
        $itemCount = $quotation->items()->count();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.mark-sent', $quotation))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertNothingSent();

        $quotation->refresh();
        $this->assertSame('sent', $quotation->status);
        $this->assertNotNull($quotation->sent_at);
        $this->assertSame($total, (float) $quotation->total);
        $this->assertSame($itemCount, $quotation->items()->count());
        $this->assertSame('Keep notes intact', $quotation->notes);
        $this->assertSame('Net 14', $quotation->terms);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('Accept')
            ->assertSee('Reject')
            ->assertDontSee('Mark as Sent');
    }

    public function test_accept_works_after_mark_as_sent(): void
    {
        Mail::fake();
        $quotation = $this->makeDraftQuotation();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.mark-sent', $quotation))
            ->assertRedirect();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.accept', $quotation->fresh()))
            ->assertRedirect();

        $this->assertSame('accepted', $quotation->fresh()->status);
        $this->assertNotNull($quotation->fresh()->accepted_at);
    }

    public function test_send_by_email_still_sends_mail_and_marks_sent(): void
    {
        Mail::fake();
        $quotation = $this->makeDraftQuotation();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.send', $quotation))
            ->assertRedirect();

        $this->assertSame('sent', $quotation->fresh()->status);
        $this->assertNotNull($quotation->fresh()->sent_at);
        $this->assertNotNull(
            Message::query()->where('quotation_id', $quotation->id)->where('delivery_status', 'sent')->first()
        );
    }

    public function test_cannot_mark_accepted_rejected_or_converted_as_sent(): void
    {
        Mail::fake();

        $accepted = $this->makeDraftQuotation(['status' => 'accepted', 'accepted_at' => now()]);
        $rejected = $this->makeDraftQuotation(['status' => 'rejected']);

        $converted = $this->makeDraftQuotation(['status' => 'accepted', 'accepted_at' => now()]);
        $invoice = Invoice::create([
            'organization_id' => $this->organizationA->id,
            'invoice_number' => Invoice::generateInvoiceNumber($this->organizationA->id),
            'customer_id' => $converted->customer_id,
            'quotation_id' => $converted->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 165,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 165,
            'paid_amount' => 0,
            'due_amount' => 165,
            'status' => 'sent',
        ]);
        $converted->update(['converted_invoice_id' => $invoice->id]);

        foreach ([$accepted, $rejected, $converted] as $quotation) {
            $before = $quotation->fresh()->status;
            $this->actingAsCrmAdmin()
                ->post(route('admin.crm.quotations.mark-sent', $quotation))
                ->assertRedirect()
                ->assertSessionHas('error');

            $this->assertSame($before, $quotation->fresh()->status);
        }

        Mail::assertNothingSent();
    }

    public function test_mark_sent_is_tenant_isolated_and_permission_gated(): void
    {
        $foreignCustomer = Customer::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Foreign Q',
            'email' => 'foreign-q-mark@example.com',
            'status' => 'active',
        ]);

        $foreign = Quotation::create([
            'organization_id' => $this->organizationB->id,
            'quotation_number' => Quotation::generateQuotationNumber($this->organizationB->id),
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
            ->post(route('admin.crm.quotations.mark-sent', $foreign))
            ->assertNotFound();

        $role = Role::create(['name' => 'quote-viewer-only', 'guard_name' => 'admin']);
        $role->givePermissionTo(Permission::findByName('view quotations', 'admin'));
        $this->adminA->syncRoles([$role]);

        $draft = $this->makeDraftQuotation();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.quotations.mark-sent', $draft))
            ->assertForbidden();

        $this->assertSame('draft', $draft->fresh()->status);
    }

    public function test_draft_show_page_lists_both_send_actions(): void
    {
        $quotation = $this->makeDraftQuotation();

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('Send by Email')
            ->assertSee('Mark as Sent')
            ->assertDontSee('>Accept<', false);
    }
}
