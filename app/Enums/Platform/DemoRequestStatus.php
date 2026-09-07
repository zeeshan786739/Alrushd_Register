<?php

namespace App\Enums\Platform;

enum DemoRequestStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case DemoScheduled = 'demo_scheduled';
    case Approved = 'approved';
    case Converted = 'converted';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::DemoScheduled => 'Demo Scheduled',
            self::Approved => 'Approved',
            self::Converted => 'Converted',
            self::Closed => 'Closed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::New => 'bg-info-focus text-info-main',
            self::Contacted => 'bg-warning-focus text-warning-main',
            self::DemoScheduled => 'bg-primary-50 text-primary-600',
            self::Approved, self::Converted => 'bg-success-focus text-success-main',
            self::Closed => 'bg-neutral-200 text-neutral-600',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::New, self::Contacted, self::DemoScheduled], true);
    }
}
