<?php

namespace App\Support;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use Illuminate\Support\Collection;

final class LeadFormOptions
{
    /**
     * @param  Collection<int, \App\Models\Admin>  $admins
     * @param  Collection<int, \App\Models\Crm\LeadCategory>  $categories
     * @return array{
     *     statusFormOptions: array<string, array{label: string, tone: string, icon: string}>,
     *     priorityFormOptions: array<string, array{label: string, tone: string, icon: string}>,
     *     assigneeFormOptions: array<string, array{label: string, tone: string, icon: string}>,
     *     categoryFormOptions: array<string, array{label: string, tone: string, icon: string}>
     * }
     */
    public static function for(Collection $admins, Collection $categories): array
    {
        $statusFormOptions = [];
        foreach (LeadStatus::cases() as $status) {
            $statusFormOptions[$status->value] = [
                'label' => $status->label(),
                'tone' => CrmStatusTone::for($status->value),
                'icon' => CrmStatusTone::icon($status->value),
            ];
        }

        $priorityFormOptions = [];
        foreach (LeadPriority::cases() as $priority) {
            $priorityFormOptions[$priority->value] = [
                'label' => $priority->label(),
                'tone' => CrmStatusTone::for($priority->value),
                'icon' => CrmStatusTone::icon($priority->value),
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

        return compact('statusFormOptions', 'priorityFormOptions', 'assigneeFormOptions', 'categoryFormOptions');
    }
}
