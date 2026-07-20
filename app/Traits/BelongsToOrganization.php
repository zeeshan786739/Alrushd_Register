<?php

namespace App\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization(): void
    {
        static::creating(function ($model): void {
            if (! empty($model->organization_id)) {
                return;
            }

            $admin = Auth::guard('admin')->user();
            if ($admin && $admin->organization_id) {
                $model->organization_id = $admin->organization_id;
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeForCurrentOrganization(Builder $query): Builder
    {
        $organizationId = Auth::guard('admin')->user()?->organization_id;

        if (! $organizationId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($query->getModel()->getTable().'.organization_id', $organizationId);
    }

    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->where($query->getModel()->getTable().'.organization_id', $organizationId);
    }
}
