<?php

namespace App\Models\Integrations;

use App\Models\Admin;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationFormMapping extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'integration_connection_id',
        'external_form_id',
        'external_form_name',
        'internal_label',
        'lead_source_label',
        'assigned_to',
        'priority',
        'auto_create_lead',
        'field_mapping',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'auto_create_lead' => 'boolean',
            'is_active' => 'boolean',
            'field_mapping' => 'array',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(IntegrationConnection::class, 'integration_connection_id');
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function metaLeadSubmissions(): HasMany
    {
        return $this->hasMany(MetaLeadSubmission::class, 'integration_form_mapping_id');
    }
}
