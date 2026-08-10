<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\Platform\PlatformActivityLogger;
use App\Services\Platform\StripeBillingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(StripeBillingService $billing)
    {
        return view('platform.settings.index', [
            'settings' => PlatformSetting::all_cached(),
            'stripeConfigured' => $billing->isConfigured(),
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
        ]);

        foreach ($data as $key => $value) {
            PlatformSetting::set($key, $value);
        }

        PlatformActivityLogger::log('settings.updated', 'Platform settings updated');

        return back()->with('success', 'Platform settings saved.');
    }
}
