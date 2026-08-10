<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'tagline', 'description', 'price', 'currency',
        'billing_interval', 'trial_days', 'features', 'limits',
        'stripe_product_id', 'stripe_price_id', 'is_active', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'trial_days' => 'integer',
            'features' => 'array',
            'limits' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SaasSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function currencySymbol(): string
    {
        return match (strtoupper($this->currency)) {
            'GBP' => '£',
            'EUR' => '€',
            'USD' => '$',
            default => strtoupper($this->currency) . ' ',
        };
    }

    public function formattedPrice(): string
    {
        return $this->currencySymbol() . number_format((float) $this->price, (float) $this->price == (int) $this->price ? 0 : 2);
    }

    public function isSyncedToStripe(): bool
    {
        return filled($this->stripe_price_id);
    }
}
