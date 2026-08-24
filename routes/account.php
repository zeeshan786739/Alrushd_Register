<?php

use App\Http\Controllers\Admin\Account\AccountController;
use App\Http\Controllers\Admin\Account\PaymentSettingsController;
use App\Http\Controllers\Admin\AdminProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');

    Route::get('/profile', [AdminProfileController::class, 'settings'])->name('profile');
    Route::put('/profile', [AdminProfileController::class, 'updateSettings'])->name('profile.update');

    Route::get('/security', [AdminProfileController::class, 'changePassword'])->name('security');
    Route::put('/security', [AdminProfileController::class, 'updatePassword'])->name('security.update');

    Route::get('/payments', [PaymentSettingsController::class, 'edit'])->name('payments.edit');
    Route::put('/payments', [PaymentSettingsController::class, 'update'])->name('payments.update');
    Route::post('/payments/test', [PaymentSettingsController::class, 'test'])->name('payments.test');
});
