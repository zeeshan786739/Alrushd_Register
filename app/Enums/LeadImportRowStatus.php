<?php

namespace App\Enums;

enum LeadImportRowStatus: string
{
    case Ready = 'ready';
    case Warning = 'warning';
    case Invalid = 'invalid';
    case Duplicate = 'duplicate';
    case Imported = 'imported';
    case Skipped = 'skipped';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Ready => 'Ready',
            self::Warning => 'Warning',
            self::Invalid => 'Invalid',
            self::Duplicate => 'Duplicate',
            self::Imported => 'Imported',
            self::Skipped => 'Skipped',
            self::Failed => 'Failed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Ready, self::Imported => 'bg-success-focus text-success-main',
            self::Warning => 'bg-warning-focus text-warning-main',
            self::Duplicate, self::Skipped => 'bg-neutral-200 text-secondary-light',
            self::Invalid, self::Failed => 'bg-danger-focus text-danger-main',
        };
    }
}
