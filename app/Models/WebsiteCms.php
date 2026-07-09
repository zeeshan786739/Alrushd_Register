<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteCms extends Model
{
    protected $table = 'website_cms';

    protected $fillable = [
        'draft',
        'published',
        'version_history',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'draft' => 'array',
        'published' => 'array',
        'version_history' => 'array',
        'published_at' => 'datetime',
    ];

    public static function instance(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
