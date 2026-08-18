<?php

use App\Http\Controllers\Admin\Integrations\FacebookIntegrationController;
use App\Http\Controllers\Admin\Integrations\IntegrationHubController;
use App\Http\Controllers\Admin\Integrations\TikTokIntegrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('integrations')->name('integrations.')->group(function () {
    Route::get('/', [IntegrationHubController::class, 'index'])->name('hub');

    Route::prefix('facebook')->name('facebook.')->group(function () {
        Route::get('/', [FacebookIntegrationController::class, 'show'])->name('show');
        Route::get('/connect', [FacebookIntegrationController::class, 'connect'])->name('connect');
        Route::get('/callback', [FacebookIntegrationController::class, 'callback'])->name('callback');
        Route::post('/select-page', [FacebookIntegrationController::class, 'selectPage'])->name('select-page');
        Route::post('/disconnect', [FacebookIntegrationController::class, 'disconnect'])->name('disconnect');
        Route::post('/sync-forms', [FacebookIntegrationController::class, 'syncForms'])->name('sync-forms');
        Route::post('/register-form', [FacebookIntegrationController::class, 'registerForm'])->name('register-form');
        Route::put('/mappings/{mapping}', [FacebookIntegrationController::class, 'updateMapping'])->name('mappings.update');
        Route::delete('/mappings/{mapping}', [FacebookIntegrationController::class, 'deleteMapping'])->name('mappings.destroy');
        Route::post('/reprocess-pending', [FacebookIntegrationController::class, 'reprocessPending'])->name('reprocess-pending');
        Route::post('/submissions/{submission}/reprocess', [FacebookIntegrationController::class, 'reprocessSubmission'])->name('submissions.reprocess');
        Route::post('/test-connection', [FacebookIntegrationController::class, 'testConnection'])->name('test-connection');
    });

    Route::prefix('tiktok')->name('tiktok.')->group(function () {
        Route::get('/', [TikTokIntegrationController::class, 'show'])->name('show');
        Route::get('/connect', [TikTokIntegrationController::class, 'connect'])->name('connect');
        Route::get('/callback', [TikTokIntegrationController::class, 'callback'])->name('callback');
        Route::post('/select-advertiser', [TikTokIntegrationController::class, 'selectAdvertiser'])->name('select-advertiser');
        Route::post('/sync-forms', [TikTokIntegrationController::class, 'syncForms'])->name('sync-forms');
        Route::get('/forms/{tiktokForm}', [TikTokIntegrationController::class, 'configure'])->name('forms.configure');
        Route::put('/forms/{tiktokForm}', [TikTokIntegrationController::class, 'updateMapping'])->name('forms.update');
        Route::post('/register-webhook', [TikTokIntegrationController::class, 'registerWebhook'])->name('register-webhook');
        Route::post('/reprocess-pending', [TikTokIntegrationController::class, 'reprocessPending'])->name('reprocess-pending');
        Route::post('/submissions/{tiktokSubmission}/reprocess', [TikTokIntegrationController::class, 'reprocessSubmission'])->name('submissions.reprocess');
    });
});
