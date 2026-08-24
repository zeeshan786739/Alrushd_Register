<?php

namespace App\Support;

use App\Models\SaasPlan;

class SaasPlanDefaults
{
    public static function forNewPlan(): array
    {
        return config('saas_plans.defaults', []);
    }

    public static function limitDefinitions(): array
    {
        return config('saas_plans.limit_definitions', []);
    }

    public static function featureSuggestions(): array
    {
        return config('saas_plans.feature_suggestions', []);
    }

    public static function ensureDefaultPlanExists(): void
    {
        if (SaasPlan::where('is_default', true)->exists()) {
            return;
        }

        $candidate = SaasPlan::active()->ordered()->first()
            ?? SaasPlan::ordered()->first();

        $candidate?->update(['is_default' => true]);
    }
}
