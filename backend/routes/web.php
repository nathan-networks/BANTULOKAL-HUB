<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('login.submit');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Service request management
        Route::get('/service-requests', [ServiceRequestController::class, 'index'])
            ->name('service-requests.index');

        Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])
            ->name('service-requests.show');

        Route::patch('/service-requests/{serviceRequest}/review', [ServiceRequestController::class, 'review'])
            ->name('service-requests.review');

        // Manual payment verification
        Route::patch(
            '/service-requests/{serviceRequest}/verify-payment',
            [ServiceRequestController::class, 'verifyPayment']
        )->name('service-requests.verify-payment');

        Route::post('/service-requests/{serviceRequest}/resend-payment-confirmation', [ServiceRequestController::class, 'resendPaymentConfirmation'])->name('service-requests.resend-payment-confirmation');
    });
});
