<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\OrganizationContext;
use App\Support\UserManagementHelper;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(UserManagementHelper::canManageAccess(), 403);

            return $next($request);
        });
        $this->middleware('permission:view role')->only('index');
        $this->middleware('permission:create role')->only(['create', 'store']);
        $this->middleware('permission:edit role')->only(['edit', 'update']);
        $this->middleware('permission:delete role')->only('destroy');
    }

    public function index()
    {
        $roles = Role::query()
            ->where('guard_name', 'admin')
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                $role->users_count = UserManagementHelper::usersCountForRole($role);

                return $role;
            });

        $stats = UserManagementHelper::stats();

        return view('admin.role-permission.roles.index', compact('roles', 'stats'));
    }

    public function create()
    {
        $permissions = Permission::query()->where('guard_name', 'admin')->orderBy('name')->get();
        $groupedPermissions = UserManagementHelper::groupPermissions($permissions);
        $stats = UserManagementHelper::stats();

        return view('admin.role-permission.roles.create', compact('permissions', 'groupedPermissions', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array|required|min:1',
        ]);

        $role = Role::create([
            'name' => trim($request->name),
            'guard_name' => 'admin',
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(string $id)
    {
        $role = Role::query()->where('guard_name', 'admin')->findOrFail($id);
        $permissions = Permission::query()->where('guard_name', 'admin')->orderBy('name')->get();
        $groupedPermissions = UserManagementHelper::groupPermissions($permissions);
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $stats = UserManagementHelper::stats();

        return view('admin.role-permission.roles.edit', compact(
            'role',
            'permissions',
            'groupedPermissions',
            'rolePermissions',
            'stats'
        ));
    }

    public function update(Request $request, string $id)
    {
        $role = Role::query()->where('guard_name', 'admin')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$id,
            'permissions' => 'array|required|min:1',
        ]);

        if (! UserManagementHelper::isProtectedRole($role)) {
            $role->update(['name' => $request->name]);
        }

        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(string $id)
    {
        $role = Role::query()->where('guard_name', 'admin')->findOrFail($id);

        if (UserManagementHelper::isProtectedRole($role)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'This system role cannot be deleted.');
        }

        $assignedUsers = UserManagementHelper::usersCountForRole($role);
        if ($assignedUsers > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', "Cannot delete \"{$role->name}\" — {$assignedUsers} team member(s) still use this role. Reassign them first.");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
