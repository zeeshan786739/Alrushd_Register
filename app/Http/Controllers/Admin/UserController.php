<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminAccessLinkMailer;
use App\Support\OrganizationContext;
use App\Support\UserManagementHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(UserManagementHelper::canManageAccess(), 403);

            return $next($request);
        });
        $this->middleware('permission:view user')->only('index');
        $this->middleware('permission:create user')->only(['create', 'store']);
        $this->middleware('permission:edit user')->only(['edit', 'update']);
        $this->middleware('permission:delete user')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Admin::query()
            ->forCurrentOrganization()
            ->where('is_platform_admin', false)
            ->with('roles')
            ->orderBy('name');

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->get();
        $roles = Role::query()->where('guard_name', 'admin')->orderBy('name')->get();
        $stats = UserManagementHelper::stats();

        return view('admin.role-permission.users.index', compact('users', 'roles', 'stats'));
    }

    public function create()
    {
        $roles = Role::query()
            ->where('guard_name', 'admin')
            ->with('permissions')
            ->orderBy('name')
            ->get();
        $stats = UserManagementHelper::stats();

        return view('admin.role-permission.users.create', compact('roles', 'stats'));
    }

    public function store(
        Request $request,
        AdminAccessLinkMailer $mailer,
    )
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'roles' => 'required|array|min:1',
        ]);

        $user = Admin::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Str::random(64),
            'organization_id' => OrganizationContext::idOrFail(),
        ]);

        $user->assignRole($request->roles);

        try {
            $inviterName = auth('admin')->user()?->name ?: 'An administrator';
            $token = Password::broker('admins')->createToken($user);
            $mailer->sendInvitation($user->load('organization'), route('admin.invitation.accept', [
                'token' => $token,
                'email' => $user->email,
            ]), $inviterName);
        } catch (\Throwable $exception) {
            Log::warning('Team invitation delivery failed', [
                'admin_id' => $user->id,
                'organization_id' => $user->organization_id,
                'error' => $exception->getMessage(),
            ]);
            $user->syncRoles([]);
            $user->delete();

            Password::broker('admins')->deleteToken($user);

            return back()->withInput()
                ->with('error', 'The invitation could not be sent: '.$exception->getMessage());
        }

        return redirect()->route('admin.users.index')->with('success', 'Team member created and invitation email sent successfully.');
    }

    public function edit($id)
    {
        $user = $this->findOrganizationUser($id);
        $roles = Role::query()->where('guard_name', 'admin')->orderBy('name')->get();
        $userRoles = $user->roles->pluck('name')->toArray();
        $stats = UserManagementHelper::stats();

        return view('admin.role-permission.users.edit', compact('user', 'roles', 'userRoles', 'stats'));
    }

    public function update(Request $request, $id)
    {
        $user = $this->findOrganizationUser($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,'.$id,
            'password' => 'nullable|min:8|confirmed',
            'roles' => 'required|array|min:1',
        ]);

        $user->name = trim($request->name);
        $user->email = strtolower(trim($request->email));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        $user->syncRoles($request->roles);

        return redirect()->route('admin.users.index')->with('success', 'Team member updated successfully.');
    }

    public function destroy($id)
    {
        $user = $this->findOrganizationUser($id);

        if ((int) $user->id === (int) auth('admin')->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account while logged in.');
        }

        $user->syncRoles([]);
        $user->syncPermissions([]);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Team member removed successfully.');
    }

    protected function findOrganizationUser(int|string $id): Admin
    {
        return Admin::query()
            ->forCurrentOrganization()
            ->where('is_platform_admin', false)
            ->findOrFail($id);
    }
}
