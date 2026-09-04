<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Support\UserManagementHelper;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth('admin')->user();

            if (! UserManagementHelper::canManageAccess($user)) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $stats = UserManagementHelper::stats();

        $users = Admin::query()
            ->forCurrentOrganization()
            ->where('is_platform_admin', false)
            ->with('roles')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $roles = Role::query()
            ->where('guard_name', 'admin')
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                $role->users_count = UserManagementHelper::usersCountForRole($role);

                return $role;
            });

        return view('admin.role-permission.index', compact('stats', 'users', 'roles'));
    }
}
