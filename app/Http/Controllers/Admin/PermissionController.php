<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\UserManagementHelper;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(UserManagementHelper::canManageAccess(), 403);

            return $next($request);
        });
        $this->middleware('permission:view permission')->only('index');
        $this->middleware('permission:create permission')->only(['create', 'store']);
        $this->middleware('permission:edit permission')->only(['edit', 'update']);
        $this->middleware('permission:delete permission')->only('destroy');
    }

    public function index()
    {
        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->orderBy('name')
            ->get();

        $groupedPermissions = UserManagementHelper::groupPermissions($permissions);
        $stats = UserManagementHelper::stats();

        return view('admin.role-permission.permissions.index', compact(
            'permissions',
            'groupedPermissions',
            'stats'
        ));
    }

    public function create()
    {
        $stats = UserManagementHelper::stats();
        $existingNames = Permission::query()
            ->where('guard_name', 'admin')
            ->pluck('name')
            ->map(fn (string $name) => strtolower($name))
            ->values()
            ->all();

        return view('admin.role-permission.permissions.create', [
            'stats' => $stats,
            'existingNames' => $existingNames,
            'builderActions' => UserManagementHelper::permissionBuilderActions(),
            'builderModules' => UserManagementHelper::permissionBuilderModules(),
            'builderPresets' => UserManagementHelper::permissionBuilderPresets(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'action' => 'nullable|string|max:50',
            'resource' => 'nullable|string|max:120',
            'name' => 'nullable|string|max:255|unique:permissions,name',
        ]);

        $name = $request->filled('name')
            ? UserManagementHelper::normalizePermissionName($request->name)
            : UserManagementHelper::buildPermissionName(
                (string) $request->input('action'),
                (string) $request->input('resource')
            );

        if ($name === '' || ! str_contains($name, ' ')) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Choose what someone can do and what area it applies to.']);
        }

        if (Permission::query()->where('guard_name', 'admin')->where('name', $name)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['name' => "The permission \"{$name}\" already exists. Assign it to a role instead."]);
        }

        Permission::create([
            'name' => $name,
            'guard_name' => 'admin',
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission “'.UserManagementHelper::formatPermissionName($name).'” created. You can now add it to a role.');
    }

    public function edit(string $id)
    {
        $permission = Permission::query()->where('guard_name', 'admin')->findOrFail($id);
        $stats = UserManagementHelper::stats();

        return view('admin.role-permission.permissions.edit', compact('permission', 'stats'));
    }

    public function update(Request $request, string $id)
    {
        $permission = Permission::query()->where('guard_name', 'admin')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,'.$id,
        ]);

        $permission->update([
            'name' => trim($request->name),
        ]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(string $id)
    {
        $permission = Permission::query()->where('guard_name', 'admin')->findOrFail($id);
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
