<?php

namespace App\Models\Crm;

use App\Models\Admin;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    use BelongsToOrganization;

    protected $table = 'crm_invoice_payments';

    protected $fillable = [
        'organization_id', 'invoice_id', 'payment_date', 'amount', 'payment_method',
        'transaction_id', 'notes', 'received_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'received_by');
    }
}
