<?php

namespace App\Support;

/**
 * Tiny safe cache-busting for locally hosted admin assets.
 * Appends ?v={filemtime} so CDN/browsers fetch a new URL after deploy.
 */
final class AdminAsset
{
    public static function url(string $relativePath): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $url = asset($relativePath);
        $version = self::version($relativePath);

        if ($version === null) {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').'v='.$version;
    }

    public static function version(string $relativePath): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $absolute = public_path($relativePath);

        if (! is_string($absolute) || $absolute === '' || ! is_file($absolute)) {
            return null;
        }

        $mtime = @filemtime($absolute);

        return ($mtime !== false && $mtime > 0) ? (string) $mtime : null;
    }
}
