<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormEntry extends Model
{
    protected $fillable = [
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
}
