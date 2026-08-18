<?php

namespace App\Enums;

enum TikTokLeadSubmissionStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Unmapped = 'unmapped';
    case Ignored = 'ignored';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processed => 'Processed',
            self::Unmapped => 'Needs mapping',
            self::Ignored => 'Disabled',
            self::Failed => 'Failed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Processed => 'bg-success-focus text-success-main',
            self::Pending => 'bg-warning-focus text-warning-main',
            self::Unmapped => 'bg-warning-focus text-warning-main',
            self::Ignored => 'bg-neutral-200 text-secondary-light',
            self::Failed => 'bg-danger-focus text-danger-main',
        };
    }

    public function canReprocess(): bool
    {
        return in_array($this, [self::Pending, self::Failed, self::Unmapped, self::Ignored], true);
    }
}
