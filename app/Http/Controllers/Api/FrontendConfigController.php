<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class FrontendConfigController extends Controller
{
    public function config(): JsonResponse
    {
        try {
            $settings = Setting::first();
        } catch (\Throwable) {
            $settings = null;
        }

        return response()->json([
            'hero_buttons' => config('frontend.hero_buttons'),
            'ctas' => config('frontend.ctas'),
            'footer_explore' => config('frontend.footer_explore'),
            'form_endpoints' => config('frontend.form_endpoints'),
            'site' => [
                'company_name' => $settings?->company_name ?? 'Al-Rushd Online School',
                'email' => $settings?->email_one ?? 'admin@alrushd.org.uk',
                'phone' => $settings?->phone_one ?? '+44 20 3633 0757',
                'address' => $settings?->address,
                'facebook' => $settings?->facebook,
                'instagram' => $settings?->instagram,
                'linkedin' => $settings?->linkedin,
                'copyright' => $settings?->copyright,
            ],
            'payment' => [
                'stripe_key' => $settings?->stripe_key,
                'online_enabled' => (bool) ($settings?->payment_method_status ?? 1),
            ],
        ]);
    }
}
