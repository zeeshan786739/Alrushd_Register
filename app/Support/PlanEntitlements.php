<?php

namespace App\Support;

use App\Models\SaasPlan;

class PlanEntitlements
{
    public static function moduleCatalog(): array
    {
        return config('saas_plans.modules', []);
    }

    public static function allModuleKeys(): array
    {
        return array_keys(self::moduleCatalog());
    }

    public static function marketingLines(array $moduleKeys): array
    {
        $catalog = self::moduleCatalog();
        $lines = [];

        foreach ($moduleKeys as $key) {
            if (isset($catalog[$key]['marketing'])) {
                $lines[] = $catalog[$key]['marketing'];
            }
        }

        return array_values(array_unique($lines));
    }

    public static function organization(?\App\Models\Organization $organization = null): ?\App\Models\Organization
    {
        $organization ??= auth('admin')->user()?->organization;

        return $organization;
    }

    public static function plan(?\App\Models\Organization $organization = null): ?SaasPlan
    {
        $organization = self::organization($organization);

        if (! $organization) {
            return null;
        }

        return $organization->currentSubscription()->first()?->plan ?? SaasPlan::defaultPlan();
    }

    public static function enabledModules(?\App\Models\Organization $organization = null): array
    {
        $plan = self::plan($organization);

        if (! $plan) {
            $default = SaasPlan::defaultPlan();

            return $default?->modules ?? self::allModuleKeys();
        }

        $modules = $plan->modules ?? [];

        return is_array($modules) ? array_values($modules) : [];
    }

    public static function allows(string $module, ?\App\Models\Organization $organization = null): bool
    {
        return in_array($module, self::enabledModules($organization), true);
    }

    public static function moduleForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        foreach (config('saas_plans.route_modules', []) as $pattern => $module) {
            if (\Illuminate\Support\Str::is($pattern, $routeName)) {
                return $module;
            }
        }

        return null;
    }

    public static function routeAllowed(?string $routeName, ?\App\Models\Organization $organization = null): bool
    {
        $module = self::moduleForRoute($routeName);

        if (! $module) {
            return true;
        }

        return self::allows($module, $organization);
    }
}
