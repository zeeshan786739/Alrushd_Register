<?php

namespace App\Models\Integrations;

use App\Enums\MetaLeadSubmissionStatus;
use App\Models\Crm\Lead;
use App\Models\FormEntry;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaLeadSubmission extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'integration_connection_id',
        'meta_leadgen_id',
        'meta_form_id',
        'meta_ad_id',
        'meta_campaign_id',
        'meta_page_id',
        'raw_payload',
        'field_data',
        'form_entry_id',
        'lead_id',
        'integration_form_mapping_id',
        'status',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => MetaLeadSubmissionStatus::class,
            'raw_payload' => 'array',
            'field_data' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(IntegrationConnection::class, 'integration_connection_id');
    }

    public function formEntry(): BelongsTo
    {
        return $this->belongsTo(FormEntry::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function formMapping(): BelongsTo
    {
        return $this->belongsTo(IntegrationFormMapping::class, 'integration_form_mapping_id');
    }
}
