<?php

namespace App\Support;

use App\Models\Crm\Invoice;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Display-only due-date state for invoices.
 * Never marks paid/cancelled invoices as overdue.
 */
final class InvoiceDueState
{
    public const NONE = 'none';

    public const UPCOMING = 'upcoming';

    public const DUE_SOON = 'due_soon';

    public const DUE_TODAY = 'due_today';

    public const OVERDUE = 'overdue';

    public const PAID = 'paid';

    public const DUE_SOON_DAYS = 3;

    public function __construct(
        public readonly string $state,
        public readonly ?CarbonInterface $dueDate,
        public readonly string $label,
        public readonly string $badgeClass,
        public readonly bool $applies,
    ) {}

    public static function forInvoice(Invoice $invoice, ?CarbonInterface $now = null): self
    {
        $now = ($now ?? Carbon::now(config('app.timezone')))->copy()->startOfDay();

        if ($invoice->status === 'paid' || (float) $invoice->due_amount <= 0.001) {
            return new self(
                self::PAID,
                $invoice->due_date,
                'Paid',
                'crm-followup-badge crm-followup-badge--none',
                $invoice->status === 'paid',
            );
        }

        if ($invoice->status === 'cancelled' || ! $invoice->due_date) {
            return new self(self::NONE, $invoice->due_date, '', 'crm-followup-badge crm-followup-badge--none', false);
        }

        $due = $invoice->due_date->copy()->startOfDay();

        if ($due->lt($now)) {
            return new self(
                self::OVERDUE,
                $due,
                'Overdue '.$due->diffForHumans($now),
                'crm-followup-badge crm-followup-badge--overdue',
                true,
            );
        }

        if ($due->isSameDay($now)) {
            return new self(
                self::DUE_TODAY,
                $due,
                'Due today',
                'crm-followup-badge crm-followup-badge--due-now',
                true,
            );
        }

        if ($due->lte($now->copy()->addDays(self::DUE_SOON_DAYS))) {
            return new self(
                self::DUE_SOON,
                $due,
                'Due '.$due->diffForHumans($now),
                'crm-followup-badge crm-followup-badge--due-soon',
                true,
            );
        }

        return new self(
            self::UPCOMING,
            $due,
            'Due '.$due->format('M j, Y'),
            'crm-followup-badge crm-followup-badge--upcoming',
            true,
        );
    }
}
