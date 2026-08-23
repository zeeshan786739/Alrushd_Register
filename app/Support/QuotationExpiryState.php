<?php

namespace App\Support;

use App\Models\Crm\Quotation;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Display-only validity state for quotations (valid_until).
 * Does not invent DB statuses or override accepted/rejected/converted lifecycle.
 */
final class QuotationExpiryState
{
    public const NONE = 'none';

    public const VALID = 'valid';

    public const EXPIRING_SOON = 'expiring_soon';

    public const EXPIRED = 'expired';

    public const HOURS_SOON = 72;

    public function __construct(
        public readonly string $state,
        public readonly ?CarbonInterface $validUntil,
        public readonly string $label,
        public readonly string $badgeClass,
        public readonly bool $applies,
    ) {}

    public static function forQuotation(Quotation $quotation, ?CarbonInterface $now = null): self
    {
        $now = ($now ?? Carbon::now(config('app.timezone')))->copy();

        // Finalized commercial outcomes take precedence over expiry display.
        if (in_array($quotation->status, ['accepted', 'rejected'], true) || $quotation->converted_invoice_id) {
            return new self(self::NONE, $quotation->valid_until, '', 'crm-followup-badge crm-followup-badge--none', false);
        }

        if (! $quotation->valid_until) {
            return new self(self::NONE, null, 'No expiry', 'crm-followup-badge crm-followup-badge--none', false);
        }

        $until = $quotation->valid_until->copy()->endOfDay();

        if ($until->lt($now)) {
            return new self(
                self::EXPIRED,
                $until,
                'Expired '.$until->diffForHumans($now),
                'crm-followup-badge crm-followup-badge--overdue',
                true,
            );
        }

        $hoursRemaining = $now->diffInHours($until, false);
        if ($hoursRemaining <= self::HOURS_SOON) {
            return new self(
                self::EXPIRING_SOON,
                $until,
                'Expires '.$until->diffForHumans($now),
                'crm-followup-badge crm-followup-badge--due-soon',
                true,
            );
        }

        return new self(
            self::VALID,
            $until,
            'Valid until '.$quotation->valid_until->format('M j, Y'),
            'crm-followup-badge crm-followup-badge--upcoming',
            true,
        );
    }
}
