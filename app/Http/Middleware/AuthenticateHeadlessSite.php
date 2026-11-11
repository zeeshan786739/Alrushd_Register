<?php

namespace App\Http\Middleware;

use App\Models\WebsiteCms;
use App\Support\PublicOrganizationContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates an external website by its API token (Authorization: Bearer …)
 * and scopes the request to that site's organization.
 */
class AuthenticateHeadlessSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $cms = WebsiteCms::findByApiToken($request->bearerToken());
        $organization = $cms?->organization;

        if (! $cms || ! $organization) {
            return response()->json(['message' => 'Invalid website API token.'], 401);
        }

        if (! $organization->allowsAccess()) {
            return response()->json(['message' => 'This school account is not active.'], 403);
        }

        PublicOrganizationContext::set($organization);
        $request->attributes->set('headless_cms', $cms);

        return $next($request);
    }
}
