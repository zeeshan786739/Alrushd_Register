<?php

namespace App\Support;

use App\Models\Crm\Lead;
use Carbon\Carbon;
use Carbon\CarbonInterface;

final class LeadFollowUpState
{
    public const NONE = 'none';

    public const UPCOMING = 'upcoming';

    public const DUE_SOON = 'due_soon';

    public const DUE_NOW = 'due_now';

    public const OVERDUE = 'overdue';

    /** Minutes before/after scheduled time considered "due now". */
    public const DUE_NOW_WINDOW_MINUTES = 15;

    /** Hours ahead considered "due soon". */
    public const DUE_SOON_HOURS = 24;

    public function __construct(
        public readonly string $state,
        public readonly ?CarbonInterface $scheduledAt,
        public readonly string $label,
        public readonly string $detail,
        public readonly string $badgeClass,
        public readonly bool $attention,
    ) {}

    public static function forLead(Lead $lead, ?CarbonInterface $now = null): self
    {
        $now = ($now ?? Carbon::now(config('app.timezone')))->copy()->timezone(config('app.timezone'));

        if (! $lead->next_follow_up_date) {
            return new self(self::NONE, null, '—', '', 'crm-followup-badge crm-followup-badge--none', false);
        }

        $scheduledAt = self::scheduledAt($lead);
        $diffSeconds = $now->diffInSeconds($scheduledAt, false);
        $absMinutes = abs($diffSeconds) / 60;

        if ($diffSeconds < 0 && $absMinutes > self::DUE_NOW_WINDOW_MINUTES) {
            return new self(
                self::OVERDUE,
                $scheduledAt,
                'Missed '.$scheduledAt->diffForHumans($now),
                self::formatDetail($lead, $scheduledAt),
                'crm-followup-badge crm-followup-badge--overdue',
                true,
            );
        }

        if (abs($diffSeconds) <= self::DUE_NOW_WINDOW_MINUTES * 60) {
            return new self(
                self::DUE_NOW,
                $scheduledAt,
                'Due now',
                self::formatDetail($lead, $scheduledAt),
                'crm-followup-badge crm-followup-badge--due-now',
                true,
            );
        }

        if ($diffSeconds > 0 && $diffSeconds <= self::DUE_SOON_HOURS * 3600) {
            return new self(
                self::DUE_SOON,
                $scheduledAt,
                'Due '.$scheduledAt->diffForHumans($now),
                self::formatDetail($lead, $scheduledAt),
                'crm-followup-badge crm-followup-badge--due-soon',
                false,
            );
        }

        return new self(
            self::UPCOMING,
            $scheduledAt,
            $scheduledAt->format('M j, Y').($lead->next_follow_up_time ? ' '.$scheduledAt->format('g:i A') : ''),
            self::formatDetail($lead, $scheduledAt),
            'crm-followup-badge crm-followup-badge--upcoming',
            false,
        );
    }

    public static function scheduledAt(Lead $lead): Carbon
    {
        $date = $lead->next_follow_up_date instanceof CarbonInterface
            ? $lead->next_follow_up_date->copy()->timezone(config('app.timezone'))
            : Carbon::parse((string) $lead->next_follow_up_date, config('app.timezone'));

        $time = $lead->next_follow_up_time;
        if ($time) {
            $parts = explode(':', (string) $time);

            return $date->setTime((int) ($parts[0] ?? 0), (int) ($parts[1] ?? 0), (int) ($parts[2] ?? 0));
        }

        return $date->startOfDay();
    }

    private static function formatDetail(Lead $lead, CarbonInterface $scheduledAt): string
    {
        $parts = [$scheduledAt->format('M j, Y g:i A')];
        if ($lead->next_follow_up_type) {
            $parts[] = $lead->next_follow_up_type;
        }

        return implode(' · ', $parts);
    }

    public function hasFollowUp(): bool
    {
        return $this->state !== self::NONE;
    }
}
