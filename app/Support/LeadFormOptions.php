<?php

namespace App\Support;

use App\Services\FormOptionsResolver;
use Illuminate\Support\Collection;

final class LeadFormOptions
{
    /** A lead is one family: the first child's year is year_group, the rest are extra_year_groups. */
    public const MAX_CHILDREN = 4;

    /** Filter value meaning "this field is blank", used to find leads that still need tagging. */
    public const FILTER_NOT_SET = '__none__';

    /** The intake the team filters by most, offered as a one-click filter on the Leads page. */
    public const FEATURED_ADMISSION_INTAKE = 'Sept 27/28';

    /**
     * @param  Collection<int, \App\Models\Admin>  $admins
     * @param  Collection<int, \App\Models\Crm\LeadCategory>  $categories
     * @return array{
     *     statusFormOptions: array<string, array{label: string, tone: string, icon: string}>,
     *     assigneeFormOptions: array<string, array{label: string, tone: string, icon: string}>,
     *     categoryFormOptions: array<string, array{label: string, tone: string, icon: string}>,
     *     yearGroupOptions: list<string>,
     *     contactMethodOptions: list<string>
     * }
     */
    public static function for(Collection $admins, Collection $categories): array
    {
        $statusFormOptions = [];
        foreach (\App\Support\LeadStatusCatalog::ordered() as $status) {
            $statusFormOptions[$status->value] = [
                'label' => $status->label(),
                'tone' => $status->tone,
                'icon' => CrmStatusTone::icon($status->value),
            ];
        }

        $assigneeFormOptions = ['' => ['label' => 'Unassigned', 'tone' => 'neutral', 'icon' => 'solar:user-linear']];
        foreach ($admins as $admin) {
            $assigneeFormOptions[(string) $admin->id] = [
                'label' => $admin->name,
                'tone' => 'neutral',
                'icon' => 'solar:user-linear',
            ];
        }

        $categoryFormOptions = ['' => ['label' => 'Uncategorized', 'tone' => 'neutral', 'icon' => 'solar:folder-linear']];
        foreach ($categories as $category) {
            $categoryFormOptions[(string) $category->id] = [
                'label' => $category->name,
                'tone' => $category->displayTone(),
                'icon' => $category->displayIcon(),
            ];
        }

        return array_merge(
            compact('statusFormOptions', 'assigneeFormOptions', 'categoryFormOptions'),
            ['yearGroupOptions' => self::yearGroups(), 'contactMethodOptions' => self::contactMethods()],
        );
    }

    /**
     * Year groups from Enrollment Setup, so leads use the same list as the public forms.
     * Falls back to the UK school years for an organization that has not set any up.
     *
     * @return list<string>
     */
    public static function yearGroups(): array
    {
        $configured = collect(app(FormOptionsResolver::class)->forOrganization(OrganizationContext::id())->resolve('student_years'))
            ->pluck('value')
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->values();

        if ($configured->isEmpty()) {
            return array_merge(['Reception'], array_map(fn (int $year) => 'Year '.$year, range(1, 13)));
        }

        return $configured->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
    }

    /** @return list<string> */
    public static function contactMethods(): array
    {
        return ['Phone call', 'WhatsApp', 'Email', 'SMS', 'In person', 'Other'];
    }

    /** @return list<string> */
    public static function admissionIntakes(): array
    {
        return ['Sept 26/27', self::FEATURED_ADMISSION_INTAKE, 'Sept 28/29'];
    }

    /**
     * Read an intake from free text or a date: "Sept 2027", "27/28", "2027-09-01".
     * Returns null unless it clearly means a September start, so that a date such as
     * 10/11/2026 is never mistaken for the 10/11 academic year.
     */
    public static function normalizeAdmissionIntake(?string $value): ?string
    {
        $text = trim((string) $value);

        if (preg_match('/^(20\d{2})-(\d{1,2})-\d{1,2}/', $text, $match)) {
            return (int) $match[2] === 9 ? self::intakeForYear((int) substr($match[1], 2)) : null;
        }
        if (preg_match('/^\d{1,2}[\/.-](\d{1,2})[\/.-](?:20)?(\d{2})$/', $text, $match)) {
            return (int) $match[1] === 9 ? self::intakeForYear((int) $match[2]) : null;
        }
        if (preg_match('/(?<!\d)(?:20)?(\d{2})\s*[\/-]\s*(?:20)?(\d{2})(?!\d)/', $text, $match) && (int) $match[2] === (int) $match[1] + 1) {
            return self::intakeForYear((int) $match[1]);
        }
        if (preg_match('/\bsep(?:t|tember)?\b/i', $text)) {
            if (preg_match('/\b20(\d{2})\b/', $text, $match)) {
                return self::intakeForYear((int) $match[1]);
            }
            if (preg_match('/\bsep(?:t|tember)?\b\W*(\d{2})\b/i', $text, $match)) {
                return self::intakeForYear((int) $match[1]);
            }
        }

        return null;
    }

    private static function intakeForYear(int $year): string
    {
        return sprintf('Sept %02d/%02d', $year, $year + 1);
    }

    /**
     * Close any gaps in the children entered on a form, so the first filled year is
     * always year_group. Twins in the same year stay as two entries.
     *
     * @return array{year_group: ?string, extra_year_groups: ?list<string>}
     */
    public static function collapseChildren(mixed $yearGroup, array $extras): array
    {
        $years = array_values(array_filter(
            [$yearGroup, ...$extras],
            fn ($year) => is_string($year) && trim($year) !== ''
        ));

        return [
            'year_group' => $years[0] ?? null,
            'extra_year_groups' => array_slice($years, 1) ?: null,
        ];
    }

    /** Align common spreadsheet spellings with the dropdown; anything else is kept as entered. */
    public static function normalizeContactMethod(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return match (mb_strtolower($value)) {
            'call', 'phone', 'phone call', 'telephone', 'called' => 'Phone call',
            'whatsapp', 'whats app', 'wa' => 'WhatsApp',
            'email', 'e-mail', 'mail' => 'Email',
            'sms', 'text', 'text message' => 'SMS',
            'in person', 'in-person', 'visit' => 'In person',
            default => $value,
        };
    }

    /**
     * Keep a saved value selectable when it is not in the list (older spreadsheet data).
     *
     * @param  list<string>  $options
     * @return list<string>
     */
    public static function withCurrent(array $options, ?string $current): array
    {
        if ($current !== null && $current !== '' && ! in_array($current, $options, true)) {
            $options[] = $current;
        }

        return $options;
    }
}
