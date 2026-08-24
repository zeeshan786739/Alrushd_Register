<?php

use App\Http\Controllers\Api\DynamicFormController;
use App\Http\Controllers\Api\FrontendFormDataController;
use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

/*
| Per-tenant public website routes (CMS landing + dynamic forms).
| Registered at /w/{slug}/… and optionally {slug}.{tenant_domain}.
*/

$tenantPublicRoutes = function () {
    Route::get('/', [FrontendController::class, 'index']);
    Route::get('/privacy-policy', [FrontendController::class, 'privacyPolicy']);
    Route::get('/forms/{slug}', [FrontendController::class, 'dynamicForm']);
    Route::get('/forms/{slug}/success', [FrontendController::class, 'dynamicFormSuccess']);
    Route::get('/api/frontend/csrf', [FrontendFormDataController::class, 'csrf']);
    Route::get('/api/frontend/forms', [DynamicFormController::class, 'index']);
    Route::get('/api/frontend/forms/{slug}', [DynamicFormController::class, 'show']);
    Route::post('/api/frontend/forms/{slug}/submit', [DynamicFormController::class, 'submit']);
};

Route::prefix('w/{orgSlug}')->middleware('tenant.public')->group($tenantPublicRoutes);

if ($tenantDomain = config('saas.tenant_domain')) {
    Route::domain('{orgSlug}.'.$tenantDomain)->middleware('tenant.public')->group($tenantPublicRoutes);
}
