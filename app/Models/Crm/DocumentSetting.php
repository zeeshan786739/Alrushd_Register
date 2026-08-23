<?php

namespace App\Models\Crm;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class DocumentSetting extends Model
{
    use BelongsToOrganization;

    protected $table = 'crm_document_settings';

    protected $fillable = [
        'organization_id',
        'logo_path',
        'branding',
        'quotation',
        'invoice',
    ];

    protected function casts(): array
    {
        return [
            'branding' => 'array',
            'quotation' => 'array',
            'invoice' => 'array',
        ];
    }
}
