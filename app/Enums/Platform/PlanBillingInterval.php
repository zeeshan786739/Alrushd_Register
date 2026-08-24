<?php

namespace App\Enums\Platform;

enum PlanBillingInterval: string
{
    case Month = 'month';
    case Year = 'year';
    case Lifetime = 'lifetime';

    public function label(): string
    {
        return match ($this) {
            self::Month => 'Monthly',
            self::Year => 'Yearly',
            self::Lifetime => 'Lifetime (one-time)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Month => 'month',
            self::Year => 'year',
            self::Lifetime => 'lifetime',
        };
    }

    public function isRecurring(): bool
    {
        return $this !== self::Lifetime;
    }

    public function stripeInterval(): ?string
    {
        return match ($this) {
            self::Month => 'month',
            self::Year => 'year',
            self::Lifetime => null,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
