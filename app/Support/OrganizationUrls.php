<?php

namespace App\Support;

use App\Models\Organization;
use Illuminate\Support\Str;

class OrganizationUrls
{
    public static function usesSubdomain(): bool
    {
        return filled(config('saas.tenant_domain'))
            && config('saas.tenant_url_mode', 'subdomain') === 'subdomain';
    }

    public static function publicBase(Organization $organization): string
    {
        if ($organization->custom_domain && $organization->custom_domain_verified_at) {
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

            return $scheme.'://'.Str::lower($organization->custom_domain);
        }

        if (self::usesSubdomain()) {
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';
            $domain = config('saas.tenant_domain');

            return $scheme.'://'.$organization->slug.'.'.$domain;
        }

        return url('/'.self::pathPrefix().'/'.$organization->slug);
    }

    public static function publicPath(Organization $organization, string $path = '/'): string
    {
        $path = '/'.ltrim($path, '/');

        if (self::usesSubdomain() || ($organization->custom_domain && $organization->custom_domain_verified_at)) {
            return rtrim(self::publicBase($organization), '/').$path;
        }

        return url('/'.self::pathPrefix().'/'.$organization->slug.$path);
    }

    public static function pathPrefix(): string
    {
        return trim((string) config('saas.tenant_path_prefix', 'w'), '/');
    }

    public static function adminLoginUrl(Organization $organization): string
    {
        return url('/admin/login');
    }

    public static function verificationHost(): string
    {
        return '_enrolliq.'.(config('saas.tenant_domain') ?: parse_url((string) config('app.url'), PHP_URL_HOST));
    }

    public static function verificationRecord(Organization $organization): string
    {
        return 'enrolliq-verify='.$organization->custom_domain_verification_token;
    }
}
