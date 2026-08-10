<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Organization;
use App\Services\Platform\PlatformActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, Organization $organization, Admin $admin)
    {
        abort_unless($admin->organization_id === $organization->id, 404);

        if ($admin->isPlatformAdmin()) {
            return back()->with('error', 'Cannot impersonate a platform admin.');
        }

        PlatformActivityLogger::log(
            'school.impersonated',
            'Logged in as ' . $admin->email . ' (' . $organization->name . ')',
            $organization
        );

        $request->session()->put('platform_impersonator_id', auth('admin')->id());

        Auth::guard('admin')->login($admin);

        return redirect()->route('admin.dashboard')
            ->with('success', 'You are now logged in as ' . $admin->name . '.');
    }

    public function leave(Request $request)
    {
        $impersonatorId = $request->session()->pull('platform_impersonator_id');

        abort_unless($impersonatorId, 403);

        $platformAdmin = Admin::where('id', $impersonatorId)
            ->where('is_platform_admin', true)
            ->firstOrFail();

        Auth::guard('admin')->login($platformAdmin);

        return redirect()->route('platform.dashboard')
            ->with('success', 'Returned to the Super Admin panel.');
    }
}
