<?php

namespace App\Support;

use App\Services\FormOptionsResolver;
use Illuminate\Support\Collection;

final class LeadFormOptions
{
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
