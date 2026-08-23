<?php

namespace App\Models\Crm;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class LeadImportProfile extends Model
{
    use BelongsToOrganization;

    protected $table = 'crm_lead_import_profiles';

    protected $fillable = [
        'organization_id',
        'name',
        'header_signature',
        'mapping',
        'options',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'mapping' => 'array',
            'options' => 'array',
            'last_used_at' => 'datetime',
        ];
    }
}
