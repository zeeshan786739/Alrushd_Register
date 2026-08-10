<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        if (! $admin->isPlatformAdmin()) {
            abort(403, 'This area is restricted to platform administrators.');
        }

        return $next($request);
    }
}
