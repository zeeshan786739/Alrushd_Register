<?php

namespace App\Support;

use App\Models\Organization;

/**
 * Request-scoped tenant for public website / form routes (resolved from host or URL).
 */
class PublicOrganizationContext
{
    private static ?Organization $organization = null;

    private static bool $locked = false;

    public static function set(Organization $organization): void
    {
        self::$organization = $organization;
        self::$locked = true;
    }

    public static function get(): ?Organization
    {
        return self::$organization;
    }

    public static function id(): ?int
    {
        return self::$organization?->id;
    }

    public static function has(): bool
    {
        return self::$organization !== null;
    }

    public static function getOrFail(): Organization
    {
        if (! self::$organization) {
            abort(404, 'School website not found.');
        }

        return self::$organization;
    }

    public static function isLocked(): bool
    {
        return self::$locked;
    }

    public static function clear(): void
    {
        self::$organization = null;
        self::$locked = false;
    }
}
