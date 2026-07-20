<?php

namespace App\Models\Crm;

use App\Models\Admin;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    protected $table = 'crm_customers';

    protected $fillable = [
        'organization_id', 'lead_id', 'form_entry_id', 'name', 'email', 'phone', 'company',
        'website', 'address', 'city', 'state', 'zip_code', 'country', 'status', 'source',
        'lifetime_value', 'notes', 'assigned_to', 'last_contacted_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'lifetime_value' => 'decimal:2',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'customer_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'customer_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CustomerActivity::class, 'customer_id')->latest();
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class, 'customer_id');
    }

    public function updateLifetimeValue(): void
    {
        $total = $this->invoices()->sum('paid_amount');
        $this->update(['lifetime_value' => $total]);
    }
}
