<?php

namespace App\Models\Integrations;

use App\Enums\LeadPriority;
use App\Models\Admin;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TikTokFormMapping extends Model
{
    use BelongsToOrganization;

    protected $table = 'tiktok_form_mappings';

    protected $fillable = [
        'organization_id',
        'integration_connection_id',
        'advertiser_id',
        'external_form_id',
        'external_form_name',
        'external_status',
        'lead_source_label',
        'assigned_to',
        'priority',
        'auto_create_lead',
        'is_active',
        'field_mapping',
        'external_fields',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'auto_create_lead' => 'boolean',
            'is_active' => 'boolean',
            'field_mapping' => 'array',
            'external_fields' => 'array',
            'last_synced_at' => 'datetime',
            'priority' => LeadPriority::class,
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

    public function mappingStatus(): string
    {
        if (! $this->is_active) {
            return 'disabled';
        }

        return $this->hasConfiguredFieldMapping() ? 'configured' : 'needs_setup';
    }

    public function mappingStatusLabel(): string
    {
        return match ($this->mappingStatus()) {
            'configured' => 'Configured',
            'disabled' => 'Disabled',
            default => 'Needs setup',
        };
    }

    public function hasConfiguredFieldMapping(): bool
    {
        foreach ($this->field_mapping ?? [] as $crmField) {
            if (is_string($crmField) && $crmField !== '') {
                return true;
            }
        }

        return false;
    }
}
