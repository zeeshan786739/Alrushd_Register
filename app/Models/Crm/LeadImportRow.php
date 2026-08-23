<?php

namespace App\Models\Crm;

use App\Enums\LeadImportRowStatus;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadImportRow extends Model
{
    use BelongsToOrganization;

    protected $table = 'crm_lead_import_rows';

    protected $fillable = [
        'organization_id',
        'lead_import_id',
        'row_number',
        'row_hash',
        'raw_data',
        'normalized_data',
        'status',
        'warnings',
        'errors',
        'lead_id',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
            'normalized_data' => 'array',
            'warnings' => 'array',
            'errors' => 'array',
            'imported_at' => 'datetime',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(LeadImport::class, 'lead_import_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function statusEnum(): LeadImportRowStatus
    {
        return LeadImportRowStatus::tryFrom((string) $this->status) ?? LeadImportRowStatus::Ready;
    }
}
