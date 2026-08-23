<?php

namespace App\Support;

use App\Models\Crm\Project;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Display-only due-date state for CRM projects. Does not invent statuses.
 */
final class ProjectDueState
{
    public const NONE = 'none';

    public const UPCOMING = 'upcoming';

    public const DUE_SOON = 'due_soon';

    public const OVERDUE = 'overdue';

    public const COMPLETED = 'completed';

    /** Days ahead treated as due soon. */
    public const DUE_SOON_DAYS = 3;

    public function __construct(
        public readonly string $state,
        public readonly ?CarbonInterface $dueDate,
        public readonly string $label,
        public readonly string $badgeClass,
    ) {}

    public static function forProject(Project $project, ?CarbonInterface $now = null): self
    {
        $now = ($now ?? Carbon::now(config('app.timezone')))->copy()->startOfDay();

        if ($project->status === 'completed') {
            return new self(
                self::COMPLETED,
                $project->end_date,
                'Completed',
                'crm-followup-badge crm-followup-badge--none',
            );
        }

        if (! $project->end_date) {
            return new self(self::NONE, null, 'No due date', 'crm-followup-badge crm-followup-badge--none');
        }

        $due = $project->end_date->copy()->startOfDay();

        if ($due->lt($now)) {
            return new self(
                self::OVERDUE,
                $due,
                'Overdue '.$due->diffForHumans($now),
                'crm-followup-badge crm-followup-badge--overdue',
            );
        }

        if ($due->lte($now->copy()->addDays(self::DUE_SOON_DAYS))) {
            return new self(
                self::DUE_SOON,
                $due,
                $due->isSameDay($now) ? 'Due today' : 'Due '.$due->diffForHumans($now),
                'crm-followup-badge crm-followup-badge--due-soon',
            );
        }

        return new self(
            self::UPCOMING,
            $due,
            'Due '.$due->format('M j, Y'),
            'crm-followup-badge crm-followup-badge--upcoming',
        );
    }
}
