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
        // Fetch the setting from the database
        $settings = Setting::first();

        if ($settings) {
            // Use the settings from the database to set the Stripe keys
            config([
                'services.stripe.key' => $settings->stripe_key ?? env('STRIPE_KEY'),
                'services.stripe.secret' => $settings->stripe_secret ?? env('STRIPE_SECRET'),
            ]);
        } else {
            // If no settings found, fallback to default .env values
            config([
                'services.stripe.key' => env('STRIPE_KEY'),
                'services.stripe.secret' => env('STRIPE_SECRET'),
            ]);
        }
    }
}
