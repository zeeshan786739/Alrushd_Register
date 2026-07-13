<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'form_step_id',
        'key',
        'label',
        'type',
        'placeholder',
        'required',
        'options',
        'options_source',
        'validation',
        'col_span',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'validation' => 'array',
        'settings' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(FormStep::class, 'form_step_id');
    }
}
