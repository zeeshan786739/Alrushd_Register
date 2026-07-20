<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class FormEntry extends Model
{
    protected $fillable = [
        'organization_id',
        'form_id',
        'legacy_source',
        'legacy_record_id',
        'entry_id',
        'data',
        'status',
        'ip_address',
        'submitted_at',
    ];

    protected $casts = [
        'data' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function scopeForCurrentOrganization(Builder $query): Builder
    {
        $organizationId = Auth::guard('admin')->user()?->organization_id;

        if (! $organizationId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $inner) use ($organizationId) {
            $inner->where('form_entries.organization_id', $organizationId)
                ->orWhereHas('form', fn (Builder $formQuery) => $formQuery->where('organization_id', $organizationId));
        });
    }
}
