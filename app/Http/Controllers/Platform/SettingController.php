<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\Platform\PlatformActivityLogger;
use App\Services\Platform\PlatformMailService;
use App\Services\Platform\StripeBillingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(StripeBillingService $billing, PlatformMailService $mail)
    {
        return view('platform.settings.index', [
            'settings' => PlatformSetting::all_cached(),
            'stripeConfigured' => $billing->isConfigured(),
            'mailConfigured' => $mail->isConfigured(),
            'sendGridReady' => $mail->sendGridReady(),
            'mailProvider' => $mail->providerLabel(),
            'mailFrom' => $mail->fromEmail(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'platform_name' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'stripe_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret' => ['nullable', 'string', 'max:255'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
            'sendgrid_api_key' => ['nullable', 'string', 'max:255'],
            'mail_from_email' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'notify_email' => ['nullable', 'email', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            if (in_array($key, ['stripe_secret', 'stripe_webhook_secret', 'sendgrid_api_key'], true)
                && ($value === null || $value === '')) {
                continue;
            }

            PlatformSetting::set($key, $value);
        }

        PlatformActivityLogger::log('settings.updated', 'Platform settings updated');

        return back()->with('success', 'Platform settings saved.');
    }

    public function testMail(Request $request, PlatformMailService $mail)
    {
        $data = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $result = $mail->sendView(
            $data['test_email'],
            'Test email from '.$mail->platformName(),
            'emails.platform.test-mail',
            ['recipient' => $data['test_email']]
        );

        if ($result->accepted) {
            PlatformActivityLogger::log('settings.mail_test', 'Test email sent to '.$data['test_email']);

            return back()->with('success', 'Test email sent to '.$data['test_email'].' via '.$result->provider.'.');
        }

        return back()->with('error', 'Test email failed: '.($result->error ?: 'Unknown mail error. Check SendGrid API key or SMTP settings.'));
    }
}
