<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = static::all_cached();

        $value = $all[$key] ?? null;

        return ($value !== null && $value !== '') ? $value : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('platform_settings');
    }

    /** @return array<string, ?string> */
    public static function all_cached(): array
    {
        try {
            return Cache::remember('platform_settings', 300, function () {
                return static::query()->pluck('value', 'key')->all();
            });
        } catch (\Throwable) {
            return [];
        }
    }
}
