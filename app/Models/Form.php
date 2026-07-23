<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'description',
        'legacy_route',
        'legacy_table',
        'success_route',
        'submit_method',
        'is_active',
        'show_on_landing',
        'hero_label',
        'hero_variant',
        'handler',
        'settings',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_on_landing' => 'boolean',
        'settings' => 'array',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(FormStep::class)->orderBy('sort_order');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FormEntry::class)->latest('submitted_at');
    }

    public function routePath(): string
    {
        if ($this->handler === 'custom') {
            $legacy = ltrim((string) $this->legacy_route, '/');

            return $legacy !== '' ? '/'.$legacy : '/forms/'.$this->slug;
        }

        return '/forms/'.$this->slug;
    }

    public function successPath(): string
    {
        if ($this->handler === 'custom') {
            $success = ltrim((string) $this->success_route, '/');

            return $success !== '' ? '/'.$success : '/forms/'.$this->slug.'/success';
        }

        $success = ltrim((string) $this->success_route, '/');
        if ($success !== '' && str_starts_with($success, 'forms/')) {
            return '/'.$success;
        }

        return '/forms/'.$this->slug.'/success';
    }

    public function usesDynamicRenderer(): bool
    {
        return $this->handler !== 'custom';
    }

    public static function resolveForLegacyRedirect(string $path): ?self
    {
        $path = ltrim($path, '/');

        return static::query()
            ->where(function ($query) use ($path) {
                $query->where('slug', $path)
                    ->orWhere('legacy_route', $path)
                    ->orWhere('legacy_route', '/'.$path);
            })
            ->first();
    }

    public static function resolveForPublicPath(string $path): ?self
    {
        $path = ltrim($path, '/');

        return static::query()
            ->where('is_active', true)
            ->where(function ($query) use ($path) {
                $query->where('slug', $path)
                    ->orWhere('legacy_route', $path)
                    ->orWhere('legacy_route', '/'.$path);
            })
            ->first();
    }

    public static function placementKeys(): array
    {
        return array_keys(config('form_options.placements', []));
    }

    public function placements(): array
    {
        $allowed = self::placementKeys();
        $stored = $this->settings['placements'] ?? null;

        if (is_array($stored)) {
            return array_values(array_intersect($stored, $allowed));
        }

        return $this->show_on_landing ? ['landing'] : [];
    }

    public function hasPlacement(string $placement): bool
    {
        return in_array($placement, $this->placements(), true);
    }

    public function setPlacements(array $placements): void
    {
        $allowed = self::placementKeys();
        $placements = array_values(array_unique(array_intersect($placements, $allowed)));

        $settings = $this->settings ?? [];
        $settings['placements'] = $placements;
        $this->settings = $settings;
        $this->show_on_landing = in_array('landing', $placements, true);
    }

    public function displayLabel(): string
    {
        return $this->hero_label ?: $this->name;
    }

    public function toNavLink(): array
    {
        return [
            'label' => $this->displayLabel(),
            'href' => $this->routePath(),
            'slug' => $this->slug,
        ];
    }
}
