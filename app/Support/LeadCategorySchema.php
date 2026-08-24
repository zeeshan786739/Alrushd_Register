<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Defensive column checks so CRM keeps working before the category migration is applied.
 */
final class LeadCategorySchema
{
    public static function ready(): bool
    {
        return Schema::hasTable('lead_categories')
            && Schema::hasColumn('crm_leads', 'lead_category_id')
            && Schema::hasColumn('crm_lead_imports', 'lead_category_id');
    }
}
