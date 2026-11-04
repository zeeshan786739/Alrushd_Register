<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Pending = 'pending';
    case Enrolled = 'enrolled';
    case NotInterested = 'not_interested';
    case Spam = 'spam';

    /**
     * Retired pipeline statuses and the status each one was merged into.
     * Used for old bookmarks, spreadsheet imports and plain-English search.
     *
     * @var array<string, string>
     */
    public const LEGACY_MAP = [
        'contacted' => 'pending',
        'proposal_sent' => 'pending',
        'negotiation' => 'pending',
        'on_hold' => 'pending',
        'won' => 'enrolled',
        'qualified' => 'enrolled',
        'lost' => 'not_interested',
    ];

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Pending => 'Pending',
            self::Enrolled => 'Enrolled',
            self::NotInterested => 'Not Interested',
            self::Spam => 'Spam',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Statuses where no further follow-up is expected.
     *
     * @return list<string>
     */
    public static function closedValues(): array
    {
        return [self::Enrolled->value, self::NotInterested->value, self::Spam->value];
    }

    /** Translate a retired status to its replacement; any other value is returned unchanged. */
    public static function canonicalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return self::LEGACY_MAP[$value] ?? $value;
    }
}
