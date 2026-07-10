<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
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
        return $this->legacy_route ?: '/forms/'.$this->slug;
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
