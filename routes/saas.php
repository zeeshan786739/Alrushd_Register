<?php

use App\Http\Controllers\Saas\DemoRequestController;
use App\Http\Controllers\Saas\LandingController;
use App\Http\Controllers\Saas\SignupController;
use App\Http\Controllers\Saas\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SaaS public marketing site + signup + billing webhooks
|--------------------------------------------------------------------------
| Served under /platform. When SAAS_DOMAIN is configured, the landing is
| additionally served at that domain's root "/".
*/

Route::name('saas.')->group(function () {

    if ($domain = config('saas.domain')) {
        Route::domain($domain)->group(function () {
            Route::get('/', [LandingController::class, 'index'])->name('landing.root');
        });
    }

    Route::prefix('platform')->group(function () {
        Route::get('/', [LandingController::class, 'index'])->name('landing');

        Route::get('book-demo', [DemoRequestController::class, 'create'])->name('demo.create');
        Route::post('book-demo', [DemoRequestController::class, 'store'])
            ->middleware('throttle:10,1')->name('demo.store');

        Route::get('signup', [SignupController::class, 'create'])->name('signup');
        Route::post('signup', [SignupController::class, 'store'])
            ->middleware('throttle:10,1')->name('signup.store');
        Route::get('signup/success', [SignupController::class, 'success'])->name('signup.success');

        // Stripe Checkout return URLs (school billing)
        Route::get('billing/success', [SignupController::class, 'billingSuccess'])->name('billing.success');
        Route::get('billing/cancel', [SignupController::class, 'billingCancel'])->name('billing.cancel');
    });

    Route::post('webhooks/stripe/platform', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');
});
