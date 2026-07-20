<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public static function default(): self
    {
        return static::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Organization', 'is_active' => true]
        );
    }
}
