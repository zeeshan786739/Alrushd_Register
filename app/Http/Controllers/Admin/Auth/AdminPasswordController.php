<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminAccessLinkMailer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AdminPasswordController extends Controller
{
    public function request(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function email(Request $request, AdminAccessLinkMailer $mailer): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        $email = mb_strtolower(trim($validated['email']));
        $admin = Admin::query()->with('organization')->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($admin) {
            try {
                $token = Password::broker('admins')->createToken($admin);
                $mailer->sendPasswordReset($admin, route('admin.password.reset', [
                    'token' => $token,
                    'email' => $admin->email,
                ]));
            } catch (\Throwable $exception) {
                Log::warning('Admin password reset delivery failed', [
                    'admin_id' => $admin->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return back()->with('status', 'If an admin account exists for that email, a password reset link has been sent.');
    }

    public function reset(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
            'invitation' => $request->routeIs('admin.invitation.accept'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('admins')->reset(
            $credentials,
            function (Admin $admin, string $password): void {
                $admin->forceFill(['password' => Hash::make($password)]);
                $admin->setRememberToken(Str::random(60));
                $admin->save();
                event(new PasswordReset($admin));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
        }

        return redirect()->route('admin.login', ['email' => $credentials['email']])
            ->with('status', 'Your password has been set. You can now sign in.');
    }
}
