<?php

namespace App\Support;

use App\Enums\LeadStatus;
use App\Models\Crm\LeadStatusDefinition;
use App\Models\Organization;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Resolves the full lead-status catalog for an organization:
 * built-in pipeline statuses + optional custom statuses.
 */
final class LeadStatusCatalog
{
    public static function ready(): bool
    {
        return Schema::hasTable('crm_lead_statuses');
    }

    /**
     * @return list<LeadStatusOption>
     */
    public static function ordered(?Organization $organization = null): array
    {
        return LeadPipeline::orderedStatuses($organization);
    }

    /** @return array<string, string> value => label */
    public static function options(?Organization $organization = null): array
    {
        $options = [];
        foreach (self::ordered($organization) as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }

    /** @return list<string> */
    public static function values(?Organization $organization = null): array
    {
        return array_keys(self::options($organization));
    }

    public static function label(?string $value, ?Organization $organization = null): string
    {
        $value = (string) $value;
        if ($value === '') {
            return '';
        }

        $enum = LeadStatus::tryFrom($value);
        if ($enum) {
            return $enum->label();
        }

        foreach (self::ordered($organization) as $status) {
            if ($status->value === $value) {
                return $status->label();
            }
        }

        return str_replace('_', ' ', $value);
    }

    public static function tone(?string $value, ?Organization $organization = null): string
    {
        $value = (string) $value;
        $enum = LeadStatus::tryFrom($value);
        if ($enum) {
            return CrmStatusTone::for($enum->value);
        }

        foreach (self::ordered($organization) as $status) {
            if ($status->value === $value) {
                return $status->tone;
            }
        }

        return CrmStatusTone::for($value);
    }

    public static function isValid(?string $value, ?Organization $organization = null): bool
    {
        return in_array((string) $value, self::values($organization), true);
    }

    public static function validationRule(?Organization $organization = null): \Illuminate\Validation\Rules\In
    {
        return Rule::in(self::values($organization));
    }

    /**
     * @return list<LeadStatusOption>
     */
    public static function customOptions(?Organization $organization = null): array
    {
        if (! self::ready()) {
            return [];
        }

        $organization ??= self::currentOrganization();
        if (! $organization) {
            return [];
        }

        return LeadStatusDefinition::query()
            ->where('organization_id', $organization->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (LeadStatusDefinition $row) => new LeadStatusOption(
                value: $row->slug,
                labelText: $row->name,
                tone: $row->displayTone(),
                isCustom: true,
            ))
            ->all();
    }

    public static function makeSlug(string $name, int $organizationId): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'status';
        }

        $base = Str::limit($base, 50, '');
        $reserved = array_column(LeadStatus::cases(), 'value');
        $slug = $base;
        $i = 2;

        while (
            in_array($slug, $reserved, true)
            || LeadStatusDefinition::query()
                ->where('organization_id', $organizationId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = Str::limit($base, 45, '').'-'.$i;
            $i++;
        }

        return $slug;
    }

    private static function currentOrganization(): ?Organization
    {
        $id = OrganizationContext::id();

        return $id ? Organization::query()->find($id) : null;
    }
}
