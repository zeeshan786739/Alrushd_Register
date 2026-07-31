<?php

use App\Http\Controllers\Admin\Integrations\FacebookIntegrationController;
use App\Http\Controllers\Admin\Integrations\IntegrationHubController;
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

    Route::get('tiktok', [\App\Http\Controllers\Admin\Integrations\TikTokIntegrationController::class, 'show'])->name('tiktok.show');
});
