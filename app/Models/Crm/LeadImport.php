<?php

namespace App\Models\Crm;

use App\Enums\LeadImportStatus;
use App\Models\Admin;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadImport extends Model
{
    use BelongsToOrganization;

    protected $table = 'crm_lead_imports';

    protected $fillable = [
        'organization_id',
        'uploaded_by',
        'original_filename',
        'stored_path',
        'file_hash',
        'detected_format',
        'selected_sheet',
        'header_row',
        'detected_headers',
        'mapping',
        'import_options',
        'status',
        'total_rows',
        'ready_rows',
        'warning_rows',
        'imported_rows',
        'skipped_rows',
        'duplicate_rows',
        'failed_rows',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'detected_headers' => 'array',
            'mapping' => 'array',
            'import_options' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'uploaded_by');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(LeadImportRow::class, 'lead_import_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'lead_import_id');
    }

    public function statusEnum(): LeadImportStatus
    {
        return LeadImportStatus::tryFrom((string) $this->status) ?? LeadImportStatus::Uploaded;
    }

    public function option(string $key, mixed $default = null): mixed
    {
        return data_get($this->import_options, $key, $default);
    }
}
