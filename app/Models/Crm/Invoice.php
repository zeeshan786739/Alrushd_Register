<?php

namespace App\Models\Crm;

use App\Support\OrganizationContext;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    protected $table = 'crm_invoices';

    protected $fillable = [
        'organization_id', 'invoice_number', 'customer_id', 'project_id', 'quotation_id',
        'invoice_date', 'due_date', 'subtotal', 'tax_percentage', 'tax_amount',
        'discount_percentage', 'discount_amount', 'total', 'paid_amount', 'due_amount',
        'status', 'terms', 'notes', 'sent_at', 'paid_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'invoice_id');
    }

    public static function generateInvoiceNumber(?int $organizationId = null): string
    {
        $organizationId ??= OrganizationContext::idOrFail();
        $last = static::withTrashed()
            ->where('organization_id', $organizationId)
            ->orderByDesc('id')
            ->first();

        $number = $last ? ((int) substr($last->invoice_number, 4)) + 1 : 1;

        return 'INV-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    public function refreshStatus(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }

        if ((float) $this->paid_amount >= (float) $this->total && (float) $this->total > 0) {
            $this->status = 'paid';
            $this->paid_at = $this->paid_at ?? now();
        } elseif ((float) $this->paid_amount > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date && $this->due_date->isPast() && ! in_array($this->status, ['paid', 'draft'], true)) {
            $this->status = 'overdue';
        }

        $this->save();
    }
}
