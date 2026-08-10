<?php

namespace App\Models;

use App\Enums\Platform\DemoRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoRequest extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'organization_name', 'organization_type',
        'country', 'students_count', 'message', 'status', 'internal_notes',
        'handled_by', 'converted_organization_id', 'source',
    ];

    protected function casts(): array
    {
        return [
            'status' => DemoRequestStatus::class,
        ];
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'handled_by');
    }

    public function convertedOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'converted_organization_id');
    }
}
