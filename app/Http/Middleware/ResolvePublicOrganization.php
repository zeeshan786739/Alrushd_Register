<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Support\OrganizationUrls;
use App\Support\PublicOrganizationContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResolvePublicOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! PublicOrganizationContext::has()) {
            $organization = $this->resolveFromRequest($request);

            if ($organization) {
                PublicOrganizationContext::set($organization);
            } elseif (config('saas.legacy_default_tenant', true) && ! $this->isSaasMarketingHost($request)) {
                PublicOrganizationContext::set(Organization::default());
            }
        }

        return $next($request);
    }

    private function resolveFromRequest(Request $request): ?Organization
    {
        if ($slug = $request->route('orgSlug')) {
            return Organization::where('slug', $slug)->where('is_active', true)->first();
        }

        $host = Str::lower($request->getHost());

        if ($organization = Organization::query()
            ->where('custom_domain', $host)
            ->whereNotNull('custom_domain_verified_at')
            ->where('is_active', true)
            ->first()) {
            return $organization;
        }

        $tenantDomain = config('saas.tenant_domain');
        if ($tenantDomain && str_ends_with($host, '.'.Str::lower($tenantDomain))) {
            $slug = substr($host, 0, -(strlen($tenantDomain) + 1));

            if ($slug !== '' && $slug !== 'www') {
                return Organization::where('slug', $slug)->where('is_active', true)->first();
            }
        }

        $prefix = OrganizationUrls::pathPrefix();
        if (preg_match('#^/'.preg_quote($prefix, '#').'/([^/]+)#', $request->getPathInfo(), $matches)) {
            return Organization::where('slug', $matches[1])->where('is_active', true)->first();
        }

        return null;
    }

    private function isSaasMarketingHost(Request $request): bool
    {
        $saasDomain = config('saas.domain');

        return $saasDomain && Str::lower($request->getHost()) === Str::lower($saasDomain);
    }
}
