<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Organization-scoped existence rules for CRM foreign keys.
 * Prefer these over bare exists:table,id for tenant safety.
 */
final class CrmOrgRules
{
    public static function currentOrganizationId(): int
    {
        return OrganizationContext::idOrFail();
    }

    public static function adminId(): Exists
    {
        return Rule::exists('admins', 'id')
            ->where(fn ($query) => $query->where('organization_id', self::currentOrganizationId()));
    }

    public static function customerId(): Exists
    {
        return Rule::exists('crm_customers', 'id')
            ->where(fn ($query) => $query
                ->where('organization_id', self::currentOrganizationId())
                ->whereNull('deleted_at'));
    }

    public static function projectId(): Exists
    {
        return Rule::exists('crm_projects', 'id')
            ->where(fn ($query) => $query
                ->where('organization_id', self::currentOrganizationId())
                ->whereNull('deleted_at'));
    }

    public static function quotationId(): Exists
    {
        return Rule::exists('crm_quotations', 'id')
            ->where(fn ($query) => $query
                ->where('organization_id', self::currentOrganizationId())
                ->whereNull('deleted_at'));
    }

    public static function leadCategoryId(bool $activeOnly = false): Exists
    {
        return Rule::exists('lead_categories', 'id')
            ->where(function ($query) use ($activeOnly) {
                $query->where('organization_id', self::currentOrganizationId());
                if ($activeOnly) {
                    $query->where('is_active', true);
                }
            });
    }
}
