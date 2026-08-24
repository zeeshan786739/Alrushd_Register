<?php

use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\DemoRequestController;
use App\Http\Controllers\Platform\ImpersonationController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\SchoolController;
use App\Http\Controllers\Platform\SettingController;
use App\Http\Controllers\Platform\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Platform (Super Admin) panel — SaaS owner only
|--------------------------------------------------------------------------
*/

Route::prefix('superadmin')->name('platform.')
    ->middleware(['auth:admin', 'platform.admin'])
    ->group(function () {

        Route::get('/', fn () => redirect()->route('platform.dashboard'));
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Schools (tenants)
        Route::get('schools', [SchoolController::class, 'index'])->name('schools.index');
        Route::get('schools/create', [SchoolController::class, 'create'])->name('schools.create');
        Route::post('schools', [SchoolController::class, 'store'])->name('schools.store');
        Route::get('schools/{organization}', [SchoolController::class, 'show'])->name('schools.show');
        Route::get('schools/{organization}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::put('schools/{organization}', [SchoolController::class, 'update'])->name('schools.update');
        Route::post('schools/{organization}/status', [SchoolController::class, 'updateStatus'])->name('schools.status');
        Route::post('schools/{organization}/subscription', [SchoolController::class, 'updateSubscription'])->name('schools.subscription');
        Route::post('schools/{organization}/admins', [SchoolController::class, 'storeAdmin'])->name('schools.admins.store');
        Route::post('schools/{organization}/impersonate/{admin}', [ImpersonationController::class, 'start'])->name('schools.impersonate');

        // Subscription plans
        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('plans/create', [PlanController::class, 'create'])->name('plans.create');
        Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
        Route::get('plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
        Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
        Route::post('plans/{plan}/toggle', [PlanController::class, 'toggle'])->name('plans.toggle');
        Route::post('plans/{plan}/set-default', [PlanController::class, 'setDefault'])->name('plans.set-default');
        Route::post('plans/{plan}/sync-stripe', [PlanController::class, 'syncStripe'])->name('plans.sync-stripe');
        Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');

        // Subscriptions
        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('subscriptions/normalize', [SubscriptionController::class, 'normalize'])->name('subscriptions.normalize');
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

        // Demo requests
        Route::get('demo-requests', [DemoRequestController::class, 'index'])->name('demo-requests.index');
        Route::get('demo-requests/{demoRequest}', [DemoRequestController::class, 'show'])->name('demo-requests.show');
        Route::put('demo-requests/{demoRequest}', [DemoRequestController::class, 'update'])->name('demo-requests.update');
        Route::delete('demo-requests/{demoRequest}', [DemoRequestController::class, 'destroy'])->name('demo-requests.destroy');

        // Platform settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

// Leave impersonation — executed while logged in as the tenant admin.
Route::post('admin/impersonation/leave', [ImpersonationController::class, 'leave'])
    ->middleware('auth:admin')
    ->name('admin.impersonation.leave');
