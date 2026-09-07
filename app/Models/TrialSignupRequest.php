<?php

namespace App\Models;

use App\Enums\Platform\TrialSignupStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialSignupRequest extends Model
{
    protected $fillable = [
        'school_name', 'country', 'phone', 'admin_name', 'admin_email', 'password_hash',
        'saas_plan_id', 'status', 'internal_notes', 'handled_by', 'organization_id',
        'approved_at', 'rejected_at', 'rejection_reason', 'source',
    ];

    protected function casts(): array
    {
        return [
            'status' => TrialSignupStatus::class,
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'handled_by');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isPending(): bool
    {
        return $this->status === TrialSignupStatus::Pending;
    }
}
