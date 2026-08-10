<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAdminHasAnyRole
{
    public function handle($request, Closure $next)
    {
        $user = Auth::guard('admin')->user();

        // Logout must always work, whatever roles the admin has.
        if ($request->routeIs('admin.logout')) {
            return $next($request);
        }

        // Platform admins live in /superadmin — send them home instead of a 403.
        if ($user && $user->isPlatformAdmin()) {
            return redirect()->route('platform.dashboard');
        }

        if ($user && $user->roles->isNotEmpty()) {
            return $next($request);
        }

        abort(403, 'Unauthorized: Admin has no assigned role.');
    }
}
