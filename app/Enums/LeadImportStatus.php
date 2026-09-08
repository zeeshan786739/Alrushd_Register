<?php

namespace App\Enums;

enum LeadImportStatus: string
{
    case Uploaded = 'uploaded';
    case Mapped = 'mapped';
    case Previewed = 'previewed';
    case Processing = 'processing';
    case Completed = 'completed';
    case Undone = 'undone';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => 'Uploaded',
            self::Mapped => 'Mapped',
            self::Previewed => 'Previewed',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Undone => 'Undone',
            self::Failed => 'Failed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Completed => 'bg-success-focus text-success-main',
            self::Undone => 'bg-warning-focus text-warning-main',
            self::Failed => 'bg-danger-focus text-danger-main',
            default => 'bg-neutral-200 text-secondary-light',
        };
    }
}
