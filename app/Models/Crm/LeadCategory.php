<?php

namespace App\Models\Crm;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadCategory extends Model
{
    use BelongsToOrganization;

    protected $table = 'lead_categories';

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'icon',
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

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'lead_category_id');
    }

    public function imports(): HasMany
    {
        return $this->hasMany(LeadImport::class, 'lead_category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function displayIcon(): string
    {
        return \App\Support\LeadCategoryUi::sanitizeIcon($this->icon);
    }

    public function displayTone(): string
    {
        $tone = trim((string) $this->tone);
        if ($tone === '') {
            return 'neutral';
        }

        if (isset(\App\Support\LeadCategoryUi::colors()[$tone]) || $tone === 'caution') {
            return $tone;
        }

        return 'neutral';
    }
}
