<?php

namespace App\Support;

final class AdminNav
{
    public static function isActive(string|array $patterns): bool
    {
        $patterns = array_values(array_filter((array) $patterns));

        if ($patterns === []) {
            return false;
        }

        return request()->routeIs(...$patterns);
    }

    public static function linkClass(string|array $patterns, string $extra = ''): string
    {
        return trim($extra.' '.(self::isActive($patterns) ? 'active-page' : ''));
    }

    public static function dropdownClass(string|array $patterns): string
    {
        $classes = ['dropdown'];

        if (self::isActive($patterns)) {
            $classes[] = 'dropdown-open';
            $classes[] = 'open';
        }

        return implode(' ', $classes);
    }

    public static function expanded(string|array $patterns): string
    {
        return self::isActive($patterns) ? 'true' : 'false';
    }
}
