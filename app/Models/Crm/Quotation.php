<?php

namespace App\Models\Crm;

use App\Support\OrganizationContext;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    protected $table = 'crm_quotations';

    protected $fillable = [
        'organization_id', 'quotation_number', 'customer_id', 'project_id', 'quotation_date',
        'valid_until', 'subtotal', 'tax_percentage', 'tax_amount', 'discount_percentage',
        'discount_amount', 'total', 'status', 'terms', 'notes', 'sent_at', 'accepted_at',
        'converted_invoice_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    public static function generateQuotationNumber(?int $organizationId = null): string
    {
        $organizationId ??= OrganizationContext::idOrFail();
        $last = static::withTrashed()
            ->where('organization_id', $organizationId)
            ->orderByDesc('id')
            ->first();

        $number = $last ? ((int) substr($last->quotation_number, 4)) + 1 : 1;

        return 'QUO-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }
}
