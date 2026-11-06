<?php

namespace App\Support;

use App\Enums\LeadStatus;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * What each organization has chosen for its Leads page: which filters show and where,
 * the quick buttons, and the dropdown lists. Stored in the organization's metadata;
 * anything not customized falls back to the defaults below.
 */
final class LeadSettings
{
    public const METADATA_KEY = 'crm_lead_settings';

    public const PLACEMENT_MAIN = 'main';

    public const PLACEMENT_MORE = 'more';

    public const PLACEMENT_HIDDEN = 'hidden';

    public const CUSTOM_PARAM_PREFIX = 'cf_';

    /** key => [default label, default placement]. The key is also the URL parameter. */
    private const FILTERS = [
        'lead_status' => ['Status', self::PLACEMENT_MAIN],
        'year_group' => ['Year group', self::PLACEMENT_MAIN],
        'admission_intake' => ['Admission intake', self::PLACEMENT_MAIN],
        'assigned_to' => ['Assignee', self::PLACEMENT_MAIN],
        'lead_category_id' => ['Category', self::PLACEMENT_MORE],
        'follow_up' => ['Follow-up', self::PLACEMENT_MORE],
        'contact_method' => ['Contact method', self::PLACEMENT_MORE],
        'contact_details' => ['Contact details', self::PLACEMENT_MORE],
        'children' => ['Children', self::PLACEMENT_MORE],
        'converted' => ['Converted to customer', self::PLACEMENT_MORE],
        'campaign_name' => ['Campaign contains', self::PLACEMENT_MORE],
    ];

    public const FOLLOW_UP_OPTIONS = ['today' => 'Due today', 'overdue' => 'Overdue', 'upcoming' => 'Upcoming', 'none' => 'No follow-up set'];

    public const CONTACT_DETAIL_OPTIONS = ['has_phone' => 'Has a phone number', 'has_email' => 'Has an email', 'missing_phone' => 'No phone number', 'missing_email' => 'No email'];

    public const CHILDREN_OPTIONS = ['multiple' => 'More than one'];

    public const CONVERTED_OPTIONS = ['no' => 'Not yet', 'yes' => 'Yes'];

    public static function raw(?int $organizationId = null): array
    {
        $organizationId ??= OrganizationContext::id();
        if (! $organizationId) {
            return [];
        }

        $key = 'crm.lead-settings.'.$organizationId;
        if (! app()->bound($key)) {
            $metadata = Organization::query()->whereKey($organizationId)->value('metadata');
            $metadata = is_string($metadata) ? json_decode($metadata, true) : $metadata;
            $settings = is_array($metadata[self::METADATA_KEY] ?? null) ? $metadata[self::METADATA_KEY] : [];
            app()->instance($key, $settings);
        }

        return app($key);
    }

    /** Change the saved settings safely, with the organization row locked. */
    public static function update(callable $change, ?int $organizationId = null): void
    {
        $organizationId ??= OrganizationContext::idOrFail();

        DB::transaction(function () use ($change, $organizationId) {
            $organization = Organization::query()->lockForUpdate()->findOrFail($organizationId);
            $metadata = $organization->metadata ?? [];
            $settings = is_array($metadata[self::METADATA_KEY] ?? null) ? $metadata[self::METADATA_KEY] : [];
            $metadata[self::METADATA_KEY] = $change($settings);
            $organization->metadata = $metadata;
            $organization->save();
        });

        app()->forgetInstance('crm.lead-settings.'.$organizationId);
    }

    /**
     * Every filter in the order chosen, with new built-in filters added at the end.
     *
     * @return list<array{key: string, param: string, label: string, default_label: string, placement: string, custom: bool}>
     */
    public static function filters(): array
    {
        $raw = self::raw();

        $known = [];
        foreach (self::FILTERS as $key => [$label, $placement]) {
            if ($key === 'lead_category_id' && ! LeadCategorySchema::ready()) {
                continue;
            }
            $known[$key] = ['key' => $key, 'param' => $key, 'label' => $label, 'default_label' => $label, 'placement' => $placement, 'custom' => false];
        }
        foreach (self::customFilters() as $filter) {
            $param = self::CUSTOM_PARAM_PREFIX.$filter['key'];
            $known[$param] = ['key' => $param, 'param' => $param, 'label' => $filter['label'], 'default_label' => $filter['label'], 'placement' => self::PLACEMENT_MORE, 'custom' => true];
        }

        $ordered = [];
        foreach ($raw['filters'] ?? [] as $saved) {
            $key = $saved['key'] ?? null;
            if (! is_string($key) || ! isset($known[$key])) {
                continue;
            }
            $filter = $known[$key];
            $filter['placement'] = in_array($saved['placement'] ?? null, [self::PLACEMENT_MAIN, self::PLACEMENT_MORE, self::PLACEMENT_HIDDEN], true)
                ? $saved['placement']
                : $filter['placement'];
            if (! $filter['custom'] && is_string($saved['label'] ?? null) && trim($saved['label']) !== '') {
                $filter['label'] = $saved['label'];
            }
            $ordered[] = $filter;
            unset($known[$key]);
        }

        return array_merge($ordered, array_values($known));
    }

    /** @return list<string> */
    public static function builtInFilterKeys(): array
    {
        return array_keys(self::FILTERS);
    }

