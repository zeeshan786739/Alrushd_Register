<?php

namespace App\Models;

use App\Enums\Platform\PlanBillingInterval;
use App\Support\PlanEntitlements;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'tagline', 'description', 'price', 'currency',
        'billing_interval', 'trial_days', 'features', 'modules', 'limits',
        'stripe_product_id', 'stripe_price_id', 'is_active', 'is_featured', 'is_default', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'trial_days' => 'integer',
            'features' => 'array',
            'modules' => 'array',
            'limits' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (SaasPlan $plan): void {
            if ($plan->is_default) {
                static::query()
                    ->where('id', '!=', $plan->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
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

    public function billingInterval(): PlanBillingInterval
    {
        return PlanBillingInterval::tryFrom((string) $this->billing_interval) ?? PlanBillingInterval::Month;
    }

    public function isLifetime(): bool
    {
        return $this->billingInterval() === PlanBillingInterval::Lifetime;
    }

    public function currencySymbol(): string
    {
        return match (strtoupper($this->currency)) {
            'GBP' => '£',
            'EUR' => '€',
            'USD' => '$',
            default => strtoupper($this->currency).' ',
        };
    }

    public function formattedPrice(): string
    {
        return $this->currencySymbol().number_format((float) $this->price, (float) $this->price == (int) $this->price ? 0 : 2);
    }

    public function formattedPriceWithInterval(): string
    {
        if ($this->isLifetime()) {
            return $this->formattedPrice().' once';
        }

        return $this->formattedPrice().'/'.$this->billingInterval()->shortLabel();
    }

    public function isSyncedToStripe(): bool
    {
        return filled($this->stripe_price_id);
    }

    public function isFree(): bool
    {
        return (float) $this->price <= 0;
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->modules ?? [], true);
    }

    public static function marketingFeaturesFromModules(array $moduleKeys, array $extra = []): array
    {
        return array_values(array_unique([
            ...PlanEntitlements::marketingLines($moduleKeys),
            ...array_filter($extra),
        ]));
    }

    public function enabledModuleCount(): int
    {
        return count($this->modules ?? []);
    }

    public function limitValue(string $key): ?int
    {
        $value = $this->limits[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    public function limitLabel(string $key): ?string
    {
        $definitions = config('saas_plans.limit_definitions', []);
        $value = $this->limitValue($key);

        if ($value === null) {
            return null;
        }

        $label = $definitions[$key]['label'] ?? $key;

        return $label.': '.$value;
    }

    public function limitsSummary(): array
    {
        $definitions = config('saas_plans.limit_definitions', []);
        $summary = [];

        foreach ($definitions as $key => $definition) {
            $value = $this->limitValue($key);
            if ($value !== null) {
                $summary[] = ($definition['label'] ?? $key).': '.number_format($value);
            }
        }

        return $summary;
    }

    public static function defaultPlan(): ?self
    {
        return static::active()->where('is_default', true)->first()
            ?? static::active()->where('is_featured', true)->ordered()->first()
            ?? static::active()->ordered()->first();
    }
}
