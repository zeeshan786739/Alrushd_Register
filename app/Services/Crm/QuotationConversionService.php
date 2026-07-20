<?php

namespace App\Services\Crm;

use App\Models\Crm\Invoice;
use App\Models\Crm\InvoiceItem;
use App\Models\Crm\Quotation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuotationConversionService
{
    public function convertToInvoice(Quotation $quotation): Invoice
    {
        if ($quotation->status !== 'accepted') {
            throw new RuntimeException('Only accepted quotations can be converted to invoices.');
        }

        if ($quotation->converted_invoice_id) {
            return Invoice::forCurrentOrganization()->findOrFail($quotation->converted_invoice_id);
        }

        return DB::transaction(function () use ($quotation) {
            $quotation->refresh();

            if ($quotation->converted_invoice_id) {
                return Invoice::forCurrentOrganization()->findOrFail($quotation->converted_invoice_id);
            }

            $invoice = Invoice::create([
                'organization_id' => $quotation->organization_id,
                'invoice_number' => Invoice::generateInvoiceNumber($quotation->organization_id),
                'customer_id' => $quotation->customer_id,
                'project_id' => $quotation->project_id,
                'quotation_id' => $quotation->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $quotation->subtotal,
                'tax_percentage' => $quotation->tax_percentage,
                'tax_amount' => $quotation->tax_amount,
                'discount_percentage' => $quotation->discount_percentage,
                'discount_amount' => $quotation->discount_amount,
                'total' => $quotation->total,
                'paid_amount' => 0,
                'due_amount' => $quotation->total,
                'status' => 'draft',
                'terms' => $quotation->terms,
                'notes' => 'Converted from quotation '.$quotation->quotation_number,
                'created_by' => auth('admin')->id(),
            ]);

            foreach ($quotation->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ]);
            }

            $quotation->update(['converted_invoice_id' => $invoice->id]);

            return $invoice->load('items');
        });
    }
}
