<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $settings = Setting::first();
        } catch (\Throwable) {
            $settings = null;
        }

        if ($settings) {
            config([
                'services.stripe.key' => $settings->stripe_key ?? env('STRIPE_KEY'),
                'services.stripe.secret' => $settings->stripe_secret ?? env('STRIPE_SECRET'),
            ]);
        } else {
            config([
                'services.stripe.key' => env('STRIPE_KEY'),
                'services.stripe.secret' => env('STRIPE_SECRET'),
            ]);
        }
    }
}
