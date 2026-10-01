<?php

namespace App\Models\Crm;

use App\Support\LeadCategoryUi;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LeadStatusDefinition extends Model
{
    use BelongsToOrganization;

    protected $table = 'crm_lead_statuses';

    protected $fillable = [
        'organization_id',
        'slug',
        'name',
        'tone',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function displayTone(): string
    {
        return LeadCategoryUi::sanitizeTone($this->tone);
    }
}