    public static function filterLabel(string $key): string
    {
        foreach (self::filters() as $filter) {
            if ($filter['key'] === $key) {
                return $filter['label'];
            }
        }

        return self::FILTERS[$key][0] ?? $key;
    }

    /**
     * @return list<array{id: string, label: string, param: string, value: string}>
     */
    public static function quickFilters(): array
    {
        $saved = self::raw()['quick_filters'] ?? null;

        return is_array($saved) ? array_values($saved) : self::defaultQuickFilters();
    }

    /** @return list<array{id: string, label: string, param: string, value: string}> */
    public static function defaultQuickFilters(): array
    {
        return [
            ['id' => 'assigned_me', 'label' => 'My leads', 'param' => 'assigned_to', 'value' => 'me'],
            ['id' => 'unassigned', 'label' => 'Unassigned', 'param' => 'assigned_to', 'value' => 'unassigned'],
            ['id' => 'new', 'label' => 'New', 'param' => 'lead_status', 'value' => LeadStatus::New->value],
            ['id' => 'follow_up', 'label' => 'Due today', 'param' => 'follow_up', 'value' => 'today'],
            ['id' => 'overdue', 'label' => 'Overdue', 'param' => 'follow_up', 'value' => 'overdue'],
            ['id' => 'admission_intake', 'label' => LeadFormOptions::FEATURED_ADMISSION_INTAKE.' admissions', 'param' => 'admission_intake', 'value' => LeadFormOptions::FEATURED_ADMISSION_INTAKE],
        ];
    }

    public static function quickFilterIcon(string $param, string $value): string
    {
        return match (true) {
            $param === 'assigned_to' && $value === 'unassigned' => 'solar:user-cross-linear',
            $param === 'assigned_to' => 'solar:user-check-linear',
            $param === 'lead_status' => 'solar:star-linear',
            $param === 'follow_up' && $value === 'overdue' => 'solar:alarm-linear',
            $param === 'follow_up' => 'solar:calendar-linear',
            $param === 'admission_intake' => 'solar:calendar-mark-linear',
            $param === 'year_group' => 'solar:square-academic-cap-linear',
            $param === 'contact_method', $param === 'contact_details' => 'solar:phone-linear',
            $param === 'lead_category_id' => 'solar:folder-linear',
            default => 'solar:filter-linear',
        };
    }

    public static function showSources(): bool
    {
        return (bool) (self::raw()['show_sources'] ?? true);
    }

    public static function showDatePicker(): bool
    {
        return (bool) (self::raw()['show_date_picker'] ?? true);
    }

    /** @return list<string> */
    public static function admissionIntakes(): array
    {
        $saved = self::raw()['admission_intakes'] ?? null;

        return is_array($saved) ? array_values($saved) : ['Sept 26/27', LeadFormOptions::FEATURED_ADMISSION_INTAKE, 'Sept 28/29'];
    }

    /** @return list<string> */
    public static function contactMethods(): array
    {
        $saved = self::raw()['contact_methods'] ?? null;

        return is_array($saved) ? array_values($saved) : ['Phone call', 'WhatsApp', 'Email', 'SMS', 'In person', 'Other'];
    }

    /** @return list<array{key: string, label: string, options: list<string>}> */
    public static function customFilters(): array
    {
        $saved = self::raw()['custom_filters'] ?? [];

        return is_array($saved) ? array_values($saved) : [];
    }

    public static function customFilter(string $key): ?array
    {
        foreach (self::customFilters() as $filter) {
            if ($filter['key'] === $key) {
                return $filter;
            }
        }

        return null;
    }

    /** @return list<string> */
    public static function customFilterParams(): array
    {
        return array_map(fn (array $filter) => self::CUSTOM_PARAM_PREFIX.$filter['key'], self::customFilters());
    }

    /** The current name for a value that has since been renamed. */
    public static function canonical(string $list, ?string $value, ?int $organizationId = null): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        foreach (self::raw($organizationId)['aliases'][$list] ?? [] as $old => $new) {
            if (mb_strtolower((string) $old) === mb_strtolower($value)) {
                return $new;
            }
        }

        return $value;
    }

    public static function statusLabel(string $status, string $default, ?int $organizationId = null): string
    {
        $label = self::raw($organizationId)['status_labels'][$status] ?? null;

        return is_string($label) && $label !== '' ? $label : $default;
    }

    /**
     * Blank and unknown values are dropped; values of a removed filter stay on the lead,
     * so adding that filter back brings them back too.
     *
     * @return array<string, string>|null
     */
    public static function mergeCustomFields(array $input, ?array $existing = null): ?array
    {
        $fields = $existing ?? [];
        foreach (self::customFilters() as $filter) {
            if (! array_key_exists($filter['key'], $input)) {
                continue;
            }
            $value = $input[$filter['key']];
            if (is_string($value) && trim($value) !== '') {
                $fields[$filter['key']] = trim($value);
            } else {
                unset($fields[$filter['key']]);
            }
        }

        return $fields ?: null;
    }

    /**
     * Validation rules for the custom fields on the lead forms.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function customFieldRules(?array $current = null): array
    {
        $rules = [];
        foreach (self::customFilters() as $filter) {
            $allowed = LeadFormOptions::withCurrent($filter['options'], $current[$filter['key']] ?? null);
            $rules['custom_fields.'.$filter['key']] = ['nullable', 'string', Rule::in($allowed)];
        }

        return $rules === [] ? [] : ['custom_fields' => ['nullable', 'array']] + $rules;
    }
}
