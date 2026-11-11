<?php

use App\Http\Controllers\Api\DynamicFormController;
use App\Http\Controllers\Api\FrontendConfigController;
use App\Http\Controllers\Api\FrontendFormDataController;
use App\Http\Controllers\Api\Headless\HeadlessSiteController;
use App\Http\Controllers\MultiStepFormController;
use Illuminate\Support\Facades\Route;

Route::prefix('frontend')->group(function () {
    Route::get('/config', [FrontendConfigController::class, 'config']);
    Route::get('/form-options', [FrontendFormDataController::class, 'options']);
    Route::get('/admission/{id?}', [FrontendFormDataController::class, 'admission']);
    Route::get('/forms', [DynamicFormController::class, 'index']);
    Route::get('/forms/landing-buttons', [DynamicFormController::class, 'landingButtons']);
    Route::get('/forms/{slug}', [DynamicFormController::class, 'show']);
    Route::post('/forms/{slug}/submit', [DynamicFormController::class, 'submit']);

    Route::get('/packages/{year}', [MultiStepFormController::class, 'getPackages']);
    Route::get('/course-details', [MultiStepFormController::class, 'getCourseDetails']);
});

/*
| Headless website API (v1) — used by an external front end (e.g. the Next.js site).
| Authenticated with the site's API token: Authorization: Bearer <token>.
| Add ?draft=1 to read unpublished content (used by the website's preview mode).
*/
Route::prefix('headless/v1')->middleware(['headless.site', 'throttle:600,1'])->group(function () {
    Route::get('/ping', [HeadlessSiteController::class, 'ping']);
    Route::get('/site', [HeadlessSiteController::class, 'site']);
    Route::get('/pages', [HeadlessSiteController::class, 'pages']);
    Route::get('/page', [HeadlessSiteController::class, 'page']);
    Route::get('/forms/{slug}', [HeadlessSiteController::class, 'form'])->where('slug', '[a-z0-9-]+');
    Route::post('/forms/{slug}/submit', [HeadlessSiteController::class, 'submit'])->where('slug', '[a-z0-9-]+');
});
