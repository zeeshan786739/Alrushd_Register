<?php

namespace App\Models\Crm;

use App\Models\Admin;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadNote extends Model
{
    use BelongsToOrganization;

    protected $table = 'crm_lead_notes';

    protected $fillable = ['organization_id', 'lead_id', 'admin_id', 'note', 'is_important'];

    protected function casts(): array
    {
        return ['is_important' => 'boolean'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
