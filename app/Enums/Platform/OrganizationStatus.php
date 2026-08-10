<?php

namespace App\Enums\Platform;

enum OrganizationStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case PastDue = 'past_due';
    case Suspended = 'suspended';
    case Inactive = 'inactive';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Trial => 'Trial',
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Suspended => 'Suspended',
            self::Inactive => 'Inactive',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Trial => 'bg-info-focus text-info-main',
            self::Active => 'bg-success-focus text-success-main',
            self::PastDue => 'bg-warning-focus text-warning-main',
            self::Suspended, self::Cancelled => 'bg-danger-focus text-danger-main',
            self::Inactive => 'bg-neutral-200 text-neutral-600',
        };
    }

    /** Statuses that grant access to the tenant panel. */
    public function allowsAccess(): bool
    {
        return in_array($this, [self::Trial, self::Active, self::PastDue], true);
    }
}
