<?php

namespace App\Support;

use App\Enums\LeadStatus;
use App\Models\Organization;

class LeadPipeline
{
    public const METADATA_KEY = 'crm_pipeline_status_order';

    /**
     * Ordered pipeline statuses for the current (or given) organization.
     * Built-in enum statuses stay first (with saved reorder), then org custom statuses.
     *
     * @return list<LeadStatusOption>
     */
    public static function orderedStatuses(?Organization $organization = null): array
    {
        $organization ??= self::currentOrganization();
        $system = self::orderedSystemStatuses($organization);
        $custom = LeadStatusCatalog::customOptions($organization);

        $byValue = [];
        foreach (array_merge($system, $custom) as $status) {
            $byValue[$status->value] = $status;
        }

        $saved = self::savedOrder($organization);
        if ($saved === []) {
            return array_values($byValue);
        }

        $ordered = [];
        foreach ($saved as $value) {
            if (isset($byValue[$value])) {
                $ordered[] = $byValue[$value];
                unset($byValue[$value]);
            }
        }

        foreach ($byValue as $status) {
            $ordered[] = $status;
        }

        return $ordered;
    }

    /**
     * @return list<LeadStatusOption>
     */
    public static function orderedSystemStatuses(?Organization $organization = null): array
    {
        $organization ??= self::currentOrganization();
        $defaults = LeadStatus::cases();
        $saved = self::savedOrder($organization);

        $byValue = [];
        foreach ($defaults as $status) {
            $byValue[$status->value] = new LeadStatusOption(
                value: $status->value,
                labelText: $status->label(),
                tone: CrmStatusTone::for($status->value),
                isCustom: false,
            );
        }

        if ($saved === []) {
            return array_values($byValue);
        }

        $ordered = [];
        foreach ($saved as $value) {
            if (isset($byValue[$value])) {
                $ordered[] = $byValue[$value];
                unset($byValue[$value]);
            }
        }

        foreach ($byValue as $status) {
            $ordered[] = $status;
        }

        return $ordered;
    }

    /**
     * SQL expression that orders lead_status by the org pipeline order.
     * Safe for MySQL FIELD(); unknown values sort first (FIELD returns 0).
     */
    public static function statusOrderSql(string $column = 'lead_status'): string
    {
        $values = array_map(
            static fn (LeadStatusOption $status): string => "'".str_replace("'", "''", $status->value)."'",
            self::orderedStatuses()
        );

        if ($values === []) {
            return '0';
        }

        return 'FIELD('.$column.', '.implode(', ', $values).')';
    }

    /**
     * Persist a full pipeline column order for an organization.
     *
     * @param  list<string>  $statuses
     * @return list<LeadStatusOption>
     */
    public static function saveOrder(Organization $organization, array $statuses): array
    {
        $allowed = LeadStatusCatalog::values($organization);
        $normalized = [];

        foreach ($statuses as $value) {
            $value = (string) $value;
            if (! in_array($value, $allowed, true) || in_array($value, $normalized, true)) {
                continue;
            }
            $normalized[] = $value;
        }

        foreach ($allowed as $value) {
            if (! in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        if (count($normalized) !== count($allowed)) {
            throw new \InvalidArgumentException('Pipeline order must include every lead status exactly once.');
        }

        $metadata = $organization->metadata ?? [];
        $metadata[self::METADATA_KEY] = $normalized;
        $organization->metadata = $metadata;
        $organization->save();

        return self::orderedStatuses($organization);
    }

    /**
     * @return list<string>
     */
    private static function savedOrder(?Organization $organization): array
    {
        if (! $organization) {
            return [];
        }

        $order = $organization->metadata[self::METADATA_KEY] ?? null;
        if (! is_array($order)) {
            return [];
        }

        return array_values(array_filter($order, static fn ($value) => is_string($value) && $value !== ''));
    }

    private static function currentOrganization(): ?Organization
    {
        $id = OrganizationContext::id();
        if (! $id) {
            return null;
        }

        return Organization::query()->find($id);
    }
}
