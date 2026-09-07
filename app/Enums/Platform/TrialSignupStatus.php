<?php

namespace App\Enums\Platform;

enum TrialSignupStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning-focus text-warning-main',
            self::Approved => 'bg-success-focus text-success-main',
            self::Rejected => 'bg-danger-focus text-danger-main',
        };
    }
}
