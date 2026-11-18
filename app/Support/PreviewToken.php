<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\WebsiteCms;

/**
 * Short-lived link that opens the connected website in DRAFT (preview) mode for one admin.
 *
 *   token = base64url(json{o: organization, a: admin, p: path, e: expires}) . "." . base64url(HMAC-SHA256)
 *
 * The CMS issues it (valid 30 minutes); the website sends it back to the CRM's headless API
 * (/preview/verify) before switching draft mode on, so the CRM decides: right signature, not
 * expired, issued for this school, and the admin still exists here and may view website settings.
 */
class PreviewToken
{
    public const TTL_MINUTES = 30;

    public static function issue(WebsiteCms $cms, Admin $admin, string $path = '/', int $ttlMinutes = self::TTL_MINUTES): string
    {
        $payload = self::encode(json_encode([
            'o' => (int) $cms->organization_id,
            'a' => (int) $admin->id,
            'p' => self::cleanPath($path),
            'e' => now()->addMinutes($ttlMinutes)->timestamp,
        ]));

        return $payload.'.'.self::encode(self::sign($payload));
    }

    /**
     * @return array{ok: true, path: string, admin_id: int}|array{ok: false, reason: string}
     *         reason: malformed | signature | expired | organization | admin
     */
    public static function verify(?string $token, WebsiteCms $cms): array
    {
        $parts = explode('.', (string) $token);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return ['ok' => false, 'reason' => 'malformed'];
        }
        [$payload, $signature] = $parts;
        if (! hash_equals(self::encode(self::sign($payload)), $signature)) {
            return ['ok' => false, 'reason' => 'signature'];
        }
        $data = json_decode((string) self::decode($payload), true);
        if (! is_array($data) || ! isset($data['o'], $data['a'], $data['p'], $data['e'])) {
            return ['ok' => false, 'reason' => 'malformed'];
        }
        if ((int) $data['e'] < now()->timestamp) {
            return ['ok' => false, 'reason' => 'expired'];
        }
        if ((int) $data['o'] !== (int) $cms->organization_id) {
            return ['ok' => false, 'reason' => 'organization'];
        }
        $admin = Admin::query()->whereKey((int) $data['a'])->first();
        if (! $admin || (int) $admin->organization_id !== (int) $cms->organization_id || ! $admin->can('view setting')) {
            return ['ok' => false, 'reason' => 'admin'];
        }

        return ['ok' => true, 'path' => self::cleanPath((string) $data['p']), 'admin_id' => (int) $admin->id];
    }

    /** A same-site path only ("/fees"), never "//evil.example" or a full URL. */
    public static function cleanPath(string $path): string
    {
        $path = '/'.ltrim(trim($path), '/');

        return preg_match('#^/[A-Za-z0-9/_\-.~%]*$#', $path) ? $path : '/';
    }

    private static function sign(string $payload): string
    {
        return hash_hmac('sha256', 'cms-preview|'.$payload, (string) config('app.key'), true);
    }

    private static function encode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private static function decode(string $value): string|false
    {
        return base64_decode(strtr($value, '-_', '+/'), true);
    }
}
