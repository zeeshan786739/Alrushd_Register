<?php

namespace App\Enums;

enum LeadImportStatus: string
{
    case Uploaded = 'uploaded';
    case Mapped = 'mapped';
    case Previewed = 'previewed';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => 'Uploaded',
            self::Mapped => 'Mapped',
            self::Previewed => 'Previewed',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
        };
    }
}
