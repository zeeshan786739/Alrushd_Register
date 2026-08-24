<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermsAndCondition extends Model
{
    protected $guarded = [];

    /** Singleton row used across admissions / staff forms. */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
