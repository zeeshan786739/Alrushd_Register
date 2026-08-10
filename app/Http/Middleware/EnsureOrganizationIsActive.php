<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOrganizationIsActive
{
    /** Routes that must stay reachable when the school is locked out. */
    private const ALLOWED_ROUTES = [
        'admin.logout',
        'admin.billing.index',
        'admin.billing.checkout',
        'admin.impersonation.leave',
    ];

    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin || $admin->isPlatformAdmin()) {
            return $next($request);
        }

        $organization = $admin->organization;

        if ($organization && ! $organization->allowsAccess()
            && ! in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)) {
            return response()->view('admin.suspended', [
                'organization' => $organization,
            ], 403);
        }

        return $next($request);
    }
}
