<?php

namespace App\Models\Crm;

use App\Models\Admin;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerActivity extends Model
{
    use BelongsToOrganization;

    protected $table = 'crm_customer_activities';

    protected $fillable = [
        'organization_id', 'customer_id', 'admin_id', 'type', 'subject',
        'description', 'activity_date', 'status', 'due_date', 'priority',
    ];

    protected function casts(): array
    {
        return ['activity_date' => 'datetime', 'due_date' => 'date'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
