<?php

namespace App\Http\Middleware;

use App\Support\PlanEntitlements;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanModule
{
    public function handle(Request $request, Closure $next, ?string $module = null): Response
    {
        $module ??= PlanEntitlements::moduleForRoute($request->route()?->getName());

        if (! $module || PlanEntitlements::allows($module)) {
            return $next($request);
        }

        $label = config("saas_plans.modules.{$module}.label", 'This feature');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "{$label} is not included in your current plan. Upgrade to unlock it.",
            ], 403);
        }

        return redirect()
            ->route('admin.billing.index')
            ->with('error', "{$label} is not included in your current plan. Choose a plan that includes it, or contact support.");
    }
}
