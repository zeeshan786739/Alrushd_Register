<?php

use App\Http\Controllers\Api\DynamicFormController;
use App\Http\Controllers\Api\FrontendConfigController;
use App\Http\Controllers\Api\FrontendFormDataController;
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
