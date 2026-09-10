<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminAccessLinkMailer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $isInvitation = $this->isInvitationRequest($request);

        $rules = [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if ($isInvitation) {
            $rules['first_name'] = ['required', 'string', 'max:100'];
            $rules['last_name'] = ['required', 'string', 'max:100'];
        }

        $validated = $request->validate($rules);

        $credentials = [
            'token' => $validated['token'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'password_confirmation' => $request->input('password_confirmation'),
        ];

        $admin = null;

        $status = Password::broker('admins')->reset(
            $credentials,
            function (Admin $user, string $password) use ($isInvitation, $validated, &$admin): void {
                $updates = [
                    'password' => Hash::make($password),
                ];

                if ($isInvitation) {
                    $updates['name'] = trim($validated['first_name']).' '.trim($validated['last_name']);
                }

                $user->forceFill($updates);
                $user->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
                $admin = $user;
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email', 'first_name', 'last_name'))
                ->withErrors(['email' => __($status)]);
        }

        if ($isInvitation && $admin instanceof Admin) {
            $admin = $admin->fresh();

            Auth::guard('admin')->login($admin, true);
            $request->session()->regenerate();
            $admin->forceFill(['last_login_at' => now()])->save();

            if ($admin->isPlatformAdmin()) {
                return redirect()->intended(route('platform.dashboard', absolute: false))
                    ->with('success', 'Welcome! Your account is ready.');
            }

            return redirect()->intended(route('admin.dashboard', absolute: false))
                ->with('success', 'Welcome! Your account is ready.');
        }

        return redirect()->route('admin.login', ['email' => $credentials['email']])
            ->with('status', 'Your password has been set. You can now sign in.');
    }

    private function isInvitationRequest(Request $request): bool
    {
        if ($request->boolean('invitation') || $request->routeIs('admin.invitation.accept')) {
            return true;
        }

        $email = mb_strtolower(trim((string) $request->input('email', '')));
        if ($email === '') {
            return false;
        }

        return Admin::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('name', Admin::INVITATION_PLACEHOLDER_NAME)
            ->exists();
    }
}
