<?php

namespace App\Enums\Platform;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Incomplete = 'incomplete';
    case Complimentary = 'complimentary';

    public function label(): string
    {
        return match ($this) {
            self::Trialing => 'Trialing',
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Canceled => 'Canceled',
            self::Incomplete => 'Incomplete',
            self::Complimentary => 'Complimentary',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Trialing => 'bg-info-focus text-info-main',
            self::Active, self::Complimentary => 'bg-success-focus text-success-main',
            self::PastDue, self::Incomplete => 'bg-warning-focus text-warning-main',
            self::Canceled => 'bg-danger-focus text-danger-main',
        };
    }

    public function isCurrent(): bool
    {
        return in_array($this, [self::Trialing, self::Active, self::PastDue, self::Complimentary], true);
    }
}
