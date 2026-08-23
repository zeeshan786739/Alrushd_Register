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
            return Invoice::query()
                ->where('organization_id', $quotation->organization_id)
                ->whereKey($quotation->converted_invoice_id)
                ->firstOrFail();
        }

        return DB::transaction(function () use ($quotation) {
            $locked = Quotation::query()
                ->whereKey($quotation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'accepted') {
                throw new RuntimeException('Only accepted quotations can be converted to invoices.');
            }

            if ($locked->converted_invoice_id) {
                return Invoice::query()
                    ->where('organization_id', $locked->organization_id)
                    ->whereKey($locked->converted_invoice_id)
                    ->firstOrFail();
            }

            $existing = Invoice::query()
                ->where('organization_id', $locked->organization_id)
                ->where('quotation_id', $locked->id)
                ->first();

            if ($existing) {
                if (! $locked->converted_invoice_id) {
                    $locked->update(['converted_invoice_id' => $existing->id]);
                }

                return $existing->load('items');
            }

            $invoice = Invoice::create([
                'organization_id' => $locked->organization_id,
                'invoice_number' => Invoice::generateInvoiceNumber($locked->organization_id),
                'customer_id' => $locked->customer_id,
                'project_id' => $locked->project_id,
                'quotation_id' => $locked->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $locked->subtotal,
                'tax_percentage' => $locked->tax_percentage,
                'tax_amount' => $locked->tax_amount,
                'discount_percentage' => $locked->discount_percentage,
                'discount_amount' => $locked->discount_amount,
                'total' => $locked->total,
                'paid_amount' => 0,
                'due_amount' => $locked->total,
                'status' => 'draft',
                'terms' => $locked->terms,
                'notes' => 'Converted from quotation '.$locked->quotation_number,
                'created_by' => auth('admin')->id(),
            ]);

            foreach ($locked->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ]);
            }

            $locked->update(['converted_invoice_id' => $invoice->id]);

            return $invoice->load('items');
        });
    }
}
